<?php

namespace TheLion\OutoftheBox\Integrations;

use TheLion\OutoftheBox\Core;
use TheLion\OutoftheBox\Helpers;
use TheLion\OutoftheBox\Processor;
use TheLion\OutoftheBox\Settings;

defined('ABSPATH') || exit;

class FormidableForms
{
    public $field_type = 'wpcp-outofthebox';
    public $default_value = '[outofthebox mode="upload" viewrole="all" upload="1" uploadrole="all" upload_auto_start="0" userfolders="auto" viewuserfoldersrole="none"]';

    public function __construct()
    {
        $this->add_hooks();
    }

    public function add_hooks()
    {
        // Add Form button to Form Builder
        add_filter('frm_available_fields', [$this, 'add_field']);

        // Set Field default values
        add_filter('frm_before_field_created', [$this, 'add_field_defaults']);

        // Add extra options to the field option box
        add_action('frm_field_options_form', [$this, 'field_options_form'], 10, 3);

        // Save the extra added options
        add_filter('frm_update_field_options', [$this, 'update_field_options'], 10, 3);

        // The render in the Form Builder
        add_action('frm_display_added_fields', [$this, 'admin_field']);
        add_action('frm_enqueue_builder_scripts', [$this, 'enqueue']);

        // The Front-End render
        add_action('frm_form_fields', [$this, 'frontend_field'], 10, 3);
        add_action('frm_entries_footer_scripts', [$this, 'enqueue_for_ajax'], 20, 2);

        // Validate the field
        add_filter('frm_validate_'.$this->field_type.'_field_entry', [$this, 'validation'], 9, 4);

        // Store Submission value
        add_filter('frm_pre_create_entry', [$this, 'save_value']);
        add_filter('frm_pre_update_entry', [$this, 'save_value']);

        // Field Submission value render
        add_filter('frm_display_'.$this->field_type.'_value_custom', [$this, 'render_value_custom'], 15, 2);
        add_filter('frm_display_value', [$this, 'render_value'], 15, 3);
        add_filter('frm_graph_value', [$this, 'graph_value'], 10, 2);
        add_filter('frm_conditional_value', [$this, 'conditional_replace_with_value'], 10, 4);

        // XML / CSV export value
        add_filter('frm_csv_value', [$this, 'csv_value'], 10, 2);

        // Custom Personal Folder names
        add_filter('outofthebox_private_folder_name', [$this, 'new_personal_folder_name'], 10, 2);
        add_filter('outofthebox_private_folder_name_guests', [$this, 'rename_personal_folder_names_for_guests'], 10, 1);
    }

    public function add_field($fields)
    {
        $fields[$this->field_type] = [
            'name' => 'Dropbox Upload',
            'icon' => 'frm_icon_font frm_upload_icon',
        ];

        return $fields;
    }

    public function add_field_defaults($field_data)
    {
        if ($this->field_type == $field_data['type']) {
            $field_data['name'] = esc_html__('Attach your documents', 'wpcloudplugins');

            $defaults = [
                'shortcode' => $this->default_value,
            ];

            foreach ($defaults as $k => $v) {
                $field_data['field_options'][$k] = $v;
            }
        }

        return $field_data;
    }

    public function field_options_form($field, $display, $values)
    {
        if ($this->field_type != $field['type']) {
            return;
        }

        if (!isset($field['shortcode'])) {
            $field['shortcode'] = $this->default_value;
        } ?>

<tr>
    <td>
        <h2><?php esc_html_e('Upload Configuration', 'wpcloudplugins'); ?></h2>
    </td>
    <td>
        <label for="shortcode_" class="howto"><?php esc_html_e('Module', 'wpcloudplugins'); ?></label>
        <textarea id="shortcode_<?php echo esc_attr($field['id']); ?>" name="field_options[shortcode_<?php echo esc_attr($field['id']); ?>]" class="frm_long_input"><?php echo esc_attr($field['shortcode']); ?></textarea>
        <a href="#" class='button-primary outofthebox open-shortcode-builder'><?php esc_html_e('Configure Module', 'wpcloudplugins'); ?></a>
    </td>
</tr>
<?php
    }

