<?php

use TheLion\OutoftheBox\Helpers;

/**
 * Class WPCP_OUTOFTHEBOX_UPLOADS_TOKENS.
 */
class WPCP_OUTOFTHEBOX_UPLOADS_TOKENS
{
    /**
     * Trigger codes that shares the same tokens.
     *
     * @var array TOKENS_TRIGGERS
     */
    public const TOKENS_TRIGGERS = [
        'WPCP_OUTOFTHEBOX_UPLOADSFINISHED',
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
            $this->get_entries_tokens(),
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

        $cached_nodes = array_shift($args['trigger_args']);
        $cached_nodes = (!is_array($cached_nodes)) ? [$cached_nodes] : $cached_nodes;
        $first_cached_node = reset($cached_nodes);

        $entries_arr = array_map(function ($cached_node) { return (array) $cached_node; }, $cached_nodes);

        // Get global params
        $sizes_in_bytes = array_column($entries_arr, 'size');
        $total_file_size = Helpers::bytes_to_size_1024(array_sum($sizes_in_bytes));

        $processor = array_shift($args['trigger_args']);

        // Folder owners
        $linked_users = Helpers::get_linked_users($first_cached_node->get_path_display());

        $upload_data_arr = [
            'data' => [
                'entries' => $entries_arr,
                'ids' => array_column($entries_arr, 'id'),
                'total_uploaded' => count($cached_nodes),
                'total_file_size' => $total_file_size,
                'upload_folder_path' => dirname($first_cached_node->get_path($processor->get_root_folder())),
                'upload_folder_url' => 'https://www.dropbox.com/home/'.$first_cached_node->get_parent(),
                'owners' => $linked_users,
                'owner_names' => array_column($linked_users, 'display_name'),
                'owner_emailaddresses' => array_column($linked_users, 'user_email'),
            ],
            'user_id' => get_current_user_id(),
            'page' => Helpers::get_page_url(),
        ];

        $upload_data = wp_json_encode($upload_data_arr);

        Automator()->db->token->save('WPCP_OUTOFTHEBOX_UPLOADS_DATA', $upload_data, $args['trigger_entry']);
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

        // Get the meta from database record.
        $upload_data = json_decode(Automator()->db->token->get('WPCP_OUTOFTHEBOX_UPLOADS_DATA', $replace_args), true);

        if (empty($upload_data) || (0 === count((array) $upload_data))) {
            return '';
        }

        switch ($pieces[2]) {
            case 'total_uploaded':
            case 'total_file_size':
            case 'upload_folder_path':
            case 'upload_folder_url':
                $value = $upload_data['data'][$pieces[2]];

                break;

            case 'owner_names':
            case 'owner_emailaddresses':
                $value = implode(',', $upload_data['data'][$pieces[2]]);

                break;

            case 'uploaded_files_ids':
                $value = implode(',', $upload_data['data']['ids']);

                break;

            case 'uploaded_files_plain':
                foreach ($upload_data['data']['entries'] as $entry) {
                    $value .= "{$entry['name']} (".Helpers::bytes_to_size_1024($entry['size']).")\r\n";
                }

                break;

            case 'uploaded_files_html':
                // Render HTML
                $current = 0;

                ob_start();
                ?><table cellpadding="0" cellspacing="0" width="100%" border="0" style="cellspacing:0;line-height:22px;border:none;table-layout:auto;width:100%;">
    <?php foreach ($upload_data['data']['entries'] as $entry) {            ?>
    <tr style="<?php echo ($current % 2) ? 'background: #fafafa;' : ''; ?> height: 26px;">
        <td style="width:20px;padding-right:10px;padding-left:5px;border:none;">
            <img alt="" height="16" src="<?php echo Helpers::get_default_icon($entry['mimetype']); ?>" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:16px;max-width:16px;" width="16">
        </td>
        <td style="padding-right:10px;border:none;">
            <a href="<?php echo urldecode($entry['preview_link']); ?>" rel="noopener" target="_blank"><?php echo basename($entry['name']).' ('.Helpers::bytes_to_size_1024($entry['size']).')'; ?></a>
            <?php echo (!empty($entry['description'])) ? '<br/><div style="font-weight:normal; max-height: 200px; overflow-y: auto;word-break: break-word;">'.nl2br($entry['description']).'</div>' : ''; ?>
        </td>
    </tr>
    <?php ++$current;
    } ?>
</table><?php

                // Remove any newlines
                $value = trim(preg_replace('/\s+/', ' ', ob_get_clean()));

                break;
        }

        return $value;
    }

    /**
     * Get files tokens.
     *
     * @return array the files tokens
     */
    public function get_entries_tokens()
    {
        return [
            [
                'name' => esc_html__('Number of files uploaded (#)', 'wpcloudplugins-automator'),
                'id' => 'total_uploaded',
            ],
            [
                'name' => esc_html__('Total file size (#MB)', 'wpcloudplugins-automator'),
                'id' => 'total_file_size',
            ],
            [
                'name' => esc_html__('List of uploaded files (plain)', 'wpcloudplugins-automator'),
                'id' => 'uploaded_files_plain',
            ],
            [
                'name' => esc_html__('List of uploaded files (HTML)', 'wpcloudplugins-automator'),
                'id' => 'uploaded_files_html',
            ],
            [
                'name' => esc_html__('List of uploaded files IDs (##,###,####)', 'wpcloudplugins-automator'),
                'id' => 'uploaded_files_ids',
            ],
            [
                'name' => esc_html__('Full upload location path', 'wpcloudplugins-automator'),
                'id' => 'upload_folder_path',
            ],
            [
                'name' => esc_html__('Link to upload location in the cloud', 'wpcloudplugins-automator'),
                'id' => 'upload_folder_url',
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
                'name' => esc_html__('Action page'),
                'id' => 'page',
            ],
        ];
    }
}
