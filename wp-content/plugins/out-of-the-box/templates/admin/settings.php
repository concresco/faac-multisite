<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

use TheLion\OutoftheBox\Integrations\Slack;

defined('ABSPATH') || exit;

// Exit if no permission
if (
    !Helpers::check_user_role(Settings::get('permissions_edit_settings'))
) {
    exit;
}

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
            if (License::is_valid()) {
                AdminLayout::render_nav_tab([
                    'key' => 'layout',
                    'title' => esc_html__('Layout', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />',
                ]);

                AdminLayout::render_nav_tab([
                    'key' => 'personal-folders',
                    'title' => esc_html__('Personal Folders', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />',
                ]);

                AdminLayout::render_nav_tab([
                    'key' => 'proofing',
                    'title' => esc_html__('Review & Approve', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12" />',
                    'beta' => true,
                ]);

                AdminLayout::render_nav_tab([
                    'key' => 'integrations',
                    'title' => esc_html__('Integrations', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />',
                ]);

                AdminLayout::render_nav_tab([
                    'key' => 'notifications',
                    'title' => esc_html__('Notifications', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />',
                ]);

                AdminLayout::render_nav_tab([
                    'key' => 'permissions',
                    'title' => esc_html__('Permissions', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />',
                ]);

                AdminLayout::render_nav_tab([
                    'key' => 'restrictions',
                    'title' => esc_html__('Usage Limits', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.05 4.575a1.575 1.575 0 1 0-3.15 0v3m3.15-3v-1.5a1.575 1.575 0 0 1 3.15 0v1.5m-3.15 0 .075 5.925m3.075.75V4.575m0 0a1.575 1.575 0 0 1 3.15 0V15M6.9 7.575a1.575 1.575 0 1 0-3.15 0v8.175a6.75 6.75 0 0 0 6.75 6.75h2.018a5.25 5.25 0 0 0 3.712-1.538l1.732-1.732a5.25 5.25 0 0 0 1.538-3.712l.003-2.024a.668.668 0 0 1 .198-.471 1.575 1.575 0 1 0-2.228-2.228 3.818 3.818 0 0 0-1.12 2.687M6.9 7.575V12m6.27 4.318A4.49 4.49 0 0 1 16.35 15m.002 0h-.002" />',
                ]);

                AdminLayout::render_nav_tab([
                    'key' => 'security',
                    'title' => esc_html__('Security', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />',
                ]);

                AdminLayout::render_nav_tab([
                    'key' => 'advanced',
                    'title' => esc_html__('Advanced', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />',
                ]);

                AdminLayout::render_nav_tab([
                    'key' => 'statistics',
                    'title' => esc_html__('Statistics', 'wpcloudplugins'),
                    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />',
                ]);

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
                ]); ?>

                            <?php
            }
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
                        <div data-nav-panel="wpcp-dashboard" class=" duration-200 space-y-6">
                            <!-- Start Account Block -->
                            <?php
  $manage_per_site = (false === Core::is_network_authorized() || (Core::is_network_authorized() && true === is_network_admin()));
$subtitle = sprintf(esc_html__('Manage your %s cloud accounts', 'wpcloudplugins'), 'Dropbox');
$app = App::instance();

if (false === $manage_per_site) {
    $subtitle = sprintf(esc_html__('Authorization is managed by the Network Admin via the %sNetwork Settings%s', 'wpcloudplugins'), '<a href="'.network_admin_url('admin.php?page=OutoftheBox_network_settings').'" class="wpcp-link-primary">', '</a>');
}

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
          if (License::is_valid() && $manage_per_site) {
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
                    AdminLayout::render_account_box($account, !$manage_per_site);
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
                            <!-- End Account Block -->

                            <!-- Start License Block -->
                            <?php
          if ($manage_per_site) {
              ?>
                            <div class="bg-white shadow-(--shadow-5) overflow-hidden sm:rounded-md">
                                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                                    <div class="-ml-4 -mt-2 flex items-center justify-between flex-wrap sm:flex-nowrap">
                                        <div class="ml-4 mt-2">
                                            <h3 class="text-2xl font-semibold text-gray-900"><?php esc_html_e('License', 'wpcloudplugins'); ?></h3>
                                            <div class="text-base text-gray-500 max-w-xl"><?php esc_html_e('Thanks for registering your product!', 'wpcloudplugins'); ?></div>
                                        </div>
                                        <div class="ml-4 mt-2 shrink-0">
                                            <a href="<?php echo admin_url('update-core.php?force-check=1'); ?>" type="button" class="wpcp-button-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                                <?php esc_html_e('Check for updates', 'wpcloudplugins'); ?>
                                            </a>
                                            <a href="https://1.envato.market/vLjyO" type="button" class="wpcp-button-secondary" target="_blank">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-3 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                                <?php esc_html_e('Buy License', 'wpcloudplugins'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <?php
$license_code = License::get();

              if (!empty($license_code)) {
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

                                <?php
          } // $manage_per_site === true
?>
                            </div>
                            <!-- End License Block -->
                        </div>
                        <!-- End Dashboard Panel -->

                        <!-- Layout Panel -->
                        <div data-nav-panel="wpcp-layout" class="!hidden space-y-6">

                            <?php

AdminLayout::render_open_panel(['title' => esc_html__('General', 'wpcloudplugins'), 'accordion' => true]);

AdminLayout::render_simple_number([
    'title' => esc_html__('Border radius', 'wpcloudplugins'),
    'description' => esc_html__('The roundness (px) of various plugin elements, such as file tiles, modal dialogs and buttons.', 'wpcloudplugins'),
    'placeholder' => '10',
    'default' => '',
    'min' => 0,
    'max' => 30,
    'key' => 'layout_border_radius',
]);

AdminLayout::render_simple_number([
    'title' => esc_html__('Grid gap', 'wpcloudplugins'),
    'description' => esc_html__('The gap (px) between rows and columns of various plugin elements, such as the File Browser grid.', 'wpcloudplugins'),
    'placeholder' => '10',
    'default' => '',
    'min' => 0,
    'max' => 30,
    'key' => 'layout_gap',
]);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel(['title' => esc_html__('Color Palette', 'wpcloudplugins'), 'accordion' => true]);

// Select Theme Style
AdminLayout::render_simple_select([
    'title' => esc_html__('Theme Style', 'wpcloudplugins'),
    'description' => '',
    'type' => 'ddslickbox',
    'options' => [
        'dark' => ['title' => esc_html__('Dark', 'wpcloudplugins'), 'imagesrc' => OUTOFTHEBOX_ROOTPATH.'/css/images/skin-dark.png'],
        'light' => ['title' => esc_html__('Light', 'wpcloudplugins'), 'imagesrc' => OUTOFTHEBOX_ROOTPATH.'/css/images/skin-light.png'],
    ],
    'key' => 'colors[style]',
    'default' => 'light',
]);

// Color Palette
$colors = [
    'accent' => [
        'label' => esc_html__('Accent Color', 'wpcloudplugins'),
        'default' => '#590e54',
        'alpha' => false,
    ],
    'black' => [
        'label' => esc_html__('Black', 'wpcloudplugins'),
        'default' => '#222',
    ],
    'dark1' => [
        'label' => esc_html__('Dark 1', 'wpcloudplugins'),
        'default' => '#666666',
    ],
    'dark2' => [
        'label' => esc_html__('Dark 2', 'wpcloudplugins'),
        'default' => '#999999',
    ],
    'background-dark' => [
        'label' => esc_html__('Background color for dark theme', 'wpcloudplugins'),
        'default' => '#333333',
    ],
    'white' => [
        'label' => esc_html__('White', 'wpcloudplugins'),
        'default' => '#fff',
    ],
    'light1' => [
        'label' => esc_html__('Light 1', 'wpcloudplugins'),
        'default' => '#fcfcfc',
    ],
    'light2' => [
        'label' => esc_html__('Light 2', 'wpcloudplugins'),
        'default' => '#e8e8e8',
    ],
    'background' => [
        'label' => esc_html__('Background color for light theme', 'wpcloudplugins'),
        'default' => '#f2f2f2',
    ],
];

AdminLayout::render_color_selectors($colors);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel(['title' => esc_html__('Loading Spinner & Images', 'wpcloudplugins'), 'accordion' => true]);

// Select Loader Spinner
AdminLayout::render_simple_select([
    'title' => esc_html__('Select Loader Spinner', 'wpcloudplugins'),
    'description' => '',
    'options' => [
        'beat' => ['title' => esc_html__('Beat', 'wpcloudplugins')],
        'spinner' => ['title' => esc_html__('Spinner', 'wpcloudplugins')],
        'custom' => ['title' => esc_html__('Custom Image (selected below)', 'wpcloudplugins')],
    ],
    'key' => 'loaders[style]',
    'default' => 'beat',
]);

AdminLayout::render_image_selector([
    'title' => esc_html__('General Loader', 'wpcloudplugins'),
    'description' => esc_html__('Loading image used in the File Browser and Gallery module', 'wpcloudplugins'),
    'key' => 'loaders[loading]',
    'default' => OUTOFTHEBOX_ROOTPATH.'/css/images/wpcp-loader.svg',
]);

AdminLayout::render_image_selector([
    'title' => esc_html__('No Results', 'wpcloudplugins'),
    'description' => esc_html__('Image shown in the File Browser and Gallery module when no content is found in the opened folder.', 'wpcloudplugins'),
    'key' => 'loaders[no_results]',
    'default' => OUTOFTHEBOX_ROOTPATH.'/css/images/loader_no_results.svg',
]);

AdminLayout::render_image_selector([
    'title' => esc_html__('Access Forbidden', 'wpcloudplugins'),
    'description' => esc_html__('Image shown when a module is not accessible for the user.', 'wpcloudplugins'),
    'key' => 'loaders[protected]',
    'default' => OUTOFTHEBOX_ROOTPATH.'/css/images/loader_protected.svg',
]);

AdminLayout::render_image_selector([
    'title' => esc_html__('iFrame Loader', 'wpcloudplugins'),
    'description' => esc_html__('Loading image used for previews and iFrams.', 'wpcloudplugins'),
    'key' => 'loaders[iframe]',
    'default' => OUTOFTHEBOX_ROOTPATH.'/css/images/wpcp-loader.svg',
]);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel(['title' => esc_html__('Icon Set', 'wpcloudplugins'), 'accordion' => true]);

// Icon Set
AdminLayout::render_simple_textbox([
    'title' => esc_html__('File Browser Icon Set', 'wpcloudplugins'),
    'description' => wp_kses(sprintf('Location to the icon set you want to use for items without thumbnail. When you want to use your own set, just make a copy of the default icon set folder (<code>%s</code>) and place it in the <code>wp-content/</code> folder', OUTOFTHEBOX_ROOTPATH.'/css/icons/'), 'wpcloudplugins'),
    'placeholder' => OUTOFTHEBOX_ROOTPATH.'/css/icons/',
    'notice' => esc_html__('Modifications to the default icons set will be lost during an update.', 'wpcloudplugins'),
    'notice_class' => 'warning',
    'default' => '',
    'key' => 'icon_set',
]);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel(['title' => esc_html__('LightBox', 'wpcloudplugins'), 'accordion' => true]);

// LightBox Skin
$skin_options = [];
foreach (new \DirectoryIterator(OUTOFTHEBOX_ROOTDIR.'/vendors/iLightBox/') as $fileInfo) {
    if ($fileInfo->isDir() && !$fileInfo->isDot() && (false !== strpos($fileInfo->getFilename(), 'skin'))) {
        if (file_exists(OUTOFTHEBOX_ROOTDIR.'/vendors/iLightBox/'.$fileInfo->getFilename().'/skin.css')) {
            $selected = '';
            $skinname = str_replace('-skin', '', $fileInfo->getFilename());
            $icon = file_exists(OUTOFTHEBOX_ROOTDIR.'/vendors/iLightBox/'.$fileInfo->getFilename().'/thumb.jpg') ? OUTOFTHEBOX_ROOTPATH.'/vendors/iLightBox/'.$fileInfo->getFilename().'/thumb.jpg' : '';

            $skin_options[$skinname] = [
                'title' => ucwords(str_replace(['_', '-'], ' ', $fileInfo->getFilename())),
                'imagesrc' => $icon,
            ];
        }
    }
}

AdminLayout::render_simple_select([
    'title' => esc_html__('LightBox Skin', 'wpcloudplugins'),
    'description' => esc_html__('Select which skin you want to use for the Inline Preview.', 'wpcloudplugins'),
    'type' => 'ddslickbox',
    'options' => $skin_options,
    'key' => 'lightbox_skin',
    'default' => 'metro-black',
]);

// LightBox Lightbox Scroll
AdminLayout::render_simple_select([
    'title' => esc_html__('Lightbox Scroll', 'wpcloudplugins'),
    'description' => esc_html__("Sets path for switching windows. Possible values are 'vertical' and 'horizontal' and the default is 'vertical'.", 'wpcloudplugins'),
    'options' => [
        'horizontal' => ['title' => esc_html__('Horizontal', 'wpcloudplugins')],
        'vertical' => ['title' => esc_html__('Vertical', 'wpcloudplugins')],
    ],
    'key' => 'lightbox_path',
    'default' => 'horizontal',
]);

// LightBox Image Source
AdminLayout::render_simple_select([
    'title' => esc_html__('Image Source', 'wpcloudplugins'),
    'description' => esc_html__('Select the source of the images. Large thumbnails load fast, original files will take some time to load.', 'wpcloudplugins'),
    'options' => [
        'thumbnail' => ['title' => esc_html__('Fast - Large preview thumbnails.', 'wpcloudplugins')],
        'original' => ['title' => esc_html__('Slow - Show original files.', 'wpcloudplugins')],
    ],
    'key' => 'loadimages',
    'default' => 'thumbnail',
]);

// Show Thumbnails
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Show Thumbnails', 'wpcloudplugins'),
    'description' => esc_html__('Show thumbnails of the files inside the Lightbox.', 'wpcloudplugins'),
    'key' => 'lightbox_thumbnails',
    'default' => true,
]);

// Allow Mouse Click
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Allow Mouse Click on Image', 'wpcloudplugins'),
    'description' => esc_html__('Should people be able to access the right click context menu to e.g. save the image?', 'wpcloudplugins'),
    'key' => 'lightbox_rightclick',
    'default' => false,
]);

// LightBox Header
AdminLayout::render_simple_select([
    'title' => esc_html__('Header', 'wpcloudplugins'),
    'description' => esc_html__('When should the header containing title and action-menu be shown?', 'wpcloudplugins'),
    'options' => [
        'true' => ['title' => esc_html__('Always.', 'wpcloudplugins')],
        'click' => ['title' => esc_html__('Show after clicking on the Lightbox.', 'wpcloudplugins')],
        'mouseenter' => ['title' => esc_html__('Show when hovering over the Lightbox.', 'wpcloudplugins')],
        'false' => ['title' => esc_html__('Never.', 'wpcloudplugins')],
    ],
    'key' => 'lightbox_showheader',
    'default' => 'true',
]);

// LightBox Caption/Description
AdminLayout::render_simple_select([
    'title' => esc_html__('Caption / Description', 'wpcloudplugins'),
    'description' => esc_html__('When should the description be shown in the Gallery Lightbox?', 'wpcloudplugins'),
    'options' => [
        'true' => ['title' => esc_html__('Always.', 'wpcloudplugins')],
        'click' => ['title' => esc_html__('Show after clicking on the Lightbox.', 'wpcloudplugins')],
        'mouseenter' => ['title' => esc_html__('Show when hovering over the Lightbox.', 'wpcloudplugins')],
        'false' => ['title' => esc_html__('Never.', 'wpcloudplugins')],
    ],
    'key' => 'lightbox_showcaption',
    'default' => 'true',
]);

// LightBox Thumbnail Bar
AdminLayout::render_simple_select([
    'title' => esc_html__('Thumbnail Bar', 'wpcloudplugins'),
    'description' => esc_html__('When should the thumbnail bar be shown in the Gallery Lightbox?', 'wpcloudplugins'),
    'options' => [
        'always' => ['title' => esc_html__('Always visible.', 'wpcloudplugins')],
        'hover' => ['title' => esc_html__('Hover over thumbnail area.', 'wpcloudplugins')],
    ],
    'key' => 'lightbox_thumbnailbar',
    'default' => 'hover',
]);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel(['title' => esc_html__('Gallery', 'wpcloudplugins'), 'accordion' => true]);

// Gallery navigation bar behavior
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Hover over module to show navigation bar', 'wpcloudplugins'),
    'description' => esc_html__('Show the navigation bar when the user starts using the Gallery. If disabled, the navigation bar will always be visible.', 'wpcloudplugins'),
    'key' => 'gallery_navbar_onhover',
    'default' => true,
]);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel(['title' => esc_html__('Media Player', 'wpcloudplugins'), 'accordion' => true]);

// MediaPlayer Skin
$skin_options = [];
foreach (new \DirectoryIterator(OUTOFTHEBOX_ROOTDIR.'/skins/') as $fileInfo) {
    if ($fileInfo->isDir() && !$fileInfo->isDot()) {
        if (file_exists(OUTOFTHEBOX_ROOTDIR.'/skins/'.$fileInfo->getFilename().'/js/Player.js')) {
            $selected = '';
            $skinname = str_replace('-skin', '', $fileInfo->getFilename());
            $icon = file_exists(OUTOFTHEBOX_ROOTDIR.'/skins/'.$fileInfo->getFilename().'/Thumb.jpg') ? OUTOFTHEBOX_ROOTPATH.'/skins/'.$fileInfo->getFilename().'/Thumb.jpg' : '';

            $skin_options[$skinname] = [
                'title' => ucwords(str_replace(['_', '-'], ' ', $fileInfo->getFilename())),
                'imagesrc' => $icon,
            ];
        }
    }
}

AdminLayout::render_simple_select([
    'title' => esc_html__('Media Player Skin', 'wpcloudplugins'),
    'description' => esc_html__('Select which Media Player skin you want to use by default.', 'wpcloudplugins'),
    'type' => 'ddslickbox',
    'options' => $skin_options,
    'key' => 'mediaplayer_skin',
    'default' => 'Default_Skin',
]);

// Load Native MediaElement.js
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Load native MediaElement.js library', 'wpcloudplugins'),
    'description' => esc_html__('Is the layout of the Media Player all mixed up and is it not initiating properly? If that is the case, you might be encountering a conflict between media player libraries on your site. To resolve this, enable this setting to load the native MediaElement.js library.', 'wpcloudplugins'),
    'key' => 'mediaplayer_load_native_mediaelement',
    'default' => false,
]);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel(['title' => esc_html__('Custom CSS', 'wpcloudplugins'), 'accordion' => true]);

// Custom CSS
AdminLayout::render_simple_textarea([
    'title' => esc_html__('Custom CSS', 'wpcloudplugins'),
    'description' => esc_html__("If you want to modify the looks of the plugin slightly, you can insert here your custom CSS. Don't edit the CSS files itself, because those modifications will be lost during an update.", 'wpcloudplugins'),
    'placeholder' => '',
    'default' => '',
    'rows' => 10,
    'key' => 'custom_css',
]);

AdminLayout::render_close_panel();

?>
                        </div>
                        <!-- End Layout Panel -->

                        <!-- Personal Folders Panel -->
                        <div data-nav-panel="wpcp-personal-folders" class="!hidden space-y-6">
                            <?php AdminLayout::render_open_panel([
                                'title' => esc_html__('Global settings for AUTO-mode', 'wpcloudplugins'),
                                'description' => esc_html__('These settings are used for all modules with automatically linked Personal Folders, unless otherwise specified.', 'wpcloudplugins'),
                                'accordion' => true,
                            ]);

// Update Personal Folders
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Create Personal Folders on registration', 'wpcloudplugins'),
    'description' => esc_html__('Automatically create the Personal Folders for an user after their registration on the site.', 'wpcloudplugins'),
    'key' => 'userfolder_oncreation',
    'default' => true,
]);

