<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

defined('ABSPATH') || exit;

// Add Button section
add_filter('wpforms_builder_fields_buttons', function ($fields) {
    $tmp = [
        'wpcloudplugins' => [
            'group_name' => 'WP Cloud Plugins',
            'fields' => [],
        ],
    ];

    return array_slice($fields, 0, 1, true) + $tmp + array_slice($fields, 1, count($fields) - 1, true);
}, 8);

class WPForms_Field_Upload_Box extends \WPForms_Field
{
    public function init()
    {
        // Define field type information.
        $this->name = 'Dropbox';
        $this->type = 'wpcp-outofthebox-upload-box';
        $this->group = 'wpcloudplugins';
        $this->icon = 'fa-cloud-upload fa-lg';
        $this->order = 3;

        add_action('wpforms_builder_enqueues_before', [$this, 'enqueues']);

        // Display values in a proper way
        add_filter('wpforms_html_field_value', [$this, 'html_field_value'], 10, 4);
        add_filter('wpforms_plaintext_field_value', [$this, 'plain_field_value'], 10, 3);
        add_filter('wpforms_pro_admin_entries_export_ajax_get_data', [$this, 'export_value'], 10, 2);
        add_filter('wpforms_smarttags_process_value', [$this, 'render_smart_tag'], 10, 6);

        // Custom Personal Folder names
        add_filter('outofthebox_private_folder_name', [$this, 'new_personal_folder_name'], 10, 2);
        add_filter('outofthebox_private_folder_name_guests', [$this, 'rename_personal_folder_names_for_guests'], 10, 1);
    }

    // //////////////////////////////
    // **** **** PUBLIC **** **** //
    // //////////////////////////////

    // Field display on the form front-end.
    public function field_display($field, $deprecated, $form_data)
    {
        echo do_shortcode($field['shortcode']);
        $field_id = sprintf('wpforms-%d-field_%d', $form_data['id'], $field['id']);

        $prefill = '';
        if (isset($_REQUEST[$field_id])) {
            $prefill_raw = $_REQUEST[$field_id];
            $prefill_json = json_decode(sanitize_text_field(stripslashes($prefill_raw)), true);

            if (!empty($prefill_json)) {
                $prefill = json_encode($prefill);
            }
        } elseif (isset($field['properties']['inputs']['primary']['attr']['value'])) {
            // Value received by WPForms Save & Resume
            $prefill = $field['properties']['inputs']['primary']['attr']['value'];
        }

        echo sprintf("<input type='hidden' name='wpforms[fields][%d]' id='%s' class='fileupload-filelist fileupload-input-filelist' value='%s'>", esc_attr($field['id']), esc_attr($field_id), esc_attr($prefill));
    }

    public function plain_field_value($value, $field, $form_data)
    {
        return $this->html_field_value($value, $field, $form_data, false);
    }

    public function html_field_value($value, $field, $form_data, $type)
    {
        if ($this->type !== $field['type']) {
            return $value;
        }

        // Reset $value as WPForms can truncate the content in e.g. the Entries table
        if (isset($field['value'])) {
            $value = $field['value'];
        }

        $ashtml = in_array($type, ['entry-single', 'entry-table', 'email-html', 'smart-tag']);

        return apply_filters('outofthebox_render_formfield_data', $value, $ashtml, $this);
    }

    public function render_smart_tag($value, $tag_name, $form_data, $fields, $entry_id, $smart_tag_object)
    {
        $attributes = $smart_tag_object->get_attributes();

        if (!isset($attributes['field_id'])) {
            return $value;
        }

        $field_id = $attributes['field_id'];

        if (!isset($fields[$field_id])) {
            return $value;
        }

        $field = $fields[$field_id];
        if ($field['type'] !== $this->type) {
            return $value;
        }

        return $this->html_field_value($field['value'], $field, $form_data, 'smart-tag');
    }

    public function export_value($export_data, $request_data)
    {
        foreach ($export_data as $row_id => &$entry) {
            if (0 === $row_id) {
                continue; // Skip Headers
            }

            foreach ($entry as $field_id => &$value) {
                if ($request_data['form_data']['fields'][$field_id]['type'] !== $this->type) {
                    continue; // Skip data that isn't related to this custom field
                }
                $value = $this->plain_field_value($value, $request_data['form_data']['fields'][$field_id], $request_data['form_data']);
            }
        }

        return $export_data;
    }

    // /////////////////////////////
    // **** **** ADMIN **** **** //
    // /////////////////////////////

    /**
     * Format field value which is stored.
     *
     * @param int   $field_id     field ID
     * @param mixed $field_submit field value that was submitted
     * @param array $form_data    form data and settings
     */
    public function format($field_id, $field_submit, $form_data)
    {
        if ($this->type !== $form_data['fields'][$field_id]['type']) {
            return;
        }

        $name = !empty($form_data['fields'][$field_id]['label']) ? sanitize_text_field($form_data['fields'][$field_id]['label']) : '';

        wpforms()->process->fields[$field_id] = [
            'name' => $name,
            'value' => $field_submit,
            'id' => absint($field_id),
            'type' => $this->type,
        ];
    }

