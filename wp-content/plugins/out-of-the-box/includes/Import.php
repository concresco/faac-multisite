<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       3.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

defined('ABSPATH') || exit;

class Import
{
    /**
     * Entry object representing the file to be imported.
     *
     * @var Entry
     */
    public $entry;

    /**
     * @var string the name of the attachment
     */
    public $attachment_name;

    /**
     * URL to download the file.
     *
     * @var string
     */
    public $download_url;

    /**
     * Path where the file will be imported.
     *
     * @var string
     */
    public $import_path;

    /**
     * The single instance of the class.
     *
     * @var Import
     */
    protected static $_instance;

    /**
     * Upload directory information.
     *
     * @var array
     */
    protected static $_upload_dir;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once ABSPATH.'wp-admin/includes/image.php';

            include_once ABSPATH.'wp-admin/includes/media.php';
        }

        self::$_upload_dir = wp_upload_dir();
    }

    /**
     * Get the single instance of the class.
     *
     * @return Import - Import instance
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Add a file to the media library.
     *
     * @param mixed $entry
     *
     * @return bool|int attachment ID
     */
    public function add_to_media_library($entry)
    {
        $this->entry = $entry;

        // Set Attachment name
        $this->attachment_name = sanitize_file_name($entry->get_name());

        // Set Download Url
        $this->download_url = Client::instance()->get_temporarily_link($entry);

        if (empty($this->download_url)) {
            return;
        }

        // Download the file to the uploads folder
        $file_path = $this->download_file_to_uploads();

        // Add to Media Library
        if ($file_path) {
            return $this->insert_attachment_to_library();
        }

        return false;
    }

    /**
     * Download the file to the uploads folder.
     */
    public function download_file_to_uploads()
    {
        $upload_dir = wp_upload_dir();

        $this->import_path = $upload_dir['path'].'/'.$this->attachment_name;

        // If file exists, rename it
        if (file_exists($this->import_path)) {
            $this->import_path = $upload_dir['path'].'/'.time().'_'.$this->attachment_name;
        }

        // Open file for writing
        $file_handle = fopen($this->import_path, 'wb');
        if (!$file_handle) {
            Helpers::log_error('Cannot open file for writing', 'Import', ['file' => $this->import_path], __LINE__);

            return false;
        }

        Helpers::set_time_limit(0);

        // Use wp_remote_get with curl options to stream directly to the file
        try {
            $response = wp_remote_get($this->download_url, [
                'timeout' => 60,
                'stream' => true,
                'filename' => $this->import_path,
            ]);
        } catch (\Exception $e) {
            fclose($file_handle);
            Helpers::log_error('Cannot store file in upload folder', 'Import', ['file' => $this->import_path], __LINE__, $e);

            return false;
        }

        if (is_wp_error($response)) {
            fclose($file_handle);
            Helpers::log_error('Cannot store file in upload folder', 'Import', ['file' => $this->import_path], __LINE__, $response);

            return false;
        }

        fclose($file_handle);

        return $this->import_path;
    }

    /**
     * Insert the downloaded file as an attachment.
     *
     * @return int attachment ID
     */
    public function insert_attachment_to_library()
    {
        $file_path = $this->import_path;

        // Create an attachment for the file
        $file_type = wp_check_filetype(basename($file_path));

        $attachment = [
            'guid' => self::$_upload_dir['url'].'/'.basename($file_path),
            'post_mime_type' => $file_type['type'],
            'post_title' => sanitize_title($this->entry->get_basename()),
            'post_content' => \sanitize_text_field($this->entry->get_description()),
            'post_status' => 'inherit',
        ];

        $attach_id = wp_insert_attachment($attachment, $file_path);

        // Generate metadata and update the attachment.
        $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
        wp_update_attachment_metadata($attach_id, $attach_data);

        return $attach_id;
    }
}