// Create all Personal Folders at once
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Create all Personal Folders the 1st time a module is used', 'wpcloudplugins'),
    'description' => esc_html__('All Personal Folders are created on the first rendering of a module that has the Personal Folders feature enabled.', 'wpcloudplugins'),
    'key' => 'userfolder_onfirstvisit',
    'default' => false,
    'notice_class' => 'warning',
    'notice' => esc_html__("Creating a Personal Folder takes around 1 sec/folder. So it isn't recommended to enable this feature when you have tons of users.", 'wpcloudplugins'),
]);

// Update Personal Folders
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Update Personal Folders name after profile update', 'wpcloudplugins'),
    'description' => esc_html__('Once a user updates their profile, their Personal Folder name will be updated.', 'wpcloudplugins'),
    'key' => 'userfolder_update',
    'default' => false,
]);

// Delete Personal Folders
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Delete Personal Folders after deleting WP User', 'wpcloudplugins'),
    'description' => esc_html__("Remove users' personal folders after they're deleted.", 'wpcloudplugins'),
    'key' => 'userfolder_remove',
    'default' => false,
]);

// Share Personal Folders - Not implemented yet
// AdminLayout::render_simple_checkbox([
//     'title' => esc_html__('Share Personal Folder with user', 'wpcloudplugins'),
//     'description' => esc_html__("Add the user's email address to the sharing permissions of the created cloud folder. The user will be able to access the folder directly through the cloud, not just through this plugin.", 'wpcloudplugins'),
//     'key' => 'userfolder_oncreation_share',
//     'default' => false,
// ]);

// Personal Folders Name Template
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Name Template', 'wpcloudplugins'),
    'description' => esc_html__('Template name for automatically created Personal Folders.', 'wpcloudplugins').' '.esc_html__('The naming template can also be set per shortcode individually.', 'wpcloudplugins'),
    'placeholder' => '',
    'default' => '',
    'key' => 'userfolder_name',
    'notice_class' => 'info',
    'notice' => sprintf(esc_html__('Available placeholders: %s', 'wpcloudplugins'), '').'<code>%user_login%</code>, <code>%user_email%</code>, <code>%display_name%</code>, <code>%ID%</code>, <code>%user_role%</code>, <code>%usermeta_first_name%</code>, <code>%usermeta_last_name%</code>, <code>%usermeta_{key}%</code>, <code>%post_id%</code>, <code>%post_title%</code>, <code>%postmeta_{key}%</code>, <code>%date_{date_format}%</code>, <code>%date_i18n_{date_format}%</code>, <code>%yyyy-mm-dd%</code>, <code>%hh:mm%</code>, <code>%uniqueID%</code>, <code>%directory_separator% (/)</code>',
]);

// Personal Folders Name Template for Guests
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Name Template Prefix for anonymous users', 'wpcloudplugins'),
    'description' => sprintf(esc_html__('As anonymous users will not have user metadata that can be used in the folder name template, the plugin will generate a unique user metadata object for those users instead. By default, their folder name will be prefixed with "%s" so all their folders are grouped together. You can change that prefix here.', 'wpcloudplugins'), esc_html__('Guests', 'wpcloudplugins').' - '),
    'placeholder' => esc_html__('Guests', 'wpcloudplugins').' - ',
    'default' => esc_html__('Guests', 'wpcloudplugins').' - ',
    'key' => 'userfolder_name_guest_prefix',
]);

AdminLayout::render_notice(
    sprintf(esc_html__('You can find more information regarding the Personal Folders feature, including video instructions, in the %s[Online Documentation]%s.', 'wpcloudplugins'), '<a href="'.OUTOFTHEBOX_ROOTPATH.'/_documentation/index.html#personal-folders" target="_blank" class="wpcp-link-primary">', '</a>'),
    'info'
);

AdminLayout::render_close_panel();

AdminLayout::render_open_panel([
    'title' => esc_html__('Global settings for MANUAL-mode', 'wpcloudplugins'),
    'description' => 'This section controls the settings for manually linked modules, such as the message displayed when a user does not have access yet.',
    'accordion' => true,
]);

