<?php

/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

use TheLion\OutoftheBox\Modules\Module;

defined('ABSPATH') || exit;

class Processor
{
    public $options = [];
    public $mobile = false;
    public $settings = [];

    /**
     * The single instance of the class.
     *
     * @var Processor
     */
    protected static $_instance;
    protected $listtoken = '';
    protected $_requestedFile;
    protected $_requestedDir;
    protected $_requestedPath;
    protected $_requestedCompletePath;
    protected $_lastPath = '/';
    protected $_rootFolder = '';

    /**
     * Construct the plugin object.
     */
    public function __construct()
    {
        register_shutdown_function([static::class, 'do_shutdown']);

        if (isset($_REQUEST['mobile']) && ('true' === $_REQUEST['mobile'])) {
            $this->mobile = true;
        }

        // If the user wants a hard refresh, set this globally
        if (isset($_REQUEST['hardrefresh']) && 'true' === $_REQUEST['hardrefresh'] && (!defined('FORCE_REFRESH'))) {
            define('FORCE_REFRESH', true);
        }
    }

    /**
     * Processor Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return Processor - Processor instance
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

    public function start_process()
    {
        if (!isset($_REQUEST['action'])) {
            Helpers::log_error('Action is missing from request', 'Processor', null, __LINE__);

            \http_response_code(400);

            exit;
        }

        $requested_action = $_REQUEST['action'];

        if (isset($_REQUEST['account_id'])) {
            $requested_account_id = $_REQUEST['account_id'];
            $requested_account = Accounts::instance()->get_account_by_id($requested_account_id);
            if (null !== $requested_account) {
                App::set_current_account($requested_account);
            } else {
                Helpers::log_error('Cannot use the requested account as it is not linked with the plugin', 'Processor', ['account_id' => sanitize_key($requested_account_id)], __LINE__);

                exit;
            }
        }

        do_action('outofthebox_before_start_process', $_REQUEST['action'], $this);

        $authorized = AjaxRequest::is_action_authorized();

        if ((true === $authorized) && ('outofthebox-revoke' === $_REQUEST['action'])) {
            $data = ['account_id' => App::get_current_account()->get_uuid(), 'action' => 'revoke', 'success' => false];
            if (Helpers::check_user_role(Settings::get('permissions_edit_settings'))) {
                if (null === App::get_current_account()) {
                    echo json_encode($data);

                    exit;
                }

                if ('true' === $_REQUEST['force']) {
                    Accounts::instance()->remove_account(App::get_current_account()->get_uuid());
                } else {
                    App::instance()->revoke_token(App::get_current_account());
                }

                $data['success'] = true;
            }

            echo json_encode($data);

            exit;
        }

        if (!isset($_REQUEST['listtoken'])) {
            Helpers::log_error('Token is missing from request', 'Processor', null, __LINE__);

            exit;
        }

        $this->listtoken = sanitize_key($_REQUEST['listtoken']);
        $this->options = Shortcodes::instance()->get_shortcode_by_id($this->listtoken);

        if (false === $this->options) {
            Helpers::log_error('Token is invalid', 'Processor', ['action' => sanitize_key($requested_action)], __LINE__);

            exit;
        }

        if (false === User::can_view() || !apply_filters('outofthebox_module_is_visible', true)) {
            Helpers::log_error('User does not have the permission to view the plugin', 'Processor', null, __LINE__);

            \http_response_code(401);

            exit;
        }

        if ('outofthebox-module-login' !== $_REQUEST['action'] && !Restrictions::unlock_module()) {
            Helpers::log_error('Process not started as the user has not unlocked the module', 'Processor', null, __LINE__);

            \http_response_code(401);

            exit;
        }

        if (!in_array($_REQUEST['action'], ['outofthebox-module-login', 'outofthebox-module-lead']) && !LeadCapture::unlock_module()) {
            Helpers::log_error('Process not started as the user has not entered lead information', 'Processor', null, __LINE__);

            \http_response_code(401);

            exit;
        }

        if (null === App::get_current_account() || false === App::get_current_account()->get_authorization()->has_access_token()) {
            Helpers::log_error('Account is not linked to the plugin', 'Processor', null, __LINE__);

            return new \WP_Error('broke', '<strong>'.sprintf(esc_html__('%s needs your help!', 'wpcloudplugins'), 'Out-of-the-Box').'</strong> '.esc_html__('Connect your account!', 'wpcloudplugins'));
        }

        Client::instance();

        // Set rootFolder
        if ('manual' === $this->options['userfolders']) {
            $manual_user_id = apply_filters('outofthebox_set_user_id_for_manual_personal_folder', get_current_user_id(), $this);
            $this->_rootFolder = UserFolders::instance()->get_manually_linked_folder_for_user($manual_user_id);
        } elseif (('auto' === $this->options['userfolders']) && !Helpers::check_user_role($this->options['view_user_folders_role'])) {
            $this->_rootFolder = UserFolders::instance()->get_auto_linked_folder_for_user();
        } elseif ('auto' === $this->options['userfolders']) {
            $this->_rootFolder = str_replace('/%user_folder%', '', $this->options['root']);
        } else {
            $this->_rootFolder = $this->options['root'];
        }

        // Open Sub Folder if needed
        if (!empty($this->options['subfolder']) && '/' !== $this->options['subfolder']) {
            $sub_folder_path = apply_filters('outofthebox_set_subfolder_path', Placeholders::apply($this->options['subfolder'], $this), $this->options, $this);
            $subfolder = API::get_sub_folder_by_path($this->_rootFolder, $sub_folder_path, true);

            if (is_wp_error($subfolder) || false === $subfolder) {
                Helpers::log_error('The subfolder cannot be found or cannot be created.', 'Processor', null, __LINE__);

                exit('-1');
            }
            $this->_rootFolder = $subfolder->get_path();
        }

        $this->_rootFolder = html_entity_decode($this->_rootFolder);
        $this->_rootFolder = str_replace('//', '/', $this->_rootFolder);

        if (isset($_REQUEST['lastpath'])) {
            $this->_lastPath = stripslashes(rawurldecode($_REQUEST['lastpath']));
        }

        if (isset($_REQUEST['OutoftheBoxpath']) && '' != $_REQUEST['OutoftheBoxpath']) {
            $path = stripslashes(rawurldecode($_REQUEST['OutoftheBoxpath']));
            $this->_set_requested_path($path);
        } else {
            $this->_set_requested_path();
        }

        $this->set_last_path($this->get_requested_path());

        // Check if the request is cached
        if (defined('FORCE_REFRESH')) {
            CacheRequest::clear_request_cache();

            if (\function_exists('wp_cache_supports') && \wp_cache_supports('flush_group')) {
                \wp_cache_flush_group('wpcp-'.CORE::$slug.'-entries');
            }
        }

        if (in_array($_REQUEST['action'], ['outofthebox-get-filelist', 'outofthebox-get-gallery', 'outofthebox-get-playlist'])) {
            // And Set GZIP compression if possible
            $this->_set_gzip_compression();

            if (!defined('FORCE_REFRESH')) {
                $cached_request = new CacheRequest();
                if ($cached_request->is_cached()) {
                    echo $cached_request->get_cached_response();

                    exit;
                }
            }
        }

        do_action('outofthebox_start_process', $_REQUEST['action'], $this);

        switch ($_REQUEST['action']) {
            case 'outofthebox-get-filelist':
                if ('proofing' === $this->get_shortcode_option('mode')) {
                    $browser = new Modules\Proofing();
                } else {
                    $browser = new Modules\Filebrowser();
                }

                if (isset($_REQUEST['query']) && !empty($_REQUEST['query']) && '1' === $this->options['search']) { // Search files
                    $browser->search_files();
                } else {
                    $browser->get_files_list(); // Read folder
                }

                break;

            case 'outofthebox-preview':
                Client::instance()->preview_entry();

                break;

            case 'outofthebox-download':
                if (false === User::can_download()) {
                    exit;
                }

                Client::instance()->download_entry();

                exit;

            case 'outofthebox-create-zip':
                if (false === User::can_download()) {
                    exit;
                }

                $request_id = $_REQUEST['request_id'];

                switch ($_REQUEST['type']) {
                    case 'do-zip':
                        $zip = new Zip($request_id);
                        $zip->do_zip();

                        break;

                    case 'get-progress':
                        Zip::get_status($request_id);

                        break;

                    default:
                        exit;
                }

                break;

            case 'outofthebox-create-link':
            case 'outofthebox-embedded':
                $link = [];

                if (isset($_REQUEST['entries'])) {
                    foreach ($_REQUEST['entries'] as $entry_id) {
                        $entry = stripslashes(rawurldecode($entry_id));
                        $link['links'][] = Client::instance()->get_shared_link_for_output($entry);
                    }
                } else {
                    $link = Client::instance()->get_shared_link_for_output();
                }
                echo json_encode($link);

                exit;

            case 'outofthebox-get-gallery':
                if (is_wp_error($authorized)) {
                    echo json_encode(['lastpath' => $this->_lastPath, 'folder' => '', 'html' => '']);

                    exit;
                }

                if ('carousel' === $_REQUEST['type']) {
                    $carousel = new Modules\Carousel($this);
                    $carousel->get_images_list();
                } else {
                    $gallery = new Modules\Gallery();

                    if (isset($_REQUEST['query']) && !empty($_REQUEST['query']) && '1' === $this->options['search']) { // Search files
                        $gallery->search_image_files();
                    } else {
                        $gallery->get_images_list(); // Read folder
                    }
                }

                break;

            case 'outofthebox-upload-file':
                $user_can_upload = User::can_upload();

                if (is_wp_error($authorized) || false === $user_can_upload) {
                    exit;
                }

                $upload_processor = new Upload();

                switch ($_REQUEST['type']) {
                    case 'upload-preprocess':
                        $upload_processor->upload_pre_process();

                        break;

                    case 'do-upload':
                        $upload_processor->do_upload();

                        break;

                    case 'get-status':
                        $upload_processor->get_upload_status();

                        break;

                    case 'get-direct-url':
                        $upload_processor->do_upload_direct();

                        break;

                    case 'upload-convert':
                        $upload_processor->upload_convert();

                        break;

                    case 'upload-postprocess':
                        $upload_processor->upload_post_process();

                        break;

                    default:
                        break;
                }

                exit;

            case 'outofthebox-delete-entries':
                // Check if user is allowed to delete entry
                $user_can_delete = User::can_delete_files() || User::can_delete_folders();

                if (is_wp_error($authorized) || false === $user_can_delete || !isset($_REQUEST['entries'])) {
                    echo json_encode(['result' => '-1', 'msg' => esc_html__('Failed to delete file.', 'wpcloudplugins')]);

                    exit;
                }

                $entries_to_delete = $_REQUEST['entries'];
                $entries = Client::instance()->delete_entries($entries_to_delete);

                foreach ($entries as $entry) {
                    if (false === $entry) {
                        echo json_encode(['result' => '-1', 'msg' => esc_html__('Not all files could be deleted.', 'wpcloudplugins')]);

                        exit;
                    }
                }
                echo json_encode(['result' => '1', 'msg' => esc_html__('File was deleted.', 'wpcloudplugins')]);

                exit;

            case 'outofthebox-rename-entry':
                // Check if user is allowed to rename entry
                $user_can_rename = User::can_rename_files() || User::can_rename_folders();

                if (is_wp_error($authorized) || false === $user_can_rename) {
                    echo json_encode(['result' => '-1', 'msg' => esc_html__('Failed to rename file.', 'wpcloudplugins')]);

                    exit;
                }

                // Strip unsafe characters
                $newname = stripslashes(rawurldecode($_REQUEST['newname']));
                $new_filename = Helpers::filter_filename($newname, false);

                $file = Client::instance()->rename_entry($new_filename);

                if (is_wp_error($file)) {
                    echo json_encode(['result' => '-1', 'msg' => $file->get_error_message()]);
                } else {
                    echo json_encode(['result' => '1', 'msg' => esc_html__('File was renamed.', 'wpcloudplugins')]);
                }

                exit;

            case 'outofthebox-copy-entries':
                // Check if user is allowed to copy entry
                $user_can_copy = User::can_copy_files() || User::can_copy_folders();

                if (false === $user_can_copy) {
                    echo json_encode(['result' => '-1', 'msg' => esc_html__('Failed to copy.', 'wpcloudplugins')]);

                    exit;
                }

                $entries_to_copy = $_REQUEST['entries'];

                $target_path = str_replace('//', '/', $this->_rootFolder.'/'.rawurldecode($_REQUEST['target']));

                $entries = Client::instance()->move_entries($entries_to_copy, $target_path, true);

                foreach ($entries as $entry) {
                    if (is_wp_error($entry) || empty($entry)) {
                        echo json_encode(['result' => '-1', 'msg' => esc_html__('Not all files could be copied.', 'wpcloudplugins')]);

                        exit;
                    }
                }
                echo json_encode(['result' => '1', 'msg' => esc_html__('Successfully copied to new location', 'wpcloudplugins')]);

                exit;

            case 'outofthebox-move-entries':
                // Check if user is allowed to move entry
                $user_can_move = User::can_move_files() || User::can_move_folders();

                if (false === $user_can_move) {
                    echo json_encode(['result' => '-1', 'msg' => esc_html__('Failed to move file.', 'wpcloudplugins')]);

                    exit;
                }

                $entries_to_move = $_REQUEST['entries'];

                $target_path = str_replace('//', '/', $this->_rootFolder.'/'.rawurldecode($_REQUEST['target']));

                $entries = Client::instance()->move_entries($entries_to_move, $target_path);

                foreach ($entries as $entry) {
                    if (is_wp_error($entry) || empty($entry)) {
                        echo json_encode(['result' => '-1', 'msg' => esc_html__('Not all files could be moved.', 'wpcloudplugins')]);

                        exit;
                    }
                }
                echo json_encode(['result' => '1', 'msg' => esc_html__('Successfully moved to new location.', 'wpcloudplugins')]);

                exit;

            case 'outofthebox-create-entry':
                // Strip unsafe characters
                $_name = stripslashes(rawurldecode($_REQUEST['name']));
                $new_name = Helpers::filter_filename($_name, false);

                // Check if user is allowed
                $user_can_create_entry = User::can_add_folders();

                if (false === $user_can_create_entry) {
                    echo json_encode(['result' => '-1', 'msg' => esc_html__('Failed to add file.', 'wpcloudplugins')]);

                    exit;
                }

                $file = Client::instance()->add_folder($new_name);

                $this->set_last_path($this->_requestedPath.'/'.$file->get_name());

                if (is_wp_error($file)) {
                    echo json_encode(['result' => '-1', 'msg' => $file->get_error_message()]);
                } else {
                    $message = htmlspecialchars($file->get_name().' '.esc_html__('was added', 'wpcloudplugins'));
                    echo json_encode(['result' => '1', 'msg' => $message, 'lastpath' => rawurlencode($this->_lastPath ?? '')]);
                }

                exit;

            case 'outofthebox-proofing':
                Proofing::instance()->process_ajax_request();

                exit;

            case 'outofthebox-get-playlist':
                if (is_wp_error($authorized)) {
                    exit;
                }

                $mediaplayer = new Modules\Mediaplayer();
                $mediaplayer->get_media_list();

                break;

            case 'outofthebox-stream':
                Client::instance()->stream_entry();

                break;

            case 'outofthebox-shorten-url':
                if (false === User::can_deeplink()) {
                    exit;
                }

                $node = Client::instance()->get_entry();
                $url = esc_url_raw($_REQUEST['url']);

                $shortened_url = API::shorten_url($url, null, ['name' => $node->get_name()]);

                $data = [
                    'id' => $node->get_id(),
                    'name' => $node->get_name(),
                    'url' => $shortened_url,
                ];

                echo json_encode($data);

                exit;

            case 'outofthebox-event-log':
                return;

            case 'outofthebox-getads':
                $ads_url = ('' !== $this->get_shortcode_option('ads_tag_url') ? htmlspecialchars_decode($this->get_shortcode_option('ads_tag_url')) : Settings::get('mediaplayer_ads_tagurl'));
                $ads_id = md5($ads_url);

                $response_body = get_transient("wpcp-ads-{$ads_id}");

                if (false === $response_body) {
                    $options = [
                        'headers' => [
                            'user-agent' => 'WPCP '.OUTOFTHEBOX_VERSION,
                        ],
                    ];

                    $response = wp_remote_get($ads_url, $options);
                    if (!empty($response) && !\is_wp_error($response)) {
                        $response_body = wp_remote_retrieve_body($response);
                        set_transient("wpcp-ads-{$ads_id}", $response_body, MINUTE_IN_SECONDS);
                    }
                }

                header('Content-Type: text/xml; charset=UTF-8');
                echo $response_body;

                exit;

            case 'outofthebox-module-login':
                $password = $_POST['module_password'] ?? '';

                if (Restrictions::instance()->unlock_module(sanitize_text_field($password))) {
                    wp_send_json_success(Processor::instance()->get_shortcode_option('password_hash'));
                } else {
                    wp_send_json_error();
                }

                exit;

            case 'outofthebox-module-lead':
                $email = sanitize_email($_POST['email'] ?? '');

                if (LeadCapture::instance()->unlock_module(sanitize_text_field($email))) {
                    wp_send_json_success();
                } else {
                    wp_send_json_error();
                }

                exit;

            case 'outofthebox-import-entries':
                // Check if user is allowed to import
                $user_can_import = User::can_import();

                if (false === $user_can_import) {
                    echo json_encode(['result' => '-1', 'msg' => esc_html__('Failed to import file.', 'wpcloudplugins')]);

                    exit;
                }

                foreach ($_REQUEST['entries'] as $requested_id) {
                    $node = Client::instance()->get_entry($requested_id);

                    if (!empty($node)) {
                        Import::instance()->add_to_media_library($node);
                    }
                }

                exit;

            default:
                Helpers::log_error('Invalid AJAX request received.', 'Processor', ['action' => sanitize_key($requested_action)], __LINE__);

                exit;
        }
    }

    public function create_from_shortcode($atts)
    {
        $atts = (is_string($atts)) ? [] : $atts;
        $atts = $this->remove_deprecated_options($atts);

        $defaults = [
            'id' => '',
            'singleaccount' => '1',
            'account' => false,
            'startaccount' => false,
            'dir' => '/',
            'items' => '',
            'subfolder' => false,
            'class' => '',
            'module_id' => '',
            'startpath' => false,
            'mode' => 'files',
            'userfolders' => 'off',
            'usertemplatedir' => '',
            'viewuserfoldersrole' => 'administrator',
            'userfoldernametemplate' => '',
            'maxuserfoldersize' => '-1',
            'includeext' => '*',
            'excludeext' => '*',
            'showfiles' => '1',
            'showfolders' => '1',
            'maxfiles' => '-1',
            'filesize' => '1',
            'filedate' => '1',
            'fileinfo_on_hover' => '0',
            'hoverthumbs' => '1',
            'filelayout' => 'list',
            'allow_switch_view' => '1',
            'showext' => '1',
            'showroot' => '0',
            'sortfield' => 'name',
            'sortorder' => 'asc',
            'show_tree' => '0',
            'show_header' => '1',
            'showbreadcrumb' => '1',
            'candownloadzip' => '0',
            'canpopout' => '0',
            'lightbox_imagesource' => 'default',
            'lightboxnavigation' => '1',
            'lightboxthumbs' => '1',
            'showsharelink' => '0',
            'share_password' => '',
            'share_expire_after' => '',
            'share_allow_download' => '1',
            'showrefreshbutton' => '1',
            'use_custom_roottext' => '1',
            'roottext' => esc_html__('Start', 'wpcloudplugins'),
            'search' => '1',
            'searchrole' => 'all',
            'searchfrom' => 'parent',
            'searchterm' => '',
            'searchcontents' => '0',
            'include' => '*',
            'exclude' => '*',
            'showsystemfiles' => '0',
            'maxwidth' => '100%',
            'maxheight' => '',
            'scrolltotop' => '1',
            'viewrole' => 'administrator|editor|author|contributor|subscriber|guest',
            'display_loginscreen' => '0',
            'password' => '',
            'requires_lead' => '0',
            'onclick' => 'download',
            'previewrole' => 'all',
            'downloadrole' => 'administrator|editor|author|contributor|subscriber|guest',
            'sharerole' => 'all',
            'previewinline' => '1',
            'previewnewtab' => '1',
            'forcedownload' => '0',
            'crop' => '0',
            'lightbox_open' => '0',
            'slideshow' => '0',
            'pausetime' => '5000',
            'showfilenames' => '0',
            'show_descriptions' => '0',
            'showdescriptionsontop' => '0',
            'targetheight' => '250',
            'folderthumbs' => '1',
            'mediaskin' => '',
            'mediabuttons' => 'prevtrack|playpause|nexttrack|volume|current|duration|fullscreen',
            'media_ratio' => '16:9',
            'autoplay' => '0',
            'showplaylist' => '1',
            'showplaylistonstart' => '1',
            'playlistinline' => '0',
            'playlistautoplay' => '1',
            'playlistloop' => '0',
            'playlistthumbnails' => '1',
            'playlist_search' => '0',
            'linktoshop' => '',
            'ads' => '0',
            'ads_tag_url' => '',
            'ads_skipable' => '1',
            'ads_skipable_after' => '',
            'axis' => 'horizontal',
            'padding' => '',
            'border_radius' => '',
            'description_position' => 'hover',
            'navigation_dots' => '1',
            'navigation_arrows' => '1',
            'slide_items' => '3',
            'slide_height' => '300px',
            'slide_by' => '1',
            'slide_speed' => '300',
            'slide_center' => '0',
            'slide_auto_size' => '0',
            'carousel_autoplay' => '1',
            'pausetime' => '5000',
            'hoverpause' => '0',
            'direction' => 'forward',
            'notificationupload' => '0',
            'notificationdownload' => '0',
            'notificationdeletion' => '0',
            'notificationmove' => '0',
            'notificationcopy' => '0',
            'notificationemail' => '%admin_email%',
            'notification_skipemailcurrentuser' => '0',
            'notification_from_name' => '',
            'notification_from_email' => '',
            'notification_replyto_email' => '',
            'proofing_use_labels' => '',
            'proofing_max_items' => '0',
            'proofing_labels' => '',
            'upload' => '0',
            'upload_folder' => '1',
            'upload_auto_start' => '1',
            'upload_filename' => '%file_name%%file_extension%',
            'upload_create_shared_link' => '0',
            'upload_create_shared_link_folder' => '0',
            'upload_button_text' => '',
            'upload_button_text_plural' => '',
            'overwrite' => '0',
            'uploadext' => '.',
            'uploadrole' => 'administrator|editor|author|contributor|subscriber',
            'minfilesize' => '0',
            'maxfilesize' => '0',
            'maxnumberofuploads' => '-1',
            'delete' => '0',
            'deletefilesrole' => 'administrator|editor',
            'deletefoldersrole' => 'administrator|editor',
            'rename' => '0',
            'renamefilesrole' => 'administrator|editor',
            'renamefoldersrole' => 'administrator|editor',
            'move' => '0',
            'movefilesrole' => 'administrator|editor',
            'movefoldersrole' => 'administrator|editor',
            'copy' => '0',
            'copyfilesrole' => 'administrator|editor',
            'copyfoldersrole' => 'administrator|editor',
            'addfolder' => '0',
            'addfolderrole' => 'administrator|editor',
            'createdocument' => '0',
            'createdocumentrole' => 'administrator|editor',
            'import' => '0',
            'usage_period' => 'default',
            'download_limits' => '0',
            'downloads_per_user' => '',
            'downloads_per_user_per_file' => '',
            'zip_downloads_per_user' => '',
            'bandwidth_per_user' => '',
            'download_limits_excluded_roles' => '',
            'deeplink' => '0',
            'deeplinkrole' => 'all',
            'embed_ratio' => '1.414:1',
            'embed_type' => 'readonly',
            'embed_direct_media' => '0',
            'popup' => '0',
            'post_id' => empty($atts['popup']) ? get_the_ID() : null,
            'wc_order_id' => null,
            'wc_product_id' => null,
            'wc_item_id' => null,
            'themestyle' => 'default',
            'demo' => '0',
        ];

        // Read shortcode & Create a unique identifier
        $shortcode = shortcode_atts($defaults, $atts, 'outofthebox');
        $this->listtoken = md5(serialize($defaults).serialize($shortcode).OUTOFTHEBOX_AUTH_KEY);
        extract($shortcode);

        $cached_shortcode = Shortcodes::instance()->get_shortcode_by_id($this->listtoken);

        if (false === $cached_shortcode) {
            switch ($mode) {
                case 'gallery':
                    $includeext = ('*' == $includeext) ? 'gif|jpg|jpeg|png|bmp|webp|mp4|m4v|ogg|ogv|webmv' : $includeext;
                    $uploadext = ('.' == $uploadext) ? 'gif|jpg|jpeg|png|bmp|webp|mp4|m4v|ogg|ogv|webmv' : $uploadext;

                    break;

                case 'carousel':
                    $includeext = ('*' == $includeext) ? 'gif|jpg|jpeg|png|bmp|webp' : $includeext;

                    break;

                case 'search':
                    $searchfrom = 'root';

                    break;

                case 'list':
                case 'button':
                    $candownloadzip = '1';

                    break;

                case 'proofing':
                    $allow_switch_view = '0';
                    $requires_lead = '1';
                    $filelayout = 'grid';

                    break;

                default:
                    break;
            }

            if (!empty($account)) {
                $singleaccount = '1';
            }

            if ('0' === $singleaccount) {
                $dir = '/';
                $account = false;
            }

            if (empty($account)) {
                $primary_account = Accounts::instance()->get_primary_account();
                if (null !== $primary_account) {
                    $account = $primary_account->get_id();
                }
            }

            $account_class = Accounts::instance()->get_account_by_id($account);
            if (null === $account_class || false === $account_class->get_authorization()->is_valid()) {
                Helpers::log_error('Module cannot be rendered. The requested account is not associated with the plugin.', 'Processor', ['account_id' => $account], __LINE__);

                return '&#9888; <strong>'.esc_html__('Module cannot be rendered as the requested content is not (longer) accessible. Contact the administrator to get access.', 'wpcloudplugins').'</strong>';
            }

            App::set_current_account($account_class);

            $dir = rtrim($dir, '/');
            $dir = ('' == $dir) ? '/' : $dir;
            if ('/' !== substr($dir, 0, 1)) {
                $dir = '/'.$dir;
            }

            $dir = \str_replace(['%5B', '%5D'], ['[', ']'], $dir);

            if (false !== $subfolder) {
                $subfolder = Helpers::clean_folder_path('/'.rtrim($subfolder, '/'));
            }

            // Explode roles
            $viewrole = explode('|', $viewrole);
            $previewrole = explode('|', $previewrole);
            $downloadrole = explode('|', $downloadrole);
            $sharerole = explode('|', $sharerole);
            $uploadrole = explode('|', $uploadrole);
            $deletefilesrole = explode('|', $deletefilesrole);
            $deletefoldersrole = explode('|', $deletefoldersrole);
            $renamefilesrole = explode('|', $renamefilesrole);
            $renamefoldersrole = explode('|', $renamefoldersrole);
            $movefilesrole = explode('|', $movefilesrole);
            $movefoldersrole = explode('|', $movefoldersrole);
            $copyfilesrole = explode('|', $copyfilesrole);
            $copyfoldersrole = explode('|', $copyfoldersrole);
            $addfolderrole = explode('|', $addfolderrole);
            $createdocumentrole = explode('|', $createdocumentrole);
            $viewuserfoldersrole = explode('|', $viewuserfoldersrole);
            $deeplinkrole = explode('|', $deeplinkrole);
            $mediabuttons = explode('|', $mediabuttons);
            $searchrole = explode('|', $searchrole);
            $download_limits_excluded_roles = explode('|', $download_limits_excluded_roles);

            // Explode items
            if (!empty($items)) {
                $items = explode('|', $items);
            }

            $this->options = [
                'id' => $id,
                'single_account' => $singleaccount,
                'account' => $account,
                'startaccount' => $startaccount,
                'class' => $class,
                'module_id' => $module_id,
                'root' => htmlspecialchars_decode($dir),
                'items' => $items,
                'subfolder' => $subfolder,
                'startpath' => $startpath,
                'mode' => $mode,
                'userfolders' => $userfolders,
                'user_template_dir' => htmlspecialchars_decode($usertemplatedir),
                'view_user_folders_role' => $viewuserfoldersrole,
                'user_folder_name_template' => $userfoldernametemplate,
                'max_user_folder_size' => $maxuserfoldersize,
                'mediaskin' => $mediaskin,
                'mediabuttons' => $mediabuttons,
                'media_ratio' => $media_ratio,
                'autoplay' => $autoplay,
                'showplaylist' => $showplaylist,
                'showplaylistonstart' => $showplaylistonstart,
                'playlistinline' => $playlistinline,
                'playlistautoplay' => $playlistautoplay,
                'playlistloop' => $playlistloop,
                'playlistthumbnails' => $playlistthumbnails,
                'playlist_search' => $playlist_search,
                'linktoshop' => $linktoshop,
                'ads' => $ads,
                'ads_tag_url' => $ads_tag_url,
                'ads_skipable' => $ads_skipable,
                'ads_skipable_after' => $ads_skipable_after,
                'include_ext' => explode('|', strtolower($includeext)),
                'exclude_ext' => explode('|', strtolower($excludeext)),
                'show_files' => $showfiles,
                'show_folders' => $showfolders,
                'max_files' => $maxfiles,
                'filelayout' => $filelayout,
                'show_filesize' => $filesize,
                'show_filedate' => $filedate,
                'fileinfo_on_hover' => $fileinfo_on_hover,
                'hover_thumbs' => $hoverthumbs,
                'allow_switch_view' => $allow_switch_view,
                'show_ext' => $showext,
                'show_root' => $showroot,
                'sort_field' => $sortfield,
                'sort_order' => $sortorder,
                'show_tree' => $show_tree,
                'show_header' => $show_header,
                'show_breadcrumb' => $showbreadcrumb,
                'can_download_zip' => $candownloadzip,
                'canpopout' => $canpopout,
                'lightbox_imagesource' => $lightbox_imagesource,
                'lightbox_navigation' => $lightboxnavigation,
                'lightbox_thumbnails' => $lightboxthumbs,
                'show_sharelink' => $showsharelink,
                'share_password' => $share_password,
                'share_expire_after' => $share_expire_after,
                'share_allow_download' => $share_allow_download,
                'show_refreshbutton' => $showrefreshbutton,
                'use_custom_roottext' => $use_custom_roottext,
                'root_text' => $roottext,
                'search' => $search,
                'search_role' => $searchrole,
                'searchfrom' => $searchfrom,
                'searchterm' => $searchterm,
                'searchcontents' => $searchcontents,
                'include' => explode('|', htmlspecialchars_decode($include)),
                'exclude' => explode('|', htmlspecialchars_decode($exclude)),
                'show_system_files' => $showsystemfiles,
                'maxwidth' => $maxwidth,
                'maxheight' => $maxheight,
                'scrolltotop' => $scrolltotop,
                'view_role' => $viewrole,
                'display_loginscreen' => $display_loginscreen,
                'password' => $password,
                'password_hash' => empty($password) ? '' : wp_hash_password($password),
                'requires_lead' => $requires_lead,
                'onclick' => ('none' !== $previewrole) ? $onclick : 'download',
                'preview_role' => $previewrole,
                'download_role' => $downloadrole,
                'share_role' => $sharerole,
                'previewinline' => ('1' === $forcedownload || 'none' === $previewrole) ? '0' : $previewinline,
                'previewnewtab' => ('none' === $previewrole) ? '0' : $previewnewtab,
                'forcedownload' => $forcedownload,
                'axis' => $axis,
                'padding' => $padding,
                'border_radius' => $border_radius,
                'description_position' => $description_position,
                'navigation_dots' => $navigation_dots,
                'navigation_arrows' => $navigation_arrows,
                'slide_items' => $slide_items,
                'slide_height' => $slide_height,
                'slide_by' => $slide_by,
                'slide_speed' => $slide_speed,
                'slide_center' => $slide_center,
                'slide_auto_size' => $slide_auto_size,
                'carousel_autoplay' => $carousel_autoplay,
                'pausetime' => $pausetime,
                'hoverpause' => $hoverpause,
                'direction' => $direction,
                'notificationupload' => $notificationupload,
                'notificationdownload' => $notificationdownload,
                'notificationdeletion' => $notificationdeletion,
                'notificationmove' => $notificationmove,
                'notificationcopy' => $notificationcopy,
                'notificationemail' => $notificationemail,
                'notification_skip_email_currentuser' => $notification_skipemailcurrentuser,
                'notification_from_name' => $notification_from_name,
                'notification_from_email' => $notification_from_email,
                'notification_replyto_email' => $notification_replyto_email,
                'proofing_max_items' => $proofing_max_items,
                'proofing_use_labels' => $proofing_use_labels,
                'proofing_labels' => !empty($proofing_labels) ? explode('|', $proofing_labels) : [],
                'upload' => $upload,
                'upload_folder' => $upload_folder,
                'overwrite' => $overwrite,
                'upload_auto_start' => $upload_auto_start,
                'upload_filename' => $upload_filename,
                'upload_create_shared_link' => $upload_create_shared_link,
                'upload_create_shared_link_folder' => $upload_create_shared_link_folder,
                'upload_button_text' => $upload_button_text,
                'upload_button_text_plural' => $upload_button_text_plural,
                'upload_ext' => strtolower($uploadext),
                'upload_role' => $uploadrole,
                'minfilesize' => $minfilesize,
                'maxfilesize' => $maxfilesize,
                'maxnumberofuploads' => $maxnumberofuploads,
                'delete' => $delete,
                'delete_files_role' => $deletefilesrole,
                'delete_folders_role' => $deletefoldersrole,
                'rename' => $rename,
                'rename_files_role' => $renamefilesrole,
                'rename_folders_role' => $renamefoldersrole,
                'move' => $move,
                'move_files_role' => $movefilesrole,
                'move_folders_role' => $movefoldersrole,
                'copy' => $copy,
                'copy_files_role' => $copyfilesrole,
                'copy_folders_role' => $copyfoldersrole,
                'addfolder' => $addfolder,
                'addfolder_role' => $addfolderrole,
                'create_document' => $createdocument,
                'create_document_role' => $createdocumentrole,
                'import' => $import,
                'deeplink' => $deeplink,
                'deeplink_role' => $deeplinkrole,
                'crop' => $crop,
                'show_filenames' => $showfilenames,
                'show_descriptions' => $show_descriptions,
                'show_descriptions_on_top' => $showdescriptionsontop,
                'targetheight' => $targetheight,
                'folderthumbs' => $folderthumbs,
                'lightbox_open' => $lightbox_open,
                'slideshow' => $slideshow,
                'pausetime' => $pausetime,
                'usage_period' => $usage_period,
                'download_limits' => $download_limits,
                'downloads_per_user' => $downloads_per_user,
                'downloads_per_user_per_file' => $downloads_per_user_per_file,
                'zip_downloads_per_user' => $zip_downloads_per_user,
                'bandwidth_per_user' => $bandwidth_per_user,
                'download_limits_excluded_roles' => $download_limits_excluded_roles,
                'embed_ratio' => $embed_ratio,
                'embed_type' => $embed_type,
                'embed_direct_media' => $embed_direct_media,
                'popup' => $popup,
                'post_id' => $post_id,
                'themestyle' => $themestyle,
                'demo' => $demo,
                'expire' => strtotime('+1 weeks'),
                'listtoken' => $this->listtoken, ];

            $this->options = apply_filters('outofthebox_shortcode_add_options', $this->options, $this, $atts);

            $this->save_shortcodes();

            $this->options = apply_filters('outofthebox_shortcode_set_options', $this->options, $this, $atts);

            // Create userfolders if needed

            if ('auto' === $this->options['userfolders'] && ('Yes' === Settings::get('userfolder_onfirstvisit'))) {
                $allusers = [];
                $roles = array_diff($this->options['view_role'], $this->options['view_user_folders_role']);

                foreach ($roles as $role) {
                    $users_query = new \WP_User_Query([
                        'fields' => 'all_with_meta',
                        'role' => $role,
                        'orderby' => 'display_name',
                    ]);
                    $results = $users_query->get_results();
                    if ($results) {
                        $allusers = array_merge($allusers, $results);
                    }
                }
                UserFolders::instance()->create_user_folders($allusers);
            }
        } else {
            $this->options = apply_filters('outofthebox_shortcode_set_options', $cached_shortcode, $this, $atts);
        }

        if (empty($this->options['startaccount'])) {
            App::set_current_account_by_id($this->options['account']);
        } else {
            App::set_current_account_by_id($this->options['startaccount']);
        }

        if (null === App::get_current_account() || false === App::get_current_account()->get_authorization()->has_access_token()) {
            return '&#9888; <strong>'.esc_html__('Module cannot be rendered as the requested content is not (longer) accessible. Contact the administrator to get access.', 'wpcloudplugins').'</strong>';
        }

        // Render the module
        \ob_start();
        Module::render($this->options['mode']);

        return \ob_get_clean();
    }

    public function create_thumbnail()
    {
        Client::instance()->build_thumbnail();

        exit;
    }

    public function get_last_path()
    {
        return $this->_lastPath;
    }

    public function set_last_path($last_path)
    {
        $this->_lastPath = $last_path;
        if ('' === $this->_lastPath) {
            $this->_lastPath = '/';
        }
        $this->_set_requested_path();

        return $this->_lastPath;
    }

    public function get_requested_path()
    {
        return $this->_requestedPath;
    }

    public function get_requested_complete_path()
    {
        return $this->_requestedCompletePath;
    }

    public function get_root_folder()
    {
        return $this->_rootFolder;
    }

    public function get_relative_path($full_path, $from_path = null)
    {
        if (empty($from_path)) {
            $from_path = $this->get_root_folder();

            if ('' === $from_path || '/' === $from_path) {
                return $full_path;
            }
        }

        $from_path_arr = explode('/', $from_path);
        $full_path_arr = explode('/', $full_path);
        $difference = (count($full_path_arr) - count($from_path_arr));

        if ($difference < 1) {
            return '/';
        }

        if (1 === $difference) {
            return '/'.end($full_path_arr);
        }

        return '/'.implode('/', array_slice($full_path_arr, -$difference));
    }

    public function get_listtoken()
    {
        return $this->listtoken;
    }

    public function sort_filelist($foldercontents)
    {
        $sort_field = $this->get_shortcode_option('sort_field') ?? 'name';
        $sort_order = $this->get_shortcode_option('sort_order') ?? 'asc';

        if (isset($_REQUEST['sort'])) {
            list($sort_field, $sort_order) = explode(':', $_REQUEST['sort']);
        }

        if (!empty($foldercontents)) {
            // Sort Filelist, folders first
            $sort = [];

            if ('shuffle' === $sort_field) {
                // Get the keys and shuffle them
                $keys = array_keys($foldercontents);
                shuffle($keys);

                // Build a new array with the shuffled keys
                $shuffled = [];
                foreach ($keys as $key) {
                    $shuffled[$key] = $foldercontents[$key];
                }

                return $shuffled;
            }

            switch ($sort_field) {
                case 'size':
                    $sort_field = 'size';

                    break;

                case 'modified':
                    $sort_field = 'last_edited';

                    break;

                case 'name':
                default:
                    $sort_field = 'name';

                    break;
            }

            switch ($sort_order) {
                case 'desc':
                    $sort_order = SORT_DESC;

                    break;

                case 'asc':
                default:
                    $sort_order = SORT_ASC;

                    break;
            }

            list($sort_field, $sort_order) = apply_filters('outofthebox_sort_filelist_settings', [$sort_field, $sort_order], $foldercontents, $this);

            foreach ($foldercontents as $k => $v) {
                if ($v instanceof EntryAbstract) {
                    $sort['is_dir'][$k] = $v->is_dir();
                    $sort['sort'][$k] = strtolower($v->{'get_'.$sort_field}() ?? '');
                } else {
                    $sort['is_dir'][$k] = $v['is_dir'];
                    $sort['sort'][$k] = $v[$sort_field];
                }
            }

            // Sort by dir desc and then by name asc
            array_multisort($sort['is_dir'], SORT_DESC, SORT_REGULAR, $sort['sort'], $sort_order, SORT_NATURAL | SORT_FLAG_CASE, $foldercontents, SORT_ASC);
        }

        $foldercontents = apply_filters('outofthebox_sort_filelist', $foldercontents, $sort_field, $sort_order, $this);

        return $foldercontents;
    }

    public function send_notification_email($notification_type, $entries)
    {
        $notification = new Notification($notification_type, $entries);
        $notification->send_notification();
    }

    // Check if $entry is allowed

    public function _is_entry_authorized(Entry $entry)
    {
        // Return in case a direct call is being made, and no shortcode is involved
        if (empty($this->options)) {
            return true;
        }

        // Action for custom filters
        $is_authorized_hook = apply_filters('outofthebox_is_entry_authorized', true, $entry, $this);
        if (false === $is_authorized_hook) {
            return false;
        }

        // Check if the entry is in the list of items
        $items = Processor::instance()->get_shortcode_option('items');
        if (!empty($items)) {
            foreach (Processor::instance()->get_shortcode_option('items') as $item_id) {
                if ($entry->get_id() === $item_id) {
                    return true;
                }
            }

            return false;
        }

        if (strtolower($entry->get_path()) === strtolower($this->_rootFolder)) {
            return true;
        }

        // skip entry if its a file, and we dont want to show files
        if ($entry->is_file() && ('0' === $this->get_shortcode_option('show_files'))) {
            return false;
        }

        // Skip entry if its a folder, and we dont want to show folders
        if ($entry->is_dir() && ('0' === $this->get_shortcode_option('show_folders'))) {
            return false;
        }

        // Only keep files with the right extension
        $extension = $entry->get_extension();
        $allowed_extensions = $this->get_shortcode_option('include_ext');
        if ('*' != $allowed_extensions[0] && $entry->is_file() && is_array($allowed_extensions) && (empty($extension) || (!in_array($entry->get_extension(), $allowed_extensions)))) {
            return false;
        }

        // Hide files with extensions
        $hide_extensions = $this->get_shortcode_option('exclude_ext');
        if ('*' != $hide_extensions[0] && $entry->is_file() && !empty($extension) && in_array(strtolower($extension), $hide_extensions)) {
            return false;
        }

        $_path = str_ireplace($this->_rootFolder.'/', '', $entry->get_path());
        $_path = strtolower($_path);
        $subs = array_filter(explode('/', $_path));

        $exclude_caseinsenstive = array_map('strtolower', $this->options['exclude']);
        if ('*' != $this->options['exclude'][0]) {
            // IDs are Case Sensitive
            if (in_array($entry->get_id(), $this->options['exclude'])) {
                return false;
            }
            if (in_array(strtolower($entry->get_name()), $exclude_caseinsenstive)) {
                return false;
            }
            if (!empty($subs)) {
                $found = false;

                foreach ($subs as $sub) {
                    if (in_array($sub, $exclude_caseinsenstive)) {
                        $found = true;
                    }
                }
                if ($found) {
                    return false;
                }
            }
        }

        // only allow included folders and files
        $include_caseinsenstive = array_map('strtolower', $this->options['include']);
        if ('*' != $this->options['include'][0]) {
            // IDs are Case Sensitive
            if (in_array($entry->get_id(), $this->options['include'])) {
                $found = true;
            } elseif (in_array(strtolower($entry->get_name()), $include_caseinsenstive)) {
                $found = true;
            } elseif (!empty($subs)) {
                $found = false;

                foreach ($subs as $sub) {
                    if (in_array($sub, $include_caseinsenstive)) {
                        $found = true;
                    }
                }
                if (!$found) {
                    return false;
                }
            }
        }

        // Check if file is hidden system file
        if ($entry->is_file() && '0' === $this->options['show_system_files']) {
            $regex = '/^\.(.*)/i';
            if (1 === preg_match($regex, $entry->get_name())) {
                return false;
            }
        }

        return true;
    }

    public function is_filtering_entries()
    {
        if ('0' === $this->get_shortcode_option('show_files')) {
            return true;
        }

        if ('0' === $this->get_shortcode_option('show_folders')) {
            return true;
        }

        $extensions = $this->get_shortcode_option('include_ext');
        if ('*' !== $extensions[0]) {
            return true;
        }

        $hide_entries = $this->get_shortcode_option('exclude');
        if ('*' !== $hide_entries[0]) {
            return true;
        }
        $include_entries = $this->get_shortcode_option('include');
        if ('*' !== $include_entries[0]) {
            return true;
        }

        return false;
    }

    public function embed_entry($entryid)
    {
        $entry = Client::instance()->get_entry($entryid, false);

        if (false === $entry || false === $entry->get_can_preview_by_cloud()) {
            return false;
        }

        if (in_array($entry->get_extension(), ['xls', 'xlsx', 'xlsm', 'gsheet', 'csv'])) {
            header('Content-Type: text/html');
        } else {
            header('Content-Disposition: inline; filename="'.$entry->get_basename().'.pdf"');
            header('Content-Description: "'.$entry->get_basename().'"');
            header('Content-Type: application/pdf');
        }

        try {
            $preview_file = App::instance()->get_sdk_client()->preview($entry->get_path());
            echo $preview_file->getContents();
        } catch (\Exception $ex) {
            Helpers::log_error('', 'API', null, __LINE__, $ex);

            exit('-1');
        }

        return true;
    }

    // Check if $extensions array has $entry

    public function _is_extension_authorized($entry, $extensions, $prefix = '.')
    {
        if ('*' != $extensions[0]) {
            $pathinfo = Helpers::get_pathinfo($entry);
            if (!isset($pathinfo['extension'])) {
                return true;
            }

            foreach ($extensions as $allowedextensions) {
                if (false !== stripos($entry, $prefix.$allowedextensions)) {
                    return true;
                }
            }
        } else {
            return true;
        }

        return false;
    }

    public function is_mobile()
    {
        return $this->mobile;
    }

    public function get_shortcode()
    {
        return $this->options;
    }

    public function get_shortcode_option($key)
    {
        if (!isset($this->options[$key])) {
            return null;
        }

        return $this->options[$key];
    }

    public function set_shortcode($listtoken)
    {
        $cached_shortcode = Shortcodes::instance()->get_shortcode_by_id($listtoken);

        if ($cached_shortcode) {
            $this->options = $cached_shortcode;
            $this->listtoken = $listtoken;
        }

        return $this->options;
    }

    /**
     * Function that enables gzip compression when is needed and when is possible.
     */
    public function _set_gzip_compression()
    {
        // Compress file list if possible
        if ('Yes' === Settings::get('gzipcompression')) {
            $zlib = ('' == ini_get('zlib.output_compression') || !ini_get('zlib.output_compression')) && ('ob_gzhandler' != ini_get('output_handler'));
            if (true === $zlib && extension_loaded('zlib') && !in_array('ob_gzhandler', ob_list_handlers())) {
                ob_start('ob_gzhandler');
            }
        }
    }

