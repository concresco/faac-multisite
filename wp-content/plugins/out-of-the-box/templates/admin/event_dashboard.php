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
if (
    !Helpers::check_user_role(Settings::get('permissions_see_dashboard'))
) {
    exit;
}

?>
<div id="wpcp" class="wpcp-app wpcp-reports hidden" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
    <div class="z-10 bg-gray-100">
        <div class="min-h-[calc(100vh-32px)] flex flex-col">

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

                <!-- Page -->
                <main class="flex flex-col flex-1">
                    <nav class="bg-white shadow-sm z-30">
                        <div class="mx-auto px-4 py-3 sm:px-6 lg:px-8 flex flex-row justify-between h-14">
                            <h1 class="text-xl font-bold tracking-tight text-gray-900 flex justify-center items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
                                </svg>
                                <?php esc_html_e('Reports Dashboard', 'wpcloudplugins'); ?>
                            </h1>

                            <div class="flex flex-row justify-between gap-2">

                                <!-- Reset Log Button -->
                                <div class="flex items-center relative">
                                    <div>
                                        <button id="clear_statistics" type="button" class="wpcp-button-secondary">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="-ml-0.5 mr-2 h-4 w-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>

                                            <?php esc_html_e('Reset Log', 'wpcloudplugins'); ?>
                                        </button>
                                    </div>
                                </div>
                                <!-- End Reset Log Button -->

                                <!-- Full Log Button -->
                                <div class="flex items-center relative">
                                    <div>
                                        <a href="#full-log" type="button" class="wpcp-button-primary">
                                            <!-- Heroicon name: adjustments-horizontal -->
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="-ml-0.5 mr-2 h-4 w-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5" />
                                            </svg>
                                            <?php esc_html_e('All Events', 'wpcloudplugins'); ?>
                                        </a>
                                    </div>
                                </div>
                                <!-- End Full Log Button -->
                            </div>

                        </div>
                    </nav>

                    <div class="flex-1 w-full mx-auto px-4 py-4 sm:px-6 lg:px-8">
                        <div class="flow-root min-w-full overflow-auto">

                            <!-- Main 3 column grid -->
                            <div class="grid grid-cols-1 gap-4 items-start lg:grid-cols-5 lg:gap-8">
                                <!-- Left column -->
                                <div class="grid grid-cols-1 gap-4 lg:col-span-3">
                                    <!-- Welcome panel -->
                                    <section aria-labelledby="profile-overview-title">
                                        <div class="rounded-lg bg-white overflow-hidden shadow">
                                            <div class="bg-white p-6">
                                                <div class="sm:flex sm:items-center sm:justify-between">
                                                    <?php $current_user = wp_get_current_user(); ?>
                                                    <div class="sm:flex sm:gap-5">
                                                        <div class="shrink-0">
                                                            <img class="mx-auto h-20 w-20 rounded-full" src="<?php echo \get_avatar_url($current_user->ID); ?>" alt="">
                                                        </div>
                                                        <div class="mt-4 text-center sm:mt-0 sm:pt-1 sm:text-left">
                                                            <p class="text-sm font-medium text-gray-600"><?php esc_html_e('Welcome back', 'wpcloudplugins'); ?>,</p>
                                                            <p class="text-xl font-bold text-gray-900 sm:text-2xl"><?php echo $current_user->display_name; ?></p>
                                                            <p class="text-sm font-medium text-gray-600"><?php echo $current_user->user_email; ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="wpcp-counter-totals" class="border-t border-gray-200 bg-gray-50 grid grid-cols-1 divide-y divide-gray-200 sm:grid-cols-4 sm:divide-y-0 sm:divide-x">
                                                <div class="px-6 py-5 text-sm font-medium text-center flex items-center justify-center space-x-2">
                                                    <div class="text-gray-900 wpcp-counter rounded-full p-2" data-type="outofthebox_previewed_entry">-</div>
                                                    <div class="text-gray-600"><?php esc_html_e('Previews', 'wpcloudplugins'); ?></div>
                                                </div>

                                                <div class="px-6 py-5 text-sm font-medium text-center flex items-center justify-center space-x-2">
                                                    <div class="text-gray-900 wpcp-counter rounded-full p-2" data-type="outofthebox_downloaded_entry">-</div>
                                                    <div class="text-gray-600"><?php esc_html_e('Downloads', 'wpcloudplugins'); ?></div>
                                                </div>

                                                <div class="px-6 py-5 text-sm font-medium text-center flex items-center justify-center space-x-2">
                                                    <div class="text-gray-900 wpcp-counter rounded-full p-2" data-type="outofthebox_created_link_to_entry">-</div>
                                                    <div class="text-gray-600"><?php esc_html_e('Items Shared', 'wpcloudplugins'); ?></div>
                                                </div>

                                                <div class="px-6 py-5 text-sm font-medium text-center flex items-center justify-center space-x-2">
                                                    <div class="text-gray-900 wpcp-counter rounded-full p-2" data-type="outofthebox_uploaded_entry">-</div>
                                                    <div class="text-gray-600"><?php esc_html_e('Uploads', 'wpcloudplugins'); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <!-- Select Period -->
                                    <section>
                                        <div class="rounded-lg bg-white overflow-hidden shadow-sm divide-y divide-gray-200 sm:divide-y-0 flex items-center justify-between">
                                            <div class="p-4 sm:px-6  flex items-center justify-start">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <h3 class="text-lg font-medium text-gray-900 ml-2 "><?php esc_html_e('Select Period', 'wpcloudplugins'); ?></h3>
                                            </div>
                                            <div class="p-4 sm:px-6">
                                                <input type="text" class="date_range_selector wpcp-input-textbox bg-white font-medium text-center w-64 max-w-xl flex-1 block shadow-xs focus:ring-brand-color-700 focus:border-brand-color-700 sm:text-sm border border-gray-300 rounded-md" name="date_range_selector">
                                            </div>
                                        </div>
                                    </section>

                                    <!-- Events per Day -->
                                    <section>
                                        <div class="rounded-lg bg-white overflow-hidden shadow-sm divide-y divide-gray-200 sm:divide-y-0 sm:grid sm:grid-cols-1 sm:gap-px">
                                            <div class="bg-white px-4 py-5 border-b border-gray-200 sm:px-6 flex items-center justify-start">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                                </svg>
                                                <h3 class="text-lg leading-6 font-medium  ml-2 mr-24"><?php esc_html_e('Events per Day', 'wpcloudplugins'); ?></h3>
                                            </div>
                                            <div class="wpcp-events-chart-container w-full px-4 py-5 aspect-video">
                                                <div class="loading">
                                                    <div class='loader-beat'></div>
                                                </div>
                                                <canvas id="wpcp-events-chart"></canvas>
                                            </div>
                                        </div>
                                    </section>

                                    <!-- Top Previews -->
                                    <section id="section-top-previews">
                                        <div class="rounded-lg bg-white overflow-hidden shadow">
                                            <div class="p-6">
                                                <div class="bg-white pb-5 border-b border-gray-200 flex items-center justify-start">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    <h3 class="text-lg leading-6 font-medium  ml-2 mr-24"><?php esc_html_e('Top Previews', 'wpcloudplugins'); ?></h3>
                                                </div>
                                                <div class="flow-root">
                                                    <table id="top-previews" class="stripe hover order-column">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th><?php esc_html_e('Document', 'wpcloudplugins'); ?></th>
                                                                <th><?php esc_html_e('Total', 'wpcloudplugins'); ?></th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <!-- Top Downloads -->
                                    <section id="section-top-downloads">
                                        <div class="rounded-lg bg-white overflow-hidden shadow">
                                            <div class="p-6">
                                                <div class="bg-white pb-5 border-b border-gray-200 flex items-center justify-start">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                    <h3 class="text-lg leading-6 font-medium  ml-2 mr-24"><?php esc_html_e('Top Downloads', 'wpcloudplugins'); ?></h3>
                                                </div>
                                                <div class="flow-root">
                                                    <table id="top-downloads" class="stripe hover order-column">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th><?php esc_html_e('Document', 'wpcloudplugins'); ?></th>
                                                                <th><?php esc_html_e('Total', 'wpcloudplugins'); ?></th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <!-- Latest Uploads -->
                                    <section id="latest-25-uploads">
                                        <div class="rounded-lg bg-white overflow-hidden shadow">
                                            <div class="p-6">
                                                <div class="bg-white pb-5 border-b border-gray-200 flex items-center justify-start">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                    </svg>
                                                    <h3 class="text-lg leading-6 font-medium  ml-2 mr-24"><?php esc_html_e('Latest Uploads', 'wpcloudplugins'); ?></h3>
                                                </div>

                                                <div class="flow-root">
                                                    <table id="latest-uploads" class="stripe hover order-column" style="width:100%">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th><?php esc_html_e('Document', 'wpcloudplugins'); ?></th>
                                                                <th><?php esc_html_e('Date', 'wpcloudplugins'); ?></th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                </div>

                                <!-- Right column -->
                                <div class="grid grid-cols-1 gap-4 lg:col-span-2">

                                    <!-- Top Users by Usage -->
                                    <section id="section-top-user-usage">
                                        <div class="rounded-lg bg-white overflow-hidden shadow">
                                            <div class="p-6">
                                                <div class="bg-white pb-5 border-b border-gray-200 flex items-center justify-start">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                                    </svg>
                                                    <h3 class="text-lg leading-6 font-medium  ml-2 mr-24"><?php esc_html_e('Bandwidth usage', 'wpcloudplugins'); ?></h3>
                                                </div>

                                                <div class="flow-root">
                                                    <table id="top-usage" class="display" style="width:100%">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th><?php esc_html_e('User', 'wpcloudplugins'); ?></th>
                                                                <th><?php esc_html_e('Usage', 'wpcloudplugins'); ?></th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>

                                                <div class="mt-6 text-xs italic">
                                                    * <?php esc_html_e("This is an estimate of the API bandwidth used. It doesn't take into account if a user has only partially streamed a file or stopped a download before it was complete.", 'wpcloudplugins'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <!-- Users Information -->
                                    <section>
                                        <div class="rounded-lg bg-white overflow-hidden shadow-sm divide-y divide-gray-200 sm:divide-y-0 sm:grid sm:grid-cols-1 sm:gap-px">
                                            <div class="p-6">
                                                <div class="bg-white pb-5 border-b border-gray-200 flex items-center justify-start">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                    <h3 class="text-lg leading-6 font-medium text-gray-900 ml-2"><?php esc_html_e('Events per User', 'wpcloudplugins'); ?></h3>
                                                </div>
                                                <div class="flow-root">
                                                    <table id="users-log" class="stripe hover order-column">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th><?php esc_html_e('User', 'wpcloudplugins'); ?></th>
                                                                <th title="<?php esc_html_e('Previews', 'wpcloudplugins'); ?>"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                                    </svg>
                                                                </th>
                                                                <th title="<?php esc_html_e('Downloads', 'wpcloudplugins'); ?>">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                                    </svg>
                                                                </th>
                                                                <th title="<?php esc_html_e('Uploads', 'wpcloudplugins'); ?>">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                                                    </svg>
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <!-- Top Downloads by User -->
                                    <section id="section-top-user-downloads">
                                        <div class="rounded-lg bg-white overflow-hidden shadow">
                                            <div class="p-6">
                                                <div class="bg-white pb-5 border-b border-gray-200 flex items-center justify-start">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                    <h3 class="text-lg leading-6 font-medium  ml-2 mr-24"><?php esc_html_e('Downloads per User', 'wpcloudplugins'); ?></h3>
                                                </div>

                                                <div class="flow-root">
                                                    <table id="top-users" class="display" style="width:100%">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th><?php esc_html_e('User', 'wpcloudplugins'); ?></th>
                                                                <th><?php esc_html_e('Downloads', 'wpcloudplugins'); ?></th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <!-- Uploads per User -->
                                    <section id="section-top-user-uploads">
                                        <div class="rounded-lg bg-white overflow-hidden shadow">
                                            <div class="p-6">
                                                <div class="bg-white pb-5 border-b border-gray-200 flex items-center justify-start">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                    <h3 class="text-lg leading-6 font-medium  ml-2 mr-24"><?php esc_html_e('Uploads per User', 'wpcloudplugins'); ?></h3>
                                                </div>

                                                <div class="flow-root">
                                                    <table id="top-uploads" class="display" style="width:100%">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th><?php esc_html_e('User', 'wpcloudplugins'); ?></th>
                                                                <th><?php esc_html_e('Uploads', 'wpcloudplugins'); ?></th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                </div>

                                <!-- All Events -->
                                <div class="grid grid-cols-1 gap-4 lg:col-span-5">
                                    <section>
                                        <div class="rounded-lg bg-white overflow-hidden shadow-sm divide-y divide-gray-200 sm:divide-y-0 sm:grid sm:grid-cols-1 sm:gap-px">
                                            <div class="p-6">
                                                <div class="bg-white pb-5 border-b border-gray-200 flex items-center justify-start">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                                    </svg>
                                                    <h3 class="text-lg leading-6 font-medium text-gray-900 ml-2"><?php esc_html_e('All Events', 'wpcloudplugins'); ?></h3>
                                                </div>
                                                <div class="flow-root">
                                                    <table id="full-log">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th><?php esc_html_e('Date', 'wpcloudplugins'); ?></th>
                                                                <th class="all"><?php esc_html_e('Description', 'wpcloudplugins'); ?></th>
                                                                <th><?php esc_html_e('Event', 'wpcloudplugins'); ?></th>
                                                                <th><?php esc_html_e('User', 'wpcloudplugins'); ?></th>
                                                                <th><?php esc_html_e('Name', 'wpcloudplugins'); ?></th>
                                                                <th><?php esc_html_e('Location', 'wpcloudplugins'); ?></th>
                                                                <th><?php esc_html_e('Module', 'wpcloudplugins'); ?></th>
                                                                <th><?php esc_html_e('Page', 'wpcloudplugins'); ?></th>
                                                                <th><?php esc_html_e('Extra', 'wpcloudplugins'); ?></th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>

                                            </div>
                                        </div>
                                    </section>
                                </div>

                            </div>

                        </div>
                    </div>
                </main>
                <!-- End Page -->

            </div>


        </div>


        <!-- Modal Details -->
        <div id="wpcp-modal-details-template" class="wpcp-dialog hidden">
            <div class="relative z-20" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-500/90 transition-opacity backdrop-blur-xs"></div>
                <div class="fixed z-30 inset-0 overflow-y-auto">
                    <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                        <div class="relative bg-gray-100 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-6xl sm:w-full sm:p-6">

                            <div class="grid grid-cols-1 gap-4 lg:col-span-2 opacity-0 transition duration-600 ease-in">
                                <!-- Welcome panel -->
                                <section aria-labelledby="profile-overview-title" class="wpcp-event-details">
                                    <div class="rounded-lg bg-white overflow-hidden shadow">
                                        <div class="bg-white p-4">
                                            <div class="sm:flex sm:items-center sm:justify-between">
                                                <div class="sm:flex sm:gap-5">
                                                    <div class="shrink-0">
                                                        <img class="wpcp-event-details-entry-img mx-auto h-16 w-16 object-cover shadow-xs" alt="" />
                                                    </div>
                                                    <div class="mt-4 flex flex-col justify-center items-start sm:mt-0 sm:pt-1">
                                                        <p class="wpcp-event-details-name text-xl font-bold text-gray-900 sm:text-2xl"></p>
                                                        <p class="wpcp-event-details-description text-sm font-medium text-gray-600 line-clamp-3"></p>
                                                    </div>
                                                </div>
                                                <div class="ml-5 flex justify-center items-center space-x-2">
                                                    <a type="button" class="wpcp-button-primary wpcp-event-download-entry inline-flex justify-center w-full" download><?php esc_html_e('Download', 'wpcloudplugins'); ?></a>
                                                    <a type="button" target="_blank" class="wpcp-button-secondary wpcp-event-user-profile inline-flex justify-center w-full">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="-ml-1 mr-3 h-5 w-5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                        </svg>
                                                        <?php esc_html_e('Profile', 'wpcloudplugins'); ?>
                                                    </a>
                                                    <button type="button" class="wpcp-button-primary wpcp-dialog-destroy inline-flex justify-center w-full"><?php esc_html_e('Close', 'wpcloudplugins'); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="wpcp-event-details-totals" class="border-t border-gray-200 bg-gray-50 grid grid-cols-1 divide-y divide-gray-200 sm:grid-cols-4 sm:divide-y-0 sm:divide-x">
                                            <div class="px-6 py-2 text-sm font-medium text-center flex items-center justify-center space-x-2">
                                                <div class="text-gray-900 wpcp-counter rounded-full p-2" data-type="outofthebox_previewed_entry">-</div>
                                                <div class="text-gray-600"><?php esc_html_e('Previews', 'wpcloudplugins'); ?></div>
                                            </div>

                                            <div class="px-6 py-2 text-sm font-medium text-center flex items-center justify-center space-x-2">
                                                <div class="text-gray-900 wpcp-counter rounded-full p-2" data-type="outofthebox_downloaded_entry">-</div>
                                                <div class="text-gray-600"><?php esc_html_e('Downloads', 'wpcloudplugins'); ?></div>
                                            </div>

                                            <div class="px-6 py-2 text-sm font-medium text-center flex items-center justify-center space-x-2">
                                                <div class="text-gray-900 wpcp-counter rounded-full p-2" data-type="outofthebox_created_link_to_entry">-</div>
                                                <div class="text-gray-600"><?php esc_html_e('Items Shared', 'wpcloudplugins'); ?></div>
                                            </div>

                                            <div class="px-6 py-2 text-sm font-medium text-center flex items-center justify-center space-x-2">
                                                <div class="text-gray-900 wpcp-counter rounded-full p-2" data-type="outofthebox_uploaded_entry">-</div>
                                                <div class="text-gray-600"><?php esc_html_e('Uploads', 'wpcloudplugins'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <!-- All Events -->
                                <section>
                                    <div class="rounded-lg bg-white overflow-hidden shadow-sm divide-y divide-gray-200 sm:divide-y-0 sm:grid sm:grid-cols-1 sm:gap-px">
                                        <div class="p-6">
                                            <div class="bg-white px-4 py-5 border-b border-gray-200">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900"><?php esc_html_e('All Events', 'wpcloudplugins'); ?></h3>
                                            </div>
                                            <div class="flow-root">
                                                <table id="wpcp-full-detail-log">
                                                    <thead>
                                                        <tr>
                                                            <th></th>
                                                            <th><?php esc_html_e('Date', 'wpcloudplugins'); ?></th>
                                                            <th class="all"><?php esc_html_e('Description', 'wpcloudplugins'); ?></th>
                                                            <th><?php esc_html_e('Event', 'wpcloudplugins'); ?></th>
                                                            <th><?php esc_html_e('User', 'wpcloudplugins'); ?></th>
                                                            <th><?php esc_html_e('Name', 'wpcloudplugins'); ?></th>
                                                            <th><?php esc_html_e('Location', 'wpcloudplugins'); ?></th>
                                                            <th><?php esc_html_e('Page', 'wpcloudplugins'); ?></th>
                                                            <th><?php esc_html_e('Extra', 'wpcloudplugins'); ?></th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                            <div class="wpcp-event-details-loader absolute inset-0 flex items-center justify-center">
                                <div class="wpcp-loading-beat"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal Details -->

    </div>
</div>