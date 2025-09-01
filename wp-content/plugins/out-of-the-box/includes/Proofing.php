<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       3.1
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

defined('ABSPATH') || exit;

class Proofing
{
    /**
     * The label colors.
     */
    public static $label_colors = [
        '#8bc34a',
        '#ffcc00',
        '#ff9800',
        '#ff6f61',
        '#e91e63',
        '#3f51b5',
        '#590E54',
    ];

    /**
     * Attributes in the module shortcode.
     *
     * @var []
     */
    public $module_attributes = [];

    /**
     * The module ID.
     *
     * @var int
     */
    public $module_id;

    /**
     * The module.
     *
     * @var \WP_Post
     */
    public $module;

    /**
     * The single instance of the class.
     *
     * @var Proofing
     */
    protected static $_instance;

    protected static $post_meta_key = 'wpcp_outofthebox_proof_selection';

    /**
     * The selection identifier.
     *
     * @var string
     */
    protected $ident;

    /**
     * Constructor.
     * Adds actions for creating post type and handling AJAX requests.
     */
    public function __construct()
    {
        // Render Selections in Edit Post
        add_action('edit_form_after_title', [$this, 'display_approved_view']);

        // Handle AJAX requests for saving and duplicating modules
        add_action('wp_ajax_outofthebox-proofing-dashboard', [$this, 'process_admin_ajax_request']);

        // Update Proofing module attributes when creating a new module
        add_filter('outofthebox_create_module_postarr', [$this, 'set_default_password_module'], 10, 1);
    }

    /**
     * Get the single instance of the class.
     *
     * @return Proofing - Proofing instance
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Process AJAX requests for proofing actions.
     */
    public function process_ajax_request()
    {
        // Load the Module
        $module_id = Processor::instance()->get_shortcode_option('id');

        $request = array_filter(filter_var_array($_REQUEST ?? [], [
            'type' => FILTER_SANITIZE_SPECIAL_CHARS,
            'ident' => FILTER_SANITIZE_SPECIAL_CHARS,
        ]));

        $this->set_module_and_ident($module_id, $request['ident'] ?? null);

        if (empty($this->module_id)) {
            \wp_send_json_error(esc_html__('Module ID is missing', 'wpcloudplugins'));
        }

        if (empty($this->module)) {
            \wp_send_json_error(esc_html__('Module not found', 'wpcloudplugins'));
        }

        if (empty($this->ident)) {
            \wp_send_json_error(esc_html__('User not found', 'wpcloudplugins'));
        }

        switch ($request['type']) {
            case 'get-selection':
                $selection = $this->get_selection();
                $selection['labels'] = $this->get_labels();

                \wp_send_json_success($selection);

                break;

            case 'save-selection':
                // Sanitize the post data
                $data = [
                    'items' => \json_decode(wp_unslash($_POST['items']) ?? [], true),
                    'last_modified' => current_datetime()->format(\DateTime::ATOM),
                ];

                $selection = $this->save_selection($data);
                $selection['labels'] = $this->get_labels();

                \wp_send_json_success($selection);

                break;

            case 'approve-selection':
                // Sanitize the post data
                $data = [
                    'items' => \json_decode(wp_unslash($_POST['items']) ?? [], true),
                    'approved' => 'true' === $_POST['approved'] ? true : false,
                    'approval_time' => 'true' === $_POST['approved'] ? current_datetime()->format(\DateTime::ATOM) : null,
                    'approval_message' => sanitize_textarea_field(wp_unslash($_POST['message']) ?? ''),
                ];

                $selection = $this->save_selection($data);
                $selection['labels'] = $this->get_labels();

                $this->approve_selection($selection);

                \wp_send_json_success($selection);

                break;

            default:
                break;
        }

        exit;
    }

