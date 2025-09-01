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

class UserFolders
{
    /**
     * The single instance of the class.
     *
     * @var UserFolders
     */
    protected static $_instance;

    /**
     * @var \stdClass|\WP_User
     */
    private $_current_user;

    /**
     * @var string
     */
    private $_user_name_template;
    private $_user_folder_name;

    /**
     * @var \WP_User[]
     */
    private $_custom_user_metadata = [];

    public function __construct()
    {
        $this->_user_name_template = Settings::get('userfolder_name');

        $shortcode = Processor::instance()->get_shortcode();
        if (!empty($shortcode) && !empty($shortcode['user_folder_name_template'])) {
            $this->_user_name_template = $shortcode['user_folder_name_template'];
        }
    }

    /**
     * Check if the module is using dynamic folders.
     *
     * @return bool
     */
    public static function is_using_dynamic_folders()
    {
        if ('auto' === Processor::instance()->get_shortcode_option('userfolders')) {
            return true;
        }

        return false;
    }

    /**
     * Get the current user processed for the Personal Folders.
     *
     * @return \stdClass|\WP_User
     */
    public function get_current_user()
    {
        if (null === $this->_current_user) {
            if (is_user_logged_in()) {
                $this->_current_user = wp_get_current_user();
            } else {
                $username = $this->get_guest_id();

                $user = new \stdClass();
                $user->user_login = $username;
                $user->display_name = $username;
                $user->ID = $username;
                $user->user_role = esc_html__('Anonymous user', 'wpcloudplugins');
                $this->_current_user = $user;
            }
        }

        return $this->_current_user;
    }

    /**
     * UserFolders Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return UserFolders - UserFolders instance
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

    public static function user_register($user_id, $force = false)
    {
        if ('Yes' !== Settings::get('userfolder_oncreation') && false === $force) {
            return;
        }

        self::instance()->_current_user = get_user_by('id', $user_id);

        foreach (Accounts::instance()->list_accounts() as $account) {
            if (false === $account->get_authorization()->has_access_token()) {
                continue;
            }

            App::set_current_account($account);
            self::instance()->create_user_folders_for_shortcodes();
        }
    }

    /**
     * Temporarily store old user meta data when user data is updated.
     * This makes old meta data available in user_profile_update function.
     *
     * @param array    $data
     * @param bool     $update
     * @param null|int $user_id
     * @param array    $userdata
     */
    public static function store_custom_user_metadata($data, $update, $user_id, $userdata)
    {
        if (empty($update) || empty($user_id)) {
            return $data;
        }

        $old_user = new \WP_User($user_id);

        self::instance()->_custom_user_metadata[$user_id] = get_user_meta($user_id);
        clean_user_cache($old_user);

        return $data;
    }

    public static function user_profile_update($user_id, $custom_user_metadata = false, $force = false)
    {
        if ('Yes' !== Settings::get('userfolder_update') && false === $force) {
            return;
        }

        self::instance()->_current_user = get_user_by('id', $user_id);

        foreach (Accounts::instance()->list_accounts() as $account) {
            if (false === $account->get_authorization()->has_access_token()) {
                continue;
            }

            App::set_current_account($account);
            self::instance()->update_user_folder($custom_user_metadata);
        }
    }

    public static function user_delete($user_id)
    {
        if ('Yes' !== Settings::get('userfolder_remove')) {
            return;
        }

        self::instance()->_current_user = get_user_by('id', $user_id);

        foreach (Accounts::instance()->list_accounts() as $account) {
            if (false === $account->get_authorization()->has_access_token()) {
                continue;
            }

            App::set_current_account($account);
            self::instance()->remove_user_folder();
        }
    }

    public function get_auto_linked_folder_name_for_user()
    {
        if ('auto' !== Processor::instance()->get_shortcode_option('userfolders')) {
            return false;
        }

        if (!empty($this->_user_folder_name)) {
            return $this->_user_folder_name;
        }

        $this->_user_folder_name = is_user_logged_in() ? $this->get_user_name_template() : $this->get_guest_user_name();

        return $this->_user_folder_name;
    }

