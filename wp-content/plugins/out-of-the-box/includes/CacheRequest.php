<?php

/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

defined('ABSPATH') || exit;

class CacheRequest
{
    /**
     * Set after how much time the cached request should be refreshed.
     * In seconds.
     *
     * @var int
     */
    protected $_max_cached_request_age;

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

    // Contains the cached response
    private $_requested_response;

    /**
     * Specific identifier for current user.
     * This identifier is used for caching purposes.
     *
     * @var string
     */
    private $_user_identifier;

    public function __construct($request = null)
    {
        if (empty($request)) {
            $request = $_REQUEST;
        }

        // Remove the nonce which is different everytime
        unset($request['_ajax_nonce']);

        $this->_max_cached_request_age = 30 * 60;

        // Set the max cache age to max expire age of temporarily links = 3.5 hours
        if ($this->_max_cached_request_age >= (210 * 60)) {
            $this->_max_cached_request_age = 210 * 60;
        }

        $this->_user_identifier = $this->_set_user_identifier();
        $encoded = json_encode($request);
        $request_hash = md5($encoded.Processor::instance()->get_requested_complete_path());
        $this->_cache_name = 'request_'.Helpers::filter_filename(App::get_current_account()->get_id().'_'.Processor::instance()->get_listtoken(), false).'_'.$request_hash.'_'.$this->get_user_identifier();

        $this->_cache_location = OUTOFTHEBOX_CACHEDIR.$this->get_cache_name().'.cache';

        // Load Cache
        $this->load_cache();
    }

    public function get_user_identifier()
    {
        return $this->_user_identifier;
    }

    public function get_cache_name()
    {
        return $this->_cache_name;
    }

    public function get_cache_location()
    {
        return $this->_cache_location;
    }

    public function load_cache()
    {
        $this->_requested_response = $this->_read_local_cache('close');
    }

    public function is_cached()
    {
        // Check if file exists
        $file = $this->get_cache_location();

        if (!file_exists($file)) {
            return false;
        }

        if ((filemtime($this->get_cache_location()) + $this->_max_cached_request_age) < time()) {
            return false;
        }

        if (empty($this->_requested_response)) {
            return false;
        }

        $sorting = Processor::instance()->get_shortcode_option('sort_field');

        if (!empty($sorting) && 'shuffle' === $sorting) {
            return false;
        }

        return true;
    }

    public function get_cached_response()
    {
        return $this->_requested_response;
    }

    public function add_cached_response($response)
    {
        $this->_requested_response = $response;
        $this->_clean_local_cache();
        $this->_save_local_cache();
    }

    public static function clear_local_cache_for_shortcode($account_id, $listtoken)
    {
        $file_name = Helpers::filter_filename($account_id.'_'.$listtoken, false);

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(OUTOFTHEBOX_CACHEDIR, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            if (false === strpos($path->getFilename(), $file_name)) {
                continue;
            }

            try {
                @unlink($path->getPathname());
            } catch (\Exception $ex) {
                continue;
            }
        }
    }

    public static function clear_request_cache()
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(OUTOFTHEBOX_CACHEDIR, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            if ($path->isDir()) {
                continue;
            }
            if ('.htaccess' === $path->getFilename()) {
                continue;
            }

            if (false === strpos($path->getFilename(), 'request_')) {
                continue;
            }

            if (!file_exists($path) || !is_writable($path)) {
                continue;
            }

            try {
                @unlink($path->getPathname());
            } catch (\Exception $ex) {
                continue;
            }
        }
    }

    protected function _set_cache_file_handle($handle)
    {
        return $this->_cache_file_handle = $handle;
    }

    protected function _get_cache_file_handle()
    {
        return $this->_cache_file_handle;
    }

    protected function _clean_local_cache()
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(OUTOFTHEBOX_CACHEDIR, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            if ($path->isDir()) {
                continue;
            }
            if ('.htaccess' === $path->getFilename()) {
                continue;
            }

            if (false === strpos($path->getFilename(), 'request_')) {
                continue;
            }

            // Some times files are removed before the plugin is able to check the date
            if (!file_exists($path) || !is_writable($path)) {
                continue;
            }

            try {
                if (($path->getMTime() + $this->_max_cached_request_age) <= time()) {
                    @unlink($path->getPathname());
                }
            } catch (\Exception $ex) {
                continue;
            }
        }
    }

    protected function _read_local_cache($close = false)
    {
        $handle = $this->_get_cache_file_handle();
        if (empty($handle)) {
            $this->_create_local_lock(LOCK_SH);
        }

        // Clear PHP’s stat cache so filesize() is up-to-date
        clearstatcache();

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

        if (function_exists('gzdecode') && function_exists('gzencode') && !empty($contents)) {
            $contents = @gzdecode($contents);
        }

        return $contents;
    }

    protected function _create_local_lock($type)
    {
        // Check if file exists
        $file = $this->get_cache_location();

        if (!file_exists($file)) {
            @file_put_contents($file, '');

            if (!is_writable($file)) {
                return null;
            }
        }

        // Check if the file is more than 1 minute old.
        $requires_unlock = ((filemtime($file) + 60) < time());

        // Temporarily workaround when flock is disabled. Can cause problems when plugin is used in multiple processes
        if (false !== strpos(ini_get('disable_functions'), 'flock')) {
            $requires_unlock = false;
        }

        // Check if file is already opened and locked in this process
        if (empty($this->_get_cache_file_handle())) {
            $this->_set_cache_file_handle(fopen($file, 'c+'));
        }

        Helpers::set_time_limit(60);
        if (!flock($this->_get_cache_file_handle(), $type | LOCK_NB)) {
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

    protected function _save_local_cache()
    {
        if (!$this->_create_local_lock(LOCK_EX)) {
            return false;
        }

        $data = $this->_requested_response;

        if (function_exists('gzdecode') && function_exists('gzencode') && !empty($data)) {
            $data = gzencode($data);
        }

        fwrite($this->_get_cache_file_handle(), $data);

        $this->_unlock_local_cache();

        return true;
    }

    protected function _unlock_local_cache()
    {
        if (!empty($this->_get_cache_file_handle())) {
            flock($this->_get_cache_file_handle(), LOCK_UN);
            fclose($this->_get_cache_file_handle());
            $this->_set_cache_file_handle(null);
        }

        clearstatcache();

        return true;
    }

    /**
     * Function to create an specific identifier for current user
     * This identifier can be used for caching purposes.
     */
    private function _set_user_identifier()
    {
        $shortcode = Processor::instance()->get_shortcode();

        if (empty($shortcode)) {
            return false;
        }

        return User::get_permissions_hash();
    }
}