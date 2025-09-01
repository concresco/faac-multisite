<?php

namespace TheLion\OutoftheBox\Integrations;

use TheLion\OutoftheBox\Accounts;
use TheLion\OutoftheBox\API;
use TheLion\OutoftheBox\App;
use TheLion\OutoftheBox\Client;
use TheLion\OutoftheBox\Helpers;
use TheLion\OutoftheBox\Processor;

defined('ABSPATH') || exit;

class GravityPDF
{
    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        if (false === get_option('gfpdf_current_version') && false === class_exists('GFPDF_Core')) {
            return;
        }

        add_action('gfpdf_post_save_pdf', [$this, 'outofthebox_post_save_pdf'], 10, 5);
        add_filter('gfpdf_form_settings_advanced', [$this, 'outofthebox_add_pdf_setting'], 10, 1);
    }

    /*
     * GravityPDF
     * Basic configuration in Form Settings -> PDF:
     *
     * Always Save PDF = YES
     * [DROPBOX] Export PDF = YES
     * [DROPBOX] Path = Full path where the PDFs need to be stored
     */

    public function outofthebox_add_pdf_setting($fields)
    {
        $fields['outofthebox_save_to_dropbox'] = [
            'id' => 'outofthebox_save_to_dropbox',
            'name' => '[DROPBOX] Export PDF',
            'desc' => 'Save the created PDF to Dropbox',
            'type' => 'radio',
            'options' => [
                'Yes' => esc_html__('Yes'),
                'No' => esc_html__('No'),
            ],
            'std' => esc_html__('No'),
        ];

        $main_account = Accounts::instance()->get_primary_account();

        $account_id = '';
        if (!empty($main_account)) {
            $account_id = $main_account->get_id();
        }

        $fields['outofthebox_save_to_account_id'] = [
            'id' => 'outofthebox_save_to_account_id',
            'name' => '[DROPBOX] Account ID',
            'desc' => 'Account ID where the PDFs need to be stored. E.g. <code>'.$account_id.'</code>. Or use <code>%upload_account_id%</code> for the Account ID for the upload location of the plugin Upload Box field.',
            'type' => 'text',
            'std' => $account_id,
        ];

        $fields['outofthebox_save_to_dropbox_path'] = [
            'id' => 'outofthebox_save_to_dropbox_path',
            'name' => '[DROPBOX] Path',
            'desc' => 'Full path where the PDFs need to be stored. E.g. <code>/path/to/folder</code>. Or use <code>%upload_folder_id%</code> for the Account ID for the upload location of the plugin Upload Box field.',
            'type' => 'text',
            'std' => '',
        ];

        return $fields;
    }

    public function outofthebox_post_save_pdf($pdf_path, $filename, $settings, $entry, $form)
    {
        if (!isset($settings['outofthebox_save_to_dropbox']) || 'No' === $settings['outofthebox_save_to_dropbox']) {
            return false;
        }

        if (!isset($settings['outofthebox_save_to_account_id'])) {
            // Fall back for older PDF configurations
            $settings['outofthebox_save_to_account_id'] = Accounts::instance()->get_primary_account()->get_id();
        }

        // Placeholders
        list($upload_account_id, $upload_folder_path) = $this->get_upload_location($entry, $form);

        if (false !== strpos($settings['outofthebox_save_to_account_id'], '%upload_account_id%')) {
            $settings['outofthebox_save_to_account_id'] = $upload_account_id;
        }

        if (false !== strpos($settings['outofthebox_save_to_dropbox_path'], '%upload_folder_id%')
        ) {
            $settings['outofthebox_save_to_dropbox_path'] = $upload_folder_path;
        }

        $account_id = apply_filters('outofthebox_gravitypdf_set_account_id', $settings['outofthebox_save_to_account_id'], $settings, $entry, $form, Processor::instance());

        $requested_account = Accounts::instance()->get_account_by_id($account_id);

        if (null !== $requested_account) {
            App::set_current_account($requested_account);
        } else {
            Helpers::log_error('Cannot use the requested account as it is not linked with the plugin.', 'GravityPDF', ['account_id' => $account_id], __LINE__);

            exit;
        }

        App::instance();

        $upload_path = Helpers::clean_folder_path($settings['outofthebox_save_to_dropbox_path'].'/'.$filename);

        $file = (object) [
            'tmp_path' => $pdf_path,
            'type' => mime_content_type($pdf_path),
            'name' => $filename,
            'size' => filesize($pdf_path),
        ];

        try {
            $result = API::upload_file($file, $upload_path);
        } catch (\Exception $ex) {
            return false;
        }

        // Add url to PDF file in cloud
        $pdfs = \GPDFAPI::get_entry_pdfs($entry['id']);

        foreach ($pdfs as $pid => $pdf) {
            if ('Yes' === $pdf['outofthebox_save_to_dropbox']) {
                $pdf['outofthebox_pdf_url'] = 'https://www.dropbox.com/home/'.trim($result->get_path_display(), '/');
                \GPDFAPI::update_pdf($form['id'], $pid, $pdf);
            }
        }
    }

    public function get_upload_location($entry, $form)
    {
        $account_id = '';
        $folder_path = '';

        if (!is_array($form['fields'])) {
            return [$account_id, $folder_path];
        }

        foreach ($form['fields'] as $field) {
            if ('outofthebox' !== $field->type) {
                continue;
            }

            if (!isset($entry[$field->id])) {
                continue;
            }

            $uploadedfiles = json_decode($entry[$field->id]);

            if ((null !== $uploadedfiles) && (count((array) $uploadedfiles) > 0)) {
                $first_entry = reset($uploadedfiles);

                $account_id = $first_entry->account_id;
                $requested_account = Accounts::instance()->get_account_by_id($account_id);
                App::set_current_account($requested_account);

                $cached_entry = Client::instance()->get_entry($first_entry->hash, false);
                $folder_path = $cached_entry->get_parent();
            }
        }

        return [$account_id, $folder_path];
    }
}

new GravityPDF();
