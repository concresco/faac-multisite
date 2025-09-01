<?php

/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

use Symfony\Polyfill\Mbstring\Mbstring;

defined('ABSPATH') || exit;

class Shortcodes
{
    /**
     * The single instance of the class.
     *
     * @var Shortcodes
     */
    protected static $_instance;

    /**
     * The file name of the requested cache. This will be set in construct.
     *
     * @var string
     */
    private $_cache_name;

    /**
     * Contains the location to the cache file.
     *
     * @var string
     */
    private $_cache_location;

    /**
     * Contains the file handle in case the plugin has to work
     * with a file for unlocking/locking.
     *
     * @var type
     */
    private $_cache_file_handle;

    /**
     * $_shortcodes contains all the cached shortcodes that are present
     * in the Cache File.
     *
     * @var array
     */
    private $_shortcodes = [];

    /**
     * Is set to true when a change has been made in the cache.
     * Forcing the plugin to save the cache when needed.
     *
     * @var bool
     */
    private $_updated = false;

    public function __construct()
    {
        $this->_cache_name = get_current_blog_id();
        if (Core::is_network_authorized()) {
            $this->_cache_name = 'network';
        }
        $this->_cache_name .= '.shortcodes';

        $this->_cache_location = OUTOFTHEBOX_CACHEDIR.$this->_cache_name;

        // Load Cache
        $this->load_cache();
    }

    public function __destruct()
    {
        $this->update_cache();
    }

    /**
     * Shortcodes Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return Shortcodes - Shortcodes instance
     *
     * @static
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static function do_shortcode($atts = [])
    {
        if (is_feed()) {
            return esc_html__('Please browse to the page to see this content', 'wpcloudplugins').'.';
        }

        if (false === Core::can_run_plugin()) {
            return '&#9888; <strong>'.esc_html__('This content is not available at this moment unfortunately. Contact the administrators of this site so they can check the plugin involved.', 'wpcloudplugins').'</strong>';
        }

        if (!License::is_valid()) {
            return '&#9888; <strong>'.esc_html__('This content is not available at this moment unfortunately. Contact the administrators of this site so they can check the plugin involved.', 'wpcloudplugins').'</strong>';
        }

        if (isset($atts['module'])) {
            $module = get_post($atts['module']);

            if (empty($module) || empty($module->post_content) || !preg_match('/^\[outofthebox\s(.*?)\]$/', $module->post_content, $matches)) {
                Helpers::log_error('Module not found or Module configuration', 'Shortcode', ['module' => $atts['module']], __LINE__);

                return '&#9888; <strong>'.esc_html__('This content is not available at this moment unfortunately. Contact the administrators of this site so they can check the plugin involved.', 'wpcloudplugins').'</strong>';
            }

            if ('publish' !== $module->post_status) {
                if (Helpers::check_user_role(Settings::get('permissions_add_shortcodes'))) {
                    return '&#9888; <strong>'.sprintf(esc_html__('This module (#%s) is not active at the moment.', 'wpcloudplugins'), $module->ID).'</strong>';
                }

                return '';
            }

            // Combine the attributes of the module with the attributes of the shortcode
            $shortcode_atts = Shortcodes::parse_attributes($module->post_content, true);
            $shortcode_atts = array_merge($shortcode_atts, $atts);

            // Add module ID to shortcode
            $shortcode_atts['id'] = $module->ID;
            unset($shortcode_atts['module']);

            // Render the new shortcode
            $shortcode = Shortcodes::parse_shortcode($shortcode_atts);

            return \do_shortcode($shortcode);
        }

        return Processor::instance()->create_from_shortcode($atts);
    }

    /**
     * Reads a full shortcode and returns the attributes as an associative array.
     *
     * @param string $shortcode
     * @param mixed  $follow_module_id
     *
     * @return array
     */
    public static function parse_attributes($shortcode, $follow_module_id = false)
    {
        $attributes = [];

        $shortcode = \wp_unslash($shortcode);

        if (empty($shortcode)) {
            return $attributes;
        }

        // Use regex to extract the attributes string
        if (preg_match('/\[outofthebox\s(.*?)\]/', $shortcode, $matches)) {
            $attributes_string = $matches[1];

            // Parse the attributes string into an associative array
            $attributes = shortcode_parse_atts($attributes_string);

            if ($follow_module_id && isset($attributes['module'])) {
                $module = get_post($attributes['module']);

                if (!empty($module) && !empty($module->post_content) && preg_match('/^\[outofthebox\s(.*?)\]$/', $module->post_content, $matches)) {
                    // Combine the attributes of the module with the attributes of the shortcode
                    $shortcode_atts = Shortcodes::parse_attributes($module->post_content);
                    $attributes = array_merge($shortcode_atts, $attributes);

                    // Add module ID to shortcode
                    $attributes['id'] = $module->ID;
                    unset($attributes['module']);
                }
            }
        }

        return $attributes;
    }

