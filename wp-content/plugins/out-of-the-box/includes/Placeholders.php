<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       3.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

defined('ABSPATH') || exit;

class Placeholders
{
    public static function apply($value, $context = null, $extra = [])
    {
        $value = self::replace_deprecated_placeholders($value);

        if (class_exists('woocommerce')) {
            list($value, $context, $extra) = self::_woocommerce($value, $context, $extra);
        }

        list($value, $context, $extra) = self::_user($value, $context, $extra);

        list($value, $context, $extra) = self::_module($value, $context, $extra);

        list($value, $context, $extra) = self::_post($value, $context, $extra);

        list($value, $context, $extra) = self::_date($value, $context, $extra);

        list($value, $context, $extra) = self::_general($value, $context, $extra);

        list($value, $context, $extra) = self::_file($value, $context, $extra);

        list($value, $context, $extra) = self::_cookies($value, $context, $extra);

        return apply_filters('outofthebox_apply_placeholders', $value, $context, $extra);
    }

    public static function replace_deprecated_placeholders($value)
    {
        return \str_replace([
            '%user_firstname%',
            '%user_lastname%',
        ], [
            '%usermeta_first_name%',
            '%usermeta_last_name%',
        ], $value);
    }

    public static function _user($value, $context = null, $extra = [])
    {
        // Add User Placeholders for Guest users
        if (!isset($extra['user_data'])) {
            if (UserFolders::is_using_dynamic_folders() && null !== UserFolders::instance()->get_current_user()) {
                $extra['user_data'] = UserFolders::instance()->get_current_user();
            } elseif (is_user_logged_in()) {
                $extra['user_data'] = wp_get_current_user();
            } else {
                if (!isset($_COOKIE['WPCP_UUID'])) {
                    $cookie_options = [
                        'expires' => null,
                        'path' => COOKIEPATH,
                        'domain' => COOKIE_DOMAIN,
                        'secure' => is_ssl() && 'https' === parse_url(get_option('home'), PHP_URL_SCHEME),
                        'httponly' => true,
                        'samesite' => 'Strict',
                    ];

                    $uuid = wp_generate_uuid4();
                    setcookie('WPCP_UUID', $uuid, $cookie_options);
                    $_COOKIE['WPCP_UUID'] = $uuid;

                    Helpers::log_error('No UUID found for use in placeholder.', 'Placeholder', null, __LINE__);
                }

                $id = $_COOKIE['WPCP_UUID'];

                $extra['user_data'] = new \stdClass();
                $extra['user_data']->user_login = $id;
                $extra['user_data']->display_name = esc_html__('Guests', 'wpcloudplugins').' - '.$id;
                $extra['user_data']->ID = $id;
                $extra['user_data']->user_role = esc_html__('Anonymous user', 'wpcloudplugins');
            }

            // Add WooCommmerce Customer information for Guest users if needed
            if ('Anonymous user' === $extra['user_data']->user_role && isset($extra['wc_order'])) {
                $user = $extra['wc_order']->get_user(); // Will return false for guest users

                if (empty($user) && !empty($extra['wc_order'])) {
                    $extra['user_data']->user_login = $extra['wc_order']->get_billing_first_name().' '.$extra['wc_order']->get_billing_last_name();
                    $extra['user_data']->display_name = $extra['wc_order']->get_billing_first_name().' '.$extra['wc_order']->get_billing_last_name();
                    $extra['user_data']->user_email = $extra['wc_order']->get_billing_email();
                    $extra['user_data']->ID = $extra['wc_order']->get_order_key();

                    $extra['custom_user_metadata'] = [
                        'first_name' => $extra['wc_order']->get_billing_first_name(),
                        'last_name' => $extra['wc_order']->get_billing_last_name(),
                    ];
                }
            }
        }

        // User Placeholders
        if (isset($extra['user_data'])) {
            $user_obj = $extra['user_data'];

            $value = strtr($value, [
                '%user_login%' => isset($user_obj->user_login) ? $user_obj->user_login : '',
                '%user_email%' => isset($user_obj->user_email) ? $user_obj->user_email : '',
                '%display_name%' => isset($user_obj->display_name) ? $user_obj->display_name : '',
                '%ID%' => isset($user_obj->ID) ? $user_obj->ID : '',
                '%user_role%' => isset($user_obj->roles) ? implode(',', $user_obj->roles) : '',
                '%user_registered%' => isset($user_obj->user_registered) ? date('Y-m-d', strtotime($user_obj->user_registered)) : '',
            ]);
        }

        // Custom User Meta Placeholders
        preg_match_all('/%usermeta_(?<key>.+)%/U', $value, $usermeta_requests, PREG_SET_ORDER, 0);

        if (!empty($usermeta_requests) && isset($extra['user_data'])) {
            $usermeta = get_user_meta($user_obj->ID);
            $old_usermeta = $extra['custom_user_metadata'] ?? [];

            foreach ($usermeta_requests as $usermeta_request) {
                $usermeta_placeholder = $usermeta_request[0];

                $usermeta_value = '';
                if (isset($old_usermeta[$usermeta_request['key']])) {
                    $usermeta_value = $old_usermeta[$usermeta_request['key']];
                } elseif (isset($usermeta[$usermeta_request['key']])) {
                    $usermeta_value = $usermeta[$usermeta_request['key']];
                }

                if (is_array($usermeta_value)) {
                    $usermeta_value = $usermeta_value[0];
                }

                $value = strtr($value, [
                    $usermeta_placeholder => $usermeta_value,
                ]);
            }
        }

        return [$value, $context, $extra];
    }

