<?php

// BEGIN iThemes Security - Do not modify or remove this line
// iThemes Security Config Details: 2
define( 'DISALLOW_FILE_EDIT', true ); // Disable File Editor - Security > Settings > WordPress Tweaks > File Editor
// END iThemes Security - Do not modify or remove this line

define( 'ITSEC_ENCRYPTION_KEY', 'YOUR_ITSEC_ENCRYPTION_KEY_HERE' );

define( 'WP_CACHE', true );

/** Enable W3 Total Cache */
 // Added by W3 Total Cache

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'your_database_name' );

/** Database username */
define( 'DB_USER', 'your_database_user' );

/** Database password */
define( 'DB_PASSWORD', 'your_database_password' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'your_auth_key_here' );
define( 'SECURE_AUTH_KEY',  'your_secure_auth_key_here' );
define( 'LOGGED_IN_KEY',    'your_logged_in_key_here' );
define( 'NONCE_KEY',        'your_nonce_key_here' );
define( 'AUTH_SALT',        'your_auth_salt_here' );
define( 'SECURE_AUTH_SALT', 'your_secure_auth_salt_here' );
define( 'LOGGED_IN_SALT',   'your_logged_in_salt_here' );
define( 'NONCE_SALT',       'your_nonce_salt_here' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'pr_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'ENV', 'production' ); // Change to 'development' for dev environments
define('API_PIM', 'https://pimiaki.faac.help/api/');
//define('API_PIM', 'https://pim-staging.iaki.it/api/'); // Use for staging
define('URL_ENCRYPTION_KEY','your_encryption_key_here');
define('URL_ENCRYPTION_IV','your_encryption_iv_here');
define('URL_CIPHERING','aes-256-ctr');

define('FS_METHOD','direct');

define( 'WP_DEBUG', false ); // Set to true for development
define( 'WP_DEBUG_LOG', false ); // Set to true for development
define( 'WP_DEBUG_DISPLAY', false );
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', true );
define( 'SUNRISE', true );
define( 'DOMAIN_CURRENT_SITE', 'your_main_domain.com' );
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );
define( 'COOKIE_DOMAIN', $_SERVER['HTTP_HOST']);
define('DISABLE_WP_CRON', true);

/* Add any custom values between this line and the "stop editing" line. */

// Mailgun Configuration
define('MAILGUN_USEAPI', true);
define('MAILGUN_APIKEY', 'your_mailgun_api_key');
define('MAILGUN_DOMAIN', 'your_mailgun_domain');
define('MAILGUN_FROM_NAME', 'Your Company Name');
define('MAILGUN_FROM_ADDRESS', 'noreply@your_domain.com');
define('MAILGUN_SECURE', true);

// reCAPTCHA v2 Invisible keys
define('RECAPTCHA_SITE_KEY', 'your_recaptcha_site_key');
define('RECAPTCHA_SECRET', 'your_recaptcha_secret');

define( 'WP_CACHE_KEY_SALT', 'your_cache_key_salt_here' );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