// Access Forbidden notice
AdminLayout::render_wpeditor(
    [
        'title' => esc_html__('"Access Forbidden" Notice', 'wpcloudplugins'),
        'description' => esc_html__("Message displayed when a user visits a module with the Personal Folders feature set to 'Manual' mode when they don't have a Personal Folder linked to their account.", 'wpcloudplugins'),
        'placeholder' => '',
        'key' => 'userfolder_noaccess',
        'wpeditor' => [
            'teeny' => true,
            'tinymce' => false,
            'textarea_rows' => 12,
            'media_buttons' => false,
        ],
    ]
);

AdminLayout::render_notice(
    sprintf(esc_html__('You can manually link users to their Personal Folder via the %s[Personal Folders]%s menu page.', 'wpcloudplugins'), '<a href="'.admin_url('admin.php?page=OutoftheBox_settings_linkusers').'" target="_blank" class="wpcp-link-primary">', '</a>'),
    'info'
);

AdminLayout::render_close_panel();

$main_account = Accounts::instance()->get_primary_account();

if (!empty($main_account)) {
    AdminLayout::render_open_panel(
        [
            'title' => esc_html__('Personal Folders in Internal modules', 'wpcloudplugins'),
            'description' => "This section controls only the personal folders of the plugin's internal modules. Use it to restrict access to File Browsers in the admin dashboard and shortcode builder.",
            'accordion' => true,
        ]
    );

    // Admin Personal Folders
    AdminLayout::render_simple_select([
        'title' => esc_html__('Enable Personal Folders in Internal modules:', 'wpcloudplugins'),
        'description' => esc_html__('Use Personal Folders in the Module Configurator and Back-End File Browser of the plugin.', 'wpcloudplugins'),
        'options' => [
            'No' => ['title' => esc_html__('No', 'wpcloudplugins'), 'toggle_container' => ''],
            'manual' => ['title' => esc_html__('Yes, I link the users manually', 'wpcloudplugins'), 'toggle_container' => ''],
            'auto' => ['title' => esc_html__('Yes, let the plugin create the User Folders for me.', 'wpcloudplugins'), 'toggle_container' => '#toggle-personal-folders-backend'],
        ],
        'key' => 'userfolder_backend',
        'default' => 'No',
        'notice' => esc_html__("This setting only restricts access to the plugin's internal modules. To enable personal folders for modules on your site, use the module's configuration panel", 'wpcloudplugins'),
        'notice_class' => 'warning',
    ]);

    AdminLayout::render_open_toggle_container(['key' => 'toggle-personal-folders-backend']);

    // Root folder for Personal Folders
    $folder_data = Settings::get('userfolder_backend_auto_root');

    $main_account = Accounts::instance()->get_primary_account();

    if ($main_account->get_authorization()->is_valid()) {
        if (empty($folder_data) || empty($folder_data['id'])) {
            App::set_current_account($main_account);

            $folder_data = [
                'account' => $main_account->get_id(),
                'id' => '/',
                'name' => '/',
                'view_roles' => ['administrator'],
            ];
        }

        $use_automatic_personal_folders = Settings::get('userfolder_backend');

        $shortcode_attr = ['singleaccount' => '0'];

        if ('auto' === $use_automatic_personal_folders) {
            $shortcode_attr = [
                'startaccount' => $folder_data['account'],
                'startpath' => $folder_data['id'],
            ];
        }

        AdminLayout::render_folder_selectbox([
            'title' => esc_html__('Location Personal Folders', 'wpcloudplugins'),
            'description' => esc_html__('Select in which folder the Personal Folders should be created.', 'wpcloudplugins').' '.esc_html__('Current selected folder', 'wpcloudplugins'),
            'key' => 'userfolder_backend_auto_root',
            'shortcode_attr' => $shortcode_attr,
            'apply_backend_personal_folder' => false,
            'inline' => false,
        ]);
    }
    // Full Access
    AdminLayout::render_tags([
        'title' => esc_html__('Full Access', 'wpcloudplugins'),
        'description' => esc_html__('By default, only Administrator users can navigate through all Personal Folders. Add other user roles if you want them to be able to see all Personal Folders.', 'wpcloudplugins'),
        'key' => 'userfolder_backend_auto_root[view_roles]',
        'default' => [],
    ]);

    AdminLayout::render_close_toggle_container();

    AdminLayout::render_close_panel();
}

?>
                        </div>
                        <!-- End Personal Folders Panel -->

                        <!-- End Review & Approve Panel -->
                        <div data-nav-panel="wpcp-proofing" class="!hidden space-y-6">

                            <?php // Restriction -> Downloads Block
AdminLayout::render_open_panel([
    'title' => esc_html__('Review & Approve', 'wpcloudplugins'),
    'description' => esc_html__('Use the Review & Approve module to allow clients to review, select and approve images or documents. Manage the global settings for this feature here.', 'wpcloudplugins'),
]);

AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Password protection by default', 'wpcloudplugins'),
    'description' => esc_html__('All new Review & Approve modules will automatically be assigned a randomly generated password.', 'wpcloudplugins'),
    'key' => 'proofing_password_by_default',
    'default' => false,
]);

AdminLayout::render_simple_number([
    'title' => esc_html__('Maximum selection size', 'wpcloudplugins'),
    'description' => esc_html__('The maximum number of items that can be selected.', 'wpcloudplugins').' '.esc_html__('Leave empty for no restriction.', 'wpcloudplugins'),
    'placeholder' => esc_attr__('Unlimited', 'wpcloudplugins'),
    'default' => '',
    'min' => 0,
    'max' => null,
    'step' => '01',
    'width' => 'w-28',
    'key' => 'proofing_max_items',
]);

AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Use labels', 'wpcloudplugins'),
    'description' => esc_html__('Allow items to be tagged with specific labels.', 'wpcloudplugins'),
    'key' => 'proofing_use_labels',
    'default' => true,
    'toggle_container' => '#toggle-proofing-labels-options',
]);

AdminLayout::render_open_toggle_container(['key' => 'toggle-proofing-labels-options']);

AdminLayout::render_tags([
    'title' => esc_html__('Available labels', 'wpcloudplugins'),
    'description' => esc_html__('Add the labels that users can add to items during the review.', 'wpcloudplugins'),
    'default' => [esc_html__('Approved', 'wpcloudplugins'), esc_html__('Needs Review', 'wpcloudplugins'), esc_html__('Pending', 'wpcloudplugins'), esc_html__('Rejected', 'wpcloudplugins')],
    'whitelist' => false,
    'key' => 'proofing_labels',
    'notice_class' => 'info',
    'notice' => esc_html__('For example:', 'wpcloudplugins').' <code>'.implode(
        '</code>, <code>',
        [
            esc_html__('Approved', 'wpcloudplugins'),
            esc_html__('Needs Review', 'wpcloudplugins'),
            esc_html__('Pending', 'wpcloudplugins'),
            esc_html__('Rejected', 'wpcloudplugins'),
            esc_html__('Favorite', 'wpcloudplugins'),
            esc_html__('Retouch Needed', 'wpcloudplugins'),
            esc_html__('Final Selection', 'wpcloudplugins'),
            esc_html__('Confidential', 'wpcloudplugins'),
            esc_html__('Requires Signature', 'wpcloudplugins'),
        ]
    ).'</code>',
]);

AdminLayout::render_close_toggle_container();

AdminLayout::render_close_panel();
?>
                        </div>
                        <!-- End Review & Approve Panel -->

                        <!-- Advanced Panel -->
                        <div data-nav-panel="wpcp-advanced" class="!hidden space-y-6">

                            <?php AdminLayout::render_open_panel([
                                'title' => esc_html__('API Application', 'wpcloudplugins'), 'accordion' => true,
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

AdminLayout::render_open_panel(['title' => esc_html__('Dropbox Account Settings', 'wpcloudplugins'), 'accordion' => true]);

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


                            <?php AdminLayout::render_open_panel(['title' => esc_html__('Advanced', 'wpcloudplugins'), 'accordion' => true]);

// Download Methods
AdminLayout::render_simple_select([
    'title' => esc_html__('Preferred Download Method', 'wpcloudplugins'),
    'description' => esc_html__('Select the method that should be used to download your files. Default is to redirect the user to a temporarily url. If you want to use your server as a proxy just set it to Download via Server. In some cases the plugin can always revert to the Download via Server method.', 'wpcloudplugins'),
    'options' => [
        'redirect' => ['title' => esc_html__('Redirect to download url', 'wpcloudplugins')],
        'proxy' => ['title' => esc_html__('Use your server as proxy', 'wpcloudplugins')],
    ],
    'key' => 'download_method',
    'default' => 'redirect',
]);

// Remember last position
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Remember last opened location', 'wpcloudplugins'),
    'description' => esc_html__('When opening a page with a previously visited File Browser module, the last opened folder location will be loaded. If you disable this setting, the plugin will always load the top folder.', 'wpcloudplugins'),
    'key' => 'remember_last_location',
    'default' => true,
]);

// Load Javascripts on all pages
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Load Javascripts on all pages', 'wpcloudplugins'),
    'description' => esc_html__('By default the plugin will only load it scripts when the shortcode is present on the page. If you are dynamically loading content via AJAX calls and the plugin does not show up, please enable this setting.', 'wpcloudplugins'),
    'key' => 'always_load_scripts',
    'default' => false,
]);

// Enable Polyfill
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Support for IE 10 & 11', 'wpcloudplugins'),
    'description' => esc_html__('Enable some polyfill javascripts that are used by the plugin but not available in old browsers. This uses the external service cdnjs.cloudflare.com/polyfill.', 'wpcloudplugins'),
    'key' => 'polyfill',
    'default' => false,
]);

// Enable Gzip compression
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Enable Gzip compression', 'wpcloudplugins'),
    'description' => esc_html__("Enables gzip-compression if the visitor's browser can handle it. This will increase the performance of the plugin if you are displaying large amounts of files and it reduces bandwidth usage as well. It uses the PHP ob_gzhandler() callback. Please use this setting with caution. Always test if the plugin still works on the Front-End as some servers are already configured to gzip content!", 'wpcloudplugins'),
    'key' => 'gzipcompression',
    'default' => false,
]);

// Delete settings on Uninstall
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Delete Modules and Settings on Uninstall', 'wpcloudplugins'),
    'description' => esc_html__('When you uninstall the plugin, what do you want to do with your modules and settings? You can save them for next time, or wipe them back to factory settings.', 'wpcloudplugins'),
    'notice' => esc_html__('When you reset the settings, the plugin will not longer be linked to your accounts, but their authorization will not be revoked', 'wpcloudplugins').'. '.esc_html__('You can revoke the authorization via the Dasbhoard tab.', 'wpcloudplugins'),
    'notice_class' => 'warning',
    'key' => 'uninstall_reset',
    'default' => true,
]);

AdminLayout::render_close_panel();

// Updates
AdminLayout::render_open_panel(['title' => esc_html__('Updates', 'wpcloudplugins'), 'accordion' => true]);

// Auto Updates
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Update automatically', 'wpcloudplugins'),
    'description' => esc_html__('When enabled, the plugin will update automatically when a new version is available. Keeping the plugin up to date is crucial as the API service is constantly changing.', 'wpcloudplugins'),
    'key' => 'auto_updates',
    'default' => false,
]);

// Beta Updates
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Beta testing', 'wpcloudplugins'),
    'description' => esc_html__('If enabled, you will be able to receive beta updates in preparation for the official release. These should be fairly stable, but will be available before the final update is ready for release.', 'wpcloudplugins'),
    'key' => 'beta_updates',
    'default' => false,
]);

AdminLayout::render_close_panel();

?>
                        </div>
                        <!-- End Advanced Panel -->

                        <!-- Security Panel -->
                        <div data-nav-panel="wpcp-security" class="!hidden space-y-6">
                            <?php

// General Security
AdminLayout::render_open_panel([
    'title' => esc_html__('General', 'wpcloudplugins'),
    'accordion' => true,
]);

// Module slugs
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Use random URLs for modules', 'wpcloudplugins'),
    'description' => esc_html__('Disable this option to use the WordPress default, where the slug is generated from the title.', 'wpcloudplugins'),
    'key' => 'modules_random_slug',
    'default' => true,
]);

