<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

use TheLion\OutoftheBox\API\Dropbox\Models\AccessToken;

defined('ABSPATH') || exit;

class Authorization
{
    /**
     * Contains the location to the token file.
     *
     * @var string
     */
    private $_token_name;

    /**
     * Contains the file handle for the token file.
     *
     * @var type
     */
    private $_token_file_handle;

    /**
     * The account id linked to this authorization.
     *
     * @var string
     */
    private $_account_id;

    /**
     * Is the current authorization still valid or can it no longer be used.
     *
     * @var bool
     */
    private $_is_valid;

    public function __construct(Account $_account)
    {
        // Required for loading the Access Token class
        require_once OUTOFTHEBOX_ROOTDIR.'/vendors/dropbox-sdk/vendor/autoload.php';

        $this->_account_id = $_account->get_id();
        $this->_token_name = Helpers::filter_filename($_account->get_email().'_'.str_replace(':', '', $_account->get_id()), false).'.access_token';
    }

    public function set_token_name($token_name)
    {
        return $this->_token_name = $token_name;
    }

    public function get_token_location()
    {
        return OUTOFTHEBOX_CACHEDIR.$this->_token_name;
    }

    public function get_access_token()
    {
        $this->get_lock();
        clearstatcache();
        rewind($this->get_token_file_handle());

        $filesize = filesize($this->get_token_location());
        if ($filesize > 0) {
            $token = fread($this->get_token_file_handle(), filesize($this->get_token_location()));
        } else {
            $token = '';
        }

        $this->unlock_token_file();
        if (empty($token)) {
            return null;
        }

        // Update function to encrypt tokens
        $updated_token = $this->update_from_single_token($token);

        // Update token to new Class
        return $this->update_from_old_class($updated_token);
    }

    public function set_access_token($_access_token)
    {
        // Remove Lost Authorisation message
        if (false !== ($timestamp = wp_next_scheduled('outofthebox_lost_authorisation_notification', ['account_id' => $this->_account_id]))) {
            wp_unschedule_event($timestamp, 'outofthebox_lost_authorisation_notification', ['account_id' => $this->_account_id]);
        }

        $this->get_lock(LOCK_EX);
        ftruncate($this->get_token_file_handle(), 0);
        rewind($this->get_token_file_handle());

        $access_token = $this->encrypt(serialize($_access_token));
        fwrite($this->get_token_file_handle(), $access_token);
        $this->unlock_token_file();
    }

    public function is_valid()
    {
        if (empty($this->_is_valid)) {
            $this->has_access_token();
        }

        return $this->_is_valid;
    }

    public function set_is_valid($valid = true)
    {
        $this->_is_valid = $valid;
    }

    public function has_access_token()
    {
        if (null !== $this->_is_valid) {
            return $this->_is_valid;
        }

        $access_token = $this->get_access_token();

        $this->set_is_valid(!empty($access_token));

        return $this->_is_valid;
    }

    public function get_lock($type = LOCK_SH)
    {
        if (!flock($this->get_token_file_handle(), $type)) {
            /*
             * If the file cannot be unlocked and the last time
             * it was modified was 1 minute, assume that
             * the previous process died and unlock the file manually
             */
            $requires_unlock = ((filemtime($this->get_token_location()) + 60) < time());

            // Temporarily workaround when flock is disabled. Can cause problems when plugin is used in multiple processes
            if (false !== strpos(ini_get('disable_functions'), 'flock')) {
                $requires_unlock = false;
            }

            if ($requires_unlock) {
                $this->unlock_token_file();
            }

            // Try to lock the file again
            flock($this->get_token_file_handle(), $type);
        }

        return $this->get_token_file_handle();
    }

    public function unlock_token_file()
    {
        $handle = $this->get_token_file_handle();
        if (!empty($handle)) {
            flock($this->get_token_file_handle(), LOCK_UN);
            fclose($this->get_token_file_handle());
            $this->set_token_file_handle(null);
        }

        clearstatcache();

        return true;
    }

    public function set_token_file_handle($handle)
    {
        return $this->_token_file_handle = $handle;
    }

    public function get_token_file_handle()
    {
        if (empty($this->_token_file_handle)) {
            // Check Cache Folder
            if (!file_exists($this->get_token_location())) {
                file_put_contents($this->get_token_location(), '');
            }

            // Check if token file is writeable
            if (!is_writable($this->get_token_location())) {
                @chmod($this->get_token_location(), 0700);

                if (!is_writable($this->get_token_location())) {
                    Helpers::log_error('Token file (%s) is not writable.', 'Authorization', ['file' => \basename($this->get_token_location())], __LINE__);

                    exit(sprintf('Cache file (%s) is not writable', $this->get_token_location()));
                }
            }

            $this->_token_file_handle = fopen($this->get_token_location(), 'c+');
            if (!is_resource($this->_token_file_handle)) {
                Helpers::log_error('Token file (%s) is not writable.', 'Authorization', ['file' => \basename($this->get_token_location())], __LINE__);

                exit(sprintf('Cache file (%s) is not writable', $this->get_token_location()));
            }
        }

        return $this->_token_file_handle;
    }

