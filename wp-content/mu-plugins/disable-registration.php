<?php
/*
Plugin Name: Disable User Registration (Multisite)
Description: Disabilita la registrazione di utenti e siti in tutta la rete multisite.
Version: 1.0
Author: Mario
*/

// Forza la disabilitazione della registrazione
add_filter('site_option_registration', '__return_false');
add_filter('site_option_users_can_register', '__return_false');

// Blocca l'accesso diretto a wp-signup.php e wp-login.php?action=register
function blocca_registrazione_multisite() {
    if (
        (isset($_GET['action']) && $_GET['action'] === 'register') ||
        (strpos($_SERVER['REQUEST_URI'], 'wp-signup.php') !== false)
    ) {
        wp_redirect(network_home_url());
        exit;
    }
}
add_action('init', 'blocca_registrazione_multisite');