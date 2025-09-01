<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       3.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

defined('ABSPATH') || exit;

// Exit if no permission
if (
    !Helpers::check_user_role(Settings::get('permissions_add_shortcodes'))
) {
    exit;
}

?>
<div id="wpcp" class="wpcp-app hidden" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
    <div class="absolute z-10 inset-0 bg-gray-100">
        <div class="min-h-[calc(100vh-32px)] flex flex-col">
            <iframe class='w-full h-full grow' src='<?php echo OUTOFTHEBOX_ADMIN_URL; ?>?action=outofthebox-getpopup&type=modules' tabindex='-1' style='border:none' title=''></iframe>
        </div>
    </div>
</div>