<?php
/**
 * Force load essential plugins before theme initialization
 * This ensures Timber and ACF are available when the theme tries to use them
 */

// Load ACF Pro first
$acf_plugin_path = WP_CONTENT_DIR . '/plugins/advanced-custom-fields-pro/acf.php';
if (file_exists($acf_plugin_path)) {
    require_once $acf_plugin_path;
}

// Then load Timber
$timber_plugin_path = WP_CONTENT_DIR . '/plugins/timber-library/timber.php';
if (file_exists($timber_plugin_path)) {
    require_once $timber_plugin_path;
} 