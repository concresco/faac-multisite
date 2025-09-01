<?php

namespace TheLion\OutoftheBox\Integrations;

use TheLion\OutoftheBox\Core;
use TheLion\OutoftheBox\Helpers;
use TheLion\OutoftheBox\Processor;
use TheLion\OutoftheBox\Settings;
use TheLion\OutoftheBox\UserFolders;

defined('ABSPATH') || exit;

class GF_WPCP_AddOn extends \GFAddOn
{
    protected $_version = '2.0';
    protected $_min_gravityforms_version = '2.5';
    protected $_slug = 'wpcp-outofthebox';
    protected $_path = 'out-of-the-box/includes/integrations/gravityforms/init.php';
    protected $_full_path = __FILE__;
    protected $_title = 'Gravity Forms Out-of-the-Box Add-On';
    protected $_short_title = 'Out-of-the-Box Add-On';

    public function init()
    {
        parent::init();

        if (!$this->is_gravityforms_supported($this->_min_gravityforms_version)) {
            return;
        }

        // Add default value for field
        add_action('gform_editor_js_set_default_values', [$this, 'field_defaults']);

        // Add a custom setting to the field
        add_action('gform_field_standard_settings', [$this, 'custom_field_settings'], 10, 2);

        // Filter to add the tooltip for the field
        add_filter('gform_tooltips', [$this, 'add_tooltip']);

        // Add support for wpDataTables <> Gravity Form integration
        if (class_exists('WPDataTable')) {
            add_action('wpdatatables_before_get_table_metadata', [$this, 'render_wpdatatables_field'], 10, 1);
        }

        // Custom Personal Folder names
        add_filter('outofthebox_private_folder_name', [$this, 'new_personal_folder_name'], 10, 2);
        add_filter('outofthebox_private_folder_name_guests', [$this, 'rename_personal_folder_names_for_guests'], 10, 1);
        add_action('gform_user_registered', [$this, 'create_personal_folder'], 10, 4);

        // Dynamically change the merge tag output for {all:fields} and field merge tag
        add_filter('gform_merge_tag_filter', [$this, 'filter_merge_tag'], 10, 6);

        // Deprecated hooks, but still in use by e.g. GravityView + GravityFlow?
        add_filter('gform_entry_field_value', [$this, 'filter_entry_field_value'], 10, 4);
    }

    public function scripts()
    {
        if (\GFForms::is_gravity_page()) {
            Core::instance()->load_scripts();
            Core::instance()->load_styles();

            add_thickbox();
        }

        $scripts = [
            [
                'handle' => $this->_slug.'-gravityforms',
                'src' => plugins_url('script.js', __FILE__),
                'version' => OUTOFTHEBOX_VERSION,
                'deps' => ['jquery', 'WPCloudplugin.Libraries'],
                'in_footer' => true,
                'enqueue' => [
                    [
                        'admin_page' => ['form_editor', 'entry_edit', 'block_editor'],
                    ],
                ],
            ],
        ];

        return array_merge(parent::scripts(), $scripts);
    }

    public function styles()
    {
        wp_enqueue_style('OutoftheBox');

        $styles = [
            [
                'handle' => $this->_slug.'-gravityforms',
                'src' => plugins_url('style.css', __FILE__),
                'version' => OUTOFTHEBOX_VERSION,
                'deps' => ['OutoftheBox'],
                'enqueue' => [
                    [
                        'admin_page' => ['form_editor', 'entry_edit', 'block_editor'],
                    ],
                ],
            ],
        ];

        return array_merge(parent::styles(), $styles);
    }

    public function custom_field_settings($position, $form_id)
    {
        if (1430 == $position) {
            ?>
<li class="outofthebox_setting field_setting">
    <label for="field_wpcp_outofthebox"><?php esc_html_e('Upload Configuration', 'wpcloudplugins'); ?> <?php echo gform_tooltip('form_field_'.$this->_slug); ?></label>
    <textarea id="field_wpcp_outofthebox" class="small" onchange="SetFieldProperty('OutoftheBoxShortcode', this.value)"></textarea>
    <br />
    <button class='button gform-button primary wpcp-shortcodegenerator outofthebox'><?php esc_html_e('Configure Module', 'wpcloudplugins'); ?></button>
</li>
<?php
        }
    }

    public function add_tooltip($tooltips)
    {
        $tooltips['form_field_'.$this->_slug] = '<h6>Shortcode</h6>'.esc_html__('Configure this module using the Module Builder or select an existing module.', 'wpcloudplugins');

        return $tooltips;
    }

