<?php

namespace TheLion\OutoftheBox\Integrations;

use TheLion\OutoftheBox\API;
use TheLion\OutoftheBox\App;
use TheLion\OutoftheBox\Client;
use TheLion\OutoftheBox\Helpers;
use TheLion\OutoftheBox\Placeholders;
use TheLion\OutoftheBox\Processor;
use TheLion\OutoftheBox\Settings;
use TheLion\OutoftheBox\ShortcodeBuilder;
use TheLion\OutoftheBox\Shortcodes;

class WooCommerce_Uploads
{
    public function __construct()
    {
        // Add Tabs & Content to Product Edit Page
        add_action('admin_enqueue_scripts', [$this, 'add_scripts']);
        add_filter('product_type_options', [$this, 'add_uploadable_product_option']);
        add_filter('woocommerce_product_data_tabs', [$this, 'add_product_data_tab']);
        add_action('woocommerce_product_data_panels', [$this, 'add_product_data_tab_content']);
        add_action('woocommerce_process_product_meta_simple', [$this, 'save_product_data_fields']);
        add_action('woocommerce_process_product_meta_variable', [$this, 'save_product_data_fields']);
        add_action('woocommerce_ajax_save_product_variations', [$this, 'save_product_data_fields']);
        add_action('woocommerce_process_product_meta_composite', [$this, 'save_product_data_fields']);
        add_action('woocommerce_process_product_meta_booking', [$this, 'save_product_data_fields']);
        add_action('woocommerce_process_product_meta', [$this, 'save_product_data_fields']);

        // Add Upload button to my Order Table
        add_filter('woocommerce_my_account_my_orders_actions', [$this, 'add_orders_column_actions'], 10, 2);

        // Add Upload Box where needed
        add_action('woocommerce_before_add_to_cart_button', [$this, 'render_on_product_page']);
        add_action('woocommerce_after_cart_item_name', [$this, 'render_on_cart_page']);
        add_action(Settings::get('woocommerce_checkout_section', 'woocommerce_after_order_notes'), [$this, 'render_on_checkout_page']);
        add_action('woocommerce_order_item_meta_end', [$this, 'render_on_order_page'], 10, 4);

        // Move temporarily stored uploads to the correct location after checkout
        add_filter('outofthebox_upload_post_process_data', [$this, 'save_temporary_uploads'], 10, 1);
        add_action('woocommerce_checkout_update_order_meta', [$this, 'move_session_uploads'], 99, 2);

        // Add Upload Box to Admin Order Page
        add_action('woocommerce_admin_order_item_headers', [$this, 'admin_order_item_headers'], 10, 1);
        add_action('woocommerce_admin_order_item_values', [$this, 'admin_order_item_values'], 10, 3);

        // Add link to upload box in the Thank You text
        add_filter('woocommerce_thankyou_order_received_text', [$this, 'change_order_received_text'], 10, 2);

        // Add Order note when uploading files
        add_action('outofthebox_upload_post_process', [$this, 'add_order_note'], 10, 1);

        // AJAX calls to load the list of uploaded files
        add_action('outofthebox_start_process', [$this, 'get_item_details'], 10, 2);

        // Remove the userfoldernametemplate field for WooCommerce as it has its own
        add_action('outofthebox_before_shortcode_builder', [$this, 'modify_shortcode_builder'], 10, 4);
    }

    /**
     * Add an order note when files are uploaded.
     *
     * @param array $_uploaded_entries
     */
    public function add_order_note($_uploaded_entries)
    {
        // Grab the Order/Product data from the shortcode
        $order_id = Processor::instance()->get_shortcode_option('wc_order_id');
        $product_id = Processor::instance()->get_shortcode_option('wc_product_id');

        if (empty($order_id) || empty($product_id)) {
            return;
        }

        $order = new \WC_Order($order_id);

        if (empty($order)) {
            return;
        }

        $product = wc_get_product($product_id);

        // Make sure that we are working with an array
        $uploaded_entries = [];
        if (!is_array($_uploaded_entries)) {
            $uploaded_entries[] = $_uploaded_entries;
        } else {
            $uploaded_entries = $_uploaded_entries;
        }

        // Build the Order note
        $order_note = sprintf(esc_html__('%d file(s) uploaded for product', 'wpcloudplugins'), count((array) $uploaded_entries)).' <strong>'.$product->get_title().'</strong>:';
        $order_note .= '<br/><br/><ul>';

        foreach ($uploaded_entries as $entry) {
            $link = (Client::instance()->has_shared_link($entry)) ? Client::instance()->get_shared_link($entry).'&dl=0' : 'javascript:void(0)';
            $name = $entry->get_name();
            $size = Helpers::bytes_to_size_1024($entry->get_size());

            $order_note .= '<li><a href="'.$link.'">'.$name.'</a> ('.$size.')</li>';
        }

        $order_note .= '</ul>';

        // Add the note
        $note = [
            'note' => $order_note,
            'is_customer_note' => false,
            'added_by_user' => false,
        ];

        $note = apply_filters('outofthebox_woocommerce_add_order_note', $note, $uploaded_entries, $order, $product, $this);
        $order->add_order_note($note['note'], $note['is_customer_note'], $note['added_by_user']);

        // Save the data
        $order->save();
    }

    /**
     * Add link to upload box in the Thank You text.
     *
     * @param string    $thank_you_text
     * @param \WC_Order $order
     *
     * @return string
     */
    public function change_order_received_text($thank_you_text, $order)
    {
        if (false === $this->requires_order_uploads($order) || false === $this->is_upload_location_active('order', true)) {
            return $thank_you_text;
        }

        $order_url = $order->get_view_order_url().'#wpcp-uploads';
        $custom_text = ' '.esc_html__('You can now start uploading documents for the products you have ordered.', 'wpcloudplugins');
        $thank_you_text .= apply_filters('outofthebox_woocommerce_thank_you_text', $custom_text, $order, $this);

        return $thank_you_text;
    }

