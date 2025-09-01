<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       3.1
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox\Modules;

defined('ABSPATH') || exit;

class LeadCapture
{
    public static $enqueued_scripts = false;

    public static function render()
    {
        // Check if the page is loaded with a lead  set
        if (!empty($_GET['email'])) {
            // Sanitize
            $email = sanitize_email($_GET['email']);

            // Unlock
            \TheLion\OutoftheBox\LeadCapture::unlock_module($email);
        }

        include sprintf('%s/templates/modules/lead_capture.php', OUTOFTHEBOX_ROOTDIR);
    }

    public static function enqueue_scripts()
    {
        if (true === self::$enqueued_scripts) {
            return;
        }

        self::$enqueued_scripts = true;
    }
}
