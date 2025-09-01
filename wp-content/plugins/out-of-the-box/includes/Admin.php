<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

use TheLion\OutoftheBox\Integrations\Slack;

defined('ABSPATH') || exit;

class Admin
{
    private $menu_id = 'OutoftheBox_settings';
    private $network_menu_id = 'OutoftheBox_network_settings';

    /**
     * Construct the plugin object.
     */
    public function __construct()
    {
        // Check if plugin can be used
        if (false === Core::can_run_plugin()) {
            add_action('admin_notices', [$this, 'get_admin_notice']);

            return;
        }

        // Only load code if needed
        if (!is_admin() && !wp_doing_ajax() && !wp_doing_cron()) {
            return;
        }

        // Add menu's
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('network_admin_menu', [$this, 'add_admin_network_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_menu_css']);

        // Ajax Calls
        add_action('wp_ajax_outofthebox-save-setting', [__NAMESPACE__.'\Settings', 'save_ajax_setting']);
        add_action('wp_ajax_outofthebox-check-account', [$this, 'check_account']);
        add_action('wp_ajax_outofthebox-reset-cache', [$this, 'start_process']);
        add_action('wp_ajax_outofthebox-reset-usage-limits', [$this, 'start_process']);
        add_action('wp_ajax_outofthebox-slack-test', [$this, 'start_process']);
        add_action('wp_ajax_outofthebox-factory-reset', [$this, 'start_process']);
        add_action('wp_ajax_outofthebox-reset-statistics', [$this, 'start_process']);
        add_action('wp_ajax_outofthebox-backup', [$this, 'start_process']);
        add_action('wp_ajax_outofthebox-revoke', [$this, 'start_process']);

        // Notices
        add_action('admin_notices', [$this, 'get_admin_notice_not_authorized']);
        add_action('admin_notices', [$this, 'get_admin_notice_not_activated']);

        // Restrictions in User Profiles
        add_action('init', [__NAMESPACE__.'\Restrictions', 'instance']);

        // Authorization callback
        add_action('admin_init', [$this, 'finish_cloud_authorization']);

        // Redirect to get started page after plugin activation
        add_action('admin_init', [$this, 'redirect_after_activation']);

        // add settings link on plugin page
        add_filter('plugin_action_links', [$this, 'add_plugin_actions_link'], 10, 2);
        add_filter('plugin_row_meta', [$this, 'add_settings_link'], 10, 2);
    }

    public static function load()
    {
        return new self();
    }

    public function start_process()
    {
        if (!isset($_REQUEST['action'])) {
            return false;
        }

        check_ajax_referer('outofthebox-admin-action', false, true);

        if (false === Helpers::check_user_role(Settings::get('permissions_edit_settings'))) {
            exit(1);
        }

        switch ($_REQUEST['action']) {
            case 'outofthebox-revoke':
                require_once ABSPATH.'wp-includes/pluggable.php';
                Processor::instance()->start_process();

                break;

            case 'outofthebox-factory-reset':
                Core::do_factory_reset();

                break;

            case 'outofthebox-slack-test':
                Slack::test_notification();

                break;

            case 'outofthebox-reset-cache':
                Processor::instance()->reset_complete_cache(true, !empty($_REQUEST['thumbnails']));

                break;

            case 'outofthebox-reset-statistics':
                Events::truncate_database();

                break;

            case 'outofthebox-backup':
                if ('export' === $_REQUEST['type']) {
                    Backup::do_export();
                }

                if ('import' === $_REQUEST['type']) {
                    Backup::do_import();
                }

                exit;

            case 'outofthebox-reset-usage-limits':
                Restrictions::reset_current_usage();

                exit;

            default:
                exit;
        }

        exit;
    }

    public function finish_cloud_authorization()
    {
        if (!isset($_REQUEST['action']) || 'outofthebox_authorization' !== $_REQUEST['action']) {
            return false;
        }

        if (!Helpers::check_user_role(Settings::get('permissions_edit_settings'))) {
            return false;
        }

        App::instance()->process_authorization();
    }