AdminLayout::render_close_panel();

// AJAX Requests Checks
AdminLayout::render_open_panel([
    'title' => esc_html__('AJAX Requests Checks', 'wpcloudplugins'),
    'accordion' => true,
]);

// AJAX Cross domain check
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Cross domain verification', 'wpcloudplugins'),
    'description' => esc_html__('Let the plugin check if AJAX requests are coming from the same site, otherwise block the request for security reasons. Disable this if you have a multi-domain configuration pointing to the same WordPress site, for example if you use language-specific domains.', 'wpcloudplugins'),
    'notice' => esc_html__('Please use this setting with caution! Only disable it when really necessary.', 'wpcloudplugins'),
    'notice_class' => 'warning',
    'key' => 'ajax_domain_verification',
    'default' => true,
]);

// Nonce Validation
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Nonce Validation', 'wpcloudplugins'),
    'description' => esc_html__('The plugin uses, among others, the WordPress Nonce system to protect you against several types of attacks including CSRF. Disable this in case you are encountering conflicts with plugins that alters this system.', 'wpcloudplugins'),
    'notice' => esc_html__('Please use this setting with caution! Only disable it when really necessary.', 'wpcloudplugins'),
    'notice_class' => 'warning',
    'key' => 'nonce_validation',
    'default' => true,
]);

AdminLayout::render_close_panel();

// Cloud Content Accessibility
AdminLayout::render_open_panel([
    'title' => esc_html__('Cloud Content Accessibility', 'wpcloudplugins'),
    'accordion' => true,
]);
// Mask Account ID
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Mask Account ID', 'wpcloudplugins'),
    'description' => esc_html__("Obfuscate the cloud account IDs used in your site's source code and front-end URLs.", 'wpcloudplugins'),
    'notice' => esc_html__('Please use this setting with caution!', 'wpcloudplugins').' '.esc_html__('The generated masked Account ID uses a salt, which could break existing shared or saved links if the salt is lost. This could happen if you reinstall the plugin, for example. This is not reversible.', 'wpcloudplugins'),
    'notice_class' => 'warning',
    'key' => 'mask_account_id',
    'default' => false,
]);

AdminLayout::render_close_panel();

// Login Screen
AdminLayout::render_open_panel([
    'title' => 'Login & Password Screens',
    'accordion' => true,
]);

// Login Message
AdminLayout::render_wpeditor(
    [
        'title' => esc_html__('Login Message', 'wpcloudplugins'),
        'description' => esc_html__('Message that is displayed when the user is prompted to log in.', 'wpcloudplugins'),
        'placeholder' => '',
        'key' => 'accessibility_loginmessage',
        'wpeditor' => [
            'teeny' => true,
            'tinymce' => false,
            'textarea_rows' => 4,
            'media_buttons' => false,
        ],
    ]
);

// Login Url
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Login Url', 'wpcloudplugins'),
    'description' => esc_html__('Set a customised login URL to be used in the login button of the modules.', 'wpcloudplugins'),
    'placeholder' => wp_login_url(),
    'default' => '',
    'key' => 'accessibility_loginurl',
]);

// Password Message
AdminLayout::render_wpeditor(
    [
        'title' => esc_html__('Password Message Template', 'wpcloudplugins'),
        'description' => esc_html__('This message is displayed when a module is protected by a password and the user is prompted to enter the password.', 'wpcloudplugins'),
        'placeholder' => '',
        'key' => 'accessibility_passwordmessage',
        'wpeditor' => [
            'teeny' => true,
            'tinymce' => false,
            'textarea_rows' => 4,
            'media_buttons' => false,
        ],
    ]
);

// Lead Message
AdminLayout::render_wpeditor(
    [
        'title' => esc_html__('User Info Template', 'wpcloudplugins'),
        'description' => esc_html__('This message is displayed when a module requires users to enter user information (e.g. email address) before they are able to access the content.', 'wpcloudplugins'),
        'placeholder' => '',
        'key' => 'accessibility_leadmessage',
        'wpeditor' => [
            'teeny' => true,
            'tinymce' => false,
            'textarea_rows' => 4,
            'media_buttons' => false,
        ],
    ]
);

AdminLayout::render_close_panel();

// ReCaptcha
AdminLayout::render_open_panel([
    'title' => 'ReCaptcha V3',
    'description' => sprintf(esc_html__('reCAPTCHA protects you against spam and other types of automated abuse. With this reCAPTCHA (V3) integration module, you can block abusive downloads of your files by bots. Create your own credentials via your %s.', 'wpcloudplugins'), "<a href='https://www.google.com/recaptcha/admin' target='_blank' class='wpcp-link-primary'>reCaptcha Dashboard</a>"),
    'accordion' => true,
]);

// ReCaptcha Site Key
AdminLayout::render_simple_textbox([
    'title' => 'Site Key',
    'description' => esc_html__('The site key is used to invoke reCAPTCHA service on your site or mobile application.', 'wpcloudplugins'),
    'placeholder' => '',
    'default' => '',
    'key' => 'recaptcha_sitekey',
]);

// ReCaptcha Secret Key
AdminLayout::render_simple_textbox([
    'title' => 'Secret Key',
    'description' => esc_html__('The secret key authorizes communication between your application backend and the reCAPTCHA server to verify the user.', 'wpcloudplugins'),
    'placeholder' => '',
    'default' => '',
    'key' => 'recaptcha_secret',
]);

AdminLayout::render_notice(
    esc_html__('Make sure that you are using V3 keys. If you already are using ReCaptcha on your site, e.g. in a Form, you might need to disable the ReCaptcha of this plugin to prevent conflicts.', 'wpcloudplugins'),
    'info'
);

AdminLayout::render_close_panel();
?>
                        </div>
                        <!-- End Security Panel -->

                        <!-- Integrations Panel -->
                        <div data-nav-panel="wpcp-integrations" class="!hidden space-y-6">

                            <?php
// Plugin Integrations
AdminLayout::render_open_panel([
    'title' => 'Plugin Integrations',
    'description' => esc_html__('The plugin integrates seamlessly with a number of other well-designed WordPress plugins, allowing you to build even more powerful solutions.', 'wpcloudplugins'),
    'accordion' => true,
]);

AdminLayout::render_plugin_integrations();

AdminLayout::render_close_panel();

// Social Sharing Buttons
AdminLayout::render_open_panel([
    'title' => esc_html__('Social Sharing Buttons', 'wpcloudplugins'),
    'description' => esc_html__('Select which sharing buttons should be accessible via the sharing dialogs of the plugin.', 'wpcloudplugins'), 'accordion' => false,
]);

AdminLayout::render_share_buttons();

AdminLayout::render_close_panel();

// URL Shortener
AdminLayout::render_open_panel([
    'title' => 'URL Shortener',
    'description' => esc_html__('You can shorten the links created by the plugin with the the shorten APIs of TinyURL, Shorte.st, Rebrandly and Bit.ly.', 'wpcloudplugins'), 'accordion' => false,
]);

AdminLayout::render_simple_select([
    'title' => esc_html__('Shortlinks API', 'wpcloudplugins'),
    'description' => esc_html__('Select which Url Shortener Service you want to use for shared links.', 'wpcloudplugins'),
    'options' => [
        'None' => ['title' => esc_html__('None', 'wpcloudplugins'), 'toggle_container' => ''],
        'prettylinks' => ['title' => 'PrettyLinks', 'toggle_container' => '#toggle-prettylinks-options', 'disabled' => !Integrations::is_active('prettylinks')],
        'Tinyurl' => ['title' => 'TinyURL', 'toggle_container' => '#toggle-tinyurl-options'],
        'Shorte.st' => ['title' => 'Shorte.st', 'toggle_container' => '#toggle-shortest-options'],
        'Rebrandly' => ['title' => 'Rebrandly', 'toggle_container' => '#toggle-rebrandly-options'],
        'Bit.ly' => ['title' => 'Bit.ly', 'toggle_container' => '#toggle-bitly-options'],
    ],
    'key' => 'shortlinks',
    'default' => 'None',
]);

AdminLayout::render_open_toggle_container(['key' => 'toggle-tinyurl-options']);

// TinyURL Options
AdminLayout::render_simple_textbox([
    'title' => 'API token',
    'description' => sprintf(esc_html__('Sign up for %s and %s get your API token%s.', 'wpcloudplugins'), 'TinyURL', "<a href='https://tinyurl.com/app/settings/api' target='_blank' class='wpcp-link-primary'>", '</a>'),
    'placeholder' => '',
    'default' => '',
    'key' => 'tinyurl_apikey',
]);

AdminLayout::render_simple_textbox([
    'title' => ' Domain (optional)',
    'description' => esc_html__('Enter your custom branded domain you want to use.', 'wpcloudplugins'),
    'placeholder' => '',
    'default' => '',
    'key' => 'tinyurl_domain',
]);

AdminLayout::render_close_toggle_container();

AdminLayout::render_open_toggle_container(['key' => 'toggle-shortest-options']);

// Shorte.st Options
AdminLayout::render_simple_textbox([
    'title' => 'API token',
    'description' => sprintf(esc_html__('Sign up for %s and %s get your API token%s.', 'wpcloudplugins'), 'Shorte.st', "<a href='https://shorte.st/tools/api' target='_blank' class='wpcp-link-primary'>", '</a>'),
    'placeholder' => '',
    'default' => '',
    'key' => 'shortest_apikey',
]);

AdminLayout::render_close_toggle_container();

AdminLayout::render_open_toggle_container(['key' => 'toggle-rebrandly-options']);

// Rebrandly Options
AdminLayout::render_simple_textbox([
    'title' => 'API key',
    'description' => sprintf(esc_html__('Sign up for %s and %s get your API token%s.', 'wpcloudplugins'), 'Rebrandly', "<a href='https://app.rebrandly.com/account/api-keys' target='_blank' class='wpcp-link-primary'>", '</a>'),
    'placeholder' => '',
    'default' => '',
    'key' => 'rebrandly_apikey',
]);

AdminLayout::render_simple_textbox([
    'title' => 'Rebrandly Domain (optional)',
    'description' => esc_html__('Enter your custom branded domain you want to use.', 'wpcloudplugins'),
    'placeholder' => '',
    'default' => '',
    'key' => 'rebrandly_domain',
]);

AdminLayout::render_simple_textbox([
    'title' => 'Rebrandly WorkSpace ID (optional)',
    'description' => esc_html__('Add your WorkSpace ID if you want to use URL shortener to interact with your account in the context of your Rebrandly Workspace.', 'wpcloudplugins'),
    'placeholder' => '',
    'default' => '',
    'key' => 'rebrandly_workspace',
]);

AdminLayout::render_close_toggle_container();

AdminLayout::render_open_toggle_container(['key' => 'toggle-bitly-options']);

// Bit.ly API token
AdminLayout::render_simple_textbox([
    'title' => 'Bitly Login',
    'description' => esc_html__('Your Bitly user name.', 'wpcloudplugins'),
    'placeholder' => '',
    'default' => '',
    'key' => 'bitly_login',
]);

AdminLayout::render_simple_textbox([
    'title' => 'Bitly Access Token',
    'description' => sprintf(esc_html__('Sign up for %s and %s get your Access Token%s.', 'wpcloudplugins'), 'Bitly', "<a href='http://bit".''."ly.com/a/your_api_key' target='_blank' class='wpcp-link-primary'>", '</a>'),
    'placeholder' => '',
    'default' => '',
    'key' => 'bitly_apikey',
]);

AdminLayout::render_close_toggle_container();

AdminLayout::render_close_panel();

// Video Ads
AdminLayout::render_open_panel([
    'title' => esc_html__('Video Advertisements (IMA/VAST)', 'wpcloudplugins'),
    'description' => esc_html__('The mediaplayer of the plugin supports VAST XML advertisments to offer monetization options for your videos. You can enable advertisments for the complete site and per Media Player shortcode. Currently, this plugin only supports Linear elements with MP4', 'wpcloudplugins'),
    'accordion' => true,
]);

