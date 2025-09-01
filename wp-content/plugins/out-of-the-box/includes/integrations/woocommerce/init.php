<?php

namespace TheLion\OutoftheBox\Integrations;

defined('ABSPATH') || exit;

function load_woocommerce_addon($integrations)
{
    global $woocommerce;

    if (is_object($woocommerce) && version_compare($woocommerce->version, '3.0', '>=')) {
        $integrations[] = __NAMESPACE__.'\WooCommerce';
    }

    return $integrations;
}

add_filter('woocommerce_integrations', '\TheLion\OutoftheBox\Integrations\load_woocommerce_addon', 10);

class WooCommerce extends \WC_Integration
{
    public function __construct()
    {
        $this->id = 'outofthebox-woocommerce';
        $this->method_title = 'Dropbox';
        $this->method_description = esc_html__('Easily manage your Digital Download and Order Uploads in the cloud.', 'wpcloudplugins');

        // Add Filter to remove the default 'Guest - ' part from the Personal Folder name
        add_filter('outofthebox_private_folder_name_guests', [$this, 'rename_personal_folder_for_guests']);

        // Update shortcodes with Product ID/Order ID when available
        add_filter('outofthebox_shortcode_add_options', [$this, 'update_shortcode'], 10, 3);

        if (wp_doing_ajax() && is_wc_endpoint_url()) {
            if (!isset($_REQUEST['action']) || false === strpos($_REQUEST['action'], 'outofthebox')) {
                return false;
            }
        }

        include_once __DIR__.'/wpcp-class-wc-uploads.php';

        include_once __DIR__.'/wpcp-class-wc-downloads.php';

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();
    }

    public function rename_personal_folder_for_guests($personal_folder_name)
    {
        return str_replace(esc_html__('Guests', 'wpcloudplugins').' - ', '', $personal_folder_name);
    }

    public function update_shortcode($options, $processor, $raw_shortcode)
    {
        if (isset($raw_shortcode['wc_order_id'])) {
            $options['wc_order_id'] = $raw_shortcode['wc_order_id'];
        }

        if (isset($raw_shortcode['wc_product_id'])) {
            $options['wc_product_id'] = $raw_shortcode['wc_product_id'];
        }

        if (isset($raw_shortcode['wc_item_id'])) {
            $options['wc_item_id'] = $raw_shortcode['wc_item_id'];
        }

        return $options;
    }
}
