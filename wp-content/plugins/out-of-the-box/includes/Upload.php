<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

defined('ABSPATH') || exit;

class Upload
{
    /**
     * @var WPCP_UploadHandler
     */
    private $upload_handler;

    public function __construct()
    {
        wp_using_ext_object_cache(false);
    }

    public function upload_pre_process()
    {
        do_action('outofthebox_upload_pre_process', Processor::instance());

        $result = ['result' => 1];

        $result = apply_filters('outofthebox_upload_pre_process_result', $result, Processor::instance());

        echo json_encode($result);
    }

    public function do_upload()
    {
        // Upload File to server
        if (!class_exists('WPCP_UploadHandler')) {
            require_once OUTOFTHEBOX_ROOTDIR.'/vendors/jquery-file-upload/server/UploadHandler.php';
        }
        if ('1' === Processor::instance()->get_shortcode_option('demo')) {
            exit(-1);
        }

        $shortcode_max_file_size = Processor::instance()->get_shortcode_option('maxfilesize');
        $shortcode_min_file_size = Processor::instance()->get_shortcode_option('minfilesize');
        $accept_file_types = '/.('.Processor::instance()->get_shortcode_option('upload_ext').')$/i';
        $post_max_size_bytes = min(Helpers::return_bytes(ini_get('post_max_size')), Helpers::return_bytes(ini_get('upload_max_filesize')));
        $max_file_size = ('0' !== $shortcode_max_file_size) ? Helpers::return_bytes($shortcode_max_file_size) : $post_max_size_bytes;
        $min_file_size = (!empty($shortcode_min_file_size)) ? Helpers::return_bytes($shortcode_min_file_size) : -1;

        $options = [
            'access_control_allow_methods' => ['POST', 'PUT'],
            'accept_file_types' => $accept_file_types,
            'inline_file_types' => '/\.____$/i',
            'orient_image' => false,
            'image_versions' => [],
            'max_file_size' => $max_file_size,
            'min_file_size' => $min_file_size,
            'print_response' => false,
        ];

        $error_messages = [
            1 => esc_html__('The uploaded file exceeds the upload_max_filesize directive in php.ini', 'wpcloudplugins'),
            2 => esc_html__('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', 'wpcloudplugins'),
            3 => esc_html__('The uploaded file was only partially uploaded', 'wpcloudplugins'),
            4 => esc_html__('No file was uploaded', 'wpcloudplugins'),
            6 => esc_html__('Missing a temporary folder', 'wpcloudplugins'),
            7 => esc_html__('Failed to write file to disk', 'wpcloudplugins'),
            8 => esc_html__('A PHP extension stopped the file upload', 'wpcloudplugins'),
            'post_max_size' => esc_html__('The uploaded file exceeds the post_max_size directive in php.ini', 'wpcloudplugins'),
            'max_file_size' => esc_html__('File is too big', 'wpcloudplugins'),
            'min_file_size' => esc_html__('File is too small', 'wpcloudplugins'),
            'accept_file_types' => esc_html__('Filetype not allowed', 'wpcloudplugins'),
            'max_number_of_files' => esc_html__('Maximum number of files exceeded', 'wpcloudplugins'),
            'max_width' => esc_html__('Image exceeds maximum width', 'wpcloudplugins'),
            'min_width' => esc_html__('Image requires a minimum width', 'wpcloudplugins'),
            'max_height' => esc_html__('Image exceeds maximum height', 'wpcloudplugins'),
            'min_height' => esc_html__('Image requires a minimum height', 'wpcloudplugins'),
        ];

        $hash = $_REQUEST['hash'];
        $path = $_REQUEST['file_path'];

        delete_transient('outofthebox_upload_'.substr($hash, 0, 40));

        $this->upload_handler = new \WPCP_UploadHandler($options, false, $error_messages);
        $response = $this->upload_handler->post(false);

        // Upload files to Dropbox
        foreach ($response['files'] as &$file) {
            $name = Helpers::filter_filename(stripslashes(rawurldecode($file->name)), false);

            // Rename, Prefix and Suffix file
            $file_extension = pathinfo($name, PATHINFO_EXTENSION);
            $file_name = pathinfo($name, PATHINFO_FILENAME);

            $name = trim(Placeholders::apply(
                Processor::instance()->get_shortcode_option('upload_filename'),
                Processor::instance(),
                [
                    'file_name' => $file_name,
                    'file_extension' => empty($file_extension) ? '' : ".{$file_extension}",
                    'queue_index' => filter_var($_REQUEST['queue_index'] ?? 1, FILTER_SANITIZE_NUMBER_INT),
                ]
            ));

            $name_parts = pathinfo($name);

            if (false !== strpos($name, '/') && !empty($name_parts['dirname'])) {
                $path = Helpers::clean_folder_path($path.$name_parts['dirname']);
            }

            $name = basename($name);

            // Set return Object
            $file->listtoken = Processor::instance()->get_listtoken();
            $file->name = $name;
            $file->hash = $hash;
            $file->path = $path;

            if (!isset($file->error)) {
                $return = ['file' => $file, 'status' => ['bytes_up_so_far' => 0, 'total_bytes_up_expected' => $file->size, 'percentage' => 0, 'progress' => 'starting']];
                self::set_upload_progress($hash, $return);

                /** Check if the user hasn't reached its usage limit */
                $max_user_folder_size = Processor::instance()->get_shortcode_option('max_user_folder_size');
                if ('0' !== Processor::instance()->get_shortcode_option('userfolders') && '-1' !== $max_user_folder_size) {
                    $disk_usage_after_upload = Client::instance()->get_folder_size() + $file->size;
                    $max_allowed_bytes = Helpers::return_bytes($max_user_folder_size);
                    if ($disk_usage_after_upload > $max_allowed_bytes) {
                        $return['status']['progress'] = 'upload-failed';
                        $file->error = esc_html__('You have reached your usage limit of', 'wpcloudplugins').' '.Helpers::bytes_to_size_1024($max_allowed_bytes);
                        self::set_upload_progress($hash, $return);
                        echo json_encode($return);

                        exit;
                    }
                }

                // Check if file already exists
                if (!empty($file->path)) {
                    $file->name = $file->path.$file->name;
                }

                $filename = apply_filters('outofthebox_upload_file_name', $file->name, Processor::instance());
                $new_file_path = Helpers::clean_folder_path(Processor::instance()->get_requested_complete_path().'/'.$filename);
                $new_file_path = apply_filters('outofthebox_upload_file_path', $new_file_path, Processor::instance());

                // Add or update file?
                $params = ['mode' => 'add', 'autorename' => true];

                if ('1' === Processor::instance()->get_shortcode_option('overwrite')) {
                    $params = ['mode' => 'overwrite', 'autorename' => false];
                }

                // Modify the uploaded file if needed
                $file = apply_filters('outofthebox_upload_file_set_object', $file, Processor::instance());

                try {
                    $entry = API::upload_file($file, $new_file_path, $params);
                    $file->completepath = Processor::instance()->get_relative_path($entry->get_path_display());
                    $file->account_id = App::get_current_account()->get_id();
                    $file->fileid = base64_encode($new_file_path);
                    $file->filesize = Helpers::bytes_to_size_1024($entry->get_size());
                    $file->link = false; // Currently no Direct link available
                } catch (\Exception $ex) {
                    Helpers::log_error('Cannot not upload the file to the cloud.', 'Upload', null, __LINE__, $ex);
                    $file->error = esc_html__('Not uploaded to the cloud', 'wpcloudplugins').$ex->getMessage();
                }

                $return['status']['progress'] = 'upload-finished';
                $return['status']['percentage'] = '100';

                CacheRequest::clear_local_cache_for_shortcode(App::get_current_account()->get_id(), Processor::instance()->get_listtoken());
            } else {
                Helpers::log_error('Failed to upload file.', 'Upload', ['error' => $file->error], __LINE__);

                $return['status']['progress'] = 'upload-failed';
                $file->error = esc_html__('Uploading failed', 'wpcloudplugins');
            }
        }

        $return['file'] = $file;
        self::set_upload_progress($hash, $return);

        // Create response
        echo json_encode($return);

        exit;
    }

