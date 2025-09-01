<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       3.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox\Modules;

defined('ABSPATH') || exit;

class NoAccess
{
    public static $enqueued_scripts = false;

    public static function render()
    {
        self::enqueue_scripts();

        echo "<div id='OutoftheBox'>";

        include sprintf('%s/templates/modules/noaccess.php', OUTOFTHEBOX_ROOTDIR);

        echo '</div>';
    }

    public static function enqueue_scripts()
    {
        if (true === self::$enqueued_scripts) {
            return;
        }

        self::$enqueued_scripts = true;
    }
}
