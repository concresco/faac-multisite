<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

use TheLion\OutoftheBox\API\Dropbox\Dropbox;
use TheLion\OutoftheBox\API\Dropbox\DropboxApp;
use TheLion\OutoftheBox\API\Dropbox\Store\DatabasePersistentDataStore;

defined('ABSPATH') || exit;

class App
{
    /**
     * The single instance of the class.
     *
     * @var App
     */
    protected static $_instance;

    /**
     * @var bool
     */
    private $_own_app = false;

    /**
     * @var string
     */
    private $_app_key;

    /**
     * @var string
     */
    private $_app_secret;

    /**
     * @var string
     */
    private $_app_token;

    /**
     * @var Dropbox
     */
    private static $_sdk_client;

    /**
     * @var DropboxApp
     */
    private static $_sdk_client_app;

    /**
     * @var Account
     */
    private static $_current_account;

    /**
     * We don't save your data or share it.
     * It is used for an easy and one-click  authorization process that will always work!
     *
     * @var string
     */
    private $_auth_url = 'https://www.wpcloudplugins.com:443/out-of-the-box/_AuthorizeApp.php';

    public function __construct()
    {
        require_once OUTOFTHEBOX_ROOTDIR.'/vendors/dropbox-sdk/vendor/autoload.php';

        // Call back for refresh token function in SDK client
        add_action('out-of-the-box-refresh-token', [$this, 'refresh_token'], 10, 1);

        add_filter('outofthebox-set-root-namespace-id', [$this, 'get_root_namespace_id'], 10, 1);
    }

    /**
     * App Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return App - App instance
     *
     * @static
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            $app = new self();
        } else {
            $app = self::$_instance;
        }

        if (empty($app::$_sdk_client)) {
            try {
                $app->start_sdk_client(App::get_current_account());
            } catch (\Exception $ex) {
                self::$_instance = $app;

                return self::$_instance;
            }
        }

        self::$_instance = $app;

        if (null !== App::get_current_account()) {
            $app->get_sdk_client(App::get_current_account());
        }

        return self::$_instance;
    }

    public function process_authorization()
    {
        if (!empty($_GET['ver']) && isset($_GET['code'])) {
            $state = strtr($_GET['state'], '-_~', '+/=');

            $urlState = null;

            $splitPos = strpos($state, '|');

            if (false !== $splitPos) {
                $urlState = substr($state, $splitPos + 1);
            }

            $redirectto = base64_decode($urlState);

            if (false === strpos($redirectto, 'outofthebox_authorization')) {
                return false;
            }

            Processor::reset_complete_cache();
            $this->create_access_token();
        }

        // Close oAuth popup and refresh plugin settings page. Only possible with inline javascript.
        echo '<script type="text/javascript">localStorage.setItem("wpcp_refreshParent", "true"); window.close();</script>';

        exit;
    }

    public function has_plugin_own_app()
    {
        return $this->_own_app;
    }

    public function get_auth_url($params = [])
    {
        $auth_helper = self::get_sdk_client()->getAuthHelper();

        if (Core::is_network_authorized() || is_network_admin()) {
            $redirect = network_admin_url('admin.php?page=OutoftheBox_network_settings&action=outofthebox_authorization');
        } else {
            $redirect = admin_url('admin.php?page=OutoftheBox_settings&action=outofthebox_authorization');
        }

        $redirect .= '&license='.(string) License::get();
        $redirect .= '&siteurl='.License::get_home_url();

        $encodedredirect = strtr(base64_encode($redirect), '+/=', '-_~');

        return $auth_helper->getAuthUrl($this->_auth_url, $params, $encodedredirect);
    }

    public function start_sdk_client(?Account $account = null)
    {
        self::$_sdk_client = new Dropbox($this->get_dropbox_app($account), ['persistent_data_store' => new DatabasePersistentDataStore()]);

        return $this->get_sdk_client($account);
    }

    public function refresh_token(?Account $account = null)
    {
        $authorization = $account->get_authorization();
        $access_token = $authorization->get_access_token();

        if (!flock($authorization->get_token_file_handle(), LOCK_EX | LOCK_NB)) {
            Helpers::log_error('Wait till another process has renewed the Authorization Token', 'App', null, __LINE__);

            /*
             * If the file cannot be unlocked and the last time
             * it was modified was 1 minute, assume that
             * the previous process died and unlock the file manually
             */
            $requires_unlock = ((filemtime($authorization->get_token_location()) + 60) < time());

