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

class Cache
{
    /**
     * The single instance of the class.
     *
     * @var Cache
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
     * $_nodes contains all the cached files that are present
     * in the Cache File or Database.
     *
     * @var CacheNode[]
     */
    private $_nodes = [];

    /**
     * Is set to true when a change has been made in the cache.
     * Forcing the plugin to save the cache when needed.
     *
     * @var bool
     */
    private $_updated = false;

    public function __construct()
    {
        $cache_id = get_current_blog_id();
        if (null !== App::get_current_account()) {
            $cache_id = App::get_current_account()->get_id();
        }

        $this->_cache_name = Helpers::filter_filename($cache_id, false).'.index';
        $this->_cache_location = OUTOFTHEBOX_CACHEDIR.$this->_cache_name;

        // Load Cache
        $this->load_cache();
    }

    public function __destruct()
    {
        $this->update_cache();
    }

    /**
     * Cache Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return Cache - Cache instance
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

    public static function instance_unload()
    {
        if (is_null(self::$_instance)) {
            return;
        }

        self::instance()->update_cache();
        self::$_instance = null;
    }

    public function load_cache()
    {
        $cache = false;

        // 1: Try to load the Local cache when needed
        $cache = $this->_read_local_cache('close');

        // 2: Uncompress if needed
        if (function_exists('gzdecode') && function_exists('gzencode') && !empty($cache)) {
            $cache = @gzdecode($cache);
        }

        // 3: Unserialize the Cache, and reset if it became somehow corrupt
        if (!empty($cache) && !is_array($cache)) {
            $this->_unserialize_cache($cache);
        }
    }

    public function reset_cache()
    {
        $this->_nodes = [];
        $this->update_cache();
    }

    public function update_cache()
    {
        if ($this->is_updated()) {
            $this->_save_local_cache();
        }

        return true;
    }

    /**
     * @param string $value
     * @param string $findby
     *
     * @return CacheNode|false
     */
    public function is_cached($value, $findby = 'id')
    {
        // Find the node by ID/NAME
        $node = null;
        if ('id' === $findby) {
            $node = $this->get_node_by_id($value);
        }

        // Return if nothing can be found in the cache
        if (empty($node)) {
            return false;
        }

        return $node;
    }

    /**
     * @return CacheNode
     */
    public function add_to_cache(Entry $entry)
    {
        // Check if entry is present in cache
        $cached_node = $this->get_node_by_id($entry->get_id());

        /* If entry is not yet present in the cache,
         * create a new CacheNode
         */
        if (false === $cached_node) {
            $cached_node = $this->add_node($entry);
            $this->set_updated();
        }

        // Check if the added file has another rev
        if ($cached_node->get_rev() !== $entry->get_rev()) {
            $cached_node->set_rev($entry->get_rev());

            // Remove the thumbnails if there is a new version available
            // $cached_node->remove_thumbnails();
        }

        // Return the cached CacheNode
        return $cached_node;
    }

    public function remove_from_cache($entry_id)
    {
        $this->get_node_by_id($entry_id);
        $this->set_updated();

        return true;
    }

    public function get_node_by_id($id)
    {
        if (!isset($this->_nodes[$id])) {
            return false;
        }

        return $this->_nodes[$id];
    }

    public function has_nodes()
    {
        return count($this->_nodes) > 0;
    }

    /**
     * @return CacheNode[]
     */
    public function get_nodes()
    {
        return $this->_nodes;
    }

    public function add_node(Entry $entry)
    {
        $cached_node = new CacheNode(
            [
                '_id' => $entry->get_id(),
                '_account_id' => App::get_current_account()->get_id(),
                '_path' => $entry->get_path(),
                '_rev' => $entry->get_rev(),
            ]
        );

        return $this->set_node($cached_node);
    }

    public function set_node(CacheNode $node)
    {
        $id = $node->get_id();
        $this->_nodes[$id] = $node;

        return $this->_nodes[$id];
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

    public function get_cache_name()
    {
        return $this->_cache_name;
    }

    public function get_cache_location()
    {
        return $this->_cache_location;
    }

    protected function _read_local_cache($close = false)
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

    protected function _create_local_lock($type)
    {
        // Check if file exists
        $file = $this->get_cache_location();

        if (!file_exists($file)) {
            @file_put_contents($file, $this->_serialize_cache());

            if (!is_writable($file)) {
                exit(sprintf('Cache file (%s) is not writable', $file));
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
                exit(sprintf('Cache file (%s) is not writable', $file));
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

    protected function _save_local_cache()
    {
        if (!$this->_create_local_lock(LOCK_EX)) {
            return false;
        }

        if (empty($this->_get_cache_file_handle())) {
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

    protected function _unlock_local_cache()
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

    protected function _set_cache_file_handle($handle)
    {
        return $this->_cache_file_handle = $handle;
    }

    protected function _get_cache_file_handle()
    {
        return $this->_cache_file_handle;
    }

    private function _serialize_cache()
    {
        $data = [
            '_nodes' => $this->_nodes,
        ];

        $data_str = serialize($data);

        if (function_exists('gzencode')) {
            $data_str = gzencode($data_str);
        }

        return $data_str;
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