    public static function reset_complete_cache($including_shortcodes = false, $including_thumbnails = false)
    {
        if (!file_exists(OUTOFTHEBOX_CACHEDIR)) {
            return false;
        }

        if (\function_exists('wp_cache_supports') && \wp_cache_supports('flush_group')) {
            \wp_cache_flush_group('wpcp-'.CORE::$slug.'-nodes');
            \wp_cache_flush_group('wpcp-'.CORE::$slug.'-limits');
            \wp_cache_flush_group('wpcp-'.CORE::$slug.'-entries');
            \wp_cache_flush_group('wpcp-'.CORE::$slug.'-other');
        }

        require_once ABSPATH.'wp-admin/includes/class-wp-filesystem-base.php';

        require_once ABSPATH.'wp-admin/includes/class-wp-filesystem-direct.php';

        $wp_file_system = new \WP_Filesystem_Direct(false);

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(OUTOFTHEBOX_CACHEDIR, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            if ('.htaccess' === $path->getFilename()) {
                continue;
            }

            if ('access_token' === $path->getExtension()) {
                continue;
            }

            if ('css' === $path->getExtension()) {
                continue;
            }

            if ('log' === $path->getExtension()) {
                continue;
            }

            if (false === $including_shortcodes && 'shortcodes' === $path->getExtension()) {
                continue;
            }

            if (false !== strpos($path->getPathname(), 'thumbnails') && false === $including_thumbnails) {
                continue;
            }

            if ('index' === $path->getExtension()) {
                // index files can be locked during purge request
                $fp = fopen($path->getPathname(), 'w');

                if (false === $fp) {
                    continue;
                }

                if (flock($fp, LOCK_EX)) {
                    ftruncate($fp, 0);
                    flock($fp, LOCK_UN);
                }
            }

            try {
                $wp_file_system->delete($path->getPathname(), true);
            } catch (\Exception $ex) {
                continue;
            }
        }

        return true;
    }