    /**
     * Process admin AJAX requests for proofing actions.
     */
    public function process_admin_ajax_request()
    {
        check_ajax_referer('outofthebox-admin-action');

        $request = array_filter(filter_var_array($_REQUEST ?? [], [
            'type' => FILTER_SANITIZE_SPECIAL_CHARS,
            'module' => FILTER_VALIDATE_INT,
            'ident' => FILTER_SANITIZE_SPECIAL_CHARS,
            'email' => FILTER_SANITIZE_EMAIL,
        ]));

        if (false === user_can(get_current_user_id(), 'edit_post', $request['module'])) {
            wp_send_json_error(__('You do not have permissions to do any changes to this selection.', 'wpcloudplugins'));

            exit;
        }

        $this->set_module_and_ident($request['module'], $request['ident'] ?? null);

        switch ($request['type']) {
            case 'get-collection':
                $collection = $this->get_collection();

                \wp_send_json_success($collection);

                break;

            case 'set-approval':
                $selection = $this->get_selection();

                if (empty($selection)) {
                    wp_send_json_error(__('Selection not found', 'wpcloudplugins'));
                }

                $data = [
                    'approved' => 'true' === $_POST['approved'],
                    'approval_time' => 'true' === $_POST['approved'] ? current_datetime()->format(\DateTime::ATOM) : null,
                ];

                $selection = $this->save_selection($data);

                wp_send_json_success($selection);

                break;

            case 'delete-selection':
                if ($this->delete_selection()) {
                    wp_send_json_success(__('Selection deleted successfully.', 'wpcloudplugins'));
                } else {
                    wp_send_json_error(__('Failed to delete selection.', 'wpcloudplugins'));
                }

                break;

            case 'add_user_selection':
                if ($this->add_user_selection($request['email'])) {
                    wp_send_json_success(__('Selection deleted successfully.', 'wpcloudplugins'));
                } else {
                    wp_send_json_error(__('Failed to delete selection.', 'wpcloudplugins'));
                }

                break;

            case 'download-proof':
                $selection = $this->get_selection();
                $this->download_proof($selection);

                exit;

            default:
                break;
        }

        exit;
    }

    /**
     * Get labels for the current module.
     *
     * @return array - An array of labels
     */
    public function get_labels()
    {
        $has_labels = $this->module_attributes['proofing_use_labels'] ?? null;
        if ('0' === $has_labels || (empty($has_labels) && 'No' === Settings::get('proofing_use_labels'))) {
            return [];
        }

        $labels_names = !empty($this->module_attributes['proofing_labels']) ? \explode('|', $this->module_attributes['proofing_labels']) : Settings::get('proofing_labels', []);
        $labels = [];

        if (!empty($labels_names)) {
            $labels[] = [
                'id' => 'none',
                'title' => esc_html__('No label', 'wpcloudplugins'),
                'color' => '#666666',
            ];

            foreach ($labels_names as $key => $name) {
                $labels[] = [
                    'id' => \sanitize_key($name),
                    'title' => esc_html($name),
                    'color' => self::$label_colors[$key % count(self::$label_colors)],
                ];
            }
        }

        return apply_filters('outofthebox_proof_get_labels', $labels);
    }

    /**
     * Get label by ID.
     *
     * @param string $label_id - The label ID
     *
     * @return array - The label
     */
    public function get_label_by_id($label_id)
    {
        $labels = $this->get_labels();

        $label = current(array_filter($labels, function ($label) use ($label_id) {
            return $label['id'] === $label_id;
        }));

        if (empty($label)) {
            return [
                'id' => $label_id,
                'title' => $label_id.' ('.\esc_html__('Label not longer available', 'wpcloudplugins').')',
                'color' => '#666666',
            ];
        }

        return $label;
    }

    /**
     * Get selection for the current module and selection ident.
     *
     * @param null|mixed $module_id
     * @param null|mixed $ident
     *
     * @return array - An array containing the selection data
     */
    public function get_selection($module_id = null, $ident = null)
    {
        if (empty($module_id)) {
            $module_id = $this->module_id;
        }

        if (empty($ident)) {
            $ident = $this->ident;
        }

        // Get the post meta, and update it with the new selection
        $data = [
            'ident' => $ident,
            'user' => \is_user_logged_in() ? \get_current_user_id() : LeadCapture::instance()->get_lead_email(),
            'items' => [],
            'last_modified' => current_datetime()->format(\DateTime::ATOM),
            'approved' => false,
            'approval_time' => null,
            'approval_message' => null,
        ];

        $post_meta = get_post_meta($module_id, self::$post_meta_key.'_'.$ident, true);

        if (empty($post_meta)) {
            return $data;
        }
        $data = array_merge($data, $post_meta);

        return apply_filters('outofthebox_proof_get_selection', $data, $module_id, $ident);
    }

