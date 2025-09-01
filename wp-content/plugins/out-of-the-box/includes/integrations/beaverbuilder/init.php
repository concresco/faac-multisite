<?php

namespace TheLion\OutoftheBox\Integrations;

use TheLion\OutoftheBox\Integrations;

defined('ABSPATH') || exit;

/**
 * A class that handles loading custom modules and custom
 * fields if the builder is installed and activated.
 */
class FL_Init
{
    public $slug = 'wpcp_outofthebox';

    public function __construct()
    {
        // Load custom modules.
        add_action('init', [$this, 'load_modules']);

        // Register custom fields.
        add_filter('fl_builder_custom_fields', [$this, 'register_fields']);

        // Enqueue custom field assets.
        add_action('init', [$this, 'enqueue_field_assets']);

        // Add MCE Editor buttons again, as they are removed by Beaver Builder
        add_filter('mce_buttons_2', __CLASS__.'::editor_buttons_2', 99999);
        add_filter('mce_external_plugins', __CLASS__.'::editor_external_plugins', 99999);

        // Load Tiny MCE buttons as well
        Integrations::load_integration('classiceditor');
    }

    public static function editor_buttons_2($buttons)
    {
        if (\FLBuilderModel::is_builder_active()) {
            $buttons = TinyMCE::register_tinymce_plugin_buttons($buttons);
        }

        return $buttons;
    }

    public static function editor_external_plugins($plugins)
    {
        if (\FLBuilderModel::is_builder_active()) {
            $plugins = TinyMCE::register_tinymce_plugin($plugins);
        }

        return $plugins;
    }

    /**
     * Loads our custom modules.
     */
    public function load_modules()
    {
        require_once OUTOFTHEBOX_ROOTDIR.'/includes/integrations/beaverbuilder/modules/wpcp_outofthebox_module/wpcp_outofthebox_module.php';
    }

    /**
     * Registers our custom fields.
     *
     * @param mixed $fields
     */
    public function register_fields($fields)
    {
        $fields[$this->slug] = OUTOFTHEBOX_ROOTDIR.'/includes/integrations/beaverbuilder/fields/field.php';

        return $fields;
    }

    /**
     * Enqueues our custom field assets.
     */
    public function enqueue_field_assets() {}
}

new FL_Init();
