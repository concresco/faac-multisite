<?php

/*
Plugin Name: WordPress Navigation Menu Links
Plugin URI: https://profilepress.net
Description: Add ProfilePress login, registration, password reset, edit profile, my profile and logout links to WordPress navigation menu.
Version: 1.0.7
Author: ProfilePress
Author URI: https://profilepress.net
License: GPL2
Text Domain: wp-navigation-menu-links
Domain Path: /languages/
*/


namespace ProfilePress\Nav_Menu_Links;

require_once dirname(__FILE__). '/backend.php';
require_once dirname(__FILE__). '/frontend.php';
require_once dirname(__FILE__). '/mo-admin-notice.php';


add_action('plugins_loaded', 'ProfilePress\Nav_Menu_Links\load_plugin');

function load_plugin()
{
    load_plugin_textdomain('wp-navigation-menu-links', false, dirname(plugin_basename(__FILE__)) . '/languages');

    Backend::get_instance();
    Frontend::get_instance();
}