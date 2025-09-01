<?php

use TheLion\OutoftheBox\App;
use TheLion\OutoftheBox\Client;
use TheLion\OutoftheBox\Helpers;

/**
 * Class WPCP_OUTOFTHEBOX_EVENT_TOKENS.
 */
class WPCP_OUTOFTHEBOX_EVENT_TOKENS
{
    /**
     * Trigger codes that shares the same tokens.
     *
     * @var array TOKENS_TRIGGERS
     */
    public const TOKENS_TRIGGERS = [
        'WPCP_OUTOFTHEBOX_NEW_EVENT',
    ];

    public function __construct()
    {
        add_action('automator_before_trigger_completed', [$this, 'save_token_data'], 20, 2);

        foreach (self::TOKENS_TRIGGERS as $trigger_code) {
            add_filter('automator_maybe_trigger_wpcp-outofthebox_'.strtolower($trigger_code).'_tokens', [$this, 'register_tokens'], 20, 2);
        }

        add_filter('automator_maybe_parse_token', [$this, 'parse_tokens'], 20, 6);
    }

    /**
     * Register the tokens.
     *
     * @param mixed $tokens
     * @param mixed $args
     */
    public function register_tokens($tokens = [], $args = [])
    {
        if (!automator_do_identify_tokens()) {
            return $tokens;
        }

        $trigger_integration = $args['integration'];

        $trigger_meta = $args['meta'];

        $tokens_collection = array_merge(
            $this->get_account_tokens(),
            $this->get_entry_tokens(),
            $this->get_owner_tokens(),
            $this->get_additional_tokens(),
        );

        $tokens = [];

        foreach ($tokens_collection as $token) {
            $tokens[] = [
                'tokenId' => str_replace(' ', '_', $token['id']),
                'tokenName' => $token['name'],
                'tokenType' => 'text',
                'tokenIdentifier' => strtoupper('WPCP_OUTOFTHEBOX_'.$token['id']),
            ];
        }

        return $tokens;
    }

    /**
     * Save the token data.
     *
     * @param mixed $args
     * @param mixed $trigger
     */
    public function save_token_data($args, $trigger)
    {
        if (!isset($args['trigger_args']) || !isset($args['entry_args']['code'])) {
            return;
        }

        // Check if trigger code is for our own ones.
        if (!in_array($args['entry_args']['code'], self::TOKENS_TRIGGERS, true)) {
            return;
        }

        list($event_type, $event_data, $cached_entry, $processor) = $args['trigger_args'];
        $entry_arr = (array) $cached_entry;

        // Custom values
        $entry_arr['size'] = Helpers::bytes_to_size_1024($cached_entry->get_size());
        $entry_arr['icon'] = $cached_entry->get_icon();
        $entry_arr['thumbnail'] = ($cached_entry->has_own_thumbnail() ? Client::instance()->get_thumbnail($cached_entry, true, 640, 480) : $cached_entry->get_icon_retina());
        $entry_arr['preview_url'] = 'https://www.dropbox.com/home/'.$cached_entry->get_path_display();
        $entry_arr['download_url'] = null;

        // Folder owners
        $linked_users = Helpers::get_linked_users($cached_entry->get_path_display());
        $current_account = App::get_current_account();

        $event_data_arr = [
            'type' => $event_type,
            'event' => $event_data,
            'entry' => $entry_arr,
            'account' => [
                'id' => $current_account->get_id(),
                'name' => $current_account->get_name(),
                'email' => $current_account->get_email(),
                'image' => $current_account->get_image(),
            ],
            'owner' => [
                'data' => $linked_users,
                'names' => array_column($linked_users, 'display_name'),
                'emailaddresses' => array_column($linked_users, 'user_email'),
            ],
            'user_id' => get_current_user_id(),
            'page' => Helpers::get_page_url(),
        ];

        $event_data_str = wp_json_encode($event_data_arr);

        Automator()->db->token->save('WPCP_OUTOFTHEBOX_EVENT_DATA', $event_data_str, $args['trigger_entry']);
    }

