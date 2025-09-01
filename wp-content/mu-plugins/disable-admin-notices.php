<?php
/**
 * Plugin Name: FAAC Customizations
 * Description: Disables WPForms education scripts and styles on post edit screens to prevent JS errors.
 * Version: 1.1
 * Author: AI Assistant
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Dequeue WPForms education scripts and styles from post edit screens.
 *
 * The script 'wpforms-edit-post-education' causes a JS error on faac.at.
 * This function prevents it and its associated stylesheet from loading.
 */
function faac_remove_wpforms_education_assets() {
    // Only run on post edit screens (post.php, post-new.php).
    $screen = get_current_screen();
    if ( ! $screen || $screen->base !== 'post' ) {
        return;
    }

    // Dequeue the script causing the error.
    wp_dequeue_script( 'wpforms-edit-post-education' );
    wp_deregister_script( 'wpforms-edit-post-education' );

    // Dequeue the associated style.
    wp_dequeue_style( 'wpforms-edit-post-education' );
    wp_deregister_style( 'wpforms-edit-post-education' );
}

// Hook into 'admin_enqueue_scripts' with a late priority to ensure it runs after WPForms.
add_action( 'admin_enqueue_scripts', 'faac_remove_wpforms_education_assets', 999 ); 