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

// Exit if no permission
if (!current_user_can('manage_network_options')) {
    exit;
}

AdminLayout::set_setting_value_location('database_network');

?><div id="wpcp" class="wpcp-app hidden" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
    <div class="absolute z-10 inset-0 bg-gray-100">
        <!-- Static sidebar for desktop -->
        <div class="font-sans flex w-64 flex-col fixed md:bottom-0 md:top-8">
            <!-- Sidebar component, swap this element with another sidebar if you like -->
            <div class="flex-1 flex flex-col min-h-0 bg-gradient-to-t from-brand-color-900 to-brand-color-secondary-900">
                <div class="flex flex-col flex-grow border-r border-gray-200 pt-5 bg-white overflow-y-auto">
                    <div class="flex items-center shrink-0 px-4">
                        <img class="h-8 w-auto" src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/wpcloudplugins-logo-dark.png" alt="WP Cloud Plugins">
                    </div>
                    <div class="mt-5 flex-grow flex flex-col">
                        <nav class="flex-1 px-2 pb-4 flex flex-col gap-1">
                            <!-- Current: "bg-gray-100 text-gray-900", Default: "text-gray-600 hover:bg-gray-50 hover:text-brand-color-900" -->
                            <a href="#" data-nav-tab="wpcp-dashboard" class="wpcp-tab-active bg-gray-100 text-gray-900 hover:bg-gray-50 hover:text-brand-color-900 group active:text-brand-color-900 focus:text-brand-color-900 group flex items-center px-2 py-1 text-sm font-medium rounded-md  focus:outline-hidden focus:ring-1 focus:ring-offset-1 focus:ring-brand-color-900">
                                <!-- Heroicon name: outline/home -->
                                <svg class="text-gray-500 mr-3 shrink-0 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                <?php esc_html_e('Dashboard', 'wpcloudplugins'); ?>
                            </a>

                            <?php
              if (Core::is_network_authorized()) {
                  AdminLayout::render_nav_tab([
                      'key' => 'advanced',
                      'title' => esc_html__('Advanced', 'wpcloudplugins'),
                      'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />',
                  ]);

                  AdminLayout::render_nav_tab([
                      'key' => 'notifications',
                      'title' => esc_html__('Notifications', 'wpcloudplugins'),
                      'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />',
                  ]);
              }

AdminLayout::render_nav_tab([
    'key' => 'tools',
    'title' => esc_html__('Tools', 'wpcloudplugins'),
    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />',
]);

AdminLayout::render_nav_tab([
    'key' => 'status',
    'title' => esc_html__('Status', 'wpcloudplugins'),
    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
]);

AdminLayout::render_nav_tab([
    'key' => 'support',
    'title' => esc_html__('Support', 'wpcloudplugins').' & '.esc_html__('Docs', 'wpcloudplugins'),
    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />',
]);
?>


                        </nav>
                    </div>
                </div>

                <div class="shrink-0 flex flex-col py-2 px-4 space-y-1 bg-white">
                    <div class="flex flex-grow flex-col">
                        <a href="https://www.wpcloudplugins.com/wp-content/plugins/out-of-the-box/_documentation/index.html#releasenotes" target="_blank" rel="noopener">
                            <?php echo esc_html__('Version:', 'wpcloudplugins').' '.OUTOFTHEBOX_VERSION; ?>
                        </a>
                    </div>
                </div>

                <div class="shrink-0 flex flex-col border-t border-brand-color-900 p-2 space-y-1">
                    <div class="flex flex-grow flex-col">
                        <div class="">
                            <p class="text-lg font-semibold text-white px-2 py-1 ">
                                Other WP Cloud Plugins
                            </p>
                            <a class="text-indigo-100 hover:bg-brand-color-700 hover:text-white group flex items-center px-2 py-1 text-sm font-medium rounded-md" href="https://1.envato.market/L6yXj" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg> Google Drive</a>
                            <!-- <a class="text-indigo-100 hover:bg-brand-color-700 hover:text-white group flex items-center px-2 py-1 text-sm font-medium rounded-md" href="https://1.envato.market/vLjyO" target="_blank" rel="noopener"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
              </svg> Dropbox</a> -->
                            <a class="text-indigo-100 hover:bg-brand-color-700 hover:text-white group flex items-center px-2 py-1 text-sm font-medium rounded-md" href="https://1.envato.market/yDbyv" target="_blank" rel="noopener"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg> OneDrive</a>
                            <a class="text-indigo-100 hover:bg-brand-color-700 hover:text-white group flex items-center px-2 py-1 text-sm font-medium rounded-md" href="https://1.envato.market/M4B53" target="_blank" rel="noopener"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg> Box</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pl-64 flex flex-col flex-1">

            <main class="flex-1 bg-gray-100">
                <div class="py-6">

                    <div class="max-w-4xl px-4 sm:px-6 md:px-8 relative">

                        <!-- Dashboard Panel -->
                        <div data-nav-panel="wpcp-dashboard" class="duration-200 space-y-6">


                            <?php
                // Lost Authorization notification
                    AdminLayout::render_open_panel([
                        'title' => esc_html__('Network settings', 'wpcloudplugins'), 'accordion' => false,
                    ]);

AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Network Wide Authorization', 'wpcloudplugins'),
    'description' => esc_html__('Manage the linked accounts via this page instead of via the individual sites.', 'wpcloudplugins'),
    'key' => 'network_wide',
    'default' => false,
]);

AdminLayout::render_close_panel();
?>

                            <!-- Start Account Block -->
                            <?php
if (Core::is_network_authorized()) {
    $subtitle = sprintf(esc_html__('Manage your %s cloud accounts', 'wpcloudplugins'), 'Dropbox');
    ?>
                            <div class="bg-white shadow-(--shadow-5) overflow-hidden sm:rounded-md">
                                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                                    <div class="-ml-4 -mt-2 flex items-center justify-between flex-wrap sm:flex-nowrap">
                                        <div class="ml-4 mt-2">
                                            <h3 class="text-2xl font-semibold text-gray-900"><?php esc_html_e('Accounts', 'wpcloudplugins'); ?></h3>
                                            <div class="text-base text-gray-500 max-w-xl"><?php echo $subtitle; ?></div>
                                        </div>
                                        <div class="ml-4 mt-2 shrink-0">
                                            <?php
              if (License::is_valid()) {
                  $app = App::instance();
                  ?>
                                            <button id='wpcp-add-account-button' type="button" class="wpcp-button-primary" data-url="<?php echo $app->get_auth_url(['prompt' => 'login']); ?>">
                                                <!-- Heroicon name: solid/plus-circle -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-0.5 mr-2 h-4 w-4"" viewBox=" 0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                                </svg>
                                                <?php esc_html_e('Add Account', 'wpcloudplugins'); ?>
                                            </button>
                                            <?php
              }
    ?>
                                        </div>
                                    </div>
                                </div>

                                <ul id="wpcp-account-list" role="list" class="divide-y divide-gray-200 border-b border-gray-200 min-h-[100px] bg-no-repeat bg-center bg-[length:0px_0px] empty:bg-[length:50px_50px]" style="background-image: url('<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/dropbox_logo.svg')"><?php
                    foreach (Accounts::instance()->list_accounts() as $account_id => $account) {
                        AdminLayout::render_account_box($account, false);
                    }
    ?></ul>

                                <div class="px-4 py-5 sm:px-6">
                                    <div class="-ml-4 -mt-2 flex items-center justify-between flex-wrap sm:flex-nowrap">
                                        <div class="ml-4 mt-2">
                                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                                <a href="#" id="wpcp-read-privacy-policy" class="wpcp-link-primary wpcp-modal-open-dialog" data-dialog-id="#wpcp-modal-privacy-policy">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    <?php esc_html_e('What happens with my data when I authorize the plugin?', 'wpcloudplugins'); ?>
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <?php
}
?>
                            <!-- End Account Block -->

                            <!-- Start License Block -->
                            <?php
              $license_code = License::get();
