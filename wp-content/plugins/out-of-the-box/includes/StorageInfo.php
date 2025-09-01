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

class StorageInfo
{
    /**
     * Quota used for Cloud Account.
     *
     * @var int
     */
    private $_quota_used;

    /**
     * Quota available for Cloud Account.
     *
     * @var int
     */
    private $_quota_total;

    public function get_quota_used()
    {
        return Helpers::bytes_to_size_1024($this->_quota_used, 1);
    }

    public function get_quota_total()
    {
        if (empty($this->_quota_total)) {
            return esc_html__('Unlimited', 'wpcloudplugins');
        }

        return Helpers::bytes_to_size_1024($this->_quota_total, 1);
    }

    public function get_quota_used_percentage_used()
    {
        if (empty($this->_quota_total)) {
            return 0;
        }

        if (empty($this->_quota_used)) {
            return 0;
        }

        return round(100 / ($this->_quota_total / $this->_quota_used));
    }

    public function set_quota_used($_quota_used)
    {
        $this->_quota_used = $_quota_used;
    }

    public function set_quota_total($_quota_total)
    {
        $this->_quota_total = $_quota_total;
    }
}
