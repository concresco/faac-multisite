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

class Accounts
{
    /**
     * The single instance of the class.
     *
     * @var Accounts
     */
    protected static $_instance;

    /**
     * $_accounts contains all the accounts that are linked with the plugin.
     *
     * @var \TheLion\OutoftheBox\Account[]
     */
    private $_accounts = [];

    public function __construct()
    {
        $this->_init_accounts();
    }

    /**
     * Accounts Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return Accounts - Accounts instance
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

    /**
     * @return bool
     */
    public function has_accounts()
    {
        return count($this->_accounts) > 0;
    }

    /**
     * @return \TheLion\OutoftheBox\Account[]
     */
    public function list_accounts()
    {
        return $this->_accounts;
    }

    /**
     * @return null|Account
     */
    public function get_primary_account()
    {
        if (0 === count($this->_accounts)) {
            return null;
        }

        $first_account = reset($this->_accounts);

        if (false === $first_account->get_authorization()->has_access_token()) {
            return null;
        }

        return $first_account;
    }

    /**
     * @param string $id
     *
     * @return null|Account
     */
    public function get_account_by_id($id)
    {
        if (empty($id)) {
            return null;
        }

        // Redirect to uuid function in case an uuid is provided
        if (false !== strpos($id, 'wpcp-')) {
            return $this->get_account_by_uuid($id);
        }

        if (false === isset($this->_accounts[(string) $id])) {
            return null;
        }

        return $this->_accounts[(string) $id];
    }

    /**
     * @param string $uuid
     *
     * @return null|Account
     */
    public function get_account_by_uuid($uuid)
    {
        foreach ($this->_accounts as $account) {
            if ($account->get_uuid() === $uuid) {
                return $account;
            }
        }

        return null;
    }

    /**
     * Convert ID to UUID.
     *
     * @param string $id
     */
    public function account_id_to_uuid($id)
    {
        $account = $this->get_account_by_id($id);

        if (!empty($account)) {
            return $account->get_uuid();
        }

        return null;
    }

    /**
     * Convert UUID to ID.
     *
     * @param string $uuid
     */
    public function account_uuid_to_id($uuid)
    {
        $account = $this->get_account_by_uuid($uuid);

        if (!empty($account)) {
            return $account->get_id();
        }

        return null;
    }

    /**
     * @param mixed $email
     *
     * @return null|Account
     */
    public function get_account_by_email($email)
    {
        foreach ($this->_accounts as $account) {
            if ($account->get_email() === $email) {
                return $account;
            }
        }

        return null;
    }

    /**
     * @return $this
     */
    public function add_account(Account $account)
    {
        $this->_accounts[$account->get_id()] = $account;

        $this->save();

        return $this;
    }

    /**
     * @param string $account_id
     *
     * @return $this
     */
    public function remove_account($account_id)
    {
        $account = $this->get_account_by_id($account_id);

        if (null === $account) {
            return $this;
        }

        $account->get_authorization()->remove_token();

        unset($this->_accounts[$account->get_id()]);

        $this->save();

        return $this;
    }

    public function save()
    {
        if (Core::is_network_authorized()) {
            Settings::save_for_network('accounts', $this->_accounts);
        } else {
            Settings::save('accounts', $this->_accounts);
        }
    }

    public static function revoke_authorizations()
    {
        require_once ABSPATH.'wp-admin/includes/class-wp-filesystem-base.php';

        require_once ABSPATH.'wp-admin/includes/class-wp-filesystem-direct.php';

        $wp_file_system = new \WP_Filesystem_Direct(false);

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(OUTOFTHEBOX_CACHEDIR, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            if ('.htaccess' === $path->getFilename()) {
                continue;
            }

            try {
                $wp_file_system->delete($path->getPathname(), true);
            } catch (\Exception $ex) {
                continue;
            }
        }

        Processor::reset_complete_cache();
    }

    private function _init_accounts()
    {
        $this->_accounts = Settings::get('accounts', []);
    }
}
