<?php

namespace TheLion\OutoftheBox\Integrations;

use TheLion\OutoftheBox\Accounts;
use TheLion\OutoftheBox\App;
use TheLion\OutoftheBox\Client;
use TheLion\OutoftheBox\Core;
use TheLion\OutoftheBox\Entry;

defined('ABSPATH') || exit;

class EasyDigitalDownloads
{
    public function __construct()
    {
        add_filter('edd_requested_file', [$this, 'do_download'], 10, 1);
        add_action('edd_meta_box_files_fields', [$this, 'render_file_selector'], 20, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts()
    {
        if (function_exists('get_current_screen')) {
            $current_screen = get_current_screen();
            if (isset($current_screen->post_type) && 'download' == $current_screen->post_type) {
                Core::instance()->load_scripts();
                Core::instance()->load_styles();

                // enqueue scripts/styles
                wp_enqueue_style('wpcp-dropbox-edd', plugins_url('backend.css', __FILE__), ['WPCloudPlugins.AdminUI'], OUTOFTHEBOX_VERSION);
                wp_enqueue_script('wpcp-dropbox-edd', plugins_url('backend.js', __FILE__), ['OutoftheBox.AdminUI'], OUTOFTHEBOX_VERSION);

                // register translations
                $translation_array = [
                    'choose_from' => sprintf(esc_html__('Add File', 'wpcloudplugins'), 'Dropbox'),
                    'download_url' => '?action=wpcp-dropbox-edd-direct-download&id=',
                    'notification_success_file_msg' => sprintf(esc_html__('%s added as downloadable file!', 'wpcloudplugins'), '{filename}'),
                    'notification_failed_file_msg' => sprintf(esc_html__('Cannot add %s!', 'wpcloudplugins'), '{filename}'),
                ];

                wp_localize_script('wpcp-dropbox-edd', 'outofthebox_edd_translation', $translation_array);
            }
        }
    }

    public function render_file_selector($post_id = 0, $type = '')
    {
        include_once 'template_file_selector.php';
    }

    public function do_download($requested_file)
    {
        if (!strpos($requested_file, 'wpcp-dropbox-edd-direct-download')) {
            return $requested_file;
        }

        $entry = $this->get_entry_for_download_by_url($requested_file);

        if (empty($entry)) {
            wp_die(__('Error 104: Sorry, this file could not be downloaded.', 'easy-digital-downloads'), __('Error Downloading File', 'easy-digital-downloads'), 403);

            exit;
        }

        return $this->get_redirect_url_for_entry($entry);
    }

    /**
     * @param string $file_path
     *
     * @return Entry
     */
    public function get_entry_for_download_by_url($file_path)
    {
        $download_url = parse_url($file_path);

        if (isset($download_url['query'])) {
            parse_str($download_url['query'], $download_url_query);
        } else {
            // In some occasions the file name contains a #, causing the parameters to end up in the fragment part of the url
            parse_str($download_url['fragment'], $download_url_query);
        }

        $entry_id = $download_url_query['id'];
        $entry_path = urldecode(base64_decode($entry_id));
        $account_id = $download_url_query['account_id'];

        $account = Accounts::instance()->get_account_by_id($account_id);

        if (null === $account) {
            return false;
        }

        App::set_current_account($account);

        $entry = Client::instance()->get_entry($entry_path, false);

        if (false === $entry) {
            return false;
        }

        return $entry;
    }

    public function get_redirect_url_for_entry(Entry $entry)
    {
        $transient_url = self::get_download_url_transient($entry->get_id());
        if (!empty($transient_url)) {
            return $transient_url;
        }

        if ($entry->is_dir()) {
            $link_settings = [];
            // Create temporarily shared links for Pro and Business accounts. Basic accounts don't support this.
            if ('basic' !== App::get_current_account()->get_type()) {
                $expire_date = current_datetime()->modify('+60 seconds');
                $link_settings['expires'] = $expire_date->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');

                $link_settings = [
                    ['expires'],
                ];
            }

            $downloadlink = Client::instance()->get_shared_link($entry, $link_settings).'&dl=1';
        } else {
            $downloadlink = Client::instance()->get_temporarily_link($entry);
        }

        do_action('outofthebox_log_event', 'outofthebox_downloaded_entry', $entry);

        self::set_download_url_transient($entry->get_id(), $downloadlink);

        return $downloadlink;
    }

    public static function get_download_url_transient($entry_id)
    {
        return get_transient('outofthebox_wc_download_'.$entry_id);
    }

    public static function set_download_url_transient($entry_id, $url)
    {
        // Update progress
        return set_transient('outofthebox_wc_download_'.$entry_id, $url, 5 * MINUTE_IN_SECONDS);
    }
}

new EasyDigitalDownloads();