    public function do_upload_direct()
    {
        if ((!isset($_REQUEST['filename'])) || (!isset($_REQUEST['file_size'])) || (!isset($_REQUEST['mimetype']))) {
            exit;
        }

        if ('1' === Processor::instance()->get_shortcode_option('demo')) {
            echo json_encode(['result' => 0]);

            exit;
        }

        $size = $_REQUEST['file_size'];
        $path = $_REQUEST['file_path'];

        // Rename, Prefix and Suffix file
        $file_extension = pathinfo(stripslashes($_REQUEST['filename']), PATHINFO_EXTENSION);
        $file_name = pathinfo(stripslashes($_REQUEST['filename']), PATHINFO_FILENAME);

        $name = trim(Placeholders::apply(
            Processor::instance()->get_shortcode_option('upload_filename'),
            Processor::instance(),
            [
                'file_name' => $file_name,
                'file_extension' => empty($file_extension) ? '' : ".{$file_extension}",
                'file_description' => !empty($_REQUEST['file_description']) ? sanitize_textarea_field(wp_unslash($_REQUEST['file_description'])) : '',
                'queue_index' => filter_var($_REQUEST['queue_index'] ?? 1, FILTER_SANITIZE_NUMBER_INT),
            ]
        ));

        $name_parts = pathinfo($name);

        if (false !== strpos($name, '/') && !empty($name_parts['dirname'])) {
            $path = Helpers::clean_folder_path($path.$name_parts['dirname']);
        }

        $name = basename($name);

        $description = sanitize_textarea_field(wp_unslash($_REQUEST['file_description']));

        if (!empty($path)) {
            $name = $path.$name;
        }

        /** Check if the user hasn't reached its usage limit */
        $max_user_folder_size = Processor::instance()->get_shortcode_option('max_user_folder_size');
        if ('0' !== Processor::instance()->get_shortcode_option('userfolders') && '-1' !== $max_user_folder_size) {
            $disk_usage_after_upload = Client::instance()->get_folder_size() + $size;
            $max_allowed_bytes = Helpers::return_bytes($max_user_folder_size);
            if ($disk_usage_after_upload > $max_allowed_bytes) {
                Helpers::log_error('Reached usage limit.', 'Upload', ['limit' => Helpers::bytes_to_size_1024($max_allowed_bytes)], __LINE__);

                echo json_encode(['result' => 0]);

                exit;
            }
        }

        // Check if file already exists
        $filename = apply_filters('outofthebox_upload_file_name', $name, Processor::instance());
        $new_file_path = Helpers::clean_folder_path(Processor::instance()->get_requested_complete_path().'/'.$filename);
        $new_file_path = apply_filters('outofthebox_upload_file_path', $new_file_path, Processor::instance());

        // Add or update file?
        $params = ['mode' => 'add', 'autorename' => true];

        if ('1' === Processor::instance()->get_shortcode_option('overwrite')) {
            //            $entry_if_exists = Client::instance()->get_entry($new_file_path);
            //
            //            $file_rev = false;
            //            if (!empty($entry_if_exists)) {
            //                $file_rev = $entry_if_exists->get_rev();
            //            }

            $params = ['mode' => 'overwrite', 'autorename' => false];
        }

        $origin = $_REQUEST['orgin'];

        try {
            $temporarily_link = App::instance()->get_sdk_client()->getTemporarilyUploadLink($new_file_path, $params, $origin);
            echo json_encode(['result' => 1, 'url' => $temporarily_link->getLink(), 'convert' => false, 'id' => base64_encode($new_file_path)]);
        } catch (\Exception $ex) {
            Helpers::log_error('File not uploaded to the cloud.', 'API', ['file_name' => $name], __LINE__, $ex);
            echo json_encode(['result' => 0]);
        }

        exit;
    }

