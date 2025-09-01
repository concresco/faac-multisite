<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

use ZipStream\Option\Archive;
use ZipStream\Option\File;
use ZipStream\ZipStream;

defined('ABSPATH') || exit;

class Zip
{
    /**
     * Unique ID.
     *
     * @var string
     */
    public $request_id;

    /**
     * Name of the zip file.
     *
     * @var string
     */
    public $zip_name;

    /**
     * Files that need to be added to ZIP.
     *
     * @var \TheLion\OutoftheBox\Entry[]
     */
    public $entries = [];

    /**
     * Number of bytes that are downloaded so far.
     *
     * @var int
     */
    public $bytes_so_far = 0;

    /**
     * Bytes that need to be download in total.
     *
     * @var int
     */
    public $bytes_total = 0;

    /**
     * Current status.
     *
     * @var string
     */
    public $current_action = 'starting';

    /**
     * Message describing the current status.
     *
     * @var string
     */
    public $current_action_str = '';

    /**
     * @var \TheLion\OutoftheBox\Entry[]
     */
    public $entries_downloaded = [];

    /**
     * @var ZipStream
     */
    private $_zip_handler;

    public function __construct($request_id)
    {
        $this->request_id = $request_id;
    }

    public function do_zip()
    {
        if (!isset($_REQUEST['files']) || (isset($_REQUEST['files']) && count($_REQUEST['files']) <= 1)) {
            if (false === $this->is_shortcode_filtered()) {
                $this->download_zip_via_url();
            }
        }

        $this->download_zip_via_server();

        exit;
    }

    /**
     * Use Dropbox ZIP function for complete folder if possible.
     */
    public function download_zip_via_url()
    {
        $requested_ids = [Processor::instance()->get_requested_complete_path()];

        if (isset($_REQUEST['files'])) {
            $requested_ids = $_REQUEST['files'];
        }

        $entry = Client::instance()->get_entry(reset($requested_ids));

        if (false === $entry) {
            return false;
        }

        if ($entry->is_file()) {
            return false;
        }

        try {
            $download_url = Client::instance()->get_shared_link($entry).'&dl=1';
        } catch (\Exception $ex) {
            return false;
        }

        $download_url = str_replace('/sh/', '/sh/dl/', $download_url);

        // Try to load the direct download link. Safari browsers can't otherwise start the ZIP download
        $client = new Vendors\GuzzleHttp\Client();
        $response = $client->head($download_url, ['allow_redirects' => false]);
        $location = $response->getHeaderLine('location');
        if (!empty($location)) {
            $download_url = $location;
        }

        header('Location: '.$download_url);

        $this->current_action = 'finished';
        $this->current_action_str = esc_html__('Finished', 'wpcloudplugins');
        $this->set_progress();

        do_action('outofthebox_log_event', 'outofthebox_downloaded_entry', $entry, ['as_zip' => true]);

        exit;
    }

    public function download_zip_via_server()
    {
        $this->initialize();
        $this->current_action = 'indexing';
        $this->current_action_str = esc_html__('Selecting files...', 'wpcloudplugins');

        $this->index();
        $this->create();

        do_action('outofthebox_log_event', 'outofthebox_downloaded_zip', Processor::instance()->get_requested_complete_path(), ['name' => $this->zip_name, 'files' => count($this->entries), 'size' => $this->bytes_total]);

        $this->current_action = 'downloading';
        $this->add_entries();

        $this->current_action = 'finalizing';
        $this->current_action_str = esc_html__('Almost ready', 'wpcloudplugins');
        $this->set_progress();
        $this->finalize();

        $this->current_action = 'finished';
        $this->current_action_str = esc_html__('Finished', 'wpcloudplugins');
        $this->set_progress();
    }

    /**
     * Load the ZIP library and make sure that the root folder is loaded.
     */
    public function initialize()
    {
        ignore_user_abort(false);

        require_once OUTOFTHEBOX_ROOTDIR.'/vendors/ZipStream/vendor/autoload.php';

        // Check if file/folder is cached and still valid
        $folder = $cachedfolder = Client::instance()->get_folder();

        if (false === $cachedfolder) {
            return new \WP_Error('broke', esc_html__("Requested directory isn't allowed", 'wpcloudplugins'));
        }

        // Check if entry is allowed
        if (!Processor::instance()->_is_entry_authorized($folder)) {
            return new \WP_Error('broke', esc_html__("Requested directory isn't allowed", 'wpcloudplugins'));
        }

        // Set Zip file name
        $last_folder_path = Processor::instance()->get_last_path();
        $zip_filename = basename($last_folder_path).'_'.time().'.zip';
        $this->zip_name = apply_filters('outofthebox_zip_filename', $zip_filename, $last_folder_path);

        $single_entry = null;
        if (isset($_REQUEST['files']) && 1 === count($_REQUEST['files'])) {
            $single_entry = reset($_REQUEST['files']);
        }

        if ($download_limit_hit_message = Restrictions::has_reached_download_limit($single_entry ?? $folder->get_id(), false, 'download_zip')) {
            $this->current_action = 'finished';
            $this->current_action_str = $download_limit_hit_message;
            $this->set_progress();

            http_response_code(429);

            exit;
        }

        $this->set_progress();

        // Stop WP from buffering, and discard the current buffer.
        $levels = ob_get_level();
        for ($i = 0; $i < $levels; ++$i) {
            ob_end_clean();
        }
    }

