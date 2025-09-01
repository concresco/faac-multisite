<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.12.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

// Exit if accessed directly.
defined('ABSPATH') || exit;

class Restrictions
{
    /**
     * The single instance of the class.
     *
     * @var Restrictions
     */
    protected static $_instance;

    public function __construct()
    {
        $this->set_hooks();
    }

    /**
     * Restrictions Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return Restrictions - Restrictions instance
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

    public function set_hooks()
    {
        // Add restriction fields to Profile page
        add_action('show_user_profile', [$this, 'profile_add_restrictions'], 10, 1);
        add_action('edit_user_profile', [$this, 'profile_add_restrictions'], 10, 1);

        // Save values on Profile page
        add_action('personal_options_update', [$this, 'profile_save_restrictions'], 10, 1);
        add_action('edit_user_profile_update', [$this, 'profile_save_restrictions'], 10, 1);
    }

    /**
     * Check if the requested file still should be accessible for the user.
     * Update the file counter if needed.
     *
     * @param string $entry_id
     * @param string $limit_name
     * @param bool   $check_only
     */
    public static function has_reached_download_limit($entry_id = null, $check_only = true, $limit_name = 'download')
    {
        if ('1' !== Processor::instance()->get_shortcode_option('download_limits') && 'Yes' !== Settings::get('download_limits')) {
            return false;
        }

        // Limits identifier
        $limits_for = '1' === Processor::instance()->get_shortcode_option('download_limits') ? Processor::instance()->get_listtoken() : 'global';

        // Returns if user is excluded from limits
        $module_excluded_roles = Processor::instance()->get_shortcode_option('download_limits_excluded_roles');
        $excluded_roles = empty($module_excluded_roles) || empty($module_excluded_roles[0]) ? Settings::get('download_limits_excluded_roles') : $module_excluded_roles;
        if (empty($excluded_roles)) {
            $excluded_roles = ['administrator'];
        }

        if (Helpers::check_user_role($excluded_roles)) {
            return false;
        }

        // Default values for usage limits
        $default_current_usage = [
            'downloads_per_user' => 0,
            'downloads_per_user_per_file' => [],
            'zip_downloads_per_user' => 0,
            'bandwidth_per_user' => -1,
        ];

        // Load the current limits for the user.
        // Also load the specified limit for user, or use the global value if that doesn't exist.
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $current_usage = get_user_meta($user_id, CORE::$slug.'-usage-limits', true);
            $user_limits = self::_get_limits_for_user($user_id);
        } elseif (isset($_COOKIE['WPCP_UUID'])) {
            $user_id = null;
            $current_usage = \get_transient(CORE::$slug.'-usage-limits-'.\sanitize_text_field($_COOKIE['WPCP_UUID']));
            $user_limits = self::_get_limits_for_user(null);
        } else {
            // By default, untraceable users are blocked from downloading.
            if ('Yes' === Settings::get('download_limits_block_untraceable_users')) {
                return \esc_html__('Log in to this site or enable cookies in your browser to download content. Then reload this page.', 'wpcloudplugins');
            }

            $user_id = null;
            $current_usage = null;
            $user_limits = self::_get_limits_for_user(null);
        }

        $day_key = date('Ymd');
        $start_period_key = date('Ymd', strtotime('-'.$user_limits['usage_period']));
        $is_current_usage_updated = false;

        // Remove usage older than one month
        if (is_array($current_usage)) {
            foreach ($current_usage as $date => $value) {
                if ((string) $date < $start_period_key) {
                    $is_current_usage_updated = true;
                    unset($current_usage[$date]);
                }
            }
        } else {
            $current_usage = [];
        }

        if (empty($current_usage) || !isset($current_usage[$day_key]) || !isset($current_usage[$day_key][$limits_for])) {
            $current_usage[$day_key] = [
                $limits_for => $default_current_usage,
            ];
            $is_current_usage_updated = true;
        }