    public static function _date($value, $context = null, $extra = [])
    {
        // Localized Date Placeholders
        preg_match_all('/%date_i18n_(?<format>.+)%/U', $value, $date_i18n_placeholders, PREG_SET_ORDER, 0);

        if (!empty($date_i18n_placeholders)) {
            foreach ($date_i18n_placeholders as $placeholder_data) {
                $date_placeholder = $placeholder_data[0];
                $value = strtr($value, [
                    $date_placeholder => !empty($placeholder_data['format']) ? date_i18n($placeholder_data['format']) : '',
                ]);
            }
        }

        // Date Placeholders
        preg_match_all('/%date_(?<format>.+)%/U', $value, $date_placeholders, PREG_SET_ORDER, 0);

        if (!empty($date_placeholders)) {
            foreach ($date_placeholders as $placeholder_data) {
                $date_placeholder = $placeholder_data[0];
                $value = strtr($value, [
                    $date_placeholder => !empty($placeholder_data['format']) ? current_time($placeholder_data['format']) : '',
                ]);
            }
        }

        return [$value, $context, $extra];
    }

    public static function _module($value, $context = null, $extra = [])
    {
        // Module Placeholders
        if (false === strpos($value, '%module_')) {
            return [$value, $context, $extra];
        }

        if ($context instanceof Processor && !is_null($context->get_shortcode_option('module_id'))) {
            $module_id = $context->get_shortcode_option('module_id');
            $module = get_post($module_id);

            $value = strtr($value, [
                '%module_title%' => is_a($module, 'WP_Post') ? $module->post_title : '',
            ]);
        }

        return [$value, $context, $extra];
    }

    public static function _post($value, $context = null, $extra = [])
    {
        // Custom Post Meta Placeholders
        if ($context instanceof Processor && !is_null($context->get_shortcode_option('post_id'))) {
            $post_id = $context->get_shortcode_option('post_id');
            $post = get_post($post_id);

            preg_match_all('/%postmeta_(?<key>.+)%/U', $value, $postmeta_placeholders, PREG_SET_ORDER, 0);

            $value = strtr($value, [
                '%post_id%' => $post_id,
                '%post_type%' => is_a($post, 'WP_Post') ? $post->post_type : '',
                '%post_title%' => is_a($post, 'WP_Post') ? $post->post_title : '',
            ]);

            if (!empty($postmeta_placeholders)) {
                foreach ($postmeta_placeholders as $placeholder_data) {
                    $postmeta_placeholder = $placeholder_data[0];
                    $postmeta_value = get_post_meta($post_id, $placeholder_data['key'], true);
                    $value = strtr($value, [
                        $postmeta_placeholder => !empty($postmeta_value) ? $postmeta_value : '',
                    ]);
                }
            }
        }

        return [$value, $context, $extra];
    }

    public static function _general($value, $context = null, $extra = [])
    {
        // Extra Placeholders
        $value = strtr($value, [
            '%yyyy-mm-dd%' => current_time('Y-m-d'),
            '%jjjj-mm-dd%' => current_time('Y-m-d'), // Backward compatibility
            '%hh:mm%' => current_time('Hi'),
            '%ip%' => Helpers::get_user_ip(),
            '%directory_separator%' => '/',
            '%uniqueID%' => get_option('out_of_the_box_uniqueID', 0),
            '%current_url%' => Helpers::get_page_url(),
            '%site_name%' => get_bloginfo(),
        ]);

        // Location Placeholder
        if (false !== strpos($value, '%location%')) {
            // Geo location only if required
            $value = strtr($value, [
                '%location%' => Helpers::get_user_location(),
            ]);
        }

        return [$value, $context, $extra];
    }