    /**
     * Create the ZIP File.
     */
    public function create()
    {
        $options = new Archive();
        $options->setSendHttpHeaders(true);
        $options->setFlushOutput(true);
        $options->setContentType('application/octet-stream');
        header('X-Accel-Buffering: no');

        // create a new zipstream object
        $this->_zip_handler = new ZipStream(Helpers::filter_filename($this->zip_name), $options);

        $this->_clear_temp_folder();
    }

    /**
     * Create a list of files and folders that need to be zipped.
     */
    public function index()
    {
        $requested_ids = [Processor::instance()->get_requested_complete_path()];

        if (isset($_REQUEST['files'])) {
            $requested_ids = $_REQUEST['files'];
        }

        foreach ($requested_ids as $fileid) {
            $entry = Client::instance()->get_entry($fileid);

            if (false === $entry) {
                continue;
            }

            if ($entry->is_dir()) {
                $folder = Client::instance()->get_folder($entry->get_path(), true, true, false);

                if (false === $folder->has_children()) {
                    continue;
                }

                $this->entries = array_merge($this->entries, $folder->get_children());

                foreach ($folder->get_children() as $child) {
                    $this->bytes_total += $child->get_size();
                }
            } else {
                $relative_path = Processor::instance()->get_relative_path($entry->get_path());
                $entry->set_path($relative_path);
                $relative_path_display = Processor::instance()->get_relative_path($entry->get_path_display());
                $entry->set_path_display($relative_path_display);
                $this->entries[] = $entry;
                $this->bytes_total += $entry->get_size();
            }

            $this->current_action_str = esc_html__('Selecting files...', 'wpcloudplugins').' ('.count($this->entries).')';
            $this->set_progress();
        }
    }

    /**
     * Add all requests files to Zip file.
     */
    public function add_entries()
    {
        if (count($this->entries) > 0) {
            foreach ($this->entries as $key => $entry) {
                // Skip file if the download limit is reached
                if ($download_limit_hit_message = Restrictions::has_reached_download_limit($entry->get_id(), false)) {
                    $fileOptions = new File();
                    $fileOptions->setComment($download_limit_hit_message);

                    $this->_zip_handler->addFile(trim($entry->get_path_display(), '/').'.download-limit-exceeded', '', $fileOptions);

                    unset($this->entries[$key]);

                    continue;
                }

                $this->add_file_to_zip($entry);

                unset($this->entries[$key]);

                $this->entries_downloaded[] = $entry;

                do_action('outofthebox_log_event', 'outofthebox_downloaded_entry', $entry, ['as_zip' => true]);

                $this->bytes_so_far += $entry->get_size();
                $this->current_action_str = esc_html__('Downloading...', 'wpcloudplugins').'<br/>('.Helpers::bytes_to_size_1024($this->bytes_so_far).' / '.Helpers::bytes_to_size_1024($this->bytes_total).')';
                $this->set_progress();
            }
        }
    }

    /**
     * Download the request file and add it to the ZIP.
     */
    public function add_file_to_zip(Entry $entry)
    {
        $path = $entry->get_path_display();

        if ($entry->is_dir()) {
            return;
        }

        // Download the File
        // Update the time_limit as this can take a while
        Helpers::set_time_limit(60);

        $tmpfname = tempnam(sys_get_temp_dir(), 'WPC-');
        $download_stream = fopen($tmpfname, 'w+b');
        $stream_meta_data = stream_get_meta_data($download_stream);

        // If the script terminates unexpectedly, the temporary file may not be deleted.
        // This handler tries to resolve that.
        register_shutdown_function(function () use ($download_stream, $stream_meta_data) {
            if (is_resource($download_stream)) {
                fclose($download_stream);
            }
            if (!empty($stream_meta_data['uri']) && @file_exists($stream_meta_data['uri'])) {
                @unlink($stream_meta_data['uri']);
            }
        });

        $fileOptions = new File();
        if (!empty($entry->get_last_edited())) {
            $date = new \DateTime();
            $date->setTimestamp(strtotime($entry->get_last_edited()));
            $fileOptions->setTime($date);
        }

        $fileOptions->setComment((string) $entry->get_description());

        try {
            // @var $download_file \TheLion\OutoftheBox\API\Dropbox\Models\File
            App::instance()->get_sdk_client()->stream($download_stream, $entry->get_id());
            // Add file contents to zip

            $this->_zip_handler->addFileFromStream(trim($path, '/'), $download_stream, $fileOptions);
        } catch (\Exception $ex) {
            Helpers::log_error('', 'API', null, __LINE__, $ex);
            fclose($download_stream);
            @unlink($stream_meta_data['uri']);

            $this->current_action = 'failed';
            $this->set_progress();

            exit;
        }

        fclose($download_stream);
        @unlink($stream_meta_data['uri']);
    }

