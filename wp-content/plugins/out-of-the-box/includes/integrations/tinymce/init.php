<?php

namespace TheLion\OutoftheBox\Integrations;

use TheLion\OutoftheBox\Helpers;
use TheLion\OutoftheBox\Settings;

defined('ABSPATH') || exit;

/**
 * Tiny MCE buttons.
 */
class TinyMCE
{
    public function __construct()
    {
        $this->hooks();
    }

    /**
     * Integration hooks.
     */
    public function hooks()
    {
        // add TinyMCE button
        // Depends on the theme were to load....
        add_action('init', [$this, 'load_shortcode_buttons']);
        add_action('admin_head', [$this, 'load_shortcode_buttons']);
        add_filter('mce_css', [$this, 'enqueue_tinymce_css_frontend']);
    }

    // Add MCE buttons and script
    public function load_shortcode_buttons()
    {
        // Abort early if the user will never see TinyMCE
        if (
            !Helpers::check_user_role(Settings::get('permissions_add_shortcodes'))
            && !Helpers::check_user_role(Settings::get('permissions_add_links'))
            && !Helpers::check_user_role(Settings::get('permissions_add_embedded'))
        ) {
            return;
        }

        if ('true' !== get_user_option('rich_editing')) {
            return;
        }

        // Add a callback to regiser our tinymce plugin
        add_filter('mce_external_plugins', [$this, 'register_tinymce_plugin'], 999);

        // Add a callback to add our button to the TinyMCE toolbar
        add_filter('mce_buttons', [$this, 'register_tinymce_plugin_buttons'], 999);

        // Add custom CSS for placeholders
        add_editor_style(OUTOFTHEBOX_ROOTPATH.'/includes/integrations/tinymce/tinymce_editor.css');
    }

    // This callback registers our plug-in

    public static function register_tinymce_plugin($plugin_array)
    {
        $plugin_array['outofthebox'] = OUTOFTHEBOX_ROOTPATH.'/includes/js/ShortcodeBuilder_Tinymce.js?ver='.OUTOFTHEBOX_VERSION;

        return $plugin_array;
    }

    // This callback adds our button to the toolbar

    public static function register_tinymce_plugin_buttons($buttons)
    {
        // Add the button ID to the $button array

        if (Helpers::check_user_role(Settings::get('permissions_add_shortcodes'))) {
            $buttons[] = 'outofthebox';
        }
        if (Helpers::check_user_role(Settings::get('permissions_add_links'))) {
            $buttons[] = 'outofthebox_links';
        }
        if (Helpers::check_user_role(Settings::get('permissions_add_embedded'))) {
            $buttons[] = 'outofthebox_embed';
        }

        return $buttons;
    }

    public function enqueue_tinymce_css_frontend($mce_css)
    {
        if (!empty($mce_css)) {
            $mce_css .= ',';
        }

        $mce_css .= OUTOFTHEBOX_ROOTPATH.'/includes/integrations/tinymce/tinymce_editor.css';

        return $mce_css;
    }
}

new TinyMCE();
