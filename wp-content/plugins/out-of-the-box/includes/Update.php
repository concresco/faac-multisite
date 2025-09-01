<?php

/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.10
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

defined('ABSPATH') || exit;

class Update
{
    public static function init()
    {
        add_action('in_plugin_update_message-'.OUTOFTHEBOX_SLUG, [__CLASS__, 'in_plugin_update_message'], 10, 2);
        add_filter('auto_update_plugin', [__CLASS__, 'enable_plugin_auto_updates'], 10, 2);

        License::is_valid();

        $beta_updates = 'Yes' === Settings::get('beta_updates') ? 1 : 0;

        require_once OUTOFTHEBOX_ROOTDIR.'/vendors/plugin-update-checker/plugin-update-checker.php';
        PucFactory::buildUpdateChecker('https://www.wpcloudplugins.com/updates_v2/?action=get_metadata&slug=out-of-the-box&purchase_code='.License::get().'&plugin_id='.Core::$plugin_id.'&siteurl='.License::get_home_url().'&beta='.$beta_updates, plugin_dir_path(__DIR__).'/out-of-the-box.php');
    }

    /**
     *  Add custom Update messages in plugin dashboard.
     *
     * @param mixed $data
     * @param mixed $response
     */
    public static function in_plugin_update_message($data, $response)
    {
        printf(" Check the <a href='%s' target='_blank'>changelog</a> before you update.", 'https://wpcloudplugins.gitbook.io/docs/other/changelog');

        if (isset($data['upgrade_notice'])) {
            printf(
                '<br /><br /><span style="display:inline-block; margin-top: 10px;"><span class="dashicons dashicons-warning"></span>&nbsp;<strong>%s</strong></span>',
                $data['upgrade_notice']
            );
        }
    }

    /**
     * Enable automatic updates for this plugin.
     *
     * @param bool   $value current auto-update status
     * @param object $item  plugin update object
     *
     * @return bool updated auto-update status
     */
    public static function enable_plugin_auto_updates($value, $item)
    {
        if ('out-of-the-box' === $item->slug && 'Yes' === Settings::get('auto_updates')) {
            return true; // Enable auto-updates for this plugin
        }

        return $value; // Preserve auto-update status for other plugins
    }

