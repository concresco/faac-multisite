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

abstract class MediaplayerSkin
{
    public $url;
    public $template_path;

    public function load_player()
    {
        $this->load_scripts();
        $this->load_styles();

        return $this->render_template();
    }

    abstract public function load_scripts();

    abstract public function load_styles();

    public function set_url($url)
    {
        return $this->url = $url;
    }

    public function get_url()
    {
        return $this->url;
    }

    public function set_template_path($template_path)
    {
        $this->template_path = $template_path;
    }

    public function get_template_path()
    {
        return $this->template_path;
    }

    public function render_template()
    {
        return include $this->get_template_path();
    }
}