// VAST XML Tag Url
AdminLayout::render_simple_textbox([
    'title' => 'VAST XML Tag Url',
    'description' => esc_html__('Enter your VAST XML tag url.', 'wpcloudplugins'),
    'placeholder' => esc_html__('Leave empty to disable Ads', 'wpcloudplugins'),
    'default' => '',
    'notice' => esc_html__('If you are unable to see the example VAST url below, please make sure you do not have an ad blocker enabled. VAST url example:', 'wpcloudplugins').' >> [<a href="https://pubads.g.doubleclick.net/gampad/ads?sz=640x480&iu=/124319096/external/single_ad_samples&ciu_szs=300x250&impl=s&gdfp_req=1&env=vp&output=vast&unviewed_position_start=1&cust_params=deployment%3Ddevsite%26sample_ct%3Dskippablelinear&correlator=" rel="no-follow">Example</a>]',
    'notice_class' => 'info',
    'key' => 'mediaplayer_ads_tagurl',
]);

// Ads Role
AdminLayout::render_tags([
    'title' => esc_html__('Exclude users', 'wpcloudplugins'),
    'description' => esc_html__('Which users should we exclude from seeing ads?', 'wpcloudplugins'),
    'key' => 'mediaplayer_ads_hide_role',
    'default' => ['guest'],
]);

// Enable Skip Button
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Enable Skip Button', 'wpcloudplugins'),
    'description' => esc_html__('Allow user to skip advertisment after after the following amount of seconds have elapsed.', 'wpcloudplugins'),
    'key' => 'mediaplayer_ads_skipable',
    'default' => false,
    'toggle_container' => '#toggle-ads-skipable',
]);

AdminLayout::render_open_toggle_container(['key' => 'toggle-ads-skipable']);

// Skip time
AdminLayout::render_simple_textbox([
    'title' => 'Skip button visible after (seconds)',
    'description' => esc_html__('Allow user to skip advertisment after after the following amount of seconds have elapsed', 'wpcloudplugins'),
    'default' => 5,
    'key' => 'mediaplayer_ads_skipable_after',
]);

AdminLayout::render_close_toggle_container();

AdminLayout::render_close_panel();

// WooCommerce Integrations
if (Integrations::is_active('woocommerce')) {
    AdminLayout::render_open_panel([
        'title' => 'Woocommerce',
        'key' => 'woocommerce-settings',
        'description' => esc_html__('Easily manage your Digital Download and Order Uploads in the cloud.', 'wpcloudplugins'),
        'accordion' => true,
    ]);

    // Upload Box locations
    AdminLayout::render_simple_checkbox_button_group([
        'title' => esc_html__('Upload locations', 'wpcloudplugins'),
        'description' => esc_html__('Select the locations where you want to show the upload box in woocommerce products.', 'wpcloudplugins'),
        'key' => 'woocommerce_upload_locations',
        'options' => [
            'product' => ['title' => esc_html__('Product Page', 'wpcloudplugins')],
            'cart' => ['title' => esc_html__('Cart', 'wpcloudplugins')],
            'checkout' => ['title' => esc_html__('Checkout', 'wpcloudplugins')],
            'order' => ['title' => esc_html__('Order Details', 'wpcloudplugins')],
        ],
        'default' => ['order'],
    ]);

    AdminLayout::render_simple_select([
        'title' => esc_html__('Checkout Upload location', 'wpcloudplugins'),
        'description' => esc_html__('Choose where the upload box should appear on the checkout page.', 'wpcloudplugins'),
        'options' => [
            'woocommerce_before_checkout_form' => ['title' => esc_html__('Before checkout form', 'wpcloudplugins')],
            'woocommerce_checkout_billing' => ['title' => esc_html__('Section: billing', 'wpcloudplugins')],
            'woocommerce_checkout_shipping' => ['title' => esc_html__('Section: shipping', 'wpcloudplugins')],
            'woocommerce_checkout_order_review' => ['title' => esc_html__('Section: order review ', 'wpcloudplugins')],
            'woocommerce_checkout_before_customer_details' => ['title' => esc_html__('Before: customer details', 'wpcloudplugins')],
            'woocommerce_checkout_after_customer_details' => ['title' => esc_html__('After: customer details', 'wpcloudplugins')],
            'woocommerce_review_order_before_payment' => ['title' => esc_html__('Before: Payment', 'wpcloudplugins')],
            'woocommerce_review_order_after_payment' => ['title' => esc_html__('After: Payment', 'wpcloudplugins')],
            'woocommerce_before_order_notes' => ['title' => esc_html__('Before: Order notes', 'wpcloudplugins')],
            'woocommerce_after_order_notes' => ['title' => esc_html__('After: Order notes', 'wpcloudplugins')],
        ],
        'key' => 'woocommerce_checkout_section',
        'default' => 'woocommerce_after_order_notes',
    ]);

    // Temporarily Upload location
    $folder_data = Settings::get('woocommerce_temporary_upload_folder');

    $main_account = Accounts::instance()->get_primary_account();

    if (!empty($main_account) && $main_account->get_authorization()->is_valid()) {
        $shortcode_attr = [
            'singleaccount' => '0',
            'startaccount' => $folder_data['account'] ?? null,
            'startpath' => $folder_data['id'] ?? null,
        ];
        AdminLayout::render_folder_selectbox([
            'title' => esc_html__('Temporary Upload Folder', 'wpcloudplugins'),
            'description' => esc_html__('Order uploads can be uploaded to an order-specific folder. If the uploads are sent from the product, cart or checkout page, the files will be stored in that specific folder until after checkout.', 'wpcloudplugins'),
            'key' => 'woocommerce_temporary_upload_folder',
            'shortcode_attr' => $shortcode_attr,
            'apply_backend_personal_folder' => false,
            'inline' => false,
            'resolve_userfolder' => false,
        ]);
    }

    AdminLayout::render_close_panel();
}

// Slack Integrations
if (Integrations::is_active('slack')) {
    AdminLayout::render_open_panel([
        'title' => 'Slack',
        'key' => 'slack-settings',
        'description' => esc_html__('Connecting with Slack can significantly enhance your management workflow. This integration allows you to receive real-time notifications about important plugin events directly in your Slack channels.', 'wpcloudplugins').'<br/><br/>'.\sprintf("<a href='%s' class='button' target='_blank'>>>> Get webhook (API documentation)</a>", 'https://api.slack.com/messaging/webhooks'),
        'accordion' => true,
    ]);

    // Slack Endpoint URL
    AdminLayout::render_simple_textbox([
        'title' => esc_html__('Webhook URLs for Your Workspace', 'wpcloudplugins'),
        'description' => esc_html__('The webhook url where the notification JSON data will be send to.', 'wpcloudplugins'),
        'placeholder' => 'https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXXXXXX',
        'default' => '',
        'key' => 'slack_endpoint_url',
    ]);

    // Slack Events
    AdminLayout::render_simple_checkbox_button_group([
        'title' => esc_html__('Notifications triggers', 'wpcloudplugins'),
        'description' => esc_html__('Select the events for which you want notifications to be sent.', 'wpcloudplugins'),
        'key' => 'slack_event_types',
        'options' => [
            'outofthebox_previewed_entry' => ['title' => esc_html__('File previewed', 'wpcloudplugins')],
            'outofthebox_edited_entry' => ['title' => esc_html__('File edited', 'wpcloudplugins')],
            'outofthebox_downloaded_entry' => ['title' => esc_html__('File downloaded', 'wpcloudplugins')],
            'outofthebox_streamed_entry' => ['title' => esc_html__('File streamed', 'wpcloudplugins')],
            'outofthebox_created_link_to_entry' => ['title' => esc_html__('File shared', 'wpcloudplugins')],
            'outofthebox_renamed_entry' => ['title' => esc_html__('File renamed', 'wpcloudplugins')],
            'outofthebox_deleted_entry' => ['title' => esc_html__('File deleted', 'wpcloudplugins')],
            'outofthebox_created_entry' => ['title' => esc_html__('File created', 'wpcloudplugins')],
            'outofthebox_moved_entry' => ['title' => esc_html__('File moved', 'wpcloudplugins')],
            'outofthebox_updated_description' => ['title' => esc_html__('File description added', 'wpcloudplugins')],
            'outofthebox_uploaded_entry' => ['title' => esc_html__('File Uploaded', 'wpcloudplugins')],
            'outofthebox_uploaded_failed' => ['title' => esc_html__('File upload failed', 'wpcloudplugins')],
        ],
        'default' => ['outofthebox_uploaded_entry', 'outofthebox_uploaded_failed'],
    ]);

    AdminLayout::render_wpeditor(
        [
            'title' => esc_html__('Slack Blocks template (JSON)', 'wpcloudplugins'),
            'description' => sprintf('Compose your notification layout using <a href="%s" target="_blank">Slack Blocks</a>.', 'https://api.slack.com/block-kit/building'),
            'placeholder' => '{}',
            'default' => Slack::get_default_block_template(),
            'key' => 'slack_blocks',
            'wpeditor' => [
                'teeny' => true,
                'tinymce' => false,
                'textarea_rows' => 30,
                'media_buttons' => false,
                'quicktags' => false,
            ],
        ],
        true,
        [
            'type' => 'json',
            'codemirror' => [
                'foldGutter' => true,
                'indentUnit' => 4,
                'lineWrapping' => true,
                'refresh' => true,
                'autoRefresh' => true,
            ],
        ]
    );

    AdminLayout::render_notice(
        esc_html__(
            'Available placeholders:',
            'wpcloudplugins'
        )
                    .' <code>%description%</code>,
                    <code>%data.entry.id%</code>,
                    <code>%data.entry.name%</code>,
                    <code>%data.entry.mimetype%</code>,
                    <code>%data.entry.size%</code>,
                    <code>%data.entry.icon%</code>,
                    <code>%data.entry.description%</code>,
                    <code>%data.entry.thumbnail%</code>,
                    <code>%data.entry.preview_url%</code>,
                    <code>%data.entry.parent_path%</code>,
                    <code>%data.account.id%</code>,
                    <code>%data.account.name%</code>,
                    <code>%data.account.email%</code>,
                    <code>%data.account.image%</code>,
                    <code>%user.id%</code>,
                    <code>%user.user_login%</code>,
                    <code>%user.user_nicename%</code>,
                    <code>%user.user_email%</code>,
                    <code>%user.display_name%</code>,
                    <code>%page%</code>',
        'info'
    );

    AdminLayout::render_simple_action_button([
        'title' => esc_html__('Test notification', 'wpcloudplugins'),
        'description' => esc_html__('Send a test notification to the channel webhook url using the notification block template.', 'wpcloudplugins'),
        'key' => 'wpcp-slack-test-button',
        'button_text' => esc_html__('Test', 'wpcloudplugins'),
    ]);

    AdminLayout::render_close_panel();
}

// Form Integrations
AdminLayout::render_open_panel([
    'title' => 'Form Integrations',
    'key' => 'form-settings',
    'description' => esc_attr__('Want more flexibility than the upload box can offer? Use a popular form integration that lets you create the perfect form!', 'wpcloudplugins'),
    'accordion' => true,
]);

// Form HTML Template
AdminLayout::render_wpeditor(
    [
        'title' => esc_html__('Upload List Template', 'wpcloudplugins').' (HTML)',
        'description' => esc_attr__('This HTML template is used to list uploads in form submissions and email confirmations. Use valid HTML for emails.', 'wpcloudplugins'),
        'placeholder' => '',
        'default' => Integrations\FormHelpers::get_default_html_template(),
        'key' => 'forms_upload_list_html_template',
        'wpeditor' => [
            'teeny' => true,
            'tinymce' => false,
            'textarea_rows' => 12,
            'media_buttons' => false,
            'quicktags' => false,
        ],
    ],
    true,
    [
        'type' => 'htmlmixed',
        'codemirror' => [
            'foldGutter' => true,
            'indentUnit' => 0,
            'lineWrapping' => false,
            'refresh' => true,
            'autoRefresh' => true,
        ],
    ]
);

