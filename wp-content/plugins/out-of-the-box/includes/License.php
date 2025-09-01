<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

defined('ABSPATH') || exit;

class License
{
    public static $license_code;

    public static function init()
    {
        // Health Check test
        add_filter('site_status_tests', [__CLASS__, 'add_health_tests']);

        add_action('wp_ajax_outofthebox-license', [__CLASS__, 'ajax_call']);

        if (isset($_REQUEST['purchase_code'], $_REQUEST['plugin_id']) && (Core::$plugin_id == $_REQUEST['plugin_id'])) {
            self::save($_REQUEST['purchase_code']);
            echo '<script type="text/javascript">localStorage.setItem("wpcp_refreshParent", "true"); window.close();</script>';

            exit;
        }
    }

    public static function save($license_code)
    {
        $license_code = sanitize_key($license_code);
        Settings::save('purchase_code', $license_code);

        if (self::is_global()) {
            update_site_option('outofthebox_purchaseid', $license_code);
        }

        delete_site_option('wpcp_license_'.$license_code);
        wp_cache_delete('wpcp_license_'.$license_code, 'site-options');
        delete_transient('wpcp_license_'.$license_code, '_error');

        self::$license_code = trim(apply_filters('outofthebox_purchasecode', $license_code ?? ''));
    }

    public static function get()
    {
        if (null !== self::$license_code) {
            return self::$license_code;
        }

        $license_code = Settings::get('purchase_code');

        if (self::is_global()) {
            $site_license_code = get_site_option('outofthebox_purchaseid');

            if (!empty($site_license_code)) {
                $license_code = $site_license_code;
            }
        }

        self::$license_code = trim(apply_filters('outofthebox_purchasecode', $license_code ?? ''));

        return self::$license_code;
    }

    public static function validate($force = false, $license_code = null, $activate_if_possible = false)
    {
        $license_code = empty($license_code) ? self::get() : $license_code;

        $cached_data = get_site_option('wpcp_license_'.$license_code);

        if (false === $force && false === empty($cached_data) && !empty($cached_data['license_data']['appdata']['key']) && $cached_data['expires'] > time() && isset($cached_data['version']) && OUTOFTHEBOX_VERSION === $cached_data['version']) {
            return $cached_data['license_data'];
        }

        if (true === get_transient('wpcp_license_'.$license_code, '_error') && false === $force) {
            return false;
        }

        $response = wp_remote_get('https://www.wpcloudplugins.com/updates_v2/?action=get_license&slug=out-of-the-box&purchase_code='.$license_code.'&plugin_id='.Core::$plugin_id.'&force='.$force.'&activate='.$activate_if_possible.'&installed_version='.OUTOFTHEBOX_VERSION.'&siteurl='.self::get_home_url());
        $response_code = wp_remote_retrieve_response_code($response);

        if (empty($response_code)) {
            if (is_wp_error($response) && !defined('LICENSE_ERROR')) {
                $error_msg = 'Cannot validate the License. Check <strong>[Tools] -> [Site Health]</strong> for communication problems if the problem persists. <small>'.$response->get_error_message().'</small>';
                define('LICENSE_ERROR', $error_msg);

                Helpers::log_error('Cannot validate the License', 'License', ['response' => $response->get_error_message()], __LINE__);
            }

            set_transient('wpcp_license_'.$license_code, '_error', true, 60);

            return false;
        }

        if (in_array($response_code, [401, 402, 403, 406])) {
            $account = Accounts::instance()->get_primary_account();
            if (!empty($account)) {
                Core::instance()->send_lost_authorisation_notification($account->get_id());
            }

            self::_revoke();

            Helpers::log_error('The License is revoked', 'License', null, __LINE__);

            $result = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($result['message']) && !defined('LICENSE_ERROR')) {
                Helpers::log_error('Cannot validate the License', 'License', ['response' => $result['message']], __LINE__);
                define('LICENSE_ERROR', $result['message']);
            }

            update_site_option('wpcp_license_'.$license_code, ['license_data' => false, 'version' => \OUTOFTHEBOX_VERSION, 'expires' => time() + YEAR_IN_SECONDS]);

            set_transient('wpcp_license_'.$license_code, '_error', true, 60);

            return false;
        }

        delete_transient('wpcp_license_'.$license_code, '_error');

        $license_data = json_decode(wp_remote_retrieve_body($response), true);
        update_site_option('wpcp_license_'.$license_code, ['license_data' => $license_data, 'version' => \OUTOFTHEBOX_VERSION, 'expires' => time() + DAY_IN_SECONDS]);