    /**
     * Redirect to the getting started page after plugin activation.
     */
    public function redirect_after_activation()
    {
        if (get_transient('outofthebox_admin_installation_redirect') && Helpers::check_user_role(Settings::get('permissions_edit_settings'))) {
            delete_transient('outofthebox_admin_installation_redirect');
            wp_redirect(admin_url('admin.php?page='.$this->menu_id.'_getting_started'));

            exit;
        }
    }

    /**
     * add a menu.
     */
    public function add_admin_menu()
    {
        // Add a page to manage this plugin's settings
        $menuadded = false;

        if (Helpers::check_user_role(Settings::get('permissions_edit_settings'))) {
            add_menu_page('Out-of-the-Box', 'Dropbox', 'read', $this->menu_id, [$this, 'load_settings_page'], OUTOFTHEBOX_ROOTPATH.'/css/images/dropbox_logo_small.png');
            $menuadded = true;
            add_submenu_page($this->menu_id, 'Out-of-the-Box - '.esc_html__('Settings'), esc_html__('Settings'), 'read', $this->menu_id, [$this, 'load_settings_page']);

            add_submenu_page($this->menu_id, esc_html__('Getting Started', 'wpcloudplugins'), esc_html__('Getting Started', 'wpcloudplugins'), 'read', $this->menu_id.'_getting_started', [$this, 'load_getting_started_page'], 3);
        }

        if (false === License::is_valid()) {
            return;
        }

        if (Helpers::check_user_role(Settings::get('permissions_add_shortcodes'))) {
            if (!$menuadded) {
                add_menu_page('Out-of-the-Box', 'Dropbox', 'read', $this->menu_id, [$this, 'load_modules_page'], OUTOFTHEBOX_ROOTPATH.'/css/images/dropbox_logo_small.png');
                add_submenu_page($this->menu_id, esc_html__('Modules', 'wpcloudplugins'), esc_html__('Modules', 'wpcloudplugins'), 'read', $this->menu_id, [$this, 'load_modules_page']);
                $menuadded = true;
            } else {
                add_submenu_page($this->menu_id, esc_html__('Modules', 'wpcloudplugins'), esc_html__('Modules', 'wpcloudplugins'), 'read', $this->menu_id.'_shortcodebuilder', [$this, 'load_modules_page']);
            }
        }

        if (Helpers::check_user_role(Settings::get('permissions_see_filebrowser'))) {
            if (!$menuadded) {
                add_menu_page('Out-of-the-Box', 'Dropbox', 'read', $this->menu_id, [$this, 'load_filebrowser_page'], OUTOFTHEBOX_ROOTPATH.'/css/images/dropbox_logo_small.png');
                add_submenu_page($this->menu_id, esc_html__('File Browser', 'wpcloudplugins'), esc_html__('File Browser', 'wpcloudplugins'), 'read', $this->menu_id, [$this, 'load_filebrowser_page']);
                $menuadded = true;
            } else {
                add_submenu_page($this->menu_id, esc_html__('File Browser', 'wpcloudplugins'), esc_html__('File Browser', 'wpcloudplugins'), 'read', $this->menu_id.'_filebrowser', [$this, 'load_filebrowser_page']);
            }
        }

        if (Helpers::check_user_role(Settings::get('permissions_link_users'))) {
            if (!$menuadded) {
                add_menu_page('Out-of-the-Box', 'Dropbox', 'read', $this->menu_id, [$this, 'load_linkusers_page'], OUTOFTHEBOX_ROOTPATH.'/css/images/dropbox_logo_small.png');
                add_submenu_page($this->menu_id, esc_html__('Personal Folders', 'wpcloudplugins'), esc_html__('Personal Folders', 'wpcloudplugins'), 'read', $this->menu_id, [$this, 'load_linkusers_page']);
                $menuadded = true;
            } else {
                add_submenu_page($this->menu_id, esc_html__('Personal Folders', 'wpcloudplugins'), esc_html__('Personal Folders', 'wpcloudplugins'), 'read', $this->menu_id.'_linkusers', [$this, 'load_linkusers_page']);
            }
        }

        if (Helpers::check_user_role(Settings::get('permissions_see_dashboard')) && ('Yes' === Settings::get('log_events'))) {
            if (!$menuadded) {
                add_menu_page('Out-of-the-Box', 'Dropbox', 'read', $this->menu_id, [$this, 'load_dashboard_page'], OUTOFTHEBOX_ROOTPATH.'/css/images/dropbox_logo_small.png');
                add_submenu_page($this->menu_id, esc_html__('Reports', 'wpcloudplugins'), esc_html__('Reports', 'wpcloudplugins'), 'read', $this->menu_id, [$this, 'load_dashboard_page']);
                $menuadded = true;
            } else {
                add_submenu_page($this->menu_id, esc_html__('Reports', 'wpcloudplugins'), esc_html__('Reports', 'wpcloudplugins'), 'read', $this->menu_id.'_dashboard', [$this, 'load_dashboard_page']);
            }
        }
    }

