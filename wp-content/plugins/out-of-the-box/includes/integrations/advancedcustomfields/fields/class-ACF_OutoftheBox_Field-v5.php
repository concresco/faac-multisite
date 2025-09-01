<?php

namespace TheLion\OutoftheBox\Integrations;

use TheLion\OutoftheBox\API;
use TheLion\OutoftheBox\Client;
use TheLion\OutoftheBox\Core;
use TheLion\OutoftheBox\Helpers;

defined('ABSPATH') || exit;

class ACF_OutoftheBox_Field extends \acf_field
{
    /**
     * Environment values relating to the theme or plugin.
     *
     * @var array plugin or theme context such as 'url' and 'version'
     */
    private $env;

    public function initialize()
    {
        $this->name = 'OutoftheBox_Field';
        $this->label = 'Dropbox';
        $this->category = 'wpcloudplugins';
        $this->description = esc_html__('Pick and choose files and folders directly from your cloud account.', 'wpcloudplugins');
        $this->defaults = [
            'max_items' => -1,
            'return_format' => 'array',
            'root_account_id' => null,
            'root_folder_id' => null,
        ];
        $this->l10n = [
        ];

        $this->env = [
            'url' => site_url(str_replace(ABSPATH, '', __DIR__)),
            'version' => \OUTOFTHEBOX_VERSION,
        ];

        $this->tutorial_url = 'https://vimeo.com/645169317';
        $this->preview_image = OUTOFTHEBOX_ROOTPATH.'/css/images/dropbox_logo.svg';

        $this->_load_hooks();
    }
    /*
    *  render_field_settings()
    *
    *  Create extra settings for your field. These are visible when editing a field
    *
    *  @type	action
    *  @since	3.6
    *  @date	23/01/13
    *
    *  @param	$field (array) the $field being edited
    *  @return	n/a
    */

    public function render_field_settings($field)
    {
        /*
        *  acf_render_field_setting
        *
        *  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
        *  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
        *
        *  More than one setting can be added by copy/paste the above code.
        *  Please note that you must also have a matching $defaults value for the field name (font_size)
        */

        acf_render_field_setting(
            $field,
            [
                'label' => 'Return Value',
                'instructions' => __('Specify the returned value on front end', 'acf').'. If the return value is a string and there is more than one item selected, then the values will be separated by a comma(,).',
                'type' => 'radio',
                'name' => 'return_format',
                'layout' => 'vertical',
                'choices' => [
                    'array' => 'Items Array —&nbsp;<small>an array containing the metadata of each item.</small>',
                    'entry_id' => 'Item ID',
                    'name' => 'Item Name',
                    'direct_url' => 'Direct URL —&nbsp;<small>a URL to the item in the cloud.</small>',
                    'download_url' => 'Download URL —&nbsp;<small>a download URL that can be used by anyone to download the file.</small>',
                    'shortlived_download_url' => 'Temporarily download URL —&nbsp;<small>an direct, short-lived, download URL to the file in the cloud.</small>',
                    'shared_url' => 'Public URL —&nbsp;<small>a shared URL to the item. Accessible by anyone with the link.</small>',
                    'embed_url' => 'Embed URL —&nbsp;<small>a shared URL for embedding the file in an iFrame. Only available for supported formats.</small>',
                    'thumbnail_url' => 'Thumbnail URL —&nbsp;<small>an URL to a thumbnail of the file. If no thumbnail is available icon url will be provided.</small>',
                ],
            ]
        );

        acf_render_field_setting(
            $field,
            [
                'label' => 'Extra data included in **Array** Return Value (requires API call)',
                'instructions' => 'What information should be available for the files objects besides the default <code>name</code>, <code>size</code>, <code>description</code>, <code>icon_url</code>, <code>direct_url</code> and <code>download_url</code>',
                'type' => 'checkbox',
                'layout' => 'vertical',
                'name' => 'return_data',
                'choices' => [
                    'shortlived_download_url' => 'Temporarily download URL —&nbsp;<small><code>shortlived_download_url</code></small>',
                    'shared_url' => 'Public URL —&nbsp;<small><code>shared_url</code></small>',
                    'embed_url' => 'Embed URL —&nbsp;<small><code>embed_url</code></small>',
                    'thumbnail_url' => 'Thumbnail URL —&nbsp;<small><code>thumbnail_url</code></small>',
                ],
            ]
        );

        if (version_compare(\ACF_MAJOR_VERSION, '6', '<')) {
            acf_render_field_setting(
                $field,
                [
                    'label' => __('Maximum number of files & folders allowed', 'acf'),
                    'instructions' => 'Define how many items can be selected by content editors. <code>-1</code> means unlimited',
                    'name' => 'max_items',
                    'type' => 'number',
                ]
            );
        }
    }

    public function render_field_validation_settings($field)
    {
        acf_render_field_setting(
            $field,
            [
                'label' => __('Maximum number of files & folders allowed', 'acf'),
                'instructions' => 'Define how many items can be selected by content editors. <code>-1</code> means unlimited',
                'name' => 'max_items',
                'type' => 'number',
            ]
        );
    }

