<?php

namespace TheLion\OutoftheBox\Integrations;

use TheLion\OutoftheBox\Core;

defined('ABSPATH') || exit;

/**
 * A class that handles the Module Block if the builder is installed and activated.
 * https://kb.wpbakery.com/docs/inner-api/vc_map/.
 */
class WPBakeryPagebuilder
{
    public $slug = 'outofthebox';
    public $name = 'Dropbox';
    public $default = '';

    public function __construct()
    {
        // Module Block
        add_action('vc_before_init', [$this, 'map_button_field']);
        add_action('vc_after_init', [$this, 'map_element']);

        // Editor assets
        add_action('vc_frontend_editor_enqueue_js_css', [$this, 'enqueue_editor_assets']);
        add_action('vc_backend_editor_enqueue_js_css', [$this, 'enqueue_editor_assets']);

        // Frontend Rendering
        add_shortcode('vc-wpcp-'.$this->slug, [$this, 'render']);
    }

    /**
     * Load the plugin's assets in the editor.
     */
    public function enqueue_editor_assets()
    {
        Core::instance()->load_scripts();
        Core::instance()->load_styles();

        wp_enqueue_style('WPBakeryPagebuilder-'.$this->slug, plugins_url('style.css', __FILE__));

        wp_enqueue_style('OutoftheBox');
        wp_enqueue_script('OutoftheBox');
    }

    /**
     * Add the 'Module Configurator' button to the VC element.
     */
    public function map_button_field()
    {
        vc_add_shortcode_param('wpcp-'.$this->slug.'-shortcode-button', [$this, 'button_field_render'], plugins_url('button.min.js?v='.OUTOFTHEBOX_VERSION, __FILE__));
    }

    /**
     * Render the 'Module Configurator' button.
     *
     * @param array  $settings
     * @param string $value
     */
    public function button_field_render($settings, $value)
    {
        return '<button class="vc_btn vc_btn-grey vc_param-animation-style-trigger vc_ui-button vc_ui-button-shape-rounded edit_'.$this->slug.'_shortcode">'.esc_html__('Configure Module', 'wpcloudplugins').'</button>';
    }

    /**
     * Register the module in the WPBakery Pagebuilder.
     */
    public function map_element()
    {
        \vc_map([
            'name' => $this->name,
            'base' => 'vc-wpcp-'.$this->slug,
            'icon' => 'icon-wpcp-'.$this->slug,
            'category' => esc_html__('Cloud Content', 'js_composer'),
            'description' => \sprintf(esc_html__('%s module', 'wpcloudplugins'), $this->name),
            'admin_enqueue_css' => plugins_url('style.css', __FILE__),
            'show_settings_on_create' => true,
            'group' => 'WP Cloud Plugins',
            'params' => [
                [
                    'type' => 'textarea',
                    'heading' => esc_html__('Raw shortcode', 'wpcloudplugins'),
                    'admin_label' => false,
                    'param_name' => 'content',
                    'description' => esc_html__('Configure this module using the Module Builder or select an existing module.', 'wpcloudplugins'),
                    'value' => $this->default,
                ],
                [
                    'type' => 'wpcp-'.$this->slug.'-shortcode-button',
                    'heading' => esc_html__('Configure Module', 'wpcloudplugins'),
                    'value' => $this->default,
                    'param_name' => 'wpcp-'.$this->slug.'-shortcode-button',
                ],
            ],
            'render_callback' => [$this, 'render_element'],
        ]);
    }

    /**
     * Render the module on the Frontend.
     *
     * @param array       $atts
     * @param null|string $content
     */
    public function render($atts, $content = null)
    {
        if (empty($content)) {
            return '';
        }

        return do_shortcode($content);
    }
}

new WPBakeryPagebuilder();
