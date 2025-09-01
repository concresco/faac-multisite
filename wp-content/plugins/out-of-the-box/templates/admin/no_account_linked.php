<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.13.4
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

defined('ABSPATH') || exit;
?>
<div class="fixed inset-0 bg-gray-500/20 transition-opacity" aria-hidden="true"></div>
<div class="absolute inset-0 z-10 overflow-y-auto">
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-sm sm:p-6">
            <div>
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-color-100">
                    <svg class="h-6 w-6 text-brand-color-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title"><?php \esc_html_e('No account connected yet'); ?></h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500"><?php echo sprintf(esc_html__("The plugin isn't currently connected to a %s account. Add an account to get access to your content in the cloud.", 'wpcloudplugins'), 'Dropbox'); ?></p>
                    </div>
                </div>
            </div>
            <?php
                                if (Helpers::check_user_role(Settings::get('permissions_edit_settings'))) {
                                    ?>
            <div class="mt-5 sm:mt-6">
                <a href="<?php echo admin_url('admin.php?page=OutoftheBox_settings'); ?>" target="_top" class="inline-flex w-full justify-center wpcp-button-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="-ml-0.5 mr-2 h-4 w-4"  viewBox=" 0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                    </svg>
                    <?php \esc_html_e('Add Account'); ?>
                </a>
            </div>
            <?php
                                }
?>
        </div>
    </div>
</div>