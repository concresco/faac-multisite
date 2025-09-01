<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

// Exit if no permission to add shortcodes
if (
    !Helpers::check_user_role(Settings::get('permissions_add_shortcodes'))
) {
    $loaders = Settings::get('loaders');
    ?>
<div id='OutoftheBox'>
    <div class='OutoftheBox list-container noaccess'>
        <div style="max-width:512px; margin: 0 auto; text-align:center;">
            <img src="<?php echo $loaders['protected']; ?>" data-src-retina="<?php echo $loaders['protected']; ?>" style="display:inline-block" alt="">
        </div>
    </div>
</div>
<?php
    exit;
}

// Load module templates
$module_templates = Modules::get_modules_templates();

// Get Shortcode from request
if (!empty($_REQUEST['shortcode'])) {
    $shortcode = Shortcodes::decode(\sanitize_text_field($_REQUEST['shortcode']));
    $shortcode_atts = Shortcodes::parse_attributes($shortcode);
    $module_id = $shortcode_atts['module'] ?? '';
} elseif (!empty($_REQUEST['module'])) {
    $module_id = sanitize_key($_REQUEST['module']);
} else {
    $shortcode = '';
    $module_id = '';
}

$module = null;

if (!empty($module_id)) {
    // Load Shortcode for module if Module ID is present
    $module = Modules::get_module_by_id($module_id);
}

if (empty($module)) {
    // Create Draft Module using the shortcode if present
    $module = Modules::create_module(
        [
            'post_content' => $shortcode,
        ]
    );
}

// Put the shortcode attributes in the GET parameters as they are used in rendering the settings
if (!empty($module->post_content)) {
    $attributes = Shortcodes::parse_attributes($module->post_content);
    $_GET = array_merge($_GET, $attributes);
}
AdminLayout::set_setting_value_location('GET');

// Short Shortcode for this module
$shortcode = Shortcodes::parse_shortcode(['module' => $module->ID]);

// Specific Module Configurator configurations

$for_upload_field = (isset($_REQUEST['foruploadfield']) && '1' === $_REQUEST['foruploadfield']) ? true : false;
$for = (isset($_REQUEST['for'])) ? \sanitize_key($_REQUEST['for']) : 'shortcode_builder';
$callback = isset($_REQUEST['callback']) ? sanitize_key($_REQUEST['callback']) : '';
$standalone = empty($callback);
$editable = $module->post_name !== Modules::$post_type.'-default-present';
$modules_url = OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-getpopup&type=modules&foruploadfield='.($for_upload_field ? 1 : 0).'&callback='.esc_attr($callback);

do_action('outofthebox_before_shortcode_builder', $standalone, $for_upload_field, $for, $callback);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo get_bloginfo('language'); ?>" class="wpcp-h-full wpcp-bg-gray-100">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php esc_html_e('Module Configurator', 'wpcloudplugins'); ?></title>
    <?php wp_print_styles(); ?>
    <style>
    .wpcp-menu-button-element {
        visibility: hidden;
    }

    .wpcp-menu-button:hover+.wpcp-menu-button-element,
    .wpcp-menu-button-element:hover {
        max-height: unset !important;
        visibility: visible;
    }
    </style>
</head>