    public function admin_field($field)
    {
        if ($this->field_type != $field['type']) {
            return;
        }

        $this->enqueue(); ?>

<div class="frm_html_field_placeholder">
    <?php echo do_shortcode($field['shortcode']); ?>
    <div class="howto button-secondary frm_html_field">Please refresh the page to see how it's changed.</div>
</div>
<?php
    }

    public function frontend_field($field, $field_name, $atts)
    {
        if ($this->field_type != $field['type']) {
            return;
        }

        $field_id = $field['id'];
        $prefill = '';
        if (!empty($_REQUEST['frm_action']) && 'create' === $_REQUEST['frm_action'] && !isset($_REQUEST['item_meta'][$field_id])) {
            // Clear all uploaded values
            foreach ($_REQUEST as $key => $value) {
                if (false !== strpos($key, 'fileupload-filelist_')) {
                    $_REQUEST[$key] = '';
                }
            }
        } elseif (isset($field['value'])) {
            $value = $field['value'];
            // Value can be different depending on FF addons installed
            if (false === is_array($value)) {
                $prefill = trim(str_ireplace($this->field_type.'-', '', $value));
            } else {
                $prefill = json_encode($value);
            }
        } else {
            $prefill = '';
            if (isset($_REQUEST['item_meta'][$field_id])) {
                $prefill_raw = $_REQUEST['item_meta'][$field_id];
                $prefill_json = json_decode(sanitize_text_field(stripslashes($prefill_raw)), true);

                if (!empty($prefill_json)) {
                    $prefill = json_encode($prefill);
                }
            }
        }

        echo do_shortcode($field['shortcode']);

        echo sprintf("<input type='hidden' name='%s' id='%s' class='fileupload-filelist fileupload-input-filelist' value='%s'>", esc_attr($field_name), esc_attr($atts['html_id']), esc_attr($prefill));
    }

    public function update_field_options($field_options, $field, $values)
    {
        if ($this->field_type != $field->type) {
            return $field_options;
        }

        $defaults = [
            'shortcode' => $this->default_value,
        ];

        foreach ($defaults as $opt => $default) {
            $field_options[$opt] = $values['field_options'][$opt.'_'.$field->id] ?? $default;
        }

        return $field_options;
    }

    public function validation($errors, $posted_field, $posted_value, $args)
    {
        if (empty($posted_field->required)) {
            return $errors;
        }

        $uploaded_files = json_decode($posted_value);

        if (empty($uploaded_files) || (0 === count((array) $uploaded_files))) {
            $errors['field'.$posted_field->id] = $errors['field'.$posted_field->id] ?? $posted_field->field_options['blank'];
        }

        return $errors;
    }

    public function render_value_custom($value, $args)
    {
        if ($this->field_type != $args['field']->type) {
            return $value;
        }

        if ($value === $this->field_type.'-') {
            return null;
        }

        // Hack to let Formidable Form think that the value is altered and while frm_display_value() will still be called with the original value
        return $value.' ';
    }

    public function render_value($value, $field, $atts)
    {
        if ($this->field_type != $field->type) {
            return $value;
        }

        $value = trim(str_ireplace($this->field_type.'-', '', $value));
        if (isset($atts['entry']) && !empty($atts['entry']->metas) && !empty($atts['entry']->metas[$field->id])) {
            $stored_value = $atts['entry']->metas[$field->id];

            // Value can be different depending on FF addons installed
            if (false === is_array($stored_value)) {
                $value = trim(str_ireplace($this->field_type.'-', '', $stored_value));
            } else {
                $value = json_encode($stored_value);
            }
        }

        $as_html = true;
        if (isset($atts['plain_text'])) {
            $as_html = !$atts['plain_text'];
        }

        if (isset($atts['html'])) {
            $as_html = !$atts['html'];
        }

        if (isset($atts['entry_id']) && (empty($value) || (isset($atts['truncate']) && true === $atts['truncate']))) {
            $data = \FrmEntry::getOne($atts['entry_id'], true);
            $value = $data->metas[$field->id];

            // Value can be different depending on FF addons installed
            if (false === is_array($value)) {
                $value = trim(str_ireplace($this->field_type.'-', '', $value));
            } else {
                $value = json_encode($value);
            }
        }

        return $this->render_value_as_text($value, $as_html);
    }

