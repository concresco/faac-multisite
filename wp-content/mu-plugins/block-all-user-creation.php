<?php
/*
Plugin Name: Block All User Creation (Network)
Description: Blocca qualsiasi creazione di utenti (via UI, REST, XML-RPC, plugin) eccetto quando eseguita manualmente da un Super Admin nell'area admin.
Version: 1.0.0
Author: FAAC SecOps
*/

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ritorna true solo se l'operazione è eseguita manualmente da Super Admin nell'area admin.
 */
function faacsec_is_creation_allowed_for_super_admin_only(): bool
{
    if (!is_user_logged_in()) {
        return false;
    }

    // Permetti esclusivamente ai Super Admin, e solo dall'admin area (no REST/frontend/XML-RPC).
    if (is_super_admin() && is_admin()) {
        return true;
    }

    return false;
}

/**
 * Blocca wp_insert_user / wp_create_user a monte per qualsiasi sorgente.
 *
 * @param array|WP_Error $userdata
 * @param bool           $update
 * @param int|null       $user_id
 * @return array|WP_Error
 */
function faacsec_block_wp_pre_insert_user_data($userdata, $update, $user_id)
{
    // Consenti solo update profilo esistenti; blocca nuove creazioni.
    $is_creation = empty($update) || empty($user_id);
    if ($is_creation && !faacsec_is_creation_allowed_for_super_admin_only()) {
        return new WP_Error('registration_disabled', __('User registration is disabled on this network.'));
    }
    return $userdata;
}
add_filter('wp_pre_insert_user_data', 'faacsec_block_wp_pre_insert_user_data', 10, 3);

/**
 * Blocca la REST API per creazione utenti: /wp/v2/users (POST) e rotta oEmbed correlate.
 */
function faacsec_block_rest_user_creation($response, $handler, $request)
{
    if ($request instanceof WP_REST_Request) {
        $route = $request->get_route();
        $method = $request->get_method();

        // Blocca TUTTE le richieste agli endpoint utenti per gli anonimi e per ruoli non amministrativi.
        if (preg_match('#^/wp/v2/users(?:/|$)#', $route)) {
            if (!is_user_logged_in()) {
                return new WP_Error('rest_forbidden', __('REST users endpoint is not available to anonymous.'), ['status' => 401]);
            }
            // Consenti solo a chi può gestire/elencare utenti (amministratori/super admin)
            if (!is_super_admin() && !current_user_can('list_users')) {
                return new WP_Error('rest_forbidden', __('You are not allowed to access users via REST API.'), ['status' => 403]);
            }
        }

        // Blocca le creazioni anche per utenti loggati non super admin fuori da admin.
        if ($method === 'POST' && preg_match('#^/wp/v2/users/?$#', $route)) {
            if (!faacsec_is_creation_allowed_for_super_admin_only()) {
                return new WP_Error('registration_disabled', __('User registration via REST API is disabled.'), ['status' => 403]);
            }
        }
    }
    return $response;
}
add_filter('rest_request_before_callbacks', 'faacsec_block_rest_user_creation', 10, 3);

/**
 * Blocca il flusso multisite di signup (wpmu) a livello di validazione.
 */
function faacsec_wpmu_block_user_signup($result)
{
    if (!faacsec_is_creation_allowed_for_super_admin_only()) {
        if (!isset($result['errors']) || !($result['errors'] instanceof WP_Error)) {
            $result['errors'] = new WP_Error();
        }
        $result['errors']->add('registration_disabled', __('User registration is disabled on this network.'));
    }
    return $result;
}
add_filter('wpmu_validate_user_signup', 'faacsec_wpmu_block_user_signup');

/**
 * Safety net: intercetta tentativi tardivi.
 */
function faacsec_abort_user_register($user_id)
{
    if (!faacsec_is_creation_allowed_for_super_admin_only()) {
        // Elimina immediatamente l'utente appena creato e genera errore.
        require_once ABSPATH . 'wp-admin/includes/user.php';
        if ($user_id) {
            wp_delete_user($user_id);
        }
        wp_die(__('User registration is disabled on this network.'), 403);
    }
}
add_action('user_register', 'faacsec_abort_user_register', 1);