    public function add_admin_network_menu()
    {
        if (!is_plugin_active_for_network(OUTOFTHEBOX_SLUG)) {
            return;
        }

        add_menu_page('Out-of-the-Box', 'Dropbox', 'manage_options', $this->network_menu_id, [$this, 'load_settings_network_page'], OUTOFTHEBOX_ROOTPATH.'/css/images/dropbox_logo_small.png');

        add_submenu_page($this->network_menu_id, 'Out-of-the-Box - '.esc_html__('Settings'), esc_html__('Settings'), 'read', $this->network_menu_id, [$this, 'load_settings_network_page']);

        add_submenu_page($this->network_menu_id, esc_html__('Getting Started', 'wpcloudplugins'), esc_html__('Getting Started', 'wpcloudplugins'), 'read', $this->network_menu_id.'_getting_started', [$this, 'load_getting_started_page'], 3);

        if (Core::is_network_authorized()) {
            add_submenu_page($this->network_menu_id, esc_html__('File Browser', 'wpcloudplugins'), esc_html__('File Browser', 'wpcloudplugins'), 'read', $this->network_menu_id.'_filebrowser', [$this, 'load_filebrowser_page']);
        }
    }

    public function enqueue_menu_css()
    {
        wp_enqueue_style('OutoftheBox.Adminbar', OUTOFTHEBOX_ROOTPATH.'/css/admin-bar.min.css', [], OUTOFTHEBOX_VERSION);
    }

    public function load_settings_page()
    {
        if (!Helpers::check_user_role(Settings::get('permissions_edit_settings'))) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcloudplugins'));
        }

        Core::instance()->load_scripts();
        wp_enqueue_script('OutoftheBox.AdminSettings');

        Core::instance()->load_styles();
        wp_enqueue_style('WPCloudPlugins.AdminUI');

        wp_enqueue_media();

        // Build Whitelist for permission selection
        $vars = [
            'whitelist' => json_encode(Helpers::get_all_users_and_roles()),
            'ajax_url' => OUTOFTHEBOX_ADMIN_URL,
        ];

        wp_localize_script('OutoftheBox.AdminUI', 'WPCloudplugin_AdminUI_vars', $vars);