// Form Text Template
AdminLayout::render_wpeditor(
    [
        'title' => esc_html__('Upload List Template', 'wpcloudplugins').' (text)',
        'description' => esc_attr__('This is the text version of the upload list for when HTML is not supported.', 'wpcloudplugins'),
        'placeholder' => '',
        'default' => Integrations\FormHelpers::get_default_text_template(),
        'key' => 'forms_upload_list_text_template',
        'wpeditor' => [
            'teeny' => true,
            'tinymce' => false,
            'textarea_rows' => 4,
            'media_buttons' => false,
            'quicktags' => false,
        ],
    ],
    true,
    [
        'type' => 'htmlmixed',
        'codemirror' => [
            'foldGutter' => true,
            'indentUnit' => 0,
            'lineWrapping' => false,
            'refresh' => true,
            'autoRefresh' => true,
        ],
    ]
);

AdminLayout::render_notice(
    esc_html__(
        'Available placeholders:',
        'wpcloudplugins'
    )
                .' <code>%site_name%</code>,
                <code>%number_of_files%</code>,
                <code>%user_name%</code>,
                <code>%user_email%</code>,
                <code>%usermeta_first_name%</code>,
                <code>%usermeta_last_name%</code>,
                <code>%file_name%</code>,
                <code>%file_size%</code>,
                <code>%file_icon%</code>,
                <code>%file_relative_path%</code>,
                <code>%file_absolute_path%</code>,
                <code>%file_cloud_preview_url%</code>,
                <code>%file_cloud_shared_url%</code>,
                <code>%folder_name%</code>,
                <code>%folder_relative_path%</code>,
                <code>%folder_absolute_path%</code>,
                <code>%folder_cloud_preview_url%</code>,
                <code>%folder_cloud_shared_url%</code>,
                <code>%ip%</code>,
                <code>%location%</code>,
                <code>%page%</code>',
    'info'
);

AdminLayout::render_close_panel();

?>
                        </div>
                        <!-- End Integrations Panel -->

                        <!-- Notifications Panel -->
                        <div data-nav-panel="wpcp-notifications" class="!hidden space-y-6">

                            <?php
  // Email Sender Information
  AdminLayout::render_open_panel([
      'title' => esc_html__('Email Sender Information', 'wpcloudplugins'), 'accordion' => true,
  ]);

// Email From Name
AdminLayout::render_simple_textbox([
    'title' => esc_html__('From Name', 'wpcloudplugins'),
    'description' => esc_html__('Enter the name you would like the notification email sent from, or use one of the available placeholders.', 'wpcloudplugins'),
    'key' => 'notification_from_name',
    'default' => '',
]);

// Email From address
AdminLayout::render_simple_textbox([
    'title' => esc_html__('From email address', 'wpcloudplugins'),
    'description' => esc_html__('Enter an authorized email address you would like the notification email sent from. To avoid deliverability issues, always use your site domain in the from email.', 'wpcloudplugins'),
    'key' => 'notification_from_email',
    'default' => '',
]);

// Email Reply-to address
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Reply-to address', 'wpcloudplugins'),
    'description' => esc_html__('Enter an email address when you want a reply on the notification to go to an email address that is different than the From: address.', 'wpcloudplugins'),
    'key' => 'notification_replyto_email',
    'default' => '',
]);

AdminLayout::render_close_panel();

// Lost Authorization notification
if (false === Core::is_network_authorized()) {
    AdminLayout::render_open_panel([
        'title' => esc_html__('Lost Authorization Notification', 'wpcloudplugins'), 'accordion' => true,
    ]);

    // Email From address
    AdminLayout::render_simple_textbox([
        'title' => esc_html__('Notification recipient', 'wpcloudplugins'),
        'description' => esc_html__('If the plugin somehow loses its authorization, a notification email will be send to the following email address.', 'wpcloudplugins'),
        'key' => 'lostauthorization_notification',
        'default' => '',
    ]);

    AdminLayout::render_close_panel();
}

// Download Notifications
AdminLayout::render_open_panel([
    'title' => esc_html__('Download Notifications', 'wpcloudplugins'), 'accordion' => true,
]);

// Download Email Subject
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Email Subject', 'wpcloudplugins'),
    'description' => esc_html__('Subject for this email notification.', 'wpcloudplugins'),
    'key' => 'download_template_subject',
    'default' => '',
]);

// Download ZIP Email Subject
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Email Subject for ZIP downloads', 'wpcloudplugins'),
    'description' => esc_html__('Subject for this email notification.', 'wpcloudplugins'),
    'key' => 'download_template_subject_zip',
    'default' => '',
]);

AdminLayout::render_wpeditor(
    [
        'title' => esc_html__('Email Body (HTML)', 'wpcloudplugins'),
        'description' => '',
        'placeholder' => '',
        'key' => 'download_template',
        'wpeditor' => [
            'teeny' => true,
            'tinymce' => false,
            'textarea_rows' => 9,
            'media_buttons' => false,
            'quicktags' => false,
        ],
    ],
    true,
    [
        'type' => 'htmlmixed',
        'codemirror' => [
            'foldGutter' => true,
            'indentUnit' => 0,
            'lineWrapping' => true,
            'refresh' => true,
            'autoRefresh' => true,
        ],
    ]
);

AdminLayout::render_notice(
    esc_html__(
        'Available placeholders:',
        'wpcloudplugins'
    )
                .' <code>%site_name%</code>, 
                <code>%number_of_files%</code>, 
                <code>%user_name%</code>, 
                <code>%user_email%</code>, 
                <code>%usermeta_first_name%</code>, 
                <code>%usermeta_last_name%</code>,                 
                <code>%recipient_name%</code>, 
                <code>%recipient_email%</code>, 
                <code>%recipient_firstname%</code>, 
                <code>%recipient_lastname%</code>, 
                <code>%admin_email%</code>,
                <code>%account_email%</code>,   
                <code>%file_name%</code>, 
                <code>%file_size%</code>, 
                <code>%file_icon%</code>, 
                <code>%file_relative_path%</code>, 
                <code>%file_absolute_path%</code>, 
                <code>%file_cloud_preview_url%</code>, 
                <code>%file_cloud_shared_url%</code>, 
                <code>%file_download_url%</code>,
                <code>%folder_name%</code>,
                <code>%folder_relative_path%</code>,
                <code>%folder_absolute_path%</code>,
                <code>%folder_url%</code>,
                <code>%ip%</code>, 
                <code>%location%</code>',
    'info'
);

AdminLayout::render_close_panel();

// Upload Notifications
AdminLayout::render_open_panel([
    'title' => esc_html__('Upload Notifications', 'wpcloudplugins'), 'accordion' => true,
]);

// Upload Email Subject
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Email Subject', 'wpcloudplugins'),
    'description' => esc_html__('Subject for this email notification.', 'wpcloudplugins'),
    'key' => 'upload_template_subject',
    'default' => '',
]);

AdminLayout::render_wpeditor(
    [
        'title' => esc_html__('Email Body (HTML)', 'wpcloudplugins'),
        'description' => '',
        'placeholder' => '',
        'key' => 'upload_template',
        'wpeditor' => [
            'teeny' => true,
            'tinymce' => false,
            'textarea_rows' => 9,
            'media_buttons' => false,
            'quicktags' => false,
        ],
    ],
    true,
    [
        'type' => 'htmlmixed',
        'codemirror' => [
            'foldGutter' => true,
            'indentUnit' => 0,
            'lineWrapping' => true,
            'refresh' => true,
            'autoRefresh' => true,
        ],
    ]
);

AdminLayout::render_notice(
    esc_html__(
        'Available placeholders:',
        'wpcloudplugins'
    )
                .' <code>%site_name%</code>, 
                <code>%number_of_files%</code>, 
                <code>%user_name%</code>, 
                <code>%user_email%</code>, 
                <code>%usermeta_first_name%</code>, 
                <code>%usermeta_last_name%</code>,                 
                <code>%recipient_name%</code>, 
                <code>%recipient_email%</code>, 
                <code>%recipient_firstname%</code>, 
                <code>%recipient_lastname%</code>, 
                <code>%admin_email%</code>,
                <code>%account_email%</code>,   
                <code>%file_name%</code>, 
                <code>%file_size%</code>, 
                <code>%file_icon%</code>, 
                <code>%file_relative_path%</code>, 
                <code>%file_absolute_path%</code>, 
                <code>%file_cloud_preview_url%</code>, 
                <code>%file_cloud_shared_url%</code>, 
                <code>%file_download_url%</code>,
                <code>%folder_name%</code>,
                <code>%folder_relative_path%</code>,
                <code>%folder_absolute_path%</code>,
                <code>%folder_url%</code>,
                <code>%ip%</code>, 
                <code>%location%</code>',
    'info'
);

AdminLayout::render_close_panel();

// Delete Notifications
AdminLayout::render_open_panel([
    'title' => esc_html__('Delete Notifications', 'wpcloudplugins'), 'accordion' => true,
]);

// Upload Email Subject
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Email Subject', 'wpcloudplugins'),
    'description' => esc_html__('Subject for this email notification.', 'wpcloudplugins'),
    'key' => 'delete_template_subject',
    'default' => '',
]);

AdminLayout::render_wpeditor(
    [
        'title' => esc_html__('Email Body (HTML)', 'wpcloudplugins'),
        'description' => '',
        'placeholder' => '',
        'key' => 'delete_template',
        'wpeditor' => [
            'teeny' => true,
            'tinymce' => false,
            'textarea_rows' => 9,
            'media_buttons' => false,
            'quicktags' => false,
        ],
    ],
    true,
    [
        'type' => 'htmlmixed',
        'codemirror' => [
            'foldGutter' => true,
            'indentUnit' => 0,
            'lineWrapping' => true,
            'refresh' => true,
            'autoRefresh' => true,
        ],
    ]
);

AdminLayout::render_notice(
    esc_html__(
        'Available placeholders:',
        'wpcloudplugins'
    )
                .' <code>%site_name%</code>, 
                <code>%number_of_files%</code>, 
                <code>%user_name%</code>, 
                <code>%user_email%</code>, 
                <code>%usermeta_first_name%</code>, 
                <code>%usermeta_last_name%</code>,                 
                <code>%recipient_name%</code>, 
                <code>%recipient_email%</code>, 
                <code>%recipient_firstname%</code>, 
                <code>%recipient_lastname%</code>, 
                <code>%admin_email%</code>,
                <code>%account_email%</code>,   
                <code>%file_name%</code>, 
                <code>%file_size%</code>, 
                <code>%file_icon%</code>, 
                <code>%file_relative_path%</code>, 
                <code>%file_absolute_path%</code>, 
                <code>%file_cloud_preview_url%</code>, 
                <code>%file_cloud_shared_url%</code>, 
                <code>%file_download_url%</code>,
                <code>%folder_name%</code>,
                <code>%folder_relative_path%</code>,
                <code>%folder_absolute_path%</code>,
                <code>%folder_url%</code>,
                <code>%ip%</code>, 
                <code>%location%</code>',
    'info'
);

AdminLayout::render_close_panel();

// Move Notifications
AdminLayout::render_open_panel([
    'title' => esc_html__('Move Notifications', 'wpcloudplugins'), 'accordion' => true,
]);

// Move Email Subject
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Email Subject', 'wpcloudplugins'),
    'description' => esc_html__('Subject for this email notification.', 'wpcloudplugins'),
    'key' => 'move_template_subject',
    'default' => '',
]);

AdminLayout::render_wpeditor(
    [
        'title' => esc_html__('Email Body (HTML)', 'wpcloudplugins'),
        'description' => '',
        'placeholder' => '',
        'key' => 'move_template',
        'wpeditor' => [
            'teeny' => true,
            'tinymce' => false,
            'textarea_rows' => 9,
            'media_buttons' => false,
            'quicktags' => false,
        ],
    ],
    true,
    [
        'type' => 'htmlmixed',
        'codemirror' => [
            'foldGutter' => true,
            'indentUnit' => 0,
            'lineWrapping' => true,
            'refresh' => true,
            'autoRefresh' => true,
        ],
    ]
);