    /**
     * Add new Product Type to the Product Data Meta Box.
     *
     * @param array $product_type_options
     *
     * @return array
     */
    public function add_uploadable_product_option($product_type_options)
    {
        $product_type_options['uploadable'] = [
            'id' => '_uploadable',
            'wrapper_class' => 'show_if_simple show_if_variable show_if_booking show_if_woosb',
            'label' => esc_html__('Uploads', 'wpcloudplugins'),
            'description' => esc_html__('Allows your customers to upload files when ordering this product.', 'wpcloudplugins'),
            'default' => 'no',
        ];

        return $product_type_options;
    }

    /**
     * Add new Data Tab to the Product Data Meta Box.
     *
     * @param array $product_data_tabs
     *
     * @return array
     */
    public function add_product_data_tab($product_data_tabs)
    {
        $product_data_tabs['cloud-uploads-dropbox'] = [
            'label' => sprintf(esc_html__('Upload to %s', 'wpcloudplugins'), 'Dropbox'),
            'target' => 'cloud_uploads_data_dropbox',
            'class' => ['show_if_uploadable'],
        ];

        return $product_data_tabs;
    }

    /**
     * Add the content of the new Data Tab.
     */
    public function add_product_data_tab_content()
    {
        global $post;

        $default_shortcode = '[outofthebox mode="files" viewrole="all" userfolders="auto" downloadrole="all" upload="1" uploadrole="all" rename="1" renamefilesrole="all" renamefoldersrole="all" editdescription="1" editdescriptionrole="all" delete="1" deletefilesrole="all" deletefoldersrole="all" viewuserfoldersrole="none" search="0" showbreadcrumb="0"]';
        $shortcode = get_post_meta($post->ID, 'outofthebox_upload_box_shortcode', true); ?>
<div id='cloud_uploads_data_dropbox' class='panel woocommerce_options_panel' style="display:none">
    <div class="cloud_uploads_data_panel options_group">
        <?php
            woocommerce_wp_checkbox(
                [
                    'id' => 'outofthebox_upload_box',
                    'label' => sprintf(esc_html__('Upload to %s', 'wpcloudplugins'), 'Dropbox'),
                ]
            ); ?>
        <div class="show_if_outofthebox_upload_box">
            <h4><?php echo 'Dropbox '.esc_html__('Upload Box Settings', 'wpcloudplugins'); ?></h4>
            <?php $default_box_description = '';
        $box_description = get_post_meta($post->ID, 'outofthebox_upload_box_description', true);

        woocommerce_wp_textarea_input(
            [
                'id' => 'outofthebox_upload_box_description',
                'label' => esc_html__('Description Upload Box', 'wpcloudplugins'),
                'placeholder' => $default_box_description,
                'desc_tip' => true,
                'description' => esc_html__('Enter a short description of what the customer needs to upload', 'wpcloudplugins').'. '.sprintf(esc_html__('See %s for available placeholders', 'wpcloudplugins'), '<strong><u>'.esc_html__('Upload Folder Name', 'wpcloudplugins').'</u></strong>').'. '.esc_html__('Shortcodes are supported', 'wpcloudplugins').'.',
                'value' => empty($box_description) ? $default_box_description : $box_description,
            ]
        );

        $default_box_button_text = esc_html__('Upload documents', 'wpcloudplugins');
        $box_button_text = get_post_meta($post->ID, 'outofthebox_upload_box_button_text', true);

        woocommerce_wp_text_input(
            [
                'id' => 'outofthebox_upload_box_button_text',
                'label' => esc_html__('Upload Button Text', 'wpcloudplugins'),
                'placeholder' => $default_box_button_text,
                'desc_tip' => true,
                'description' => esc_html__('Enter the text for the upload button.', 'wpcloudplugins').' '.sprintf(esc_html__('See %s for available placeholders', 'wpcloudplugins'), '<strong><u>'.esc_html__('Upload Folder Name', 'wpcloudplugins').'</u></strong>').'. '.esc_html__('Shortcodes are supported', 'wpcloudplugins').'.',
                'value' => empty($box_button_text) ? $default_box_button_text : $box_button_text,
            ]
        );

        ?>
            <p class="form-field outofthebox_upload_folder ">
                <label for="outofthebox_upload_folder"><?php esc_html_e('Upload Configuration', 'wpcloudplugins'); ?></label>
                <button type="button" class="button wpcp-insert-dropbox-shortcode button-primary" style="float:none"><?php esc_html_e('Configure', 'wpcloudplugins'); ?></button>
                <button type="button" class="button" style="float:none" onclick="jQuery('#outofthebox_upload_box_shortcode').fadeToggle()"><?php esc_html_e('Edit manually', 'wpcloudplugins'); ?></button>
                <textarea class="long" style="display:none; margin:10px 0px;" name="outofthebox_upload_box_shortcode" id="outofthebox_upload_box_shortcode" placeholder="<?php echo $default_shortcode; ?>" rows="5" cols="20"><?php echo (empty($shortcode)) ? $default_shortcode : $shortcode; ?></textarea>
            </p>

            <?php
                      $default_folder_template = '%wc_order_id% (%user_email%)/%wc_product_name%';
        $folder_template = get_post_meta($post->ID, 'outofthebox_upload_box_folder_template', true);

        woocommerce_wp_text_input(
            [
                'id' => 'outofthebox_upload_box_folder_template',
                'label' => esc_html__('Upload Folder Name', 'wpcloudplugins'),
                'description' => esc_html__('Unique folder name where the uploads should be stored. Make sure that Personal Folder feature is enabled in the shortcode', 'wpcloudplugins').'. '.sprintf(esc_html__('Available placeholders: %s', 'wpcloudplugins'), '<code>%wc_order_id%</code>, <code>%wc_order_date_created%</code>, <code>%wc_order_quantity%</code>, <code>%wc_product_id%</code>, <code>%wc_product_sku%</code>, <code>%wc_product_quantity%</code>, <code>%wc_product_name%</code>, <code>%wc_item_id%</code>, <code>%wc_item_quantity%</code>, <code>%user_login%</code>, <code>%user_email%</code>, <code>%display_name%</code>, <code>%ID%</code>, <code>%user_role%</code>, <code>%usermeta_{key}%</code>, <code>%date_{date_format}%</code>, <code>%date_i18n_{date_format}%</code>, <code>%yyyy-mm-dd%</code>, <code>%directory_separator%</code>'),
                'desc_tip' => false,
                'placeholder' => $default_folder_template,
                'value' => empty($folder_template) ? $default_folder_template : $folder_template,
            ]
        );

        $upload_locations = [
            'product' => ['title' => esc_html__('Product Page', 'wpcloudplugins')],
            'cart' => ['title' => esc_html__('Cart', 'wpcloudplugins')],
            'checkout' => ['title' => esc_html__('Checkout', 'wpcloudplugins')],
            'order' => ['title' => esc_html__('Order Details', 'wpcloudplugins')],
        ];
        ?>

            <fieldset class="form-field outofthebox_upload_location ">
                <legend><?php esc_html_e('Upload Box Locations', 'wpcloudplugins'); ?></legend>
                <ul class="wc-radios">
                    <?php foreach ($upload_locations as $upload_location_key => $upload_location) {
                        $checked = $this->is_upload_location_active($upload_location_key) ? 'checked="checked"' : ''; ?>
                    <li>
                        <label>
                            <input type="checkbox" class="select short" disabled="disabled" <?php echo $checked; ?> />
                            <?php echo $upload_location['title']; ?>
                        </label>
                    </li>
                    <?php } ?>
                </ul>
                <?php echo wc_help_tip(esc_html__('You can control this setting from the main plugin options page (Integrations > WooCommerce).', 'wpcloudplugins')); ?>
            </fieldset>
            <?php

        $outofthebox_upload_box_active_on_status = get_post_meta($post->ID, 'outofthebox_upload_box_active_on_status', true);
        if (empty($outofthebox_upload_box_active_on_status) || !is_array($outofthebox_upload_box_active_on_status)) {
            $outofthebox_upload_box_active_on_status = ['wc-pending', 'wc-processing'];
        }

        $this->woocommerce_wp_multi_checkbox([
            'id' => 'outofthebox_upload_box_active_on_status',
            'name' => 'outofthebox_upload_box_active_on_status[]',
            'label' => esc_html__(''
                    .'Show when Order is', 'woocommerce'),
            'options' => wc_get_order_statuses(),
            'value' => $outofthebox_upload_box_active_on_status,
        ]); ?>

        </div>
    </div>
</div><?php
    }

