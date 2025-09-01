<?php
/*
Plugin Name: Forms reCAPTCHA Integration (Network)
Description: Applica reCAPTCHA v2 Invisible a CF7 e WPForms con verifica server-side.
Version: 1.0.0
Author: FAAC SecOps
*/

if (!defined('ABSPATH')) { exit; }

function faacsec_forms_recaptcha_enabled(): bool {
    return defined('RECAPTCHA_SITE_KEY') && RECAPTCHA_SITE_KEY && defined('RECAPTCHA_SECRET') && RECAPTCHA_SECRET;
}

// --------------------------
// Contact Form 7
// --------------------------
add_action('wp_enqueue_scripts', function(){
    if (!faacsec_forms_recaptcha_enabled()) { return; }
    if (!is_singular()) { return; }
    // Carica script google solo quando necessario; CF7 di solito usa shortcode/contact form assets
    wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', [], null, true);
});

// Inietta reCAPTCHA invisibile nei form CF7
add_filter('wpcf7_form_elements', function($content){
    if (!faacsec_forms_recaptcha_enabled()) { return $content; }
    // Aggiunge il container g-recaptcha al termine del form
    $widget = '<div class="g-recaptcha" data-sitekey="' . esc_attr(RECAPTCHA_SITE_KEY) . '" data-size="invisible"></div>';
    if (strpos($content, 'g-recaptcha') === false) {
        $content .= $widget;
    }
    return $content;
});

// Verifica server-side per CF7
add_filter('wpcf7_validate', function($result, $tags){
    if (!faacsec_forms_recaptcha_enabled()) { return $result; }
    $token = isset($_POST['g-recaptcha-response']) ? trim((string) $_POST['g-recaptcha-response']) : '';
    if ($token === '') {
        $result->invalidate([], __('reCAPTCHA verification failed.'));
        return $result;
    }
    $resp = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
        'timeout' => 5,
        'body' => [
            'secret' => RECAPTCHA_SECRET,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ],
    ]);
    if (is_wp_error($resp)) {
        $result->invalidate([], __('Unable to verify reCAPTCHA. Please try again.'));
        return $result;
    }
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    if (empty($data['success'])) {
        $result->invalidate([], __('reCAPTCHA verification failed.'));
    }
    return $result;
}, 10, 2);

// --------------------------
// WPForms
// --------------------------
// Iniezione script su pagine con shortcode WPForms
add_action('wp_enqueue_scripts', function(){
    if (!faacsec_forms_recaptcha_enabled()) { return; }
    if (!is_singular()) { return; }
    if (has_shortcode(get_post_field('post_content', get_queried_object_id()), 'wpforms')) {
        wp_enqueue_script('google-recaptcha');
    }
});

// Verifica server-side prima del processamento di WPForms
add_action('wpforms_process_before_form_data', function($fields, $entry, $form_data){
    if (!faacsec_forms_recaptcha_enabled()) { return; }
    $token = isset($_POST['g-recaptcha-response']) ? trim((string) $_POST['g-recaptcha-response']) : '';
    if ($token === '') {
        wpforms()->process->errors[$form_data['id']]['header'] = __('reCAPTCHA verification failed.');
        return;
    }
    $resp = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
        'timeout' => 5,
        'body' => [
            'secret' => RECAPTCHA_SECRET,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ],
    ]);
    if (is_wp_error($resp)) {
        wpforms()->process->errors[$form_data['id']]['header'] = __('Unable to verify reCAPTCHA. Please try again.');
        return;
    }
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    if (empty($data['success'])) {
        wpforms()->process->errors[$form_data['id']]['header'] = __('reCAPTCHA verification failed.');
    }
}, 10, 3);