    // Enqueue Out-of-the-Box scripts
    public function enqueues()
    {
        Core::instance()->load_scripts();
        Core::instance()->load_styles();

        wp_enqueue_style('OutoftheBox');

        wp_enqueue_script('WPCP-'.$this->type.'-WPForms', plugins_url('WPForms.js', __FILE__), ['WPCloudplugin.Libraries'], false, true);
        wp_enqueue_style('WPCP-'.$this->type.'-WPForms', plugins_url('WPForms.css', __FILE__));
    }

    // Field options panel inside the builder
    public function field_options($field)
    {
        // Basic field options.

        // Options open markup.
        $this->field_option(
            'basic-options',
            $field,
            [
                'markup' => 'open',
            ]
        );
        // Label.
        $this->field_option('label', $field);

        // Description.
        $this->field_option('description', $field);

        $btn = $this->custom_option_field(
            $field['id'],
            'builder',
            null,
            [
                'html_type' => 'button',
                'type' => 'button',
                'slug' => 'shortcode-builder',
                'class' => 'button outofthebox open-shortcode-builder',
                'name' => 'shortcode-builder',
                'value' => 'Configure Module',
            ],
            false
        );

        $lbl = $this->field_element(
            'label',
            $field,
            [
                'slug' => 'shortcode',
                'value' => esc_attr__('Upload Configuration', 'wpcloudplugins'),
                'tooltip' => 'Edit the raw shortcode or use the Module Configurator',
            ],
            false
        );

        $fld = $this->field_element(
            'textarea',
            $field,
            [
                'class' => '',
                'slug' => 'shortcode',
                'name' => 'shortcode',
                'rows' => 5,
                'value' => isset($field['shortcode']) ? $field['shortcode'] : '[outofthebox mode="upload" viewrole="all" upload="1" uploadrole="all" upload_auto_start="0" userfolders="auto" viewuserfoldersrole="none"]',
            ],
            false
        );

        $args = [
            'slug' => 'shortcode',
            'content' => $lbl.$fld.$btn,
        ];

        $this->field_element('row', $field, $args);

        // Required toggle.
        $this->field_option('required', $field);

        // Options close markup.
        $this->field_option(
            'basic-options',
            $field,
            [
                'markup' => 'close',
            ]
        );

        // Advanced field options

        // Options open markup.
        $this->field_option(
            'advanced-options',
            $field,
            [
                'markup' => 'open',
            ]
        );

        // Hide label.
        $this->field_option('label_hide', $field);

        // Custom CSS classes.
        $this->field_option('css', $field);

        // Options close markup.
        $this->field_option(
            'advanced-options',
            $field,
            [
                'markup' => 'close',
            ]
        );
    }

    // Field preview inside the builder.
    public function field_preview($field)
    {
        // Label.
        $this->field_preview_option('label', $field);

        // Description.
        $this->field_preview_option('description', $field);

        // Real-Time preview isn't available for this element
        echo '<p>(Real-Time preview is not available for this element. Please refresh page to see changes to its options rendered.)</p>';

        if (!empty($field['shortcode'])) {
            // Shortcode.
            echo do_shortcode($field['shortcode']);
        } else {
            echo '<div class="wpcp-wpforms-placeholder"></div>';
        }
    }

    // The function that will help us create the buttons in the form builder
    public function custom_option_field($field_id, $field_class_mark, $label, $field_info, $echo = true)
    {
        if ('button' === $field_info['html_type']) {
            $output = sprintf('<button class="%s" id="wpforms-field-option-%d-%s" name="fields[%d][%s]" type="%s">%s</button>', $field_info['class'], $field_id, $field_info['slug'], $field_id, $field_info['slug'], $field_info['type'], $field_info['value']);
        }

        if (!$echo) {
            return $output;
        }

        echo $output;
    }

    /**
     * Function to change the Personal Folder Name.
     *
     * @param string    $personal_folder_name
     * @param Processor $processor
     *
     * @return string
     */
    public function new_personal_folder_name($personal_folder_name, $processor)
    {
        if (!isset($_COOKIE['WPCP-FORM-NAME-'.$processor->get_listtoken()])) {
            return $personal_folder_name;
        }

        if ('wpforms_upload_box' !== Processor::instance()->get_shortcode_option('class')) {
            return $personal_folder_name;
        }

        $raw_name = sanitize_text_field($_COOKIE['WPCP-FORM-NAME-'.$processor->get_listtoken()]);
        $name = str_replace(['|', '/'], ' ', $raw_name);
        $filtered_name = Helpers::filter_filename(stripslashes($name), false);

        return trim($filtered_name);
    }

    /**
     * Function to change the Personal Folder Name for Guest users.
     *
     * @param string $personal_folder_name_guest
     *
     * @return string
     */
    public function rename_personal_folder_names_for_guests($personal_folder_name_guest)
    {
        if ('wpforms_upload_box' !== Processor::instance()->get_shortcode_option('class')) {
            return $personal_folder_name_guest;
        }

        $prefix = Settings::get('userfolder_name_guest_prefix');

        return str_replace($prefix, '', $personal_folder_name_guest);
    }
}

new WPForms_Field_Upload_Box();
