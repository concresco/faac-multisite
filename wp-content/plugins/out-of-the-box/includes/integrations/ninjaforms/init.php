<?php

namespace TheLion\OutoftheBox\Integrations;

defined('ABSPATH') || exit;

use TheLion\OutoftheBox\Core;
use TheLion\OutoftheBox\Helpers;

class NinjaFormsIntegration
{
    public static $field_key = 'wpcp_outofthebox';

    public function __construct()
    {
        $this->set_hooks();
    }

    public function set_hooks()
    {
        add_filter('ninja_forms_register_fields', [$this, 'register_field']);
        add_filter('ninja_forms_field_template_file_paths', [$this, 'custom_field_file_path']);
        add_action('ninja_forms_enqueue_scripts', [$this, 'enqueue_scripts'], 1);
        add_filter('ninja_forms_before_container', [$this, 'render_frontend'], 10, 3);
        add_filter('ninja_forms_display_fields', [$this, 'display_fields'], 10, 2);
    }

    public function display_fields($form_fields, $form_id)
    {
        foreach ($form_fields as $form_id => $field) {
            if ($this::$field_key == $field['type']) {
                unset($form_fields[$form_id]['wpcp_outofthebox_shortcode']);
            }
        }

        return $form_fields;
    }

    public function enqueue_scripts($data)
    {
        $form_id = $data['form_id'];

        $fields = \Ninja_Forms()->form($form_id)->get_fields();

        foreach ($fields as $field) {
            if ($this::$field_key == $field->get_setting('type')) {
                Core::instance()->load_scripts();
                Core::instance()->load_styles();

                break;
            }
        }
    }

    public function register_field($fields)
    {
        $fields[$this::$field_key] = new NinjaFormsField();

        return $fields;
    }

    public function custom_field_file_path($paths)
    {
        $paths[] = __DIR__.'/templates/';

        return $paths;
    }

    public function render_frontend($form_id, $form_settings, $form_fields)
    {
        foreach ($form_fields as $field) {
            if (is_object($field)) {
                $field = [
                    'id' => $field->get_id(),
                    'settings' => $field->get_settings(),
                ];
            }

            if ($this::$field_key == $field['settings']['type']) {
                $shortcode = $field['settings'][$this::$field_key.'_shortcode'];

                echo "<div id='wpcp-shortcode-nf-container-".$field['id']."' style='display:none'>";
                echo do_shortcode($shortcode);
                echo '</div>';
            }
        }
    }
}

class NinjaFormsField extends \NF_Abstracts_Field
{
    protected $_name = 'wpcp_outofthebox';
    protected $_type = 'wpcp_outofthebox';
    protected $_nicename = 'Dropbox';
    protected $_parent_type = 'textbox';
    protected $_section = 'common';
    protected $_templates = 'wpcp_outofthebox';
    protected $_icon = 'cloud-upload';
    protected $_test_value = false;
    protected $_settings_all_fields = [
        'key',
        'label',
        'label_pos',
        'required',
        'classes',
        'manual_key',
        'help',
        'description',
    ];

    public function __construct()
    {
        parent::__construct();

        $settings = [
            'wpcp_outofthebox_shortcode' => [
                'name' => 'wpcp_outofthebox_shortcode',
                'type' => 'textarea',
                'value' => '[outofthebox mode="upload" viewrole="all" upload="1" uploadrole="all" upload_auto_start="0" userfolders="auto" viewuserfoldersrole="none"]',
                'label' => esc_attr__('Upload Configuration', 'wpcloudplugins'),
                'group' => 'primary',
                'width' => 'full',
                'help' => esc_attr__('Configure the upload field with module builder.', 'wpcloudplugins'),
            ],

            'wpcp_outofthebox_configure' => [
                'name' => 'wpcp_outofthebox_configure',
                'type' => 'html',
                'value' => '<button class="nf-button primary outofthebox open-shortcode-builder">'.esc_html__('Configure Module', 'wpcloudplugins').'</button>',
                'group' => 'primary',
                'width' => 'full',
            ],
        ];

        $this->_settings = array_merge($this->_settings, $settings);

        add_action('nf_admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        add_filter('ninja_forms_merge_tag_value_'.$this->_name, [$this, 'filter_merge_tag_value'], 10, 2);
        add_filter('ninja_forms_subs_export_field_value_'.$this->_type, [$this, 'export_value'], 10, 2);
        add_action('ninja_forms_builder_templates', [$this, 'load_template']);
    }

    public function load_template()
    {
        $templatePath = __DIR__."/templates/fields-{$this->_type}.html";
        if (file_exists($templatePath)) {
            include_once $templatePath;
        } else {
            Helpers::log_error('Template does not exist.', 'NinjaForms', ['template' => $templatePath], __LINE__);
        }
    }

    public function export_value($value, $field)
    {
        return \str_replace("\r\n", ',', $value);
    }

    public function filter_merge_tag_value($value, $field)
    {
        return $value;
    }

    public function process($field, $data)
    {
        $text = $this->render_upload_list($field['value']);

        if (isset($data['extra']['wpcp_uploads'])) {
            $data['extra']['wpcp_uploads'] = [];
        }
        if (isset($data['extra']['wpcp_uploads'][$field['id']])) {
            $data['extra']['wpcp_uploads'][$field['id']] = [];
        }

        $data['extra']['wpcp_uploads'][$field['id']]['json'] = $field['value'];

        $data['fields'][$field['id']]['value'] = $text;

        return $data;
    }

    public function render_upload_list($data)
    {
        $uploaded_files = json_decode($data);

        if (empty($uploaded_files) || (0 === count((array) $uploaded_files))) {
            return $data;
        }

        $file_links = [];
        foreach ($uploaded_files as $file) {
            $file_links[] = urldecode($file->link);
        }

        return implode("\r\n", $file_links);
    }

    public function admin_enqueue_scripts()
    {
        Core::instance()->load_scripts();
        Core::instance()->load_styles();

        wp_enqueue_style('OutoftheBox');
        wp_enqueue_script('WPCP-OutoftheBox-NinjaForms', plugins_url('NinjaForms.js', __FILE__), ['OutoftheBox.UploadBox', 'OutoftheBox'], OUTOFTHEBOX_VERSION, true);
    }

    public function validate($field, $data)
    {
        $errors = parent::validate($field, $data);
        if (!empty($errors)) {
            return $errors;
        }

        if (isset($field['required']) && 1 == intval($field['required'])) {
            // Get information uploaded files from hidden input
            $attached_files = json_decode($data);

            if (empty($attached_files)) {
                $errors['message'] = esc_html__('This field is required. Please upload your files.', 'wpcloudplugins');
            }
        }

        return $errors;
    }
}

new NinjaFormsIntegration();
