<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.15
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox\Integrations;

defined('ABSPATH') || exit;

class PrettyLinks
{
    /**
     * The single instance of the class.
     *
     * @var PrettyLinks
     */
    protected static $_instance;

    public function __construct()
    {
        $this->set_hooks();
    }

    /**
     * PrettyLinks Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return PrettyLinks - PrettyLinks instance
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

    public function set_hooks()
    {
        add_filter('outofthebox_api_prettylinks_shorten_url', [$this, 'shorten_url'], 10, 2);
    }

    public function shorten_url($url, $params)
    {
        if (function_exists('prli_create_pretty_link')) {
            $name = isset($params['name']) ? $params['name'] : null;
            $slug = 'wpcp-'.substr(hash('sha256', $name), 0, 6);

            $pretty_link = \prli_get_link_from_slug($slug);

            if (false === $pretty_link) {
                $id = \prli_create_pretty_link($url, $slug, $name);
            } else {
                $id = $pretty_link->id;
            }

            if ($id) {
                return \prli_get_pretty_link_url($id);
            }
        }

        return $url;
    }
}

PrettyLinks::instance();
