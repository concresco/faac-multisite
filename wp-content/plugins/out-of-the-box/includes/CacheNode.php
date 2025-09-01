<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

use TheLion\OutoftheBox\API\Dropbox\Models\FileLinkMetaData;
use TheLion\OutoftheBox\API\Dropbox\Models\FolderLinkMetaData;
use TheLion\OutoftheBox\API\Dropbox\Models\MediaInfo;
use TheLion\OutoftheBox\API\Dropbox\Models\MediaMetadata;
use TheLion\OutoftheBox\API\Dropbox\Models\VideoMetadata;

defined('ABSPATH') || exit;

#[\AllowDynamicProperties]
class CacheNode
{
    /**
     * ID of the Node = ID of the Cached Entry.
     *
     * @var string
     */
    private $_id;

    /**
     * ID of the Account.
     *
     * @var mixed
     */
    private $_account_id;

    private $_rev;
    private $_shared_links;
    private $_media_info = [];
    private $_temporarily_link;

    public function __construct($params = null)
    {
        if (!empty($params)) {
            foreach ($params as $key => $val) {
                $this->{$key} = $val;
            }
        }
    }

    public function __serialize()
    {
        return [
            '_id' => $this->_id,
            '_account_id' => $this->_account_id,
            '_rev' => $this->_rev,
            '_shared_links' => $this->_shared_links,
            '_media_info' => $this->_media_info,
            '_temporarily_link' => $this->_temporarily_link,
        ];
    }

    public function __unserialize($data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function get_id()
    {
        return $this->_id;
    }

    public function get_account_id()
    {
        return $this->_account_id;
    }

    public function get_account_uuid()
    {
        return Accounts::instance()->account_id_to_uuid($this->_account_id);
    }

    public function get_rev()
    {
        return $this->_rev;
    }

    public function set_rev($rev)
    {
        return $this->_rev = $rev;
    }

    public function add_temporarily_link($link, $expires = null)
    {
        if (empty($expires)) {
            $expires = time() + (4 * 60 * 60);
        }

        $this->_temporarily_link = [
            'url' => $link,
            'expires' => $expires,
        ];

        Cache::instance()->set_updated();
    }

    public function get_temporarily_link()
    {
        if (!isset($this->_temporarily_link['url']) || empty($this->_temporarily_link['url'])) {
            return false;
        }

        if (!(empty($this->_temporarily_link['expires'])) && $this->_temporarily_link['expires'] < time() + 60) {
            return false;
        }

        return $this->_temporarily_link['url'];
    }

    /**
     * @param FileLinkMetaData|FolderLinkMetaData $shared_link_info
     * @param mixed                               $link_settings
     */
    public function add_shared_link($shared_link_info, $link_settings)
    {
        // Update link settings if needed
        if (null !== $shared_link_info->getLinkPermissions()->getAllowDownload()) {
            $link_settings['allow_download'] = $shared_link_info->getLinkPermissions()->getAllowDownload();
        }

        if (!empty($shared_link_info->getLinkPermissions()->getResolvedVisibility())) {
            $link_settings['audience'] = $shared_link_info->getLinkPermissions()->getResolvedVisibility();
        }

        if (!empty($shared_link_info->getLinkPermissions()->getRequirePassword())) {
            $link_settings['require_password'] = $shared_link_info->getLinkPermissions()->getRequirePassword();
        }

        if (!empty($shared_link_info->getExpires())) {
            $link_settings['expires'] = $shared_link_info->getExpires();
        }

        // Store the data
        $hash = md5(serialize($link_settings));

        // Strip dl parameter from url
        $url = $shared_link_info->getUrl();
        $url = str_replace('?dl=0&', '?', $url);
        $url = str_replace('&dl=0', '', $url);
        $url = str_replace('?dl=0', '', $url);
        if (false === strpos($url, '?')) {
            $url .= '?';
        }

        // Don't store shared links with expire date. Those are unique anyway
        if (!empty($link_settings['expires'])) {
            return $url;
        }

        $this->_shared_links[$hash] = array_merge($link_settings, [
            'url' => $url,
            'expires' => $shared_link_info->getExpires(),
        ]);

        Cache::instance()->set_updated();

        return $this->get_shared_link($link_settings);
    }

    public function get_shared_link($link_settings)
    {
        $hash = md5(serialize($link_settings));

        if (!isset($this->_shared_links[$hash])) {
            return false;
        }

        if (!empty($this->_shared_links[$hash]['expires'])) {
            $now = current_datetime()->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');

            if ($this->_shared_links[$hash]['expires'] < $now) {
                return false;
            }
        }

        return $this->_shared_links[$hash]['url'];
    }

    /**
     * @param MediaInfo $media_info
     */
    public function add_media_info($media_info)
    {
        $media_data = $media_info->getMediaMetadata();
        if (!$media_data instanceof MediaMetadata) {
            return $this->_media_info;
        }

        $dimensions = $media_data->getDimensions();
        if (!empty($dimensions)) {
            $this->_media_info['width'] = $dimensions['width'];
            $this->_media_info['height'] = $dimensions['height'];
        }

        $time_taken = $media_data->getTimeTaken();
        if (!empty($time_taken)) {
            $this->_media_info['time'] = $time_taken->getTimestamp();
        }

        if ($media_data instanceof VideoMetadata) {
            $this->_media_info['duration'] = $media_data->getDuration();
        }

        return $this->_media_info;
    }

    public function get_media_info($key = null)
    {
        if (null === $key) {
            return $this->_media_info;
        }

        if (!isset($this->_media_info[$key])) {
            return null;
        }

        return $this->_media_info[$key];
    }
}