    /**
     * Parses the shortcode attributes into a shortcode.
     *
     * @param array $atts
     */
    public static function parse_shortcode($atts = [])
    {
        $params = implode(' ', array_map(function ($key, $value) {
            return $key.'="'.esc_attr($value).'"';
        }, array_keys($atts), $atts));

        return '[outofthebox '.$params.']';
    }

    public function remove_shortcode($token)
    {
        if (isset($this->_shortcodes[$token])) {
            return false;
        }

        unset($this->_shortcodes[$token]);
        $this->set_updated();

        return true;
    }

    public function get_shortcode_by_id($token)
    {
        if (!isset($this->_shortcodes[$token])) {
            return false;
        }

        // Delete the removal flag when the shortcode as the shortcode is still in use
        if (isset($this->_shortcodes[$token]['remove'])) {
            unset($this->_shortcodes[$token]['remove']);
            $this->_shortcodes[$token]['expire'] = strtotime('+1 weeks');
            $this->set_updated();
        }

        return $this->_shortcodes[$token];
    }

    public function has_shortcodes()
    {
        return count($this->_shortcodes) > 0;
    }

    public function get_all_shortcodes()
    {
        return $this->_shortcodes;
    }

    public function set_shortcode($token, $shortcode)
    {
        $this->_shortcodes[$token] = $shortcode;
        $this->set_updated();

        return $this->_shortcodes[$token];
    }

    public static function encode($string)
    {
        if (!extension_loaded('mbstring') || !\function_exists('mb_convert_encoding')) {
            require_once OUTOFTHEBOX_ROOTDIR.'/vendors/ZipStream/vendor/symfony/polyfill-mbstring/Mbstring.php';

            return base64_encode(Mbstring::mb_convert_encoding($string, 'UTF-8', 'UTF-8'));
        }

        return base64_encode(mb_convert_encoding($string, 'UTF-8', 'UTF-8'));
    }

    public static function decode($base64String)
    {
        if (!extension_loaded('mbstring') || !\function_exists('mb_convert_encoding')) {
            require_once OUTOFTHEBOX_ROOTDIR.'/vendors/ZipStream/vendor/symfony/polyfill-mbstring/Mbstring.php';

            return Mbstring::mb_convert_encoding(base64_decode($base64String), 'UTF-8', 'UTF-8');
        }

        return mb_convert_encoding(base64_decode($base64String), 'UTF-8', 'UTF-8');
    }

    public function is_updated()
    {
        return $this->_updated;
    }

    public function set_updated($value = true)
    {
        $this->_updated = (bool) $value;

        return $this->_updated;
    }

    public function reset_cache()
    {
        $this->_shortcodes = [];
        $this->update_cache();
    }

    public function update_cache()
    {
        if ($this->is_updated()) {
            $this->_save_local_cache();
            $this->set_updated(false);
        }
    }

    private function get_cache_name()
    {
        return $this->_cache_name;
    }

    private function get_cache_location()
    {
        return $this->_cache_location;
    }

    private function load_cache()
    {
        $cache = $this->_read_local_cache('close');

        if (!empty($cache) && !is_array($cache)) {
            $this->_unserialize_cache($cache);
        }
    }

    private function _set_cache_file_handle($handle)
    {
        return $this->_cache_file_handle = $handle;
    }

    private function _get_cache_file_handle()
    {
        return $this->_cache_file_handle;
    }

    private function _unlock_local_cache()
    {
        $handle = $this->_get_cache_file_handle();
        if (!empty($handle)) {
            flock($this->_get_cache_file_handle(), LOCK_UN);
            fclose($this->_get_cache_file_handle());
            $this->_set_cache_file_handle(null);
        }

        clearstatcache();

        return true;
    }

