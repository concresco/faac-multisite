<?php

namespace TheLion\OutoftheBox\Integrations;

use TheLion\OutoftheBox\Core;
use TheLion\OutoftheBox\Helpers;
use TheLion\OutoftheBox\Processor;
use TheLion\OutoftheBox\Settings;
use TheLion\OutoftheBox\Shortcodes;

defined('ABSPATH') || exit;

class ContactForm
{
    public function __construct()
    {
        if (!defined('WPCF7_VERSION') || false === version_compare(WPCF7_VERSION, '6.0', '>=')) {
            return;
        }

        add_action('wpcf7_init', [$this, 'add_shortcode_handler']);
        add_action('wpcf7_admin_init', [$this, 'add_tag_generator'], 60);

        add_action('admin_enqueue_scripts', [$this, 'add_admin_scripts']);
        add_action('wpcf7_admin_footer', [$this, 'load_admin_scripts']);

        add_filter('wpcf7_mail_tag_replaced_outofthebox*', [$this, 'set_email_tag'], 999, 4);
        add_filter('wpcf7_mail_tag_replaced_outofthebox', [$this, 'set_email_tag'], 999, 4);

        add_filter('outofthebox_private_folder_name', [$this, 'new_personal_folder_name'], 10, 2);
        add_filter('outofthebox_private_folder_name_guests', [$this, 'rename_personal_folder_names_for_guests'], 10, 1);
    }

    public function add_admin_scripts($hook_suffix)
    {
        if (false === strpos($hook_suffix, 'wpcf7')) {
            return;
        }

        Core::instance()->load_scripts();
        Core::instance()->load_styles();

        wp_enqueue_script('WPCloudplugin.Libraries');
        wp_enqueue_script('OutoftheBox.ShortcodeBuilder');
        wp_enqueue_style('OutoftheBox');
    }

    public function load_admin_scripts()
    {
        wp_register_script('OutoftheBox.ContactForm7', plugins_url('ContactForm7.js', __FILE__), ['jquery'], OUTOFTHEBOX_VERSION, true);
        wp_enqueue_script('OutoftheBox.ContactForm7');
    }

    public function add_tag_generator()
    {
        if (class_exists('WPCF7_TagGenerator')) {
            $tag_generator = \WPCF7_TagGenerator::get_instance();
            $tag_generator->add('outofthebox', 'Dropbox', [$this, 'tag_generator_body'], ['version' => 2]);
        }
    }

    public function tag_generator_body($contact_form, $args = '')
    {
        $args = wp_parse_args($args, []);

        ?>
<header class="description-box">
    <h3><?php esc_html_e('Generate a cloud upload field.', 'wpcloudplugins'); ?></h3>
    <p><?php esc_html_e('Generate a form-tag for this upload field.', 'wpcloudplugins'); ?></p>
</header>
<div class="control-box">

    <fieldset>
        <legend> <?php esc_html_e('Field type', 'contact-form-7'); ?> </legend>
        <select data-tag-part="basetype">
            <option value="outofthebox">Dropbox Upload</option>
        </select>
        <br />
        <label><input type="checkbox" data-tag-part="type-suffix" value="*"> <?php esc_html_e('Required field', 'contact-form-7'); ?></label>
    </fieldset>

    <fieldset>
        <legend> <?php esc_html_e('Name', 'contact-form-7'); ?> </legend>
        <input type="text" data-tag-part="name" pattern="[A-Za-z][A-Za-z0-9_\-]*">
    </fieldset>

    <fieldset>
        <legend> <label for="tag-generator-panel-outofthebox-module-id"><?php esc_html_e('Module', 'wpcloudplugins'); ?></label> </legend>
        <input type="text" id="tag-generator-panel-outofthebox-module-id" data-tag-part="option" data-tag-option="module-id:" pattern="[0-9]*" />
        <br />
        <button id="tag-generator-panel-outofthebox-module-selector" type="button" class="button-secondary" data-target="tag-generator-panel-outofthebox-module-selector"><?php esc_html_e('Select Module', 'wpcloudplugins'); ?></button>

        <dialog id="tag-generator-panel-outofthebox-module-selector-dialog" class="tag-generator-dialog" style="height:768px; width:1024px; border:0; padding:0; overflow:hidden;">
            <iframe id="outofthebox-shortcode-iframe" title="Module Selector" src="" data-src='<?php echo OUTOFTHEBOX_ADMIN_URL; ?>?action=outofthebox-getpopup&type=modules&foruploadfield=1&callback=wpcp_oftb_cf7_add_content' width='1024' height='768' tabindex='-1' style='border:none'></iframe>
        </dialog>

    </fieldset>

</div> <!-- /.control-box -->
<footer class="insert-box">
    <div class="flex-container">
        <input type="text" class="code" readonly="readonly" onfocus="this.select();" data-tag-part="tag" aria-label="The form-tag to be inserted into the form template">
        <button type="button" class="button-primary" data-taggen="insert-tag"><?php esc_html_e('Insert Tag', 'contact-form-7'); ?></button>
    </div>
    <p class="mail-tag-tip">
        To use the user input in the email, insert the corresponding mail-tag <strong data-tag-part="mail-tag"></strong> into the email template.
    </p>
</footer>

<?php
    }

    /**
     * Add shortcode handler to CF7.
     */
    public function add_shortcode_handler()
    {
        if (function_exists('wpcf7_add_form_tag')) {
            wpcf7_add_form_tag(
                ['outofthebox', 'outofthebox*'],
                [$this, 'shortcode_handler'],
                true
            );
        }
    }

    public function shortcode_handler($tag)
    {
        $tag = new \WPCF7_FormTag($tag);

        if (empty($tag->name)) {
            return '';
        }

        $required = ('*' == substr($tag->type, -1));
        if ($required) {
            add_filter('outofthebox_shortcode_set_options', [$this, 'set_required_shortcode'], 10, 1);
        }

        // Shortcode
        $module_id = $tag->get_option('module-id', '', true);

        if (!empty($module_id)) {
            $shortcode = '[outofthebox module="'.$module_id.'"]';
        } else {
            $shortcode = Shortcodes::decode(urldecode($tag->get_option('shortcode', '', true)));
        }

        $return = apply_filters('outofthebox-wpcf7-render-shortcode', do_shortcode($shortcode));
        $return .= "<input type='hidden' name='".$tag->name."' class='fileupload-filelist fileupload-input-filelist'/>";

        wp_enqueue_script('OutoftheBox.ContactForm7');

        return $return;
    }

    public function set_required_shortcode($options)
    {
        $options['class'] .= ' wpcf7-validates-as-required';

        return $options;
    }

    public function set_email_tag($output, $submission, $ashtml, $mail_tag)
    {
        $filelist = stripslashes($submission);

        return $this->render_uploaded_files_list($filelist, $ashtml);
    }

    public function render_uploaded_files_list($data, $ashtml = true)
    {
        return apply_filters('outofthebox_render_formfield_data', $data, $ashtml, $this);
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

        if ('cf7_upload_box' !== Processor::instance()->get_shortcode_option('class')) {
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
        if ('cf7_upload_box' !== Processor::instance()->get_shortcode_option('class')) {
            return $personal_folder_name_guest;
        }

        $prefix = Settings::get('userfolder_name_guest_prefix');

        return str_replace($prefix, '', $personal_folder_name_guest);
    }
}

new ContactForm();