    public static function do_shutdown()
    {
        $error = error_get_last();

        if (null === $error) {
            return;
        }

        if (E_ERROR !== $error['type']) {
            return;
        }

        if (isset($error['file']) && false !== strpos($error['file'], OUTOFTHEBOX_ROOTDIR)) {
            Helpers::log_error('The cache has been reset.', 'Cache', null, __LINE__);
        }
    }

    protected function remove_deprecated_options($options = [])
    {
        // Deprecated Shuffle
        if (isset($options['shuffle'])) {
            unset($options['shuffle']);
            $options['sortfield'] = 'shuffle';
        }
        // Changed Userfolders
        if (isset($options['user_upload_folders'])) {
            $options['userfolders'] = $options['user_upload_folders'];
            unset($options['user_upload_folders']);
        }

        if (isset($options['userfolders']) && '1' === $options['userfolders']) {
            $options['userfolders'] = 'auto';
        }

        if (isset($options['partiallastrow'])) {
            unset($options['partiallastrow']);
        }

        if (isset($options['maxfiles']) && empty($options['maxfiles'])) {
            unset($options['maxfiles']);
        }

        // Convert bytes in version before 1.8 to MB
        if (isset($options['maxfilesize']) && !empty($options['maxfilesize']) && ctype_digit($options['maxfilesize'])) {
            $options['maxfilesize'] = Helpers::bytes_to_size_1024($options['maxfilesize']);
        }

        if (isset($options['forcedownload']) && 1 === $options['forcedownload'] && !isset($options['previewrole'])) {
            $options['previewrole'] = 'none';
        }

        if (isset($options['userfolders']) && '0' === $options['userfolders']) {
            $options['userfolders'] = 'off';
        }

        if (isset($options['ext'])) {
            $options['includeext'] = $options['ext'];
            unset($options['ext']);
        }

        if (!empty($options['mode']) && in_array($options['mode'], ['video', 'audio']) && isset($options['linktomedia'])) {
            if ('0' === $options['linktomedia']) {
                $options['downloadrole'] = empty($options['downloadrole']) ? 'none' : $options['downloadrole'];
            } else {
                $options['downloadrole'] = empty($options['downloadrole']) ? 'all' : $options['downloadrole'];
            }
            unset($options['linktomedia']);
        }

        if (isset($options['allowpreview']) && '0' === $options['allowpreview']) {
            unset($options['allowpreview']);
            $options['previewrole'] = 'none';
        }

        if (isset($options['upload_filename_prefix'])) {
            $options['upload_filename'] = $options['upload_filename_prefix'].(isset($options['upload_filename']) ? $options['upload_filename'] : '%file_name%%file_extension%');
            unset($options['upload_filename_prefix']);
        }

        if (isset($options['hideplaylist'])) {
            $options['showplaylist'] = '0' !== $options['hideplaylist'];
        }

        if (isset($options['mcepopup'])) {
            $options['popup'] = $options['mcepopup'];
            unset($options['mcepopup']);
        }

        if (isset($options['move_role'])) {
            $options['movefilesrole'] = $options['move_role'];
            $options['movefoldersrole'] = $options['move_role'];
            unset($options['move_role']);
        }

        if (isset($options['popup']) && 'woocommerce' === $options['popup']) {
            $options['popup'] = 'selector';
        }

        // Usage Limits now uses periods
        if (isset($options['downloads_per_user_per_day'])) {
            $options['downloads_per_user'] = $options['downloads_per_user_per_day'];
            unset($options['downloads_per_user_per_day']);
        }

        if (isset($options['zip_downloads_per_user_per_day'])) {
            $options['zip_downloads_per_user'] = $options['zip_downloads_per_user_per_day'];
            unset($options['zip_downloads_per_user_per_day']);
        }

        if (isset($options['bandwidth_per_user_per_day'])) {
            $options['bandwidth_per_user'] = $options['bandwidth_per_user_per_day'];
            unset($options['bandwidth_per_user_per_day']);
        }

        // Changed ..._thumbnail to just thumbnail)
        if (isset($options['lightbox_imagesource']) && false !== strpos($options['lightbox_imagesource'], 'thumbnail')) {
            $options['lightbox_imagesource'] = 'thumbnail';
        }

        return $options;
    }

