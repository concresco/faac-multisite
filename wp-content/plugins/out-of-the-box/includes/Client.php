<?php

/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

use TheLion\OutoftheBox\API\Dropbox\Dropbox;
use TheLion\OutoftheBox\API\Dropbox\Exceptions\DropboxClientException;
use TheLion\OutoftheBox\API\Dropbox\Models\FolderMetadata;

defined('ABSPATH') || exit;

require_once OUTOFTHEBOX_ROOTDIR.'/vendors/dropbox-sdk/vendor/autoload.php';

class Client
{
    /**
     * The single instance of the class.
     *
     * @var Client
     */
    protected static $_instance;

    /**
     * Client Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return Client - Client instance
     *
     * @static
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function get_account_info()
    {
        return App::instance()->get_sdk_client()->getCurrentAccount();
    }

    public function get_account_space_info()
    {
        return App::instance()->get_sdk_client()->getSpaceUsage();
    }

    public function get_entry($requested_path = null, $check_if_allowed = true)
    {
        if (null === $requested_path) {
            $requested_path = Processor::instance()->get_requested_complete_path();
        }

        // Clean path if needed
        if (false !== strpos($requested_path, '/')) {
            $requested_path = Helpers::clean_folder_path($requested_path);
        }

        // Get entry meta data (no meta data for root folder_
        if ('/' === $requested_path || '' === $requested_path) {
            $entry = new Entry();
            $entry->set_id('Dropbox');
            $entry->set_name('Dropbox');
            $entry->set_basename('Dropbox');
            $entry->set_path('/');
            $entry->set_path_display('/');
            $entry->set_is_dir(true);
        } else {
            try {
                $entry = API::get_entry($requested_path, ['include_media_info' => true]);
            } catch (\Exception $ex) {
                return false;
            }
        }

        if ($check_if_allowed && !Processor::instance()->_is_entry_authorized($entry)) {
            exit('-1');
        }

        return $entry;
    }

    public function get_multiple_entries($entries = [])
    {
        $dropbox_entries = [];
        foreach ($entries as $entry) {
            $dropbox_entry = $this->get_entry($entry, false);
            if (!empty($dropbox_entry)) {
                $dropbox_entries[] = $dropbox_entry;
            }
        }

        return $dropbox_entries;
    }

    /**
     * @param string $requested_path
     * @param bool   $check_if_allowed
     * @param mixed  $recursive
     * @param mixed  $hierarchical
     *
     * @return bool|Entry
     */
    public function get_folder($requested_path = null, $check_if_allowed = true, $recursive = false, $hierarchical = true)
    {
        if (null === $requested_path) {
            $requested_path = Processor::instance()->get_requested_complete_path();
        }

        // Clean path if needed
        if (false !== strpos($requested_path, '/')) {
            $requested_path = Helpers::clean_folder_path($requested_path);
        }

        try {
            $folder = API::get_folder($requested_path, ['recursive' => $recursive, 'hierarchical' => $hierarchical]);
        } catch (\Exception $ex) {
            return false;
        }

        foreach ($folder->get_children() as $key => $child) {
            if ($check_if_allowed && false === Processor::instance()->_is_entry_authorized($child)) {
                unset($folder->children[$key]);
            }
        }

        return $folder;
    }

    public function search($search_query)
    {
        $found_entries = [];

        // Get requested path
        $requested_path = Processor::instance()->get_requested_complete_path();

        // Set Search settings
        $folder_to_search_in = ('parent' === Processor::instance()->get_shortcode_option('searchfrom')) ? $requested_path : Processor::instance()->get_root_folder();
        $filename_only = ('1' === Processor::instance()->get_shortcode_option('searchcontents')) ? false : true;

        // Get Results
        $max_results = ('-1' !== Processor::instance()->get_shortcode_option('max_files')) ? (int) Processor::instance()->get_shortcode_option('max_files') : 1000;

        try {
            $found_entries = API::search($search_query, $folder_to_search_in, $filename_only, null, null, $max_results, ['file_status' => 'active']);
        } catch (\Exception $ex) {
            Helpers::log_error('Client is receiving API error.', 'API', null, __LINE__, $ex);

            exit('-1');
        }

        // Sort contents
        foreach ($found_entries as $key => $found_entry) {
            if (!Processor::instance()->_is_entry_authorized($found_entry)) {
                unset($found_entries[$key]);
            }
        }

        $folder = new Entry();
        $folder->set_name(basename($folder_to_search_in));
        $folder->set_path(Processor::instance()->get_relative_path($folder_to_search_in));
        $folder->set_is_dir(true);
        $folder->set_children($found_entries);

        return $folder;
    }