    /**
     * Get all selections for the current module.
     *
     * @return array - An array containing all selections
     */
    public function get_collection()
    {
        $post_meta = get_post_meta($this->module_id);

        $collection = [
            'items' => [],
            'users' => [],
            'labels' => self::get_labels(),
        ];

        // Get all keys starting with $post_meta_key
        $post_meta_keys = array_keys($post_meta);
        $filtered_keys = array_filter($post_meta_keys, function ($key) {
            return 0 === strpos($key, self::$post_meta_key);
        });

        foreach ($filtered_keys as $post_meta_key) {
            $ident = str_replace(self::$post_meta_key.'_', '', $post_meta_key);

            // Get each selection
            $selection = self::get_selection($this->module_id, $ident);

            // Get user information for the selection and add it to the collection
            $user = self::get_user_selection($selection);
            $collection['users'][] = [
                'id' => $selection['user'],
                'ident' => $ident,
                'display_name' => $user->display_name,
                'user_email' => $user->user_email,
                'thumbnail' => get_avatar_url($user->email ?? $user->ID, ['size' => '48']),
                'approved' => $selection['approved'],
                'approval_time_text' => !empty($selection['approval_time']) ? wp_date(get_option('date_format').', '.get_option('time_format'), strtotime($selection['approval_time'])) : '',
                'approval_message' => $selection['approval_message'] ?? '',
                'last_modified_time_text' => !empty($selection['last_modified']) ? wp_date(get_option('date_format').', '.get_option('time_format'), strtotime($selection['last_modified'])) : '',
                'download_proof_url' => OUTOFTHEBOX_ADMIN_URL."?action=outofthebox-proofing-dashboard&type=download-proof&module={$this->module_id}&ident={$ident}&_ajax_nonce=".wp_create_nonce('outofthebox-admin-action'),
                'selection_url' => $this->get_url_for_selection($ident),
            ];

            // Get Nodes
            $nodes = $this->get_nodes_for_selection($selection);

            // Build the list of all items, including labels, users and selections
            foreach ($selection['items'] as $item) {
                // Skip items that are no longer str_selection_set_approval_failed
                if (empty($item['selected'])) {
                    continue;
                }

                $item_id = $item['id'];

                // Check if item is not already present in $collection['items']
                if (!isset($collection['items'][$item_id])) {
                    $collection['items'][$item_id] = [
                        'id' => $item_id,
                        'name' => $item['name'],
                        'thumbnail' => Helpers::get_default_icon(Helpers::get_mimetype(pathinfo($item['name'], PATHINFO_EXTENSION))),
                        'exists' => false,
                        'users' => [],
                        'labels' => [],
                    ];
                }

                // Load the node if possible
                if (empty($nodes[$item_id])) {
                    $nodes[$item_id] = Client::instance()->get_entry($item_id, false);
                }

                // Load the node and update the name and thumbnail
                if (!empty($nodes[$item_id])) {
                    $collection['items'][$item_id]['name'] = $nodes[$item_id]->get_name();
                    $collection['items'][$item_id]['thumbnail'] = Client::instance()->get_thumbnail($nodes[$item_id], true, 640, 480);
                    $collection['items'][$item_id]['exists'] = true;
                }

                // Add user if not yet present in list
                if (!in_array($selection['user'], $collection['items'][$item_id]['users'])) {
                    $collection['items'][$item_id]['users'][] = $selection['user'];
                }

                // Add label if not yet present in list
                if (!empty($item['label']) && !in_array($item['label'], $collection['items'][$item_id]['labels'])) {
                    $collection['items'][$item_id]['labels'][] = $item['label'];
                }
            }
        }

        // Remove duplicates
        $collection['users'] = array_unique($collection['users'], SORT_REGULAR);

        // Remove keys from $collection['items'], and get a flattend array
        $collection['items'] = array_values($collection['items']);

        return $collection;
    }

    /**
     * Get filenames for a specific selection.
     *
     * @param array $selection - The selection data
     *
     * @return array - An array of filenames
     */
    public static function get_filenames_selection($selection)
    {
        $filenames = [];
        foreach ($selection['items'] as $item) {
            $filenames[] = $item['name'];
        }

        return $filenames;
    }

