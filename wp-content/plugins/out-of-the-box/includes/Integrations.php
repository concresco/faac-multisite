<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.11
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

defined('ABSPATH') || exit;

class Integrations
{
    public static $integrations = [];

    public static function list()
    {
        $integrations = [
            'advancedcustomfields' => [
                'key' => 'advancedcustomfields',
                'title' => 'Advanced Custom Fields',
                'img' => 'integrations/advancedcustomfields/thumb.png',
                'available' => class_exists('ACF'),
                'default' => true,
                'init' => 'integrations/advancedcustomfields/init.php',
            ],
            'beaverbuilder' => [
                'key' => 'beaverbuilder',
                'title' => 'Beaver Builder',
                'img' => 'integrations/beaverbuilder/thumb.png',
                'available' => class_exists('FLBuilder'),
                'default' => true,
                'init' => 'integrations/beaverbuilder/init.php',
            ],
            'contactform7' => [
                'key' => 'contactform7',
                'title' => 'Contact Form 7',
                'img' => 'integrations/contactform7/thumb.png',
                'available' => defined('WPCF7_PLUGIN'),
                'default' => true,
                'init' => false,
            ],
            'classiceditor' => [
                'key' => 'classiceditor',
                'title' => 'Classic Editor & TinyMCE Editors',
                'img' => 'integrations/tinymce/thumb.png',
                'available' => true,
                'default' => true,
                'init' => 'integrations/tinymce/init.php',
            ],
            'divipagebuilder' => [
                'key' => 'divipagebuilder',
                'title' => 'Divi Page Builder',
                'img' => 'integrations/divipagebuilder/thumb.png',
                'available' => defined('ET_BUILDER_THEME'),
                'default' => true,
                'init' => 'integrations/divipagebuilder/init.php',
            ],
            'elementor' => [
                'key' => 'elementor',
                'title' => 'Elementor',
                'img' => 'integrations/elementor/thumb.png',
                'available' => did_action('elementor/loaded') > 0,
                'default' => true,
                'init' => 'integrations/elementor/init.php',
            ],
            'easydigitaldownloads' => [
                'key' => 'easydigitaldownloads',
                'title' => 'Easy Digital Downloads',
                'img' => 'integrations/easydigitaldownloads/thumb.png',
                'available' => defined('EDD_PLUGIN_FILE'),
                'default' => true,
                'init' => 'integrations/easydigitaldownloads/init.php',
                'beta' => true,
            ],
            'fluentforms' => [
                'key' => 'fluentforms',
                'title' => 'Fluent Forms',
                'img' => 'integrations/fluentforms/thumb.png',
                'available' => defined('FLUENTFORM'),
                'default' => true,
                'init' => 'integrations/fluentforms/init.php',
            ],
            'formidableforms' => [
                'key' => 'formidableforms',
                'title' => 'Formidable Forms',
                'img' => 'integrations/formidableforms/thumb.png',
                'available' => class_exists('FrmHooksController'),
                'default' => true,
                'init' => 'integrations/formidableforms/init.php',
            ],
            'gravityforms' => [
                'key' => 'gravityforms',
                'title' => 'Gravity Forms',
                'available' => class_exists('GFForms') && class_exists('GFCommon'),
                'img' => 'integrations/gravityforms/thumb.png',
                'default' => true,
                'init' => 'integrations/gravityforms/init.php',
            ],
            'gravitypdf' => [
                'key' => 'gravitypdf',
                'title' => 'Gravity PDF',
                'img' => 'integrations/gravitypdf/thumb.png',
                'available' => defined('PDF_EXTENDED_VERSION') && class_exists('GFForms'),
                'default' => true,
                'init' => 'integrations/gravitypdf/init.php',
            ],
            'gutenberg' => [
                'key' => 'gutenberg',
                'title' => 'Gutenberg',
                'img' => 'integrations/gutenberg/thumb.png',
                'available' => true,
                'default' => true,
                'init' => 'integrations/gutenberg/init.php',
            ],
            'ninjaforms' => [
                'key' => 'ninjaforms',
                'title' => 'NinjaForms',
                'available' => class_exists('Ninja_Forms'),
                'img' => 'integrations/ninjaforms/thumb.png',
                'default' => true,
                'init' => 'integrations/ninjaforms/init.php',
                'beta' => true,
            ],
            'prettylinks' => [
                'key' => 'prettylinks',
                'title' => 'PrettyLinks',
                'available' => defined('PRLI_VERSION'),
                'img' => 'integrations/prettylinks/thumb.png',
                'default' => true,
                'init' => 'integrations/prettylinks/init.php',
                'beta' => true,
            ],
            'slack' => [
                'key' => 'slack',
                'title' => 'Slack',
                'available' => true,
                'img' => 'integrations/slack/thumb.png',
                'has_settings' => true,
                'default' => false,
                'init' => 'integrations/slack/init.php',
                'beta' => true,
            ],
            'ultimatemember' => [
                'key' => 'ultimatemember',
                'title' => 'UltimateMember',
                'img' => 'integrations/ultimatemember/thumb.png',
                'available' => defined('UM_VERSION'),
                'default' => true,
                'init' => 'integrations/ultimatemember/init.php',
            ],
            'uncannyautomator' => [
                'key' => 'uncannyautomator',
                'title' => 'Uncanny Automator',
                'img' => 'integrations/automator/thumb.png',
                'available' => defined('AUTOMATOR_PLUGIN_VERSION'),
                'default' => true,
                'init' => 'integrations/automator/init.php',
            ],
            'woocommerce' => [
                'key' => 'woocommerce',
                'title' => 'WooCommerce',
                'img' => 'integrations/woocommerce/thumb.png',
                'available' => class_exists('woocommerce'),
                'has_settings' => true,
                'default' => true,
                'init' => 'integrations/woocommerce/init.php',
            ],
            'wpbakerypagebuilder' => [
                'key' => 'wpbakerypagebuilder',
                'title' => 'WPBakery Page Builder',
                'img' => 'integrations/wpbakerypagebuilder/thumb.png',
                'available' => defined('WPB_VC_VERSION'),
                'default' => false,
                'init' => 'integrations/wpbakerypagebuilder/init.php',
            ],
            'wpforms' => [
                'key' => 'wpforms',
                'title' => 'WPForms',
                'img' => 'integrations/wpforms/thumb.png',
                'available' => defined('WPFORMS_VERSION') && class_exists('WPForms_Field') && version_compare(\WPFORMS_VERSION, '1.6.7', '>'),
                'default' => true,
                'init' => 'integrations/wpforms/init.php',
            ],
        ];

        if (defined('WPCF7_PLUGIN')) {
            if (version_compare(WPCF7_VERSION, '6', '<')) {
                $integrations['contactform7']['init'] = 'integrations/contactform7legacy/init.php';
            } else {
                $integrations['contactform7']['init'] = 'integrations/contactform7/init.php';
            }
        }

        $integrations = \apply_filters('outofthebox_set_integrations', $integrations);

        foreach ($integrations as $key => $integration) {
            $value = Settings::get("integrations[{$key}]", $integration['default']);
            $integrations[$key]['value'] = true === $value || '1' === $value || 'Yes' === $value;
        }

        self::$integrations = $integrations;

        return self::$integrations;
    }

    public static function init()
    {
        // Add Global Form Helpers
        require_once 'integrations/FormHelpers.php';

        // Load integrations
        foreach (self::list() as $key => $integration) {
            // Divi uses some special hooks to get started. Always load it
            if ('divipagebuilder' === $key && true === $integration['value'] && \file_exists(__DIR__.'/integrations/divipagebuilder/init.php')) {
                require_once 'integrations/divipagebuilder/init.php';
            }

            if (true === $integration['available'] && true === $integration['value']) {
                self::load_integration($key);
            }
        }
    }

    public static function load_integration($key)
    {
        if (empty(self::$integrations)) {
            self::list();
        }

        if (empty(self::$integrations[$key]) || false === file_exists(__DIR__.'/'.self::$integrations[$key]['init'])) {
            return;
        }

        require_once self::$integrations[$key]['init'];
    }

    /**
     * Check if a specific integration is active.
     *
     * @param string $key the key of the integration to check
     *
     * @return bool returns true if the integration is active, false otherwise
     */
    public static function is_active($key)
    {
        if (empty(self::$integrations)) {
            self::list();
        }

        return isset(self::$integrations[$key]) && true === self::$integrations[$key]['value'];
    }
}
