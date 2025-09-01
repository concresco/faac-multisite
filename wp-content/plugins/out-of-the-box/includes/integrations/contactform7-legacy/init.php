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
        if (!defined('WPCF7_VERSION') || false === version_compare(WPCF7_VERSION, '5.0', '>=')) {
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
            $tag_generator->add('outofthebox', 'Dropbox', [$this, 'tag_generator_body']);
        }
    }

    public function tag_generator_body($contact_form, $args = '')
    {
        $args = wp_parse_args($args, []);
        $type = 'outofthebox';

        $description = esc_html__('Generate a form-tag for this upload field.', 'wpcloudplugins'); ?>
<div class="control-box">
    <fieldset>
        <legend><?php echo sprintf(esc_html($description)); ?></legend>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e('Field type', 'contact-form-7'); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e('Field type', 'contact-form-7'); ?></legend>
                            <label><input type="checkbox" name="required" /> <?php esc_html_e('Required field', 'contact-form-7'); ?></label>
                        </fieldset>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="<?php echo esc_attr($args['content'].'-name'); ?>"><?php esc_html_e('Name', 'contact-form-7'); ?></label></th>
                    <td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr($args['content'].'-name'); ?>" /></td>
                </tr>

                <tr>
                    <th scope="row"><label for="<?php echo esc_attr($args['content'].'-shortcode'); ?>"><?php esc_html_e('Shortcode', 'wpcloudplugins'); ?></label></th>
                    <td>
                        <input type="hidden" name="shortcode" class="outofthebox-shortcode-value large-text option" id="<?php echo esc_attr($args['content'].'-shortcode'); ?>" />
                        <textarea id="outofthebox-shortcode-decoded-value" rows="6" style="margin-bottom:15px;display:none;width: 400px;word-wrap: break-word;"></textarea>
                        <input type="button" class="button button-primary OutoftheBox-CF-shortcodegenerator " value="<?php esc_attr_e('Configure Module', 'wpcloudplugins'); ?>" />
                        <iframe id="outofthebox-shortcode-iframe" title="Module Selector" src="about:blank" data-src='<?php echo OUTOFTHEBOX_ADMIN_URL; ?>?action=outofthebox-getpopup&type=modules&foruploadfield=1&callback=wpcp_oftb_cf7_add_content' width='100%' tabindex='-1' style="display:none; border:0"></iframe>
                    </td>
                </tr>

            </tbody>
        </table>
    </fieldset>
</div>

<div class="insert-box">
    <input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

    <div class="submitbox">
        <input type="button" class="button button-primary insert-tag" value="<?php esc_attr_e(esc_html__('Insert Tag', 'contact-form-7')); ?>" />
    </div>

    <br class="clear" />

    <p class="description mail-tag"><label for="<?php echo esc_attr($args['content'].'-mailtag'); ?>"><?php echo sprintf(esc_html('To list the uploads in your email, insert the mail-tag (%s) in the Mail tab.'), '<strong><span class="mail-tag"></span></strong>'); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr($args['content'].'-mailtag'); ?>" /></label></p>
</div>
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
        $shortcode = Shortcodes::decode(urldecode($tag->get_option('shortcode', '', true)));
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