    public function field_defaults()
    {
        ?>
case 'outofthebox':
field.label = <?php echo json_encode(esc_html__('Attach your documents', 'wpcloudplugins')); ?>;
break;
<?php
    }

    public function create_personal_folder($user_id, $feed, $entry, $password)
    {
        if ('Yes' === Settings::get('userfolder_oncreation')) {
            UserFolders::user_register($user_id);
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

        if ('gf_upload_box' !== Processor::instance()->get_shortcode_option('class')) {
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
        if ('gf_upload_box' !== Processor::instance()->get_shortcode_option('class')) {
            return $personal_folder_name_guest;
        }

        $prefix = Settings::get('userfolder_name_guest_prefix');

        return str_replace($prefix, '', $personal_folder_name_guest);
    }

    public function render_wpdatatables_field($tableId)
    {
        add_filter('gform_get_input_value', [$this, 'entry_field_value'], 10, 4);
    }

    public function filter_entry_field_value($value, $field, $entry, $form)
    {
        return $this->entry_field_value($value, $entry, $field, null);
    }

    public function entry_field_value($value, $entry, $field, $input_id)
    {
        if ('outofthebox' !== $field->type) {
            return $value;
        }

        return apply_filters('outofthebox_render_formfield_data', html_entity_decode($value), true, $this);
    }

    public function filter_merge_tag($value, $merge_tag, $modifier, $field, $raw_value, $format = 'html')
    {
        if ('outofthebox' !== $field->type) {
            return $value;
        }

        $modifier = empty($modifier) ? 'default' : $modifier;
        $data = html_entity_decode($raw_value);

        switch ($modifier) {
            case 'url':
            case 'preview_url':
            case 'shared_url':
            case 'direct_url':
                $uploaded_files = json_decode($data);
                $urls = [];

                if (empty($uploaded_files) || (0 === count((array) $uploaded_files))) {
                    return $value;
                }

                foreach ($uploaded_files as $file) {
                    if (isset($file->link)) {
                        $file->preview_url = $file->link;
                        $file->shared_url = $file->link;
                    }

                    if ('url' === $modifier || 'preview_url' === $modifier) {
                        $urls[] = urldecode($file->preview_url);
                    } elseif ('shared_url' === $modifier) {
                        $urls[] = urldecode($file->shared_url);
                    } elseif ('direct_url' === $modifier) {
                        $urls[] = str_replace('dl=0', 'dl=1', urldecode($file->shared_url));
                    }
                }

                return ('html' == $format) ? join('<br />', $urls) : join(',', $urls);

            case 'array':
                return json_decode($data, true);

            case 'json':
                return $data;

            case 'default':
            default:
                return apply_filters('outofthebox_render_formfield_data', $data, 'html' === $format, $field);
        }
    }
}

class GF_WPCP_Field extends \GF_Field
{
    public $type = 'outofthebox';
    public $defaultValue = '[outofthebox mode="upload" viewrole="all" upload="1" uploadrole="all" upload_auto_start="0" userfolders="auto" viewuserfoldersrole="none"]';

    public function get_form_editor_field_title()
    {
        return 'Dropbox';
    }

    public function add_button($field_groups)
    {
        $field_groups = $this->maybe_add_field_group($field_groups);

        return parent::add_button($field_groups);
    }

    public function maybe_add_field_group($field_groups)
    {
        foreach ($field_groups as $field_group) {
            if ('wpcp_group' == $field_group['name']) {
                return $field_groups;
            }
        }

        $field_groups[] = [
            'name' => 'wpcp_group',
            'label' => 'WP Cloud Plugins Fields',
            'fields' => [],
        ];

        return $field_groups;
    }

    public function get_form_editor_button()
    {
        return [
            'group' => 'wpcp_group',
            'text' => $this->get_form_editor_field_title(),
        ];
    }

    public function get_form_editor_field_icon()
    {
        return 'gform-icon--upload';
    }

    public function get_form_editor_field_description()
    {
        return esc_attr__('Let users attach files to this form. The files will be stored in the cloud', 'wpcloudplugins');
    }

    public function get_form_editor_field_settings()
    {
        return [
            'conditional_logic_field_setting',
            'error_message_setting',
            'label_setting',
            'label_placement_setting',
            'admin_label_setting',
            'rules_setting',
            'visibility_setting',
            'duplicate_setting',
            'description_setting',
            'css_class_setting',
            'outofthebox_setting',
        ];
    }

    public function get_value_default()
    {
        return $this->is_form_editor() ? $this->defaultValue : \GFCommon::replace_variables_prepopulate($this->defaultValue);
    }

    public function is_conditional_logic_supported()
    {
        return false;
    }

    public function get_field_input($form, $value = '', $entry = null)
    {
        $form_id = $form['id'];
        $is_entry_detail = $this->is_entry_detail();
        $id = (int) $this->id;

        if ($is_entry_detail) {
            $input = "<input type='hidden' id='input_{$id}' name='input_{$id}' value='{$value}' />";
            $input .= $this->renderUploadedFiles(html_entity_decode($value), true);

            return $input.'<br/><em>('.esc_html__('This field is not editable', 'wpcloudplugins').')</em>';
        }

        Core::instance()->load_scripts();
        Core::instance()->load_styles();

        if ($this->is_form_editor()) {
            return $this->get_placeholder();
        }

        // Display placeholder when form is rendered in Gutenberg
        if ('true' === rgar(rgget('attributes'), 'formPreview')) {
            return $this->get_placeholder(true);
        }

        $prefill = '';
        if (isset($_REQUEST['input_'.$id])) {
            $prefill = stripslashes($_REQUEST['input_'.$id]);
        } elseif (isset($entry[$id])) {
            $prefill = $entry[$id];
        }

        $input = do_shortcode($this->OutoftheBoxShortcode);
        $input .= "<input type='hidden' name='input_".$id."' id='input_".$form_id.'_'.$id."'  class='fileupload-filelist fileupload-input-filelist' value='{$prefill}'>";

        return $input;
    }

    public function get_placeholder($force = false)
    {
        if (!empty($this->OutoftheBoxShortcode) && !$force) {
            return do_shortcode($this->OutoftheBoxShortcode);
        }

        ob_start();
        wp_print_styles('OutoftheBox');
        ?>
<div id="OutoftheBox" class="light upload">
    <div class="OutoftheBox upload" style="width: 100%;">
        <div class="fileupload-box -is-formfield -is-required -has-files -placeholder" style="width:100%;max-width:100%;"">
                    <!-- FORM ELEMENTS -->
                    <div class=" fileupload-form">
            <!-- END FORM ELEMENTS -->

            <!-- UPLOAD BOX HEADER -->
            <div class="fileupload-header">
                <div class="fileupload-header-title">
                    <div class="fileupload-empty">
                        <div class="fileupload-header-text-title upload-add-file"><?php esc_html_e('Add your file', 'wpcloudplugins'); ?></div>
                        <div class="fileupload-header-text-subtitle upload-add-folder"><a><?php esc_html_e('Or select a folder', 'wpcloudplugins'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END UPLOAD BOX HEADER -->

        </div>
    </div>
</div>
<?php
                return ob_get_clean();
    }

    public function validate($value, $form)
    {
        if (false === $this->isRequired) {
            return;
        }

        // Get information uploaded files from hidden input
        $attached_files = json_decode($value);

        if (empty($attached_files)) {
            $this->failed_validation = true;

            if (!empty($this->errorMessage)) {
                $this->validation_message = $this->errorMessage;
            } else {
                $this->validation_message = esc_html__('This field is required. Please upload your files.', 'gravityforms');
            }
        }
    }

    public function get_value_entry_detail($value, $currency = '', $use_text = false, $format = 'html', $media = 'screen')
    {
        return $this->renderUploadedFiles(html_entity_decode($value), 'html' === $format);
    }

    public function get_value_entry_list($value, $entry, $field_id, $columns, $form)
    {
        if (!empty($value)) {
            return $this->renderUploadedFiles(html_entity_decode($value));
        }
    }

    public function get_value_export($entry, $input_id = '', $use_text = false, $is_csv = false)
    {
        $value = rgar($entry, $input_id);

        return $this->renderUploadedFiles(html_entity_decode($value), false);
    }

    public function renderUploadedFiles($data, $ashtml = true)
    {
        return apply_filters('outofthebox_render_formfield_data', $data, $ashtml, $this);
    }

    public function get_field_container_tag($form)
    {
        if (\GFCommon::is_legacy_markup_enabled($form)) {
            return parent::get_field_container_tag($form);
        }

        return 'fieldset';
    }
}

\GFForms::include_addon_framework();
$GF_WPCP_AddOn = new GF_WPCP_AddOn();

\GF_Fields::register(new GF_WPCP_Field());