    public function get_auto_linked_folder_for_user()
    {
        // Add folder if needed
        $result = $this->create_user_folder($this->get_auto_linked_folder_name_for_user(), Processor::instance()->get_shortcode(), 5000000);

        if (false === $result) {
            exit;
        }

        return $result->get_path();
    }

    /**
     * Convert old manually linked folders value to new format.
     * The new value is an array of personal folders.
     *
     * @param array|bool $value
     */
    public static function convert_old_manually_linked_folders_value($value)
    {
        if (!is_array($value)) {
            return $value; // No folders linked
        }
        if (isset($value['folderid'])) {
            return ['personal-folder-'.md5($value['accountid'].$value['folderid']) => $value]; // Return an array of folders for older values
        }

        return $value; // Value is up to date
    }

    /**
     * Get manually linked personal folder for user.
     *
     * @param null|int $user_id
     *
     * @return string
     */
    public static function get_manually_linked_folder_for_user($user_id = null)
    {
        $personal_folders = $user_id ? get_user_option('out_of_the_box_linkedto', $user_id) : get_site_option('out_of_the_box_guestlinkedto');

        if (is_array($personal_folders) && !empty($personal_folders)) {
            $personal_folder = reset($personal_folders);
            $linked_account = isset($personal_folder['accountid']) ? Accounts::instance()->get_account_by_id($personal_folder['accountid']) : Accounts::instance()->get_primary_account();

            App::set_current_account($linked_account);

            // Untill multiple folders are supported, just return the first folder
            return $personal_folder['folderid'];
        }
        if (null !== $user_id) {
            // User does not have a folder associated with his account, load folder for guest users
            return self::get_manually_linked_folder_for_user();
        }

        exit(-1);
    }

    /**
     * Manually link a folder to a user.
     *
     * @param int|string $user_id
     * @param array      $linkedto
     */
    public function manually_link_folder($user_id, $linkedto)
    {
        // Set the current account by ID
        App::set_current_account_by_id($linkedto['accountid']);

        // Get the folder node
        $node = Client::instance()->get_folder($linkedto['folderid'], false);
        $linkedto['foldertext'] = $node->get_name();
        $personal_folder_key = 'personal-folder-'.md5($linkedto['accountid'].$linkedto['folderid']);

        // Handle linking for both guest and registered users
        $option_name = ('GUEST' === $user_id) ? 'out_of_the_box_guestlinkedto' : 'out_of_the_box_linkedto';
        $personal_folders = ('GUEST' === $user_id) ? get_site_option($option_name) : get_user_option($option_name, $user_id);

        $personal_folders = [$personal_folder_key => $linkedto];

        // ADD: When supporting multiple folders
        // if (!is_array($personal_folders)) {
        //     $personal_folders = [];
        // }

        // $personal_folders[$personal_folder_key] = $linkedto;
        // END ADD

        if ('GUEST' === $user_id) {
            update_site_option($option_name, $personal_folders);
        } else {
            update_user_option($user_id, $option_name, $personal_folders, false);
        }

        // Return the updated personal folders as JSON
        echo json_encode($personal_folders);

        exit;
    }

    /**
     * Manually unlink a folder from a user.
     *
     * @param int|string $user_id
     * @param string     $personal_folder_key
     */
    public function manually_unlink_folder($user_id, $personal_folder_key)
    {
        $option_name = ('GUEST' === $user_id) ? 'out_of_the_box_guestlinkedto' : 'out_of_the_box_linkedto';
        $personal_folders = ('GUEST' === $user_id) ? get_site_option($option_name) : get_user_option($option_name, $user_id);

        if (!is_array($personal_folders)) {
            exit('-1');
        }

        unset($personal_folders[$personal_folder_key]);

        if (empty($personal_folders)) {
            $result = ('GUEST' === $user_id) ? delete_site_option($option_name) : delete_user_option($user_id, $option_name, false);
        } else {
            $result = ('GUEST' === $user_id) ? update_site_option($option_name, $personal_folders) : update_user_option($user_id, $option_name, $personal_folders, false);
        }

        if (false !== $result) {
            exit('1');
        }
    }