AdminLayout::render_notice(
    esc_html__(
        'Available placeholders:',
        'wpcloudplugins'
    )
                .' <code>%site_name%</code>, 
                <code>%number_of_files%</code>, 
                <code>%user_name%</code>, 
                <code>%user_email%</code>, 
                <code>%usermeta_first_name%</code>, 
                <code>%usermeta_last_name%</code>,                 
                <code>%recipient_name%</code>, 
                <code>%recipient_email%</code>, 
                <code>%recipient_firstname%</code>, 
                <code>%recipient_lastname%</code>, 
                <code>%admin_email%</code>, 
                <code>%account_email%</code>,  
                <code>%file_name%</code>, 
                <code>%file_size%</code>, 
                <code>%file_icon%</code>, 
                <code>%file_relative_path%</code>,
                <code>%file_absolute_path%</code>,
                <code>%file_cloud_download_url%</code>, 
                <code>%file_cloud_preview_url%</code>, 
                <code>%file_cloud_shared_url%</code>, 
                <code>%file_download_url%</code>,
                <code>%folder_name%</code>,
                <code>%folder_relative_path%</code>,
                <code>%folder_absolute_path%</code>,
                <code>%folder_url%</code>,
                <code>%ip%</code>, 
                <code>%location%</code>',
    'info'
);

AdminLayout::render_close_panel();

// Copy Notifications
AdminLayout::render_open_panel([
    'title' => esc_html__('Copy Notifications', 'wpcloudplugins'), 'accordion' => true,
]);

// Copy Email Subject
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Email Subject', 'wpcloudplugins'),
    'description' => esc_html__('Subject for this email notification.', 'wpcloudplugins'),
    'key' => 'copy_template_subject',
    'default' => '',
]);

AdminLayout::render_wpeditor(
    [
        'title' => esc_html__('Email Body (HTML)', 'wpcloudplugins'),
        'description' => '',
        'placeholder' => '',
        'key' => 'copy_template',
        'wpeditor' => [
            'teeny' => true,
            'tinymce' => false,
            'textarea_rows' => 9,
            'media_buttons' => false,
            'quicktags' => false,
        ],
    ],
    true,
    [
        'type' => 'htmlmixed',
        'codemirror' => [
            'foldGutter' => true,
            'indentUnit' => 0,
            'lineWrapping' => true,
            'refresh' => true,
            'autoRefresh' => true,
        ],
    ]
);
AdminLayout::render_notice(
    esc_html__(
        'Available placeholders:',
        'wpcloudplugins'
    )
                .' <code>%site_name%</code>, 
                <code>%number_of_files%</code>, 
                <code>%user_name%</code>, 
                <code>%user_email%</code>, 
                <code>%usermeta_first_name%</code>, 
                <code>%usermeta_last_name%</code>,                 
                <code>%recipient_name%</code>, 
                <code>%recipient_email%</code>, 
                <code>%recipient_firstname%</code>, 
                <code>%recipient_lastname%</code>, 
                <code>%admin_email%</code>, 
                <code>%account_email%</code>,  
                <code>%file_name%</code>, 
                <code>%file_size%</code>, 
                <code>%file_icon%</code>, 
                <code>%file_relative_path%</code>,
                <code>%file_absolute_path%</code>,
                <code>%file_cloud_download_url%</code>, 
                <code>%file_cloud_preview_url%</code>, 
                <code>%file_cloud_shared_url%</code>, 
                <code>%file_download_url%</code>,
                <code>%folder_name%</code>,
                <code>%folder_relative_path%</code>,
                <code>%folder_absolute_path%</code>,
                <code>%folder_url%</code>,
                <code>%ip%</code>, 
                <code>%location%</code>',
    'info'
);

AdminLayout::render_close_panel();

// Proof Approval Notifications
AdminLayout::render_open_panel([
    'title' => esc_html__('Approval Notifications', 'wpcloudplugins'), 'accordion' => true,
]);

// Upload Email Subject
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Email Subject', 'wpcloudplugins'),
    'description' => esc_html__('Subject for this email notification.', 'wpcloudplugins'),
    'key' => 'proof_template_subject',
    'default' => '',
]);

AdminLayout::render_wpeditor(
    [
        'title' => esc_html__('Email Body (HTML)', 'wpcloudplugins'),
        'description' => '',
        'placeholder' => '',
        'key' => 'proof_template',
        'wpeditor' => [
            'teeny' => true,
            'tinymce' => false,
            'textarea_rows' => 9,
            'media_buttons' => false,
            'quicktags' => false,
        ],
    ],
    true,
    [
        'type' => 'htmlmixed',
        'codemirror' => [
            'foldGutter' => true,
            'indentUnit' => 0,
            'lineWrapping' => true,
            'refresh' => true,
            'autoRefresh' => true,
        ],
    ]
);

AdminLayout::render_notice(
    esc_html__(
        'Available placeholders:',
        'wpcloudplugins'
    )
                .' <code>%site_name%</code>, 
                <code>%number_of_files%</code>, 
                <code>%user_name%</code>, 
                <code>%user_email%</code>, 
                <code>%usermeta_first_name%</code>, 
                <code>%usermeta_last_name%</code>,                 
                <code>%recipient_name%</code>, 
                <code>%recipient_email%</code>, 
                <code>%recipient_firstname%</code>, 
                <code>%recipient_lastname%</code>, 
                <code>%admin_email%</code>, 
                <code>%account_email%</code>,  
                <code>%file_name%</code>, 
                <code>%file_size%</code>, 
                <code>%file_icon%</code>, 
                <code>%file_relative_path%</code>,
                <code>%file_absolute_path%</code>,
                <code>%file_cloud_download_url%</code>, 
                <code>%file_cloud_preview_url%</code>, 
                <code>%file_cloud_shared_url%</code>, 
                <code>%file_download_url%</code>,
                <code>%folder_name%</code>,
                <code>%folder_relative_path%</code>,
                <code>%folder_absolute_path%</code>,
                <code>%folder_url%</code>,
                <code>%ip%</code>, 
                <code>%location%</code>',
    'info'
);

AdminLayout::render_close_panel();

// Template %filelist% Placeholder
AdminLayout::render_open_panel([
    'title' => esc_html__('File item template', 'wpcloudplugins'), 'accordion' => true,
]);

AdminLayout::render_wpeditor(
    [
        'title' => sprintf(esc_html__('Template for %s placeholder', 'wpcloudplugins'), '<code>%filelist%</code>'),
        'description' => esc_html__('Template for each file item in the filelist in the download/upload/delete notification body.', 'wpcloudplugins'),
        'placeholder' => '',
        'key' => 'filelist_template',
        'wpeditor' => [
            'teeny' => true,
            'tinymce' => false,
            'textarea_rows' => 9,
            'media_buttons' => false,
            'quicktags' => false,
        ],
    ],
    true,
    [
        'type' => 'htmlmixed',
        'codemirror' => [
            'foldGutter' => true,
            'indentUnit' => 0,
            'lineWrapping' => true,
            'refresh' => true,
            'autoRefresh' => true,
        ],
    ]
);

AdminLayout::render_notice(
    esc_html__(
        'Available placeholders:',
        'wpcloudplugins'
    )
                .' <code>%file_name%</code>, 
                <code>%file_size%</code>, 
                <code>%file_lastedited%</code>,                
                <code>%file_icon%</code>, 
                <code>%file_cloud_preview_url%</code>, 
                <code>%file_cloud_shared_url%</code>, 
                <code>%file_download_url%</code>,
<code>%file_deeplink_url%</code>,                
                <code>%file_relative_path%</code>, 
                <code>%file_absolute_path%</code>, 
                <code>%folder_relative_path%</code>,
                <code>%folder_absolute_path%</code>,
                <code>%folder_url%</code>',
    'info'
);

AdminLayout::render_close_panel();

?>
                        </div>
                        <!-- End Notifications Panel -->

                        <!-- Permissions Panel -->
                        <div data-nav-panel="wpcp-permissions" class="!hidden space-y-6">

                            <?php
// Permissions
AdminLayout::render_open_panel([
    'title' => esc_html__('Permissions', 'wpcloudplugins'),
    'description' => esc_html__('Select which roles or users should be able to perform the following actions in the WordPress Admin Dashboard.', 'wpcloudplugins'),
    'accordion' => false,
]);

AdminLayout::render_tags([
    'title' => esc_html__('Change Plugin Settings', 'wpcloudplugins'),
    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />',
    'description' => esc_html__('Who should be able to access the settings page of the plugin?', 'wpcloudplugins'),
    'key' => 'permissions_edit_settings',
    'default' => ['administrator'],
]);

AdminLayout::render_tags([
    'title' => esc_html__('Link Users to Personal Folders', 'wpcloudplugins'),
    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />',
    'description' => esc_html__('Who can manually link users to their personal folder?', 'wpcloudplugins'),
    'key' => 'permissions_link_users',
    'default' => ['administrator', 'editor'],
]);

AdminLayout::render_tags([
    'title' => esc_html__('See Reports', 'wpcloudplugins'),
    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />',
    'description' => esc_html__('Who should have access to reports and statistics?', 'wpcloudplugins'),
    'key' => 'permissions_see_dashboard',
    'default' => ['administrator', 'editor'],
]);

AdminLayout::render_tags([
    'title' => esc_html__('Use Back-End Filebrowser', 'wpcloudplugins'),
    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 012.25-2.25h7.5A2.25 2.25 0 0118 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 004.5 9v.878m13.5-3A2.25 2.25 0 0119.5 9v.878m0 0a2.246 2.246 0 00-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0121 12v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6c0-.98.626-1.813 1.5-2.122" />',
    'description' => esc_html__('Who should have access to the File Browser in the admin dashboard?', 'wpcloudplugins'),
    'key' => 'permissions_see_filebrowser',
    'default' => ['administrator'],
]);

AdminLayout::render_tags([
    'title' => esc_html__('Add & Configure Modules', 'wpcloudplugins'),
    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 002.25-2.25V6a2.25 2.25 0 00-2.25-2.25H6A2.25 2.25 0 003.75 6v2.25A2.25 2.25 0 006 10.5zm0 9.75h2.25A2.25 2.25 0 0010.5 18v-2.25a2.25 2.25 0 00-2.25-2.25H6a2.25 2.25 0 00-2.25 2.25V18A2.25 2.25 0 006 20.25zm9.75-9.75H18a2.25 2.25 0 002.25-2.25V6A2.25 2.25 0 0018 3.75h-2.25A2.25 2.25 0 0013.5 6v2.25a2.25 2.25 0 002.25 2.25z" />',
    'description' => esc_html__('Who can add and configure modules?', 'wpcloudplugins'),
    'key' => 'permissions_add_shortcodes',
    'default' => ['administrator', 'editor', 'author', 'contributor'],
]);

AdminLayout::render_tags([
    'title' => esc_html__('Add Direct links', 'wpcloudplugins'),
    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />',
    'description' => esc_html__('Who can add shared document links to your pages and posts?', 'wpcloudplugins'),
    'key' => 'permissions_add_links',
    'default' => ['administrator', 'editor', 'author', 'contributor'],
]);

AdminLayout::render_tags([
    'title' => esc_html__('Embed Documents', 'wpcloudplugins'),
    'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />',
    'description' => esc_html__('Who can embed documents on your pages and posts?', 'wpcloudplugins'),
    'key' => 'permissions_add_embedded',
    'default' => ['administrator', 'editor', 'author', 'contributor'],
]);

AdminLayout::render_close_panel();
?>
                        </div>
                        <!-- End Permissions Panel -->

                        <!-- Restrictions Panel -->
                        <div data-nav-panel="wpcp-restrictions" class="!hidden space-y-6">

                            <?php // Restriction -> Downloads Block
AdminLayout::render_open_panel([
    'title' => esc_html__('Usage Limits', 'wpcloudplugins'),
    'description' => esc_html__('Limit the use of the plugin modules. For example, you can limit the number of downloads allowed. All restrictions are monitored on a per-user basis.', 'wpcloudplugins'),
]);

AdminLayout::render_simple_select([
    'title' => esc_html__('Restriction Period', 'wpcloudplugins'),
    'description' => esc_html__('Defines the time frame for applying usage limits. Choose from predefined periods.', 'wpcloudplugins'),
    'options' => [
        '1 day' => ['title' => esc_html__('Per Day', 'wpcloudplugins')],
        '1 week' => ['title' => esc_html__('Per Week', 'wpcloudplugins')],
        '1 month' => ['title' => esc_html__('Per Month', 'wpcloudplugins')],
    ],
    'key' => 'usage_period',
    'default' => '1 day',
]);

AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Download Restrictions', 'wpcloudplugins'),
    'description' => esc_html__('Limit the number of downloads for all modules. You can also control the download limit per individual module in the module configuration.', 'wpcloudplugins'),
    'key' => 'download_limits',
    'default' => false,
    'toggle_container' => '#toggle-download_limits',
]);

AdminLayout::render_open_toggle_container(['key' => 'toggle-download_limits']);

AdminLayout::render_simple_number([
    'title' => esc_html__('Bandwidth usage (in GB)', 'wpcloudplugins'),
    'description' => esc_html__('The total amount of bandwidth that a user is allowed to use during the selected period.'),
    'placeholder' => esc_attr__('Unlimited', 'wpcloudplugins'),
    'default' => '',
    'min' => 0.1,
    'max' => null,
    'step' => '0.1',
    'width' => 'w-28',
    'key' => 'bandwidth_per_user',
    'notice' => esc_html__('An estimate of the API bandwidth is used. If a user stops downloading a file before it is complete, it will still count as a full download.', 'wpcloudplugins'),
    'notice_class' => 'warning',
]);

AdminLayout::render_simple_number([
    'title' => esc_html__('Downloads', 'wpcloudplugins'),
    'description' => esc_html__('The number of files that a user is allowed to download during the selected period.', 'wpcloudplugins'),
    'placeholder' => esc_attr__('Unlimited', 'wpcloudplugins'),
    'default' => '',
    'min' => 1,
    'max' => null,
    'width' => 'w-28',
    'key' => 'downloads_per_user',
]);

AdminLayout::render_simple_number([
    'title' => esc_html__('Downloads per file', 'wpcloudplugins'),
    'description' => esc_html__('The number of times the same file can be downloaded by a user during the selected period.', 'wpcloudplugins'),
    'placeholder' => esc_attr__('Unlimited', 'wpcloudplugins'),
    'default' => '',
    'min' => 1,
    'max' => null,
    'width' => 'w-28',
    'key' => 'downloads_per_user_per_file',
]);

AdminLayout::render_simple_number([
    'title' => esc_html__('ZIP downloads', 'wpcloudplugins'),
    'description' => esc_html__('Number of ZIP files that can be downloaded per user, during the selected period.', 'wpcloudplugins'),
    'placeholder' => esc_attr__('Unlimited', 'wpcloudplugins'),
    'default' => '',
    'min' => 1,
    'max' => null,
    'width' => 'w-28',
    'key' => 'zip_downloads_per_user',
]);

AdminLayout::render_notice(esc_html__('You can set individual limits for each of your users in their user profile.', 'wpcloudplugins'), 'info');

echo '<br/>';

AdminLayout::render_tags([
    'title' => esc_html__('Exclude Roles & Users', 'wpcloudplugins'),
    'description' => esc_html__('Which users are excluded from the limits set above?', 'wpcloudplugins'),
    'key' => 'download_limits_excluded_roles',
    'default' => ['administrator'],
]);

AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Send email notification when limit is reached', 'wpcloudplugins'),
    'description' => esc_html__('Send an email notification to the administrator when an user hits a specific limit.', 'wpcloudplugins'),
    'key' => 'download_limits_notification',
    'default' => false,
]);

AdminLayout::render_close_toggle_container();

AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Support for individual limits', 'wpcloudplugins'),
    'description' => esc_html__('Add individual usage limits via the User Profile page. These user-specific limits will override the global or module usage limits.', 'wpcloudplugins'),
    'key' => 'usage_allow_individual_limits',
    'default' => true,
]);

AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Block untraceable users', 'wpcloudplugins'),
    'description' => esc_html__('To monitor limits, the user must be logged in to your site or have cookies enabled in their browser. If a user is untraceable, the action will be blocked if this setting is enabled.', 'wpcloudplugins'),
    'key' => 'download_limits_block_untraceable_users',
    'default' => true,
]);

AdminLayout::render_simple_action_button([
    'title' => esc_html__('Reset usage limits', 'wpcloudplugins'),
    'description' => esc_html__('Resets the current usage limits for all users for today.', 'wpcloudplugins'),
    'key' => 'wpcp-reset-usage-limits-button',
    'button_text' => esc_html__('Reset', 'wpcloudplugins'),
]);

AdminLayout::render_close_panel();
?>
                        </div>
                        <!-- End Restrictions Panel -->

                        <!-- Statistics Panel -->
                        <div data-nav-panel="wpcp-statistics" class="!hidden space-y-6">

                            <?php
// Statistics
AdminLayout::render_open_panel([
    'title' => 'Statistics',
]);

// Log Events
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Log Events', 'wpcloudplugins'),
    'description' => esc_html__('Register all plugin events.', 'wpcloudplugins'),
    'key' => 'log_events',
    'default' => false,
    'toggle_container' => '#toggle-event-options',
]);

AdminLayout::render_open_toggle_container(['key' => 'toggle-event-options']);

// Retention period
AdminLayout::render_simple_select([
    'title' => esc_html__('Retention Period', 'wpcloudplugins'),
    'description' => esc_html__('Please select for which period the events should be stored.', 'wpcloudplugins'),
    'options' => [
        '7 day' => ['title' => esc_html__('1 week', 'wpcloudplugins')],
        '14 day' => ['title' => esc_html__('2 weeks', 'wpcloudplugins')],
        '1 month' => ['title' => esc_html__('1 month', 'wpcloudplugins')],
        '3 month' => ['title' => esc_html__('3 months', 'wpcloudplugins')],
        '6 month' => ['title' => esc_html__('6 months', 'wpcloudplugins')],
        '12 month' => ['title' => esc_html__('1 year', 'wpcloudplugins')],
        '18 month' => ['title' => esc_html__('1,5 year', 'wpcloudplugins')],
        '24 month' => ['title' => esc_html__('2 years', 'wpcloudplugins')],
    ],
    'key' => 'event_retention_period',
    'default' => '6 month',
]);

// Summary Email
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Summary Email', 'wpcloudplugins'),
    'description' => esc_html__('Email a summary of all the events that are logged with the plugin.', 'wpcloudplugins'),
    'key' => 'event_summary',
    'default' => false,
    'toggle_container' => '#toggle-event-summary-options',
]);

AdminLayout::render_open_toggle_container(['key' => 'toggle-event-summary-options']);

// Email Summary Interval
AdminLayout::render_simple_select([
    'title' => esc_html__('Interval', 'wpcloudplugins'),
    'description' => esc_html__('Please select the interval the summary needs to be send.', 'wpcloudplugins'),
    'options' => [
        'daily' => ['title' => esc_html__('Every day', 'wpcloudplugins')],
        'weekly' => ['title' => esc_html__('Weekly', 'wpcloudplugins')],
        'monthly' => ['title' => esc_html__('Monthly', 'wpcloudplugins')],
    ],
    'key' => 'event_summary_period',
    'default' => 'daily',
]);

// Email Summary Recipients
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Recipients', 'wpcloudplugins'),
    'description' => esc_html__('Set to which email address(es) the summary should be send.', 'wpcloudplugins'),
    'placeholder' => get_option('admin_email'),
    'default' => '',
    'key' => 'event_summary_recipients',
]);

AdminLayout::render_close_toggle_container();

// Events WebHook
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Use Webhook', 'wpcloudplugins'),
    'description' => esc_html__('Send automated messages (JSON data) to another application for every event logged by the plugin.', 'wpcloudplugins'),
    'key' => 'webhook_active',
    'default' => false,
    'toggle_container' => '#toggle-event-webhook-options',
]);

AdminLayout::render_open_toggle_container(['key' => 'toggle-event-webhook-options']);

// Webhook Endpoint URL
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Webhook Endpoint URL', 'wpcloudplugins'),
    'description' => esc_html__('The listener URL where the JSON data will be send to.', 'wpcloudplugins'),
    'placeholder' => 'https://example.com/listener.php',
    'default' => '',
    'key' => 'webhook_endpoint_url',
]);

// Webhook Endpoint Secret
AdminLayout::render_simple_textbox([
    'title' => esc_html__('Webhook Secret', 'wpcloudplugins'),
    'description' => esc_html__('The events send to your endpoint will include a signature. You can use this secret to verify that the events were sent by this plugin, not by a third party. See the documentation for more information.', 'wpcloudplugins'),
    'placeholder' => '',
    'default' => '',
    'key' => 'webhook_endpoint_secret',
]);

AdminLayout::render_close_toggle_container();

AdminLayout::render_close_toggle_container();

// Google Analytics
AdminLayout::render_simple_checkbox([
    'title' => esc_html__('Use Google Analytics tracker', 'wpcloudplugins'),
    'description' => esc_html__('The plugin will send its events to Google Analytics if your Google tracker has been added to your site.', 'wpcloudplugins'),
    'key' => 'google_analytics',
    'default' => false,
]);

AdminLayout::render_close_panel();
?>
                        </div>
                        <!-- End Statistics Panel -->

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
    'description' => esc_html__('As the API does not provide thumbnails on the fly, the plugin will generate and store them locally. You can purge that folder if needed.', 'wpcloudplugins'),
    'key' => 'wpcp-purge-thumbnails-button',
    'button_text' => esc_html__('Purge', 'wpcloudplugins'),
]);

AdminLayout::render_close_panel();

// Tools -> Export/Import
AdminLayout::render_open_panel([
    'title' => esc_html__('Backup', 'wpcloudplugins'),
]);

AdminLayout::render_simple_select([
    'title' => 'Select backup data',
    'description' => 'Select what kind of information should be stored in the backup.',
    'options' => [
        'all' => ['title' => esc_html__('Everything (settings, modules, event logs & user ⇔ folder links)', 'wpcloudplugins'), 'toggle_container' => ''],
        'settings' => ['title' => esc_html__('Global settings', 'wpcloudplugins'), 'toggle_container' => ''],
        'modules' => ['title' => esc_html__('Modules', 'wpcloudplugins'), 'toggle_container' => ''],
        'userfolders' => ['title' => esc_html__('User ⇔ Folder links', 'wpcloudplugins'), 'toggle_container' => ''],
        'events' => ['title' => esc_html__('Event Logs', 'wpcloudplugins'), 'toggle_container' => ''],
    ],
    'key' => 'tools_export_fields',
    'default' => 'all',
]);

AdminLayout::render_simple_action_button([
    'title' => esc_html__('Export', 'wpcloudplugins'),
    'description' => esc_html__('When you click the export button, a (gzipped) JSON file will be generated. You can use the Import action below to restore the backup.', 'wpcloudplugins'),
    'key' => 'wpcp-export-button',
    'button_text' => esc_html__('Export', 'wpcloudplugins'),
]);

AdminLayout::render_file_selector([
    'title' => esc_html__('Import data', 'wpcloudplugins'),
    'description' => esc_html__('Select the export file(.json or .gz) you would like to import. Please note that the import will replace your current data.', 'wpcloudplugins'),
    'key' => 'wpcp-import',
    'accept' => '.json,.gz',
    'button_text' => esc_html__('Import', 'wpcloudplugins'),
]);

AdminLayout::render_close_panel();

// Tools -> Reset Block
AdminLayout::render_open_panel([
    'title' => esc_html__('Reset', 'wpcloudplugins'),
]);

AdminLayout::render_simple_action_button([
    'title' => esc_html__('Reset to Factory Settings', 'wpcloudplugins'),
    'description' => esc_html__('Need to revert back to the default settings? This button will instantly reset your settings to the defaults. When you reset the settings, the plugin will not longer be linked to your accounts, but their authorization will not be revoked. You can revoke the authorization via the Dashboard tab.', 'wpcloudplugins'),
    'key' => 'wpcp-factory-reset-button',
    'button_text' => esc_html__('Reset Plugin', 'wpcloudplugins'),
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

                                        <a href="https://1.envato.market/vLjyO" type="button" class="wpcp-button-secondary" target="_blank">
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

    </div>
</div>