?>
                            <div class="bg-white shadow-(--shadow-5) overflow-hidden sm:rounded-md">
                                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                                    <div class="-ml-4 -mt-2 flex items-center justify-between flex-wrap sm:flex-nowrap">
                                        <div class="ml-4 mt-2">
                                            <h3 class="text-2xl font-semibold text-gray-900"><?php esc_html_e('License', 'wpcloudplugins'); ?></h3>
                                            <div class="text-base text-gray-500 max-w-xl"><?php (false === Core::is_network_authorized()) ? esc_html_e('Licenses are managed per site individually.', 'wpcloudplugins') : esc_html_e('Thanks for registering your product!', 'wpcloudplugins'); ?></div>
                                        </div>
                                        <div class="ml-4 mt-2 shrink-0">
                                            <?php
if (!empty($license_code)) {
    ?>
                                            <a href="<?php echo admin_url('update-core.php?force-check=1'); ?>" type="button" class="wpcp-button-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                                <?php esc_html_e('Check for updates', 'wpcloudplugins'); ?>
                                            </a>
                                            <?php
}
?>
                                            <a href="https://1.envato.market/L6yXj" type="button" class="wpcp-button-secondary" target="_blank" rel="noopener">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-3 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                                <?php esc_html_e('Buy License', 'wpcloudplugins'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <?php

if (Core::is_network_authorized() && !empty($license_code)) {
    ?>
                                <ul class="divide-y divide-gray-200 border-b border-gray-200">
                                    <li class="wpcp-license" data-license-code="<?php echo $license_code; ?>">
                                        <div class="block hover:bg-gray-50">
                                            <div class="flex items-center px-4 py-4 sm:px-6">
                                                <div class="min-w-0 flex-1 flex items-center">
                                                    <div class="shrink-0">
                                                        <img class="h-12 w-12 wpcp-license-icon" src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/wpcp-logo-dark.svg" alt="">
                                                    </div>
                                                    <div class="min-w-0 flex-1 px-4 items-center">
                                                        <div>
                                                            <p class="text-xl font-medium text-brand-color-900 truncate"><code><?php echo License::mask_code($license_code); ?></code></p>
                                                            <div class="mt-2 wpcp-license-details hidden">
                                                                <div class="flex items-center justify-start space-x-4 text-sm text-gray-500">
                                                                    <div class="group flex items-center">
                                                                        <!-- Heroicon name: outline/user-circle -->
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                        </svg>
                                                                        <span class="truncate"><a href="http://themeforest.net/user/" class="wpcp-link-primary wpcp-license-buyer" target="_blank"></a></span>
                                                                    </div>
                                                                    <div class="group flex items-center space">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                        </svg>
                                                                        <span class="wpcp-license-type"></span>
                                                                    </div>
                                                                    <div class="group flex items-center space">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                                                        </svg>
                                                                        <span class="wpcp-license-support"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <button id="wpcp-deactivate-license-button" type="button" class="wpcp-button-secondary" title="<?php esc_html_e('Deactivate License', 'wpcloudplugins'); ?>">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="-h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                                        </svg>
                                                        &nbsp;<?php esc_html_e('Deactivate', 'wpcloudplugins'); ?>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="wpcp-license-error hidden">
                                                <div class="bg-red-50 border-red-400  border-l-4 p-4 mt-4">
                                                    <div class="flex">
                                                        <div class="shrink-0">
                                                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                            </svg>
                                                        </div>
                                                        <div class="ml-3">
                                                            <div>
                                                                <h3 class="text-sm font-medium text-red-800"><?php esc_html_e('Support Expired', 'wpcloudplugins'); ?></h3>
                                                                <div class="mt-2 text-sm text-red-700">
                                                                    <p class="wpcp-license-error-message"></p>
                                                                </div>
                                                                <div class="mt-4">
                                                                    <div class="-mx-2 -my-1.5 flex">
                                                                        <a href="https://1.envato.market/vLjyO" class="relative inline-flex items-center bg-red-50 px-2 py-1.5 rounded-md text-sm font-medium text-red-800 border border-solid border-red-800 hover:bg-red-100 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-offset-red-50 focus:ring-red-600" target="_blank">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-3 h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                                                                <path d="M9 4.5a.75.75 0 01.721.544l.813 2.846a3.75 3.75 0 002.576 2.576l2.846.813a.75.75 0 010 1.442l-2.846.813a3.75 3.75 0 00-2.576 2.576l-.813 2.846a.75.75 0 01-1.442 0l-.813-2.846a3.75 3.75 0 00-2.576-2.576l-2.846-.813a.75.75 0 010-1.442l2.846-.813A3.75 3.75 0 007.466 7.89l.813-2.846A.75.75 0 019 4.5zM18 1.5a.75.75 0 01.728.568l.258 1.036c.236.94.97 1.674 1.91 1.91l1.036.258a.75.75 0 010 1.456l-1.036.258c-.94.236-1.674.97-1.91 1.91l-.258 1.036a.75.75 0 01-1.456 0l-.258-1.036a2.625 2.625 0 00-1.91-1.91l-1.036-.258a.75.75 0 010-1.456l1.036-.258a2.625 2.625 0 001.91-1.91l.258-1.036A.75.75 0 0118 1.5zM16.5 15a.75.75 0 01.712.513l.394 1.183c.15.447.5.799.948.948l1.183.395a.75.75 0 010 1.422l-1.183.395c-.447.15-.799.5-.948.948l-.395 1.183a.75.75 0 01-1.422 0l-.395-1.183a1.5 1.5 0 00-.948-.948l-1.183-.395a.75.75 0 010-1.422l1.183-.395c.447-.15.799-.5.948-.948l.395-1.183A.75.75 0 0116.5 15z" />
                                                                            </svg>
                                                                            <?php esc_html_e('Renew now!', 'wpcloudplugins'); ?>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="wpcp-license-info">
                                                <div class="bg-blue-50 border-blue-400  border-l-4 p-4 mt-4">
                                                    <div class="flex">
                                                        <div class="shrink-0">
                                                            <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                                </path>
                                                            </svg>
                                                        </div>
                                                        <div class="ml-3">
                                                            <div>
                                                                <h3 class="text-sm font-medium text-blue-800"><?php esc_html_e('License terms for WordPress Networks', 'wpcloudplugins'); ?></h3>
                                                                <div class="mt-2 text-sm text-blue-700">
                                                                    <p class="wpcp-license-info-message"><?php esc_html_e('The plugin license gives permission to use the plugin on a single site. You will need separate licenses for each site if you have the plugin active on multiple sites.', 'wpcloudplugins'); ?></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <?php
}
?>

                                <div class="px-4 py-5 sm:px-6">
                                    <div class="flex flex-col gap-2">
                                        <img src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/envato-market.svg" width="200">
                                        <a href="https://1.envato.market/a4ggZ" target="_blank" rel="noopener" class="wpcp-link-primary italic ">Envato Market is the only official distributor of the WP Cloud Plugins.</a>
                                    </div>
                                </div>

                            </div>
                            <!-- End License Block -->
                        </div>
                        <!-- End Dashboard Panel -->


                        <?php
            if (Core::is_network_authorized()) {
                ?>
                        <!-- Advanced Panel -->
                        <div data-nav-panel="wpcp-advanced" class="!hidden space-y-6">

                            <?php AdminLayout::render_open_panel([
                                'title' => esc_html__('API Application', 'wpcloudplugins'), 'accordion' => false,
                            ]);

                AdminLayout::render_simple_checkbox([
                    'title' => esc_html__('Use Custom App', 'wpcloudplugins'),
                    'description' => esc_html__('For an easy configuration you can just use the default App of the plugin itself.', 'wpcloudplugins'),
                    'key' => 'dropbox_app_own',
                    'default' => false,
                    'toggle_container' => '#toggle-custom-app-options',
                ]);

                AdminLayout::render_open_toggle_container(['key' => 'toggle-custom-app-options']);

                AdminLayout::render_simple_textbox([
                    'title' => esc_html__('Dropbox App Key', 'wpcloudplugins'),
                    'description' => esc_html__('Only if you want to use your own App, insert your App Key here', 'wpcloudplugins'),
                    'placeholder' => '<--- '.esc_html__('Leave empty for easy setup', 'wpcloudplugins').' --->',
                    'default' => '',
                    'key' => 'dropbox_app_key',
                ]);

                AdminLayout::render_simple_textbox([
                    'title' => esc_html__('Dropbox App Secret', 'wpcloudplugins'),
                    'description' => esc_html__('If you want to use your own App, insert your App Secret here', 'wpcloudplugins'),
                    'placeholder' => '<--- '.esc_html__('Leave empty for easy setup', 'wpcloudplugins').' --->',
                    'default' => '',
                    'key' => 'dropbox_app_secret',
                ]);

                AdminLayout::render_notice(esc_html__('Set the OAuth 2.0 Redirect URI in your application to the following uri:', 'wpcloudplugins').'<br/><code>'.App::instance()->get_auth_uri().'</code>', 'info');

                AdminLayout::render_notice(esc_html__('We do not collect and do not have access to your personal data. See our privacy statement for more information.', 'wpcloudplugins'), 'info');

                AdminLayout::render_simple_checkbox([
                    'title' => esc_html__('Use App Folder', 'wpcloudplugins'),
                    'description' => esc_html__('Is your App configured to only access the App specific folder?', 'wpcloudplugins'),
                    'key' => 'use_app_folder',
                    'default' => false,
                ]);

                AdminLayout::render_close_toggle_container();

                AdminLayout::render_notice('<strong>Using your own Dropbox App is <u>optional</u> and <u>not recommended</u></strong>. The advantage of using your own app is limited. If you decided to create your own Dropbox App anyway, please enter your settings. In the <a href="https://florisdeleeuwnl.zendesk.com/hc/en-us/articles/115002520772" target="_blank" class="wpcp-link-primary">documentation</a> you can find how you can create a Dropbox App.', 'warning');

                AdminLayout::render_notice(esc_html__('If you encounter any issues when trying to use your own App, please fall back on the default App by disabling this setting.', 'wpcloudplugins'), 'warning');

                AdminLayout::render_close_panel();

                AdminLayout::render_open_panel(['title' => esc_html__('Dropbox Account Settings', 'wpcloudplugins'), 'accordion' => false]);

                // Dropbox Team Folders
                AdminLayout::render_simple_checkbox([
                    'title' => esc_html__('Dropbox Team Folders', 'wpcloudplugins'),
                    'description' => esc_html__('Allows you to access your Dropbox Team Folders if you are using a Business Account', 'wpcloudplugins'),
                    'key' => 'use_team_folders',
                    'notice' => esc_html__('Please check your existing Shortcodes and Manually linked Personal Folders when changing this setting. Your root folder will not longer be your Personal Folder when Team Folders are enabled', 'wpcloudplugins'),
                    'notice_class' => 'warning',
                    'default' => true,
                ]);

                AdminLayout::render_close_panel();

                ?>
                        </div>
                        <!-- End Advanced Panel -->

                        <!-- Notifications Panel -->
                        <div data-nav-panel="wpcp-notifications" class="!hidden space-y-6">

                            <?php
                // Lost Authorization notification
                    AdminLayout::render_open_panel([
                        'title' => esc_html__('Lost Authorization Notification', 'wpcloudplugins'), 'accordion' => false,
                    ]);

                // Email From address
                AdminLayout::render_simple_textbox([
                    'title' => esc_html__('Notification recipient', 'wpcloudplugins'),
                    'description' => esc_html__('If the plugin somehow loses its authorization, a notification email will be send to the following email address.', 'wpcloudplugins'),
                    'key' => 'lostauthorization_notification',
                    'default' => '',
                ]);

                AdminLayout::render_close_panel();
                ?>
                        </div>

                        <!-- End Notifications Panel -->
                        <?php
            }
?>

                        <!-- Tools Panel -->
                        <div data-nav-panel="wpcp-tools" class="!hidden space-y-6">

                            <?php // Tools -> Cache Block
AdminLayout::render_open_panel([
    'title' => esc_html__('Cache', 'wpcloudplugins'),
]);

AdminLayout::render_simple_action_button([
    'title' => esc_html__('Purge Cache', 'wpcloudplugins'),
    'description' => esc_html__('WP Cloud Plugins uses a cache to improve performance. If the plugin somehow is causing issues, try to reset the cache first.', 'wpcloudplugins'),
    'key' => 'wpcp-purge-cache-button',
    'button_text' => esc_html__('Purge', 'wpcloudplugins'),
]);

AdminLayout::render_simple_action_button([
    'title' => esc_html__('Purge Thumbnails', 'wpcloudplugins'),
    'description' => esc_html__('As the API does not provide thumbnails on the fly, the plugin will generate and store them locally. You can purge that folder if needed..', 'wpcloudplugins'),
    'key' => 'wpcp-purge-thumbnails-button',
    'button_text' => esc_html__('Purge', 'wpcloudplugins'),
]);

AdminLayout::render_close_panel();
?>
                        </div>
                        <!-- End Tools Panel -->

                        <!-- System Information Panel -->
                        <div data-nav-panel="wpcp-status" class="!hidden space-y-6">
                            <?php
    echo $this->get_system_information();
?>
                        </div>
                        <!-- End System Information Panel -->

                        <!-- Support Panel -->
                        <div data-nav-panel="wpcp-support" class="!hidden space-y-6">
                            <?php
// Support buttons
AdminLayout::render_open_panel([
    'title' => esc_html__('Support & Documentation', 'wpcloudplugins'),
    'description' => esc_html__("Get instant help for WP Cloud Plugins with all resources in one place! Can't find your answer and you want to speak to someone directly? Make sure you have an active support package, and create a ticket. We're here to help!", 'wpcloudplugins'),
]);
?>
                            <div class="mt-5">
                                <a type="button" href='<?php echo OUTOFTHEBOX_ROOTPATH; ?>/_documentation/index.html' target="_blank" class="inline-flex items-center px-4 py-2 mr-2 border border-transparent shadow-xs text-sm font-medium rounded-md text-white bg-brand-color-900 hover:bg-brand-color-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-brand-color-700">

                                    <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    <?php esc_html_e('Open Documentation', 'wpcloudplugins'); ?>
                                </a>

                                <a type="button" href='https://florisdeleeuwnl.zendesk.com/hc/en-us/articles/201845893' target="_blank" rel="noopener" class="inline-flex items-center px-4 py-2 mr-2 border border-brand-color-700 shadow-xs text-base font-medium rounded-md text-brand-color-700  hover:bg-gray-200 hover:text-brand-color-900 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-brand-color-700 sm:text-sm">

                                    <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                    </svg>
                                    <?php esc_html_e('Create support ticket', 'wpcloudplugins'); ?>
                                </a>

                            </div>
                            <?php
AdminLayout::render_close_panel();

AdminLayout::render_open_panel([
    'title' => '<i class="eva eva-play-circle-outline eva-lg"></i>&nbsp;&nbsp;Frequently asked questions',
    'accordion' => true,
]);
?>
                            <div class="mt-5 relative bg-black">
                                <div class="aspect-video"><iframe src='https://vimeo.com/showcase/9015441/embed' allowfullscreen loading="lazy" style='position:absolute;top:0;left:0;width:100%;height:100%;border:none' title="Video Instructions"></iframe></div>
                            </div>
                            <?php
AdminLayout::render_close_panel();

AdminLayout::render_open_panel([
    'title' => '<i class="eva eva-play-circle-outline eva-lg"></i>&nbsp;&nbsp;Pagebuilders',
    'accordion' => true,
]);
?>
                            <div class="mt-5 relative bg-black">
                                <div class="aspect-video"><iframe src='https://vimeo.com/showcase/9015455/embed' allowfullscreen loading="lazy" style='position:absolute;top:0;left:0;width:100%;height:100%;border:none' title="Video Instructions"></iframe></div>
                            </div>
                            <?php
AdminLayout::render_close_panel();

AdminLayout::render_open_panel([
    'title' => '<i class="eva eva-play-circle-outline eva-lg"></i>&nbsp;&nbsp;Form Plugins',
    'accordion' => true,
]);
?>
                            <div class="mt-5 relative bg-black">
                                <div class="aspect-video"><iframe src='https://vimeo.com/showcase/11404136/embed' allowfullscreen loading="lazy" style='position:absolute;top:0;left:0;width:100%;height:100%;border:none' title="Video Instructions"></iframe></div>
                            </div>
                            <?php
AdminLayout::render_close_panel();

AdminLayout::render_open_panel([
    'title' => '<i class="eva eva-play-circle-outline eva-lg"></i>&nbsp;&nbsp;WooCommerce',
    'accordion' => true,
]);
?>
                            <div class="mt-5 relative bg-black">
                                <div class="aspect-video"><iframe src='https://vimeo.com/showcase/9015490/embed' allowfullscreen loading="lazy" style='position:absolute;top:0;left:0;width:100%;height:100%;border:none' title="Video Instructions"></iframe></div>
                            </div>
                            <?php
AdminLayout::render_close_panel();

AdminLayout::render_open_panel([
    'title' => '<i class="eva eva-play-circle-outline eva-lg"></i>&nbsp;&nbsp;Integrations',
    'accordion' => true,
]);
?>
                            <div class="mt-5 relative bg-black">
                                <div class="aspect-video"><iframe src='https://vimeo.com/showcase/11404163/embed' allowfullscreen loading="lazy" style='position:absolute;top:0;left:0;width:100%;height:100%;border:none' title="Video Instructions"></iframe></div>
                            </div>
                            <?php
AdminLayout::render_close_panel();
?>

                        </div>
                        <!-- End Support Panel -->

                    </div>
                </div>
            </main>
        </div>

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

        <!-- Modal Privacy Policy -->
        <div id="wpcp-modal-privacy-policy" class="hidden wpcp-dialog">
            <div class="relative z-20" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity"></div>
                <div class="fixed z-30 inset-0 overflow-y-auto">
                    <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                        <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full sm:p-6">
                            <div>
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-brand-color-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-5">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Requested scopes and justification', 'wpcloudplugins'); ?></h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            <?php echo sprintf(esc_html__('In order to display your content stored on %s, you have to authorize it with your %s account.', 'wpcloudplugins'), 'Dropbox', 'Dropbox'); ?> <?php _e('The authorization will ask you to grant the application the following scopes:', 'wpcloudplugins'); ?>
                                        </p>
                                    </div>
                                    <div class="my-3 p-2 border-2 border-gray-200 rounded-md">
                                        <code>files.content.read</code>
                                        <p class="text-xs text-gray-500 mt-2">
                                            View content of your Dropbox files and folders. Used to display your content in the plugins modules, preview it and download the files.
                                        </p>
                                    </div>
                                    <div class="my-3 p-2 border-2 border-gray-200 rounded-md">
                                        <code>files.content.write</code>
                                        <p class="text-xs text-gray-500 mt-2">
                                            Edit content of your Dropbox files and folders. Required to create/upload/copy/rename/delete content on your Dropbox.
                                        </p>
                                    </div>
                                    <div class="my-3 p-2 border-2 border-gray-200 rounded-md">
                                        <code>sharing.write</code>
                                        <p class="text-xs text-gray-500 mt-2">
                                            View and manage your Dropbox sharing settings and collaborators. Required to create shared links or direct links to your files.
                                        </p>
                                    </div>
                                    <div class="my-3 p-2 border-2 border-gray-200 rounded-md">
                                        <code>account_info.read</code>
                                        <p class="text-xs text-gray-500 mt-2">
                                            <?php esc_html_e('Allow the plugin to see your publicly available personal info, like name and profile picture. Your name and profile picture will be displayed on this page for easy account identification.', 'wpcloudplugins'); ?>
                                        </p>
                                    </div>
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Information about the data', 'wpcloudplugins'); ?></h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            The authorization tokens will be stored, encrypted, on this server and is not accessible by the developer or any third party. When you use the Application, all communications are strictly between your server and the cloud storage service servers. The communication is encrypted and the communication will not go through WP Cloud Plugins servers. We do not collect and do not have access to your personal data.
                                        </p>
                                    </div>

                                    <div class="mt-2">
                                        <a href="https://www.wpcloudplugins.com/privacy-policy/privacy-policy-out-of-the-box/
            " class="wpcp-link-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            <?php esc_html_e('Read the full Privacy Policy if you have any further privacy concerns.', 'wpcloudplugins'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 sm:mt-6">
                                <button type="button" class="wpcp-button-primary wpcp-dialog-close inline-flex justify-center w-full">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal Privacy Policy -->

        <?php
if (Core::is_network_authorized()) {
    ?>
        <!-- Modal Activation -->
        <div id="wpcp-modal-activation" class="<?php echo License::is_valid() ? 'hidden' : ''; ?> wpcp-dialog">
            <div class="relative z-20" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-500/90 transition-opacity backdrop-blur-xs"></div>
                <div class="fixed z-30 inset-0 overflow-y-auto">
                    <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                        <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full sm:p-6">
                            <div>
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-brand-color-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-5">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Activate your license', 'wpcloudplugins'); ?></h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            <?php esc_html_e('To start using this plugin, please activate your license.', 'wpcloudplugins'); ?>
                                        </p>
                                    </div>
                                    <div class="my-3 p-2">
                                        <button id='wpcp-activate-button' type="button" class="wpcp-button-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                            <?php esc_html_e('Activate via Envato Market', 'wpcloudplugins'); ?>
                                        </button>

                                        <a href="https://1.envato.market/L6yXj" type="button" class="wpcp-button-secondary" target="_blank" rel="noopener">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-3 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <?php esc_html_e('Buy License', 'wpcloudplugins'); ?>
                                        </a>
                                    </div>
                                    <div class="mt-6 mb-4 sm:flex items-center justify-center">
                                        <div class="flex-grow flex flex-col gap-2 max-w-xl">
                                            <div class="text-sm text-gray-700 flex items-center justify-center italic "><?php esc_html_e('Or insert your license code manually:', 'wpcloudplugins'); ?></div>
                                            <div class="mt-1 flex rounded-md shadow-xs">
                                                <div class="relative flex items-stretch flex-grow focus-within:z-10">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <!-- Heroicon name: solid/key -->
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                    <input type="text" name="license_code" id="license_code" class="text-center block w-full shadow-xs focus:ring-brand-color-700 focus:border-brand-color-700 sm:text-sm border border-gray-300 p-2 rounded-none rounded-l-md" value="<?php echo License::get(); ?>" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                                                </div>
                                                <button id="wpcp-license-activate" type="button" class="-ml-px relative inline-flex items-center space-x-2 px-4 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-hidden focus:ring-1 focus:ring-brand-color-700 focus:border-brand-color-700 disabled:opacity-75" disabled="disabled">
                                                    <!-- Heroicon name: solid/lock-open -->
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                                    </svg>
                                                    <span><?php esc_html_e('Activate', 'wpcloudplugins'); ?></span>
                                                </button>
                                            </div>
                                            <p class="purchase-input-error purchase-input-error-message hidden mt-2 text-sm text-red-600 "></p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="https://florisdeleeuwnl.zendesk.com/hc/en-us/articles/360017620619" class="wpcp-link-primary" target="_blank" rel="noopener">FAQ: All about Licenses</a> |
                                        <a href="https://codecanyon.net/licenses/terms/regular" class="wpcp-link-primary" target="_blank" rel="noopener"><?php esc_html_e('Terms Regular License', 'wpcloudplugins'); ?></a> |
                                        <a href="https://codecanyon.net/licenses/terms/extended" class="wpcp-link-primary" target="_blank" rel="noopener"><?php esc_html_e('Terms Extended License', 'wpcloudplugins'); ?></a>
                                    </div>
                                    <div class="flex flex-col items-center mt-6 pt-6 text-gray-500 border-t border-gray-200 space-y-2">
                                        <img src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/envato-market.svg" width="200">
                                        <a href="https://1.envato.market/a4ggZ" target="_blank" rel="noopener" class="wpcp-link-primary italic ">Envato Market is the only official distributor of the WP Cloud Plugins.</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal Activation -->
        <?php
}
?>
    </div>
</div>