    public static function get_upload_progress($file_hash)
    {
        wp_using_ext_object_cache(false);

        return get_transient('outofthebox_upload_'.substr($file_hash, 0, 40));
    }

    public static function set_upload_progress($file_hash, $status)
    {
        wp_using_ext_object_cache(false);

        // Update progress
        return set_transient('outofthebox_upload_'.substr($file_hash, 0, 40), $status, HOUR_IN_SECONDS);
    }

    public function get_upload_status()
    {
        $hash = $_REQUEST['hash'];

        // Try to get the upload status of the file
        for ($_try = 1; $_try < 10; ++$_try) {
            $result = self::get_upload_progress($hash);

            if (false !== $result) {
                if ('upload-failed' === $result['status']['progress'] || 'upload-finished' === $result['status']['progress']) {
                    delete_transient('outofthebox_upload_'.substr($hash, 0, 40));
                }

                break;
            }

            // Wait a moment, perhaps the upload still needs to start
            usleep(1000000 * $_try);
        }

        if (false === $result) {
            $result = ['file' => false, 'status' => ['bytes_up_so_far' => 0, 'total_bytes_up_expected' => 0, 'percentage' => 0, 'progress' => 'no-progress-found']];
        }

        echo json_encode($result);

        exit;
    }

    public function upload_convert()
    {
        // NOT IMPLEMENTED
        echo json_encode(['result' => 1, 'fileid' => $_REQUEST['fileid']]);

        exit;
    }

