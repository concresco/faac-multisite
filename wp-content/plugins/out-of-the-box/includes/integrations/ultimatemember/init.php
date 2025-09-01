<?php

namespace TheLion\OutoftheBox\Integrations;

use TheLion\OutoftheBox\UserFolders;

defined('ABSPATH') || exit;

class UltimateMember
{
    private static $_instance;

    public function __construct()
    {
        // Ultimate Members is first registering users before adding the custom metadata like roles, or first/last name.
        // That means that the plugin is creating the Personal Folders when the custom metadata isn't yet available.
        add_filter('um_add_user_frontend_submitted', [$this, 'disable_personal_folder_on_um_user_registration'], 10, 1);
        add_action('um_after_save_registration_details', [$this, 'create_personal_folder_after_um_user_registration'], 10, 1);
    }

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function disable_personal_folder_on_um_user_registration($args)
    {
        remove_action('user_register', ['TheLion\OutoftheBox\UserFolders', 'user_register']);

        return $args;
    }

    public function create_personal_folder_after_um_user_registration($user_id)
    {
        UserFolders::user_register($user_id);
    }
}

UltimateMember::instance();
