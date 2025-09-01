<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.1
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

defined('ABSPATH') || exit;

class Backup
{
    public static function do_export()
    {
        global $wpdb;

        $data = [
            'plugin' => 'out-of-the-box',
            'version' => \OUTOFTHEBOX_VERSION,
            'db_prefix' => $wpdb->prefix,
        ];

        if (in_array(Settings::get('tools_export_fields'), ['', 'all', 'settings'])) {
            $data['settings'] = self::export_settings();
        }

        if (in_array(Settings::get('tools_export_fields'), ['', 'all', 'userfolders'])) {
            $data['userfolders'] = self::export_privatefolders();
        }

        if (in_array(Settings::get('tools_export_fields'), ['', 'all', 'modules'])) {
            $data['modules'] = self::export_modules();
        }

        if (in_array(Settings::get('tools_export_fields'), ['', 'all', 'events'])) {
            $data['events'] = self::export_events();
        }

        $filename = date('Y-m-d').' - wpcloudplugins-outofthebox-backup-'.Settings::get('tools_export_fields').'.json';
        if (function_exists('gzencode')) {
            $filename .= '.gz';
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; '.sprintf('filename="%s"; ', rawurlencode($filename)).sprintf("filename*=utf-8''%s", rawurlencode($filename)));

        $json = \json_encode($data, JSON_PRETTY_PRINT);

        if (function_exists('gzencode')) {
            $json = gzencode($json);
        }

        echo $json;

        exit;
    }

    public static function do_import()
    {
        $data = self::process_import_file();

        if (empty($data) || !isset($data['plugin'])) {
            echo json_encode(['result' => 0, 'msg' => \esc_html__('Cannot read import file.', 'wpcloudplugins')]);

            exit;
        }

        if ('out-of-the-box' !== $data['plugin']) {
            echo json_encode(['result' => 0, 'msg' => \esc_html__('This is not a backup file for this plugin.', 'wpcloudplugins')]);

            exit;
        }

        // Import Settings
        if (isset($data['settings'])) {
            $result = self::import_settings($data['settings']);
        }

        // Import User <> Folder links
        if (isset($data['userfolders'])) {
            $result = self::import_privatefolders($data['userfolders'], $data['db_prefix'] ?? null);
        }

        // Import Modules
        if (isset($data['modules'])) {
            $result = self::import_modules($data['modules']);
        }

        // Import Events
        if (isset($data['events'])) {
            $result = self::import_events($data['events']);
        }

        // Purge WordPress Cache
        Helpers::purge_cache_others();

        if (empty($result)) {
            echo json_encode(['result' => 0, 'msg' => \esc_html__('Data has not been successfully imported.', 'wpcloudplugins')]);
        } else {
            echo json_encode(['result' => 1, 'msg' => \esc_html__('Data has been successfully imported.', 'wpcloudplugins')]);
        }

        exit;
    }

    private static function export_settings()
    {
        return serialize(\apply_filters('outofthebox_export_custom_settings', [
            'options' => [
                'out_of_the_box_settings' => get_option(Settings::$db_key),
                'out_of_the_box_uniqueID' => get_option('out_of_the_box_uniqueID'),
                'out_of_the_box_activated' => get_option('out_of_the_box_activated'),
                'out_of_the_box_version' => get_option('out_of_the_box_version'),
            ],
            'site_options' => [
                'outofthebox_network_settings' => get_site_option(Settings::$db_network_key),
                'out_of_the_box_guestlinkedto' => get_site_option('out_of_the_box_guestlinkedto'),
                'outofthebox_purchaseid' => get_site_option('outofthebox_purchaseid'),
            ],
        ]));
    }

    private static function export_privatefolders()
    {
        global $wpdb;

        $sql = "SELECT * FROM `{$wpdb->prefix}usermeta` WHERE `meta_key` = '{$wpdb->prefix}out_of_the_box_linkedto'";
        $results = $wpdb->get_results($sql, ARRAY_A);

        if (empty($results)) {
            return null;
        }

        return $results;
    }

    private static function export_modules()
    {
        return Modules::export();
    }

    private static function export_events()
    {
        return Events::export();
    }

    private static function import_settings($data)
    {
        $data = \unserialize($data);

        // Add options
        if (isset($data['options'])) {
            foreach ($data['options'] as $option_key => $option_value) {
                update_option($option_key, $option_value);
            }
        }

        // Add site options
        if (isset($data['site_options'])) {
            foreach ($data['site_options'] as $site_option_key => $site_option_value) {
                update_site_option($site_option_key, $site_option_value);
            }
        }

        return true;
    }

    private static function import_privatefolders($data = [], $db_prefix = null)
    {
        global $wpdb;

        if (!empty($db_prefix)) {
            foreach ($data as &$row) {
                $row['meta_key'] = str_replace($db_prefix, $wpdb->prefix, $row['meta_key']);
            }
        }

        return self::import_to_table($data, $wpdb->prefix.'usermeta');
    }

    private static function import_modules($data = [])
    {
        global $wpdb;

        return self::import_to_table($data, "{$wpdb->prefix}posts");
    }

    private static function import_events($data = [])
    {
        return self::import_to_table($data, Event_DB_Model::table());
    }

    private static function process_import_file()
    {
        if (empty($_FILES['tools_import_file']['tmp_name'])) {
            echo json_encode(['result' => 0, 'msg' => \esc_html__('Cannot read import file.', 'wpcloudplugins')]);

            exit;
        }

        $file_content = file_get_contents($_FILES['tools_import_file']['tmp_name']);

        if (strpos($_FILES['tools_import_file']['name'], '.json.gz')) {
            $file_content = gzdecode($file_content);
        }

        return json_decode($file_content, true);
    }

    private static function import_to_table($data, $table)
    {
        global $wpdb;

        Helpers::set_time_limit(300);

        $column_names = array_keys(current($data));
        $columns_list = implode(', ', $column_names);

        $support_csv = true;

        // First try CSV LOAD DATA
        $support_local_infile = $wpdb->get_row("SHOW VARIABLES LIKE 'secure_file_priv'", ARRAY_A);
        if (!array_key_exists('Value', $support_local_infile) || null === $support_local_infile['Value']) {
            $support_csv = false;
        }
        if ('' !== $support_local_infile['Value'] && !is_writable($support_local_infile['Value'])) {
            $support_csv = false;
        }

        // Use CSV load if available.
        if ($support_csv) {
            if ('' === $support_local_infile['Value']) {
                $handle = tmpfile();
            } else {
                $tmpfname = tempnam($support_local_infile['Value'], 'wp-');
                $handle = fopen($tmpfname, 'w');
            }

            $filepath = str_replace(['\\', '//'], '/', stream_get_meta_data($handle)['uri']);

            $separator = ',';
            $enclosure = '"';
            $escape = '\\';

            fputcsv($handle, $column_names, $separator, $enclosure, $escape);
            foreach ($data as $row) {
                fputcsv($handle, $row, $separator, $enclosure, $escape);
            }

            rewind($handle);

            $sql = "
        LOAD DATA INFILE '{$filepath}' REPLACE INTO TABLE `{$table}`
        FIELDS TERMINATED BY '{$separator}'
        ENCLOSED BY '{$enclosure}'
        ESCAPED BY '\\\\'
        LINES TERMINATED BY '\\n'
        IGNORE 1 LINES
        ({$columns_list});
        ";

            $sql_result = $wpdb->query($sql);

            @unlink($filepath);

            if (false !== $sql_result) {
                return true;
            }
        }

        // Otherwise, insert rows in groups
        $groups = array_chunk($data, 500);

        foreach ($groups as $group) {
            $sql = "REPLACE INTO `{$table}` ({$columns_list}) VALUES ";

            $rowArr = [];
            foreach ($group as $row) {
                $rowArr[] = '("'.implode('","', array_map('addslashes', $row)).'")';
            }

            $sql .= implode(','."\r\n", $rowArr);

            $sql_result = $wpdb->query($sql);

            if (empty($sql_result)) {
                return false;
            }
        }

        return true;
    }
}