    /**
     * Update settings in database from earlier versions.
     *
     * @param array $settings
     */
    public static function update_database($settings)
    {
        $updated = false;

        // Set default values
        if (empty($settings['google_analytics'])) {
            $settings['google_analytics'] = 'No';
            $updated = true;
        }

        if (empty($settings['download_template_subject'])) {
            $settings['download_template_subject'] = '%site_name% | %user_name% downloaded %file_name%';
            $updated = true;
        }

        if (empty($settings['download_template_subject_zip'])) {
            $settings['download_template_subject_zip'] = '%site_name% | %user_name% downloaded %number_of_files% file(s) from %folder_name%';
            $updated = true;
        }

        if (empty($settings['download_template'])) {
            $settings['download_template'] = '<h2>Hi %recipient_name%!</h2>

<p>%user_name% has downloaded the following files via %site_name%:</p>

<table cellpadding="0" cellspacing="0" width="100%" border="0" style="cellspacing:0;color:#000000;font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;font-size:14px;line-height:22px;table-layout:auto;width:100%;">

%filelist%

</table>';
            $updated = true;
        }

        if (empty($settings['upload_template_subject'])) {
            $settings['upload_template_subject'] = '%site_name% | %user_name% uploaded (%number_of_files%) file(s) to %folder_name%';
            $updated = true;
        }

        if (empty($settings['upload_template'])) {
            $settings['upload_template'] = '<h2>Hi %recipient_name%!</h2>

<p>%user_name% has uploaded the following file(s) via %site_name%:</p>

<table cellpadding="0" cellspacing="0" width="100%" border="0" style="cellspacing:0;color:#000000;font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;font-size:14px;line-height:22px;table-layout:auto;width:100%;">

%filelist%

</table>';
            $updated = true;
        }

        if (empty($settings['delete_template_subject'])) {
            $settings['delete_template_subject'] = '%site_name% | %user_name% deleted (%number_of_files%) file(s) from %folder_name%';
            $updated = true;
        }

        if (empty($settings['delete_template'])) {
            $settings['delete_template'] = '<h2>Hi %recipient_name%!</h2>

<p>%user_name% has deleted the following file(s) via %site_name%:</p>

<table cellpadding="0" cellspacing="0" width="100%" border="0" style="cellspacing:0;color:#000000;font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;font-size:14px;line-height:22px;table-layout:auto;width:100%;">

%filelist%

</table>';
            $updated = true;
        }

        if (empty($settings['move_template_subject'])) {
            $settings['move_template_subject'] = '%site_name% | %user_name% moved (%number_of_files%) file(s) from %folder_name%';
            $updated = true;
        }

        if (empty($settings['move_template'])) {
            $settings['move_template'] = '<h2>Hi %recipient_name%!</h2>

<p>%user_name% has moved the following file(s) via %site_name%:</p>

<table cellpadding="0" cellspacing="0" width="100%" border="0" style="cellspacing:0;color:#000000;font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;font-size:14px;line-height:22px;table-layout:auto;width:100%;">

%filelist%

</table>';
            $updated = true;
        }

        if (empty($settings['copy_template_subject'])) {
            $settings['copy_template_subject'] = '%site_name% | %user_name% copied (%number_of_files%) file(s) from %folder_name%';
            $updated = true;
        }

        if (empty($settings['copy_template'])) {
            $settings['copy_template'] = '<h2>Hi %recipient_name%!</h2>

<p>%user_name% has copied the following file(s) via %site_name%:</p>

<table cellpadding="0" cellspacing="0" width="100%" border="0" style="cellspacing:0;color:#000000;font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;font-size:14px;line-height:22px;table-layout:auto;width:100%;">

%filelist%

</table>';
            $updated = true;
        }

        if (empty($settings['proof_template_subject'])) {
            $settings['proof_template_subject'] = '%site_name% | Collection %module_title% approved by %user_name%';
            $updated = true;
        }

        if (empty($settings['proof_template'])) {
            $settings['proof_template'] = '<h2>Hi %recipient_name%!</h2>

<p>The collection "%module_title%" has been approved by %user_name%.</p>
<p>%approval_message%</p>

<table cellpadding="0" cellspacing="0" width="100%" border="0" style="cellspacing:0;color:#000000;font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;font-size:14px;line-height:22px;table-layout:auto;width:100%;">

%filelist%

</table>';
            $updated = true;
        }

        if (empty($settings['filelist_template'])) {
            $settings['filelist_template'] = '<tr style="height: 50px;">
  <td style="width:20px;padding-right:10px;padding-top: 5px;padding-left: 5px;">
    <img alt="" height="16" src="%file_icon%" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;" width="16">
  </td>
  <td style="line-height:25px;padding-left:5px;">
    <a href="%file_cloud_preview_url%" target="_blank">%file_name%</a>
    <br/>
    <div style="font-size:12px;line-height:18px;color:#a6a6a6;outline:none;text-decoration:none;">%folder_absolute_path%</div>
  </td>
  <td style="font-weight: bold;">%file_size%</td>
</tr>';
            $updated = true;
        }

        if (empty($settings['mediaplayer_skin'])) {
            $settings['mediaplayer_skin'] = 'Default_Skin';
            $updated = true;
        }

        if (empty($settings['lightbox_skin'])) {
            $settings['lightbox_skin'] = 'metro-black';
            $updated = true;
        }
        if (empty($settings['lightbox_path'])) {
            $settings['lightbox_path'] = 'horizontal';
            $updated = true;
        }

        if (empty($settings['permissions_edit_settings'])) {
            $settings['permissions_edit_settings'] = ['administrator'];
            $updated = true;
        }
        if (empty($settings['permissions_link_users'])) {
            $settings['permissions_link_users'] = ['administrator', 'editor'];
            $updated = true;
        }
        if (empty($settings['permissions_see_filebrowser'])) {
            $settings['permissions_see_filebrowser'] = ['administrator'];
            $updated = true;
        }
        if (empty($settings['permissions_add_shortcodes'])) {
            $settings['permissions_add_shortcodes'] = ['administrator', 'editor', 'author', 'contributor'];
            $updated = true;
        }
        if (empty($settings['permissions_add_links'])) {
            $settings['permissions_add_links'] = ['administrator', 'editor', 'author', 'contributor'];
            $updated = true;
        }
        if (empty($settings['permissions_add_embedded'])) {
            $settings['permissions_add_embedded'] = ['administrator', 'editor', 'author', 'contributor'];
            $updated = true;
        }

        if (empty($settings['gzipcompression'])) {
            $settings['gzipcompression'] = 'No';
            $updated = true;
        }

        if (empty($settings['polyfill'])) {
            $settings['polyfill'] = 'No';
            $updated = true;
        }

        if (empty($settings['userfolder_backend'])) {
            $settings['userfolder_backend'] = 'No';
            $updated = true;
        }

        if (!is_array($settings['userfolder_backend_auto_root'])) {
            $settings['userfolder_backend_auto_root'] = [];
            $updated = true;
        }

        if (empty($settings['colors'])) {
            $settings['colors'] = [
                'style' => 'light',
                'background' => '#f9f9f9',
                'background-dark' => '#333333',
                'accent' => '#590e54',
                'black' => '#222',
                'dark1' => '#666',
                'dark2' => '#999',
                'white' => '#fff',
                'light1' => '#fcfcfc',
                'light2' => '#e8e8e8',
            ];
            $updated = true;
        }
        if (in_array($settings['colors']['background'], ['rgb(242,242,242)', '#f2f2f2'])) {
            $settings['colors']['background'] = '#f9f9f9';
            $updated = true;
        }

        if (!isset($settings['layout_border_radius']) || '' == $settings['layout_border_radius']) {
            $settings['layout_border_radius'] = '10';
            $updated = true;
        }
        if (!isset($settings['layout_gap']) || '' == $settings['layout_gap']) {
            $settings['layout_gap'] = '10';
            $updated = true;
        }

        if (empty($settings['loaders'])) {
            $settings['loaders'] = [
                'style' => 'spinner',
                'loading' => OUTOFTHEBOX_ROOTPATH.'/css/images/wpcp-loader.svg',
                'no_results' => OUTOFTHEBOX_ROOTPATH.'/css/images/loader_no_results.svg',
                'protected' => OUTOFTHEBOX_ROOTPATH.'/css/images/loader_protected.svg',
            ];
            $updated = true;
        }

        if ($settings['loaders']['loading'] === OUTOFTHEBOX_ROOTPATH.'/css/images/loader_loading.gif') {
            $settings['loaders']['loading'] = OUTOFTHEBOX_ROOTPATH.'/css/images/wpcp-loader.svg';
            $updated = true;
        }

        if ($settings['loaders']['no_results'] === OUTOFTHEBOX_ROOTPATH.'/css/images/loader_no_results.png') {
            $settings['loaders']['no_results'] = OUTOFTHEBOX_ROOTPATH.'/css/images/loader_no_results.svg';
            $updated = true;
        }

        if ($settings['loaders']['protected'] === OUTOFTHEBOX_ROOTPATH.'/css/images/loader_protected.png') {
            $settings['loaders']['protected'] = OUTOFTHEBOX_ROOTPATH.'/css/images/loader_protected.svg';
            $updated = true;
        }

        if (empty($settings['loaders']['iframe'])) {
            $settings['loaders']['iframe'] = OUTOFTHEBOX_ROOTPATH.'/css/images/wpcp-loader.svg';
            $updated = true;
        }

        if (empty($settings['colors']['background-dark'])) {
            $settings['colors']['background-dark'] = '#333333';
            $updated = true;
        }

        if (empty($settings['lightbox_rightclick'])) {
            $settings['lightbox_rightclick'] = 'No';
            $updated = true;
        }

        if (empty($settings['lightbox_thumbnails'])) {
            $settings['lightbox_thumbnails'] = 'Yes';
            $updated = true;
        }

        if (empty($settings['lightbox_showcaption'])) {
            $settings['lightbox_showcaption'] = 'always';
            $updated = true;
        }
        if (empty($settings['lightbox_showheader'])) {
            $settings['lightbox_showheader'] = 'always';
            $updated = true;
        }

        if (empty($settings['lightbox_thumbnailbar'])) {
            $settings['lightbox_thumbnailbar'] = 'hover';
            $updated = true;
        }

        if (empty($settings['always_load_scripts'])) {
            $settings['always_load_scripts'] = 'No';
            $updated = true;
        }

        if (empty($settings['nonce_validation'])) {
            $settings['nonce_validation'] = 'Yes';
            $updated = true;
        }

        if (empty($settings['ajax_domain_verification'])) {
            $settings['ajax_domain_verification'] = 'Yes';
            $updated = true;
        }

        if (empty($settings['cloud_security_folder_check'])) {
            $settings['cloud_security_folder_check'] = 'Yes';
            $updated = true;
        }

        if (empty($settings['mask_account_id'])) {
            $settings['mask_account_id'] = 'No';
            $updated = true;
        }

        if (empty($settings['shortlinks'])) {
            $settings['shortlinks'] = 'None';
            $settings['bitly_login'] = '';
            $settings['bitly_apikey'] = '';
            $settings['shortest_apikey'] = '';
            $settings['rebrandly_apikey'] = '';
            $settings['rebrandly_domain'] = '';
            $settings['rebrandly_workspace'] = '';
            $updated = true;
        }

        if (!isset($settings['rebrandly_workspace'])) {
            $settings['rebrandly_workspace'] = '';
            $updated = true;
        }

        if (empty($settings['permissions_see_dashboard'])) {
            $settings['permissions_see_dashboard'] = ['administrator', 'editor'];
            $updated = true;
        }

        if (empty($settings['log_events'])) {
            $settings['log_events'] = 'Yes';
            $updated = true;
        }

        if (empty($settings['icon_set']) || '/' === $settings['icon_set']) {
            $settings['icon_set'] = OUTOFTHEBOX_ROOTPATH.'/css/icons/';
            $updated = true;
        }

        if (empty($settings['download_method'])) {
            $settings['download_method'] = 'redirect';
            $updated = true;
        }

        if (empty($settings['lostauthorization_notification'])) {
            $settings['lostauthorization_notification'] = get_site_option('admin_email');
            $updated = true;
        }

        // Users updating the plugin will have not have enabled this by default
        if (empty($settings['use_team_folders'])) {
            $settings['use_team_folders'] = 'No';
            $updated = true;
        }

        if (!isset($settings['recaptcha_sitekey'])) {
            $settings['recaptcha_sitekey'] = '';
            $settings['recaptcha_secret'] = '';
            $updated = true;
        }

        if ('default' === $settings['mediaplayer_skin']) {
            $settings['mediaplayer_skin'] = 'Default_Skin';
            $updated = true;
        }

        if (empty($settings['mediaplayer_load_native_mediaelement'])) {
            $settings['mediaplayer_load_native_mediaelement'] = 'No';
            $updated = true;
        }

        if (!isset($settings['mediaplayer_ads_tagurl'])) {
            $settings['mediaplayer_ads_tagurl'] = '';
            $settings['mediaplayer_ads_skipable'] = 'Yes';
            $settings['mediaplayer_ads_skipable_after'] = '5';
            $updated = true;
        }

        if (!isset($settings['mediaplayer_ads_hide_role'])) {
            $settings['mediaplayer_ads_hide_role'] = ['users'];
            $updated = true;
        }

        if (!isset($settings['event_summary'])) {
            $settings['event_summary'] = 'No';
            $settings['event_summary_period'] = 'daily';
            $settings['event_summary_recipients'] = get_site_option('admin_email');
            $updated = true;
        }

        if (!isset($settings['webhook_endpoint_url'])) {
            $settings['webhook_endpoint_url'] = '';
            $updated = true;
        }

        if (!isset($settings['webhook_endpoint_secret'])) {
            require_once ABSPATH.'wp-includes/pluggable.php';
            $settings['webhook_endpoint_secret'] = wp_generate_password(16);
            $updated = true;
        }

        if (empty($settings['loadimages']) || 'googlethumbnail' === $settings['loadimages']) {
            $settings['loadimages'] = 'thumbnail';
            $updated = true;
        }

        if (empty($settings['userfolder_noaccess'])) {
            $settings['userfolder_noaccess'] = "<h2>No Access</h2>

<p>Your account isn't (yet) configured to access this content. Please contact the administrator of the site if you would like to have access. The administrator can link your account to the right content.</p>";
            $updated = true;
        }

        if (!isset($settings['auto_updates'])) {
            $settings['auto_updates'] = 'No';
            $updated = true;
        }

        if (!isset($settings['beta_updates'])) {
            $settings['beta_updates'] = 'No';
            $updated = true;
        }

        if (!isset($settings['uninstall_reset'])) {
            $settings['uninstall_reset'] = 'Yes';
            $updated = true;
        }

        if (empty($settings['use_app_folder'])) {
            $settings['use_app_folder'] = 'No';
            $updated = true;
        }

        if ('Dropbox' === $settings['shortlinks']) {
            $settings['shortlinks'] = 'None';
            $updated = true;
        }

        if (isset($settings['purcasecode'])) {
            $settings['purchase_code'] = $settings['purcasecode'];
            unset($settings['purcasecode']);
            $updated = true;
        }

        if (isset($settings['auth_key']) && false === get_site_option('wpcp-outofthebox-auth_key')) {
            add_site_option('wpcp-outofthebox-auth_key', $settings['auth_key']);
            unset($settings['auth_key']);
            $updated = true;
        }

        if (empty($settings['share_buttons'])) {
            $settings['share_buttons'] = [
                'bluesky' => 'enabled',
                'clipboard' => 'enabled',
                'email' => 'enabled',
                'facebook' => 'enabled',
                'linkedin' => 'enabled',
                'mastodon' => 'disabled',
                'messenger' => 'enabled',
                'odnoklassniki' => 'disabled',
                'pinterest' => 'enabled',
                'pocket' => 'disabled',
                'reddit' => 'disabled',
                'teams' => 'disabled',
                'telegram' => 'enabled',
                'twitter' => 'enabled',
                'viber' => 'disabled',
                'vkontakte' => 'disabled',
                'whatsapp' => 'enabled',
            ];
            $updated = true;
        }

        if (empty($settings['share_buttons']['bluesky'])) {
            $settings['share_buttons']['bluesky'] = 'enabled';
            $settings['share_buttons']['teams'] = 'disabled';
            $updated = true;
        }

        if (!isset($settings['notification_from_name'])) {
            $settings['notification_from_name'] = '';
            $settings['notification_from_email'] = '';
            $updated = true;
        }

        if (!isset($settings['userfolder_name_guest_prefix'])) {
            $settings['userfolder_name_guest_prefix'] = esc_html__('Guests', 'wpcloudplugins').' - ';
            $updated = true;
        }

        if (!isset($settings['remember_last_location'])) {
            $settings['remember_last_location'] = 'Yes';
            $updated = true;
        }

        if (!isset($settings['gallery_navbar_onhover'])) {
            $settings['gallery_navbar_onhover'] = 'Yes';
            $updated = true;
        }

        if (!isset($settings['userfolder_oncreation_share'])) {
            $settings['userfolder_oncreation_share'] = 'No';
            $updated = true;
        }

        if (!isset($settings['download_limits'])) {
            $settings['download_limits'] = 'No';
            $settings['downloads_per_user'] = '';
            $settings['downloads_per_user_per_file'] = '';
            $settings['zip_downloads_per_user'] = '';
            $settings['bandwidth_per_user'] = '';
            $settings['download_limits_notification'] = '';
            $settings['download_limits_excluded_roles'] = ['administrator'];
            $updated = true;
        }

        if (!isset($settings['usage_period'])) {
            $settings['usage_period'] = '1 day';
            $settings['downloads_per_user'] = $settings['downloads_per_user_per_day'] ?? '';
            $settings['zip_downloads_per_user'] = $settings['zip_downloads_per_user_per_day'] ?? '';
            $settings['bandwidth_per_user'] = $settings['bandwidth_per_user_per_day'] ?? '';
            $updated = true;
        }

        if (!isset($settings['download_limits_block_untraceable_users'])) {
            $settings['download_limits_block_untraceable_users'] = 'Yes';
            $updated = true;
        }

        if (!isset($settings['proofing_password_by_default'])) {
            $settings['proofing_password_by_default'] = 'No';
            $settings['proofing_max_items'] = '';
            $settings['proofing_use_labels'] = 'Yes';
            $settings['proofing_labels'] = [esc_html__('Approved', 'wpcloudplugins'), esc_html__('Needs Review', 'wpcloudplugins'), esc_html__('Pending', 'wpcloudplugins'), esc_html__('Rejected', 'wpcloudplugins')];

            $updated = true;
        }

        if (!isset($settings['modules_random_slug'])) {
            $settings['modules_random_slug'] = 'Yes';
            $updated = true;
        }

        if (!isset($settings['dropbox_app_key'])) {
            $settings['dropbox_app_key'] = '';
            $settings['dropbox_app_secret'] = '';
            $updated = true;
        }

        if (empty($settings['accessibility_loginmessage'])) {
            $settings['accessibility_loginmessage'] = '<p>'.esc_html__('Please log in to access this content.', 'wpcloudplugins').'</p>';
            $updated = true;
        }

        if (empty($settings['accessibility_passwordmessage'])) {
            $settings['accessibility_passwordmessage'] = '<p>'.esc_html__('This content is password protected. To view it please enter your password below:', 'wpcloudplugins').'</p>';
            $updated = true;
        }

        if (empty($settings['accessibility_leadmessage'])) {
            $settings['accessibility_leadmessage'] = '<p>'.esc_html__('Please enter your email address below to proceed.', 'wpcloudplugins').'</p>';
            $updated = true;
        }

        if (!defined('OUTOFTHEBOX_AUTH_KEY')) {
            $auth_key = get_site_option('wpcp-outofthebox-auth_key');
            if (false === $auth_key) {
                require_once ABSPATH.'wp-includes/pluggable.php';
                $auth_key = wp_generate_password(32);
                add_site_option('wpcp-outofthebox-auth_key', $auth_key);

                // Delete all existing authorization tokens as they become useless with a new encryption key
                Helpers::log_error('New encryption key set. All authorization tokens have been removed.', 'Update', null, __LINE__);
                Accounts::revoke_authorizations();
            }

            define('OUTOFTHEBOX_AUTH_KEY', $auth_key);
        }

        if ($updated) {
            update_option('out_of_the_box_settings', $settings);
        }

        $version = get_option('out_of_the_box_version');

        if (version_compare($version, '1.13') < 0) {
            // Install / Update Event Database
            Events::install_database();
        }

        if (version_compare($version, '2.10.2') < 0) {
            Events::update_2_10_2();
        }

        if (version_compare($version, '3') < 0) {
            Events::update_3_0();
        }

        if (version_compare($version, '3.2.0') < 0) {
            // force rewrite rules, as modules are now accessible via permanent links
            delete_option('rewrite_rules');
        }

        if (version_compare($version, '3.2.8.2') < 0) {
            Events::update_3_2_8_2();
        }

        if (false !== $version) {
            if (version_compare($version, '1.13.11') < 0) {
                // Remove old DB lists
                delete_option('out_of_the_box_lists');
            }

            if (version_compare($version, '1.14') < 0) {
                // Remove old skin
                $settings['mediaplayer_skin'] = 'Default_Skin';
                update_option('out_of_the_box_settings', $settings);
            }

            if (version_compare($version, '2.13.2') < 0) {
                add_action('wp_loaded', [__NAMESPACE__.'\Update', 'update_2_13_2']);
            }

            if (version_compare($version, '3.2.3') < 0) {
                add_action('wp_loaded', [__NAMESPACE__.'\Update', 'update_3_2_3']);
            }
        }

        // Update Version number
        if (OUTOFTHEBOX_VERSION !== $version) {
            // Update License information
            add_action('wp_loaded', [__NAMESPACE__.'\License', 'reset']);

            // Clear Cache
            Processor::reset_complete_cache();

            // Clear WordPress Cache
            add_action('wp_loaded', [__NAMESPACE__.'\Helpers', 'purge_cache_others']);

            update_option('out_of_the_box_version', OUTOFTHEBOX_VERSION);
        }

        return $settings;
    }

    public static function update_2_13_2()
    {
        foreach (Accounts::instance()->list_accounts() as $account) {
            $account->set_uuid();
        }

        Accounts::instance()->save();
    }

    public static function update_3_2_3()
    {
        Processor::reset_complete_cache(true);
    }
}
