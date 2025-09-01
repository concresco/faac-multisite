<?php

use TheLion\OutoftheBox\Processor;
use TheLion\OutoftheBox\Settings;

// Display login Screen
if ('1' === Processor::instance()->get_shortcode_option('display_loginscreen') && false === is_user_logged_in()) {
    $login_url = Settings::get('accessibility_loginurl');
    if (empty($login_url)) {
        global $wp;
        $login_url = wp_login_url(home_url($wp->request));
    }

    $module_id = Processor::instance()->get_shortcode_option('module_id');
    if (empty($module_id)) {
        $module_id = 'wpcp-'.Processor::instance()->get_listtoken();
    }
    ?>

<div id='<?php echo $module_id; ?>' class='wpcp-container'>
    <div id='OutoftheBox' data-module-id='<?php echo $module_id; ?>'>
        <div id='OutoftheBox-<?php echo Processor::instance()->get_listtoken(); ?>' class='wpcp-module OutoftheBox'>
            <div class="wpcp-login-container" style="position:relative;">
                <?php echo Settings::get('accessibility_loginmessage'); ?>
                <div class="wpcp-login-form">
                    <button onclick="javascript:window.location='<?php echo $login_url; ?>';" class="button"><i class="eva eva-arrowhead-right-outline eva-lg" aria-hidden="true"></i> <?php esc_html_e('Login', 'wpcloudplugins'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
}