            // Temporarily workaround when flock is disabled. Can cause problems when plugin is used in multiple processes
            if (false !== strpos(ini_get('disable_functions'), 'flock')) {
                $requires_unlock = false;
            }

            if ($requires_unlock) {
                $authorization->unlock_token_file();
            }

            if (flock($authorization->get_token_file_handle(), LOCK_SH)) {
                clearstatcache();
                rewind($authorization->get_token_file_handle());
                $token = fread($authorization->get_token_file_handle(), filesize($authorization->get_token_location()));
                $decrypted_token = $authorization->decrypt($token);
                $token_object = unserialize($decrypted_token);
                Helpers::log_error('New Authorization Token has been received by another process.', 'App', null, __LINE__);
                self::$_sdk_client->setAccessToken($token_object);
                $authorization->unlock_token_file();

                return self::$_sdk_client;
            }
        }

        // Stop if we need to get a new AccessToken but somehow ended up without a refreshtoken
        $refresh_token = $access_token->getRefreshToken();

        if (empty($refresh_token)) {
            Helpers::log_error('No Refresh Token found during the renewing of the current token. This will stop the authorization completely.', 'App', null, __LINE__);
            $authorization->set_is_valid(false);
            $authorization->unlock_token_file();
            $this->revoke_token($account);

            return false;
        }

        // Refresh token
        try {
            $new_accesstoken = self::$_sdk_client->getAuthHelper()->refreshToken();

            // Store the new token
            $authorization->set_access_token($new_accesstoken);
            $authorization->unlock_token_file();
            self::get_sdk_client()->setAccessToken($new_accesstoken);

            if (false !== ($timestamp = wp_next_scheduled('outofthebox_lost_authorisation_notification', ['account_id' => $account->get_id()]))) {
                wp_unschedule_event($timestamp, 'outofthebox_lost_authorisation_notification', ['account_id' => $account->get_id()]);
            }
        } catch (\Exception $ex) {
            $authorization->set_is_valid(false);
            $authorization->unlock_token_file();
            Helpers::log_error('Cannot refresh Authorization Token.', 'App', null, __LINE__, $ex);

            if (!wp_next_scheduled('outofthebox_lost_authorisation_notification', ['account_id' => $account->get_id()])) {
                wp_schedule_event(time(), 'daily', 'outofthebox_lost_authorisation_notification', ['account_id' => $account->get_id()]);
            }

            Processor::reset_complete_cache();

            throw $ex;
        }

        return self::$_sdk_client;
    }

    public function create_access_token()
    {
        try {
            $code = sanitize_text_field($_REQUEST['code']);
            $state = sanitize_text_field($_REQUEST['state']);

            // Fetch the AccessToken
            $accessToken = self::get_sdk_client()->getAuthHelper()->getAccessToken($code, $state, $this->_auth_url);
            self::$_sdk_client->setAccessToken($accessToken);

            $account_data = self::get_sdk_client()->getCurrentAccount();
            $root_info = $account_data->getRootInfo();
            $root_namespace_id = $root_info['root_namespace_id'];

            $account = new Account($account_data->getAccountId(), $account_data->getDisplayName(), $account_data->getEmail(), $root_namespace_id, $account_data->getAccountType(), $account_data->getProfilePhotoUrl());
            $account->get_authorization()->set_access_token($accessToken);
            $account->get_authorization()->unlock_token_file();

            if ($account_data->emailIsVerified()) {
                $account->set_is_verified(true);
            }

            Accounts::instance()->add_account($account);

            delete_transient('outofthebox_'.$account->get_id().'_is_authorized');
        } catch (\Exception $ex) {
            Helpers::log_error('Cannot generate Access Token.', 'App', null, __LINE__, $ex);

            return new \WP_Error('broke', esc_html__('Error communicating with API:', 'wpcloudplugins').$ex->getMessage());
        }

        return true;
    }

    public function revoke_token(Account $account)
    {
        Helpers::log_error('Authorization for account revoked.', 'App', ['account_id' => $account->get_id(), 'account_email' => $account->get_email()], __LINE__);

        // Reset Personal Folders Back-End if the account it is pointing to is deleted
        $personal_folders_data = Settings::get('userfolder_backend_auto_root', []);
        if (is_array($personal_folders_data) && isset($personal_folders_data['account']) && $personal_folders_data['account'] === $account->get_id()) {
            Settings::save('userfolder_backend_auto_root', []);
        }

        Processor::reset_complete_cache();

        if (false !== ($timestamp = wp_next_scheduled('outofthebox_lost_authorisation_notification', ['account_id' => $account->get_id()]))) {
            wp_unschedule_event($timestamp, 'outofthebox_lost_authorisation_notification', ['account_id' => $account->get_id()]);
        }

        Core::instance()->send_lost_authorisation_notification($account->get_id());

        try {
            $this->get_sdk_client($account)->getAuthHelper()->revokeAccessToken();
            Accounts::instance()->remove_account($account->get_id());
        } catch (\Exception $ex) {
            Helpers::log_error('Authorization for account cannot be revoked.', 'App', ['account_id' => $account->get_id(), 'account_email' => $account->get_email()], __LINE__, $ex);
        }

        delete_transient('outofthebox_'.$account->get_id().'_is_authorized');
    }

    public function get_app_key()
    {
        if (empty($this->_app_key)) {
            $license = License::validate();
            $this->_app_key = $license['appdata']['key'] ?? null;
            $this->_app_token = $license['appdata']['token'] ?? null;

            if (!empty($own_key = Settings::get('dropbox_app_key'))) {
                $this->_app_key = $own_key;
                $this->_own_app = true;
            }
        }

        return $this->_app_key;
    }

    public function get_app_secret()
    {
        if (empty($this->_app_secret)) {
            $license = License::validate();
            $this->_app_secret = $license['appdata']['secret'] ?? null;

            if (!empty($own_secret = Settings::get('dropbox_app_secret'))) {
                $this->_app_secret = $own_secret;
                $this->_own_app = true;
            }
        }

        return $this->_app_secret;
    }

    /**
     * @param null|Account $account
     *
     * @return Dropbox
     */
    public static function get_sdk_client($account = null)
    {
        if (!empty($account)) {
            self::set_current_account($account);
        }

        return self::$_sdk_client;
    }

    /**
     * @param null|mixed $account
     *
     * @return DropboxApp
     */
    public function get_dropbox_app($account = null)
    {
        if (empty(self::$_sdk_client_app)) {
            if (!empty($account)) {
                self::$_sdk_client_app = new DropboxApp($this->get_app_key(), $this->get_app_secret(), $account->get_authorization()->get_access_token());
            } else {
                self::$_sdk_client_app = new DropboxApp($this->get_app_key(), $this->get_app_secret());
            }
        }

        return self::$_sdk_client_app;
    }

    public function get_root_namespace_id($id = '')
    {
        $use_app_folder = Settings::get('use_app_folder', 'No');
        if ('Yes' === $use_app_folder) {
            return '';
        }

        $use_team_folders = Settings::get('use_team_folders', 'No');

        if ('No' === $use_team_folders) {
            return $id;
        }

        $current_account = App::get_current_account();

        if (empty($current_account)) {
            return $id;
        }

        return $current_account->get_root_namespace_id();
    }

    /**
     * @return Accounts
     */
    public function get_accounts()
    {
        return Accounts::instance();
    }

    /**
     * @return Account
     */
    public static function get_current_account()
    {
        if (empty(self::$_current_account) && null !== Processor::instance()->get_shortcode()) {
            $account = Accounts::instance()->get_account_by_id(Processor::instance()->get_shortcode_option('account'));
            if (!empty($account)) {
                self::set_current_account($account);
            }
        }

        return self::$_current_account;
    }

    public static function set_current_account(Account $account)
    {
        if (self::$_current_account !== $account) {
            self::$_current_account = $account;
            Cache::instance_unload();

            if ($account->get_authorization()->has_access_token()) {
                if (empty(self::$_sdk_client)) {
                    self::instance();
                }

                self::$_sdk_client->setAccessToken($account->get_authorization()->get_access_token());
            }
        }

        return self::$_current_account;
    }

    public static function set_current_account_by_id(string $account_id)
    {
        $account = Accounts::instance()->get_account_by_id($account_id);

        if (empty($account)) {
            Helpers::log_error('Cannot use the requested account as it is not linked with the plugin. Plugin falls back to primary account.', 'App', ['account_id' => $account_id], __LINE__);
            $account = Accounts::instance()->get_primary_account();

            if (empty($account)) {
                self::$_current_account = null;

                return self::$_current_account;
            }
        }

        return self::set_current_account($account);
    }

    public static function clear_current_account()
    {
        self::$_current_account = null;
        Cache::instance_unload();
    }

    public function get_auth_uri()
    {
        return $this->_auth_url;
    }
}