    /**
     * Get nodes for a specific selection.
     *
     * @param array $selection - The selection data
     *
     * @return array - An array of nodes
     */
    public static function get_nodes_for_selection($selection)
    {
        $nodes = [];
        $nodes_loaded = [];
        $has_parent_loaded = [];

        foreach ($selection['items'] as $item) {
            $entry_id = $item['id'];

            try {
                if (isset($nodes_loaded[$entry_id])) {
                    $nodes[$entry_id] = $nodes_loaded[$entry_id];

                    continue;
                }

                $node = Client::instance()->get_entry($item['id'], false);
                $nodes[$entry_id] = $node;

                if (!empty($node)) {
                    $parent = dirname($node->get_path_original());

                    // Load the parent folder as well, so that the following entries are already loaded
                    if (!in_array($parent, $has_parent_loaded)) {
                        $full_folder = Client::instance()->get_folder($parent, false, true, false);
                        $nodes_loaded += $full_folder->get_children();
                        $has_parent_loaded[] = $parent;
                    }
                }
            } catch (\Exception $e) {
                $nodes[$entry_id] = false;
            }
        }

        return array_filter($nodes);
    }

    /**
     * Get files per label for a specific selection.
     *
     * @param array $selection - The selection data
     *
     * @return array - An array of files per label
     */
    public static function get_files_per_label($selection)
    {
        $files_per_label = [];
        foreach ($selection['items'] as $item) {
            if (!empty($item['label'])) {
                if (!isset($files_per_label[$item['label']])) {
                    $files_per_label[$item['label']] = [];
                }

                $files_per_label[$item['label']][] = $item['name'];
            }
        }

        return $files_per_label;
    }

    /**
     * Get user information for a specific selection.
     *
     * @param array $selection - The selection data
     *
     * @return object - The user object
     */
    public static function get_user_selection($selection)
    {
        $user = get_user_by('id', $selection['user']) ?: get_user_by('email', $selection['user']);
        if (empty($user)) {
            $user = new \stdClass();
            $user->ID = 0;
            $user->display_name = $selection['user'];
            $user->first_name = '';
            $user->last_name = '';
            $user->user_email = $selection['user'];
        }

        return $user;
    }

    public function add_user_selection($email)
    {
        $user = get_user_by('email', $email);

        $identifier = $email;
        if (!empty($user)) {
            $identifier = $user->ID;
        }

        $ident = hash('sha256', $identifier.wp_salt('user'));

        $data = [
            'ident' => $ident,
            'user' => $identifier,
            'items' => [],
            'last_modified' => current_datetime()->format(\DateTime::ATOM),
            'approved' => false,
            'approval_time' => null,
            'approval_message' => null,
        ];

        $post_meta = get_post_meta($this->module_id, self::$post_meta_key.'_'.$ident, true);

        // Only add if the selection does not exist yet
        if (empty($post_meta)) {
            update_post_meta($this->module_id, self::$post_meta_key.'_'.$ident, $data);
        }

        return $this->get_selection($this->module_id, $ident);
    }

    /**
     * Save a selection for the current module and selection ident.
     *
     * @param array $sanitized_post_data - The sanitized post data
     *
     * @return array - The saved selection data
     */
    public function save_selection($sanitized_post_data)
    {
        // Get current selection
        $data = array_merge($this->get_selection(), $sanitized_post_data);

        if (empty($data['items'])) {
            $data['items'] = new \stdClass();
        }

        // Clear empty items from the selection
        foreach ($data['items'] as $key => $item) {
            if (empty($item['selected']) && empty($item['label'])) {
                unset($data['items'][$key]);
            }
        }

        // Truncate selection when exceeding maximum number of items
        $max_items_allowed = Processor::instance()->get_shortcode_option('proofing_max_items');
        if (!empty($data['items']) && $max_items_allowed > 0) {
            $data['items'] = array_slice($data['items'], 0, $max_items_allowed, true);
        }

        $data = apply_filters('outofthebox_proof_save_selection', $data, $this->module_id, $this->ident);

        // Make sure the array keys are numeric, so JS can handle it as an array of objects
        $data['items'] = array_values($data['items']);

        // Store the new selection in the post meta
        update_post_meta($this->module_id, self::$post_meta_key.'_'.$this->ident, $data);

        return $this->get_selection();
    }

    /**
     * Delete a selection for the current module and selection ident.
     *
     * @return bool - True if the selection was deleted, false otherwise
     */
    public function delete_selection()
    {
        do_action('outofthebox_log_event', 'outofthebox_proof_collection_deleted', null, ['module_id' => $this->module_id]);

        return delete_post_meta($this->module_id, self::$post_meta_key.'_'.$this->ident);
    }

