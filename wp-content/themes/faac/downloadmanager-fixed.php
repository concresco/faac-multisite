<?php
/*
Template name: Download Manager
*
*
* @package faac
*/

// hide notices
@ini_set('error_reporting', E_ALL & ~ E_NOTICE);

//- turn off compression on the server
// FIX per LiteSpeed: usa putenv invece di apache_setenv
if (function_exists('apache_setenv')) {
    @apache_setenv('no-gzip', 1);
} else {
    // Alternativa per LiteSpeed/nginx
    @putenv('no-gzip=1');
}
@ini_set('zlib.output_compression', 'Off');

// Il resto del codice rimane identico... 