<body class="wpcp-h-full" data-module-id="<?php echo $module->ID; ?>" data-shortcode="<?php echo \esc_attr(Shortcodes::encode($shortcode)); ?>" data-full-shortcode="<?php echo \esc_attr(Shortcodes::encode($module->post_content)); ?>" data-callback="<?php echo esc_attr($callback); ?>" data-configuration="<?php echo ($for_upload_field) ? 'upload-field' : ''; ?>">
    <div id="wpcp" class="wpcp-app wpcp-h-full hidden" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">

        <div class="relative">
            <div class="fixed inset-0 flex flex-col">
                <div class="flex grow flex-col ">
                    <!-- Logo Bar -->
                    <header class="z-50 bg-brand-color-900 flex-none">
                        <div class="mx-auto  px-4 sm:px-6 lg:px-8">
                            <div class="flex h-12 items-center justify-around">
                                <!-- Logo -->
                                <div class="shrink-0">
                                    <img class="h-8 w-auto" src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/wpcloudplugins-logo-light.png" alt="WP Cloud Plugins">
                                </div>
                            </div>
                        </div>
                    </header>
                    <!-- End Logo Bar -->

                    <!-- Page: Module Builder -->
                    <main data-menu-panel="wpcp-module-builder" class="flex flex-col flex-1">

                        <nav class="bg-white shadow" navigation="application" aria-label="Module actions">
                            <div class="mx-auto px-4 py-3 sm:px-6 lg:px-8 flex flex-row justify-between h-14 items-center gap-2">
                                <a href="<?php echo $modules_url; ?>" type="button" class="wpcp-button-icon-only inline-flex justify-center" data-dialog-id="#wpcp-modal-show-shortcode" title="<?php esc_html_e('All Modules', 'wpcloudplugins'); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                                    </svg>
                                </a>

                                <div class="relative">
                                    <button type="button" class="wpcp-menu-button">
                                        <div id="wpcp-module-icon" class="flex h-10 w-10 -mr-2 shrink-0 justify-self-center self-center items-center justify-center rounded-l-md <?php echo (!empty($attributes['mode'])) ? $module_templates[$attributes['mode']]['color'] : $module_templates['files']['color']; ?>">
                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                <?php echo (!empty($attributes['mode'])) ? $module_templates[$attributes['mode']]['icon'] : $module_templates['files']['icon']; ?>
                                            </svg>
                                        </div>
                                    </button>
                                    <div class="wpcp-menu-button-element absolute left-0 z-20 w-96 px-4 origin-top-left divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-hidden overflow-hidden max-h-0" role="menu" aria-orientation="vertical" tabindex="-1">
                                        <?php

$mode_settings = [
    'key' => 'mode',
    'title' => '',
    'default' => 'files',
    'type' => 'radio_group',
    'options' => Modules::get_modules_templates(),
    'modules' => ['all'],
];
AdminLayout::render_simple_radio_group($mode_settings);

