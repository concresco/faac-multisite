<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       3.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox\Modules;

use TheLion\OutoftheBox\Restrictions;

defined('ABSPATH') || exit;

class Password
{
    public static $enqueued_scripts = false;

    public static function render()
    {
        // Check if the page is loaded with a module password set
        if (!empty($_GET['module_pass'])) {
            // Sanitize
            $password = wp_kses(stripslashes($_REQUEST['module_pass']), 'strip');

            // Unlock
            Restrictions::unlock_module($password);
        }

        include sprintf('%s/templates/modules/password.php', OUTOFTHEBOX_ROOTDIR);
    }

    public static function enqueue_scripts()
    {
        if (true === self::$enqueued_scripts) {
            return;
        }

        self::$enqueued_scripts = true;
    }
}