    /**
     * New Multi Checkbox field for woocommerce backend.
     *
     * @param mixed $field
     */
    public function woocommerce_wp_multi_checkbox($field)
    {
        global $thepostid, $post;

        $thepostid = empty($thepostid) ? $post->ID : $thepostid;
        $field['class'] = $field['class'] ?? 'select short';
        $field['style'] = $field['style'] ?? '';
        $field['wrapper_class'] = $field['wrapper_class'] ?? '';
        $field['value'] = $field['value'] ?? get_post_meta($thepostid, $field['id'], true);
        $field['cbvalue'] = $field['cbvalue'] ?? 'yes';
        $field['name'] = $field['name'] ?? $field['id'];
        $field['desc_tip'] = $field['desc_tip'] ?? false;

        echo '<fieldset class="form-field '.esc_attr($field['id']).'_field '.esc_attr($field['wrapper_class']).'">
    <legend>'.wp_kses_post($field['label']).'</legend>';

        if (!empty($field['description']) && false !== $field['desc_tip']) {
            echo wc_help_tip($field['description']);
        }

        echo '<ul class="wc-radios">';

        foreach ($field['options'] as $key => $value) {
            echo '<li><label><input type="checkbox" class="'.esc_attr($field['class']).'" style="'.esc_attr($field['style']).'" name="'.esc_attr($field['name']).'" value="'.esc_attr($key).'" '.(in_array($key, $field['value']) ? 'checked="checked"' : '').' /> '.esc_html($value).'</label></li>';
        }
        echo '</ul>';

        if (!empty($field['description']) && false === $field['desc_tip']) {
            echo '<span class="description">'.wp_kses_post($field['description']).'</span>';
        }

        echo '</fieldset>';
    }

    /**
     * Add the scripts and styles required for the new Data Tab.
     */
    public function add_scripts()
    {
        $current_screen = get_current_screen();

        if (!in_array($current_screen->id, ['product', 'shop_order'])) {
            return;
        }
        wp_register_style('outofthebox-woocommerce', plugins_url('backend.css', __FILE__), OUTOFTHEBOX_VERSION);
        wp_register_script('outofthebox-woocommerce', plugins_url('backend.js', __FILE__), ['jquery'], OUTOFTHEBOX_VERSION);

        wp_enqueue_style('outofthebox-woocommerce');
        wp_enqueue_script('outofthebox-woocommerce');

        // register translations
        $translation_array = [
            'choose_from' => sprintf(esc_html__('Add File', 'wpcloudplugins'), 'Dropbox'),
            'download_url' => '?action=outofthebox-wc-direct-download&id=',
            'file_browser_url' => OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-getwoocommercepopup',
            'wcpd_url' => OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-wcpd-direct-download&id=',
        ];

        wp_localize_script('outofthebox-woocommerce', 'outofthebox_woocommerce_translation', $translation_array);
    }