    /*
    *  render_field()
    *
    *  Create the HTML interface for your field
    *
    *  @param	$field (array) the $field being rendered
    *
    *  @type	action
    *  @since	3.6
    *  @date	23/01/13
    *
    *  @param	$field (array) the $field being edited
    *  @return	n/a
    */

    public function render_field($field)
    {
        acf_hidden_input(
            [
                'name' => $field['name'],
                'value' => empty($field['value']) ? '{}' : json_encode($field['value']),
                'data-name' => 'id',
            ]
        ); ?>
<table class="wpcp-acf-items-table wp-list-table widefat striped">
    <thead>
        <th style="width: 18px;"></th>
        <th><?php esc_html_e('Name', 'wpcloudplugins'); ?></th>
        <th><?php esc_html_e('File ID', 'wpcloudplugins'); ?></th>
        <th style="width: 175px;"></th>
    </thead>
    <tbody>
    </tbody>
</table>
<br />
<a href="#" class="button button-primary button-large wpcp-acf-add-item" data-max-items="<?php echo $field['max_items']; ?>"><?php printf(esc_html__('Choose from %s', 'wpcloudplugins'), 'Dropbox'); ?> <?php echo ($field['max_items'] > 0) ? "(max: {$field['max_items']} items)" : ''; ?></a>

<?php
             include 'template_file_selector.php';
    }

    /*
    *  input_admin_enqueue_scripts()
    *
    *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
    *  Use this action to add CSS + JavaScript to assist your render_field() action.
    *
    *  @type	action (admin_enqueue_scripts)
    *  @since	3.6
    *  @date	23/01/13
    *
    *  @param	n/a
    *  @return	n/a
    */

    public function input_admin_enqueue_scripts()
    {
        // register & include JS
        Core::instance()->load_scripts();
        Core::instance()->load_styles();

        wp_enqueue_script('OutoftheBox.AdminUI');
        wp_enqueue_style('WPCloudPlugins.AdminUI');

        wp_register_script('WPCP_ACF_'.$this->name, plugins_url('../assets/js/input.js', __FILE__), ['acf-input', 'OutoftheBox'], \OUTOFTHEBOX_VERSION);
        wp_enqueue_script('WPCP_ACF_'.$this->name);
    }

    /*
    *  load_value()
    *
    *  This filter is applied to the $value after it is loaded from the db
    *
    *  @type	filter
    *  @since	3.6
    *  @date	23/01/13
    *
    *  @param	$value (mixed) the value found in the database
    *  @param	$post_id (mixed) the $post_id from which the value was loaded
    *  @param	$field (array) the field array holding all the field options
    *  @return	$value
    */

    public function load_value($value, $post_id, $field)
    {
        if (empty($value)) {
            return [];
        }

        if (false === strpos($value, 'embed_url')) {
            update_field($field['key'], $value, $post_id);

            return get_field($field['key'], $post_id);
        }

        return json_decode($value, true);
    }

    /*
    *  update_value()
    *
    *  This filter is applied to the $value before it is saved in the db
    *
    *  @type	filter
    *  @since	3.6
    *  @date	23/01/13
    *
    *  @param	$value (mixed) the value found in the database
    *  @param	$post_id (mixed) the $post_id from which the value was loaded
    *  @param	$field (array) the field array holding all the field options
    *  @return	$value
    */

    public function update_value($value, $post_id, $field)
    {
        if (!is_array($value)) {
            $start_pos_json = strpos($value, '{');
            $json = substr($value, $start_pos_json);
            $entries = json_decode(wp_unslash($json), true);
        } else {
            $entries = $value;
        }

        if (empty($entries)) {
            return [];
        }

        foreach ($entries as $entry_id => $entry) {
            if (!empty($entries[$entry_id]['embed_url'])) {
                continue; // Don't get all data again if it is already present
            }

            API::set_account_by_id($entry['account_id']);
            $full_entry = Client::instance()->get_entry($entry_id, false);

            $entry_details = [
                'account_id' => $entry['account_id'],
                'entry_id' => $entry_id,
                'name' => '',
                'description' => '',
                'size' => '',
                'direct_url' => '',
                'download_url' => '',
                'shortlived_download_url' => '',
                'shared_url' => '',
                'embed_url' => '',
                'thumbnail_url' => '',
                'icon_url' => '',
            ];

            // Backwards compatibilty
            $entries[$entry_id]['id'] = $entry_id;

            // Name
            $entry_details['name'] = $full_entry->get_name();

            // Description
            $entry_details['description'] = $full_entry->get_description();

            // Size
            $size = $full_entry->get_size();
            $entry_details['size'] = ($size > 0) ? Helpers::bytes_to_size_1024($size) : '';

            // Direct URL
            $entry_details['direct_url'] = 'https://www.dropbox.com/home/'.$full_entry->get_path_display();

            // Download link
            $entry_details['download_url'] = OUTOFTHEBOX_ADMIN_URL."?action=outofthebox-acf-download&pid={$post_id}&fid={$field['key']}&aid={$entry['account_id']}&id={$entry_id}";

            // Icon URL
            $entry_details['icon_url'] = $full_entry->get_icon();

            $entries[$entry_id] = $entry_details;
        }

        if ($field['max_items'] > 0) {
            $entries = array_slice($entries, 0, $field['max_items']);
        }

        return json_encode(apply_filters('outofthebox_acf_field_update_value', $entries, $post_id, $field));
    }