    public function create_user_folder($userfoldername, $shortcode, $mswaitaftercreation = 0)
    {
        if (false !== strpos($shortcode['root'], '%user_folder%')) {
            $userfolder_path = Helpers::clean_folder_path(str_replace('%user_folder%', $userfoldername, $shortcode['root']));
        } else {
            $userfolder_path = Helpers::clean_folder_path($shortcode['root'].'/'.$userfoldername);
        }

        try {
            $api_entry = App::instance()->get_sdk_client()->getMetadata($userfolder_path);

            return new Entry($api_entry);
        } catch (\Exception $ex) {
            // Folder doesn't exists, so continue
        }

        $user_template_path = $shortcode['user_template_dir'];

        try {
            if (empty($user_template_path)) {
                $api_entry_new = App::instance()->get_sdk_client()->createFolder($userfolder_path);
            } else {
                $api_entry_new = App::instance()->get_sdk_client()->copy($user_template_path, $userfolder_path);

                // New Meta data isn't fully available directly after copy command
                usleep($mswaitaftercreation);
            }
        } catch (\Exception $ex) {
            Helpers::log_error('Failed to add user folder.', 'Dynamic Folders', ['entry_path' => $userfolder_path], __LINE__);

            return false;
        }

        $user_folder = new Entry($api_entry_new);
        do_action('outofthebox_log_event', 'outofthebox_created_entry', $user_folder);

        do_action('outofthebox_dynamic_folder_created', $user_folder, $shortcode);

        // Create a shared link to the folder if needed
        if ('Yes' === Settings::get('userfolder_oncreation_share')) {
            $this->share_personal_folder($user_folder);
        }

        return $user_folder;
    }

    public function create_user_folders_for_shortcodes()
    {
        $outoftheboxlists = Shortcodes::instance()->get_all_shortcodes();
        $current_account = App::get_current_account();

        foreach ($outoftheboxlists as $list) {
            if (!isset($list['userfolders']) || 'auto' !== $list['userfolders']) {
                continue;
            }

            if (!isset($list['account']) || $current_account->get_id() !== $list['account']) {
                continue; // Skip shortcodes that don't belong to the account that is being processed
            }

            if (false === Helpers::check_user_role($list['view_role'], $this->get_current_user())) {
                continue; // Skip shortcodes that aren't accessible for user
            }

            if (false !== strpos($list['class'], 'disable-create-personal-folder-on-registration')) {
                continue; // Skip shortcodes that explicitly have set to skip automatic folder creation
            }

            if (!empty($list['user_folder_name_template'])) {
                $this->_user_name_template = $list['user_folder_name_template'];
            } else {
                $this->_user_name_template = Settings::get('userfolder_name');
            }

            if (false === strpos($this->_user_name_template, '%')) {
                continue; // Skip shortcodes that don't have a dynamic folder template
            }

            $new_userfoldersname = $this->get_user_name_template();

            $this->create_user_folder($new_userfoldersname, $list);
        }
    }

    public function create_user_folders($users = [])
    {
        if (0 === count($users)) {
            return;
        }

        foreach ($users as $user) {
            $this->_current_user = $user;
            $userfoldersname = $this->get_user_name_template();

            $this->create_user_folder($userfoldersname, Processor::instance()->get_shortcode());
        }
    }

    public function remove_user_folder()
    {
        $outoftheboxlists = Shortcodes::instance()->get_all_shortcodes();
        $current_account = App::get_current_account();

        foreach ($outoftheboxlists as $list) {
            if (!isset($list['userfolders']) || 'auto' !== $list['userfolders']) {
                continue;
            }

            if (!isset($list['account']) || $current_account->get_id() !== $list['account']) {
                continue; // Skip shortcodes that don't belong to the account that is being processed
            }

            if (false === Helpers::check_user_role($list['view_role'], $this->get_current_user())) {
                continue; // Skip shortcodes that aren't accessible for user
            }

            if (!empty($list['user_folder_name_template'])) {
                $this->_user_name_template = $list['user_folder_name_template'];
            } else {
                $this->_user_name_template = Settings::get('userfolder_name');
            }

            if (false === strpos($this->_user_name_template, '%')) {
                continue; // Skip shortcodes that don't have a dynamic folder template
            }

            $userfoldername = $this->get_user_name_template();

            if (false !== strpos($list['root'], '%user_folder%')) {
                $userfolder_path = Helpers::clean_folder_path(str_replace('%user_folder%', $userfoldername, $list['root']));
            } else {
                $userfolder_path = Helpers::clean_folder_path($list['root'].'/'.$userfoldername);
            }

            try {
                App::instance()->get_sdk_client()->delete($userfolder_path);
            } catch (\Exception $ex) {
                return false;
            }
        }

        return true;
    }

