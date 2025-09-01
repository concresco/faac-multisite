<?php

namespace TheLion\OutoftheBox\Integrations;

use TheLion\OutoftheBox\Accounts;
use TheLion\OutoftheBox\App;
use TheLion\OutoftheBox\Client;
use TheLion\OutoftheBox\Core;
use TheLion\OutoftheBox\Entry;

class WooCommerce_Downloads
{
    public function __construct()
    {
        // Actions
        add_action('woocommerce_download_file_force', [$this, 'do_direct_download'], 1, 2);
        add_action('woocommerce_download_file_xsendfile', [$this, 'do_xsendfile_download'], 1, 2);
        add_action('woocommerce_download_file_redirect', [$this, 'do_redirect_download'], 1, 2);

        if (class_exists('WC_Product_Documents')) {
            add_action('wp_ajax_nopriv_outofthebox-wcpd-direct-download', [$this, 'wc_product_documents_download_via_url']);
            add_action('wp_ajax_outofthebox-wcpd-direct-download', [$this, 'wc_product_documents_download_via_url']);
            add_filter('wc_product_documents_link_target', [$this, 'wc_product_documents_open_link_in_new_window'], 10, 4);
            add_filter('wc_product_documents_get_sections', [$this, 'wc_product_documents_update_document_urls'], 10, 3);
        }

        // Load custom scripts in the admin area
        if (is_admin()) {
            add_action('admin_enqueue_scripts', [$this, 'add_scripts']);
            add_action('edit_form_advanced', [$this, 'render_file_selector'], 1, 1); // Classic Editor on Product edit page
            add_action('block_editor_meta_box_hidden_fields', [$this, 'render_file_selector'], 1, 1); // Gutenberg Editor on Product edit page
        }
    }

    /**
     * Render the File Browser to allow the user to add files to the Product.
     *
     * @return string
     */
    public function render_file_selector(?\WP_Post $post = null)
    {
        if (isset($post) && 'product' !== $post->post_type) {
            return;
        }

        include 'template_file_selector.php';
    }