?>
                                    </div>
                                </div>

                                <?php $placeholder = esc_html__('Module title', 'wpcloudplugins'); ?>

                                <div class="relative rounded-md shadow-xs w-full flex-initial">
                                    <input type="text" id="wpcp-module-name" class="block w-full rounded-r-md  py-1.5 pl-3 pr-12 text-gray-900 ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-color-700 focus:border-brand-color-700 border border-gray-300 text-xl font-bold tracking-tight" placeholder="<?php echo esc_attr($placeholder); ?>" value="<?php echo esc_attr($module->post_title); ?>" <?php echo ($editable) ? '' : 'readonly="readonly"'; ?> autocomplete="off">

                                    <?php if ($editable) { ?>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 gap-2 wpcp-copy-shortcode">
                                        <span class="text-gray-500 sm:text-sm" id="module-id"><?php echo $shortcode; ?></span>
                                        <button type="button" class="wpcp-copy-shortcode wpcp-button-icon-only" title="<?php \esc_attr_e('Copy Shortcode', 'wpcloudplugins'); ?>">
                                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <?php } ?>

                                </div>

                                <div class="flex items-center gap-2">

                                    <?php if ($editable) { ?>
                                    <button type="button" class="wpcp-input-checkbox-button bg-gray-200 relative inline-flex shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-brand-color-700" role="switch" aria-checked="false" title="<?php esc_html_e('Is the Module active?', 'wpcloudplugins'); ?>">
                                        <span class="wpcp-input-checkbox-button-container translate-x-0 pointer-events-none relative inline-block h-5 w-5 rounded-full bg-white shadow-sm transform ring-0 transition ease-in-out duration-200">
                                            <span class="wpcp-input-checkbox-button-off opacity-100 ease-in duration-200 absolute inset-0 h-full w-full flex items-center justify-center transition-opacity" aria-hidden="true">
                                                <svg class="h-3 w-3 " fill="none" viewBox="0 0 12 12">
                                                    <path d="M4 8l2-2m0 0l2-2M6 6L4 4m2 2l2 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </span>
                                            <span class="wpcp-input-checkbox-button-on opacity-0 ease-out duration-100 absolute inset-0 h-full w-full flex items-center justify-center transition-opacity" aria-hidden="true">
                                                <svg class="h-3 w-3 text-brand-color-900" fill="currentColor" viewBox="0 0 12 12">
                                                    <path d="M3.707 5.293a1 1 0 00-1.414 1.414l1.414-1.414zM5 8l-.707.707a1 1 0 001.414 0L5 8zm4.707-3.293a1 1 0 00-1.414-1.414l1.414 1.414zm-7.414 2l2 2 1.414-1.414-2-2-1.414 1.414zm3.414 2l4-4-1.414-1.414-4 4 1.414 1.414z" />
                                                </svg>
                                            </span>
                                        </span>
                                        <input type="checkbox" class="wpcp-toggle-status hidden" <?php echo ('publish' === get_post_status($module->ID)) ? 'checked' : ''; ?> />
                                    </button>

                                    <button id="wpcp-button-create-shortcode" type="button" class="wpcp-button-icon-only inline-flex justify-center" data-dialog-id="#wpcp-modal-show-shortcode" title="<?php esc_html_e('Raw Shortcode', 'wpcloudplugins'); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                        </svg>
                                    </button>

                                    <button type="button" class="wpcp-duplicate-module wpcp-button-icon-only inline-flex justify-center" title="<?php esc_html_e('Duplicate', 'wpcloudplugins'); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 8.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v8.25A2.25 2.25 0 0 0 6 16.5h2.25m8.25-8.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-7.5A2.25 2.25 0 0 1 8.25 18v-1.5m8.25-8.25h-6a2.25 2.25 0 0 0-2.25 2.25v6" />
                                        </svg>
                                    </button>

                                    <!--   Proof Details -->
                                    <?php if (false !== strpos($module->post_content, 'mode="proofing"') && !empty(get_edit_post_link($module->ID))) { ?>
                                    <a href="<?php echo esc_attr(get_edit_post_link($module->ID)); ?>" class="wpcp-proofing-edit wpcp-button-icon-only inline-flex justify-center" title="<?php \esc_attr_e('View Selections', 'wpcloudplugins'); ?>" target="_top">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12" />
                                        </svg>
                                    </a>
                                    <?php } ?>
                                    <!--    End Proof Details -->

                                    <a href="<?php echo get_permalink($module->ID); ?>" target="blank" class="wpcp-view-module wpcp-button-icon-only inline-flex justify-center" title="<?php \esc_attr_e('View Module', 'wpcloudplugins'); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </a>
                                    <?php } ?>

                                    <button id="wpcp-button-save-shortcode" type="button" class="wpcp-button-primary inline-flex justify-center" title="<?php esc_html_e('Save', 'wpcloudplugins'); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                                        </svg>
                                        <?php esc_html_e('Save', 'wpcloudplugins'); ?>
                                    </button>
                                    <?php if (false === $standalone) { ?>
                                    <button id="wpcp-button-pick-shortcode" type="button" class="wpcp-button-primary wpcp-dialog-close inline-flex justify-center" title="<?php esc_html_e('Choose this module', 'wpcloudplugins'); ?>" <?php echo ($editable) ? '' : 'disabled="disabled"'; ?>>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                                        </svg>
                                        <?php esc_html_e('Choose', 'wpcloudplugins'); ?>
                                    </button>
                                    <?php } else { ?>
                                    <button id="wpcp-button-back" type="button" class="wpcp-button-primary inline-flex justify-center" title="<?php esc_html_e('Click to go back', 'wpcloudplugins'); ?>" onclick="window.location.href='<?php echo $modules_url; ?>;'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                                        </svg>
                                        <?php esc_html_e('Back', 'wpcloudplugins'); ?>
                                    </button>
                                    <?php } ?>
                                </div>
                            </div>
                        </nav>

                        <div class="flex-1 w-full mx-auto px-4 py-4 sm:px-6 lg:px-8">
                            <div class="flow-root min-w-full h-[calc(100vh-9rem)]">

                                <div class="flex flex-row h-full min-h-full max-h-full gap-4">

                                    <div class="flex flex-col w-64">
                                        <div class="flex-1 flex flex-col min-h-0 border-r border-gray-200 bg-white rounded-md shadow-(--shadow-5) overflow-y-auto">
                                            <nav class="flex-1 px-2 my-5 flex flex-col gap-1" navigation="role" aria-label="Module options">
                                                <?php foreach (ShortcodeBuilder::$nav_tabs as $nav_tab_key => $nav_tab_settings) {
                                                    AdminLayout::render_nav_tab(
                                                        array_merge(['key' => $nav_tab_key], $nav_tab_settings)
                                                    );
                                                } ?>
                                            </nav>

                                        </div>
                                    </div>
                                    <div class="flex flex-col flex-1 rounded-md overflow-auto">
                                        <?php if (false === Accounts::instance()->has_accounts()) {
                                            include_once 'no_account_linked.php';
                                        } else {
                                            foreach (ShortcodeBuilder::$nav_tabs as $nav_tab_key => $nav_tab_settings) {
                                                AdminLayout::render_nav_panel_open(
                                                    array_merge(['key' => $nav_tab_key], $nav_tab_settings)
                                                ); ?>

                                        <div class="mx-auto">

                                            <?php foreach (ShortcodeBuilder::$fields[$nav_tab_key] as $field_key => $field) {
                                                $field['key'] = $field_key;
                                                AdminLayout::render_field($field_key, $field);
                                            } ?>
                                        </div>
                                        <?php AdminLayout::render_nav_panel_close();
                                            }
                                        } ?>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </main>
                    <!-- END Page: Module Builder -->


                </div>
            </div>

            <div class="fixed bottom-8 right-8 -z-10 opacity-75" aria-hidden="true">
                <img class="object-contain h-24" src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/dropbox_logo.svg" alt="">
            </div>
        </div>

        <!-- Modal Missing Content -->
        <div id="wpcp-modal-missing-content" class="wpcp-dialog hidden">
            <div class="relative z-20" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity"></div>
                <div class="fixed z-30 inset-0 overflow-y-auto">
                    <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                        <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full sm:p-6">
                            <div>
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-brand-color-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-5">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Configuration problem', 'wpcloudplugins'); ?></h3>
                                    <div class="my-3 p-4">
                                        <p><?php esc_html_e('This module is currently linked to a cloud account and/or folder which is no longer accessible by the plugin. To resolve this, please relink the module again to the correct folder.', 'wpcloudplugins'); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-3 sm:flex sm:justify-center">
                                <button type="button" class="wpcp-button-primary wpcp-dialog-close w-full justify-center sm:ml-3 sm:w-auto sm:text-sm"><?php esc_html_e('Close', 'wpcloudplugins'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal Missing Content -->

        <!-- Modal Raw Shortcode -->
        <div id="wpcp-modal-show-shortcode" class="wpcp-dialog hidden">
            <div class="relative z-20" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity"></div>
                <div class="fixed z-30 inset-0 overflow-y-auto">
                    <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                        <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full sm:p-6">
                            <div>
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-brand-color-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-5">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Raw Shortcode', 'wpcloudplugins'); ?></h3>
                                    <p class="my-3 p-4"><?php echo sprintf(esc_html__('To manage modules directly from the module overview, we recommend using the short version %s.', 'wpcloudplugins'), "<code>{$shortcode}</code>"); ?></p>
                                    <div class="my-3 p-4 border-2 border-gray-200 rounded-md break-all text-xs">
                                        <textarea id="wpcp-raw-shortcode-preview" rows="5" class="wpcp-input-textarea max-w-xl block w-full shadow-xs focus:ring-brand-color-700 focus:border-brand-color-700 sm:text-sm border border-gray-300 rounded-md"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-3 sm:flex sm:justify-center">
                                <button id="wpcp-button-import-shortcode" type="button" class="wpcp-button-secondary w-full justify-center sm:ml-3 sm:w-auto sm:text-sm"><?php esc_html_e('Import', 'wpcloudplugins'); ?></button>
                                <button id="wpcp-copy-raw-shortcode" type="button" class="wpcp-button-secondary w-full justify-center sm:ml-3 sm:w-auto sm:text-sm"><?php esc_html_e('Copy to clipboard', 'wpcloudplugins'); ?></button>
                                <button type="button" class="wpcp-button-primary wpcp-dialog-close w-full justify-center sm:ml-3 sm:w-auto sm:text-sm"><?php esc_html_e('Close', 'wpcloudplugins'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal Raw Shortcode -->

        <!-- Modal Review -->
        <div id="wpcp-modal-review" class="wpcp-dialog <?php echo ShortcodeBuilder::ask_for_review() ? '' : 'hidden'; ?>">
            <div class="relative z-20" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity"></div>
                <div class="fixed z-30 inset-0 overflow-y-auto">
                    <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                        <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full sm:p-6">
                            <div>
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-brand-color-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                    </svg>
                                </div>

                                <div class="mt-3 text-center sm:mt-5 enjoying-container lets-ask">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Enjoying this plugin?', 'wpcloudplugins'); ?></h3>
                                    <div class="pt-6 sm:flex sm:justify-center">
                                        <button type="button" id="enjoying-button-lets-ask-no" class="wpcp-button-secondary w-full justify-center sm:ml-3 sm:w-auto sm:text-sm"><?php esc_html_e('Not really', 'wpcloudplugins'); ?></button>
                                        <button type="button" id="enjoying-button-lets-ask-yes" class="wpcp-button-primary w-full justify-center sm:ml-3 sm:w-auto sm:text-sm" id="enjoying-button-mwah-yes"><?php esc_html_e('Yes!', 'wpcloudplugins'); ?></button>
                                    </div>
                                </div>

                                <div class="mt-3 text-center sm:mt-5 enjoying-container go-for-it hidden">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Great! How about a review, then?', 'wpcloudplugins'); ?></h3>
                                    <div class="pt-6 sm:flex sm:justify-center">
                                        <button type="button" id="enjoying-button-go-for-it-no" class="wpcp-button-secondary wpcp-dialog-close w-full justify-center sm:ml-3 sm:w-auto sm:text-sm"><?php esc_html_e('No, thanks', 'wpcloudplugins'); ?></button>
                                        <a type="button" id="enjoying-button-go-for-it-yes" class="wpcp-button-primary wpcp-dialog-close w-full justify-center sm:ml-3 sm:w-auto sm:text-sm" id="enjoying-button-mwah-yes" href="https://1.envato.market/c/1260925/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Foutofthebox-google-drive-plugin-for-wordpress%2Freviews%2F6219776" target="_blank" rel="noopener"><?php esc_html_e('Ok, sure!', 'wpcloudplugins'); ?></a>
                                    </div>
                                </div>


                                <div class="mt-3 text-center sm:mt-5 enjoying-container mwah hidden">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Would you mind giving us some feedback?', 'wpcloudplugins'); ?></h3>
                                    <div class="pt-6 sm:flex sm:justify-center">
                                        <a type="button" class="wpcp-button-secondary wpcp-dialog-close w-full justify-center sm:ml-3 sm:w-auto sm:text-sm" id="enjoying-button-mwah-yes" href="https://docs.google.com/forms/d/e/1FAIpQLSct8a8d-_7iSgcvdqeFoSSV055M5NiUOgt598B95YZIaw7LhA/viewform?usp=pp_url&entry.83709281=Out-of-the-Box+(Dropbox)&entry.450972953&entry.1149244898" target="_blank" rel="noopener"><?php esc_html_e('Ok, sure!', 'wpcloudplugins'); ?></a>
                                        <button type="button" id="enjoying-button-mwah-no" class="wpcp-button-primary wpcp-dialog-close w-full justify-center sm:ml-3 sm:w-auto sm:text-sm"><?php esc_html_e('No, thanks', 'wpcloudplugins'); ?></button>
                                    </div>
                                </div>


                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal Review -->

        <!-- Notification -->
        <div id="wpcp-notification" aria-live="assertive" class="fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:p-6 sm:items-end" style="display:none;">
            <div class="w-full flex flex-col items-center gap-4 sm:items-end">
                <div class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black/5 overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="shrink-0">
                                <!-- Heroicon name: outline/check-circle -->
                                <svg class="wpcp-notification-success h-6 w-6 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>

                                <!-- Heroicon name: outline/exclamation-circle -->
                                <svg class="wpcp-notification-failed h-6 w-6 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                <p class="wpcp-notification-message wpcp-notification-success text-sm font-medium text-gray-900" data-default-text="<?php esc_html_e('Successfully saved!', 'wpcloudplugins'); ?>"></p>
                                <p class="wpcp-notification-message wpcp-notification-failed text-sm font-medium text-red-400" data-default-text="<?php esc_html_e('Setting not saved!', 'wpcloudplugins'); ?>"></p>
                            </div>
                            <div class="ml-4 shrink-0 flex">
                                <button type="button" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-brand-color-700">
                                    <span class="sr-only"><?php esc_html_e('Close', 'wpcloudplugins'); ?></span>
                                    <!-- Heroicon name: solid/x -->
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Notification -->

    </div>
    <?php wp_print_scripts(); ?>
</body>

</html>