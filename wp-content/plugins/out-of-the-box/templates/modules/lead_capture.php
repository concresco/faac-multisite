<?php

use TheLion\OutoftheBox\LeadCapture;
use TheLion\OutoftheBox\Processor;
use TheLion\OutoftheBox\Settings;

if ('1' === Processor::instance()->get_shortcode_option('requires_lead')) {
    $has_lead = LeadCapture::instance()->unlock_module();

    ?>
<div class="wpcp-lead-container <?php echo ($has_lead) ? 'wpcp-lead-unlocked' : ''; ?>" wpcp-user-lead="<?php ($has_lead) ? esc_attr_e(LeadCapture::instance()->get_lead_email()) : ''; ?>">
    <?php echo Settings::get('accessibility_leadmessage'); ?>
    <div class="wpcp-lead-form">
        <input class="wpcp-lead-input" name="wpcp-lead" type="email" autocomplete="off" size="40" spellcheck="false" placeholder="<?php esc_html_e('Enter your e-mail address', 'wpcloudplugins'); ?>" aria-label="<?php esc_html_e('Email', 'wpcloudplugins'); ?>" />
        <button class="wpcp-lead-submit button" type="submit"><i class="eva eva-unlock-outline eva-lg" aria-hidden="true"></i> <?php esc_html_e('Continue', 'wpcloudplugins'); ?></button>
    </div>
</div>
<?php
}