    /**
     * Save the new added input fields properly.
     *
     * @param int $post_id
     */
    public function save_product_data_fields($post_id)
    {
        $is_uploadable = isset($_POST['_uploadable']) ? 'yes' : 'no';
        update_post_meta($post_id, '_uploadable', $is_uploadable);

        $outofthebox_upload_box = isset($_POST['outofthebox_upload_box']) ? 'yes' : 'no';
        update_post_meta($post_id, 'outofthebox_upload_box', $outofthebox_upload_box);

        if (isset($_POST['outofthebox_upload_box_description'])) {
            update_post_meta($post_id, 'outofthebox_upload_box_description', $_POST['outofthebox_upload_box_description']);
        }

        if (isset($_POST['outofthebox_upload_box_button_text'])) {
            update_post_meta($post_id, 'outofthebox_upload_box_button_text', $_POST['outofthebox_upload_box_button_text']);
        }

        if (isset($_POST['outofthebox_upload_box_shortcode'])) {
            update_post_meta($post_id, 'outofthebox_upload_box_shortcode', $_POST['outofthebox_upload_box_shortcode']);
        }

        if (isset($_POST['outofthebox_upload_box_folder_template'])) {
            update_post_meta($post_id, 'outofthebox_upload_box_folder_template', $_POST['outofthebox_upload_box_folder_template']);
        }

        if (isset($_POST['outofthebox_upload_box_active_on_status'])) {
            $post_data = $_POST['outofthebox_upload_box_active_on_status'];
            // Data sanitization
            $sanitize_data = [];
            if (is_array($post_data) && sizeof($post_data) > 0) {
                foreach ($post_data as $value) {
                    $sanitize_data[] = esc_attr($value);
                }
            }
            update_post_meta($post_id, 'outofthebox_upload_box_active_on_status', $sanitize_data);
        } else {
            update_post_meta($post_id, 'outofthebox_upload_box_active_on_status', ['wc-pending', 'wc-processing']);
        }
    }

    /**
     * Add an 'Upload' Action to the Order Table.
     *
     * @param array $actions
     *
     * @return array
     */
    public function add_orders_column_actions($actions, \WC_Order $order)
    {
        if (false === $this->is_upload_location_active('order', true)) {
            return $actions;
        }

        $box_button_text = esc_html__('Upload documents', 'wpcloudplugins');

        if ($this->requires_order_uploads($order)) {
            foreach ($order->get_items() as $order_item) {
                $product = $this->get_product($order_item);

                $requires_upload = $this->requires_product_uploads($product, $order);

                if ($requires_upload) {
                    if ($this->is_product_variation($product)) {
                        $product = wc_get_product($product->get_parent_id());
                    }

                    $box_button_text = get_post_meta($product->get_id(), 'outofthebox_upload_box_button_text', true);

                    break;
                }
            }

            $actions['upload'] = [
                'url' => $order->get_view_order_url().'#wpcp-uploads',
                'name' => $box_button_text,
            ];
        }

        return $actions;
    }

    /**
     * Add a custom column on the Admin Order Page.
     *
     * @param mixed $order
     */
    public function admin_order_item_headers($order)
    {
        if (false === $this->requires_order_uploads($order)) {
            return false;
        }

        // set the column name
        $column_name = esc_html__('Uploaded documents', 'wpcloudplugins');

        // display the column name
        echo '<th>'.$column_name.'</th>';
    }

    /**
     * Add the value for the custom column on the Admin Order Page.
     *
     * @param mixed      $_product
     * @param mixed      $item
     * @param null|mixed $item_id
     */
    public function admin_order_item_values($_product, $item, $item_id = null)
    {
        if (false === $this->requires_order_uploads($item->get_order())) {
            return false;
        }

        if (false === $this->requires_product_uploads($_product, $item->get_order())) {
            echo '<td></td>';

            return;
        }

        echo '<td>';
        echo $this->render_upload_field($item->get_id(), $item, $item->get_order(), null);
        echo '</td>';
    }

    /**
     * Check if the upload box should be rendered on a location.
     *
     * @param string $location
     * @param bool   $default
     */
    public function is_upload_location_active($location, $default = false)
    {
        $upload_locations = Settings::get('woocommerce_upload_locations');

        if (empty($upload_locations[$location])) {
            return $default;
        }

        if ('No' === $upload_locations[$location]) {
            return false;
        }

        return true;
    }

    /**
     * Render the Upload Box on the Product Page.
     */
    public function render_on_product_page()
    {
        if (false === $this->is_upload_location_active('product')) {
            return;
        }

        global $product;

        $this->render_upload_field_temporarily_location($product);
    }

    /**
     * @param mixed $cart_item
     */
    public function render_on_cart_page($cart_item)
    {
        if (false === $this->is_upload_location_active('cart')) {
            return;
        }

        $product_id = $cart_item['product_id'];
        $product = wc_get_product($product_id);

        $this->render_upload_field_temporarily_location($product);
    }

