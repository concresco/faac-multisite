<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       3.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox\Modules;

use TheLion\OutoftheBox\Account;
use TheLion\OutoftheBox\Accounts;
use TheLion\OutoftheBox\App;
use TheLion\OutoftheBox\Core;
use TheLion\OutoftheBox\Processor;
use TheLion\OutoftheBox\Settings;
use TheLion\OutoftheBox\User;

defined('ABSPATH') || exit;

class Module
{
    public static $enqueued_scripts = false;

    /**
     * Render the module based on the provided shortcode options.
     *
     * @param string $module the module to render
     */
    public static function render($module)
    {
        // Reload User Object for this module
        User::reset();

        // Display Login screen if user is not allowed to view
        if (false === User::can_view()) {
            Login::render();

            return;
        }

        if (!apply_filters('outofthebox_module_is_visible', true)) {
            return;
        }

        // Update Unique ID
        update_option('out_of_the_box_uniqueID', get_option('out_of_the_box_uniqueID', 0) + 1);

        // Get Shortcode options
        $shortcode = Processor::instance()->get_shortcode();

        // Set folder parameters
        $rootfolder = '';
        $dataaccountid = (false !== $shortcode['startaccount']) ? $shortcode['startaccount'] : $shortcode['account'];

        if (isset($shortcode['userfolders']) && 'manual' === $shortcode['userfolders']) {
            $personal_folders = get_user_option('out_of_the_box_linkedto');
            if (is_array($personal_folders) && !empty($personal_folders)) {
                $personal_folder = reset($personal_folders);
                $dataaccountid = $personal_folder['accountid'];
            } else {
                $guest_personal_folders = get_site_option('out_of_the_box_guestlinkedto');
                if (is_array($guest_personal_folders) && !empty($guest_personal_folders)) {
                    $guest_personal_folder = reset($guest_personal_folders);
                    $dataaccountid = $guest_personal_folder['accountid'];
                } else {
                    // User does not have a folder associated with his account
                    self::enqueue_scripts();

                    NoAccess::render();

                    return;
                }
            }

            $linked_account = Accounts::instance()->get_account_by_id($dataaccountid);
            App::set_current_account($linked_account);
        }

        $dataaccountid = App::get_current_account()->get_uuid();
        $dataorgid = $rootfolder;
        $rootfolder = (false !== $shortcode['startpath']) ? $shortcode['startpath'] : $rootfolder;

        $shortcode_class = ('1' === $shortcode['scrolltotop']) ? ' wpcp-has-scroll-to-top' : '';

        if ('0' !== $shortcode['popup']) {
            $shortcode_class .= ' wpcp-theme-light';
        } else {
            $shortcode_class .= ('default' !== $shortcode['themestyle']) ? ' wpcp-theme-'.$shortcode['themestyle'] : '';
        }

        do_action('outofthebox_before_shortcode', Processor::instance());
        do_action('outofthebox_before_module', Processor::instance());

        $module_id = $shortcode['module_id'];
        if (empty($module_id)) {
            $module_id = 'wpcp-'.Processor::instance()->get_listtoken();
        }
        echo "<div id='{$module_id}' class='wpcp-container'>";
        echo "<div id='OutoftheBox' class='{$shortcode['class']} {$shortcode['mode']} {$shortcode_class}' data-module-id='{$module_id}' style='display:none'>";
        echo "<noscript><div class='OutoftheBox-nojsmessage'>".esc_html__('To view this content, you need to have JavaScript enabled in your browser', 'wpcloudplugins').'.<br/>';
        echo "<a href='http://www.enable-javascript.com/' target='_blank'>".esc_html__('To do so, please follow these instructions', 'wpcloudplugins').'</a>.</div></noscript>';

        $attributes = [
            'id' => 'OutoftheBox-'.Processor::instance()->get_listtoken(),
            'data-token' => Processor::instance()->get_listtoken(),
            'data-account-id' => $dataaccountid,
            'data-org-id' => $dataorgid,
            'data-path' => rawurlencode($rootfolder ?? ''),
            'data-org-path' => rawurlencode(Processor::instance()->get_last_path() ?? ''),
            'data-source' => md5($shortcode['account'].$shortcode['root'].$shortcode['mode']),
            'data-sort' => $shortcode['sort_field'].':'.$shortcode['sort_order'],
        ];

        self::render_module($module, $attributes);

        echo '</div>';

        // Render module when it becomes available (e.g. when loading dynamically via AJAX)
        echo "<div class='wpcp-module-script'><script type='text/javascript'>if (typeof(jQuery) !== 'undefined' && typeof(jQuery.cp) !== 'undefined' && typeof(jQuery.cp.OutoftheBox) === 'function') { jQuery('#OutoftheBox-".Processor::instance()->get_listtoken()."').OutoftheBox(OutoftheBox_vars); };</script></div>";

        echo '</div>';

        do_action('outofthebox_after_shortcode', Processor::instance());
        do_action('outofthebox_after_module', Processor::instance());

        self::enqueue_scripts();
    }

    /**
     * Parse attributes array into a string for HTML rendering.
     *
     * @param array $attributes_arr the array of attributes
     *
     * @return string the parsed attributes
     */
    public static function parse_attributes($attributes_arr = [])
    {
        $attributes = '';
        foreach ($attributes_arr as $key => $value) {
            $attributes .= " {$key}='{$value}'";
        }

        return $attributes;
    }

    /**
     * Render the specified module with the given attributes.
     *
     * @param string $module     the module to render
     * @param array  $attributes the attributes for the module
     */
    public static function render_module($module, $attributes = [])
    {
        switch ($module) {
            case 'files':
                Filebrowser::render($attributes);

                break;

            case 'upload':
                Upload::render_standalone($attributes);

                break;

            case 'gallery':
                Gallery::render($attributes);

                break;

            case 'carousel':
                Carousel::render($attributes);

                break;

            case 'search':
                Filebrowser::render_search($attributes);

                break;

            case 'video':
            case 'audio':
                Mediaplayer::render($attributes);

                break;

            case 'list':
                ItemList::render($attributes);

                break;

            case 'embed':
                Embed::render($attributes);

                break;

            case 'button':
                Button::render($attributes);

                break;

            case 'proofing':
                Proofing::render($attributes);

                break;

            default:
                do_action('outofthebox_render_custom_module', $module, $attributes);

                break;
        }
    }

    /**
     * Enqueue necessary scripts and styles for the module.
     */
    public static function enqueue_scripts()
    {
        if (true === self::$enqueued_scripts) {
            return;
        }

        Core::instance()->load_scripts();
        Core::instance()->load_styles();

        wp_enqueue_style('Eva-Icons');
        wp_enqueue_style('OutoftheBox');
        wp_enqueue_script('OutoftheBox');

        if (!is_user_logged_in() && '' !== Settings::get('recaptcha_sitekey')) {
            wp_enqueue_script('google-recaptcha');
        }

        self::$enqueued_scripts = true;
    }
}
