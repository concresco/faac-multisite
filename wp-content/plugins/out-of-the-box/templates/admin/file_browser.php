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
    !Helpers::check_user_role(Settings::get('permissions_see_filebrowser'))
) {
    exit;
}

?>
<div id="wpcp" class="wpcp-app hidden" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
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
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878m0 0a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122" />
                                </svg>
                                <?php esc_html_e('File Browser', 'wpcloudplugins'); ?>
                            </h1>

                        </div>
                    </nav>

                    <div class="flex-1 w-full mx-auto">
                        <div class="flow-root min-w-full overflow-auto">

                            <?php if (false === Accounts::instance()->has_accounts()) {
                                include_once 'no_account_linked.php';
                            } else {
                                ?>


                            <div class="bg-white rounded-lg shadow">

                                <?php $processor = Processor::instance();

                                $params = [
                                    'singleaccount' => '0',
                                    'mode' => 'files',
                                    'filelayout' => 'grid',
                                    'viewrole' => 'all',
                                    'downloadrole' => 'all',
                                    'uploadrole' => 'all',
                                    'upload' => '1',
                                    'rename' => '1',
                                    'delete' => '1',
                                    'deletefilesrole' => 'all',
                                    'deletefoldersrole' => 'all',
                                    'addfolder' => '1',
                                    'search' => '1',
                                    'searchcontents' => '1',
                                    'edit' => '1',
                                    'move' => '1',
                                    'copy' => '1',
                                    'candownloadzip' => '1',
                                    'showsharelink' => '1',
                                    'deeplink' => '1',
                                    'editdescription' => '1',
                                    'import' => '1',
                                    'themestyle' => 'light',
                                    'maxheight' => 'calc(100vh - 220px)',
                                ];

                                $user_folder_backend = apply_filters('outofthebox_use_user_folder_backend', Settings::get('userfolder_backend'));

                                if ('No' !== $user_folder_backend) {
                                    $params['userfolders'] = $user_folder_backend;

                                    $private_root_folder = Settings::get('userfolder_backend_auto_root');
                                    if ('auto' === $user_folder_backend && !empty($private_root_folder) && isset($private_root_folder['id'])) {
                                        if (!isset($private_root_folder['account']) || empty($private_root_folder['account'])) {
                                            $main_account = Accounts::instance()->get_primary_account();
                                            $params['account'] = $main_account->get_id();
                                        } else {
                                            $params['account'] = $private_root_folder['account'];
                                        }

                                        $params['dir'] = $private_root_folder['id'];

                                        if (!isset($private_root_folder['view_roles']) || empty($private_root_folder['view_roles'])) {
                                            $private_root_folder['view_roles'] = ['none'];
                                        }
                                        $params['viewuserfoldersrole'] = implode('|', $private_root_folder['view_roles']);
                                    }
                                }

                                $params = apply_filters('outofthebox_set_shortcode_filebrowser_backend', $params);

                                echo $processor->create_from_shortcode($params);

                                ?>
                            </div>


                            <?php }
                            ?>

                        </div>
                    </div>
                </main>
                <!-- End Page -->

            </div>


        </div>
    </div>
</div>