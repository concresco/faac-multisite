<?php

namespace TheLion\OutoftheBox\Integrations\Elementor;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use ElementorPro\Modules\Forms\Classes;
use ElementorPro\Modules\Forms\Classes\Form;
use ElementorPro\Modules\Forms\Classes\Form_Record;
use ElementorPro\Modules\Forms\Fields\Field_Base;
use ElementorPro\Plugin;
use TheLion\OutoftheBox\Core;

defined('ABSPATH') || exit;

class FormField extends Field_Base
{
    /**
     * Field constructor. Adds editor preview hook.
     */
    public function __construct()
    {
        parent::__construct();

        wp_register_script('OutoftheBox.Elementor.Widget', plugins_url('widget.js', __FILE__), ['jquery'], OUTOFTHEBOX_VERSION);

        add_action('elementor/preview/init', [$this, 'editor_preview_footer']);
    }

    /**
     * Returns our custom field type.
     */
    public function get_type()
    {
        return 'wpcp_outofthebox_upload';
    }

    /**
     * Returns field label shown in editor.
     */
    public function get_name()
    {
        return esc_html__('File Upload', 'elementor-pro').' → Dropbox';
    }

    /**
     * Insert our field type into the list, right after 'upload' if present.
     *
     * @param mixed $field_types
     */
    public function add_field_type($field_types)
    {
        $key = $this->get_type();
        $label = $this->get_name();
        $new = [$key => $label];

        $pos = array_search('upload', array_keys($field_types), true);
        if (false !== $pos) {
            // splice in after 'upload'
            $prefix = array_slice($field_types, 0, $pos + 1, true);
            $suffix = array_slice($field_types, $pos + 1, null, true);

            return $prefix + $new + $suffix;
        }

        // fallback: append
        return $field_types + $new;
    }

    /**
     * Inject our custom controls into the form_fields control.
     *
     * @param Widget_Base $widget
     */
    public function update_controls($widget)
    {
        // fetch existing form_fields control
        $form_controls = Plugin::elementor()
            ->controls_manager
            ->get_control_from_stack($widget->get_unique_name(), 'form_fields')
        ;

        if (is_wp_error($form_controls)) {
            return;
        }

        // define our additional controls
        $custom_fields = [
            'wpcp_outofthebox_module_configure_button' => [
                'name' => 'wpcp_outofthebox_module_configure_button',
                'type' => Controls_Manager::BUTTON,
                'label' => '<span class="eicon eicon-settings"></span> '.__('Configure Module', 'wpcloudplugins'),
                'text' => __('Configure', 'wpcloudplugins'),
                'event' => 'wpcp:editor:edit_outofthebox_shortcode',
                'description' => __('Configure or select an existing module.', 'wpcloudplugins'),
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => ['field_type' => $this->get_type()],
            ],
            'wpcp_outofthebox_module_data' => [
                'name' => 'wpcp_outofthebox_module_data',
                'label' => __('Module Shortcode', 'wpcloudplugins'),
                'type' => Controls_Manager::TEXTAREA,
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'default' => '[outofthebox mode="upload" viewrole="all" upload="1" uploadrole="all" upload_auto_start="0" userfolders="auto" viewuserfoldersrole="none"]',
                'condition' => ['field_type' => $this->get_type()],
            ],
        ];

        // merge and update
        $form_controls['fields'] = $this->inject_field_controls(
            $form_controls['fields'],
            $custom_fields
        );
        $widget->update_control('form_fields', $form_controls);
    }

    /**
     * Renders the field HTML on the frontend.
     *
     * @param array $item  Field settings
     * @param int   $index Index in fields array
     * @param Form  $form
     */
    public function render($item, $index, $form)
    {
        // load both scripts & styles together
        Core::instance()->load_scripts();
        Core::instance()->load_styles();

        $field_id = esc_attr($item['custom_id']);
        $initial_val = (isset($_REQUEST[$field_id]) ? stripslashes($_REQUEST[$field_id]) : '');

        // set up attributes once
        $attr = [
            'class' => 'fileupload-filelist fileupload-input-filelist',
            // 'id' => "form-field-{$field_id}",
            'value' => $initial_val,
        ];
        $form->add_render_attribute("input{$index}", $attr);

        echo '<div class="elementor-field-wrapper" style="flex:1 1 100%;">';
        echo do_shortcode($item['wpcp_outofthebox_module_data']);
        ?><input type="hidden" <?php $form->print_render_attribute_string("input{$index}"); ?> /><?php
        echo '</div>';
    }

    /**
     * Process the submitted files JSON and store clickable links.
     *
     * @param array $field_settings
     */
    public function process_field($field_settings, Form_Record $record, Classes\Ajax_Handler $ajax_handler)
    {
        $id = $field_settings['id'];
        $files = json_decode($field_settings['raw_value'], true) ?: [];

        if (empty($files)) {
            return;
        }

        $links = array_map(function ($file) {
            $url = $file['preview_url'];
            if (!empty($file['shared_url'])) {
                $url = $file['shared_url'];
            }

            return esc_url(urldecode($url));
        }, $files);

        $record->update_field($id, 'value', implode(',', $links));
    }

    /**
     * Validates required file uploads.
     *
     * @param array $field_settings
     */
    public function validation($field_settings, Form_Record $record, Classes\Ajax_Handler $ajax_handler)
    {
        if (empty($field_settings['required'])) {
            return;
        }

        $files = json_decode($field_settings['raw_value'], true) ?: [];
        if (empty($files)) {
            $ajax_handler->add_error(
                $field_settings['id'],
                Ajax_Handler::get_default_message(Ajax_Handler::FIELD_REQUIRED)
            );
        }
    }

    /**
     * Elementor editor preview.
     *
     * Add a script to the footer of the editor preview screen.
     */
    public function editor_preview_footer()
    {
        add_action('wp_footer', [$this, 'content_template_script']);
    }

    /**
     * Content template script.
     *
     * Add content template alternative, to display the field in Elementor editor.
     */
    public function content_template_script()
    {
        ?>
<script>
jQuery(document).ready(() => {

    elementor.hooks.addFilter(
        'elementor_pro/forms/content_template/field/<?php echo $this->get_type(); ?>',
        function(inputField, item, i) {

            return `
            <div id="OutoftheBox" class="light upload" style="flex-basis: 100%;max-width: 100%;>
                <div class="OutoftheBox upload" style="width: 100%;">
                    <div class="fileupload-box -is-formfield -is-required -has-files" style="width:100%;max-width:100%;height:unset;">
                        <!-- FORM ELEMENTS -->
                        <div class=" fileupload-form">
                        <!-- END FORM ELEMENTS -->

                            <!-- UPLOAD BOX HEADER -->
                            <div class="fileupload-header">
                                <div class="fileupload-header-title">
                                    <div class="fileupload-empty">
                                        <div class="fileupload-header-text-title upload-add-file"><?php esc_html_e('Add your file', 'wpcloudplugins'); ?></div>
                                        <div class="fileupload-header-text-subtitle upload-add-folder"><a><?php esc_html_e('Or select a folder', 'wpcloudplugins'); ?></a></div>
                                    </div>
                                </div>
                                <div class="fileupload-header-button">
                                    <button class="fileupload-add-button button button-round-icon" type="button" title="<?php esc_html_e('Add your files', 'wpcloudplugins'); ?>" aria-expanded="false"><i class="eva eva-plus-outline eva-lg"></i></button>
                                </div>
                            </div>
                            <!-- END UPLOAD BOX HEADER -->
                        </div>
                    </div>
                </div>
            </div>`

        }, 10, 3
    );

});
</script>
<?php
    }
}