    public function render_on_checkout_page()
    {
        if (false === $this->is_upload_location_active('checkout')) {
            return;
        }

        $cart_items = WC()->cart->get_cart();

        if (empty($cart_items)) {
            return;
        }

        // Render the upload box for each product in the cart
        ob_start();
        foreach ($cart_items as $cart_item_key => $cart_item) {
            $product_id = $cart_item['product_id'];
            $product = wc_get_product($product_id);

            // Check if product requires uploads
            if (false === $this->requires_product_uploads($product, null)) {
                continue;
            }

            $product_name = $product->get_name();
            $product_permalink = get_permalink($product_id);

            echo '<strong>'.esc_html__('Product', 'wpcloudplugins').':</strong> <a href="'.esc_url($product_permalink).'" target="_blank">'.esc_html($product_name).'</a><br>';
            $this->render_upload_field_temporarily_location($product);
        }
        $uploads_html = ob_get_clean();

        if (!empty($uploads_html)) {
            echo '<h3>'.__('Uploads', 'wpcloudplugins').'</h3>';

            echo $uploads_html;
        }
    }

    /**
     * Render the Upload Box on the Order View.
     *
     * @param mixed $item_id
     * @param mixed $item
     * @param mixed $order
     * @param bool  $plain_text
     */
    public function render_on_order_page($item_id, $item, $order, $plain_text = false)
    {
        if ($this->is_upload_location_active('order', true)) {
            $this->render_upload_field($item_id, $item, $order, $plain_text);
        }
    }

    /**
     * Render a simplified Upload Box before order is placed.
     *
     * @param mixed $originial_product
     */
    public function render_upload_field_temporarily_location($originial_product)
    {
        // Check if product requires uploads
        if (false === $this->requires_product_uploads($originial_product, null)) {
            return;
        }

        // Check if there is a temporarily upload location set
        $temporarily_upload_folder_data = Settings::get('woocommerce_temporary_upload_folder');
        if (empty($temporarily_upload_folder_data['account']) || empty($temporarily_upload_folder_data['id'])) {
            Helpers::log_error('A temporary folder location has not been selected, so upload cannot be enabled before checkout.', 'WooCommerce', null, __LINE__);

            return;
        }

        // Make sure that we have a WC Session with cookie and unique customer ID
        if (WC()->session && !WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
        }

        wp_register_style('outofthebox-woocommerce-frontend-css', plugins_url('frontend.css', __FILE__), ['Eva-Icons'], OUTOFTHEBOX_VERSION);
        wp_enqueue_style('outofthebox-woocommerce-frontend-css');

        /** Select the product that contains the information * */
        $meta_product = $originial_product;
        if ($this->is_product_variation($originial_product)) {
            $meta_product = wc_get_product($originial_product->get_parent_id());
        }

        $shortcode = get_post_meta($meta_product->get_id(), 'outofthebox_upload_box_shortcode', true);
        $shortcode_params = Shortcodes::parse_attributes($shortcode, true);
        $shortcode_params['wc_product_id'] = $originial_product->get_id();
        $shortcode_params['post_id'] = $originial_product->get_id();
        $shortcode_params['maxheight'] = '40vh';
        $shortcode_params['mode'] = 'upload';
        $shortcode_params['downloadrole'] = 'none';
        $shortcode_params['upload'] = '1';
        $shortcode_params['delete'] = '1';
        $shortcode_params['editdescription'] = '1';
        $shortcode_params['popup'] = 'wpcp_wc_temporarily_upload';
        $shortcode_params['userfoldernametemplate'] = date_i18n('Y').'/'.date_i18n('m').'/'.date_i18n('Ymd').' - '.WC()->session->get_customer_id().' - #'.$originial_product->get_id();
        $shortcode_params['account'] = $temporarily_upload_folder_data['account'];
        $shortcode_params['dir'] = $temporarily_upload_folder_data['id'];
        $shortcode_params['userfolders'] = 'auto';
        $shortcode_params['viewuserfoldersrole'] = 'none';

        $show_box = apply_filters('outofthebox_woocommerce_show_upload_field', true, null, $originial_product, $this);

        if ($show_box) {
            if (empty($shortcode_params['dir'])) {
                echo "<button class='woocommerce-button button' disabled='disabled'><i class='eva eva-alert-triangle-outline eva-lg'></i> ".esc_html__('Upload not available.', 'wpcloudplugins').'</button>';
                echo '<br/><em>'.((Helpers::check_user_role(['administrator'])) ? esc_html__('Please configure the upload location for this product.', 'wpcloudplugins') : esc_html__('Please get in touch with us to resolve this issue.', 'wpcloudplugins')).'</em>';
            } else {
                do_action('outofthebox_woocommerce_before_render_upload_field', null, $originial_product, $this);

                $this->prefill_temporarily_uploads($originial_product->get_id());

                echo Shortcodes::do_shortcode($shortcode_params);

                $upload_session = $this->get_temporary_session($originial_product->get_id());

                if (empty($upload_session)) {
                    WC()->session->set('wpcp_outofthebox_uploads_for_'.$originial_product->get_id(), [
                        'listtoken' => Processor::instance()->get_listtoken(),
                        'account_id' => $shortcode_params['account'] ?? App::get_current_account()->get_id(),
                        'folder_id' => $shortcode_params['dir'],
                        'folder_template' => $shortcode_params['userfoldernametemplate'],
                        'uploads' => [],
                    ]);
                }

                do_action('outofthebox_woocommerce_after_render_upload_field', null, $originial_product, $this);
            }
        }
    }