        // 0: Make totals of all limits in the set usage period
        $totals = [
            'downloads_per_user' => 0,
            'downloads_per_user_per_file' => [],
            'zip_downloads_per_user' => 0,
            'bandwidth_per_user' => 0,
        ];

        foreach ($current_usage as $date => $usage) {
            if (!isset($usage[$limits_for])) {
                continue;
            }

            if ((string) $date > $start_period_key) {
                $totals['downloads_per_user'] += $usage[$limits_for]['downloads_per_user'];
                $totals['zip_downloads_per_user'] += $usage[$limits_for]['zip_downloads_per_user'];
                $totals['bandwidth_per_user'] += $usage[$limits_for]['bandwidth_per_user'];

                foreach ($usage[$limits_for]['downloads_per_user_per_file'] as $file_id => $count) {
                    if (!isset($totals['downloads_per_user_per_file'][$file_id])) {
                        $totals['downloads_per_user_per_file'][$file_id] = 0;
                    }
                    $totals['downloads_per_user_per_file'][$file_id] += $count;
                }
            }
        }

        // 1: Is the number of downloads per day reached?
        if (
            is_int($user_limits['downloads_per_user'])
            && ($totals['downloads_per_user'] >= $user_limits['downloads_per_user'])
        ) {
            $error_msg = \esc_html__('You have reached the number of downloads allowed for today. You can download more content tomorrow.', 'wpcloudplugins');
            if (!$check_only) {
                self::_do_limit_reached_events($entry_id, $user_id, $error_msg);
            }

            return $error_msg;
        }

        // 2: Is the number of downloads per day for this file reached?
        if (
            !empty($entry_id)
            && is_int($user_limits['downloads_per_user_per_file'])
        ) {
            if (
                isset($totals['downloads_per_user_per_file'][$entry_id])
                && $totals['downloads_per_user_per_file'][$entry_id] >= $user_limits['downloads_per_user_per_file']
            ) {
                $error_msg = \esc_html__('You have reached the number of downloads allowed for this file today. You will be able to download more content tomorrow.', 'wpcloudplugins');
                if (!$check_only) {
                    self::_do_limit_reached_events($entry_id, $user_id, $error_msg);
                }

                return $error_msg;
            }
        }

        // 3: Is the bandwidth limit reached?
        if (
            (!empty($user_limits['bandwidth_per_user']))
            && $user_limits['bandwidth_per_user'] > 0
            && ($totals['bandwidth_per_user'] >= Helpers::return_bytes($user_limits['bandwidth_per_user'].'GB'))
        ) {
            $error_msg = \esc_html__('You have reached your bandwidth limit for today. You will be able to download more content tomorrow.', 'wpcloudplugins');
            if (!$check_only) {
                self::_do_limit_reached_events($entry_id, $user_id, $error_msg);
            }

            return $error_msg;
        }

        // 4: Is the number of zip files reached
        if (
            ('download_zip' === $limit_name)
            && is_int($user_limits['zip_downloads_per_user'])
            && ($totals['zip_downloads_per_user'] >= $user_limits['zip_downloads_per_user'])
        ) {
            $error_msg = \esc_html__('You have reached the number of ZIP downloads allowed for today. You will be able to download new ZIP files tomorrow.', 'wpcloudplugins');
            if (!$check_only) {
                self::_do_limit_reached_events($entry_id, $user_id, $error_msg);
            }

            return $error_msg;
        }

        // Limit is not reached, so update current usage
        if (false === $check_only) {
            if ('download_zip' === $limit_name) {
                ++$current_usage[$day_key][$limits_for]['zip_downloads_per_user'];
            } else {
                ++$current_usage[$day_key][$limits_for]['downloads_per_user'];
                if (!empty($entry_id)) {
                    if (!isset($current_usage[$day_key][$limits_for]['downloads_per_user_per_file'][$entry_id])) {
                        $current_usage[$day_key][$limits_for]['downloads_per_user_per_file'][$entry_id] = 0;
                    }
                    ++$current_usage[$day_key][$limits_for]['downloads_per_user_per_file'][$entry_id];

                    if ($user_limits['bandwidth_per_user'] > -1) {
                        $entry = API::get_entry($entry_id);
                        $current_usage[$day_key][$limits_for]['bandwidth_per_user'] += $entry->get_size();
                    }
                }
            }

            $is_current_usage_updated = true;
        }