    /**
     * Parsing the tokens.
     *
     * @param mixed $value
     * @param mixed $pieces
     * @param mixed $recipe_id
     * @param mixed $trigger_data
     * @param mixed $user_id
     * @param mixed $replace_args
     */
    public function parse_tokens($value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args)
    {
        $trigger_code = '';

        if (isset($trigger_data[0]['meta']['code'])) {
            $trigger_code = $trigger_data[0]['meta']['code'];
        }

        if (empty($trigger_code) || !in_array($trigger_code, self::TOKENS_TRIGGERS, true)) {
            return $value;
        }

        if (!is_array($pieces) || !isset($pieces[1]) || !isset($pieces[2])) {
            return $value;
        }

        // The $pieces[2] is the token id.
        $token_id_parts = explode('_', $pieces[2], 2);

        // Get the meta from database record.
        $event_data = json_decode(Automator()->db->token->get('WPCP_OUTOFTHEBOX_EVENT_DATA', $replace_args), true);

        // Add a check to prevent notice.
        if (1 === count($token_id_parts)) {
            if (isset($event_data[$token_id_parts[0]])) {
                $value = $event_data[$token_id_parts[0]];
            }
        }
        if (isset($token_id_parts[0], $token_id_parts[1])) {
            // Example: $event_data['data']['entry']['id].
            if (isset($event_data[$token_id_parts[0]][$token_id_parts[1]])) {
                $value = $event_data[$token_id_parts[0]][$token_id_parts[1]];
            }
        }

        return $value;
    }

    /**
     * Get entry tokens.
     *
     * @return array the entry tokens
     */
    public function get_entry_tokens()
    {
        return [
            [
                'name' => esc_html__('File ID', 'wpcloudplugins-automator'),
                'id' => 'entry_id',
            ],
            [
                'name' => esc_html__('File name', 'wpcloudplugins-automator'),
                'id' => 'entry_name',
            ],
            [
                'name' => esc_html__('File mimetype', 'wpcloudplugins-automator'),
                'id' => 'entry_mimetype',
            ],
            [
                'name' => esc_html__('File size', 'wpcloudplugins-automator'),
                'id' => 'entry_size',
            ],
            [
                'name' => esc_html__('File Icon URL', 'wpcloudplugins-automator'),
                'id' => 'entry_icon',
            ],
            [
                'name' => esc_html__('File description', 'wpcloudplugins-automator'),
                'id' => 'entry_description',
            ],
            [
                'name' => esc_html__('File thumbnail URL', 'wpcloudplugins-automator'),
                'id' => 'entry_thumbnail',
            ],
            [
                'name' => esc_html__('File preview URL', 'wpcloudplugins-automator'),
                'id' => 'entry_preview_url',
            ],
            [
                'name' => esc_html__('Parent folder Path', 'wpcloudplugins-automator'),
                'id' => 'event_parent_path',
            ],
        ];
    }

    /**
     * Get account tokens.
     *
     * @return array the account tokens
     */
    public function get_account_tokens()
    {
        return [
            [
                'name' => esc_html__('Account ID', 'wpcloudplugins-automator'),
                'id' => 'account_id',
            ],
            [
                'name' => esc_html__('Account Name', 'wpcloudplugins-automator'),
                'id' => 'account_name',
            ],
            [
                'name' => esc_html__('Account Email', 'wpcloudplugins-automator'),
                'id' => 'account_email',
            ],
            [
                'name' => esc_html__('Account Profile Image Url', 'wpcloudplugins-automator'),
                'id' => 'account_image',
            ],
        ];
    }

    /**
     * Owner tokens.
     *
     * @return array the owner tokens
     */
    public function get_owner_tokens()
    {
        return [
            [
                'name' => esc_html__('Owner names with access to the folder. Uses Personal Folder data. (##,###,####)'),
                'id' => 'owner_names',
            ],
            [
                'name' => esc_html__('Owner emails addresses with access to the folder. Uses Personal Folder data. (##@##.###,###@###.###)'),
                'id' => 'owner_emailaddresses',
            ],
        ];
    }

    /**
     * Additional tokens.
     *
     * @return array the additional tokens
     */
    public function get_additional_tokens()
    {
        return [
            [
                'name' => esc_html__('Event ID'),
                'id' => 'type',
            ],
            [
                'name' => esc_html__('Event Name'),
                'id' => 'event_text',
            ],
            [
                'name' => esc_html__('Event Description'),
                'id' => 'event_description',
            ],
            [
                'name' => esc_html__('Action page'),
                'id' => 'page',
            ],
        ];
    }
}
