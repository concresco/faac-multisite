<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.11
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

defined('ABSPATH') || exit;

class Settings
{
    public static $db_key = 'out_of_the_box_settings';
    public static $db_network_key = 'outfthebox_network_settings';

    public static $settings;

    /**
     * The single instance of the class.
     *
     * @var Settings
     */
    protected static $_instance;

    public function __construct()
    {
        // Load settings from DB
        $settings = get_option(self::$db_key, self::get_defaults());

        // Update settings if needed
        $settings = Update::update_database($settings);

        if (Core::is_network_authorized()) {
            $settings = array_merge($settings, get_site_option(self::$db_network_key, []));
        }

        self::$settings = $settings;
    }

    /**
     * Settings Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return Settings - Settings instance
     *
     * @static
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static function reload()
    {
        self::$_instance = new self();

        return self::$_instance;
    }

    public static function get($key, $default = null)
    {
        if (1 == preg_match('/(.*?)\[(.*?)\]/', $key, $keys)) {
            $setting = self::get($keys[1]);

            if (empty($setting) || empty($setting[$keys[2]])) {
                return $default;
            }

            return $setting[$keys[2]];
        }

        return array_key_exists($key, self::instance()::$settings) ? self::instance()::$settings[$key] : $default;
    }

    public static function save($key, $value)
    {
        self::instance()::$settings[$key] = $value;

        return update_option(self::$db_key, self::instance()::$settings);
    }

    public static function save_for_network($key, $value)
    {
        $network_settings = get_site_option(self::$db_network_key, []);
        $network_settings[$key] = $value;

        return update_site_option(self::$db_network_key, $network_settings);
    }

    /**
     * Save settings configurated via Admin Menu.
     */
    public static function save_ajax_setting()
    {
        // Check AJAX call
        check_ajax_referer('outofthebox-admin-action');

        // Get setting data
        $setting_key = $_REQUEST['key'];
        $new_value = wp_unslash($_REQUEST['value']);
        $is_network_setting = (true == $_REQUEST['network']);

        if ($is_network_setting) {
            $current_settings = $old_settings = get_site_option(self::$db_network_key, []);
            $old_value = $old_settings[$setting_key] ?? null;
        } else {
            $old_value = self::get($setting_key);
            $current_settings = $old_settings = self::$settings;
        }

        // Process setting value
        if ('true' === $new_value) {
            $new_value = 'Yes';
        } elseif ('false' === $new_value) {
            $new_value = 'No';
        }

        if (is_string($new_value)) {
            $new_value = trim($new_value);
        }

        // Store the ID of fields using tagify data
        if (is_string($new_value) && false !== strpos($new_value, '[{')) {
            $new_value = self::_format_tagify_data($new_value);
        }

        if ('dropbox_app_own' === $setting_key && 'No' === $new_value) {
            $current_settings['dropbox_app_key'] = '';
            $current_settings['dropbox_app_secret'] = '';
            $return['dropbox_app_key'] = '';
            $return['dropbox_app_secret'] = '';
        }

        if ('webhook_active' === $setting_key && 'No' === $new_value) {
            $current_settings['webhook_endpoint_url'] = '';
            $return['webhook_endpoint_url'] = '';
        }

        if ('icon_set' === $setting_key) {
            $new_value = rtrim($new_value, '/').'/';
        }

        if (in_array($setting_key, ['icon_set', 'mask_account_id']) && $new_value !== $old_value) {
            Processor::reset_complete_cache();
        }

        if ('network_wide' === $setting_key) {
            $return['reload'] = true;
        }

        if (1 == preg_match('/(.*?)\[(.*?)\]/', $setting_key, $setting_keys)) {
            $current_settings[$setting_keys[1]][$setting_keys[2]] = $new_value;
        } else {
            $current_settings[$setting_key] = $new_value;
        }

        // Save new settings
        if ($new_value === $old_value || empty($new_value) && empty($old_value)) {
            // do nothing
            $return[$setting_key] = $new_value;
            echo json_encode($return, JSON_PRETTY_PRINT);

            exit;
        }

        if ($is_network_setting) {
            if ('purchase_code' === $setting_key) {
                $saved = update_site_option('outofthebox_purchaseid', $new_value);
            } else {
                $saved = update_site_option(self::$db_network_key, $current_settings);
            }
        } else {
            $saved = update_option(self::$db_key, $current_settings);
        }

        if ($saved) {
            self::reload();
            $return[$setting_key] = $new_value;
        } else {
            http_response_code(500);

            exit('-1');
        }

        if (false === $is_network_setting) {
            // Update Cron Job settings
            if (self::get('event_summary') !== $old_settings['event_summary'] || self::get('event_summary_period') !== $old_settings['event_summary_period']) {
                $summary_cron_job = wp_next_scheduled('outofthebox_send_event_summary');
                if (false !== $summary_cron_job) {
                    wp_unschedule_event($summary_cron_job, 'outofthebox_send_event_summary');
                }
            }
            // If needed, a new cron job will be set when the plugin initiates again

            // Keep account data
            if (empty(Settings::get('accounts'))) {
                self::save('accounts', $old_settings['accounts'] ?? []);
            }
        }

        echo json_encode($return, JSON_PRETTY_PRINT);

        exit;
    }