        if ($is_current_usage_updated) {
            // Store the new limits for the current user
            if (is_user_logged_in()) {
                update_user_meta($user_id, CORE::$slug.'-usage-limits', $current_usage);
            } elseif (isset($_COOKIE['WPCP_UUID'])) {
                set_transient(CORE::$slug.'-usage-limits-'.\sanitize_text_field($_COOKIE['WPCP_UUID']), $current_usage, \DAY_IN_SECONDS);
            }
        }

        return false;
    }

    public function profile_add_restrictions($user)
    {
        if (false === $this->are_individual_usage_limits_supported()) {
            return;
        }

        if (false === current_user_can('promote_users')) {
            return;
        }

        $user_limits = $this->_get_limits_for_user($user->ID, false);

        $global_usage_period = Settings::get('usage_period');
        $global_downloads = Settings::get('downloads_per_user');
        $global_downloads_per_file = Settings::get('downloads_per_user_per_file');
        $global_zip_downloads = Settings::get('zip_downloads_per_user');
        $global_bandwidth = Settings::get('bandwidth_per_user');

        ?>
<h3>WP Cloud Plugins (Dropbox) | Usage Restrictions</h3>
<p><?php
        esc_html_e('You can set custom usage restrictions that override the module/global usage restrictions set. If no values are set, the module or global restrictions are used.', 'wpcloudplugins');
        ?>
</p>

<table class="form-table">
    <tr>
        <th><label for="<?php echo CORE::$slug; ?>-usage-period"><?php esc_html_e('Restriction Period', 'wpcloudplugins'); ?></label></th>
        <td>
            <select name="<?php echo CORE::$slug; ?>-usage-period">
                <option value="default" <?php echo ($user_limits['usage_period'] === $global_usage_period) ? 'selected="selected"' : ''; ?>><?php echo esc_html__('Default', 'wpcloudplugins').' (='.$global_usage_period.')'; ?></option>
                <option value="1 day" <?php echo ('1 day' === $user_limits['usage_period'] && $user_limits['usage_period'] !== $global_usage_period) ? 'selected="selected"' : ''; ?>><?php echo esc_html__('Per Day', 'wpcloudplugins'); ?></option>
                <option value="1 week" <?php echo ('1 week' === $user_limits['usage_period'] && $user_limits['usage_period'] !== $global_usage_period) ? 'selected="selected"' : ''; ?>><?php echo esc_html__('Per Week', 'wpcloudplugins'); ?></option>
                <option value="1 month" <?php echo ('1 month' === $user_limits['usage_period'] && $user_limits['usage_period'] !== $global_usage_period) ? 'selected="selected"' : ''; ?>><?php echo esc_html__('Per Month', 'wpcloudplugins'); ?></option>
            </select>
            <p class="description"><?php echo esc_html__('Defines the time frame for applying usage limits. Choose from predefined periods.', 'wpcloudplugins'); ?></p>
        </td>
    </tr>
    <tr>
        <th><label for="<?php echo CORE::$slug; ?>-download-per-day"><?php esc_html_e('Downloads', 'wpcloudplugins'); ?></label></th>
        <td>
            <input type="number" step="1" min="1" name="<?php echo CORE::$slug; ?>-download-per-day" value="<?php echo esc_attr($user_limits['downloads_per_user']); ?>" placeholder="<?php echo !empty($global_downloads) && $global_downloads >= 1 ? $global_downloads : esc_attr__('Unlimited', 'wpcloudplugins'); ?>" class="regular-text" />
            <p class="description"><?php echo esc_html__('The number of files that a user is allowed to download during the selected period.', 'wpcloudplugins'); ?></p>
        </td>
    </tr>

    <tr>
        <th><label for="<?php echo CORE::$slug; ?>-download-per-file"><?php esc_html_e('Downloads per file', 'wpcloudplugins'); ?></label></th>
        <td>
            <input type="number" step="1" min="1" name="<?php echo CORE::$slug; ?>-download-per-file" value="<?php echo esc_attr($user_limits['downloads_per_user_per_file']); ?>" placeholder="<?php echo !empty($global_downloads_per_file) && $global_downloads_per_file >= 1 ? $global_downloads_per_file : esc_attr__('Unlimited', 'wpcloudplugins'); ?>" class="regular-text" />
            <p class="description"><?php echo esc_html__('The number of times the same file can be downloaded by a user during the selected period.', 'wpcloudplugins'); ?></p>
        </td>
    </tr>

    <tr>
        <th><label for="<?php echo CORE::$slug; ?>-zip-download-per-day"><?php esc_html_e('ZIP downloads', 'wpcloudplugins'); ?></label></th>
        <td>
            <input type="number" step="1" min="1" name="<?php echo CORE::$slug; ?>-zip-download-per-day" value="<?php echo esc_attr($user_limits['zip_downloads_per_user']); ?>" placeholder="<?php echo !empty($global_zip_downloads) && $global_zip_downloads >= 1 ? $global_zip_downloads : esc_attr__('Unlimited', 'wpcloudplugins'); ?>" class="regular-text" />
            <p class="description"><?php echo esc_html__('Number of ZIP files that can be downloaded per user, during the selected period.', 'wpcloudplugins'); ?></p>
        </td>
    </tr>

    <tr>
        <th><label for="<?php echo CORE::$slug; ?>-bandwidth-per-day"><?php esc_html_e('Bandwidth usage (in GB)', 'wpcloudplugins'); ?></label></th>
        <td>
            <input type="number" step="0.1" min="0.1" name="<?php echo CORE::$slug; ?>-bandwidth-per-day" value="<?php echo esc_attr($user_limits['bandwidth_per_user']); ?>" placeholder="<?php echo !empty($global_bandwidth) && $global_bandwidth >= .1 ? $global_bandwidth : esc_attr__('Unlimited', 'wpcloudplugins'); ?>" class="regular-text" />
            <p class="description"><?php echo esc_html__('The total amount of bandwidth that a user is allowed to use during the selected period..', 'wpcloudplugins'); ?></p>
        </td>
    </tr>
</table>
<?php
    }

    public function profile_save_restrictions($user_id)
    {
        if (false === current_user_can('promote_users')) {
            return;
        }

        $fields = [
            CORE::$slug.'-usage-period',
            CORE::$slug.'-download-per-day',
            CORE::$slug.'-download-per-file',
            CORE::$slug.'-zip-download-per-day',
            CORE::$slug.'-bandwidth-per-day',
        ];

        foreach ($fields as $field_name) {
            if (isset($_POST[$field_name])) {
                $value = sanitize_text_field($_POST[$field_name]);
                if ('' !== $value && $field_name !== CORE::$slug.'-usage-period') {
                    $value = intval($value);
                }

                if ($value > 0) {
                    update_user_meta($user_id, $field_name, $value);
                } else {
                    delete_user_meta($user_id, $field_name);
                }
            }
        }
    }

    public static function reset_current_usage($user_id = null)
    {
        if (!empty($user_id)) {
            // Delete usage for specific user
            delete_user_meta($user_id, CORE::$slug.'-usage-limits');
        } else {
            // Delete usage for all users
            delete_metadata('user', null, CORE::$slug.'-usage-limits', null, true);

            // Delete usage for all not logged in users
            Helpers::delete_transients_with_prefix(CORE::$slug.'-usage-limits-');
        }

        // Delete the current usage from persistent/object caches
        Helpers::purge_cache_others();
    }

    public static function unlock_module($password = null)
    {
        $key = 'wpcp-'.Processor::instance()->get_listtoken().'-pass';
        $hash = Processor::instance()->get_shortcode_option('password_hash');

        if (empty($hash)) {
            return true;
        }

        // Check with the given password
        if (!empty($password)) {
            require_once ABSPATH.WPINC.'/class-phpass.php';

            $is_valid = wp_check_password($password, $hash);

            if ($is_valid) {
                $cookie_options = [
                    'expires' => null,
                    'path' => COOKIEPATH,
                    'domain' => COOKIE_DOMAIN,
                    'secure' => is_ssl() && 'https' === parse_url(get_option('home'), PHP_URL_SCHEME),
                    'httponly' => true,
                    'samesite' => 'Strict',
                ];

                setcookie($key, Processor::instance()->get_shortcode_option('password_hash'), $cookie_options);

                // Set the password hash in the $_COOKIE as well, so that the modules that will be rendered will already be unlocked.
                $_COOKIE[$key] = Processor::instance()->get_shortcode_option('password_hash');
            }

            return $is_valid;
        }

        // If no password is given, check with the cookie
        if (!isset($_COOKIE[$key])) {
            return false;
        }

        $cookie_hash = wp_unslash($_COOKIE[$key]);
        if (!str_starts_with($cookie_hash, '$P$B') && !str_starts_with($cookie_hash, '$wp')) {
            return false;
        }

        if ($hash === $cookie_hash) {
            return true;
        }

        return false;
    }

    /**
     * Check if individual user limits are supported.
     *
     * @return bool true if individual user limits are supported, false otherwise
     */
    public static function are_individual_usage_limits_supported()
    {
        return 'Yes' === Settings::get('usage_allow_individual_limits', 'Yes');
    }

    private static function _do_limit_reached_events($entry_id, $user_id, $error_message)
    {
        do_action('outofthebox_log_event', 'outofthebox_user_reached_limit', $entry_id, ['message' => $error_message]);

        if ('Yes' === Settings::get('download_limits_notification') && !empty($user_id)) {
            self::_send_notification($user_id, $error_message);
        }
    }

    private static function _send_notification($user_id, $error_message)
    {
        $has_send_email = get_transient(CORE::$slug."-email-notification-user-{$user_id}-user-limits");
        if (!empty($has_send_email)) {
            return;
        }

        $user = get_user_by('id', $user_id);

        if (empty($user)) {
            return;
        }

        try {
            $headers = [
                'Content-Type: text/html; charset=UTF-8',
            ];

            $user_limits = self::_get_limits_for_user($user_id);

            $recipient_html_message = \sprintf(\esc_html__('User %s has encountered the following download limitation: %s', 'wpcloudplugins'), '<b>'.$user->display_name.'</b>', '<i>'.$error_message.'</i>');
            $recipient_html_message .= '<br/><br/><strong>'.esc_html__('Usage Limits', 'wpcloudplugins').'</strong>';
            $recipient_html_message .= '<table>
<tr><td>'.esc_html__('Restriction Period', 'wpcloudplugins').'</td><td>'.esc_html(!empty($user_limits['usage_period']) ? $user_limits['usage_period'] : esc_attr__('Unlimited', 'wpcloudplugins')).'</td></tr>
<tr><td>'.esc_html__('Downloads', 'wpcloudplugins').'</td><td>'.esc_html(!empty($user_limits['downloads_per_user']) ? $user_limits['downloads_per_user'] : esc_attr__('Unlimited', 'wpcloudplugins')).'</td></tr>
<tr><td>'.esc_html__('Downloads per file', 'wpcloudplugins').'</td><td>'.esc_html(!empty($user_limits['downloads_per_user_per_file']) ? $user_limits['downloads_per_user_per_file'] : esc_attr__('Unlimited', 'wpcloudplugins')).'</td></tr>
<tr><td>'.esc_html__('ZIP downloads', 'wpcloudplugins').'</td><td>'.esc_html(!empty($user_limits['zip_downloads_per_user']) ? $user_limits['zip_downloads_per_user'] : esc_attr__('Unlimited', 'wpcloudplugins')).'</td></tr>
<tr><td>'.esc_html__('Bandwidth usage (in GB)', 'wpcloudplugins').'</td><td>'.esc_html(!empty($user_limits['bandwidth_per_user']) ? $user_limits['bandwidth_per_user'] : esc_attr__('Unlimited', 'wpcloudplugins')).'</td></tr>
</table><br/><br/>';
            $recipient_html_message .= \sprintf(\esc_html__('You can adjust the limit for this user via %s', 'wpcloudplugins'), get_edit_profile_url($user_id));

            $recipient = get_option('admin_email');
            $recipient_subject = get_bloginfo().' | '.\sprintf(\esc_html__('User %s has reached the download limitation', 'wpcloudplugins'), $user->display_name);
            wp_mail($recipient, $recipient_subject, $recipient_html_message, $headers);

            set_transient(CORE::$slug."-email-notification-user-{$user_id}-user-limits", true, \DAY_IN_SECONDS);
        } catch (\Exception $ex) {
            Helpers::log_error('Could not send restriction notification email.', 'Notification', ['recipient' => $recipient], __LINE__, $ex);
        }
    }

    private static function _get_limits_for_user($user_id, $use_cache = true)
    {
        $limits_for = '1' === Processor::instance()->get_shortcode_option('download_limits') ? Processor::instance()->get_listtoken() : 'global';

        if ($use_cache) {
            $user_limits = wp_cache_get('wpcp-'.$user_id.'-'.CORE::$slug.'-usage-limits-'.$limits_for, 'wpcp-'.CORE::$slug.'-limits', false);
            if (is_array($user_limits) && !empty($user_limits)) {
                return $user_limits;
            }
        }

        // Get global and module values
        $settings = [
            'usage_period' => Settings::get('usage_period'),
            'downloads_per_user' => Settings::get('downloads_per_user'),
            'downloads_per_user_per_file' => Settings::get('downloads_per_user_per_file'),
            'zip_downloads_per_user' => Settings::get('zip_downloads_per_user'),
            'bandwidth_per_user' => Settings::get('bandwidth_per_user'),
        ];

        foreach ($settings as $key => $default) {
            $module_value = Processor::instance()->get_shortcode_option($key);
            if (!empty($module_value) && 'default' !== $module_value) {
                $settings[$key] = $module_value;
            }
        }

        // Get user-specific values
        if (!empty($user_id) && self::are_individual_usage_limits_supported()) {
            $user_settings = [
                'usage_period' => get_user_meta($user_id, CORE::$slug.'-usage-period', true),
                'downloads_per_user' => get_user_meta($user_id, CORE::$slug.'-download-per-day', true),
                'downloads_per_user_per_file' => get_user_meta($user_id, CORE::$slug.'-download-per-file', true),
                'zip_downloads_per_user' => get_user_meta($user_id, CORE::$slug.'-zip-download-per-day', true),
                'bandwidth_per_user' => get_user_meta($user_id, CORE::$slug.'-bandwidth-per-day', true),
            ];

            foreach ($user_settings as $key => $user_value) {
                if (!empty($user_value) && 'default' !== $user_value) {
                    $settings[$key] = $user_value;
                }
            }
        }

        // Ensure values are integers where applicable
        $user_limits = [
            'usage_period' => $settings['usage_period'],
            'downloads_per_user' => $settings['downloads_per_user'] >= 1 ? (int) $settings['downloads_per_user'] : '',
            'downloads_per_user_per_file' => $settings['downloads_per_user_per_file'] >= 1 ? (int) $settings['downloads_per_user_per_file'] : '',
            'zip_downloads_per_user' => $settings['zip_downloads_per_user'] >= 1 ? (int) $settings['zip_downloads_per_user'] : '',
            'bandwidth_per_user' => $settings['bandwidth_per_user'] >= 1 ? (int) $settings['bandwidth_per_user'] : '',
        ];

        wp_cache_set('wpcp-'.$user_id.'-'.CORE::$slug.'-usage-limits-'.$limits_for, $user_limits, 'wpcp-'.CORE::$slug.'-limits', 600);

        return $user_limits;
    }
}
