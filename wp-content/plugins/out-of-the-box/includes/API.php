<?php
/*
 * API Class.
 *
 * Use the API to execute calls directly for the set cloud account.
 * You can use the API using WPCP_DROPBOX_API::get_entry(...)
 *
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

use TheLion\OutoftheBox\API\Dropbox\Exceptions\DropboxClientException;
use TheLion\OutoftheBox\API\Dropbox\Models\File;
use TheLion\OutoftheBox\API\Dropbox\Models\SharedLinkSettings;
use TheLion\OutoftheBox\API\Dropbox\Models\TemporaryLink;

defined('ABSPATH') || exit; // Exit if accessed directly.

require_once OUTOFTHEBOX_ROOTDIR.'/vendors/dropbox-sdk/vendor/autoload.php';

class API
{
    /**
     * Set which cloud account should be used.
     *
     * @return Account|false - Account
     */
    public static function set_account_by_id(string $account_id)
    {
        $account = Accounts::instance()->get_account_by_id($account_id);
        if (null === $account) {
            Helpers::log_error('Cannot use the requested account as it is not linked with the plugin', 'Account', ['account_id' => $account_id], __LINE__);

            return false;
        }

        return App::set_current_account($account);
    }

    /**
     * Get entry information.
     *
     * @param string $id     ID or PATH of the entry that should be loaded
     * @param array  $params
     *
     * @return API_Exception|Entry
     */
    public static function get_entry($id, $params = ['include_media_info' => true])
    {
        // Clean path if needed
        if (false !== strpos($id, '/')) {
            $id = Helpers::clean_folder_path($id);
        }

        if ('/' === $id) {
            $id = ''; // Root folder doesn't have a /
        }

        do_action('outofthebox_api_before_get_entry', $id);

        $cache_key = 'wpcp-e-'.md5(App::get_current_account()->get_id().$id.'-'.serialize($params));
        $entry = wp_cache_get($cache_key, 'wpcp-'.CORE::$slug.'-entries', false);

        if (!empty($entry)) {
            do_action('outofthebox_api_after_get_entry', $entry);

            return $entry;
        }

        try {
            $api_entry = App::instance()->get_sdk_client()->getMetadata($id, $params);
        } catch (\Exception $ex) {
            Helpers::log_error('', 'API', null, __LINE__, $ex);

            throw new API_Exception(esc_html__('Failed to load file.', 'wpcloudplugins'));
        }

        $entry = new Entry($api_entry);

        wp_cache_set($cache_key, $entry, 'wpcp-'.CORE::$slug.'-entries', 300);

        do_action('outofthebox_api_after_get_entry', $entry);

        return $entry;
    }

    /**
     * Get folder information. Metadata of direct child files are loaded as well.
     *
     * @param string $id     ID or PATH of the folder that should be loaded
     * @param array  $params
     *
     * @return API_Exception|Entry
     */
    public static function get_folder($id, $params = ['recursive' => false, 'hierarchical' => true])
    {
        // Clean path if needed
        if (false !== strpos($id, '/')) {
            $id = Helpers::clean_folder_path($id);
        }

        if ('/' === $id) {
            $id = ''; // Root folder doesn't have a /
        }

        do_action('outofthebox_api_before_get_folder', $id);

        $cache_key = 'wpcp-e-'.md5(App::get_current_account()->get_id().$id.'-'.serialize($params));
        $folder_entry = wp_cache_get($cache_key, 'wpcp-'.CORE::$slug.'-entries', false);

        if (!empty($folder_entry)) {
            foreach ($folder_entry->get_children() as $child) {
                $relative_path = Processor::instance()->get_relative_path($child->get_path_original());
                $child->set_path($relative_path);
                $relative_path_display = Processor::instance()->get_relative_path($child->get_path_display_original());
                $child->set_path_display($relative_path_display);
            }

            do_action('outofthebox_api_after_get_folder', $folder_entry);

            $children = Processor::instance()->sort_filelist($folder_entry->get_children());
            $folder_entry->set_children($children);

            return $folder_entry;
        }

        // Get Folder items
        try {
            $api_folders_contents = App::instance()->get_sdk_client()->listFolder($id, ['recursive' => $params['recursive']]);
            $api_entries = $api_folders_contents->getItems()->toArray();

            while ($api_folders_contents->hasMoreItems()) {
                $cursor = $api_folders_contents->getCursor();
                $api_folders_contents = App::instance()->get_sdk_client()->listFolderContinue($cursor);
                $api_entries = array_merge($api_entries, $api_folders_contents->getItems()->toArray());
            }
        } catch (\Exception $ex) {
            Cache::instance()->reset_cache();
            Helpers::log_error('', 'API', ['entry_id' => $id], __LINE__, $ex);

            throw new API_Exception(esc_html__('Failed to load file.', 'wpcloudplugins'));
        }

        $children = [];
        if (count($api_entries) > 0) {
            foreach ($api_entries as $api_entry) {
                $entry = new Entry($api_entry);
                $relative_path = Processor::instance()->get_relative_path($entry->get_path_original());
                $entry->set_path($relative_path);
                $relative_path_display = Processor::instance()->get_relative_path($entry->get_path_display_original());
                $entry->set_path_display($relative_path_display);
                $children[$entry->get_id()] = $entry;
            }
        }

        // Sort contents
        if (!empty($children)) {
            $children = Processor::instance()->sort_filelist($children);
        }

        // Make a hierarchical structure if a recursive reponse is requested
        if ($params['recursive'] && $params['hierarchical']) {
            foreach ($children as $child_id => $child) {
                $relative_path = Processor::instance()->get_relative_path($child->get_parent());
                $parent_id = Helpers::find_item_in_array_with_value($children, 'path', $relative_path);

                if (false === $parent_id || $parent_id === $child->get_id()) {
                    $child->flag = false;

                    continue;
                }

                $parent = $children[$parent_id];
                $parent_childs = $parent->get_children();
                $parent_childs[$child->get_id()] = $child;
                $parent->set_children($parent_childs);

                $child->flag = true;
            }

            foreach ($children as $child_id => $child) {
                if ($child->flag) {
                    unset($children[$child_id]);
                }
            }
        }

        // Get folder meta data (no meta data for root folder)
        if ('' === $id) {
            $folder_entry = new Entry();
            $folder_entry->set_id('Dropbox');
            $folder_entry->set_name('Dropbox');
            $folder_entry->set_basename('Dropbox');
            $folder_entry->set_path('/');
            $folder_entry->set_path_original('/');
            $folder_entry->set_path_display('/');
            $folder_entry->set_path_display_original('/');
            $folder_entry->set_is_dir(true);
            $folder_entry->set_children($children);
        } elseif (!$params['recursive'] || !$params['hierarchical']) {
            $api_entry = App::instance()->get_sdk_client()->getMetadata($id);
            $folder_entry = new Entry($api_entry);
            $folder_entry->set_children($children);
        } else {
            $folder_entry = reset($children);
        }

        wp_cache_set($cache_key, $folder_entry, 'wpcp-'.CORE::$slug.'-entries', 300);

        do_action('outofthebox_api_after_get_folder', $folder_entry);

        return $folder_entry;
    }

    /**
     * Get (and create) sub folder by path.
     *
     * @param string $parent_folder_path
     * @param string $subfolder_path
     * @param bool   $create_if_not_exist
     *
     * @return bool|Entry
     */
    public static function get_sub_folder_by_path($parent_folder_path, $subfolder_path, $create_if_not_exist = false)
    {
        $full_path = helpers::clean_folder_path($parent_folder_path.'/'.$subfolder_path);

        try {
            $api_entry = App::instance()->get_sdk_client()->getMetadata($full_path);

            return new Entry($api_entry);
        } catch (\Exception $ex) {
            if (false === apply_filters('outofthebox_api_create_subfolder_if_not_exist', $create_if_not_exist, $parent_folder_path, $subfolder_path)) {
                return false;
            }
            // Folder doesn't exists, so continue
        }

        try {
            $api_entry_new = App::instance()->get_sdk_client()->createFolder($full_path);
        } catch (\Exception $ex) {
            return false;
        }

        $sub_folder = new Entry($api_entry_new);
        do_action('outofthebox_log_event', 'outofthebox_created_entry', $sub_folder);

        return $sub_folder;
    }

    /**
     * Create a new folder in the Cloud Account.
     *
     * @param string $new_name           the name for the newly created folder
     * @param string $target_folder_path The folder path where the new folder should be created
     * @param array  $params
     *
     * @return API_Exception|Entry
     */
    public static function create_folder($new_name, $target_folder_path, $params = ['autorename' => false])
    {
        $target_folder_path = apply_filters('outofthebox_api_create_folder_set_parent_path', $target_folder_path);
        $params = apply_filters('outofthebox_api_create_folder_set_params', $params);

        do_action('outofthebox_api_before_create_folder', $new_name, $target_folder_path, $params);

        $new_folder_path = Helpers::clean_folder_path($target_folder_path.'/'.$new_name);

        try {
            $api_entry = App::instance()->get_sdk_client()->createFolder($new_folder_path, $params['autorename']);
            $new_entry = new Entry($api_entry);

            do_action('outofthebox_log_event', 'outofthebox_created_entry', $new_entry);

            CacheRequest::clear_local_cache_for_shortcode(App::get_current_account()->get_id(), Processor::instance()->get_listtoken());
        } catch (DropboxClientException $ex) {
            if (false !== strpos($ex->getErrorSummary(), 'path/conflict/folder/')) {
                return self::get_entry($target_folder_path.'/'.$new_name);
            }

            Cache::instance()->reset_cache();
            Helpers::log_error('', 'API', null, __LINE__, $ex);

            throw new API_Exception(esc_html__('Failed to create folder.', 'wpcloudplugins'));
        }

        do_action('outofthebox_api_after_create_folder', $new_entry);

        return $new_entry;
    }

    /**
     * Rename a file/folder.
     *
     * @param Entry  $entry    The entry that should be renamed
     * @param string $new_name The new name
     * @param array  $params
     *
     * @return API_Exception|Entry
     */
    public static function rename(Entry $entry, $new_name, $params = ['autorename' => false])
    {
        $new_name = apply_filters('outofthebox_api_rename_set_params', $new_name);
        $params = apply_filters('outofthebox_api_rename_set_params', $params);

        do_action('outofthebox_api_before_rename', $new_name, $entry, $params);

        $new_folder_path = Helpers::clean_folder_path($entry->get_parent().'/'.$new_name);

        try {
            $old_name = $entry->get_name();
            $api_entry = App::instance()->get_sdk_client()->move($entry->get_id(), $new_folder_path, $params['autorename']);
            $new_entry = new Entry($api_entry);

            do_action('outofthebox_log_event', 'outofthebox_renamed_entry', $new_entry, ['old_name' => $old_name]);
        } catch (\Exception $ex) {
            Helpers::log_error('', 'API', null, __LINE__, $ex);

            throw new API_Exception(esc_html__('Failed to rename file.', 'wpcloudplugins'));
        }

        CacheRequest::clear_local_cache_for_shortcode(App::get_current_account()->get_id(), Processor::instance()->get_listtoken());

        do_action('outofthebox_api_after_rename', $new_entry);

        return $new_entry;
    }

    /**
     * Create a temporarily download url for a file or folder.
     *
     * @param string $id     ID of the entry for which you want to create the temporarily download url
     * @param string $format Format for the downloaded file. Only 'default' currently supported
     * @param array  $params
     *
     * @return API_Exception|TemporaryLink
     */
    public static function create_temporarily_download_url($id, $format = 'default', $params = [])
    {
        do_action('outofthebox_api_before_create_temporarily_download_url', $id, $format, $params);

        try {
            // Get a Download link via the Box API
            switch ($format) {
                case 'default':
                default:
                    $url = App::instance()->get_sdk_client()->getTemporaryLink($id, false);
            }

            if (empty($url)) {
                Helpers::log_error('Cannot generate temporarily download link.', 'API', ['entry_id' => $id], __LINE__);

                return false;
            }
        } catch (\Exception $ex) {
            Helpers::log_error('', 'API', null, __LINE__, $ex);

            return false;
        }

        $url = apply_filters('outofthebox_api_create_temporarily_download_url_set_url', $url);

        do_action('outofthebox_api_after_create_temporarily_download_url', $id, $format, $url);

        return $url;
    }

    /**
     * Create a public shared url for a file or folder.
     *
     * @param string $id     ID of the entry for which you want to create the shared url
     * @param array  $params
     *
     * @return API_Exception|\TheLion\OutoftheBox\API\Dropbox\Models\FileLinkMetadata[]|\TheLion\OutoftheBox\API\Dropbox\Models\FolderLinkMetadata[] Returns an array with shared link information
     */
    public static function create_shared_url($id, $params = [])
    {
        $default_params = [
            'audience' => 'public',
            'access' => 'viewer',
            'expires' => null,
            'require_password' => null,
            'link_password' => null,
        ];

        $params = array_merge($default_params, $params);
        $params = apply_filters('outofthebox_api_create_shared_url_set_params', $params);

        do_action('outofthebox_api_before_create_shared_url', $id, $params);

        $settings = new SharedLinkSettings($params);

        try {
            $shared_link_info = App::instance()->get_sdk_client()->createSharedLinkWithSettings($id, $settings);
            $shared_link_info = apply_filters('outofthebox_api_create_shared_url_set_link', $shared_link_info);

            do_action('outofthebox_log_event', 'outofthebox_updated_metadata', $id, ['metadata_field' => 'Sharing Permissions']);
        } catch (DropboxClientException $ex) {
            if ('shared_link_already_exists' === $ex->getError() || (false !== strpos($ex->getErrorSummary(), 'shared_link_already_exists'))) {
                // Get existing shared link
                return App::instance()->get_sdk_client()->listSharedLinks($id)->getItems();
            }

            Helpers::log_error('Cannot generate shared url.', 'API', ['entry_id' => $id, 'error_msg' => $ex->getErrorSummary()], __LINE__);

            return [];
        }

        do_action('outofthebox_api_after_create_shared_url', $shared_link_info);

        return [$shared_link_info];
    }

    /**
     * Create a public embed url for a file.
     * NOT SUPPORTED BY DROPBOX.
     *
     * @param string $id     ID of the entry for which you want to create the embed url
     * @param array  $params
     *
     * @return API_Exception|array Returns an array with shared link information
     */
    public static function create_embed_url($id, $params = [])
    {
        do_action('outofthebox_api_before_create_embedded_url', $id, $params);

        $embedded_link = null;
        $embedded_link = apply_filters('outofthebox_api_create_embed_url_set_link', $embedded_link);

        do_action('outofthebox_api_after_create_embedded_url', $embedded_link);

        return $embedded_link;
    }

    /**
     * Create an url to an editable view of the file.
     * NOT SUPPORTED BY DROPBOX.
     *
     * @param string $id     ID of the entry for which you want to create the editable url
     * @param array  $params
     *
     * @return API_Exception|string
     */
    public static function create_edit_url($id, $params = [])
    {
        $params = apply_filters('outofthebox_api_create_edit_url_set_params', $params);

        do_action('outofthebox_api_before_create_edit_url', $id, $params);

        $link = null; // NOT SUPPORTED BY DROPBOX.

        do_action('outofthebox_api_after_create_edit_url', $link);

        return $link;
    }

    /**
     * Create an url to a preview of the file.
     *
     * @param Entry $entry  Entry object for which you want to create the preview
     * @param array $params
     *
     * @return API_Exception|string
     */
    public static function create_preview_url(Entry $entry, $params = [])
    {
        do_action('outofthebox_api_before_create_preview_url', $entry, $params);
        $params = apply_filters('outofthebox_api_create_preview_url_set_params', $params);

        if (false === $entry->get_can_preview_by_cloud()) {
            return false;
        }
        if (in_array($entry->get_extension(), ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga', 'wav', 'flac'])) {
            // Preview for Media files = raw file itself
            $link = self::create_temporarily_download_url($entry->get_id());
        } elseif (in_array($entry->get_extension(), ['txt', 'jpg', 'jpeg', 'gif', 'png', 'webp'])) {
            // Preview for PDF and images files = raw file itself but via a shared url
            $link = self::convert_to_raw_url(self::create_shared_url($entry, ['audience' => 'public', 'access' => 'viewer']), $entry->get_extension());
        } else {
            // Create Preview via API
            try {
                $link = App::instance()->get_sdk_client()->preview($entry->get_id());
            } catch (\Exception $ex) {
                Helpers::log_error('', 'API', null, __LINE__, $ex);

                return false;
            }
        }

        $link = apply_filters('outofthebox_api_create_preview_url_set_link', $link);

        do_action('outofthebox_api_after_create_preview_url', $link);

        return $link;
    }

    /**
     * Convert a shared link to a raw url.
     *
     * @param string $shared_url
     * @param mixed  $extension
     */
    public static function convert_to_raw_url($shared_url, $extension)
    {
        $raw_url = str_replace('/s/', '/s/raw/', $shared_url);

        if (false === strpos($raw_url, 'scl/fi/')) {
            return $raw_url;
        }

        // Support for new /scl/fi links
        $raw_url .= '&raw=1';

        // Dropbox has disabled raw previews of PDF files in the past.
        // Keep this code in place, in case a fallback is required.

        // $raw_url = str_replace('www.dropbox.com', 'dl.dropboxusercontent.com', $raw_url);
        // $raw_url = preg_replace('/dl=(\d)/', 'dl=0', $raw_url);
        // if (false === strpos($raw_url, 'dl=0')) {
        //     $raw_url .= '&dl=0';
        // }

        return $raw_url;
    }

    /**
     * Create an a preview for a supported file format.
     *
     * @param string $id     ID of the entry for which you want to create the preview for
     * @param array  $params
     *
     * @return File Use $return->getContents() for the preview data
     */
    public static function get_preview($id, $params = [])
    {
        do_action('outofthebox_api_before_create_preview', $id, $params);
        $params = apply_filters('outofthebox_api_create_preview_set_params', $params);

        // Create Preview via API
        try {
            $file = App::instance()->get_sdk_client()->preview($id);
        } catch (\Exception $ex) {
            Helpers::log_error('', 'API', null, __LINE__, $ex);

            return false;
        }

        do_action('outofthebox_api_after_create_preview', $file);

        return $file;
    }

    /**
     * Copy multiple files to a new location.
     *
     * @param array $request An array with 'from_path' and 'to_path' keys. Path can both be an ID or a actual path
     *                       <code>
     *                       [{
     *                       "from_path": "/Homework/math",
     *                       "to_path": "/Homework/algebra"
     *                       }]
     *                       </code>
     * @param array $params  ['original_name'=>'...']
     *
     * @return API_Exception|Entry[]
     */
    public static function copy($request, $params = [])
    {
        $request = apply_filters('outofthebox_api_copy_set_request', $request);
        $params = apply_filters('outofthebox_api_copy_set_params', $params);

        do_action('outofthebox_api_before_copy', $request, $params);

        try {
            $result = App::instance()->get_sdk_client()->copyBatch($request);
            $api_entries = $result->getItems()->toArray();
            $entries = [];
            foreach ($api_entries as $api_entry) {
                $new_entry = new Entry($api_entry);
                $entries[] = $new_entry;
                do_action('outofthebox_log_event', 'outofthebox_copied_entry', $new_entry, ['original' => isset($params['original_name']) ? $params['original_name'] : '']);
            }
        } catch (\Exception $ex) {
            Cache::instance()->reset_cache();
            Helpers::log_error('', 'API', null, __LINE__, $ex);

            throw new API_Exception(esc_html__('Failed to copy file.', 'wpcloudplugins'));
        }

        Cache::instance()->update_cache();

        // Clear Cached Requests
        CacheRequest::clear_local_cache_for_shortcode(App::get_current_account()->get_id(), Processor::instance()->get_listtoken());

        do_action('outofthebox_api_after_copy', $entries);

        return $entries;
    }

    /**
     * Move an entry to a new location.
     *
     * @param array $request An array with 'from_path' and 'to_path' keys. Path can both be an ID or a actual path
     *                       <code>
     *                       {entries: [{
     *                       "from_path": "/Homework/math",
     *                       "to_path": "/Homework/algebra"
     *                       }]}
     *                       </code>
     * @param array $params
     *
     * @return array CacheNode[]|API_Exception
     */
    public static function move($request, $params = [])
    {
        $request = apply_filters('outofthebox_api_move_set_request', $request);
        $params = apply_filters('outofthebox_api_move_set_params', $params);

        do_action('outofthebox_api_before_move', $request, $params);

        try {
            $result = App::instance()->get_sdk_client()->moveBatch($request);
            $api_entries = $result->getItems()->toArray();
            $entries = [];

            foreach ($api_entries as $api_entry) {
                if ('failure' === $api_entry->{'.tag'}) {
                    continue;
                }

                $new_entry = new Entry($api_entry);

                $entries[$new_entry->get_id()] = $new_entry;
                do_action('outofthebox_log_event', 'outofthebox_moved_entry', $new_entry);
            }
        } catch (\Exception $ex) {
            Cache::instance()->reset_cache();
            Helpers::log_error('', 'API', null, __LINE__, $ex);

            throw new API_Exception(esc_html__('Failed to move file.', 'wpcloudplugins'));
        }

        Cache::instance()->update_cache();

        // Clear Cached Requests
        CacheRequest::clear_local_cache_for_shortcode(App::get_current_account()->get_id(), Processor::instance()->get_listtoken());

        do_action('outofthebox_api_after_move', $entries);

        return $entries;
    }

    /**
     * Move the contents of an folder into another folder.
     *
     * @param string $folder_id ID or path of the folder containing the files that should be moved
     * @param string $target_id ID or path of the folder where the files should be moved to
     *
     * @return API_Exception|bool
     */
    public static function move_folder_content($folder_id, $target_id)
    {
        do_action('outofthebox_api_before_move_folder_content', $folder_id, $target_id);

        $folder = self::get_folder($folder_id);
        $target_folder = self::get_folder($target_id);

        $entries = $folder->get_children();
        $entries = apply_filters('outofthebox_api_move_folder_content_set_entries', $entries);

        $request = ['entries' => []];

        foreach ($entries as $entry) {
            $request['entries'][] = ['from_path' => $entry->get_id(), 'to_path' => $target_folder->get_path_original().'/'.$entry->get_name()];
        }

        try {
            $result = self::move($request);
        } catch (\Exception $ex) {
            Cache::instance()->reset_cache();
            Helpers::log_error('Cannot move the content of a folder into another folder.', 'API', ['entry_id' => $folder_id, 'target_id' => $target_id], __LINE__, $ex);

            return false;
        }

        do_action('outofthebox_api_after_move_folder_content', $result);

        return $result;
    }

    /**
     * Delete  files by path.
     *
     * @param array $request An array with 'path' keys. Path can both be an ID or a actual path
     *                       <code>
     *                       {entries: [{
     *                       "path": "/Homework/math",
     *                       }]}
     *                       </code>
     * @param array $params
     *
     * @return API_Exception|Entry[]
     */
    public static function delete($request, $params = [])
    {
        do_action('outofthebox_api_before_delete', $request, $params);

        try {
            $result = App::instance()->get_sdk_client()->deleteBatch($request);

            $api_entries = $result->getItems()->toArray();
            $entries = [];

            foreach ($api_entries as $api_entry) {
                $entry = new Entry($api_entry);
                do_action('outofthebox_log_event', 'outofthebox_deleted_entry', $entry);
                $entries[] = $entry;
            }
        } catch (\Exception $ex) {
            Cache::instance()->reset_cache();
            Helpers::log_error('', 'API', null, __LINE__, $ex);

            throw new API_Exception(esc_html__('Failed to delete file.', 'wpcloudplugins'));
        }

        do_action('outofthebox_api_after_delete', $entries);

        return $entries;
    }

    /**
     * Get the account information.
     *
     * @return API\Dropbox\Models\Account
     */
    public static function get_account_info()
    {
        $cache_key = 'outofthebox_account_'.App::get_current_account()->get_id();
        if (empty($account_info = get_transient($cache_key, false))) {
            $account_info = App::instance()->get_sdk_client()->getCurrentAccount();

            \set_transient($cache_key, $account_info, HOUR_IN_SECONDS);
        }

        return $account_info;
    }

    /**
     * Get the information about the available space.
     *
     * @return array
     */
    public static function get_space_info()
    {
        $cache_key = 'outofthebox_account_'.App::get_current_account()->get_id().'_space';
        if (empty($space_info = get_transient($cache_key, false))) {
            $space_info = App::instance()->get_sdk_client()->getSpaceUsage();

            \set_transient($cache_key, $space_info, HOUR_IN_SECONDS);
        }

        return $space_info;
    }

    /**
     * Search Cloud Account.
     *
     * @param mixed  $query         The search query itself
     * @param mixed  $folder_id     ID of the folder where the search should take place
     * @param bool   $filename_only Restricts search to only match on filenames. The default for this field is False.
     * @param array  $extensions    Array of extra search data. E.g. ['jpg','png','tiff','etc']
     * @param string $type          Restricts search to only the file categories specified. Only supported for active file search. This field is optional.
     *                              image|document|pdf|spreadsheet|presentation|audio|video|folder|paper|others
     * @param int    $limit         defines the maximum number of items to return as part of a page of results
     * @param array  $params
     *
     * @return API_Exception|Entry[]
     */
    public static function search($query, $folder_id, $filename_only = false, $extensions = null, $type = null, $limit = 200, $params = [])
    {
        $extensions = is_array($extensions) ? join(',', $extensions) : $extensions;

        $default_params = [
            'filename_only' => $filename_only,
            'file_extensions' => $extensions,
            'file_status' => 'active',
            'file_categories' => $type,
            'max_results' => min($limit, 1000),
        ];

        $params = array_merge($default_params, $params);

        // Set all params
        $query = apply_filters('outofthebox_api_search_set_query', $query);
        $params = apply_filters('outofthebox_api_search_set_params', $params);

        // Do the search
        do_action('outofthebox_api_before_search', $query, $folder_id, $limit, $params);

        $searched_folder = self::get_folder($folder_id);

        do_action('outofthebox_log_event', 'outofthebox_searched', $searched_folder, ['query' => $query]);

        try {
            $result = App::instance()->get_sdk_client()->search($folder_id, $query, $params);

            $api_entries = $result->getItems()->toArray();

            while ($result->hasMoreItems() && count($api_entries) < $params['max_results'] && count($api_entries) < 1000) {
                $cursor = $result->getCursor();
                $result = App::instance()->get_sdk_client()->search_continue($cursor);
                $api_entries = array_merge($api_entries, $result->getItems()->toArray());
            }
        } catch (\Exception $ex) {
            Helpers::log_error('', 'API', null, __LINE__, $ex);

            return [];
        }

        $found_entries = [];
        foreach ($api_entries as $search_result) {
            $entry = new Entry($search_result->getMetadata());

            $relative_path = Processor::instance()->get_relative_path($entry->get_path());
            $entry->set_path($relative_path);
            $relative_path_display = Processor::instance()->get_relative_path($entry->get_path_display());
            $entry->set_path_display($relative_path_display);
            $found_entries[$entry->get_id()] = $entry;
        }

        if (false === apply_filters('outofthebox_use_search_order', true)) {
            $found_entries = Processor::instance()->sort_filelist($found_entries);
        }

        Cache::instance()->update_cache();

        do_action('outofthebox_api_after_search', $found_entries);

        return $found_entries;
    }

    /**
     * Upload a file to the cloud using a simple file object.
     *
     * @param stdClass    $file         Object containing the file details. Same as file object in $_FILES.
     *                                  <code>
     *                                  $file = object {
     *                                  'name' : 'filename.ext',
     *                                  'type' : 'image/jpeg',
     *                                  'tmp_name'=> '...\php8D2C.tmp
     *                                  'size' => 1274994
     *                                  }
     *                                  </code>
     * @param string      $dropbox_path The upload PATH on DROPBOX containing the filename /path/to/folder/filename.ext
     * @param null|string $description  Add a description to the file
     *                                  NOT SUPPORTED BY DROPBOX
     * @param array       $params       E.g. [{'mode' => 'add', 'autorename' => true}]
     *
     * @return Entry
     */
    public static function upload_file($file, $dropbox_path, $description = null, $params = [])
    {
        $default_params = ['mode' => 'add', 'autorename' => true];

        $params = array_merge($default_params, $params);

        $dropbox_path = apply_filters('outofthebox_api_upload_set_path', $dropbox_path);
        $file = apply_filters('outofthebox_api_upload_set_file', $file);
        $description = apply_filters('outofthebox_api_upload_set_description', $description);
        $params = apply_filters('outofthebox_api_upload_set_params', $params);

        do_action('outofthebox_api_before_upload', $dropbox_path, $file, $description, $params);

        if (!isset($file->tmp_name) && isset($file->tmp_path)) {
            $file->tmp_name = $file->tmp_path;
        }

        // Do the actual upload
        try {
            $api_result = App::instance()->get_sdk_client()->upload($file->tmp_name, Helpers::clean_folder_path($dropbox_path), $params);
            $entry = new Entry($api_result);
        } catch (\Exception $ex) {
            Cache::instance()->reset_cache();
            Helpers::log_error('Cannot upload file to the cloud.', 'API', ['entry_name' => $file->tmp_name], __LINE__, $ex);

            return false;
        }

        do_action('outofthebox_log_event', 'outofthebox_uploaded_entry', $entry);

        do_action('outofthebox_api_after_upload', $entry);

        return $entry;
    }

    /**
     * Get a one-time use temporary upload link to upload a file to a Dropbox location.
     *
     * @param stdClass    $file         Object containing the file details. Same as file object in $_FILES.
     *                                  <code>
     *                                  $file = object {
     *                                  'name' : 'filename.ext',
     *                                  'size' => 1274994
     *                                  }
     *                                  </code>
     * @param string      $origin       JS: window.location.origin*
     * @param null|string $description  Add a description to the file
     *                                  NOT SUPPORTED BY DROPBOX
     * @param array       $params       E.g. [{'mode': 'add', 'autorename': true}]
     * @param mixed       $dropbox_path
     *
     * @return array {'url': 'https://content.dropboxapi.com/....', 'path': '/path/to/file.ext'}
     */
    public static function upload_get_temporarily_url($file, $dropbox_path, $origin, $description = null, $params = [])
    {
        $default_params = ['mode' => 'add', 'autorename' => true];

        $params = array_merge($default_params, $params);

        $dropbox_path = apply_filters('outofthebox_api_upload_set_path', $dropbox_path);
        $file = apply_filters('outofthebox_api_upload_set_file', $file);
        $description = apply_filters('outofthebox_api_upload_set_description', $description);
        $params = apply_filters('outofthebox_api_upload_set_params', $params);

        $new_folder_path = Helpers::clean_folder_path($dropbox_path.'/'.$file->name);

        // Files larger than 300MB cannot be uploaded directly to Dropbox :(
        if ($file->size > 314572800) {
            Helpers::log_error('Could not create a temporarily upload url as the file is larger than this endpoint supports.', 'API', ['entry_name' => $file->name], __LINE__);

            return ['url' => false, 'path' => $new_folder_path];
        }

        do_action('outofthebox_api_before_upload', $dropbox_path, $file, $description, $params);

        // Do the actual upload
        try {
            $temporarily_link = App::instance()->get_sdk_client()->getTemporarilyUploadLink($new_folder_path, $params, $origin);
        } catch (\Exception $ex) {
            Helpers::log_error('Could not create a temporarily url', 'API', ['entry_name' => $file->name], __LINE__);

            return ['url' => false, 'path' => $new_folder_path];
        }

        return ['url' => $temporarily_link->getLink(), 'path' => $new_folder_path];
    }

    /**
     * Get a shortened url via the requested service.
     *
     * @param string $url
     * @param string $service
     * @param array  $params  Add extra data that can be used for certain services, e.g. ['name' => $node->get_name()]
     *
     * @return API_Exception|string The shortened url
     */
    public static function shorten_url($url, $service = null, $params = [])
    {
        if (empty($service)) {
            $service = Settings::get('shortlinks');
        }

        $service = apply_filters('outofthebox_api_shorten_url_set_service', $service);

        do_action('outofthebox_api_before_shorten_url', $url, $service, $params);

        if (false !== strpos($url, 'localhost')) {
            // Most APIs don't support localhosts
            return $url;
        }

        try {
            switch ($service) {
                case 'Bit.ly':
                    $response = wp_remote_post('https://api-ssl.bitly.com/v4/shorten', [
                        'body' => json_encode(
                            [
                                'long_url' => $url,
                            ]
                        ),
                        'headers' => [
                            'Authorization' => 'Bearer '.Settings::get('bitly_apikey'),
                            'Content-Type' => 'application/json',
                        ],
                    ]);

                    $data = json_decode($response['body'], true);

                    return $data['link'];

                case 'Shorte.st':
                    $response = wp_remote_get('https://api.shorte.st/s/'.Settings::get('shortest_apikey').'/'.$url);

                    $data = json_decode($response['body'], true);

                    return $data['shortenedUrl'];

                case 'Tinyurl':
                    $response = wp_remote_post('https://api.tinyurl.com/create?api_token='.Settings::get('tinyurl_apikey'), [
                        'body' => json_encode(
                            [
                                'url' => $url,
                                'domain' => Settings::get('tinyurl_domain'),
                            ]
                        ),
                        'headers' => [
                            'Content-Type' => 'application/json',
                        ],
                    ]);

                    $data = json_decode($response['body'], true);

                    return (!empty($data['errors'])) ? htmlspecialchars(reset($data['errors']), ENT_QUOTES) : $data['data']['tiny_url'];

                case 'Rebrandly':
                    $response = wp_remote_post('https://api.rebrandly.com/v1/links', [
                        'body' => json_encode(
                            [
                                'title' => isset($params['name']) ? $params['name'] : '',
                                'destination' => $url,
                                'domain' => ['fullName' => Settings::get('rebrandly_domain')],
                            ]
                        ),
                        'headers' => [
                            'apikey' => Settings::get('rebrandly_apikey'),
                            'Content-Type' => 'application/json',
                            'workspace' => Settings::get('rebrandly_workspace'),
                        ],
                    ]);

                    $data = json_decode($response['body'], true);

                    return 'https://'.$data['shortUrl'];

                case 'None':
                default:
                    break;
            }
        } catch (\Exception $ex) {
            Helpers::log_error('', 'API', null, __LINE__, $ex);

            return $url;
        }

        $url = apply_filters('outofthebox_api_'.$service.'_shorten_url', $url, $params);
        $shortened_url = apply_filters('outofthebox_api_shorten_url_set_shortened_url', $url, $params);

        do_action('outofthebox_api_after_shorten_url', $shortened_url);

        return $shortened_url;
    }
}

/**
 * API_Exception Class.
 *
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since 2.0
 * @see https://www.wpcloudplugins.com
 */
class API_Exception extends \Exception {}
