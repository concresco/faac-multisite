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

class User
{
    /**
     * The single instance of the class.
     *
     * @var User
     */
    protected static $_instance;

    private static $_can_view = false;
    private static $_can_preview = false;
    private static $_can_download = false;
    private static $_can_download_zip = false;
    private static $_can_delete_files = false;
    private static $_can_delete_folders = false;
    private static $_can_rename_files = false;
    private static $_can_rename_folders = false;
    private static $_can_add_folders = false;
    private static $_can_create_document = false;
    private static $_can_upload = false;
    private static $_can_move_files = false;
    private static $_can_move_folders = false;
    private static $_can_copy_files = false;
    private static $_can_copy_folders = false;
    private static $_can_share = false;
    private static $_can_edit_description = false;
    private static $_can_deeplink = false;
    private static $_can_search = false;
    private static $_can_import = false;
    private static $_locale;

    public function __construct()
    {
        self::init();
    }

    public static function reset()
    {
        self::$_instance = new self();

        return self::$_instance;
    }

    /**
     * User Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return User - User instance
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

    public static function can_view()
    {
        self::instance();

        return self::$_can_view;
    }

    public static function can_preview()
    {
        self::instance();

        return self::$_can_preview;
    }

    public static function can_download()
    {
        self::instance();

        return self::$_can_download;
    }

    public static function can_download_zip()
    {
        self::instance();

        return self::$_can_download_zip;
    }

    public static function can_delete_files()
    {
        self::instance();

        return self::$_can_delete_files;
    }

    public static function can_delete_folders()
    {
        self::instance();

        return self::$_can_delete_folders;
    }

    public static function can_rename_files()
    {
        self::instance();

        return self::$_can_rename_files;
    }

    public static function can_rename_folders()
    {
        self::instance();

        return self::$_can_rename_folders;
    }

    public static function can_add_folders()
    {
        self::instance();

        return self::$_can_add_folders;
    }

    public static function can_create_document()
    {
        self::instance();

        return self::$_can_create_document;
    }

    public static function can_upload()
    {
        self::instance();

        return self::$_can_upload;
    }

    public static function can_move_files()
    {
        self::instance();

        return self::$_can_move_files;
    }

    public static function can_move_folders()
    {
        self::instance();

        return self::$_can_move_folders;
    }

    public static function can_copy_files()
    {
        self::instance();

        return self::$_can_copy_files;
    }

    public static function can_copy_folders()
    {
        self::instance();

        return self::$_can_copy_folders;
    }

    public static function can_share()
    {
        self::instance();

        return self::$_can_share;
    }

    public static function can_deeplink()
    {
        self::instance();

        return self::$_can_deeplink;
    }

    public static function can_edit_description()
    {
        self::instance();

        return self::$_can_edit_description;
    }

    public static function can_search()
    {
        self::instance();

        return self::$_can_search;
    }

    public static function can_import()
    {
        self::instance();

        return self::$_can_import;
    }

    public static function get_permissions_hash()
    {
        self::instance();

        $class = new \ReflectionClass('\TheLion\OutoftheBox\User');
        $data = $class->getStaticProperties();

        unset($data['_instance']);
        $data = json_encode($data);

        return md5($data);
    }

    private static function init()
    {
        self::$_can_view = Helpers::check_user_role(Processor::instance()->get_shortcode_option('view_role'));

        if (false === self::$_can_view) {
            return;
        }

        self::$_can_preview = Helpers::check_user_role(Processor::instance()->get_shortcode_option('preview_role'));
        self::$_can_download = Helpers::check_user_role(Processor::instance()->get_shortcode_option('download_role'));
        self::$_can_download_zip = ('1' === Processor::instance()->get_shortcode_option('can_download_zip')) && self::$_can_download;

        if ('1' === Processor::instance()->get_shortcode_option('delete')) {
            self::$_can_delete_files = Helpers::check_user_role(Processor::instance()->get_shortcode_option('delete_files_role'));
            self::$_can_delete_folders = Helpers::check_user_role(Processor::instance()->get_shortcode_option('delete_folders_role'));
        }

        if ('1' === Processor::instance()->get_shortcode_option('rename')) {
            self::$_can_rename_files = Helpers::check_user_role(Processor::instance()->get_shortcode_option('rename_files_role'));
            self::$_can_rename_folders = Helpers::check_user_role(Processor::instance()->get_shortcode_option('rename_folders_role'));
        }

        self::$_can_add_folders = ('1' === Processor::instance()->get_shortcode_option('addfolder')) && Helpers::check_user_role(Processor::instance()->get_shortcode_option('addfolder_role'));
        self::$_can_create_document = ('1' === Processor::instance()->get_shortcode_option('create_document')) && Helpers::check_user_role(Processor::instance()->get_shortcode_option('create_document_role'));
        self::$_can_upload = ('1' === Processor::instance()->get_shortcode_option('upload')) && Helpers::check_user_role(Processor::instance()->get_shortcode_option('upload_role'));

        if ('1' === Processor::instance()->get_shortcode_option('move')) {
            self::$_can_move_files = Helpers::check_user_role(Processor::instance()->get_shortcode_option('move_files_role'));
            self::$_can_move_folders = Helpers::check_user_role(Processor::instance()->get_shortcode_option('move_folders_role'));
        }

        if ('1' === Processor::instance()->get_shortcode_option('copy')) {
            self::$_can_copy_files = Helpers::check_user_role(Processor::instance()->get_shortcode_option('copy_files_role'));
            self::$_can_copy_folders = Helpers::check_user_role(Processor::instance()->get_shortcode_option('copy_folders_role'));
        }

        self::$_can_share = ('1' === Processor::instance()->get_shortcode_option('show_sharelink')) && Helpers::check_user_role(Processor::instance()->get_shortcode_option('share_role'));
        self::$_can_edit_description = false;
        self::$_can_deeplink = ('1' === Processor::instance()->get_shortcode_option('deeplink')) && Helpers::check_user_role(Processor::instance()->get_shortcode_option('deeplink_role'));

        self::$_can_search = ('1' === Processor::instance()->get_shortcode_option('search')) && Helpers::check_user_role(Processor::instance()->get_shortcode_option('search_role'));

        self::$_can_import = ('1' === Processor::instance()->get_shortcode_option('import'));

        self::$_locale = get_user_locale(0);
    }
}