    /*
    *  format_value()
    *
    *  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
    *
    *  @type	filter
    *  @since	3.6
    *  @date	23/01/13
    *
    *  @param	$value (mixed) the value which was loaded from the database
    *  @param	$post_id (mixed) the $post_id from which the value was loaded
    *  @param	$field (array) the field array holding all the field options
    *
    *  @return	$value (mixed) the modified value
    */

    public function format_value($entries, $post_id, $field)
    {
        // bail early if no value
        if (empty($entries)) {
            return [];
        }

        if (empty($field['return_data'])) {
            $field['return_data'] = [];
        }

        foreach ($entries as $entry_id => $entry) {
            API::set_account_by_id($entry['account_id']);
            $full_entry = Client::instance()->get_entry($entry_id, false);

            // Path
            $entries[$entry_id]['path'] = $full_entry->get_path();

            // Download link
            $entries[$entry_id]['download_url'] = OUTOFTHEBOX_ADMIN_URL."?action=outofthebox-acf-download&pid={$post_id}&fid={$field['key']}&aid={$entry['account_id']}&id={$entry_id}";

            // Thumbnail
            if (in_array('thumbnail_url', $field['return_data'], true) || 'thumbnail_url' === $field['return_format']) {
                if (empty($entries[$entry_id]['thumbnail_url'])) {
                    $entries[$entry_id]['thumbnail_url'] = ($full_entry->has_own_thumbnail() ? Client::instance()->get_thumbnail($full_entry, true, 640, 480) : $full_entry->get_icon_retina());
                }
            } else {
                $entries[$entry_id]['thumbnail_url'] = null;
            }

            // Embed URL
            if (in_array('embed_url', $field['return_data'], true) || 'embed_url' === $field['return_format']) {
                if (empty($entries[$entry_id]['embed_url'])) {
                    $entries[$entry_id]['embed_url'] = Client::instance()->get_embedded_link($full_entry);

                    // Update this information
                    update_field($field['key'], $entries, $post_id);
                }
            } else {
                $entries[$entry_id]['embed_url'] = null;
            }

            // Shared (public) URL
            if (in_array('shared_url', $field['return_data'], true) || 'shared_url' === $field['return_format']) {
                if (empty($entries[$entry_id]['shared_url'])) {
                    $entries[$entry_id]['shared_url'] = Client::instance()->get_shared_link($full_entry);

                    // Update this information
                    update_field($field['key'], $entries, $post_id);
                }
            } else {
                $entries[$entry_id]['shared_url'] = null;
            }

            // Short-lived direct download URL
            if (in_array('shortlived_download_url', $field['return_data'], true) || 'shortlived_download_url' === $field['return_format']) {
                if (empty($entries[$entry_id]['shortlived_download_url'])) {
                    $entries[$entry_id]['shortlived_download_url'] = Client::instance()->get_temporarily_link($full_entry);
                }
            } else {
                $entries[$entry_id]['shortlived_download_url'] = null;
            }
        }

        // return
        if ('array' == $field['return_format']) {
            $return_value = $entries;
        } else {
            $columns = \array_column($entries, $field['return_format']);

            $return_value = implode(',', $columns);
        }

        return apply_filters('outofthebox_acf_field_format_value', $return_value, $entries, $post_id, $field);
    }

    /**
     * Start the download for entry with $id.
     */
    public function start_download()
    {
        if (!isset($_REQUEST['pid']) || !isset($_REQUEST['fid']) || !isset($_REQUEST['aid']) || !isset($_REQUEST['id'])) {
            http_response_code(400);

            exit;
        }

        $entries = get_field(sanitize_key($_REQUEST['fid']), sanitize_key($_REQUEST['pid']), false);

        if (empty($entries) || !isset($entries[$_REQUEST['id']])) {
            http_response_code(401);

            exit;
        }

        $account_id = sanitize_text_field($_REQUEST['aid']);
        API::set_account_by_id($account_id);

        $full_entry = Client::instance()->get_entry($_REQUEST['id'], false);

        if (empty($full_entry)) {
            http_response_code(404);
        }

        if ($full_entry->is_dir()) {
            try {
                $download_url = Client::instance()->get_shared_link($full_entry).'&dl=1';
            } catch (\Exception $ex) {
                return false;
            }

            header('Location: '.$download_url);

            exit;
        }

        $temporarily_link = Client::instance()->get_temporarily_link($full_entry);
        header('Location: '.$temporarily_link);

        exit;
    }

    private function _load_hooks()
    {
        add_action('wp_ajax_nopriv_outofthebox-acf-download', [$this, 'start_download']);
        add_action('wp_ajax_outofthebox-acf-download', [$this, 'start_download']);
    }
}

// initialize
new ACF_OutoftheBox_Field($this->settings);