        include_once sprintf('%s/templates/admin/settings.php', OUTOFTHEBOX_ROOTDIR);
    }

    public function load_settings_network_page()
    {
        Core::instance()->load_scripts();
        wp_enqueue_script('OutoftheBox.AdminSettings');

        Core::instance()->load_styles();
        wp_enqueue_style('WPCloudPlugins.AdminUI');

        // Build Whitelist for permission selection
        $vars = [
            'whitelist' => json_encode(Helpers::get_all_users_and_roles()),
            'ajax_url' => OUTOFTHEBOX_ADMIN_URL,
        ];

        wp_localize_script('OutoftheBox.AdminUI', 'WPCloudplugin_AdminUI_vars', $vars);

        include_once sprintf('%s/templates/admin/settings_network.php', OUTOFTHEBOX_ROOTDIR);
    }

    public function load_filebrowser_page()
    {
        if (!Helpers::check_user_role(Settings::get('permissions_see_filebrowser'))) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcloudplugins'));
        }

        Core::instance()->load_scripts();
        wp_enqueue_script('OutoftheBox.AdminUI');

        Core::instance()->load_styles();
        wp_enqueue_style('WPCloudPlugins.AdminUI');

        include_once sprintf('%s/templates/admin/file_browser.php', OUTOFTHEBOX_ROOTDIR);
    }

    public function load_linkusers_page()
    {
        if (!Helpers::check_user_role(Settings::get('permissions_link_users'))) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcloudplugins'));
        }
        LinkUsers::render();
    }

    public function load_modules_page()
    {
        if (!Helpers::check_user_role(Settings::get('permissions_add_shortcodes'))) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcloudplugins'));
        }

        Core::instance()->load_scripts();
        wp_enqueue_script('OutoftheBox.AdminUI');

        Core::instance()->load_styles();
        wp_enqueue_style('WPCloudPlugins.AdminUI');

        include_once sprintf('%s/templates/admin/modules_standalone.php', OUTOFTHEBOX_ROOTDIR);
    }

    public function load_dashboard_page()
    {
        if (!Helpers::check_user_role(Settings::get('permissions_see_dashboard'))) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcloudplugins'));
        }

        Core::instance()->load_scripts();
        wp_enqueue_script('OutoftheBox.Dashboard');

        Core::instance()->load_styles();
        wp_enqueue_style('WPCloudPlugins.AdminUI');
        wp_dequeue_style('OutoftheBox');

        include_once sprintf('%s/templates/admin/event_dashboard.php', OUTOFTHEBOX_ROOTDIR);
    }

    public function load_getting_started_page()
    {
        if (!Helpers::check_user_role(Settings::get('permissions_edit_settings'))) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpcloudplugins'));
        }

        Core::instance()->load_scripts();
        wp_enqueue_script('OutoftheBox.Dashboard');

        Core::instance()->load_styles();
        wp_enqueue_style('WPCloudPlugins.AdminUI');
        wp_dequeue_style('OutoftheBox');

        include_once sprintf('%s/templates/admin/getting_started.php', OUTOFTHEBOX_ROOTDIR);
    }

    public function check_account()
    {
        // Check AJAX call
        check_ajax_referer('outofthebox-admin-action');

        // Get Account
        $account_id = \sanitize_text_field($_POST['account_id']);
        $account = Accounts::instance()->get_account_by_id($account_id);

        // Get App
        $app = App::instance();

        // Check Authorization
        $has_token = true === $account->get_authorization()->has_access_token();
        $transient_name = 'outofthebox_'.$account->get_id().'_is_authorized';
        $is_authorized = !empty(get_transient($transient_name));

        // Set return data
        $return = [
            'id' => $account->get_id(),
            'email' => $account->get_email(),
            'image' => $account->get_image(),
            'has_token' => $has_token,
            'is_authorized' => $is_authorized,
            'quota_used' => '',
            'quota_total' => '',
            'quota_used_percentage' => '',
            'auth_url' => $app->get_auth_url(),
            'error_message' => '',
            'error_details' => '',
        ];

        // Check if authorization token is available
        if (false === $has_token) {
            $return['error_message'] = esc_html__('Account is not linked to the plugin anymore.', 'wpcloudplugins').' '.esc_html__('Please re-authorize!', 'wpcloudplugins');
            echo \json_encode($return);

            exit;
        }

        // Re-Check authorization if needed
        if (false === $is_authorized) {
            try {
                App::set_current_account($account);
                Client::instance()->get_account_space_info();
                set_transient($transient_name, true, 5 * MINUTE_IN_SECONDS);
                $return['is_authorized'] = true;
            } catch (\Exception $ex) {
                Helpers::log_error('', 'API', null, __LINE__, $ex);

                App::get_current_account()->get_authorization()->set_is_valid(false);
                set_transient($transient_name, false, 5 * MINUTE_IN_SECONDS);
                $return['error_message'] = esc_html__('Account is not linked to the plugin anymore.', 'wpcloudplugins').' '.esc_html__('Please refresh the authorization or remove the account from the list.', 'wpcloudplugins');

                if ($app->has_plugin_own_app()) {
                    $return['error_message'] .= ' '.esc_html__('If the problem persists, fall back to the default App via the settings on the Advanced tab.', 'wpcloudplugins');
                }

                $return['error_details'] = '<pre>Error Details: '.$ex->getMessage().'</pre>';

                echo \json_encode($return);

                exit;
            }
        }

        try {
            $storageinfo = $account->get_storage_info();
            $return['quota_total'] = $storageinfo->get_quota_total();
            $return['quota_used'] = $storageinfo->get_quota_used();
            $return['quota_used_percentage'] = $storageinfo->get_quota_used_percentage_used();

            if (false !== ($timestamp = wp_next_scheduled('outofthebox_lost_authorisation_notification', ['account_id' => $account_id]))) {
                wp_unschedule_event($timestamp, 'outofthebox_lost_authorisation_notification', ['account_id' => $account_id]);
            }
        } catch (\Exception $ex) {
            $return['error_message'] = esc_html__('Cannot get account storage information.', 'wpcloudplugins');
            $return['error_details'] = '<p>Error Details:</p><pre>'.$ex->getMessage().'</pre>';
        }

        // Show if account is verified by Dropbox or not
        if (false === $account->is_verified()) {
            $return['error_message'] = sprintf(esc_html__('Your Account is not verified. Please take a look at %sthis Article%s for more information how to verify your account', 'wpcloudplugins'), '<a href="https://www.dropbox.com/help/sign-in/verify-email" target="blank">', '</a>');
            $return['error_details'] = '';
        }

        echo \json_encode($return);

        exit;
    }

    public function get_admin_notice()
    {
        // Check if cURL is present and its functions can be used
        $disabled_php_functions = explode(',', ini_get('disable_functions'));

        if (version_compare(PHP_VERSION, '7.4') < 0) {
            echo '<div id="message" class="error"><p><strong>Out-of-the-Box - Error: </strong>'.sprintf(esc_html__('You need at least PHP %s if you want to use this plugin', 'wpcloudplugins'), '7.4').'. '.
                esc_html__('You are using:', 'wpcloudplugins').' <u>'.phpversion().'</u></p></div>';
        } elseif (!function_exists('curl_init') || !function_exists('curl_exec') || !function_exists('curl_reset')) {
            echo '<div id="message" class="error"><p><strong>Out-of-the-Box - Error: </strong>'.
            esc_html__("We are not able to connect to the API as you don't have the cURL PHP extension installed", 'wpcloudplugins').'. '.
            esc_html__('Please enable or install the cURL extension on your server', 'wpcloudplugins').'. '.
            '</p></div>';
        } elseif (in_array('curl_init', $disabled_php_functions) || in_array('curl_exec', $disabled_php_functions)) {
            echo '<div id="message" class="error"><p><strong>Out-of-the-Box - Error: </strong>'.
            esc_html__('We are not able to connect to the API as cURL PHP functions curl_init and/or curl_exec are on the list of disabled functions in your PHP configuration.', 'wpcloudplugins').' '.
            esc_html__('To resolve this, please remove those functions from the "disabled_functions" PHP configuration.', 'wpcloudplugins').' '.
            '</p></div>';
        } elseif (!extension_loaded('mbstring')) {
            echo '<div id="message" class="error"><p><strong>Out-of-the-Box - Error: </strong>'.
            sprintf(
                esc_html__("You don't have the %s mbstring PHP extension%s enabled in your PHP configuration/package", 'wpcloudplugins'),
                '<a href="https://www.php.net/manual/en/mbstring.installation.php" target="_blank"><strong>',
                '</strong></a>'
            ).'. '.
            esc_html__('Multibyte string is required to handle (e.g.) non-Latin file names.', 'wpcloudplugins').
            esc_html__(' In most cases this can be enabled via your server configuration panel. Otherwise, ask your web host how to enable this extension for your server.', 'wpcloudplugins').
            '</p></div>';
        } elseif (!file_exists(OUTOFTHEBOX_CACHEDIR) || !is_writable(OUTOFTHEBOX_CACHEDIR)) {
            echo '<div id="message" class="error"><p><strong>Out-of-the-Box - Error: </strong>'.sprintf(esc_html__('Cannot create the cache directory %s, or it is not writable', 'wpcloudplugins'), '<code>'.OUTOFTHEBOX_CACHEDIR.'</code>').'. '.
            sprintf(esc_html__('Please check if the directory exists on your server and has %s writing permissions %s', 'wpcloudplugins'), '<a href="https://codex.wordpress.org/Changing_File_Permissions" target="_blank">', '</a>').'</p></div>';
        }

        global $is_apache;

        if ($is_apache && !file_exists(OUTOFTHEBOX_CACHEDIR.'/.htaccess') && !function_exists('is_wpe')) {
            echo '<div id="message" class="error"><p><strong>Dropbox - Error: </strong>'.sprintf(esc_html__('Cannot find .htaccess file in cache directory %s', 'wpcloudplugins'), '<code>'.OUTOFTHEBOX_CACHEDIR.'</code>').'. '.
            sprintf(esc_html__('Please check if the file exists on your server or copy it from the %s folder', 'wpcloudplugins'), OUTOFTHEBOX_ROOTDIR.'/cache').'</p></div>';
        }

        // Notice for not meeting TLS/SSL Requirements.
        if (!defined('CURL_SSLVERSION_TLSv1_2') && !defined('CURL_SSLVERSION_TLSv1_3')) {
            echo '<div id="message" class="error"><p><strong>Dropbox - Error: </strong>'.esc_html__('The cURL library of your server does not support TLS1.2 or TLS1.3 (CURL_SSLVERSION_TLSv1_2 | CURL_SSLVERSION_TLSv1_3. This is required by the API to create a secure connection. Please contact your webhost and ask them to upgrade the curl library and/or enable TLS1.2 support or higher for this library', 'wpcloudplugins').'. '.
                '</p></div>';
        }
    }

    public function get_admin_notice_not_authorized()
    {
        global $pagenow;
        if (('index.php' == $pagenow || 'plugins.php' == $pagenow) && (current_user_can('manage_options') || current_user_can('edit_theme_options'))) {
            $location = get_admin_url(null, 'admin.php?page=OutoftheBox_settings');

            $accounts = Accounts::instance()->list_accounts();

            if (empty($accounts)) {
                echo '<div id="message" class="error"><p><span class="dashicons dashicons-warning"></span>&nbsp;<strong>Dropbox: </strong>'.sprintf(esc_html__("The plugin isn't currently connected to a %s account. Add an account or disable the plugin if you are not using it.", 'wpcloudplugins'), 'Dropbox').'</p>'.
                    "<p><a href='{$location}' class='button-primary'>❱❱❱ &nbsp;".esc_html__('Connect your account!', 'wpcloudplugins').'</a></p></div>';

                return;
            }

            $accounts_that_require_attention = [];
            foreach ($accounts as $account_id => $account) {
                if (false === $account->get_authorization()->has_access_token() || (false !== wp_next_scheduled('outofthebox_lost_authorisation_notification', ['account_id' => $account_id]))) {
                    $accounts_that_require_attention[] = $account->get_email();
                }
            }

            if (!empty($accounts_that_require_attention)) {
                echo '<div id="message" class="error"><p><span class="dashicons dashicons-warning"></span>&nbsp;<strong>Dropbox: </strong>'.sprintf(esc_html__("The plugin isn't longer linked to the account(s): %s", 'wpcloudplugins'), '<strong>'.implode('</strong>, <strong>', $accounts_that_require_attention).'</strong>').'.</p>'.
                    "<p><a href='{$location}' class='button-primary'>❱❱❱ &nbsp;".esc_html__('Refresh the authorization!', 'wpcloudplugins').'</a></p></div>';
            }
        }
    }

    public function get_admin_notice_not_activated()
    {
        global $pagenow;

        if ('index.php' != $pagenow && 'plugins.php' != $pagenow) {
            return;
        }

        if (License::is_valid()) {
            return;
        }

        if (current_user_can('manage_options') || current_user_can('edit_theme_options')) {
            $location = get_admin_url(null, 'admin.php?page=OutoftheBox_settings'); ?>
<div id="message" class="error">
    <img src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/wpcp-logo-dark.svg" height="84" width="84" class="alignleft" style="padding: 20px 20px 20px 10px;" alt="">
    <h3>Out-of-the-Box: <?php esc_html_e('Inactive License', 'wpcloudplugins'); ?></h3>
    <p><?php
                                    esc_html_e('The plugin is not yet activated. This means you’re missing out on updates and support! Please activate the plugin in order to start using the plugin, or disable the plugin.', 'wpcloudplugins'); ?>
    </p>
    <p>
        <a href='<?php echo $location; ?>' class='button-primary'>❱❱❱ &nbsp;<?php esc_html_e('Activate the plugin!', 'wpcloudplugins'); ?></a>
        &nbsp;
        <a href="https://1.envato.market/vLjyO" rel="noopener" target="_blank" class="button button-secondary"><?php esc_html_e('Buy License', 'wpcloudplugins'); ?></a>
    </p>
</div>
<?php
        }
    }

    public function add_plugin_actions_link($links_array, $plugin_file_name)
    {
        if (OUTOFTHEBOX_SLUG == $plugin_file_name && !is_network_admin()) {
            array_unshift($links_array, sprintf('<a href="admin.php?page=%s">%s</a>', 'OutoftheBox_settings', esc_html__('Settings', 'wpcloudplugins')));
        }

        return $links_array;
    }

    public function add_settings_link($links_array, $plugin_file_name)
    {
        if (OUTOFTHEBOX_SLUG == $plugin_file_name && !is_network_admin()) {
            return array_merge(
                $links_array,
                [sprintf('<a href="'.OUTOFTHEBOX_ROOTPATH.'/_documentation/index.html" target="_blank">%s</a>', esc_html__('Docs', 'wpcloudplugins'))],
                [sprintf('<a href="https://florisdeleeuwnl.zendesk.com/hc/en-us" target="_blank">%s</a>', esc_html__('Support', 'wpcloudplugins'))]
            );
        }

        return $links_array;
    }

    public function get_system_information()
    {
        // Figure out cURL version, if installed.
        $curl_version = '';
        if (function_exists('curl_version')) {
            $curl_version = curl_version();
            $curl_version = $curl_version['version'].', '.$curl_version['ssl_version'];
        } elseif (extension_loaded('curl')) {
            $curl_version = esc_html__('cURL installed but unable to retrieve version.', 'wpcloudplugins');
        }

        // WP memory limit.
        $wp_memory_limit = Helpers::return_bytes(WP_MEMORY_LIMIT);
        if (function_exists('memory_get_usage')) {
            $wp_memory_limit = max($wp_memory_limit, Helpers::return_bytes(@ini_get('memory_limit')));
        }

        // Return all environment info. Described by JSON Schema.
        $environment = [
            'home_url' => get_option('home'),
            'license_url' => urldecode(License::get_home_url()),
            'site_url' => get_option('siteurl'),
            'version' => OUTOFTHEBOX_VERSION,
            'cache_directory' => OUTOFTHEBOX_CACHEDIR,
            'cache_directory_writable' => (bool) @fopen(OUTOFTHEBOX_CACHEDIR.'/test-cache.log', 'a'),
            'wp_version' => get_bloginfo('version'),
            'wp_multisite' => is_multisite(),
            'wp_memory_limit' => $wp_memory_limit,
            'wp_debug_mode' => (defined('WP_DEBUG') && WP_DEBUG),
            'wp_cron' => !(defined('DISABLE_WP_CRON') && DISABLE_WP_CRON),
            'language' => get_locale(),
            'external_object_cache' => wp_using_ext_object_cache(),
            'server_info' => isset($_SERVER['SERVER_SOFTWARE']) ? wp_unslash($_SERVER['SERVER_SOFTWARE']) : '',
            'php_version' => phpversion(),
            'php_post_max_size' => Helpers::return_bytes(ini_get('post_max_size')),
            'php_max_execution_time' => ini_get('max_execution_time'),
            'php_max_input_vars' => ini_get('max_input_vars'),
            'curl_version' => $curl_version,
            'max_upload_size' => wp_max_upload_size(),
            'default_timezone' => date_default_timezone_get(),
            'curl_enabled' => (function_exists('curl_init') && function_exists('curl_exec')),
            'allow_url_fopen' => ini_get('allow_url_fopen'),
            'gzip_compression_enabled' => extension_loaded('zlib'),
            'mbstring_enabled' => extension_loaded('mbstring'),
            'flock' => (false === strpos(ini_get('disable_functions'), 'flock')),
            'secure_connection' => is_ssl(),
            'openssl_encrypt' => (function_exists('openssl_encrypt') && in_array('aes-256-cbc', openssl_get_cipher_methods())),
            'hide_errors' => !(defined('WP_DEBUG') && defined('WP_DEBUG_DISPLAY') && WP_DEBUG && WP_DEBUG_DISPLAY) || 0 === intval(ini_get('display_errors')),
            'gravity_forms' => class_exists('GFForms'),
            'formidableforms' => class_exists('FrmAppHelper'),
            'gravity_pdf' => class_exists('GFPDF_Core'),
            'gravity_wpdatatables' => class_exists('WPDataTable'),
            'elementor' => defined('ELEMENTOR_VERSION'),
            'wpforms' => defined('WPFORMS_VERSION'),
            'fluentforms' => defined('FLUENTFORM_VERSION'),
            'contact_form_7' => defined('WPCF7_PLUGIN'),
            'acf' => class_exists('ACF'),
            'beaver_builder' => class_exists('FLBuilder'),
            'divi_page_builder' => defined('ET_BUILDER_THEME'),
            'woocommerce' => class_exists('WC_Integration'),
            'woocommerce_product_documents' => class_exists('WC_Product_Documents'),
            'edd' => defined('EDD_PLUGIN_FILE'),
            'ninjaforms' => class_exists('Ninja_Forms'),
        ];

        // Get Theme info
        $active_theme = wp_get_theme();

        // Get parent theme info if this theme is a child theme, otherwise
        // pass empty info in the response.
        if (is_child_theme()) {
            $parent_theme = wp_get_theme($active_theme->template);
            $parent_theme_info = [
                'parent_name' => $parent_theme->name,
                'parent_version' => $parent_theme->version,
                'parent_author_url' => $parent_theme->{'Author URI'},
            ];
        } else {
            $parent_theme_info = [
                'parent_name' => '',
                'parent_version' => '',
                'parent_version_latest' => '',
                'parent_author_url' => '',
            ];
        }

        $active_theme_info = [
            'name' => $active_theme->name,
            'version' => $active_theme->version,
            'author_url' => esc_url_raw($active_theme->{'Author URI'}),
            'is_child_theme' => is_child_theme(),
        ];

        $theme = array_merge($active_theme_info, $parent_theme_info);

        // Get Active plugins
        require_once ABSPATH.'wp-admin/includes/plugin.php';

        if (!function_exists('get_plugin_data')) {
            return [];
        }

        $active_plugins = (array) get_option('active_plugins', []);
        if (is_multisite()) {
            $network_activated_plugins = array_keys(get_site_option('active_sitewide_plugins', []));
            $active_plugins = array_merge($active_plugins, $network_activated_plugins);
        }

        $active_plugins_data = [];

        foreach ($active_plugins as $plugin) {
            $data = get_plugin_data(WP_PLUGIN_DIR.'/'.$plugin);
            $active_plugins_data[] = [
                'plugin' => $plugin,
                'name' => $data['Name'],
                'version' => $data['Version'],
                'url' => $data['PluginURI'],
                'author_name' => $data['AuthorName'],
                'author_url' => esc_url_raw($data['AuthorURI']),
                'network_activated' => $data['Network'],
            ];
        }

        include_once sprintf('%s/templates/admin/system_information.php', OUTOFTHEBOX_ROOTDIR);
    }
}