    /**
     * Add settings to Database when plugin is activated.
     */
    public static function add_settings_to_db()
    {
        add_option(self::$db_key, self::get_defaults());
    }

    /**
     * Default for plugin settings.
     */
    public static function get_defaults()
    {
        return [
            'accounts' => [],
            'purchase_code' => '',
            'dropbox_app_key' => '',
            'dropbox_app_secret' => '',
            'lostauthorization_notification' => '',
            'google_analytics' => 'No',
            'gallery_navbar_onhover' => 'Yes',
            'loadimages' => 'thumbnail',
            'lightbox_skin' => 'metro-black',
            'lightbox_path' => 'horizontal',
            'lightbox_rightclick' => 'No',
            'lightbox_thumbnails' => 'Yes',
            'lightbox_showcaption' => 'always',
            'lightbox_showheader' => 'always',
            'lightbox_thumbnailbar' => 'hover',
            'mediaplayer_skin' => 'Default_Skin',
            'mediaplayer_load_native_mediaelement' => 'No',
            'mediaplayer_ads_tagurl' => '',
            'mediaplayer_ads_hide_role' => ['users'],
            'mediaplayer_ads_skipable' => 'Yes',
            'mediaplayer_ads_skipable_after' => '5',
            'userfolder_name' => '%user_login% (%user_email%)',
            'userfolder_name_guest_prefix' => 'Guests - ',
            'userfolder_oncreation' => 'Yes',
            'userfolder_oncreation_share' => 'No',
            'userfolder_onfirstvisit' => 'No',
            'userfolder_update' => 'Yes',
            'userfolder_remove' => 'Yes',
            'userfolder_backend' => 'No',
            'userfolder_backend_auto_root' => [],
            'userfolder_noaccess' => '',
            'notification_from_name' => '',
            'notification_from_email' => '',
            'notification_replyto_email' => '',
            'download_template_subject' => '',
            'download_template_subject_zip' => '',
            'download_template' => '',
            'upload_template_subject' => '',
            'upload_template' => '',
            'delete_template_subject' => '',
            'delete_template' => '',
            'move_template_subject' => '',
            'move_template' => '',
            'copy_template_subject' => '',
            'copy_template' => '',
            'proof_template_subject' => '',
            'proof_template' => '',
            'filelist_template' => '',
            'permissions_edit_settings' => ['administrator'],
            'permissions_link_users' => ['administrator', 'editor'],
            'permissions_see_dashboard' => ['administrator', 'editor'],
            'permissions_see_filebrowser' => ['administrator'],
            'permissions_add_shortcodes' => ['administrator', 'editor', 'author', 'contributor'],
            'permissions_add_links' => ['administrator', 'editor', 'author', 'contributor'],
            'permissions_add_embedded' => ['administrator', 'editor', 'author', 'contributor'],
            'custom_css' => '',
            'loaders' => [],
            'colors' => [],
            'layout_border_radius' => '10',
            'layout_gap' => '10',
            'download_method' => 'redirect',
            'remember_last_location' => 'Yes',
            'gzipcompression' => 'No',
            'polyfill' => 'No',
            'always_load_scripts' => 'No',
            'nonce_validation' => 'Yes',
            'ajax_domain_verification' => 'Yes',
            'cloud_security_folder_check' => 'Yes',
            'mask_account_id' => 'No',
            'share_buttons' => [],
            'shortlinks' => 'None',
            'bitly_login' => '',
            'bitly_apikey' => '',
            'shortest_apikey' => '',
            'tinyurl_apikey' => '',
            'tinyurl_domain' => '',
            'rebrandly_apikey' => '',
            'rebrandly_domain' => '',
            'rebrandly_workspace' => '',
            'log_events' => 'Yes',
            'icon_set' => '',
            'use_team_folders' => 'Yes',
            'use_app_folder' => 'No',
            'recaptcha_sitekey' => '',
            'recaptcha_secret' => '',
            'event_summary' => 'No',
            'event_summary_period' => 'daily',
            'event_summary_recipients' => get_site_option('admin_email'),
            'webhook_endpoint_url' => '',
            'webhook_endpoint_secret' => '',
            'usage_period' => '1 day',
            'download_limits' => 'No',
            'downloads_per_user' => '',
            'downloads_per_user_per_file' => '',
            'zip_downloads_per_user' => '',
            'bandwidth_per_user' => '',
            'download_limits_notification' => '',
            'download_limits_excluded_roles' => ['administrator'],
            'download_limits_block_untraceable_users' => 'Yes',
            'proofing_password_by_default' => 'No',
            'proofing_max_items' => '',
            'proofing_use_labels' => 'Yes',
            'proofing_labels' => [],
            'modules_random_slug' => 'Yes',
            'auto_updates' => 'No',
            'beta_updates' => 'No',
            'uninstall_reset' => 'Yes',
        ];
    }

    /**
     * Format Tagify data.
     *
     * @param mixed  $data
     * @param string $field
     */
    private static function _format_tagify_data($data, $field = 'id')
    {
        if (is_array($data)) {
            return $data;
        }

        $data_obj = json_decode($data);

        if (null === $data_obj) {
            return $data;
        }

        $new_data = [];

        foreach ($data_obj as $value) {
            $new_data[] = $value->{$field} ?? $value->value;
        }

        return $new_data;
    }
}