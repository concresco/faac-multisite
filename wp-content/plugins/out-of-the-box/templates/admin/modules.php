<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       3.0
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

// Load modules
$module_templates = Modules::get_modules_templates();
$all_modules = Modules::get_modules();

// Set the callback for selecting a module
$callback = isset($_REQUEST['callback']) ? sanitize_key($_REQUEST['callback']) : '';
$standalone = empty($callback);
$for_upload_field = (isset($_REQUEST['foruploadfield']) && '1' === $_REQUEST['foruploadfield']) ? true : false;
$modules_url = OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-getpopup&type=modules&foruploadfield='.($for_upload_field ? 1 : 0).'&callback='.esc_attr($callback);
$editor_url = OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-getpopup&type=shortcodebuilder&foruploadfield='.($for_upload_field ? 1 : 0).'&callback='.esc_attr($callback);
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
        max-height: 300px !important;
        visibility: visible;
    }
    </style>
</head>

<body class="wpcp-h-full" data-callback="<?php echo esc_attr(Helpers::select_callback($callback)); ?>">
    <div id="wpcp" class="wpcp-app wpcp-h-full hidden" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">

        <div class="relative">
            <div class="fixed inset-0 flex flex-col">
                <div class="flex grow flex-col ">
                    <!-- Logo Bar -->
                    <header class="z-50 bg-brand-color-900 flex-none h-12">
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

                    <!-- Page: All Modules -->
                    <main data-menu-panel="wpcp-all-modules" class="flex flex-col flex-1">

                        <nav class="bg-white shadow-sm z-30">
                            <div class="mx-auto px-4 py-3 sm:px-6 lg:px-8 flex flex-row justify-between h-14">
                                <h1 class="text-xl font-bold tracking-tight text-gray-900 flex justify-center items-center gap-2">
                                    <a href="<?php echo $modules_url; ?>" type="button" class="wpcp-button-icon-only inline-flex justify-center" data-dialog-id="#wpcp-modal-show-shortcode" title="<?php esc_html_e('All Modules', 'wpcloudplugins'); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                                        </svg>
                                    </a>
                                    <?php esc_html_e('Modules', 'wpcloudplugins'); ?>
                                </h1>

                                <div class="flex flex-row justify-between gap-2">
                                    <!-- Edit Default Button -->
                                    <div class="flex items-center relative">
                                        <div>
                                            <a id="wpcp-create-module" href="<?php echo $editor_url; ?>&module=<?php echo Modules::get_default_module()->ID; ?>" type="button" class="wpcp-button-secondary">
                                                <!-- Heroicon name: adjustments-horizontal -->
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="-ml-0.5 mr-2 h-4 w-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                                                </svg>
                                                <?php esc_html_e('Edit Default Preset', 'wpcloudplugins'); ?>
                                            </a>
                                        </div>
                                    </div>
                                    <!-- End Edit Default Button -->

                                    <!-- Add Module Button -->
                                    <div class="flex items-center relative">
                                        <div>
                                            <button id="wpcp-add-module" type="button" class="wpcp-button-primary wpcp-modal-open-dialog" data-dialog-id="#wpcp-modal-module-selector">
                                                <!-- Heroicon name: solid/plus-circle -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-0.5 mr-2 h-4 w-4" viewBox=" 0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                                </svg>
                                                <?php esc_html_e('Add Module', 'wpcloudplugins'); ?>
                                            </button>
                                        </div>
                                    </div>
                                    <!-- End Add Module Button -->
                                </div>

                            </div>
                        </nav>

                        <div class="flex-1 w-full mx-auto px-4 py-4 sm:px-6 lg:px-8">
                            <div class="flow-root min-w-full h-[calc(100vh-9rem)] overflow-auto">

                                <?php if (empty($all_modules)) { ?>
                                <div class="text-center rounded-lg border-2 border-dashed border-gray-300 p-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-semibold text-gray-900"><?php esc_html_e('No modules', 'wpcloudplugins'); ?></h3>
                                    <p class="mt-1 text-sm text-gray-500"><?php esc_html_e('Get started by creating a new module', 'wpcloudplugins'); ?></p>
                                </div>
                                <?php } else { ?>
                                <table class="min-w-full divide-y divide-gray-300 bg-white rounded-md shadow-(--shadow-5)">
                                    <thead class="">
                                        <tr>
                                            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50/75 backdrop-blur-sm backdrop-filter pl-3 py-2 text-left text-sm font-semibold text-gray-900 w-16 hidden lg:table-cell" data-sort-order="DESC">
                                                <div class="flex flex-row items-center gap-4">
                                                    <button type="button" class="wpcp-sort-order-button wpcp-button-icon-only inline-flex justify-center gap-4 px-2">
                                                        ID
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="wpcp-sort-order-icon-desc h-3 w-3 ">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                                                        </svg>
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="wpcp-sort-order-icon-asc h-3 w-3 hidden">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </th>
                                            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50/75 backdrop-blur-sm backdrop-filter py-2  text-center text-sm font-semibold text-gray-900">
                                                <div class="flex flex-row items-center gap-4">
                                                    <button type="button" class="wpcp-sort-order-button wpcp-button-icon-only inline-flex justify-center gap-4 px-2">
                                                        <?php esc_html_e('Type', 'wpcloudplugins'); ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="wpcp-sort-order-icon-desc h-3 w-3 hidden">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                                                        </svg>
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="wpcp-sort-order-icon-asc h-3 w-3 hidden">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </th>
                                            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50/75 backdrop-blur-sm backdrop-filter py-2  text-left text-sm font-semibold text-gray-900">

                                                <div class="flex flex-row items-center gap-4">
                                                    <button type="button" class="wpcp-sort-order-button wpcp-button-icon-only inline-flex justify-center gap-4 px-2">
                                                        <?php esc_html_e('Title', 'wpcloudplugins'); ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="wpcp-sort-order-icon-desc h-3 w-3 hidden">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                                                        </svg>
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="wpcp-sort-order-icon-asc h-3 w-3 hidden">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </th>
                                            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50/75 backdrop-blur-sm backdrop-filter py-2 text-left text-sm font-semibold text-gray-900">
                                                <div class="flex flex-row items-center gap-4">
                                                    <button type="button" class="wpcp-sort-order-button wpcp-button-icon-only inline-flex justify-center gap-4 px-2">
                                                        <?php esc_html_e('Location', 'wpcloudplugins'); ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="wpcp-sort-order-icon-desc h-3 w-3 hidden">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                                                        </svg>
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="wpcp-sort-order-icon-asc h-3 w-3 hidden">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </th>
                                            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50/75 backdrop-blur-sm backdrop-filter py-2 text-left text-sm font-semibold text-gray-900 hidden lg:table-cell ">
                                                <?php esc_html_e('Live', 'wpcloudplugins'); ?>
                                            </th>
                                            <th scope="col" class="sticky top-0 z-10 border-b border-gray-300 bg-gray-50/75 backdrop-blur-sm backdrop-filter py-2 text-left text-sm font-semibold text-gray-900"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200  ">
                                        <?php foreach ($all_modules as $module) {
                                            // Only show upload modules when the builder is used to create an upload field
                                            if ($for_upload_field && !in_array($module['type'], ['files', 'upload'])) {
                                                continue;
                                            } ?>

                                        <!--    Existing Module -->
                                        <tr class="wpcp-existing-module wpcp-select-module group text-gray-600 hover:text-gray-800 hover:bg-gray-100 cursor-pointer hover:shadow-(--shadow-5) rounded-lg" data-module-id="<?php echo $module['id']; ?>" data-shortcode="<?php echo \esc_attr(Shortcodes::encode($module['shortcode'])); ?>">
                                            <!-- Module ID -->
                                            <td class="hidden lg:table-cell whitespace-nowrap pl-3 py-2 text-xs ">
                                                #<?php echo $module['id']; ?>

                                            </td>
                                            <!-- Module Icon -->
                                            <td class="whitespace-nowrap py-2 text-sm w-12">
                                                <div class="flex h-8 w-8 shrink-0 justify-self-center self-center items-center justify-center rounded-lg <?php echo $module_templates[$module['type']]['color']; ?>">
                                                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                        <?php echo $module_templates[$module['type']]['icon']; ?>
                                                    </svg>
                                                </div>
                                                <span class="hidden"><?php echo $module['type']; ?></span>
                                            </td>
                                            <!-- Module Details -->
                                            <td class="whitespace-nowrap px-3 py-2 text-sm">
                                                <div class="min-w-0">
                                                    <div class="flex items-start gap-x-3 wpcp-edit-module">
                                                        <p class="text-sm/6 font-semibold text-gray-900 group-hover:text-brand-color-900 truncate"><?php echo $module['title']; ?></p>
                                                        <p class="mt-0.5 whitespace-nowrap rounded-md px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset ring-gray-500/10"><?php echo $module_templates[$module['type']]['title']; ?></p>
                                                    </div>
                                                    <div class="flex items-center gap-x-2 text-xs/5 ">
                                                        <p class="whitespace-nowrap underline decoration-dotted"><time datetime="<?php echo $module['date']; ?>"><?php echo $module['date_str']; ?></time></p>
                                                        &bullet;
                                                        <p class="truncate"><?php echo \sprintf(esc_html__('Created by %s', 'wpcloudplugins'), $module['author']); ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <!-- Module Location -->
                                            <td class="whitespace-nowrap px-3 py-2 text-sm max-w-36">
                                                <p class="text-sm/6 font-semibold truncate wpcp-module-path" title="<?php echo esc_attr($module['path']); ?>" data-module-dir="<?php echo esc_attr($module['dir']); ?>" data-module-path-loaded="<?php echo (!empty($module['path'])) ? '1' : '0'; ?>">
                                                    <?php if (!empty($module['path'])) {
                                                        echo $module['path'];
                                                    } else {
                                                        ?>
                                                    <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    <?php
                                                    } ?>
                                                </p>
                                                <p class="text-xs/5 truncate wpcp-module-account-email"><?php (!empty($module['account']) && !empty($module['path'])) ? esc_attr_e($module['account']->get_email()) : ''; ?></p>
                                            </td>
                                            <!-- Module Status -->
                                            <td class="hidden lg:table-cell whitespace-nowrap py-2 text-sm ">
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
                                                    <input type="checkbox" class="wpcp-toggle-status hidden" data-module-id="<?php esc_attr_e($module['id']); ?>" <?php echo $module['active'] ? 'checked' : ''; ?> />
                                                </button>
                                            </td>

                                            <!--    Module Actions -->
                                            <td class="whitespace-nowrap pr-3 py-2 text-sm text-right text-gray-500">

                                                <div class="flex flex-row items-center justify-end relative  gap-2">

                                                    <!--   Proof Details -->
                                                    <?php if ('proofing' === $module['type'] && !empty($module['edit_url'])) { ?>
                                                    <a href="<?php echo esc_attr($module['edit_url']); ?>" class="wpcp-proofing-edit flex items-center focus-within:ring-2 focus-within:ring-brand-color-700 hover:bg-gray-50 rounded-md" title="<?php \esc_attr_e('View Selections', 'wpcloudplugins'); ?>" target="_top">
                                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12" />
                                                        </svg>
                                                    </a>
                                                    <?php } ?>
                                                    <!--    End Proof Details -->

                                                    <div class="flex relative">
                                                        <button type="button" class="wpcp-menu-button wpcp-button-icon-only" title="<?php \esc_attr_e('Copy Shortcode', 'wpcloudplugins'); ?>">
                                                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                                                            </svg>
                                                        </button>

                                                        <div class="wpcp-menu-button-element absolute right-0 z-20 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-hidden overflow-hidden max-h-0" role="menu" aria-orientation="vertical" tabindex="-1">
                                                            <!--    Edit Module -->
                                                            <a type="button" class="wpcp-edit-module flex items-center gap-2 m-2 p-1 focus-within:ring-2 focus-within:ring-brand-color-700 hover:bg-gray-50 rounded-md" title="<?php \esc_attr_e('Edit Module', 'wpcloudplugins'); ?>">
                                                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                                                                </svg>
                                                                <?php \esc_attr_e('Configure', 'wpcloudplugins'); ?>
                                                            </a>
                                                            <!--    End Edit Module -->
                                                            <hr />
                                                            <!--    View Module -->
                                                            <a href="<?php echo $module['view_url']; ?>" target="_top" class="wpcp-view-module flex items-center gap-2 m-2 p-1 focus-within:ring-2 focus-within:ring-brand-color-700 hover:bg-gray-50 rounded-md" title="<?php \esc_attr_e('View Module', 'wpcloudplugins'); ?>">
                                                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                                </svg>
                                                                <?php \esc_attr_e('View', 'wpcloudplugins'); ?>
                                                            </a>
                                                            <!--    End View Module -->
                                                            <!--    Copy Shortcode -->
                                                            <a type="button" class="wpcp-copy-shortcode flex items-center gap-2 m-2 p-1 focus-within:ring-2 focus-within:ring-brand-color-700 hover:bg-gray-50 rounded-md" title="<?php \esc_attr_e('Copy Shortcode', 'wpcloudplugins'); ?>">
                                                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                                                </svg>
                                                                <?php \esc_attr_e('Copy Shortcode', 'wpcloudplugins'); ?>
                                                            </a>
                                                            <!--    End Copy Shortcode -->
                                                            <!--    Duplicate Module -->
                                                            <a type="button" class="wpcp-duplicate-module flex items-center gap-2 m-2 p-1 focus-within:ring-2 focus-within:ring-brand-color-700 hover:bg-gray-50 rounded-md" title="<?php \esc_attr_e('Duplicate', 'wpcloudplugins'); ?>">
                                                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 8.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v8.25A2.25 2.25 0 0 0 6 16.5h2.25m8.25-8.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-7.5A2.25 2.25 0 0 1 8.25 18v-1.5m8.25-8.25h-6a2.25 2.25 0 0 0-2.25 2.25v6" />
                                                                </svg>
                                                                <?php \esc_attr_e('Duplicate', 'wpcloudplugins'); ?>
                                                            </a>
                                                            <!--    End Duplicate Module -->
                                                            <!--    Delete Module -->
                                                            <a type="button" data-url="<?php echo $module['delete_url']; ?>" class="wpcp-delete-module flex items-center gap-2 m-2 p-1 focus-within:ring-2 focus-within:ring-brand-color-700 hover:bg-gray-50 rounded-md text-red-600" title="<?php \esc_attr_e('Delete Module', 'wpcloudplugins'); ?>">
                                                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                                </svg>
                                                                <?php \esc_attr_e('Delete', 'wpcloudplugins'); ?>
                                                            </a>
                                                            <!--    End Delete Module -->
                                                        </div>
                                                    </div>

                                                    <!--    Edit Module -->
                                                    <a type="button" class="wpcp-edit-module flex items-center focus-within:ring-2 focus-within:ring-brand-color-700 hover:bg-gray-50 rounded-md" title="<?php \esc_attr_e('Edit Module', 'wpcloudplugins'); ?>">
                                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                                                        </svg>
                                                    </a>
                                                    <!--    End Edit Module -->

                                                    <?php if (!$standalone) { ?>

                                                    <!--    Select Module -->
                                                    <button type="button" class="wpcp-select-module wpcp-button-icon-only" title="<?php \esc_attr_e('Select Module', 'wpcloudplugins'); ?>">
                                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                                        </svg>
                                                    </button>
                                                    <!--    End Select Module -->
                                                    <?php } ?>

                                                </div>
                                            </td>


                                            <!--   End Module Actions -->
                                        </tr>
                                        <?php } ?>
                                        <!--    End Existing Module -->
                                    </tbody>
                                </table>
                                <?php }?>

                            </div>
                        </div>
                    </main>
                    <!-- End Page: All Modules -->

                </div>
            </div>

            <div class="fixed bottom-8 right-8 -z-10 opacity-75" aria-hidden="true">
                <img class="object-contain h-24" src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/dropbox_logo.svg" alt="">
            </div>
        </div>

        <!-- Module Selector -->
        <div id="wpcp-modal-module-selector" class="wpcp-dialog hidden">
            <div class="relative z-[999999]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-500/90 transition-opacity backdrop-blur-xs"></div>
                <div class="fixed z-30 inset-0 overflow-y-auto">
                    <div class="flex items-end sm:items-center justify-center min-h-full p-4 sm:p-0 text-center">

                        <div class="relative bg-white rounded-lg m-8 px-12 py-4 text-left overflow-hidden shadow-xl transform transition-all max-w-5xl">
                            <div class="mt-3 text-center sm:mt-5">
                                <h3 class="text-xl font-bold tracking-tight text-gray-900" id="modal-title"><?php esc_html_e('Select Module', 'wpcloudplugins'); ?></h3>

                                <div class="mt-6 mb-4 sm:flex items-center justify-center">
                                    <ul class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-4 lg:grid-cols-2">
                                        <?php foreach ($module_templates as $module_key => $module_template) {
                                            // Only show upload modules when the builder is used to create an upload field
                                            if ($for_upload_field && !in_array($module_key, ['files', 'upload'])) {
                                                continue;
                                            }

                                            ?>
                                        <a href="<?php echo $editor_url; ?>&mode=<?php echo $module_key; ?>" type="button" class="col-span-1 flex rounded-md shadow-xs hover:shadow-(--shadow-5) group-hover:text-brand-color-900 cursor-pointer">
                                            <div class="flex w-12 shrink-0 items-center justify-center rounded-l-lg <?php echo $module_template['color']; ?> ">
                                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                                    <?php echo $module_template['icon']; ?>
                                                </svg>
                                            </div>
                                            <div class="flex flex-1 items-center justify-between truncate rounded-r-lg border-b border-r border-t border-gray-200 bg-white text-left h-14">
                                                <div class="flex-1 truncate px-4 py-2 text-sm">
                                                    <p class="text-lg font-medium text-gray-900 hover:text-gray-600"><?php echo $module_template['title']; ?></p>
                                                    <p class="text-gray-500 font-normal"><?php echo $module_template['description']; ?></p>
                                                </div>
                                            </div>
                                        </a>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>

                            <div class="mt-12 flex justify-center">
                                <button type="button" class="wpcp-button-primary wpcp-dialog-close inline-flex justify-center w-full sm:w-auto"><?php esc_html_e('Close'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Module Selector -->

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