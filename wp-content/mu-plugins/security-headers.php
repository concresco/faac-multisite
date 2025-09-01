<?php
/*
Plugin Name: Security Headers (Network)
Description: Imposta header di sicurezza a livello applicativo (senza CSP).
Version: 1.0.0
Author: FAAC SecOps
*/

if (!defined('ABSPATH')) { exit; }

add_action('send_headers', function() {
    // HSTS solo se la richiesta è HTTPS
    if (is_ssl()) {
        // 1 anno + include subdomini + preload
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload', true);
    }

    // Clickjacking protection
    header('X-Frame-Options: SAMEORIGIN', true);

    // MIME sniffing protection
    header('X-Content-Type-Options: nosniff', true);

    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin', true);

    // Feature permissions
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()', true);
});


