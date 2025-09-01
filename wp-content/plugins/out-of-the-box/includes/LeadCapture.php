<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       3.1
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

// Exit if accessed directly.
defined('ABSPATH') || exit;

class LeadCapture
{
    /**
     * The single instance of the class.
     *
     * @var LeadCapture
     */
    protected static $_instance;

    protected static $cookie_key = 'wpcp-user-lead';

    /**
     * LeadCapture Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return LeadCapture - LeadCapture instance
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

    /**
     * Unlock the module if the user is logged in or a valid email is provided.
     *
     * @param null|string $email the email address to unlock the module
     *
     * @return bool true if the module is unlocked, false otherwise
     */
    public static function unlock_module($email = null)
    {
        $requires_lead = '1' === Processor::instance()->get_shortcode_option('requires_lead');

        if (!$requires_lead || is_user_logged_in()) {
            return true;
        }

        // Check with the given email
        if (!empty($email)) {
            return self::validate_and_set_email_cookie($email);
        }

        // If no email is given, check with the cookie
        if (isset($_COOKIE[self::$cookie_key])) {
            $email = wp_unslash($_COOKIE[self::$cookie_key]);

            return is_email($email);
        }

        return false;
    }

    public function get_lead_email()
    {
        return $_COOKIE[self::$cookie_key] ?? '';
    }

    /**
     * Validate the email and set it in the cookie if valid.
     *
     * @param string $email the email address to validate and set
     *
     * @return bool true if the email is valid, false otherwise
     */
    protected static function validate_and_set_email_cookie($email)
    {
        if (!is_email($email)) {
            return false;
        }

        $cookie_options = [
            'expires' => null,
            'path' => COOKIEPATH,
            'domain' => COOKIE_DOMAIN,
            'secure' => is_ssl() && 'https' === parse_url(get_option('home'), PHP_URL_SCHEME),
            'httponly' => true,
            'samesite' => 'Strict',
        ];

        setcookie(self::$cookie_key, $email, $cookie_options);

        // Set in the $_COOKIE as well, so that the modules that will be rendered will already be unlocked.
        $_COOKIE[self::$cookie_key] = $email;

        // Action to do something with the email
        do_action('outofthebox-lead-captured', ['email' => $email]);

        return true;
    }
}
