<?php
/*
Plugin Name: Login Security Hardening (Network)
Description: Throttling dei tentativi di login per IP/utente e reCAPTCHA invisibile sul login se configurato.
Version: 1.0.0
Author: FAAC SecOps
*/

if (!defined('ABSPATH')) {
    exit;
}

// =====================
// Configurazione
// =====================
define('FAACSEC_LOGIN_MAX_ATTEMPTS', 5);          // tentativi per finestra
define('FAACSEC_LOGIN_WINDOW_SECONDS', 10 * 60);  // 10 minuti
define('FAACSEC_LOGIN_LOCK_SECONDS', 15 * 60);    // lock 15 minuti

// Chiavi reCAPTCHA: usa costanti o variabili d'ambiente se disponibili
$faacsec_recaptcha_site_key = defined('RECAPTCHA_SITE_KEY') ? RECAPTCHA_SITE_KEY : getenv('RECAPTCHA_SITE_KEY');
$faacsec_recaptcha_secret   = defined('RECAPTCHA_SECRET') ? RECAPTCHA_SECRET : getenv('RECAPTCHA_SECRET');

// =====================
// Helper Rate Limit
// =====================
function faacsec_get_client_ip(): string
{
    foreach ([
        'HTTP_CF_CONNECTING_IP', // Cloudflare
        'HTTP_X_FORWARDED_FOR',  // Proxies
        'HTTP_X_REAL_IP',
        'REMOTE_ADDR',
    ] as $key) {
        if (!empty($_SERVER[$key])) {
            $value = $_SERVER[$key];
            if ($key === 'HTTP_X_FORWARDED_FOR') {
                $parts = array_map('trim', explode(',', $value));
                $value = $parts[0] ?? $value;
            }
            return $value;
        }
    }
    return '0.0.0.0';
}

function faacsec_rate_key_for_ip(string $ip): string { return 'faacsec_login_ip_' . md5($ip); }
function faacsec_rate_key_for_user(string $user): string { return 'faacsec_login_user_' . md5(strtolower($user)); }
function faacsec_lock_key_for_ip(string $ip): string { return 'faacsec_login_lock_ip_' . md5($ip); }
function faacsec_lock_key_for_user(string $user): string { return 'faacsec_login_lock_user_' . md5(strtolower($user)); }

function faacsec_is_locked(string $ip, string $user): bool
{
    return (bool) get_transient(faacsec_lock_key_for_ip($ip)) || (bool) get_transient(faacsec_lock_key_for_user($user));
}

function faacsec_register_failed_attempt(string $ip, string $user): void
{
    $ipKey = faacsec_rate_key_for_ip($ip);
    $uKey  = faacsec_rate_key_for_user($user);

    $ipAttempts = (int) get_transient($ipKey);
    $uAttempts  = (int) get_transient($uKey);

    $ipAttempts++;
    $uAttempts++;

    set_transient($ipKey, $ipAttempts, FAACSEC_LOGIN_WINDOW_SECONDS);
    set_transient($uKey, $uAttempts, FAACSEC_LOGIN_WINDOW_SECONDS);

    if ($ipAttempts >= FAACSEC_LOGIN_MAX_ATTEMPTS) {
        set_transient(faacsec_lock_key_for_ip($ip), 1, FAACSEC_LOGIN_LOCK_SECONDS);
    }
    if ($uAttempts >= FAACSEC_LOGIN_MAX_ATTEMPTS) {
        set_transient(faacsec_lock_key_for_user($user), 1, FAACSEC_LOGIN_LOCK_SECONDS);
    }
}

// Blocca login se in lock window
add_filter('authenticate', function($userOrError, $username) {
    // Consenti flussi non interattivi a proseguire (se non hanno username, non è un normale login form)
    if (empty($username)) {
        return $userOrError;
    }

    $ip = faacsec_get_client_ip();
    if (faacsec_is_locked($ip, $username)) {
        return new WP_Error('too_many_attempts', __('Too many failed login attempts. Please try again later.'));
    }

    return $userOrError;
}, 5, 2);

// Conta i fallimenti e applica lock
add_action('wp_login_failed', function($username) {
    $ip = faacsec_get_client_ip();
    faacsec_register_failed_attempt($ip, (string) $username);
});

// Reset parziale su login riuscito
add_action('wp_login', function($user_login) {
    $ip = faacsec_get_client_ip();
    delete_transient(faacsec_rate_key_for_ip($ip));
    delete_transient(faacsec_rate_key_for_user((string) $user_login));
}, 10, 1);

// =====================
// reCAPTCHA invisibile su login (se configurato)
// =====================
function faacsec_recaptcha_is_enabled(): bool
{
    global $faacsec_recaptcha_site_key, $faacsec_recaptcha_secret;
    return !empty($faacsec_recaptcha_site_key) && !empty($faacsec_recaptcha_secret);
}

// Inietta widget invisibile nel form di login
add_action('login_form', function() {
    if (!faacsec_recaptcha_is_enabled()) {
        return;
    }
    global $faacsec_recaptcha_site_key;
    echo '<div class="g-recaptcha" data-sitekey="' . esc_attr($faacsec_recaptcha_site_key) . '" data-size="invisible" data-badge="bottomright" data-callback="faacsecOnSubmit"></div>';
});

// Carica script Google e handler
add_action('login_enqueue_scripts', function() {
    if (!faacsec_recaptcha_is_enabled()) {
        return;
    }
    wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', [], null, true);
    $inline = 'window.faacsecOnSubmit=function(){var f=document.getElementById("loginform");if(f){f.submit();}};(function(){var f=document.getElementById("loginform");if(!f)return;var btn=f.querySelector("#wp-submit");if(!btn)return;btn.addEventListener("click",function(e){var w=window.grecaptcha;if(w&&w.execute){e.preventDefault();w.execute();}});})();';
    wp_add_inline_script('google-recaptcha', $inline);
});

// Verifica server-side del token al login
add_filter('authenticate', function($userOrError, $username) {
    if (!faacsec_recaptcha_is_enabled()) {
        return $userOrError;
    }

    // Verifica solo per richieste login form (POST, presenza pwd)
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $userOrError;
    }
    if (empty($_POST['log']) || empty($_POST['pwd'])) {
        return $userOrError;
    }

    $token = isset($_POST['g-recaptcha-response']) ? trim((string) $_POST['g-recaptcha-response']) : '';
    if ($token === '') {
        return new WP_Error('recaptcha_missing', __('reCAPTCHA verification failed.'));
    }

    global $faacsec_recaptcha_secret;
    $resp = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
        'timeout' => 5,
        'body'    => [
            'secret'   => $faacsec_recaptcha_secret,
            'response' => $token,
            'remoteip' => faacsec_get_client_ip(),
        ],
    ]);

    if (is_wp_error($resp)) {
        return new WP_Error('recaptcha_http_error', __('Unable to verify reCAPTCHA. Please try again.'));
    }

    $code = (int) wp_remote_retrieve_response_code($resp);
    $body = wp_remote_retrieve_body($resp);
    $data = json_decode($body, true);
    if ($code !== 200 || empty($data['success'])) {
        return new WP_Error('recaptcha_failed', __('reCAPTCHA verification failed.'));
    }

    return $userOrError;
}, 8, 2);


