<?php

namespace TheLion\OutoftheBox\Integrations;

use Elementor\Plugin;
use TheLion\OutoftheBox\Integrations\Elementor\FormField;
use TheLion\OutoftheBox\Integrations\Elementor\Widget;

defined('ABSPATH') || exit;

/**
 * Elementor block with live preview.
 */
class Elementor
{
    public const VERSION = \OUTOFTHEBOX_VERSION;
    public const MINIMUM_ELEMENTOR_VERSION = '3.5.0';
    public const MINIMUM_PHP_VERSION = '7.4';

    private static $_instance;

    public function __construct()
    {
        // Add Widget
        \add_action('elementor/elements/categories_registered', [$this, 'add_elementor_category']);
        \add_action('elementor/widgets/register', [$this, 'register_widget']);

        // Add Form field
        add_action('elementor_pro/forms/fields/register', [$this, 'register_form_field']);
    }

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function add_elementor_category($elements_manager)
    {
        $elements_manager->add_category(
            'wpcloudplugins',
            [
                'title' => 'WP Cloud Plugins',
                'icon' => 'fa fa-plug',
            ]
        );
    }

    /**
     * Register Elementor Form Field.
     *
     * @param mixed $fields_manager
     */
    public function register_form_field($fields_manager)
    {
        include_once __DIR__.'/form.php';
        $fields_manager->register(new FormField());
    }

    /**
     * Register Elementor Widget.
     */
    public function register_widget()
    {
        // Include Widget files
        require_once __DIR__.'/widget.php';

        // Register widget
        Plugin::instance()->widgets_manager->register(new Widget());
    }
}
Elementor::instance();
