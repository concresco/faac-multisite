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
    !Helpers::check_user_role(Settings::get('permissions_link_users'))
) {
    exit;
}

?>
<div id="wpcp" class="wpcp-app hidden" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
    <div class="absolute z-10 bg-gray-100">
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
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                                <?php esc_html_e('Connect Personal Folders', 'wpcloudplugins'); ?>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-0.5 text-xs font-medium text-gray-800">
                                    <svg class="mr-1.5 h-2 w-2 text-gray-400" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3"></circle>
                                    </svg>
                                    Manual mode
                                </span>
                            </h1>
                        </div>
                    </nav>

                    <div class="flex-1 w-full mx-auto px-4 py-4 sm:px-6 lg:px-8">
                        <div class="flow-root min-w-full overflow-auto">
                            <form method="post">
                                <input type="hidden" name="page" /> <?php
                                    $users_list = new User_List_Table();
$users_list->views();
$users_list->prepare_items();
$users_list->search_box('search', 'search_id');
$users_list->display();
?>
                            </form>
                        </div>
                    </div>
                </main>
                <!-- End Page -->

                <footer>
                    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="border-t border-gray-200 py-4 text-sm text-gray-500 text-center sm:text-left">
                            <span class="block sm:inline">
                                <?php AdminLayout::render_notice(esc_html__('Only manually added links between users and folders are displayed in this view. If you are using Dynamic Folders in Auto mode, these links are not displayed in this table.', 'wpcloudplugins'), 'info'); ?>
                            </span>
                        </div>
                    </div>
                </footer>

            </div>


        </div>
    </div>

    <!-- Modal Selector -->
    <div id="wpcp-modal-selector" class="wpcp-dialog hidden">
        <div class="relative z-[10000]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500/90 transition-opacity backdrop-blur-xs"></div>
            <div class="fixed z-30 inset-0 overflow-y-auto">
                <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                    <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full sm:p-6">
                        <div>
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-brand-color-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>

                            </div>
                            <div class="mt-3 text-center sm:mt-5">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Select Personal Folder', 'wpcloudplugins'); ?></h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        <?php esc_html_e("Select the user's Personal Folder. Set the Personal Folders feature to 'manual' to give the user access to this folder only.", 'wpcloudplugins'); ?>
                                    </p>
                                </div>

                                <div class="mt-6 mb-4 sm:flex items-center justify-center">
                                    <div id='oftb-embedded' class="w-full">
                                        <?php echo Processor::instance()->create_from_shortcode(
                                            [
                                                'singleaccount' => '0',
                                                'maxheight' => '400px',
                                                'mode' => 'files',
                                                'filelayout' => 'list',
                                                'filesize' => '0',
                                                'filedate' => '0',
                                                'upload' => '0',
                                                'delete' => '0',
                                                'rename' => '0',
                                                'addfolder' => '0',
                                                'showfiles' => '0',
                                                'downloadrole' => 'none',
                                                'candownloadzip' => '0',
                                                'showsharelink' => '0',
                                                'popup' => 'personal_folders_selector',
                                                'search' => '1',
                                            ]
                                        ); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:flex sm:gap-3 sm:flex-row-reverse">
                            <button type="button" class="wpcp-button-primary wpcp-dialog-entry-select inline-flex justify-center w-full sm:w-auto"><?php esc_html_e('Select'); ?></button>
                            <button type="button" class="wpcp-button-secondary wpcp-dialog-close inline-flex justify-center w-full sm:w-auto"><?php esc_html_e('Close'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal Selector -->
</div>


<style>
#wpcp table {
    border: none;
}

#wpcp tfoot {
    display: none;
}


#wpcp tbody tr:hover {
    background-color: rgb(0 0 0 / 7%) !important;
}

#wpcp td {
    padding: 8px 4px;
    vertical-align: middle;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

#wpcp .column-avatar {
    width: 64px;
}

#wpcp .column-username {
    width: 150px;
}

#wpcp .column-role {
    width: 100px;
}

#wpcp .column-role {
    width: 100px;
}

#wpcp .column-buttons {
    width: 48px;
    text-align: center;
}
</style>