    /**
     * Approve a selection for the current module.
     *
     * @param array $selection - The selection data
     */
    public function approve_selection($selection)
    {
        $nodes = $this->get_nodes_for_selection($selection);

        $this->send_approval_email($selection, $nodes);

        do_action('outofthebox_log_event', 'outofthebox_proof_collection_approved', null, ['module_id' => $this->module_id, 'approved' => $selection['approved']]);

        do_action('outofthebox_proof_approve_selection', $selection, $this->module_id);
    }

    /**
     * Download proof for the current module and selection.
     *
     * @param array $selection - The selection data
     * @param bool  $save_file - Whether to save the file or not
     *
     * @return null|string - The file path if saved, null otherwise
     */
    public function download_proof($selection, $save_file = false)
    {
        if (empty($selection)) {
            exit(esc_html__('Selection not found', 'wpcloudplugins'));
        }

        // Get Title
        $title = '# '.sprintf(__('Selection summary for "%s"', 'wpcloudplugins'), get_the_title($this->module_id));

        // User
        $user = self::get_user_selection($selection);

        // Approved
        if ($selection['approved']) {
            $approval_time = wp_date(get_option('date_format').', '.get_option('time_format'), strtotime($selection['approval_time']));
            $approved = sprintf(esc_html__('%s approved the collection on %s', 'wpcloudplugins'), $user->display_name, $approval_time);
            $approved_message = !empty($selection['approval_message']) ? "\r".__('The following comment was added on approval', 'wpcloudplugins').":\r".$selection['approval_message'] : '';
        } else {
            $last_modified_time = wp_date(get_option('date_format').', '.get_option('time_format'), strtotime($selection['last_modified']));
            $approved = sprintf(esc_html__('%s has not yet approved the collection. The latest changes are from %s', 'wpcloudplugins'), $user->display_name, $last_modified_time);
            $approved_message = '';
        }

        // Selected Files
        $filenames = self::get_filenames_selection($selection);
        $selected_files = esc_html__('Selected files', 'wpcloudplugins').":\r".implode(',', $filenames);

        // Files per label
        $files_per_label_content = '';
        foreach (self::get_files_per_label($selection) as $label_id => $files) {
            if (!empty($files)) {
                $label = $this->get_label_by_id($label_id) ?? ['title' => $label_id];
                $files_per_label_content .= "[{$label['title']}]:\r".implode(',', $files)."\r\r";
            }
        }

        $proof_file_content = <<<EOT
{$title}

* * *
{$user->display_name}
{$user->user_email}

{$approved}
{$approved_message}

{$selected_files}

{$files_per_label_content}
EOT;

        // Filter and sanitize file name
        $proof_file_name = apply_filters('outofthebox_proof_file_name', __('Selection', 'wpcploudplugins').'-'.sanitize_title(get_the_title($this->module_id)).'-'.sanitize_title($user->display_name).'.txt', $this->module_id);
        $proof_file_name = sanitize_file_name($proof_file_name);

        // Save the file
        if (true === $save_file) {
            $upload_dir = wp_get_upload_dir();
            $full_path = $upload_dir['path'].$this->module_id.'-'.$proof_file_name;

            file_put_contents($full_path, $proof_file_content);

            return $full_path;
        }

        // Open the file
        header('Content-Type: application/download');
        header('Content-Disposition: attachment; filename="'.$proof_file_name.'"');
        echo $proof_file_content;

        exit;
    }

    /**
     * Send approval email for the current module and selection.
     *
     * @param array $selection   - The selection data
     * @param array $entry_nodes - The entry nodes
     */
    public function send_approval_email($selection, $entry_nodes = [])
    {
        // Use custom placeholders, since current_user() isn't necessarily the user who owns the selection.
        add_filter('outofthebox_notification_create_placeholders', function ($placeholders, $notification) use ($selection) {
            $user = self::get_user_selection($selection);

            $placeholders['%user_name%'] = $user->display_name;
            $placeholders['%user_email%'] = $user->user_email;
            $placeholders['%user_first_name%'] = $user->first_name;
            $placeholders['%user_last_name%'] = $user->last_name;

            $approved_message = !empty($selection['approval_message']) ? "\r".__('The following comment was added on approval', 'wpcloudplugins').":\r".$selection['approval_message'] : '';

            $placeholders['%approval_message%'] = \esc_html($approved_message);

            return $placeholders;
        }, 10, 2);

        // Create attachment
        $attachment = $this->download_proof($selection, true);

        $notification = new Notification('proof_approval', $entry_nodes);
        $notification->set_attachment($attachment);
        $notification->send_notification();

        // Delete the attachment after sending the email
        if (file_exists($attachment)) {
            unlink($attachment);
        }
    }