    /*
     * Render the Upload Box on the Order View.
     *
     * @param mixed $item_id
     * @param mixed $item
     * @param mixed $order
     * @param bool  $plain_text
     */
    public function render_upload_field($item_id, $item, $order, $plain_text = false)
    {
        $originial_product = $this->get_product($item);

        if (false === $this->requires_product_uploads($originial_product, $order)) {
            return;
        }

        wp_register_style('outofthebox-woocommerce-frontend-css', plugins_url('frontend.css', __FILE__), ['Eva-Icons'], OUTOFTHEBOX_VERSION);
        wp_enqueue_style('outofthebox-woocommerce-frontend-css');

        /** Select the product that contains the information * */
        $meta_product = $originial_product;
        if ($this->is_product_variation($originial_product)) {
            $meta_product = wc_get_product($originial_product->get_parent_id());
        }

        $box_description = get_post_meta($meta_product->get_id(), 'outofthebox_upload_box_description', true);
        $box_button_text = get_post_meta($meta_product->get_id(), 'outofthebox_upload_box_button_text', true);
        $shortcode = get_post_meta($meta_product->get_id(), 'outofthebox_upload_box_shortcode', true);
        $folder_template = get_post_meta($meta_product->get_id(), 'outofthebox_upload_box_folder_template', true);
        $upload_active_on = get_post_meta($meta_product->get_id(), 'outofthebox_upload_box_active_on_status', true);
        if (empty($upload_active_on) || !is_array($upload_active_on)) {
            $upload_active_on = ['wc-pending', 'wc-processing'];
        }
        $upload_active = in_array('wc-'.$order->get_status(), $upload_active_on);

        if (empty($box_button_text)) {
            $box_button_text = esc_html__('Upload documents', 'wpcloudplugins');
        }

        // Don't include upload box in email notifications
        $is_sending_mail = doing_action('woocommerce_email_order_details');

        if ($is_sending_mail || (!is_wc_endpoint_url() && !is_admin())) {
            $order_url = $order->get_view_order_url()."#wpcp-outofthebox-uploads-{$item_id}";

            if ($upload_active) {
                $email_upload_message = '<br/><small>'.sprintf(esc_html__('You can upload your documents on the %sorder page%s', 'wpcloudplugins'), '<a href="'.$order_url.'">', '</a>').'.</small>';
                echo apply_filters('outofthebox_woocommerce_email_upload_message', $email_upload_message, $order, $item, $this);
            }

            return;
        }

        $shortcode_params = Shortcodes::parse_attributes($shortcode, true);
        $shortcode_params['userfoldernametemplate'] = $this->set_placeholders($folder_template, $item, $order, $originial_product);
        $shortcode_params['wc_order_id'] = $order->get_id();
        $shortcode_params['wc_product_id'] = $originial_product->get_id();
        $shortcode_params['wc_item_id'] = $item->get_id();
        $shortcode_params['maxheight'] = '40vh';

        // When Upload box isn't active, change it to a view only file browser
        if (false === $upload_active) {
            $shortcode_params['mode'] = 'files';
            $shortcode_params['upload'] = '0';
            $shortcode_params['delete'] = '0';
            $shortcode_params['rename'] = '0';
            $shortcode_params['candownloadzip'] = '1';
            $shortcode_params['editdescription'] = '0';
        }

        $show_box = apply_filters('outofthebox_woocommerce_show_upload_field', true, $order, $originial_product, $this);

        $is_admin_page = is_admin();
        if ($is_admin_page) {
            // Always show the File Browser mode in the Dashboard

            $shortcode_params['showbreadcrumb'] = '1';
            $shortcode_params['mode'] = 'files';
            $shortcode_params['candownloadzip'] = '1';
            $shortcode_params['viewuserfoldersrole'] = 'none';

            // Meta Box is located inside Form tag, so force the plugin to start the update
            $shortcode_params['class'] = (isset($shortcode_params['class']) ? $shortcode_params['class'].' auto_upload' : 'auto_upload');

            $show_box = true;
        }

        if ($show_box) {
            $module_incomplete_configuration = false;

            echo "<div id='wpcp-outofthebox-uploads-{$item_id}' class='wpcp-outofthebox wpcp-upload-container ".($is_admin_page ? 'wpcp-uploads-container-admin' : '')."' data-item-id='{$item_id}' >";

            // Don't show the upload box when there isn't select a root folder
            if ($module_incomplete_configuration) {
                echo "<button class='woocommerce-button button' disabled='disabled'><i class='eva eva-alert-triangle-outline eva-lg'></i> ".esc_html__('Upload not available.', 'wpcloudplugins').'</button>';
                echo '<br/><em>'.(($is_admin_page) ? esc_html__('Please configure the upload location for this product.', 'wpcloudplugins') : esc_html__('Please get in touch with us to resolve this issue.', 'wpcloudplugins')).'</em>';
            } else {
                wp_register_script('outofthebox-woocommerce-frontend', plugins_url('frontend.js', __FILE__), ['jquery'], OUTOFTHEBOX_VERSION);
                wp_enqueue_script('outofthebox-woocommerce-frontend');

                // Upload button
                echo "<a class='woocommerce-button button wpcp-wc-open-box'><i class='eva eva-attach eva-lg'></i> ".(($is_admin_page) ? esc_html__('View documents', 'wpcloudplugins') : $box_button_text).'</a>';

                // Placeholder for list of uploaded files in folder. Content is loaded dynamically via AJAX
                echo '<ul class="wpcp-uploads-list"><div class="loading initialize"><svg class="loader-spinner" viewBox="25 25 50 50"><circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10"></circle></svg></div></ul>';

                // Upload Box
                echo "<div class='woocommerce-order-upload-box' style='display:none;' data-order='".esc_attr__('Order #', 'woocommerce').$order->get_order_number()."' data-product-name='".esc_attr($meta_product->get_name())."' data-title='".esc_html(($is_admin_page) ? esc_html__('View documents', 'wpcloudplugins') : $box_button_text)."'>";

                do_action('outofthebox_woocommerce_before_render_upload_field', $order, $originial_product, $this);

                echo Shortcodes::do_shortcode($shortcode_params);

                do_action('outofthebox_woocommerce_after_render_upload_field', $order, $originial_product, $this);

                // Description Box
                if (!empty($box_description)) {
                    echo '<div class="wpcp-modal-badge"><i class="eva eva-menu-outline eva-lg"></i> '.esc_html__('Description', 'wpcloudplugins').'</div>';
                    echo do_shortcode('<p>'.$this->set_placeholders($box_description, $item, $order, $originial_product).'</p>');
                }

                echo '</div>';
            }
            echo '</div>';
        }
    }