    public function update_user_folder($old_user)
    {
        $outoftheboxlists = Shortcodes::instance()->get_all_shortcodes();
        $current_account = App::get_current_account();

        foreach ($outoftheboxlists as $list) {
            if (!isset($list['userfolders']) || 'auto' !== $list['userfolders']) {
                continue;
            }

            if (!isset($list['account']) || $current_account->get_id() !== $list['account']) {
                continue; // Skip shortcodes that don't belong to the account that is being processed
            }

            if (false === Helpers::check_user_role($list['view_role'], $this->get_current_user())) {
                continue; // Skip shortcodes that aren't accessible for user
            }

            if (!empty($list['user_folder_name_template'])) {
                $this->_user_name_template = $list['user_folder_name_template'];
            } else {
                $this->_user_name_template = Settings::get('userfolder_name');
            }

            if (false === strpos($this->_user_name_template, '%')) {
                continue; // Skip shortcodes that don't have a dynamic folder template
            }

            $new_userfoldersname = $this->get_user_name_template();
            $old_userfoldersname = $this->get_user_name_template($old_user, ['custom_user_metadata' => $this->_custom_user_metadata[$old_user->ID] ?? null]);

            if ($new_userfoldersname === $old_userfoldersname) {
                continue;
            }

            if (defined('out_of_the_box_update_user_folder_'.$list['root'].'_'.$new_userfoldersname)) {
                continue;
            }

            define('out_of_the_box_update_user_folder_'.$list['root'].'_'.$new_userfoldersname, true);

            if (false !== strpos($list['root'], '%user_folder%')) {
                $new_userfolder_path = Helpers::clean_folder_path(str_replace('%user_folder%', $new_userfoldersname, $list['root']));
                $old_userfolder_path = Helpers::clean_folder_path(str_replace('%user_folder%', $old_userfoldersname, $list['root']));
            } else {
                $new_userfolder_path = Helpers::clean_folder_path($list['root'].'/'.$new_userfoldersname);
                $old_userfolder_path = Helpers::clean_folder_path($list['root'].'/'.$old_userfoldersname);
            }

            try {
                App::instance()->get_sdk_client()->move($old_userfolder_path, $new_userfolder_path);
            } catch (\Exception $ex) {
                return false;
            }
        }

        return true;
    }

    public function get_user_name_template($user = null, $placeholder_params = [])
    {
        if (null === $user) {
            $user = $this->get_current_user();
        }

        $placeholder_params['user_data'] = $user;

        $user_folder_name = Placeholders::apply($this->_user_name_template, Processor::instance(), $placeholder_params);

        return apply_filters('outofthebox_private_folder_name', $user_folder_name, Processor::instance());
    }

    public function get_guest_user_name()
    {
        $user_folder_name = $this->get_user_name_template();

        if (empty($user_folder_name)) {
            $user_folder_name = $this->get_guest_id();
        }

        $prefix = Settings::get('userfolder_name_guest_prefix');

        return apply_filters('outofthebox_private_folder_name_guests', $prefix.$user_folder_name, Processor::instance());
    }

    public function share_personal_folder($entry)
    {
        // Not implemented yet
    }

    public static function get_guest_id()
    {
        if (!isset($_COOKIE['WPCP_UUID'])) {
            Helpers::log_error('No UUID found.', 'Dynamic Folders', null, __LINE__);

            exit;
        }

        return $_COOKIE['WPCP_UUID'];
    }
}
