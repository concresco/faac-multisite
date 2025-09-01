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

// Exit if no permission to add shortcodes
if (
    !Helpers::check_user_role(Settings::get('permissions_add_shortcodes'))
) {
    exit;
}

Core::instance()->load_scripts();
Core::instance()->load_styles();

function remove_all_scripts()
{
    global $wp_scripts;
    $wp_scripts->queue = [];

    wp_enqueue_script('jquery-effects-fade');
    wp_enqueue_script('OutoftheBox');
}

function remove_all_styles()
{
    global $wp_styles;
    $wp_styles->queue = [];
    wp_enqueue_style('OutoftheBox');
}

add_action('wp_print_scripts', __NAMESPACE__.'\remove_all_scripts', 1000);
add_action('wp_print_styles', __NAMESPACE__.'\remove_all_styles', 1000);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo get_bloginfo('language'); ?>" style="overflow: hidden;">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php esc_html_e('Shortcode Previewer', 'wpcloudplugins'); ?></title>
    <?php wp_print_scripts(); ?>
    <?php wp_print_styles(); ?>
</head>

<body>
    <?php

if (isset($_REQUEST['module_id'])) {
    $module_id = intval($_REQUEST['module_id']);
    echo do_shortcode("[outofthebox module='{$module_id}']");
} elseif (isset($_REQUEST['shortcode'])) {
    $shortcode = Shortcodes::decode(\sanitize_text_field($_REQUEST['shortcode']));
    echo do_shortcode($shortcode);
}

do_action('wp_print_footer_scripts');

?></body>

</html>