    /**
     * Finalize the zip file.
     */
    public function finalize()
    {
        $this->set_progress();

        // Close zip
        $this->_zip_handler->finish();

        // Send email if needed
        if ('1' === Processor::instance()->get_shortcode_option('notificationdownload')) {
            Processor::instance()->send_notification_email('download', $this->entries_downloaded);
        }

        // Download Zip Hook
        do_action('outofthebox_download_zip', $this->entries_downloaded);
    }

    /**
     * Received progress information for the ZIP process from database.
     *
     * @param string $request_id
     */
    public static function get_progress($request_id)
    {
        return get_transient('outofthebox_zip_'.substr($request_id, 0, 40));
    }

    /**
     * Set current progress information for ZIP process in database.
     */
    public function set_progress()
    {
        $status = [
            'id' => $this->request_id,
            'status' => [
                'bytes_so_far' => $this->bytes_so_far,
                'bytes_total' => $this->bytes_total,
                'percentage' => ($this->bytes_total > 0) ? (round(($this->bytes_so_far / $this->bytes_total) * 100)) : 0,
                'progress' => $this->current_action,
                'progress_str' => $this->current_action_str,
            ],
        ];

        // Update progress
        return set_transient('outofthebox_zip_'.substr($this->request_id, 0, 40), $status, HOUR_IN_SECONDS);
    }

    /**
     * Get progress information for the ZIP process
     * Used to display a progress percentage on Front-End.
     *
     * @param string $request_id
     */
    public static function get_status($request_id)
    {
        // Try to get the upload status of the file
        for ($_try = 1; $_try < 6; ++$_try) {
            $result = self::get_progress($request_id);

            if (false !== $result) {
                if ('failed' === $result['status']['progress'] || 'finished' === $result['status']['progress']) {
                    delete_transient('outofthebox_zip_'.substr($request_id, 0, 40));
                }

                break;
            }

            // Wait a moment, perhaps the upload still needs to start
            usleep(500000 * $_try);
        }

        if (false === $result) {
            $result = ['file' => false, 'status' => ['bytes_down_so_far' => 0, 'total_bytes_down_expected' => 0, 'percentage' => 0, 'progress' => 'failed']];
        }

        echo json_encode($result);

        exit;
    }

    /**
     * Check if the current shortcode is excluding data from view
     * If that isn't the case, the complete folder can be downloaded instead of indiviual files.
     */
    public function is_shortcode_filtered()
    {
        if ('1' !== Processor::instance()->get_shortcode_option('show_files')) {
            return true;
        }

        $ext = Processor::instance()->get_shortcode_option('include_ext');
        $exclude = Processor::instance()->get_shortcode_option('exclude');
        $include = Processor::instance()->get_shortcode_option('include');

        if ('1' !== Processor::instance()->get_shortcode_option('show_folders')) {
            $requested_ids = [Processor::instance()->get_requested_complete_path()];

            if (isset($_REQUEST['files'])) {
                $requested_ids = $_REQUEST['files'];
            }

            $top_folder = Client::instance()->get_folder(reset($requested_ids), false);
            foreach ($top_folder->get_children() as $child) {
                // Render folder div
                if ($child->is_dir()) {
                    return true;
                }
            }
        }

        // Gallery modules are always filtered, but that doesn't mean that there are other files in the folder
        if ('gallery' === Processor::instance()->get_shortcode_option('mode')) {
            $requested_ids = [Processor::instance()->get_requested_complete_path()];

            if (isset($_REQUEST['files'])) {
                $requested_ids = $_REQUEST['files'];
            }

            $top_folder = Client::instance()->get_folder(reset($requested_ids), false);
            foreach ($top_folder->get_children() as $child) {
                if (!Processor::instance()->_is_entry_authorized($child)) {
                    return true;
                }
            }

            return false;
        }

        return
          ('*' !== $ext[0])
           || ('*' !== $exclude[0])
            || ('*' !== $include[0]);
    }

    /**
     * Clear temporary files older than specific number of hours.
     *
     * @param int $max_age_hours
     */
    private function _clear_temp_folder($max_age_hours = 2)
    {
        // Define the temp directory and file pattern
        $temp_dir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $file_pattern = $temp_dir.'WPC-*';

        // Define the age limit (12 hours in seconds)
        $max_age = $max_age_hours * 3600;
        $current_time = time();

        // Get all files matching the pattern
        $files = glob($file_pattern);

        if (false !== $files) {
            foreach ($files as $file) {
                // Check if it's a file and not a directory
                if (is_file($file)) {
                    // Get the file's modification time
                    $file_mod_time = filemtime($file);
                    if (false !== $file_mod_time && ($current_time - $file_mod_time) > $max_age) {
                        // Attempt to delete the file
                        @unlink($file);
                    }
                }
            }
        }
    }
}
