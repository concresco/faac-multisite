<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       3.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox\Modules;

use TheLion\OutoftheBox\Processor;

defined('ABSPATH') || exit;

class Login
{
    public static $enqueued_scripts = false;

    public static function render()
    {
        include sprintf('%s/templates/modules/login.php', OUTOFTHEBOX_ROOTDIR);

        do_action('outofthebox_shortcode_no_view_permission', Processor::instance());
    }

    public static function enqueue_scripts()
    {
        if (true === self::$enqueued_scripts) {
            return;
        }

        self::$enqueued_scripts = true;
    }
}
