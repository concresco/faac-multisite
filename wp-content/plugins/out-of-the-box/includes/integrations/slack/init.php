<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.14
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox\Integrations;

use TheLion\OutoftheBox\Helpers;
use TheLion\OutoftheBox\Settings;

defined('ABSPATH') || exit;

class Slack
{
    /**
     * The single instance of the class.
     *
     * @var Slack
     */
    protected static $_instance;

    protected static $webhook_url;
    protected static $event_types;

    public function __construct()
    {
        $this->set_hooks();
        self::$webhook_url = Settings::get('slack_endpoint_url', '');
        self::$event_types = Settings::get('slack_event_types', []);
    }

    /**
     * Slack Instance.
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return Slack - Slack instance
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
        add_action('outofthebox_webhook_start_sending', [$this, 'send'], 10, 2);
    }

    public function send($webhook_endpoint, $events = [])
    {
        if (empty(self::$webhook_url) || false === filter_var(self::$webhook_url, FILTER_VALIDATE_URL)) {
            return false;
        }

        if (empty($events)) {
            return false;
        }

        $json_blocks = Settings::get('slack_blocks', self::get_default_block_template());

        if (empty($json_blocks)) {
            $json_blocks = self::get_default_block_template();
        }

        $result = '';

        foreach ($events as $event) {
            if ('outofthebox_test_event' !== $event['type'] && (!isset(self::$event_types[$event['type']]) || 'Yes' !== self::$event_types[$event['type']])) {
                continue;
            }

            $json_blocks = trim(self::fill_placeholders($json_blocks, $event));

            $ch = curl_init(self::$webhook_url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_blocks);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CAINFO, OUTOFTHEBOX_ROOTDIR.'/vendors/dropbox-sdk/vendor/guzzlehttp/guzzle/src/Handler/cacerts.pem');

            $result = curl_exec($ch);

            if (!empty($result) && 'ok' !== $result) {
                Helpers::log_error('Not able to send Slack notification', 'Integration/Slack', ['result' => $result, 'json_blocks' => $json_blocks], __LINE__);

                break;
            }

            curl_close($ch);
        }

        return $result;
    }

    public static function fill_placeholders($json, $events)
    {
        preg_match_all('/%([^%]+)%/U', $json, $placeholders, PREG_SET_ORDER, 0);

        foreach ($placeholders as $placeholder) {
            $value = self::get_value_by_placeholder($events, $placeholder[0]);
            if (null !== $value) {
                $json = str_replace($placeholder[0], $value, $json);
            } else {
                $json = str_replace($placeholder[0], '', $json);
            }
        }

        return $json;
    }

    /**
     * Get the value by placeholder.
     *
     * @param mixed  $data        the data to search in
     * @param string $placeholder the placeholder to search for
     *
     * @return null|mixed the value if found, null otherwise
     */
    public static function get_value_by_placeholder($data, $placeholder)
    {
        $path = trim($placeholder, '%');

        $keys = explode('.', $path);

        $value = $data;
        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                return null;
            }
        }

        return $value;
    }

    /**
     * Test the notification functionality.
     */
    public static function test_notification()
    {
        $test_events = ['events' => [
            'timestamp' => date('c'),
            'type' => 'outofthebox_test_event',
            'description' => 'John Johnson previewed the file UK Festival Market Report.pdf',
            'data' => [
                'entry' => [
                    'id' => '1-y9psKPDJCycz38c2sN_a3lRoO9S',
                    'name' => 'UK Festival Market Report.pdf',
                    'mimetype' => 'application/pdf',
                    'size' => '2 MB',
                    'icon' => 'https://picsum.photos/id/117/256',
                    'description' => 'Festival Insights and the UK Festival Awards are proud to release the UK Festival Market Report.',
                    'thumbnail' => 'https://picsum.photos/id/117/536/354',
                    'preview_url' => 'https://example.com',
                    'download_url' => 'https://example.com',
                    'is_dir' => false,
                    'parent_id' => '0By3zfuC9ZTdGZCT1pUd0E',
                    'parent_path' => '/Path/To/Folder',
                ],
                'account' => [
                    'id' => '1030123322434145',
                    'name' => 'Your Account name',
                    'email' => 'info@example.com',
                    'image' => 'https://picsum.photos/id/237/256',
                ],
            ],
            'user' => [
                'ID' => '3',
                'user_login' => 'John Johnson',
                'user_nicename' => 'john-johnson',
                'user_email' => 'info@example.com',
                'display_name' => 'John Johnson',
            ],
            'page' => Helpers::get_page_url(),
        ]];

        $result = self::instance()->send('', $test_events);

        echo \json_encode(['result' => $result]);

        exit;
    }

    public static function get_default_block_template()
    {
        // Return a JSON encoded string representing the default block template
        return \json_encode(
            [
                'blocks' => [
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => '%description%',
                        ],
                    ],
                    [
                        'type' => 'divider',
                    ],
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => "*<%data.entry.preview_url%|%data.entry.name%>*\n*Location:*\t%data.entry.parent_path%\n*Size:*\t\t\t%data.entry.size%",
                        ],
                        'accessory' => [
                            'type' => 'image',
                            'image_url' => '%data.entry.thumbnail%',
                            'alt_text' => '%data.entry.name%',
                        ],
                    ],
                    [
                        'type' => 'actions',
                        'elements' => [
                            [
                                'type' => 'button',
                                'text' => [
                                    'type' => 'plain_text',
                                    'emoji' => true,
                                    'text' => sprintf('Open in %s', 'Dropbox'),
                                ],
                                'style' => 'primary',
                                'url' => '%data.entry.preview_url%',
                            ],
                        ],
                    ],
                    [
                        'type' => 'divider',
                    ],
                    [
                        'type' => 'context',
                        'elements' => [
                            [
                                'type' => 'mrkdwn',
                                'text' => 'Received via %page%',
                            ],
                        ],
                    ],
                ],
            ],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT // JSON encoding options
        );
    }
}

Slack::instance();