    public function set_account_id($account_id)
    {
        $this->_account_id = $account_id;
    }

    public function get_account_id()
    {
        return $this->_account_id;
    }

    public function remove_token()
    {
        @unlink($this->get_token_location());
    }

    public function update_from_single_token($token)
    {
        $decrypted_token = $this->decrypt($token);
        $token_object = unserialize($decrypted_token);

        // Return if token is already encrypted and converted
        if (false !== $decrypted_token) {
            return $token_object;
        }

        // If token is not yet encrypted
        if (false !== $token_object) {
            return $this->set_access_token($token_object);
        }

        // Else convert & encrypt outdated token
        $data = [
            'access_token' => $token,
        ];

        $token_object = new AccessToken($data);

        // Store the new token format
        $this->set_access_token($token_object);

        return $token_object;
    }

    public function update_from_old_class($token_object)
    {
        if (false === is_a($token_object, '\TheLion\OutoftheBox\API\Dropbox\Models\AccessToken')) {
            $received_data = (array) $this->fix_object($token_object);

            $received_data['access_token'] = $received_data['token'] ?? '';
            $received_data['refresh_token'] = $received_data['refreshToken'] ?? '';
            $received_data['expires_in'] = $received_data['expiresIn'] ?? '';

            $token_object = new AccessToken($received_data);

            // Store the new token format
            $this->set_access_token($token_object);
        }

        return $token_object;
    }

    /**
     * Takes an __PHP_Incomplete_Class and casts it to a stdClass object.
     * All properties will be made public in this step.
     *
     * @param object $object __PHP_Incomplete_Class
     *
     * @return object
     */
    public function fix_object($object)
    {
        // 1. Serialize the object to a string.
        $dump = serialize($object);

        // 2. Change class-type to 'stdClass'.
        $dump = preg_replace('/^O:\d+:"[^"]++"/', 'O:8:"stdClass"', $dump);

        // 3. Make private and protected properties public.
        $dump = preg_replace_callback('/:\d+:"\0.*?\0([^"]+)"/', [$this, 'calc_key_length'], $dump);

        // 4. Unserialize the modified object again.
        try {
            return unserialize($dump);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function calc_key_length($matches)
    {
        return ':'.strlen($matches[1]).':"'.$matches[1].'"';
    }

    public function encrypt($data, $method = 'aes-256-gcm')
    {
        // Fallback for servers without openssl support
        if (false === function_exists('openssl_encrypt') || false === in_array($method, openssl_get_cipher_methods())) {
            return base64_encode($data);
        }

        $iv_length = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($iv_length);
        $tag = null;

        $first_encrypted = openssl_encrypt($data, $method, OUTOFTHEBOX_AUTH_KEY, OPENSSL_RAW_DATA, $iv, $tag);

        return base64_encode($tag.'|##|'.$iv.'|##|'.$first_encrypted);
    }

    public function decrypt($input, $method = 'aes-256-gcm')
    {
        $mix = base64_decode($input);

        // Fallback for servers without openssl support
        if (false === function_exists('openssl_encrypt') || false === in_array($method, openssl_get_cipher_methods())) {
            return $mix;
        }

        // Decrypt old data with deprecated method
        if (false === strpos($mix, '|##|')) {
            $deprecated_method = 'aes-256-cbc';
            $iv_length = openssl_cipher_iv_length($deprecated_method);

            $iv = substr($mix, 0, $iv_length);
            $first_encrypted = substr($mix, $iv_length);

            // This line uses an insecure encryption algorithm for decrypting and migrating old data.
            // @ignore This is intentional and will be removed after migration.
            $deprecated_data = openssl_decrypt($first_encrypted, $deprecated_method, OUTOFTHEBOX_AUTH_KEY, OPENSSL_RAW_DATA, $iv);

            // Migrate old data to new encryption method
            $this->set_access_token($deprecated_data);

            return $deprecated_data;
        }

        // Decrypt using AES-256-GCM
        list($tag, $iv, $message) = explode('|##|', $mix);

        // New secure encryption method
        return openssl_decrypt($message, $method, OUTOFTHEBOX_AUTH_KEY, OPENSSL_RAW_DATA, $iv, $tag);
    }
}