        return $license_data;
    }

    public static function is_valid()
    {
        $license_code = self::get();

        if (empty($license_code)) {
            return false;
        }

        return false !== self::validate(false, $license_code);
    }

    public static function ajax_call()
    {
        // Check AJAX call
        check_ajax_referer('outofthebox-admin-action');

        $license_code = isset($_POST['license_code']) ? sanitize_key($_POST['license_code']) : self::get();

        $return = [
            'license_code' => $license_code,
            'valid' => false,
            'support_package' => false,
            'error_message' => '',
            'data' => [],
        ];

        if (isset($_POST['type']) && 'deactivate' === $_POST['type']) {
            self::_revoke();
            self::save('');
            echo \json_encode($return);

            exit;
        }

        $license_data = self::validate(true, $license_code, (isset($_POST['type']) && 'activate' === $_POST['type']) ? true : false);

        if (false === $license_data) {
            $return['error_message'] = defined('LICENSE_ERROR') ? LICENSE_ERROR : 'Cannot validate the License. Check the code or try to activate via Envato Market.<br/> <small>Check <strong>[Tools] -> [Site Health]</strong> for communication problems if the problem persists.</small>';

            echo \json_encode($return);

            exit;
        }

        if (isset($_POST['type']) && 'activate' === $_POST['type']) {
            self::save($license_code);
        }

        $return['valid'] = true;
        $return['data'] = $license_data;
        $supported_until_str = isset($license_data['supported_until']) ? date_i18n(get_option('date_format'), strtotime($license_data['supported_until'])) : esc_html__('today', 'wpcloudplugins');
        $return['supported_until_str'] = sprintf(esc_html__('Support till %s', 'wpcloudplugins'), $supported_until_str);
        unset($return['data']['appdata']);

        if (isset($license_data['supported_until']) && $license_data['supported_until'] < date('c')) {
            $return['error_message'] = sprintf(esc_html__('The support period for this license has expired on %s.', 'wpcloudplugins'), $supported_until_str);
        } else {
            $return['support_package'] = true;
        }

        echo \json_encode($return);

        exit;
    }

    public static function reset()
    {
        $license_code = self::get();

        if (empty($license_code)) {
            return false;
        }

        delete_site_option('wpcp_license_'.$license_code);
        wp_cache_delete('wpcp_license_'.$license_code, 'site-options');
    }

    public static function add_health_tests($tests)
    {
        $tests['direct']['wpcp_license_server'] = [
            'label' => __('Communication WP Cloud Plugin license server'),
            'test' => [__CLASS__, 'test_license_server'],
        ];

        return $tests;
    }

    public static function test_license_server()
    {
        $result = [
            'label' => __('The WP Cloud Plugins are able to communicate with their licence server.', 'wpcloudplugins'),
            'status' => 'good',
            'badge' => [
                'label' => 'WP Cloud Plugins',
                'color' => 'green',
            ],
            'description' => sprintf(
                '<p>%s</p>',
                __('To use the WP Cloud Plugins, you need a valid licence. This licence is validated from time to time using the licence server.', 'wpcloudplugins')
            ),
            'actions' => '',
            'test' => 'wpcp_license_server',
        ];

        $error = false;

        try {
            $response = wp_remote_get('https://www.wpcloudplugins.com/updates_v2/');
        } catch (\Exception $ex) {
            $error = true;
            $message = $ex->getMessage();
        }

        if (is_wp_error($response)) {
            $error = true;
            $message = $response->get_error_message();
        }

        if ($error) {
            $result['status'] = 'critical';
            $result['label'] = __('WP Cloud Plugins cannot communicate with the licence server', 'wpcloudplugins');
            $result['badge']['color'] = 'red';
            $result['description'] = sprintf(
                '<p>%s</p><h3>Error information</h3><code>%s</code>',
                __('Your website cannot establish a secure connection with the licensing server. This will cause their plugins to stop working because the licence cannot be validated.', 'wpcloudplugins'),
                htmlentities($message, ENT_QUOTES | ENT_HTML401)
            );
            $result['actions'] = sprintf(
                '<p><a href="%s" target="_blank">%s <span class="dashicons dashicons-external"> </span></a> - <a href="%s" target="_blank">%s <span class="dashicons dashicons-external"</a></p>',
                esc_url('https://www.google.com/search?q=WordPress+wp_remote_get+'.urlencode(htmlentities($message, ENT_QUOTES | ENT_HTML401))),
                __('Find a solution'),
                esc_url('https://florisdeleeuwnl.zendesk.com/hc/en-us/articles/201845893'),
                __('Contact Support')
            );
        }

        return $result;
    }

    public static function get_home_url()
    {
        if (!function_exists('is_plugin_active_for_network')) {
            require_once ABSPATH.'/wp-admin/includes/plugin.php';
        }

        // Load the home url as set in the WordPress Database without applying filters.
        $home_url = self::get_option_without_filters('option_home', function () {
            return _config_wp_home(get_option('home'));
        });
        if (empty($home_url)) {
            $home_url = get_bloginfo('url');
        }

        $home_url = set_url_scheme($home_url, 'admin');

        if (false === strpos($home_url, '?')) {
            $home_url .= '?';
        }

        if (false === is_multisite()) {
            return urlencode($home_url).'&multisite=0&network_enabled=0&network_wide=0';
        }
        if (!is_plugin_active_for_network(OUTOFTHEBOX_SLUG)) {
            return urlencode($home_url).'&multisite=1&network_enabled=0&network_wide=0';
        }

        if (self::is_global()) {
            // Load the network home url as set in the WordPress Database without applying filters.
            $home_url = self::get_option_without_filters('network_home_url', function () {
                return network_home_url();
            });

            if (empty($home_url)) {
                $home_url = get_bloginfo('url');
            }

            if (false === strpos($home_url, '?')) {
                $home_url .= '?';
            }

            return urlencode($home_url).'&multisite=1&network_enabled=1&network_wide=1';
        }

        return urlencode($home_url).'&multisite=1&network_enabled=1&network_wide=0';
    }

    /**
     * Load an option as set in the WordPress Database without applying filters.
     *
     * @param string   $hook     filter name
     * @param callable $callback function execited while filter disabled
     *
     * @return mixed value returned by $callback
     */
    public static function get_option_without_filters($hook, $callback)
    {
        global $wp_filter;

        $wp_hook = null;
        // Remove and cache the filter
        if (isset($wp_filter[$hook]) && $wp_filter[$hook] instanceof \WP_Hook) {
            $wp_hook = $wp_filter[$hook];
            unset($wp_filter[$hook]);
        }

        $retval = call_user_func($callback);

        // Add back the filter
        if ($wp_hook instanceof \WP_Hook) {
            $wp_filter[$hook] = $wp_hook;
        }

        return $retval;
    }

    public static function requires_single()
    {
        if (!function_exists('is_plugin_active_for_network')) {
            require_once ABSPATH.'/wp-admin/includes/plugin.php';
        }

        if (false === is_multisite()) {
            return true;
        }
        if (!is_plugin_active_for_network(OUTOFTHEBOX_SLUG)) {
            return true;
        }

        if (self::is_global()) {
            return true;
        }

        return false;
    }

    public static function is_global()
    {
        if (!function_exists('is_plugin_active_for_network')) {
            require_once ABSPATH.'/wp-admin/includes/plugin.php';
        }

        $network_settings = get_site_option(Settings::$db_network_key, []);
        $network_wide = isset($network_settings['network_wide']) && ('Yes' === $network_settings['network_wide']);

        return is_multisite() && is_plugin_active_for_network(OUTOFTHEBOX_SLUG) && $network_wide;
    }

    public static function mask_code($license_code)
    {
        // Split the UUID into its components
        $parts = explode('-', $license_code);

        // Mask the middle parts
        for ($i = 1; $i < count($parts) - 1; ++$i) {
            $parts[$i] = str_repeat('*', strlen($parts[$i]));
        }

        // Join the parts back together
        return implode('-', $parts);
    }

    private static function _revoke()
    {
        $license_code = empty($license_code) ? self::get() : $license_code;

        $cached_data = get_site_option('wpcp_license_'.$license_code);

        if (!empty($cached_data) && isset($cached_data['license_data']['secret'])) {
            $secret = $cached_data['license_data']['secret'];
            wp_remote_get('https://www.wpcloudplugins.com/updates_v2/?action=deactivate_license&secret='.$secret.'&slug=out-of-the-box&purchase_code='.$license_code.'&plugin_id='.Core::$plugin_id.'&installed_version='.\OUTOFTHEBOX_VERSION.'&siteurl='.self::get_home_url());
        }

        self::reset();

        // Remove Cache Files
        require_once ABSPATH.'wp-admin/includes/class-wp-filesystem-base.php';

        require_once ABSPATH.'wp-admin/includes/class-wp-filesystem-direct.php';

        $wp_file_system = new \WP_Filesystem_Direct(false);

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(OUTOFTHEBOX_CACHEDIR, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            if ('.htaccess' === $path->getFilename()) {
                continue;
            }

            try {
                $wp_file_system->delete($path->getPathname(), true);
            } catch (\Exception $ex) {
                continue;
            }
        }

        Processor::reset_complete_cache();
    }
}