    public function upload_post_process()
    {
        if ((!isset($_REQUEST['files'])) || 0 === count($_REQUEST['files'])) {
            echo json_encode(['result' => 0]);

            exit;
        }

        $uploaded_files = $_REQUEST['files'];
        $_uploaded_entries = [];
        $_email_entries = [];

        foreach ($uploaded_files as $hash) {
            $base64_id = base64_decode($hash, true);
            $file_id = (false === $base64_id) ? $hash : $base64_id;

            try {
                $api_entry = App::instance()->get_sdk_client()->getMetadata($file_id);
                $entry = new Entry($api_entry);
            } catch (\Exception $ex) {
                continue;
            }

            if (false === $entry) {
                continue;
            }

            // Upload Hook
            if (false === get_transient('outofthebox_upload_'.$file_id)) {
                do_action('outofthebox_upload', $entry);
                do_action('outofthebox_log_event', 'outofthebox_uploaded_entry', $entry);

                $_email_entries[] = $entry;
            }

            $_uploaded_entries[$hash] = $entry;
        }

        do_action('outofthebox_upload_post_process', $_uploaded_entries, Processor::instance());

        // Send email if needed

        if (!empty($_email_entries) && ('1' === Processor::instance()->get_shortcode_option('notificationupload'))) {
            Processor::instance()->send_notification_email('upload', $_email_entries);
        }

        // Create Shared Folder url if needed
        $shared_folder_url = null;
        if ('1' === Processor::instance()->get_shortcode_option('upload_create_shared_link_folder')) {
            $shared_folder_url = Client::instance()->get_shared_link(Client::instance()->get_entry(), []).'&dl=0';
        }

        // Return information of the files
        $files = [];

        foreach ($_uploaded_entries as $oldhash => $entry) {
            $file = [];
            $file['old_hash'] = $oldhash;
            $file['name'] = $entry->get_name();
            $file['type'] = $entry->get_mimetype();
            $file['absolute_path'] = $entry->get_path();
            $file['relative_path'] = Processor::instance()->get_relative_path($entry->get_path());
            $file['description'] = $entry->get_description();
            $file['account_id'] = App::get_current_account()->get_id();
            $file['fileid'] = $entry->get_id();
            $file['filesize'] = Helpers::bytes_to_size_1024($entry->get_size());
            $file['folder_preview_url'] = urlencode('https://www.dropbox.com/home'.rtrim($entry->get_parent(), '/'));
            $file['folder_shared_url'] = $shared_folder_url;
            $file['folder_absolute_path'] = rtrim($entry->get_parent(), '/');
            $file['folder_relative_path'] = Processor::instance()->get_relative_path($entry->get_parent());
            $file['temp_thumburl'] = (count($_uploaded_entries) < 10 && $entry->has_own_thumbnail()) ? Client::instance()->get_thumbnail($entry, true, 128, 128) : null;
            $file['preview_url'] = urlencode('https://www.dropbox.com/home'.$entry->get_path());
            $file['shared_url'] = false;

            if (apply_filters('outofthebox_upload_post_process_createlink', '1' === Processor::instance()->get_shortcode_option('upload_create_shared_link'), $entry, Processor::instance())) {
                $file['shared_url'] = urlencode(Client::instance()->get_shared_link($entry, []).'&dl=0');
            }

            $files[$file['fileid']] = apply_filters('outofthebox_upload_entry_information', $file, $entry, Processor::instance());

            set_transient('outofthebox_upload_'.$entry->get_id(), true, HOUR_IN_SECONDS);
        }

        $files = apply_filters('outofthebox_upload_post_process_data', $files, Processor::instance());

        // Clear Cached Requests
        CacheRequest::clear_request_cache();

        echo json_encode(['result' => 1, 'files' => $files]);
    }
}