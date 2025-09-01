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
?>
<div id="wpcp" class="wpcp-app">

    <!-- Modal Selector -->
    <div id="wpcp-modal-selector-dropbox" class="wpcp-dialog hidden">
        <div class="relative z-[999999]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500/90 transition-opacity backdrop-blur-xs"></div>
            <div class="fixed z-30 inset-0 overflow-y-auto">
                <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">

                    <div class="relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full sm:p-6">
                        <div>
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-brand-color-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-5">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"><?php esc_html_e('Select files or folders', 'wpcloudplugins'); ?></h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        <?php esc_html_e('Select the content you want to add.', 'wpcloudplugins'); ?>
                                    </p>
                                </div>

                                <div class="mt-6 mb-4 sm:flex items-center justify-center">
                                    <div id='oftb-embedded' class="w-full">
                                        <?php
                                            $processor = Processor::instance();
$params = [
    'singleaccount' => '0',
    'dir' => '',
    'mode' => 'files',
    'filelayout' => 'list',
    'maxheight' => '300px',
    'hoverthumbs' => '0',
    'filesize' => '0',
    'filedate' => '0',
    'addfolder' => '0',
    'downloadrole' => 'none',
    'previewrole' => 'none',
    'search' => '1',
    'candownloadzip' => '0',
    'showsharelink' => '0',
    'popup' => 'selector',
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
$params = apply_filters('outofthebox_set_shortcode_file_selector', $params, 'edd');

echo $processor->create_from_shortcode($params);
?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                            <button type="button" class="wpcp-button-secondary wpcp-dialog-close inline-flex justify-center w-full sm:w-auto"><?php esc_html_e('Close'); ?></button>
                            <button type="button" class="wpcp-button-primary wpcp-edd-dialog-entry-select inline-flex justify-center w-full sm:w-auto"><?php esc_html_e('Add'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal Selector -->

    <!-- Notification -->
    <div id="wpcp-notification" aria-live="assertive" class="fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:p-6 sm:items-end z-[1000000]" style="display:none;">
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
                        <div class="ml-3 w-0 flex-1 pt-0.5 line-clamp-10">
                            <p class="wpcp-notification-message wpcp-notification-success text-sm font-medium text-gray-900" data-default-text="<?php echo sprintf(esc_html__('%s added as downloadable file!', 'wpcloudplugins'), '{filename}'); ?>"></p>
                            <p class="wpcp-notification-message wpcp-notification-failed text-sm font-medium text-red-400" data-default-text="<?php echo sprintf(esc_html__('Cannot add %s!', 'wpcloudplugins'), '{filename}'); ?>"></p>
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