    public static function _woocommerce($value, $context = null, $extra = [])
    {
        // WooCommerce Products Placeholders
        if (empty($extra['wc_product']) && $context instanceof Processor && !is_null($context->get_shortcode_option('wc_product_id'))) {
            $product_id = $context->get_shortcode_option('wc_product_id');

            try {
                $product = new \WC_Product($product_id);
            } catch (\Exception $ex) {
                $product = null;
            }

            if (empty($product)) {
                try {
                    $product = new \WC_Product_Variation($product_id);
                } catch (\Exception $ex) {
                    $product = null;
                }
            }

            if (!empty($product) && $product instanceof \WC_Product) {
                $extra['wc_product'] = $product;
            }
        }

        if (isset($extra['wc_product'])) {
            $value = strtr($value, [
                '%wc_product_id%' => $extra['wc_product']->get_id(),
                '%wc_product_sku%' => $extra['wc_product']->get_sku(),
                '%wc_product_name%' => $extra['wc_product']->get_name(),
            ]);

            if (isset($extra['wc_order'])) {
                $product_quantity = 0;
                foreach ($extra['wc_order']->get_items() as $item_product) {
                    if ($item_product->get_product_id() == $extra['wc_product']->get_id()) {
                        $product_quantity = $extra['wc_order']->get_item_count($item_product->get_type());
                    }
                }

                $value = strtr($value, [
                    '%wc_product_quantity%' => $product_quantity,
                ]);
            }
        }

        // WooCommerce Order Placeholders
        if (empty($extra['wc_order']) && $context instanceof Processor && !is_null($context->get_shortcode_option('wc_order_id'))) {
            try {
                $order = new \WC_Order($context->get_shortcode_option('wc_order_id'));
            } catch (\Exception $ex) {
                $order = null;
            }

            if (!empty($order) && $order instanceof \WC_Order) {
                $extra['wc_order'] = $order;
            }
        }

        if (isset($extra['wc_order'])) {
            $value = strtr($value, [
                '%wc_order_id%' => $extra['wc_order']->get_order_number(),
                '%wc_order_quantity%' => $extra['wc_order']->get_item_count(),
                '%wc_order_date_created%' => (empty($extra['wc_order']->get_date_created()) ? '' : $extra['wc_order']->get_date_created()->format('Y-m-d')),
            ]);
        }

        // WooCommerce Item Placeholders
        if (empty($extra['wc_item']) && $context instanceof Processor && !is_null($context->get_shortcode_option('wc_item_id'))) {
            try {
                $item = new \WC_Order_Item_Product($context->get_shortcode_option('wc_item_id'));
            } catch (\Exception $ex) {
                $item = null;
            }

            if (!empty($item) && $item instanceof \WC_Order_Item_Product) {
                $extra['wc_item'] = $item;
            }
        }

        if (isset($extra['wc_item'])) {
            $value = strtr($value, [
                '%wc_item_id%' => $extra['wc_item']->get_id(),
                '%wc_item_quantity%' => $extra['wc_item']->get_quantity(),
            ]);
        }

        return [$value, $context, $extra];
    }

    public static function _cookies($value, $context = null, $extra = [])
    {
        // Form Input Fields
        if ($context instanceof Processor && isset($_COOKIE['WPCP-FORM-VALUES-'.$context->get_listtoken()])) {
            $form_values = json_decode(stripslashes($_COOKIE['WPCP-FORM-VALUES-'.$context->get_listtoken()]), true);

            foreach ($form_values as $placeholder_key => $form_value) {
                $value = strtr($value, [
                    '%'.$placeholder_key.'%' => !empty($form_value) ? Helpers::filter_filename($form_value, false) : '',
                ]);
            }
        }

        return [$value, $context, $extra];
    }

    public static function _file($value, $context = null, $extra = [])
    {
        // Upload Placeholders
        if (isset($extra['file_name'])) {
            $value = strtr($value, [
                '%file_name%' => $extra['file_name'] ?? '',
                '%file_extension%' => $extra['file_extension'] ?? '',
                '%file_description%' => $extra['file_description'] ?? '',
                '%queue_index%' => $extra['queue_index'] ?? '',
            ]);
        }

        return [$value, $context, $extra];
    }
}