    public function render_value_as_text($json_data, $ashtml = true)
    {
        return apply_filters('outofthebox_render_formfield_data', $json_data, $ashtml, $this);
    }

    public function conditional_replace_with_value($replace_with, $atts, $field, $tag)
    {
        if ($this->field_type != $field->type) {
            return $replace_with;
        }

        if ($replace_with === $this->field_type.'-') {
            return null;
        }

        return $replace_with;
    }

    public function save_value($values)
    {
        foreach ($values['item_meta'] as $field_id => $value) {
            $field = \FrmField::getOne($field_id);

            if (empty($field)) {
                continue;
            }

            if ($this->field_type != $field->type) {
                continue;
            }

            if ('{}' === $value) {
                unset($values['item_meta'][$field_id]);
            } else {
                $values['item_meta'][$field_id] = $this->field_type.'-'.$value;
            }
        }

        return $values;
    }

    public function graph_value($value, $field)
    {
        if (!is_object($field) || $this->field_type != $field->type) {
            return $value;
        }

        $value = trim(str_ireplace($this->field_type.'-', '', $value));

        $data = json_decode($value, true);

        if ((null === $data) || (0 === count((array) $data))) {
            return $value;
        }

        return 'Uploads: '.count($data);
    }

    public function csv_value($value, $atts)
    {
        if ($this->field_type != $atts['field']->type) {
            return $value;
        }

        // Value can be different depending on FF addons installed
        if (false === is_array($value)) {
            $value = trim(str_ireplace($this->field_type.'-', '', $value));
            $data = json_decode($value, true);
        } else {
            $data = $value;
        }

        if ((null === $data) || (0 === count((array) $data))) {
            return $value;
        }

        $return = '';
        foreach ($data as $fileid => $file) {
            $link = isset($file['preview_url']) ? $file['preview_url'] : $file['link'];
            $return .= urldecode($link)."\n";
        }

        return $return;
    }

    public function enqueue()
    {
        $action = \FrmAppHelper::simple_get('frm_action', 'sanitize_title');
        $is_builder_page = (\FrmAppHelper::is_admin_page('formidable') || \FrmAppHelper::is_admin_page('formidable-entries')) && ('edit' === $action || 'duplicate' === $action);

        if (!$is_builder_page) {
            return;
        }

        Core::instance()->load_scripts();
        Core::instance()->load_styles();

        wp_enqueue_style('OutoftheBox');

        wp_enqueue_script('WPCP-'.$this->field_type.'-FormidableForms', plugins_url('FormidableForms.js', __FILE__), ['OutoftheBox.UploadBox', 'OutoftheBox'], OUTOFTHEBOX_VERSION, true);
    }

    public function enqueue_for_ajax($fields, $form)
    {
        if (empty($form)) {
            return;
        }

        $form_is_using_ajax = (isset($form->options['ajax_load']) && '1' === $form->options['ajax_load']) || (isset($form->options['ajax_submit']) && '1' === $form->options['ajax_submit']);
        $form_has_fields = \FrmField::get_all_types_in_form($form->id, $this->field_type);

        if (false === $form_is_using_ajax || 0 === count($form_has_fields)) {
            return;
        }

        foreach ($form_has_fields as $field) {
            // Process shortcodes to load required styles and scripts, but don't echo the output itself
            do_shortcode($field->field_options['shortcode']);
        }
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

        if ('formidableforms_upload_box' !== Processor::instance()->get_shortcode_option('class')) {
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
        if ('formidableforms_upload_box' !== Processor::instance()->get_shortcode_option('class')) {
            return $personal_folder_name_guest;
        }

        $prefix = Settings::get('userfolder_name_guest_prefix');

        return str_replace($prefix, '', $personal_folder_name_guest);
    }
}

new FormidableForms();