    private function _read_local_cache($close = false)
    {
        $handle = $this->_get_cache_file_handle();
        if (empty($handle)) {
            $this->_create_local_lock(LOCK_SH);
        }

        // Clear PHP’s stat cache so filesize() is up-to-date
        clearstatcache();
        rewind($this->_get_cache_file_handle());

        $contents = '';

        // Read until end-of-file in 8 192-byte chunks
        while (filesize($this->get_cache_location()) > 0 && !feof($this->_get_cache_file_handle())) {
            $chunk = fread($this->_get_cache_file_handle(), 8192);
            if (false === $chunk) {
                // sth went wrong—break out and return what we have
                break;
            }
            $contents .= $chunk;
        }

        if (false !== $close) {
            $this->_unlock_local_cache();
        }

        return $contents;
    }

    private function _create_local_lock($type)
    {
        // Check if file exists
        $file = $this->get_cache_location();

        if (!file_exists($file)) {
            @file_put_contents($file, $this->_serialize_cache());

            if (!is_writable($file)) {
                Helpers::log_error('Shortcode file is not writable.', 'Shortcode', ['file' => $file], __LINE__);

                exit(sprintf('Shortcode file (%s) is not writable', $file));
            }
        }

        // Check if the file is more than 1 minute old.
        $requires_unlock = ((filemtime($file) + 60) < time());

        // Temporarily workaround when flock is disabled. Can cause problems when plugin is used in multiple processes
        if (false !== strpos(ini_get('disable_functions'), 'flock')) {
            $requires_unlock = false;
        }

        // Check if file is already opened and locked in this process
        $handle = $this->_get_cache_file_handle();
        if (empty($handle)) {
            $handle = fopen($file, 'c+');
            if (!is_resource($handle)) {
                Helpers::log_error('Shortcode file is not writable.', 'Shortcode', ['file' => $file], __LINE__);

                exit(sprintf('Shortcode file (%s) is not writable', $file));
            }
            $this->_set_cache_file_handle($handle);
        }

        Helpers::set_time_limit(60);

        if (!flock($this->_get_cache_file_handle(), $type)) {
            /*
             * If the file cannot be unlocked and the last time
             * it was modified was 1 minute, assume that
             * the previous process died and unlock the file manually
             */
            if ($requires_unlock) {
                $this->_unlock_local_cache();
                $handle = fopen($file, 'c+');
                $this->_set_cache_file_handle($handle);
            }
            // Try to lock the file again
            flock($this->_get_cache_file_handle(), LOCK_EX);
        }
        Helpers::set_time_limit(60);

        return true;
    }

    private function _save_local_cache()
    {
        if (!$this->_create_local_lock(LOCK_EX)) {
            return false;
        }

        $data = $this->_serialize_cache($this);

        ftruncate($this->_get_cache_file_handle(), 0);
        rewind($this->_get_cache_file_handle());

        fwrite($this->_get_cache_file_handle(), $data);

        $this->_unlock_local_cache();
        $this->set_updated(false);

        return true;
    }

    private function _serialize_cache()
    {
        $now = time();
        foreach ($this->_shortcodes as $token => $shortcode) {
            if (!isset($shortcode['expire']) || $shortcode['expire'] < $now) {
                // Only delete the shortcode once it is marked to prevent issues with multiple shortcodes on the same page
                if (isset($shortcode['remove']) && true === $shortcode['remove']) {
                    unset($this->_shortcodes[$token]);
                } else {
                    $this->_shortcodes[$token]['remove'] = true;
                    $this->_shortcodes[$token]['expire'] = strtotime('+1 weeks');
                }
            }
        }

        // Only keep the latest 1000 shortcodes used for performance reasons
        if (count($this->_shortcodes) > 1000) {
            uasort($this->_shortcodes, fn ($a, $b) => $a['expire'] <=> $b['expire']);

            array_splice($this->_shortcodes, 0, 100);
        }

        $data = [
            '_shortcodes' => $this->_shortcodes,
        ];

        return serialize($data);
    }

    private function _unserialize_cache($data)
    {
        $values = unserialize($data);
        if (false !== $values) {
            foreach ($values as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }
}