    /**
     * Display the approved view for a specific module.
     *
     * @param \WP_Post $module - The module post object
     */
    public static function display_approved_view(\WP_Post $module)
    {
        if ($module->post_type !== Modules::$post_type) {
            return;
        }

        // Parse the attributes from the module's post content
        $attributes = Shortcodes::parse_attributes($module->post_content);

        if ('proofing' !== $attributes['mode']) {
            return;
        }

        Core::instance()->load_scripts();
        wp_enqueue_script('OutoftheBox.ProofingDashboard');

        // Fix: Dequeue other WP Cloud Plugins scripts
        wp_dequeue_script('UseyourDrive.AdminUI');
        wp_dequeue_script('LetsBox.AdminUI');
        wp_dequeue_script('ShareoneDrive.AdminUI');

        $vars = [
            'ajax_url' => OUTOFTHEBOX_ADMIN_URL,
            'admin_nonce' => wp_create_nonce('outofthebox-admin-action'),
            'str_copied_to_clipboard' => esc_html__('Copied to clipboard!', 'wpcloudplugins'),
            'str_selection_set_approval_approved_success' => esc_html__('The collection is marked as approved.', 'wpcloudplugins'),
            'str_selection_set_approval_open_success' => esc_html__('The collection has been reopened.', 'wpcloudplugins'),
            'str_selection_set_approval_failed' => esc_html__('The collection status is not modified.', 'wpcloudplugins'),
            'str_selection_deleted_success' => esc_html__('Selection successfully deleted.', 'wpcloudplugins'),
            'str_selection_deleted_failed' => esc_html__('The selection could not be deleted.', 'wpcloudplugins'),
            'str_selection_add_user_success' => esc_html__('The user has been successfully added to the collection.', 'wpcloudplugins'),
            'str_selection_add_user_failed' => esc_html__('Could not add user to collection.', 'wpcloudplugins'),
        ];

        wp_localize_script('OutoftheBox.ProofingDashboard', 'WPCloudplugin_AdminUI_vars', $vars);

        Core::instance()->load_styles();
        wp_enqueue_style('WPCloudPlugins.AdminUI');
        wp_dequeue_style('OutoftheBox');

        include_once \OUTOFTHEBOX_ROOTDIR.'/templates/admin/collections.php';
    }

    public function set_default_password_module($postarr = [])
    {
        $attributes = Shortcodes::parse_attributes($postarr['post_content']);
        if ('proofing' === $attributes['mode'] && empty($attributes['password']) && 'Yes' === Settings::get('proofing_password_by_default')) {
            $attributes['password'] = wp_generate_password(8);
            $postarr['post_content'] = Shortcodes::parse_shortcode($attributes);
        }

        return $postarr;
    }

    public function get_url_for_selection($ident = null)
    {
        $url = get_permalink($this->module_id);

        $params = [
            'module_pass' => $this->module_attributes['password'] ?? null,
        ];

        if (!empty($ident)) {
            $selection = $this->get_selection($this->module_id, $ident);
            $user = $this->get_user_selection($selection);

            $params['ident'] = $ident;
            $params['email'] = $user->user_email;
        }

        // Add the selection ident & password to the URL
        return add_query_arg($params, $url);
    }

    /**
     * Set the module ID and selection ident.
     *
     * @param int        $module_id - The module ID
     * @param null|mixed $ident
     */
    protected function set_module_and_ident($module_id, $ident = null)
    {
        $this->module_id = sanitize_key($module_id);
        if (empty($ident)) {
            if (is_user_logged_in()) {
                $identifier = get_current_user_id();
            } else {
                $identifier = 'lead-'.LeadCapture::instance()->get_lead_email();
            }

            $ident = hash('sha256', $identifier.wp_salt('user'));
        }

        $this->ident = sanitize_key($ident);

        $this->module = Modules::get_module_by_id($this->module_id);

        if (empty($this->module)) {
            return;
        }

        // Parse the attributes from the module's post content
        $this->module_attributes = Shortcodes::parse_attributes("[outofthebox module='{$this->module->ID}']", true);

        if ((int) Processor::instance()->get_shortcode_option('id') !== $this->module->ID) {
            // Load the module and set the current account in the application context
            Shortcodes::do_shortcode($this->module_attributes);
        }
    }
}
