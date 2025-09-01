<?php

namespace TheLion\OutoftheBox\Integrations\Elementor;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use TheLion\OutoftheBox\Modules\Mediaplayer;

defined('ABSPATH') || exit;

class Widget extends Widget_Base
{
    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);

        wp_register_script('OutoftheBox.Elementor.Widget', plugins_url('widget.js', __FILE__), ['jquery'], OUTOFTHEBOX_VERSION);
    }

    public function is_editor()
    {
        return is_admin() && (isset($_GET['action']) && 'elementor' === $_GET['action']) || (isset($_REQUEST['elementor-preview']));
    }

    public function get_script_depends()
    {
        if (false === $this->is_editor()) {
            return [];
        }

        $mediaplayer = Mediaplayer::load_skin();

        if (!empty($mediaplayer)) {
            $mediaplayer->load_scripts(['wp-mediaelement']);
            $mediaplayer->load_styles();
        }

        return ['OutoftheBox.Carousel', 'OutoftheBox.Proofing', 'OutoftheBox.UploadBox', 'OutoftheBox', 'OutoftheBox.Elementor.Widget'];
    }

    public function get_style_depends()
    {
        if (false === $this->is_editor()) {
            return [];
        }

        return ['Eva-Icons', 'OutoftheBox', 'OutoftheBox'];
    }

    public function is_reload_preview_required()
    {
        return true;
    }

    public function get_name()
    {
        return 'wpcp-outofthebox';
    }

    public function get_title()
    {
        return 'Dropbox';
    }

    public function get_icon()
    {
        return 'eicon-cloud-check';
    }

    public function get_categories()
    {
        return ['wpcloudplugins'];
    }

    public function get_keywords()
    {
        return ['cloud', 'dropbox', 'documents', 'files', 'upload', 'video', 'audio', 'media', 'embed'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Module', 'wpcloudplugins'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'shortcode',
            [
                'label' => esc_html__('Module Configuration', 'wpcloudplugins'),
                'type' => Controls_Manager::TEXTAREA,
                'description' => esc_html__('Configure this module using the Module Builder or select an existing module.', 'wpcloudplugins'),
                'default' => '',
                'dynamic' => [
                    'active' => true,
                ],
                'rows' => 7,
            ]
        );

        $this->add_control(
            'edit_shortcode',
            [
                'type' => Controls_Manager::BUTTON,
                'show_label' => false,
                'text' => esc_html__('Configure Module', 'wpcloudplugins'),
                'event' => 'wpcp:editor:edit_outofthebox_shortcode',
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $html = $this->get_render_html($settings['shortcode']);

        echo ($html) ? $html : $settings['shortcode'];
    }

    protected function get_render_html($shortcode)
    {
        if (empty($shortcode)) {
            return '<div style="text-align:center;">⚠ '.esc_html__('This WP Cloud Plugin module is not yet configured.', 'wpcloudplugins').'</div>';
        }

        \ob_start();

        echo do_shortcode($shortcode);

        $output = \ob_get_clean();

        if ($this->is_editor()) {
            // Do not enqueue script in the 'Editor' part of Elementor Builder
            wp_dequeue_script(['OutoftheBox.UploadBox', 'OutoftheBox.Carousel', 'OutoftheBox.Default_Skin.Player', 'OutoftheBox.Basic_Playlist.Player', 'OutoftheBox.Legacy_jPlayer.Player', 'WPCloudplugin.Libraries', 'OutoftheBox']);
        }

        if (empty($output)) {
            return '';
        }

        return $output;
    }
}
