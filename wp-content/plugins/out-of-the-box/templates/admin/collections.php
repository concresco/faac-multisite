<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       3.1
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

?>
<style>
#poststuff #post-body {
    margin: 0, important !important;
    width: 100%;
}

#poststuff #side-sortables {
    display: none;
}
</style>
<div id="wpcp" class="wpcp-app wpcp-h-full hidden" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">

    <div class="flex min-h-full flex-col">

        <div class="mx-auto flex w-full items-start gap-6">
            <aside class="sticky top-8 w-64 shrink-0 flex flex-col gap-6">
                <!-- Left column area -->

                <!-- Filters -->
                <div class="flex flex-col min-h-0 border-r border-gray-200 divide-y divide-gray-200 bg-white rounded-md shadow-(--shadow-5) overflow-y-auto p-3 gap-2">

                    <p class="text-base font-bold text-gray-900 p-3 flex justify-between">
                        <span><?php esc_html_e('Filter', 'wpcloudplugins'); ?></span>
                        <button type="button" class="wpcp-button-secondary wpcp-proof-collection-filter-reset text-xs px-2 py-0">
                            <span><?php esc_html_e('Reset', 'wpcloudplugins'); ?></span>
                        </button>
                    </p>

                    <!-- Filter: People -->
                    <fieldset class="p-3">
                        <div class="text-base font-bold text-gray-900"><?php esc_html_e('People', 'wpcloudplugins'); ?></div>
                        <div id="wpcp-proof-collection-filter-people" class="mt-4 flex flex-col gap-3 max-h-80 overflow-x-auto">

                            <!-- Skeleton -->
                            <div class="flex gap-3 wpcp-proof-filter-list">
                                <div class="flex shrink-0 items-center">
                                    <img class="size-5 rounded-full" src="https://secure.gravatar.com/avatar/?s=48&d=mm&r=g" alt="">
                                </div>
                                <div class="flex-1 text-gray-900 items-center justify-between w-8 flex gap-2">
                                    <div class="truncate"><?php esc_html_e('No persons found.', 'wpcloudplugins'); ?></div><span class="counter"></span>
                                </div>
                                <div class="flex shrink-0 items-center w-5">

                                    <div class="group grid size-4 grid-cols-1">
                                        <input name="users[]" type="checkbox" class="wpcp-proof-filter wpcp-proof-filter-user col-start-1 row-start-1 appearance-none rounded-xs border border-gray-300 bg-white checked:border-brand-color-700 checked:bg-brand-color-700 indeterminate:border-brand-color-700 indeterminate:bg-brand-color-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-color-700 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto text-brand-color-900 before:hidden hover:ring-brand-color-700 focus:ring-brand-color-700" data-filter-for="users" disabled="disabled">
                                    </div>
                                </div>
                            </div>
                            <!-- End Skeleton -->
                        </div>
                    </fieldset>
                    <!-- End Filter: People -->

                    <!-- Filter: Labels -->
                    <fieldset class="p-3">
                        <div class="text-base font-bold text-gray-900"><?php esc_html_e('Labels', 'wpcloudplugins'); ?></div>
                        <div id="wpcp-proof-collection-filter-labels" class="mt-4 flex flex-col gap-3">

                            <!-- Skeleton -->
                            <div class="flex gap-3 wpcp-proof-filter-list" data-label-id="none">
                                <div class="flex w-6 shrink-0 items-center rounded-full font-medium select-none " style="color: #666666;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class=" h-5 w-5">
                                        <path fill-rule="evenodd" d="M5.25 2.25a3 3 0 0 0-3 3v4.318a3 3 0 0 0 .879 2.121l9.58 9.581c.92.92 2.39 1.186 3.548.428a18.849 18.849 0 0 0 5.441-5.44c.758-1.16.492-2.629-.428-3.548l-9.58-9.581a3 3 0 0 0-2.122-.879H5.25ZM6.375 7.5a1.125 1.125 0 1 0 0-2.25 1.125 1.125 0 0 0 0 2.25Z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 text-gray-900 items-center justify-between w-8 flex gap-2">
                                    <div class="truncate"><?php esc_html_e('No labels found.', 'wpcloudplugins'); ?></div></span>
                                </div>
                                <div class="flex shrink-0 items-center w-5">
                                    <div class="group grid size-4 grid-cols-1">
                                        <input name="labels[]" value="none" type="checkbox" class="wpcp-proof-filter wpcp-proof-filter-label col-start-1 row-start-1 appearance-none rounded-xs border border-gray-300 bg-white checked:border-brand-color-700 checked:bg-brand-color-700 indeterminate:border-brand-color-700 indeterminate:bg-brand-color-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-color-700 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto text-brand-color-900 before:hidden hover:ring-brand-color-700 focus:ring-brand-color-700" data-filter-for="labels" disabled="disabled">
                                    </div>
                                </div>
                            </div>
                            <!-- End Skeleton -->
                        </div>
                    </fieldset>
                    <!-- End Filter: Labels -->


                    <!-- Select -->
                    <button type="button" class="wpcp-button-primary wpcp-proof-collection-select-assets block" data-button-text="<?php esc_attr_e('Select %s Items', 'wpcloudplugins'); ?>">
                        <span><?php esc_html_e('Select all items', 'wpcloudplugins'); ?></span>
                    </button>

                </div>
                <!-- End Filters -->

            </aside>

            <main id="wpcp-proof-collection-main" class="flex flex-col flex-1 rounded-md overflow-auto bg-white shadow-(--shadow-5) p-6 relative">
                <!-- Main area -->

                <div id="loading-overlay" class="absolute inset-0 bg-gradient-to-r from-brand-color-100/20  to-brand-color-700/30 bg-[length:200%_200%] backdrop-blur-md flex items-center justify-center pointer-events-none z-20">
                    <div class="animate-spin h-10 w-10 border-4 border-gray-300 border-t-brand-color-900 rounded-full"></div>
                </div>

                <ul id="wpcp-proof-collection-items" class="w-full grid auto-rows-auto grid-cols-[repeat(auto-fill,minmax(196px,1fr))] gap-x-2 gap-y-6 sm:gap-x-4 xl:gap-x-6 z-10">

                    <li class="wpcp-proof-collection-item relative group aspect-[4/3] select-none">
                        <div class="overflow-hidden rounded-lg bg-gray-100 focus-within:ring-2 focus-within:ring-brand-color-700 focus-within:ring-offset-2 focus-within:ring-offset-gray-100 group-[.wpcp-selected]:ring-2 group-[.wpcp-selected]:ring-offset-4 group-[.wpcp-selected]:ring-brand-color-700 group-[.wpcp-selected]:ring-offset-brand-color-700 group-[.wpcp-selecting]:ring-2 group-[.wpcp-selecting]:ring-offset-4 group-[.wpcp-selecting]:ring-brand-color-700 ">
                            <!-- Image -->
                            <div class="relative w-full pointer-events-none aspect-[4/3]  justify-self-center  group-hover:opacity-75  ">
                            </div>
                        </div>

                        <p class="wpcp-proof-collection-item-name pointer-events-none mt-2 block truncate text-sm font-medium text-gray-900"><?php esc_html_e('There are no items found.', 'wpcloudplugins'); ?></p>
                    </li>

                </ul>
                <div class="wpcp wpcp-selectable-helper hidden"></div>

            </main>

            <aside class="sticky top-8 w-80 shrink-0 flex flex-col gap-6">
                <!-- Right column area -->

                <!-- General -->
                <div class="flex flex-col min-h-0 border-r border-gray-200 divide-y divide-gray-200 bg-white rounded-md shadow-(--shadow-5) overflow-y-auto p-3 gap-2">

                    <fieldset class="p-3">
                        <p class="text-base font-bold text-gray-900"><?php esc_html_e('General', 'wpcloudplugins'); ?></p>
                        <div class="mt-2 max-w-xl text-sm text-gray-500">
                            <p><?php esc_html_e('Direct link to this collection.', 'wpcloudplugins'); ?></p>
                        </div>
                        <div class="mt-2 flex sm:items-center gap-1">
                            <div class="flex-1">
                                <input type="text" id="wpcp-proof-module-url" class="wpcp-input-textbox block w-full shadow-xs focus:ring-brand-color-700 focus:border-brand-color-700 sm:text-sm border border-gray-300 p-2 rounded-md" readonly value="<?php echo esc_url(get_permalink(get_the_ID())); ?>">
                            </div>
                            <!-- Copy -->
                            <button type="button" class="wpcp-button-icon-only wpcp-copy-to-clipboard flex flex-none" data-input="#wpcp-proof-module-url" title="<?php \esc_attr_e('Copy to clipboard', 'wpcloudplugins'); ?>">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5V6.108c0-1.135.845-2.098 1.976-2.192.373-.03.748-.057 1.123-.08M15.75 18H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5A3.375 3.375 0 0 0 6.375 7.5H5.25m11.9-3.664A2.251 2.251 0 0 0 15 2.25h-1.5a2.251 2.251 0 0 0-2.15 1.586m5.8 0c.065.21.1.433.1.664v.75h-6V4.5c0-.231.035-.454.1-.664M6.75 7.5H4.875c-.621 0-1.125.504-1.125 1.125v12c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V16.5a9 9 0 0 0-9-9Z" />
                                </svg>
                            </button>

                            <!-- Open -->
                            <a href="<?php echo Proofing::instance()->get_url_for_selection(); ?>" target="_blank" class="wpcp-button-icon-only wpcp-open-link flex flex-none" title="<?php \esc_attr_e('Open', 'wpcloudplugins'); ?>" onclick="">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                            </a>
                        </div>

                    </fieldset>

                </div>
                <!-- End General -->

                <!-- Selections -->
                <div class="flex flex-col min-h-0 border-r border-gray-200 divide-y divide-gray-200 bg-white rounded-md shadow-(--shadow-5) overflow-y-auto p-3 gap-2">

                    <fieldset class="p-3 flex flex-col">
                        <p class="text-base font-bold text-gray-900"><?php esc_html_e('Selections', 'wpcloudplugins'); ?></p>
                        <div id="wpcp-proof-collection-selections" class="mt-4 flex flex-col gap-3 overflow-hidden w-full">

                            <!-- Skeleton -->
                            <div class="flex items-center gap-x-2">
                                <div class="flex-1 min-w-0">
                                    <div class="text-gray-500 truncate overflow-hidden text-ellipsis whitespace-nowrap">
                                        <p class="text-sm font-semibold truncate text-gray-900"><?php esc_html_e('There are no selections available.', 'wpcloudplugins'); ?></p>
                                    </div>
                                </div>
                                <div class="flex flex-none items-center gap-x-4">

                                </div>
                            </div>

                            <!-- End Skeleton  -->
                        </div>

                        <!-- Add User -->
                        <div class="flex gap-2 mt-5">
                            <input type="text" id="wpcp-proof-collection-add-user-email" class="wpcp-input-textbox block w-full shadow-xs focus:ring-brand-color-700 focus:border-brand-color-700 sm:text-sm border border-gray-300 p-2 rounded-md" placeholder="user@domain.com">

                            <button type="button" class="wpcp-button-secondary wpcp-proof-collection-add-user block" data-button-text="<?php esc_attr_e('Add', 'wpcloudplugins'); ?>">
                                <span><?php esc_html_e('Add', 'wpcloudplugins'); ?></span>
                            </button>
                        </div>

                    </fieldset>

                </div>
                <!-- End Selections -->

            </aside>


        </div>
    </div>

    <!-- Status bar -->
    <div id="wpcp-proof-collection-status-bar" class="fixed bottom-4 left-1/2 -translate-x-1/2 bg-white shadow-lg px-6 py-3 rounded-full border hidden z-50 select-none">
        <div class="flex items-center gap-4">
            <!-- Selected Items Count -->
            <div class="flex gap-2 items-center">
                <div class="wpcp-proof-collection-status-bar-selected-assets text-white bg-brand-color-900 p-2 rounded-full font-bold text-2xl min-w-12 text-center">0</div>
                <div class="flex flex-col items-start gap-0">
                    <span class="text-gray-500 text-sm"><?php esc_html_e('Items', 'wpcloudplugins'); ?></span>
                    <span class="text-gray-500 text-sm"><?php esc_html_e('selected', 'wpcloudplugins'); ?></span>
                </div>
            </div>

            <!-- Separator -->
            <div class="h-6 w-px bg-gray-300"></div>

            <!-- Action Buttons -->
            <button type="button" class="wpcp-modal-open-dialog p-2 rounded-full hover:bg-gray-100" title="<?php esc_attr_e('Copy Filenames', 'wpcloudplugins'); ?>" data-dialog-id="#wpcp-modal-export">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                </svg>
            </button>

            <button type="button" class="wpcp-proof-collection-deselect-assets p-2 rounded-full hover:bg-gray-100" title="<?php esc_attr_e('Deselect items', 'wpcloudplugins'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
    <!-- End Status bar -->

    <!-- Export -->
    <div id="wpcp-modal-export" class="wpcp-dialog hidden">
        <div class="relative z-[1000]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500/90 transition-opacity backdrop-blur-xs"></div>
            <div class="fixed z-30 inset-0 overflow-y-auto">
                <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                    <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full sm:p-6">

                        <div class="absolute right-0 top-0 hidden pr-4 pt-4 sm:block">
                            <button type="button" class="wpcp-dialog-close rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <span class="sr-only">Close</span>
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div>
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-brand-color-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"></path>
                                </svg>

                            </div>
                            <div class="mt-3 text-center sm:mt-5">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Copy Filenames'); ?></h3>
                                <div class="mt-6 w-full sm:flex items-center justify-center">
                                    <textarea id="wpcp-modal-export-filenames" rows="5" class="wpcp-input-textarea max-w-xl block w-full shadow-xs focus:ring-brand-color-700 focus:border-brand-color-700 sm:text-sm border border-gray-300 rounded-md" onclick="this.select();"></textarea>
                                </div>
                            </div>

                        </div>
                        <div class="mt-5">
                            <button type="button" class="wpcp-button-primary wpcp-proof-copy-filenames wpcp-copy-to-clipboard justify-center w-full" data-input="#wpcp-modal-export-filenames"><?php esc_html_e('Copy to clipboard'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Export -->


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

<script id="wpcp-proof-collection-filter-people-template" type="text/template">
    <div class="flex gap-3 wpcp-proof-filter-list" data-user-id="<%= id %>">
        <div class="flex shrink-0 items-center">
            <img class="size-5 rounded-full" src="<%= thumbnail %>" alt="">
        </div>
        <div class="flex-1 text-gray-900 items-center justify-between w-8 flex gap-2">
            <div class="truncate"><%= display_name %></div><span class="counter">(0)</span>
        </div>
        <div class="flex shrink-0 items-center w-5">
            
            <div class="group grid size-4 grid-cols-1">
                <input name="users[]" value="<%= id %>" type="checkbox" class="wpcp-proof-filter wpcp-proof-filter-user col-start-1 row-start-1 appearance-none rounded-xs border border-gray-300 bg-white checked:border-brand-color-700 checked:bg-brand-color-700 indeterminate:border-brand-color-700 indeterminate:bg-brand-color-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-color-700 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto text-brand-color-900 before:hidden hover:ring-brand-color-700 focus:ring-brand-color-700" data-filter-for="users">
            </div>
        </div>
    </div>
</script>

<script id="wpcp-proof-collection-filter-label-template" type="text/template">
    <div class="flex gap-3 wpcp-proof-filter-list" data-label-id="<%= id %>">
        <div class="flex w-6 shrink-0 items-center rounded-full font-medium select-none " style="color: <%= color %>;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class=" h-5 w-5">
                <path fill-rule="evenodd" d="M5.25 2.25a3 3 0 0 0-3 3v4.318a3 3 0 0 0 .879 2.121l9.58 9.581c.92.92 2.39 1.186 3.548.428a18.849 18.849 0 0 0 5.441-5.44c.758-1.16.492-2.629-.428-3.548l-9.58-9.581a3 3 0 0 0-2.122-.879H5.25ZM6.375 7.5a1.125 1.125 0 1 0 0-2.25 1.125 1.125 0 0 0 0 2.25Z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="flex-1 text-gray-900 items-center justify-between w-8 flex gap-2">
            <div class="truncate"><%= title %></div><span class="counter">(0)</span>
        </div>
        <div class="flex shrink-0 items-center w-5">
            <div class="group grid size-4 grid-cols-1">
                <input name="labels[]" value="<%= id %>" type="checkbox" class="wpcp-proof-filter wpcp-proof-filter-label col-start-1 row-start-1 appearance-none rounded-xs border border-gray-300 bg-white checked:border-brand-color-700 checked:bg-brand-color-700 indeterminate:border-brand-color-700 indeterminate:bg-brand-color-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-color-700 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto text-brand-color-900 before:hidden hover:ring-brand-color-700 focus:ring-brand-color-700" data-filter-for="labels">
            </div>
        </div>
    </div>
</script>

<script id="wpcp-proof-collection-selection-item-template" type="text/template">
    <div class="flex items-center gap-x-2" data-selection-ident="<%= ident %>">
        <!-- Approved Toggle -->
        <div class="relative self-center">
            <button type="button" class="wpcp-proof-collection-toggle-approved <%= (approved) ? 'bg-brand-color-900' : 'bg-gray-200' %> relative inline-flex shrink-0 h-4 w-8 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-brand-color-700" role="switch" aria-checked="false">
                <span class="wpcp-input-checkbox-button-container <%= (approved) ? 'translate-x-4' : 'translate-x-0' %> pointer-events-none relative inline-block h-3 w-3 rounded-full bg-white shadow-sm transform ring-0 transition ease-in-out duration-200">
                    <span class="wpcp-input-checkbox-button-off opacity-100 ease-in duration-200 absolute inset-0 h-full w-full flex items-center justify-center transition-opacity" aria-hidden="true">
                        <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 12 12">
                            <path d="M4 8l2-2m0 0l2-2M6 6L4 4m2 2l2 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </span>
                    <span class="wpcp-input-checkbox-button-on opacity-0 ease-out duration-100 absolute inset-0 h-full w-full flex items-center justify-center transition-opacity" aria-hidden="true">
                        <svg class="h-3 w-3 text-brand-color-900" fill="currentColor" viewBox="0 0 12 12">
                            <path d="M3.707 5.293a1 1 0 00-1.414 1.414l1.414-1.414zM5 8l-.707.707a1 1 0 001.414 0L5 8zm4.707-3.293a1 1 0 00-1.414-1.414l1.414 1.414zm-7.414 2l2 2 1.414-1.414-2-2-1.414 1.414zm3.414 2l4-4-1.414-1.414-4 4 1.414 1.414z"></path>
                        </svg>
                    </span>
                </span>
                <input type="checkbox" class="hidden" name="wpcp-proof-collection-toggle-approved" checked="<%= (approved) ? 'checked' : '' %>">
            </button>
        </div>
        <!-- End Approved Toggle -->

        <div class="flex-1 min-w-0">
            <div class="text-gray-500 flex flex-col whitespace-normal break-words">
                <p class="text-sm font-semibold truncate text-gray-900" title="<%= user_email %>"><%= display_name %></p>
                <% if (display_name !== user_email){ %>
                    <p class="text-xs text-gray-500 truncate overflow-hidden text-ellipsis whitespace-nowrap"  title="<%= user_email %>"><%= user_email %></p>
                <% } %>
                <p class="text-xs font-light text-gray-500 truncate "><%= (approved) ? approval_time_text : last_modified_time_text %></p>
                <% if (approval_message){ %>
                <p class="text-xs font-light text-gray-500 italic pt-2"><span class="whitespace-normal break-words"><%= approval_message %></span></p>
                <% } %>
            </div>
        </div>
        <div class="flex flex-none items-center gap-x-4">
            <div class="relative flex-none">
                <!-- Open -->
                <a href="<%= selection_url %>" target="_blank" class="wpcp-button-icon-only wpcp-open-link" title="<?php \esc_attr_e('Open', 'wpcloudplugins'); ?>" onclick="">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                    </svg>
                </a>

                <!-- Download Proof -->
                <a href="<%= download_proof_url %>" download class="wpcp-button-icon-only wpcp-open-link" title="<?php esc_html_e('Download Proof', 'wpcloudplugins'); ?> (.txt)">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                </a>

                <!-- Remove -->
                <button type="button" class="wpcp-button-icon-only wpcp-proof-collection-delete-selection" title="<?php \esc_attr_e('Remove', 'wpcloudplugins'); ?>">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</script>

<script id="wpcp-proof-collection-item-template" type="text/template">
    <li class="wpcp-proof-collection-item relative group aspect-[4/3] select-none" data-id="<%= item.id %>" data-filename="<%= item.name %>">
        <div class="overflow-hidden rounded-lg bg-gray-100 focus-within:ring-2 focus-within:ring-brand-color-700 focus-within:ring-offset-2 focus-within:ring-offset-gray-100 group-[.wpcp-selected]:ring-2 group-[.wpcp-selected]:ring-offset-4 group-[.wpcp-selected]:ring-brand-color-700 group-[.wpcp-selected]:ring-offset-brand-color-700 group-[.wpcp-selecting]:ring-2 group-[.wpcp-selecting]:ring-offset-4 group-[.wpcp-selecting]:ring-brand-color-700 ">
            <!-- Image -->
            <div class="relative w-full pointer-events-none aspect-[4/3]  justify-self-center <% if (item.missing) { %> opacity-25 <% } else { %> group-hover:opacity-75 <% }; %> ">
                <img src="<%= item.thumbnail %>" class="absolute inset-0 w-full h-full object-contain pointer-events-none" alt="" loading="lazy" referrerPolicy='no-referrer'>
            </div>

            <button type="button" class="absolute inset-0 focus:outline-hidden">
                <span class="sr-only"><?php esc_html_e('View details', 'wpcloudplugins'); ?></span>
            </button>

            <!-- Labels -->
            <div class="wpcp-proof-collection-item-label absolute -top-2 right-1 flex flex-wrap gap-1 justify-end">
                <% if (item.missing) { %>
                <span class="inline-flex items-center rounded-full px-3 py-0 text-xs font-medium text-white select-none bg-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                    </svg>
                </span>
                <% }; %>
                <% _.each(labels, function(label, key) { %>
                <span class="wpcp-proof-collection-item-label-icon inline-flex items-center rounded-full px-2 text-xs font-medium text-white select-none bg-gray-700 group/label" style="background-color: <%= label.color %>;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
                        <path fill-rule="evenodd" d="M5.25 2.25a3 3 0 0 0-3 3v4.318a3 3 0 0 0 .879 2.121l9.58 9.581c.92.92 2.39 1.186 3.548.428a18.849 18.849 0 0 0 5.441-5.44c.758-1.16.492-2.629-.428-3.548l-9.58-9.581a3 3 0 0 0-2.122-.879H5.25ZM6.375 7.5a1.125 1.125 0 1 0 0-2.25 1.125 1.125 0 0 0 0 2.25Z" clip-rule="evenodd" />
                    </svg>
                    <p class="wpcp-proof-collection-item-label-text ml-1.5 truncate"><%= label.title %></p>
                </span>
                <% }); %>
            </div>
        </div>
        
        <p class="wpcp-proof-collection-item-name pointer-events-none mt-2 block truncate text-sm font-medium text-gray-900"><%= item.name %></p>
    </li>
</script>