    public function get_temporary_session($product_id)
    {
        $upload_session = WC()->session->get('wpcp_outofthebox_uploads_for_'.$product_id, []);

        if (empty($upload_session)) {
            return null;
        }

        return $upload_session;
    }

    public function prefill_temporarily_uploads($product_id)
    {
        $upload_session = $this->get_temporary_session($product_id);

        if (empty($upload_session) || empty($upload_session['uploads'])) {
            return;
        }

        $prefill = [];

        foreach ($upload_session['uploads'] as $entry_id => $data) {
            $prefill[$entry_id] = [
                'hash' => $entry_id,
                'account_id' => $data['account_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'type' => $data['type'],
                'path' => $data['relative_path'],
                'absolute_path' => $data['absolute_path'],
                'size' => $data['filesize'],
                'preview_url' => $data['preview_url'],
                'shared_url' => $data['shared_url'],
                'folder_absolute_path' => $data['folder_absolute_path'],
                'folder_relative_path' => $data['folder_relative_path'],
                'folder_preview_url' => $data['folder_preview_url'],
                'folder_shared_url' => $data['folder_shared_url'],
            ];
        }

        $_REQUEST['fileupload-filelist_'.$upload_session['listtoken']] = json_encode($prefill);
    }

    public function save_temporary_uploads($files)
    {
        if (
            'upload' === Processor::instance()->get_shortcode_option('mode')
             && 'wpcp_wc_temporarily_upload' === Processor::instance()->get_shortcode_option('popup')
        ) {
            $product_id = Processor::instance()->get_shortcode_option('wc_product_id');

            if (!empty($product_id)) {
                $upload_session = $this->get_temporary_session($product_id);

                if (!empty($upload_session)) {
                    $upload_session['listtoken'] = Processor::instance()->get_listtoken();
                    $upload_session['uploads'] = array_merge($upload_session['uploads'], $files);

                    WC()->session->set('wpcp_outofthebox_uploads_for_'.$product_id, $upload_session);
                }
            }
        }

        return $files;
    }

    /**
     * Move the uploaded files to the correct location.
     *
     * @param mixed $order_id
     * @param mixed $posted_data
     */
    public function move_session_uploads($order_id, $posted_data)
    {
        $order = wc_get_order($order_id);

        if (false === ($order instanceof \WC_Order)) {
            return false;
        }

        foreach ($order->get_items() as $item_id => $order_item) {
            $originial_product = $this->get_product($order_item);

            $requires_upload = $this->requires_product_uploads($originial_product, $order);

            if (!$requires_upload) {
                continue;
            }

            /** Select the product that contains the information * */
            $meta_product = $originial_product;
            if ($this->is_product_variation($originial_product)) {
                $meta_product = wc_get_product($originial_product->get_parent_id());
            }

            // Get the upload data from the session
            $upload_data = $this->get_temporary_session($originial_product->get_id());

            if (empty($upload_data)) {
                continue;
            }

            // Get the location of the temporarily folder
            App::set_current_account_by_id($upload_data['account_id']);
            $temporarily_folder = API::get_sub_folder_by_path($upload_data['folder_id'], $upload_data['folder_template']);

            // Move the files to the correct location
            if (!empty($temporarily_folder)) {
                $shortcode = get_post_meta($meta_product->get_id(), 'outofthebox_upload_box_shortcode', true);
                $folder_template = get_post_meta($meta_product->get_id(), 'outofthebox_upload_box_folder_template', true);

                $shortcode_params = Shortcodes::parse_attributes($shortcode, true);
                $folder_template = $this->set_placeholders($folder_template, $order_item, $order, $originial_product);

                App::set_current_account_by_id($shortcode_params['account']);
                $order_folder = API::get_sub_folder_by_path($shortcode_params['dir'], $folder_template, true);
                API::move_folder_content($temporarily_folder->get_id(), $order_folder->get_id());

                // Delete the temp folder
                $request = ['entries' => []];
                $request['entries'][] = ['path' => $temporarily_folder->get_id()];

                API::delete($request, false);
            }

            // Unset session data
            WC()->session->__unset('wpcp_outofthebox_uploads_for_'.$originial_product->get_id());
        }
    }

    /**
     * Checks if the order uses this upload functionality.
     *
     * @param \WC_Order $order
     *
     * @return bool
     */
    public function requires_order_uploads($order)
    {
        if (false === ($order instanceof \WC_Order)) {
            return false;
        }

        foreach ($order->get_items() as $order_item) {
            $product = $this->get_product($order_item);
            $requires_upload = $this->requires_product_uploads($product, $order);

            if ($requires_upload) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the product uses this upload functionality.
     *
     * @param \WC_Product $product
     * @param null|mixed  $order
     *
     * @return bool
     */
    public function requires_product_uploads($product = null, $order = null)
    {
        if (empty($product) || !($product instanceof \WC_Product)) {
            return false;
        }

        if ($this->is_product_variation($product)) {
            $product = wc_get_product($product->get_parent_id());
        }

        $_uploadable = get_post_meta($product->get_id(), '_uploadable', true);

        if ('yes' !== $_uploadable) {
            return false;
        }

        $_outofthebox_upload_box = get_post_meta($product->get_id(), 'outofthebox_upload_box', true);

        if ('yes' !== $_outofthebox_upload_box) {
            return false;
        }

        if (!empty($order)) {
            $upload_active_on = get_post_meta($product->get_id(), 'outofthebox_upload_box_active_on_status', true);
            if (empty($upload_active_on) || !is_array($upload_active_on)) {
                $upload_active_on = ['wc-pending', 'wc-processing'];
            }

            $upload_active = in_array('wc-'.$order->get_status(), $upload_active_on);
        } else {
            $upload_active = true;
        }

        if (\is_admin()) {
            $current_screen = \get_current_screen();
            if (!empty($current_screen) && in_array($current_screen->post_type, ['shop_order'])) {
                $upload_active = true;
            } elseif (isset($_REQUEST['type']) || 'wc-item-details' !== $_REQUEST['type']) {
                $upload_active = true;
            }
        }

        $show_upload_box = apply_filters('outofthebox_woocommerce_show_upload_field', $upload_active, $order, $product, $this);

        if ($show_upload_box) {
            return true;
        }

        return false;
    }

    /**
     * Loads the product or its parent product in case of a variation.
     *
     * @param type $order_item
     *
     * @return \WC_Product
     */
    public function get_product($order_item)
    {
        $product = $order_item->get_product();

        if (empty($product) || !($product instanceof \WC_Product)) {
            return false;
        }

        return $product;
    }

    /**
     * Check if product is a variation
     * Upload meta data is currently only stored on the parent product.
     *
     * @param mixed $product
     *
     * @return bool
     */
    public function is_product_variation($product)
    {
        $product_type = $product->get_type();

        return 'variation' === $product_type;
    }

    /**
     * Fill the placeholders with the User/Product/Order information.
     *
     * @param string $template
     *
     * @return string
     */
    public function set_placeholders($template, \WC_Order_Item_Product $item, \WC_Order $order, \WC_Product $product)
    {
        $user = !empty($order) ? $order->get_user() : null;

        $placeholders_extra = [
            'user_data' => $user,
            'wc_order' => $order,
            'wc_product' => $product,
            'wc_item' => $item,
        ];

        // Guest User
        if (empty($user) && !empty($order)) {
            $user_id = $order->get_order_key();
            $guest_user = new \stdClass();
            $guest_user->user_login = $order->get_billing_first_name().' '.$order->get_billing_last_name();
            $guest_user->display_name = $order->get_billing_first_name().' '.$order->get_billing_last_name();
            $guest_user->user_email = $order->get_billing_email();
            $guest_user->ID = $user_id;
            $guest_user->user_role = esc_html__('Anonymous user', 'wpcloudplugins');

            $placeholders_extra['user_data'] = $guest_user;
            $placeholders_extra['custom_user_metadata'] = [
                'first_name' => $order->get_billing_first_name(),
                'last_name' => $order->get_billing_last_name(),
            ];
        }

        // Apply placeholders
        $output = Placeholders::apply(
            $template,
            Processor::instance(),
            $placeholders_extra
        );

        return apply_filters('outofthebox_woocommerce_set_placeholders', $output, $template, $order, $product, $item);
    }

    public function modify_shortcode_builder($standalone, $uploadbox_only, $for, $callback)
    {
        if ('woocommerce' !== $for) {
            return;
        }

        add_filter('outofthebox_shortcodebuilder_fields', function ($fields) {
            unset($fields['personal_folders']['smartclient_panel']['fields']['personal_folder_auto_panel']['fields']['userfoldernametemplate']);

            return $fields;
        }, 10, 1);
        ShortcodeBuilder::instance()->reset();
    }

    public function get_item_details($action, $processor)
    {
        if ('outofthebox-get-filelist' !== $action || !isset($_REQUEST['type']) || 'wc-item-details' !== $_REQUEST['type']) {
            return;
        }

        // Check if item indeed requires uploads
        $order_item_id = \sanitize_key($_REQUEST['item_id']);
        $item = new \WC_Order_Item_Product($order_item_id);

        if (false === $this->requires_order_uploads($item->get_order())) {
            echo json_encode([]);

            exit;
        }

        if (false === $this->requires_product_uploads($item->get_product(), $item->get_order())) {
            echo json_encode([]);

            exit;
        }

        // List the uploads
        $data = [];

        try {
            $folder = Client::instance()->get_folder(null, true, true, false);

            foreach ($folder->get_children() as $entry) {
                if ($entry->is_dir()) {
                    continue;
                }

                $data[$entry->get_id()] = [
                    'name' => trim($entry->get_path_display(), '/'),
                    'icon' => $entry->get_icon_retina(),
                    'url' => OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-download&OutoftheBoxpath='.rawurlencode($entry->get_path()).'&lastpath='.rawurlencode(Processor::instance()->get_last_path()).'&account_id='.App::get_current_account()->get_uuid().'&listtoken='.Processor::instance()->get_listtoken().'&dl=1',
                ];
            }
        } catch (\Exception $ex) {
        }

        $data = \apply_filters('outofthebox_woocommerce_uploaded_filelist', $data, $folder->get_children(), $item);

        header('Content-Type: application/json; charset=utf-8');

        echo \json_encode($data);

        exit;
    }
}

new WooCommerce_Uploads();