    public function get_folder_size($requested_path = null)
    {
        if (null === $requested_path) {
            $requested_path = Processor::instance()->get_requested_complete_path();
        }

        // Clean path if needed
        if (false !== strpos($requested_path, '/')) {
            $requested_path = Helpers::clean_folder_path($requested_path);
        }

        // Get folder children
        try {
            $api_folders_contents = App::instance()->get_sdk_client()->listFolder($requested_path, ['recursive' => true]);
            $api_entries = $api_folders_contents->getItems()->toArray();

            while ($api_folders_contents->hasMoreItems()) {
                $cursor = $api_folders_contents->getCursor();
                $api_folders_contents = App::instance()->get_sdk_client()->listFolderContinue($cursor);
                $api_entries = array_merge($api_entries, $api_folders_contents->getItems()->toArray());
            }

            unset($api_folders_contents);
        } catch (\Exception $ex) {
            Helpers::log_error('Client is receiving API error.', 'API', null, __LINE__, $ex);

            return null;
        }

        $total_size = 0;

        foreach ($api_entries as $api_entry) {
            $total_size += ($api_entry instanceof FolderMetadata) ? 0 : $api_entry->size;
        }

        unset($api_entries);

        return $total_size;
    }

    public function preview_entry()
    {
        // Get file meta data
        $entry = $this->get_entry();

        if (false === $entry) {
            exit('-1');
        }

        if (false === $entry->get_can_preview_by_cloud()) {
            exit('-1');
        }

        if (false === User::can_preview()) {
            exit('-1');
        }

        do_action('outofthebox_log_event', 'outofthebox_previewed_entry', $entry);

        // Preview for Media files in HTML5 Player
        if (in_array($entry->get_extension(), ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga', 'wav', 'flac'])) {
            if ($this->has_shared_link($entry)) {
                $temporarily_link = API::convert_to_raw_url($this->get_shared_link($entry), $entry->get_extension());
            } else {
                $temporarily_link = $this->get_temporarily_link($entry);
            }
            header('Location: '.$temporarily_link);

            exit;
        }

        // Preview for Image files
        if (in_array($entry->get_extension(), ['txt', 'jpg', 'jpeg', 'gif', 'png', 'webp'])) {
            $shared_link = API::convert_to_raw_url($this->get_shared_link($entry), $entry->get_extension());
            header('Location: '.$shared_link);

            exit;
        }

        // Preview for PDF files, read only via Google Viewer when needed
        if ('pdf' === $entry->get_extension()) {
            $shared_link = $this->get_shared_link($entry);
            $raw_link = API::convert_to_raw_url($shared_link, $entry->get_extension());

            if (false === User::can_download() && $entry->get_size() < 25000000) {
                $raw_link = 'https://docs.google.com/viewerng/viewer?embedded=true&url='.urlencode($raw_link);
            }

            header('Location: '.$raw_link);

            exit;
        }

        // Preview for Office files via Office Viewer (Except read-only)
        if (User::can_download()
         && in_array($entry->get_extension(), [
             'xls', 'xlsx', 'xlsm',
             'doc', 'docx', 'docm',
             'ppt', 'pptx', 'pptm', 'pps', 'ppsm', 'ppsx', ])
        ) {
            $temporarily_link = $this->get_temporarily_link($entry);
            $office_previewer = 'https://view.officeapps.live.com/op/embed.aspx?src='.urlencode($temporarily_link);
            header('Location: '.$office_previewer);

            exit;
        }

        // HTML previews are generated for files with the following extensions: .csv, .ods, .xls, .xlsm, .gsheet, .xlsx.
        if (in_array($entry->get_extension(), ['xls', 'xlsx', 'xlsm', 'gsheet', 'csv', 'ods'])) {
            header('Content-Type: text/html');
        } else {
            // PDF previews are generated for files with the following extensions: .ai, .doc, .docm, .docx, .eps, .gdoc, .gslides, .odp, .odt, .pps, .ppsm, .ppsx, .ppt, .pptm, .pptx, .rtf.
            header('Content-Disposition: inline; filename="'.$entry->get_basename().'.pdf"');
            header('Content-Description: "'.$entry->get_basename().'"');
            header('Content-Type: application/pdf');
        }

        try {
            $preview_file = API::get_preview($entry->get_id());
            echo $preview_file->getContents();
        } catch (\Exception $ex) {
            exit;
        }

        exit;
    }

    public function download_entry($entry = null)
    {
        if (null === $entry) {
            // Get file meta data
            $entry = $this->get_entry();
        }

        if (false === $entry) {
            exit(-1);
        }

        // TO DO Download notifications
        if ('1' === Processor::instance()->get_shortcode_option('notificationdownload')) {
            Processor::instance()->send_notification_email('download', [$entry]);
        }

        // If there is a temporarily download url present for this file, just redirect the user
        $stream = (isset($_REQUEST['action']) && 'outofthebox-stream' === $_REQUEST['action'] && !isset($_REQUEST['caption']));

        // ISSUE: Dropbox API can return errors for temporarily download links
        // When fixed, enable the following code:
        // $stored_url = ($stream) ? get_transient('outofthebox_stream_'.$entry->get_id().'_'.$entry->get_extension()) : get_transient('outofthebox_download_'.$entry->get_id().'_'.$entry->get_extension());
        // if (false !== $stored_url && filter_var($stored_url, FILTER_VALIDATE_URL)) {
        //     do_action('outofthebox_download', $entry, $stored_url);
        //     header('Location: '.$stored_url);

        //     exit();
        // }

        // Check if usage limits are hit
        if (!$stream && $download_limit_hit_message = Restrictions::has_reached_download_limit($entry->get_id(), false)) {
            header('Content-disposition: attachment; filename=Download limit exceeded - '.$entry->get_name().'.empty');

            if ('Firefox' === Helpers::get_browser_name()) {
                header('Content-type: text/plain');
                echo $download_limit_hit_message;

                exit;
            }

            http_response_code(429);

            exit;
        }

        if (!empty($entry->save_as) && 'web' !== $entry->get_extension()) {
            $this->export_entry($entry);

            do_action('outofthebox_download', $entry, null);
            do_action('outofthebox_log_event', 'outofthebox_downloaded_entry', $entry);

            exit;
        }

        if ('url' === $entry->get_extension()) {
            $download_file = App::instance()->get_sdk_client()->download($entry->get_id());
            preg_match_all('/URL=(.*)/', $download_file->getContents(), $location, PREG_SET_ORDER);

            if (2 === count($location[0])) {
                $temporarily_link = $location[0][1];
            }
        } elseif ('web' === $entry->get_extension()) {
            $download_file = App::instance()->get_sdk_client()->download($entry->get_id(), true);
            $data = json_decode($download_file->getContents());

            if (isset($data->url)) {
                $temporarily_link = $data->url;
            }
        } elseif ($stream && in_array($entry->get_extension(), ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga', 'wav', 'flac'])) {
            // Preview for Media files in HTML5 Player

            if ('1' === Processor::instance()->get_shortcode_option('files')) {
                // HTML 5 player in File Browser not working with temporarily url in iOS/OS
                $temporarily_link = API::convert_to_raw_url($this->get_shared_link($entry), $entry->get_extension());
            } else {
                $temporarily_link = $this->get_temporarily_link($entry);
            }
        } else {
            $temporarily_link = $this->get_temporarily_link($entry);
        }

        // Download Hook
        do_action('outofthebox_download', $entry, $temporarily_link);

        $event_type = $stream ? 'outofthebox_streamed_entry' : 'outofthebox_downloaded_entry';
        do_action('outofthebox_log_event', $event_type, $entry);

        if ('redirect' === Settings::get('download_method') && !isset($_REQUEST['proxy'])) {
            header('Location: '.$temporarily_link);
            set_transient('outofthebox_'.(($stream) ? 'stream' : 'download').'_'.$entry->get_id().'_'.$entry->get_extension(), $temporarily_link, MINUTE_IN_SECONDS * 5);
        } else {
            $this->download_via_proxy($entry, $temporarily_link);
        }

        exit;
    }

    public function export_entry(Entry $entry, $export_as = 'default')
    {
        if ('default' === $export_as) {
            $export_as = $entry->get_save_as();
        }

        $filename = ('default' === $export_as) ? $entry->get_name() : $entry->get_basename().'.'.$export_as;

        Helpers::set_time_limit(60);

        // Get file
        $stream = fopen('php://temp', 'r+');

        // Stop WP from buffering, and discard the current buffer.
        $levels = ob_get_level();
        for ($i = 0; $i < $levels; ++$i) {
            ob_end_clean();
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; '.sprintf('filename="%s"; ', rawurlencode($filename)).sprintf("filename*=utf-8''%s", rawurlencode($filename)));

        try {
            flush();

            $export_file = App::instance()->get_sdk_client()->download($entry->get_id(), $export_as);

            fwrite($stream, $export_file->getContents());
            rewind($stream);

            unset($export_file);

            while (!@feof($stream)) {
                echo @fread($stream, 1024 * 1024);
                ob_flush();
                flush();
            }
        } catch (\Exception $ex) {
            Helpers::log_error('Client is receiving API error.', 'API', null, __LINE__, $ex);
        }

        fclose($stream);

        exit;
    }

    public function download_via_proxy(Entry $entry, $url, $inline = false)
    {
        // Stop WP from buffering, and discard the current buffer.
        $levels = ob_get_level();
        for ($i = 0; $i < $levels; ++$i) {
            ob_end_clean();
        }

        Helpers::set_time_limit(500);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: '.($inline ? 'inline' : 'attachment').'; filename="'.basename($entry->get_name()).'"');
        header("Content-length: {$entry->get_size()}");

        if ($inline) {
            header("Content-type: {$entry->get_mimetype()}");
        }

        $options = ['curl' => [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RANGE => null,
            CURLOPT_NOBODY => null,
            CURLOPT_HEADER => false,
            CURLOPT_CONNECTTIMEOUT => null,
            CURLOPT_TIMEOUT => null,
            CURLOPT_WRITEFUNCTION => function ($curl, $data) {
                echo $data;

                return strlen($data);
            },
        ]];
        App::instance()->get_sdk_client()->getClient()->getHttpClient()->send($url, 'GET', '', [], $options);

        exit;
    }

    public function stream_entry()
    {
        // Get file meta data
        $entry = $this->get_entry();

        if (false === $entry) {
            exit(-1);
        }

        $extension = $entry->get_extension();
        $allowedextensions = ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'oga', 'wav', 'webm', 'vtt', 'srt'];

        if (empty($extension) || !in_array($extension, $allowedextensions)) {
            exit;
        }

        // Download Captions directly
        if (in_array($extension, ['vtt', 'srt'])) {
            $temporarily_link = $this->get_temporarily_link($entry);
            $this->download_via_proxy($entry, $temporarily_link);

            exit;
        }

        $this->download_entry($entry);
    }

    public function get_thumbnail(Entry $entry, $aslink = false, $width = null, $height = null, $crop = false)
    {
        if (false === $entry->has_own_thumbnail()) {
            $thumbnail_url = $entry->get_icon_large();
        } else {
            $thumbnail = new Thumbnail($entry, $width, $height, $crop);
            $thumbnail_url = $thumbnail->get_url();
        }

        if ($aslink) {
            return $thumbnail_url;
        }
        header('Location: '.$thumbnail_url);

        exit;
    }

    public function build_thumbnail()
    {
        $src = $_REQUEST['src'];
        preg_match_all('/(.+)_w(\d+)h(\d+)_c(\d)_([a-z]+)/', $src, $attr, PREG_SET_ORDER);

        if (1 !== count($attr) || 6 !== count($attr[0])) {
            exit;
        }

        $entry_id = $attr[0][1];
        $width = $attr[0][2];
        $height = $attr[0][3];
        $crop = $attr[0][4];
        $format = $attr[0][5];

        $entry = $this->get_entry($entry_id, false);

        if (false === $entry) {
            exit(-1);
        }

        // get the last-modified-date of this very file
        $lastModified = strtotime($entry->get_last_edited());
        // get a unique hash of this file (etag)
        $etagFile = md5($lastModified);
        // get the HTTP_IF_MODIFIED_SINCE header if set
        $ifModifiedSince = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false);
        // get the HTTP_IF_NONE_MATCH header if set (etag: unique file hash)
        $etagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

        if (!empty($entry->get_last_edited())) {
            header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastModified).' GMT');
            header("Etag: {$etagFile}");
        }

