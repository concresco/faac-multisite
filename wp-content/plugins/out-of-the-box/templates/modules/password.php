<?php

use TheLion\OutoftheBox\Processor;
use TheLion\OutoftheBox\Restrictions;
use TheLion\OutoftheBox\Settings;

if (!empty($hash = Processor::instance()->get_shortcode_option('password_hash'))) {
    $is_unlocked = Restrictions::instance()->unlock_module();

    ?>
<div class="wpcp-login-container <?php echo ($is_unlocked) ? 'wpcp-login-unlocked' : ''; ?>" data-pass="<?php ($is_unlocked) ? esc_attr_e($hash) : ''; ?>">
    <?php echo Settings::get('accessibility_passwordmessage'); ?>
    <div class="wpcp-login-form">
        <input class="wpcp-login-input" name="wpcp-login" type="password" autocomplete="off" size="40" spellcheck="false" placeholder="<?php esc_html_e('Enter password', 'wpcloudplugins'); ?>" aria-label="<?php esc_html_e('Password', 'wpcloudplugins'); ?>" />
        <button class="wpcp-login-submit button" type="submit"><i class="eva eva-unlock-outline eva-lg" aria-hidden="true"></i> <?php esc_html_e('Unlock', 'wpcloudplugins'); ?></button>
    </div>
</div>

<?php
}