    protected function save_shortcodes()
    {
        Shortcodes::instance()->set_shortcode($this->listtoken, $this->options);
        Shortcodes::instance()->update_cache();
    }

    private function _set_requested_path($path = '')
    {
        if ('' === $path) {
            if ('' !== $this->_lastPath) {
                $path = $this->_lastPath;
            } else {
                $path = '/';
            }
        }

        $regex = '/(id:.*)|(ns:[\d]+(\/.*)?)/i';
        if (1 === preg_match($regex, $path)) {
            $this->_requestedPath = $path;
            $this->_requestedCompletePath = $path;

            return;
        }

        $path = Helpers::clean_folder_path($path);
        $path_parts = Helpers::get_pathinfo($path);

        $this->_requestedDir = '';
        $this->_requestedFile = '';

        if (isset($path_parts['extension'])) {
            // it's a file
            $this->_requestedFile = $path_parts['basename'];
            $this->_requestedDir = str_replace('\\', '/', $path_parts['dirname']);
            $requestedDir = ('/' === $this->_requestedDir) ? '/' : $this->_requestedDir.'/';
            $this->_requestedPath = $requestedDir.$this->_requestedFile;
        } else {
            // it's a dir
            $this->_requestedDir = str_replace('\\', '/', $path);
            $this->_requestedFile = '';
            $this->_requestedPath = $this->_requestedDir;
        }

        $requestedCompletePath = $this->_rootFolder;

        if ($this->_rootFolder !== $this->_requestedPath) {
            $requestedCompletePath = html_entity_decode($this->_rootFolder.$this->_requestedPath);
        }

        $this->_requestedCompletePath = str_replace('//', '/', $requestedCompletePath);
    }
}