        header('Expires: '.gmdate('D, d M Y H:i:s', time() + 60 * 5).' GMT');
        header('Cache-Control: must-revalidate');

        if (!empty($entry->get_last_edited()) && ((false !== $ifModifiedSince && false !== $etagHeader) && @strtotime($ifModifiedSince) == $lastModified || $etagHeader == $etagFile)) {
            header('HTTP/1.1 304 Not Modified');

            exit;
        }

        if (false === $entry->has_own_thumbnail()) {
            header('Location: '.$entry->get_icon_large());

            exit;
        }

        $thumbnail = new Thumbnail($entry, $width, $height, $crop, $format);

        if (false === $thumbnail->does_thumbnail_exist()) {
            $thumbnail_created = $thumbnail->build_thumbnail();

            if (false === $thumbnail_created) {
                header('Location: '.$entry->get_icon_large());

                exit;
            }
        }

        header("Content-type: 'image/jpeg'");
        header('Content-Length: '.filesize($thumbnail->get_location_thumbnail()));
        readfile($thumbnail->get_location_thumbnail());

        exit;
    }

    public function has_temporarily_link(Entry $entry)
    {
        $cached_entry = Cache::instance()->is_cached($entry->get_id());

        if (false === $cached_entry) {
            return false;
        }
        $temporarily_link = $cached_entry->get_temporarily_link();

        return !empty($temporarily_link);
    }

    public function get_temporarily_link(Entry $entry)
    {
        $cached_entry = Cache::instance()->is_cached($entry->get_id());

        if (false !== $cached_entry) {
            if ($temporarily_link = $cached_entry->get_temporarily_link()) {
                return $temporarily_link;
            }
        }

        try {
            $temporarily_link = API::create_temporarily_download_url($entry->get_id());
            $cached_entry = Cache::instance()->add_to_cache($entry);

            $expires = time() + (1 * 60 * 60);

            $cached_entry->add_temporarily_link($temporarily_link->getLink(), $expires);
        } catch (\Exception $ex) {
            return false;
        }

        Cache::instance()->set_updated();

        return $cached_entry->get_temporarily_link();
    }

    public function has_shared_link(Entry $entry, $link_settings = ['audience' => 'public'])
    {
        $cached_entry = Cache::instance()->is_cached($entry->get_id());

        if (false !== $cached_entry && false !== $this->get_shared_link($entry, $link_settings, false)) {
            return true;
        }

        return false;
    }

    public function get_shared_link(Entry $entry, $link_settings = ['audience' => 'public'], $create = true)
    {
        $cached_entry = Cache::instance()->is_cached($entry->get_id());

        // Custom link settings for non Basic accounts
        if (empty($link_settings) && in_array(App::get_current_account()->get_type(), ['pro', 'business'])) {
            // Add Password
            $password = Processor::instance()->get_shortcode_option('share_password');
            if (!empty($password)) {
                $link_settings['require_password'] = true;
                $link_settings['link_password'] = $password;
            }

            // Add Expire date
            $expire_after = Processor::instance()->get_shortcode_option('share_expire_after');
            if (!empty($expire_after)) {
                $expire_date = current_datetime()->modify('+'.$expire_after);
                $link_settings['expires'] = $expire_date->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
            }

            // Read-Only?
            $share_allow_download = Processor::instance()->get_shortcode_option('share_allow_download');
            if ('0' === $share_allow_download) {
                $link_settings['allow_download'] = false;
            }
        }

        $default_settings = [
            'audience' => 'public',
            'allow_download' => true,
            'require_password' => false,
            'expires' => null,
        ];

        $link_settings = array_merge($default_settings, $link_settings);

        if (false !== $cached_entry && ($shared_link = $cached_entry->get_shared_link($link_settings))) {
            return $shared_link;
        }

        return ($create) ? $this->create_shared_link($entry, $link_settings) : false;
    }

    public function create_shared_link(Entry $entry, $link_settings)
    {
        $cached_entry = Cache::instance()->add_to_cache($entry);

        $shared_links = API::create_shared_url($entry->get_id(), $link_settings);
        foreach ($shared_links as $shared_link_info) {
            $cached_entry->add_shared_link($shared_link_info, $link_settings);
        }

        if (empty($shared_links)) {
            exit(esc_html__('The sharing permissions on this file is preventing you from accessing this shared link. Please contact the administrator to change the sharing settings for this document in the cloud.'));
        }

        if (1 === $shared_links) {
            do_action('outofthebox_log_event', 'outofthebox_created_link_to_entry', $entry, ['url' => reset($shared_links)]);
        }

        return $cached_entry->get_shared_link($link_settings);
    }

    public function get_embedded_link(Entry $entry)
    {
        if (false === $entry->get_can_preview_by_cloud()
         || in_array($entry->get_extension(), ['pdf', 'jpg', 'jpeg', 'png', 'gif'])
         || in_array($entry->get_extension(), ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga', 'wav', 'flac'])
        ) {
            return API::convert_to_raw_url($this->get_shared_link($entry), $entry->get_extension());
        }

        // Preview for Office files via Office Viewer (Except read-only)
        if (User::can_download()
         && in_array($entry->get_extension(), [
             'xls', 'xlsx', 'xlsm',
             'doc', 'docx', 'docm',
             'ppt', 'pptx', 'pptm', 'pps', 'ppsm', 'ppsx', ])
        ) {
            $shared_link = API::convert_to_raw_url($this->get_shared_link($entry), $entry->get_extension());

            return 'https://view.officeapps.live.com/op/embed.aspx?src='.urlencode($shared_link);
        }

        return OUTOFTHEBOX_ADMIN_URL."?action=outofthebox-embed-entry&OutoftheBoxpath={$entry->get_id()}&account_id=".App::get_current_account()->get_id();
    }

    public function get_shared_link_for_output($entry_path = null)
    {
        $entry = $this->get_entry($entry_path);

        if (false === $entry) {
            exit(-1);
        }

        $shared_link = $this->get_shared_link($entry, []).'&dl=1';
        $embed_link = $this->get_embedded_link($entry);

        return [
            'name' => $entry->get_name(),
            'extension' => $entry->get_extension(),
            'link' => API::shorten_url($shared_link, null, ['name' => $entry->get_name()]),
            'embeddedlink' => $embed_link,
            'size' => Helpers::bytes_to_size_1024($entry->get_size()),
            'error' => false,
        ];
    }

    public function add_folder($name_of_folder_to_create, $target_folder_path = null)
    {
        if ('1' === Processor::instance()->get_shortcode_option('demo')) {
            exit(-1);
        }

        if (null === $target_folder_path) {
            $target_folder_path = Processor::instance()->get_requested_complete_path();
        }

        $target_entry = $this->get_entry($target_folder_path);

        try {
            $new_entry = API::create_folder($name_of_folder_to_create, $target_entry->get_path(), ['autorename' => false]);
        } catch (DropboxClientException $ex) {
            return new \WP_Error('broke', esc_html__('Failed to add folder', 'wpcloudplugins'));
        }

        return $new_entry;
    }

    public function rename_entry($new_name, $target_entry_path = null)
    {
        if (null === $target_entry_path) {
            $target_entry_path = Processor::instance()->get_requested_complete_path();
        }

        $target_entry = $this->get_entry($target_entry_path);

        if (
            $target_entry->is_file() && false === User::can_rename_files()) {
            exit(-1);
        }

        if (
            $target_entry->is_dir() && false === User::can_rename_folders()) {
            exit(-1);
        }

        if ('1' === Processor::instance()->get_shortcode_option('demo')) {
            exit(-1);
        }

        try {
            $new_entry = API::rename($target_entry, $new_name);
        } catch (\Exception $ex) {
            return new \WP_Error('broke', esc_html__('Failed to rename file.', 'wpcloudplugins'));
        }

        return $new_entry;
    }

    public function move_entries($entries, $target_entry_path, $copy = false)
    {
        $entries_to_move = [];
        $batch_request = [
            'entries' => [],
        ];
        $target = $this->get_entry($target_entry_path);

        if (false === $target) {
            Helpers::log_error('Failed to move as target folder is not found.', 'Client', ['target' => $target_entry_path], __LINE__);

            return $entries_to_move;
        }

        foreach ($entries as $entry_path) {
            $entry = $this->get_entry($entry_path);

            if (false === $entry) {
                continue;
            }

            if (!$copy && $entry->is_dir() && (false === User::can_move_folders())) {
                Helpers::log_error('Failed to move as user is not allowed to move folders.', 'Client', ['target' => $target->get_path()], __LINE__);
                $entries_to_move[$entry->get_id()] = false;

                continue;
            }

            if ($copy && $entry->is_dir() && (false === User::can_copy_folders())) {
                Helpers::log_error('Failed to move as user is not allowed to copy folders.', 'Client', ['target' => $target->get_id()], __LINE__);
                $entries_to_move[$entry->get_id()] = false;

                continue;
            }

            if (!$copy && $entry->is_file() && (false === User::can_move_files())) {
                Helpers::log_error('Failed to move as user is not allowed to move files.', 'Client', ['target' => $target->get_path()], __LINE__);
                $entries_to_move[$entry->get_id()] = false;

                continue;
            }

            if ($copy && $entry->is_file() && (false === User::can_copy_files())) {
                Helpers::log_error('Failed to move as user is not allowed to copy files.', 'Client', ['target' => $target->get_path()], __LINE__);
                $entries_to_move[$entry->get_id()] = false;

                continue;
            }

            if ('1' === Processor::instance()->get_shortcode_option('demo')) {
                $entries_to_move[$entry->get_id()] = false;

                continue;
            }

            // Check user permission
            if (!$copy && !$entry->get_permission('canmove')) {
                Helpers::log_error('Failed to move as the sharing permissions on it prevent this.', 'Client', ['target' => $target->get_id()], __LINE__);
                $entries_to_move[$entry->get_id()] = false;

                continue;
            }

            $new_entry_path = Helpers::clean_folder_path($target->get_path().'/'.$entry->get_name());

            $batch_request['entries'][] = [
                'from_path' => $entry->get_path(),
                'to_path' => $new_entry_path,
            ];

            $entries_to_move[$entry->get_id()] = false; // update if batch request was succesfull
        }

        try {
            if ($copy) {
                $batch_request['autorename'] = true;
                $processed_entries = API::copy($batch_request);
            } else {
                $processed_entries = API::move($batch_request);
            }
        } catch (\Exception $ex) {
            Helpers::log_error('Client is receiving API error.', 'API', null, __LINE__, $ex);

            return $entries_to_move;
        }

        // Send email if needed
        if ($copy && '1' === Processor::instance()->get_shortcode_option('notificationcopy')) {
            Processor::instance()->send_notification_email('copy_multiple', $processed_entries);
        } elseif ('1' === Processor::instance()->get_shortcode_option('notificationmove')) {
            Processor::instance()->send_notification_email('move_multiple', $processed_entries);
        }

        return $processed_entries;
    }

    public function delete_entries($entries_to_delete = [])
    {
        $deleted_entries = [];
        $batch_request = ['entries' => []];

        foreach ($entries_to_delete as $target_entry_id) {
            $target_entry = $this->get_entry($target_entry_id);

            if (false === $target_entry) {
                continue;
            }

            if ($target_entry->is_file() && false === User::can_delete_files()) {
                continue;
            }

            if ($target_entry->is_dir() && false === User::can_delete_folders()) {
                continue;
            }

            if ('1' === Processor::instance()->get_shortcode_option('demo')) {
                continue;
            }

            $deleted_entries[$target_entry->get_id()] = $target_entry;

            $batch_request['entries'][] = [
                'path' => $target_entry->get_id(),
            ];
        }

        try {
            $deleted_entries = API::delete($batch_request);
        } catch (\Exception $ex) {
            return new \WP_Error('broke', esc_html__('Failed to delete file.', 'wpcloudplugins'));
        }

        if ('1' === Processor::instance()->get_shortcode_option('notificationdeletion')) {
            // TO DO NOTIFICATION
            Processor::instance()->send_notification_email('deletion', $deleted_entries);
        }

        CacheRequest::clear_request_cache();

        return $deleted_entries;
    }
}