    /**
     * Load all the required Script and Styles.
     */
    public function add_scripts()
    {
        $current_screen = get_current_screen();

        if (!in_array($current_screen->id, ['product', 'shop_order'])) {
            return;
        }

        Core::instance()->load_styles();
        Core::instance()->load_scripts();

        // register scripts/styles
        wp_register_style('outofthebox-woocommerce', plugins_url('backend.css', __FILE__), OUTOFTHEBOX_VERSION);
        wp_register_script('outofthebox-woocommerce', plugins_url('backend.js', __FILE__), ['jquery'], OUTOFTHEBOX_VERSION);

        // enqueue scripts/styles
        wp_enqueue_style('outofthebox-woocommerce');
        wp_enqueue_script('outofthebox-woocommerce');

        wp_enqueue_script('OutoftheBox.AdminUI');
        wp_enqueue_style('WPCloudPlugins.AdminUI');

        // register translations
        $translation_array = [
            'choose_from' => sprintf(esc_html__('Add File', 'wpcloudplugins'), 'Dropbox'),
            'download_url' => '?action=outofthebox-wc-direct-download&id=',
            'file_browser_url' => OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-getwoocommercepopup',
            'wcpd_url' => OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-wcpd-direct-download&id=',
            'notification_success_file_msg' => sprintf(esc_html__('%s added as downloadable file!', 'wpcloudplugins'), '{filename}'),
            'notification_failed_file_msg' => sprintf(esc_html__('Cannot add %s!', 'wpcloudplugins'), '{filename}'),
        ];

        wp_localize_script('outofthebox-woocommerce', 'outofthebox_woocommerce_translation', $translation_array);
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

        // Fallback for old embed urls without account info
        if (!isset($download_url_query['account_id'])) {
            $primary_account = Accounts::instance()->get_primary_account();
            if (null === $primary_account) {
                return false;
            }
            $account_id = $primary_account->get_id();
        } else {
            $account_id = $download_url_query['account_id'];
        }

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

    public function wc_product_documents_download_via_url()
    {
        if (!isset($_REQUEST['id'])) {
            return false;
        }

        if (!isset($_REQUEST['pid'])) {
            return false;
        }

        $entry_id = urldecode($_REQUEST['id']);
        $account_id = $_REQUEST['account_id'] ?? null;

        $product_id = $_REQUEST['pid'];
        $documents_collection = new \WC_Product_Documents_Collection($product_id);

        foreach ($documents_collection->get_sections() as $section) {
            foreach ($section->get_documents() as $position => $document) {
                $file_location = urldecode($document->get_file_location());

                if (false === strpos($file_location, 'outofthebox-wcpd-direct-download')) {
                    continue;
                }

                // Fallback for old urls without account info
                if (empty($account_id)) {
                    $primary_account = Accounts::instance()->get_primary_account();
                    if (null === $primary_account) {
                        return false;
                    }
                    $account_id = $primary_account->get_id();
                }

                $account = Accounts::instance()->get_account_by_id($account_id);

                if (null === $account) {
                    return false;
                }

                App::set_current_account($account);

                if (false !== strpos($file_location, 'id='.$entry_id)) {
                    $entry_path = urldecode(base64_decode($entry_id));
                    $cached_entry = Client::instance()->get_entry($entry_path, false);
                    $downloadlink = $this->get_redirect_url_for_entry($cached_entry);

                    // Redirect to the file
                    header('Location: '.$downloadlink);

                    exit;
                }
            }
        }

        self::download_error(esc_html__('File not found', 'woocommerce'));
    }

    public function wc_product_documents_open_link_in_new_window($target, $product, $section, $document)
    {
        $file_location = $document->get_file_location();

        if (false === strpos($file_location, 'outofthebox-wcpd-direct-download')) {
            return false; // Do nothing
        }

        return '_blank" class="lightbox-group" title="'.$document->get_label();
    }

    public function wc_product_documents_update_document_urls($sections, $collection, $include_empty)
    {
        $product_id = $collection->get_product_id();
        if (empty($product_id)) {
            return $sections;
        }

        foreach ($sections as $section) {
            foreach ($section->get_documents() as $position => $document) {
                $file_location = $document->get_file_location();

                if (false === strpos($file_location, 'outofthebox-wcpd-direct-download')) {
                    continue;
                }

                if (false !== strpos($file_location, 'pid')) {
                    continue;
                }

                $section->add_document(new \WC_Product_Documents_Document($document->get_label(), $file_location.'&pid='.$collection->get_product_id()), $position);
            }
        }

        return $sections;
    }

    public function do_direct_download($file_path, $filename)
    {
        if (false === strpos($file_path, 'outofthebox-wc-direct-download')) {
            return false; // Do nothing
        }

        $entry = $this->get_entry_for_download_by_url($file_path);

        if (empty($entry)) {
            self::download_error(esc_html__('File not found', 'woocommerce'));
        }

        $downloadlink = $this->get_redirect_url_for_entry($entry);
        $filename = $entry->get_name();

        if ($entry->is_dir()) {
            $filename .= '.zip';
        }

        // Download the file
        self::download_headers($downloadlink, $filename);

        if (!\WC_Download_Handler::readfile_chunked($downloadlink)) {
            $this->do_redirect_download($file_path, $filename);
        }

        exit;
    }

    public function do_xsendfile_download($file_path, $filename)
    {
        if (false === strpos($file_path, 'outofthebox-wc-direct-download')) {
            return false; // Do nothing
        }

        // Fallback
        $this->do_direct_download($file_path, $filename);
    }

    public function do_redirect_download($file_path, $filename)
    {
        if (false === strpos($file_path, 'outofthebox-wc-direct-download')) {
            return false; // Do nothing
        }

        $entry = $this->get_entry_for_download_by_url($file_path);

        if (empty($entry)) {
            self::download_error(esc_html__('File not found', 'woocommerce'));
        }

        $downloadlink = $this->get_redirect_url_for_entry($entry);

        // Redirect to the file
        header('Location: '.$downloadlink);

        exit;
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

    /**
     * Get content type of a download.
     *
     * @param string $file_path
     *
     * @return string
     */
    private static function get_download_content_type($file_path)
    {
        $file_extension = strtolower(substr(strrchr($file_path, '.'), 1));
        $ctype = 'application/force-download';

        foreach (get_allowed_mime_types() as $mime => $type) {
            $mimes = explode('|', $mime);
            if (in_array($file_extension, $mimes)) {
                $ctype = $type;

                break;
            }
        }

        return $ctype;
    }

    /**
     * Set headers for the download.
     *
     * @param string $file_path
     * @param string $filename
     */
    private static function download_headers($file_path, $filename)
    {
        self::check_server_config();
        self::clean_buffers();
        nocache_headers();

        header('X-Robots-Tag: noindex, nofollow', true);
        header('Content-Type: '.self::get_download_content_type($file_path));
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; '.sprintf('filename="%s"; ', rawurlencode($filename)).sprintf("filename*=utf-8''%s", rawurlencode($filename)));
        header('Content-Transfer-Encoding: binary');

        if ($size = @filesize($file_path)) {
            header('Content-Length: '.$size);
        }
    }

    /**
     * Check and set certain server config variables to ensure downloads work as intended.
     */
    private static function check_server_config()
    {
        wc_set_time_limit(0);
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('zlib.output_compression', 'Off');
        @session_write_close();
    }

    /**
     * Clean all output buffers.
     *
     * Can prevent errors, for example: transfer closed with 3 bytes remaining to read.
     */
    private static function clean_buffers()
    {
        if (ob_get_level()) {
            $levels = ob_get_level();
            for ($i = 0; $i < $levels; ++$i) {
                @ob_end_clean();
            }
        } else {
            @ob_end_clean();
        }
    }

    /**
     * Die with an error message if the download fails.
     *
     * @param string $message
     * @param string $title
     * @param int    $status
     */
    private static function download_error($message, $title = '', $status = 404)
    {
        if (!strstr($message, '<a ')) {
            $message .= ' <a href="'.esc_url(get_site_url()).'">Go to back</a>';
        }
        wp_die($message, $title, ['response' => $status]);
    }
}

new WooCommerce_Downloads();
