<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       3.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

defined('ABSPATH') || exit;

class Modules
{
    public static $post_type = 'wpcp_dropbox_module';

    /**
     * The single instance of the class.
     *
     * @var Modules
     */
    protected static $_instance;

    /**
     * Constructor.
     * Adds actions for creating post type and handling AJAX requests.
     */
    public function __construct()
    {
        // Register custom post type
        add_action('init', [$this, 'create_wpcp_dropbox_module_post_type']);

        // Register custom permanent slug
        add_action('admin_init', [$this, 'slug_settings']);
        add_filter('previous_post_link', [$this, 'disable_post_navigation'], 10, 5);
        add_filter('next_post_link', [$this, 'disable_post_navigation'], 10, 5);
        add_filter('get_previous_post_where', [$this, 'disable_adjacent_post_navigation'], 10, 5);
        add_filter('get_next_post_where', [$this, 'disable_adjacent_post_navigation'], 10, 5);

        // Remove  modules from sitemaps
        add_filter('wp_sitemaps_post_types', function ($post_types) {
            unset($post_types[self::$post_type]);

            return $post_types;
        });

        // Disable loading of module pages on the frontend
        add_filter('template_include', [$this, 'disable_module_frontend'], 10, 1);

        // Rendering of module
        add_filter('the_content', [$this, 'modify_post_content'], 10, 1);
        add_filter('protected_title_format', [$this, 'remove_protected_prefix'], 10, 2);

        // Custom title for the module post type
        add_action('admin_head', [$this, 'customize_edit_screen']);

        // Handle AJAX requests for saving and duplicating modules
        add_action('wp_ajax_outofthebox-save-module', [$this, 'save_module']);
        add_action('wp_ajax_outofthebox-duplicate-module', [$this, 'duplicate_module']);
        add_action('wp_ajax_outofthebox-get-module-location', [$this, 'get_path_for_modules']);

        // Load indivual modules classes
        Proofing::instance();
    }

    /**
     * Get the single instance of the class.
     *
     * @return Modules - Modules instance
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Create custom post type for modules.
     */
    public function create_wpcp_dropbox_module_post_type()
    {
        $labels = [
            'name' => __('Modules', 'wpcloudplugins'),
            'singular_name' => __('Module', 'wpcloudplugins'),
            'menu_name' => __('Modules', 'wpcloudplugins'),
            'name_admin_bar' => __('Module', 'wpcloudplugins'),
            'add_new' => __('Add New', 'wpcloudplugins'),
            'add_new_item' => __('Add New Module', 'wpcloudplugins'),
            'new_item' => __('New Module', 'wpcloudplugins'),
            'edit_item' => __('Edit Module', 'wpcloudplugins'),
            'view_item' => __('View Module', 'wpcloudplugins'),
            'all_items' => __('All Modules', 'wpcloudplugins'),
            'search_items' => __('Search Modules', 'wpcloudplugins'),
            'not_found' => __('No modules found.', 'wpcloudplugins'),
            'not_found_in_trash' => __('No modules found in Trash.', 'wpcloudplugins'),
        ];

        $args = [
            'labels' => $labels,
            'public' => false, // Not publicly visible
            'publicly_queryable' => true, // Can be accessed via direct URL
            'exclude_from_search' => true,  // Hidden from search
            'show_ui' => true, // Visible in admin
            'show_in_menu' => false, // Not in menus
            'show_in_rest' => false, // Not in REST API
            'query_var' => true,
            'rewrite' => [
                'slug' => self::get_module_base_slug(),
                'with_front' => false,
                'pages' => false,
            ],
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => false,
            'delete_with_user' => false,
            'capabilities' => [
                'create_posts' => 'do_not_allow', // Disable the "Add New" button
            ],
            'map_meta_cap' => true, // Required for custom capabilities to take effect
        ];

        register_post_type(self::$post_type, $args);
    }

    /**
     * Add slug setting & handle update.
     */
    public function slug_settings()
    {
        // translators: Slug base label
        add_settings_field(self::$post_type.'_slug', 'WP Cloud Plugins (Dropbox)', [$this, 'module_slug_output'], 'permalink', 'optional');

        // Update collection slug option
        if (isset($_POST['permalink_structure']) && !empty($_POST[self::$post_type.'_slug']) && current_user_can('manage_options')) {
            // Trim spaces and ensure it's a valid string
            $new_slug = trim(sanitize_text_field($_POST[self::$post_type.'_slug']));

            // Allow only alphanumeric characters, dashes, slashes, and underscores
            $new_slug = preg_replace('#[^a-zA-Z0-9/_-]#', '', $new_slug);

            // Prevent leading or trailing slashes to avoid accidental issues
            $new_slug = trim($new_slug, '/');

            update_option(self::$post_type.'_slug', $new_slug);

            // Flush rewrite rules
            delete_option('rewrite_rules');
        }
    }

    /**
     * Display slug settings.
     *
     * Find it under "Settings > Permalinks > Optional"
     */
    public function module_slug_output()
    {
        // Load slug option
        $slug = self::get_module_base_slug();

        ?><input name="<?php echo self::$post_type; ?>_slug" type="text" class="regular-text code" value="<?php echo esc_attr($slug); ?>" placeholder="modules/d" /><?php

    }

    /**
     * Get the module slug.
     */
    public static function get_module_base_slug()
    {
        // Load slug from options
        $slug = get_option(self::$post_type.'_slug');

        // Fallback on default
        if (empty($slug)) {
            $slug = 'modules/d';
        }

        return $slug;
    }

    public function disable_module_frontend($template)
    {
        if (!is_singular(self::$post_type)) {
            return $template;
        }

        // Make the default present not accessible via the front-end
        if (get_query_var('name') === self::$post_type.'-default-present') {
            status_header(404);
            nocache_headers();

            include get_404_template();

            exit;
        }

        return $template;
    }

    public function modify_post_content($content)
    {
        if (!is_singular(self::$post_type)) {
            return $content;
        }

        return '[outofthebox module="'.get_the_ID().'"]';
    }

    /**
     * Remove "Proteced" prefix from password protected collection title.
     *
     * @param string Text displayed before the post title; default 'Protected: %s'
     * @param object The collection post object
     * @param mixed $title_text
     * @param mixed $post
     *
     * @return string The filtered title text
     */
    public function remove_protected_prefix($title_text, $post)
    {
        if (self::$post_type == $post->post_type) {
            return '%s';
        }

        return $title_text;
    }

    public function disable_post_navigation($output, $format, $link, $post, $adjacent)
    {
        if (\is_a($post, 'WP_Post') && $post->post_type == self::$post_type) {
            return '';
        }

        return $output;
    }

    public function disable_adjacent_post_navigation($where, $in_same_term, $excluded_terms, $taxonomy, $post)
    {
        if (self::$post_type === $post->post_type) {
            if (false !== strpos($where, 'WHERE')) {
                return 'WHERE 1=0'; // Prevents SQL from returning a post
            }

            return 'AND 1=0'; // Prevents SQL from returning a post
        }

        return $where;
    }

    public static function generate_module_slug($title)
    {
        if ('Yes' === Settings::get('modules_random_slug')) {
            // Generate a random alphanumeric string
            $random_string = wp_generate_password(16, false, false);

            // Ensure the slug is unique
            return wp_unique_post_slug($random_string, 0, 'publish', self::$post_type, 0);
        }

        return wp_unique_post_slug($title, 0, 'publish', self::$post_type, 0);
    }

    /**
     * Replace regular "Edit Module" title with actual title.
     */
    public function customize_edit_screen()
    {
        global $post, $title, $action;

        if (empty($post) || empty($action)) {
            return;
        }

        if ('edit' == $action && $post->post_type == self::$post_type) {
            if (!empty($post->post_title)) {
                $title = __('(no title)', 'wpcloudplugins');
                $title = get_the_title($post->ID);
            }
        }
    }

    /**
     * Get predefined module templates.
     *
     * @return array - List of module templates
     */
    public static function get_modules_templates()
    {
        return [
            'files' => [
                'title' => esc_html__('File Browser', 'wpcloudplugins'),
                'description' => esc_html__('Let people browse a specific folder', 'wpcloudplugins'),
                'color' => 'bg-module-color-filebrowser',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />',
            ],
            'gallery' => [
                'title' => esc_html__('Gallery', 'wpcloudplugins'),
                'imagesrc' => '',
                'description' => esc_html__('Image and video gallery', 'wpcloudplugins'),
                'color' => 'bg-module-color-gallery',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />',
                'extensions' => ['gif', 'jpg', 'jpeg', 'png', 'bmp', 'cr2', 'crw', 'raw', 'tif', 'tiff', 'webp', 'heic', 'dng', 'mp4', 'm4v', 'ogg', 'ogv', 'webmv'],
            ],
            'carousel' => [
                'title' => esc_html__('Slider / Carousel', 'wpcloudplugins'),
                'imagesrc' => '',
                'description' => esc_html__('Slider for displaying images', 'wpcloudplugins'),
                'color' => 'bg-module-color-carousel',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878m0 0a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122" />',
                'extensions' => ['gif', 'jpg', 'jpeg', 'png', 'bmp', 'cr2', 'crw', 'raw', 'tif', 'tiff', 'webp', 'heic'],
            ],
            'audio' => [
                'title' => esc_html__('Audio Player', 'wpcloudplugins'),
                'imagesrc' => '',
                'description' => esc_html__('Play your music', 'wpcloudplugins'),
                'color' => 'bg-module-color-audio',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />',
                'extensions' => ['mp3', 'm4a', 'ogg', 'oga', 'wav'],
            ],
            'video' => [
                'title' => esc_html__('Video Player', 'wpcloudplugins'),
                'imagesrc' => '',
                'description' => esc_html__('Stream your video files', 'wpcloudplugins'),
                'color' => 'bg-module-color-video',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-3.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-1.5A1.125 1.125 0 0 1 18 18.375M20.625 4.5H3.375m17.25 0c.621 0 1.125.504 1.125 1.125M20.625 4.5h-1.5C18.504 4.5 18 5.004 18 5.625m3.75 0v1.5c0 .621-.504 1.125-1.125 1.125M3.375 4.5c-.621 0-1.125.504-1.125 1.125M3.375 4.5h1.5C5.496 4.5 6 5.004 6 5.625m-3.75 0v1.5c0 .621.504 1.125 1.125 1.125m0 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m1.5-3.75C5.496 8.25 6 7.746 6 7.125v-1.5M4.875 8.25C5.496 8.25 6 8.754 6 9.375v1.5m0-5.25v5.25m0-5.25C6 5.004 6.504 4.5 7.125 4.5h9.75c.621 0 1.125.504 1.125 1.125m1.125 2.625h1.5m-1.5 0A1.125 1.125 0 0 1 18 7.125v-1.5m1.125 2.625c-.621 0-1.125.504-1.125 1.125v1.5m2.625-2.625c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125M18 5.625v5.25M7.125 12h9.75m-9.75 0A1.125 1.125 0 0 1 6 10.875M7.125 12C6.504 12 6 12.504 6 13.125m0-2.25C6 11.496 5.496 12 4.875 12M18 10.875c0 .621-.504 1.125-1.125 1.125M18 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m-12 5.25v-5.25m0 5.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125m-12 0v-1.5c0-.621-.504-1.125-1.125-1.125M18 18.375v-5.25m0 5.25v-1.5c0-.621.504-1.125 1.125-1.125M18 13.125v1.5c0 .621.504 1.125 1.125 1.125M18 13.125c0-.621.504-1.125 1.125-1.125M6 13.125v1.5c0 .621-.504 1.125-1.125 1.125M6 13.125C6 12.504 5.496 12 4.875 12m-1.5 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M19.125 12h1.5m0 0c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h1.5m14.25 0h1.5" />',
                'extensions' => ['mp4', 'm4v', 'ogg', 'ogv', 'webm', 'webmv'],
            ],
            'proofing' => [
                'title' => esc_html__('Review & Approve', 'wpcloudplugins'),
                'imagesrc' => '',
                'description' => esc_html__('Review, select, and confirm files', 'wpcloudplugins'),
                'color' => 'bg-module-color-proofing',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12" />',
            ],
            'upload' => [
                'title' => esc_html__('Upload Box', 'wpcloudplugins'),
                'imagesrc' => '',
                'description' => esc_html__('Let people upload content', 'wpcloudplugins'),
                'color' => 'bg-module-color-upload',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />',
            ],
            'search' => [
                'title' => esc_html__('Search Box', 'wpcloudplugins'),
                'imagesrc' => '',
                'description' => esc_html__('Search box for files and content', 'wpcloudplugins'),
                'color' => 'bg-module-color-search',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />',
            ],
            'embed' => [
                'title' => esc_html__('Embed Documents', 'wpcloudplugins'),
                'imagesrc' => '',
                'description' => esc_html__('Embed documents directly into your page', 'wpcloudplugins'),
                'color' => 'bg-module-color-embed',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 0 1-1.125-1.125v-3.75ZM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-2.25Z" />',
            ],
            'list' => [
                'title' => esc_html__('List', 'wpcloudplugins'),
                'imagesrc' => '',
                'description' => esc_html__('Add a list of file download/view links', 'wpcloudplugins'),
                'color' => 'bg-module-color-list',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />',
            ],
            'button' => [
                'title' => esc_html__('Button', 'wpcloudplugins'),
                'imagesrc' => '',
                'description' => esc_html__('Add a button to download a file', 'wpcloudplugins'),
                'color' => 'bg-module-color-button',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" />',
            ],
        ];
    }

    /**
     * Render the module.
     * Adds actions for enqueuing scripts and styles.
     */
    public function render()
    {
        // Enqueue scripts and styles
        add_action('wp_print_scripts', [$this, 'enqueue_scripts'], 1000);
        add_action('wp_print_styles', [$this, 'enqueue_styles'], 1000);

        // Load core scripts and styles
        Core::instance()->load_scripts();
        Core::instance()->load_styles();

        // Include the admin template
        include_once OUTOFTHEBOX_ROOTDIR.'/templates/admin/modules.php';
    }

    /**
     * Enqueue necessary scripts for the module.
     */
    public function enqueue_scripts()
    {
        // Add own styles and script and remove default ones
        global $wp_scripts;
        $wp_scripts->queue = [];

        wp_enqueue_script('jquery-effects-fade');
        wp_enqueue_script('OutoftheBox');
        wp_enqueue_script('OutoftheBox.AdminUI');
        wp_enqueue_script('OutoftheBox.ShortcodeBuilder');

        // Build Whitelist for permission selection
        $vars = [
            'whitelist' => json_encode(Helpers::get_all_users_and_roles()),
            'ajax_url' => OUTOFTHEBOX_ADMIN_URL,
            'admin_nonce' => wp_create_nonce('outofthebox-admin-action'),
            'module_nonce' => \wp_create_nonce('outofthebox-module-preview'),
        ];

        wp_localize_script('OutoftheBox.AdminUI', 'WPCloudplugin_AdminUI_vars', $vars);
    }

    /**
     * Enqueue necessary styles for the module.
     */
    public function enqueue_styles()
    {
        global $wp_styles;
        $wp_styles->queue = [];
        wp_enqueue_style('OutoftheBox');
        wp_enqueue_style('WPCloudPlugins.AdminUI');
    }

    /**
     * Create a new module.
     *
     * @param array $post - Post data for the new module
     *
     * @return WP_Post - The created module post
     */
    public static function create_module($post = [])
    {
        // First check if a module with the same configuration already exists
        if (!empty($post['post_content'])) {
            global $wpdb;

            // Query the database for posts with the same post_content
            $query = $wpdb->prepare("
                SELECT ID
                FROM `{$wpdb->posts}`
                WHERE post_content = %s
                AND post_type = %s
            ", $post['post_content'], self::$post_type);

            // Execute the query
            $posts = $wpdb->get_results($query);

            if (!empty($posts)) {
                return self::get_module_by_id($posts[0]->ID);
            }
        } else {
            // If no shortcode is provided, use the shortcode from the default present
            $default_module = self::get_default_module();

            if (empty($_GET['mode'])) {
                $post['post_content'] = $default_module->post_content;
            } else {
                $post['post_content'] = \preg_replace('/mode="[^"]*"/', 'mode="'.\sanitize_key($_GET['mode']).'"', $default_module->post_content);
            }
        }

        $post_default = [
            'post_type' => self::$post_type,
            'post_name' => self::generate_module_slug(\sanitize_title('module-%ID%')),
            'post_title' => \sanitize_text_field(__('Module', 'wpcloudplugins').' %ID%'),
            'post_content' => '',
            'post_status' => 'publish',
        ];

        $post = apply_filters('outofthebox_create_module_postarr', $post);

        $post = array_merge($post_default, $post);

        $module_id = wp_insert_post($post);

        // Add the ID to the post_name and post_title
        $post_name = \str_replace('%ID%', $module_id, $post['post_name']);
        $post_title = \str_replace('%ID%', '#'.$module_id, $post['post_title']);
        \wp_update_post([
            'ID' => $module_id,
            'post_name' => $post_name,
            'post_title' => $post_title,
        ]);

        return self::get_module_by_id($module_id);
    }

    /**
     * Duplicate an existing module.
     * Handles AJAX request for duplicating a module.
     */
    public static function duplicate_module()
    {
        check_ajax_referer('outofthebox-admin-action');

        if (
            !Helpers::check_user_role(Settings::get('permissions_add_shortcodes'))
        ) {
            wp_send_json_error(__('You do not have permission to save modules.', 'wpcloudplugins'));

            exit;
        }

        $post_data = array_filter(filter_var_array($_REQUEST['post'] ?? [], [
            'ID' => FILTER_VALIDATE_INT,
        ]));

        if (empty($post_data['ID'])) {
            \wp_send_json_error(__('Module ID is missing.', 'wpcloudplugins'));

            exit;
        }

        $original_module = self::get_module_by_id($post_data['ID']);
        if (empty($original_module)) {
            \wp_send_json_error(__('Module cannot be found.', 'wpcloudplugins'));
        }

        $new_post_data = [
            'post_type' => self::$post_type,
            'post_name' => self::generate_module_slug(\sanitize_title('module-%ID%')),
            'post_title' => $original_module->post_title.' - '.__('Copy', 'wpcloudplugins'),
            'post_content' => $original_module->post_content,
            'post_status' => 'publish',
        ];

        $new_post_data = apply_filters('outofthebox_create_module_postarr', $new_post_data);

        $module_id = wp_insert_post($new_post_data);

        // Add the ID to the post_name and post_title
        $post_name = \str_replace('%ID%', $module_id, $new_post_data['post_name']);
        \wp_update_post([
            'ID' => $module_id,
            'post_name' => $post_name,
        ]);

        \wp_send_json_success(self::get_module_by_id($module_id));

        exit;
    }

    /**
     * Save a module.
     * Handles AJAX request for saving a module.
     */
    public static function save_module()
    {
        check_ajax_referer('outofthebox-admin-action');

        if (
            !Helpers::check_user_role(Settings::get('permissions_add_shortcodes'))
        ) {
            wp_send_json_error(__('You do not have permission to save modules.', 'wpcloudplugins'));

            exit;
        }

        $post_data = array_filter(filter_var_array($_REQUEST['post'] ?? [], [
            'ID' => FILTER_VALIDATE_INT,
            'post_title' => FILTER_SANITIZE_SPECIAL_CHARS,
            'post_content' => FILTER_FLAG_NO_ENCODE_QUOTES,
            'post_status' => FILTER_SANITIZE_ENCODED,
        ]));

        if (empty($post_data['ID'])) {
            \wp_send_json_error(__('Module ID is missing.', 'wpcloudplugins'));

            exit;
        }

        $module = self::get_module_by_id($post_data['ID']);
        if (empty($module)) {
            \wp_send_json_error(__('Module cannot be found.', 'wpcloudplugins'));
        }

        if (!empty($post_data['post_content'])) {
            $post_data['post_content'] = wp_unslash($post_data['post_content']);
        }

        // Update the module with the new data
        wp_update_post($post_data);

        // Store individual items as post metadata
        delete_post_meta($post_data['ID'], 'items-last-updated');

        \wp_send_json_success(self::get_module_by_id($post_data['ID']));

        exit;
    }

    /**
     * Get items associated with a module.
     *
     * @param int $post_id - Module post ID
     *
     * @return array - List of items
     */
    public static function get_items($post_id)
    {
        self::update_items($post_id);

        $items = get_post_meta($post_id, 'items', true);

        if (empty($items)) {
            return [];
        }

        return $items;
    }

    /**
     * Update items associated with a module.
     *
     * @param int  $post_id - Module post ID
     * @param bool $force   - Force update
     */
    public static function update_items($post_id, $force = false)
    {
        $last_updated = get_post_meta($post_id, 'items-last-updated', true);

        if ($force || $last_updated < strtotime('-1 day')) {
            self::save_items($post_id);
        }
    }

    /**
     * Save items associated with a module.
     *
     * @param int $post_id - Module post ID
     */
    public static function save_items($post_id)
    {
        $module = self::get_module_by_id($post_id);

        if (empty($module->post_content)) {
            update_post_meta($post_id, 'items', []);
            update_post_meta($post_id, 'items-last-updated', time());

            return;
        }

        // Current items
        $current_items = get_post_meta($post_id, 'items', true);

        // Parse module shortcode
        $shortcode_attr = Shortcodes::parse_attributes($module->post_content);

        if (isset($shortcode_attr['items'])) {
            // Set the account
            $account = Accounts::instance()->get_account_by_id($shortcode_attr['account']);
            App::set_current_account($account);

            $items = explode('|', $shortcode_attr['items']);
            $list = [];

            if (empty($items)) {
                return;
            }

            // Build the list with entries
            foreach ($items as $item_id) {
                $entry = Client::instance()->get_entry($item_id, false);

                if (empty($entry)) {
                    continue;
                }

                $list[$item_id] = [
                    'id' => $entry->get_id(),
                    'account_id' => $account->get_id(),
                    'name' => $entry->get_name(),
                    'basename' => $entry->get_basename(),
                    'description' => $entry->get_description(),
                    'is_dir' => $entry->is_dir(),
                    'size' => $entry->get_size(),
                    'extension' => $entry->get_extension(),
                    'mimetype' => $entry->get_mimetype(),
                    'icon' => $entry->get_icon_large(),
                    'last_edited' => $entry->get_last_edited(),
                    'last_edited_str' => $entry->get_last_edited_str(),
                    'has_preview' => $entry->get_can_preview_by_cloud(),
                    'is_editable' => $entry->get_can_edit_by_cloud(),
                ];

                // Embed module: Add the embed URL
                if ('embed' === $shortcode_attr['mode']) {
                    // Skip directories
                    if ($entry->is_dir()) {
                        unset($list[$item_id]);

                        continue;
                    }

                    // Don't update existing embedded urls
                    if (is_array($current_items) && isset($current_items[$item_id]) && !empty($current_items[$item_id]['embed_url'])) {
                        $list[$item_id]['embed_url'] = $current_items[$item_id]['embed_url'];
                    } else {
                        // Get embedded urls and update sharing permissions if needed
                        $embed_type = $shortcode_attr['embed_type'] ?? 'readonly';

                        if ('readonly' !== $embed_type && $entry->get_can_edit_by_cloud() && ($editurl = API::create_edit_url($entry->get_id())) !== false) {
                            $list[$item_id]['embed_url'] = $editurl;
                        } else {
                            $list[$item_id]['embed_url'] = Client::instance()->get_embedded_link($entry);
                        }
                    }
                }
            }

            update_post_meta($post_id, 'items', $list);
            update_post_meta($post_id, 'items-last-updated', time());
        }
    }

    /**
     * Get all modules.
     *
     * @return array - List of modules
     */
    public static function get_modules()
    {
        $args = [
            'post_type' => self::$post_type,
            'posts_per_page' => -1,
        ];

        $query = new \WP_Query($args);

        $modules = [];

        if (empty($query->posts)) {
            return $modules;
        }

        foreach ($query->posts as $module) {
            // Do not include the default present module
            if ($module->post_name === self::$post_type.'-default-present') {
                continue;
            }

            // Ensure that the module has an unique slug if the setting is enabled
            if ('Yes' === Settings::get('modules_random_slug') && 16 !== strlen($module->post_name)) {
                wp_update_post(['ID' => $module->ID, 'post_name' => self::generate_module_slug(\sanitize_title('module-%ID%'))]);
            }

            $attributes = Shortcodes::parse_attributes($module->post_content);

            $t_time = get_the_modified_time('Y/m/d g:i:s a', $module);
            $m_time = $module->post_modified;
            $time = get_post_modified_time('G', true, $module);

            if (abs(time() - $time) < DAY_IN_SECONDS) {
                $h_time = sprintf(__('Modified %s ago', 'wpcloudplugins'), human_time_diff($time));
            } else {
                $h_time = mysql2date('Y/m/d', $m_time);
            }

            $module_attr = [
                'id' => $module->ID,
                'account' => isset($attributes['account']) ? Accounts::instance()->get_account_by_id($attributes['account']) : '',
                'dir' => isset($attributes['dir']) ? $attributes['dir'] : '',
                'path' => isset($attributes['dir']) ? $attributes['dir'] : 'Dropbox',
                'title' => $module->post_title,
                'author' => get_the_author_meta('display_name', $module->post_author),
                'type' => isset($attributes['mode']) ? $attributes['mode'] : '',
                'shortcode' => Shortcodes::parse_shortcode(['module' => $module->ID]),
                'date' => $t_time,
                'date_str' => $h_time,
                'active' => 'publish' === get_post_status($module->ID),
                'view_url' => get_permalink($module->ID),
                'edit_url' => get_edit_post_link($module->ID),
                'delete_url' => get_delete_post_link($module->ID, '', true),
            ];

            if (isset($attributes['userfolders'])) {
                if ('manual' === $attributes['userfolders']) {
                    $module_attr['path'] = esc_html__('Dynamic Folders', 'wpcloudplugins').' ('.esc_html__('Manual', 'wpcloudplugins').')';
                } elseif (!empty($module_attr['path'])) {
                    $module_attr['path'] = esc_html__('Dynamic Folders', 'wpcloudplugins').' ('.$module_attr['path'].')';
                }
            }

            $modules[$module->ID] = $module_attr;
        }

        // By default, sort the modules by the key Descending
        krsort($modules);

        return $modules;
    }

    /**
     * Retrieves the path for a module based on the provided module ID.
     *
     * This function fetches the module by its ID from the POST request, parses its attributes,
     * and determines the path of the directory specified in the module's attributes.
     */
    public static function get_path_for_modules()
    {
        // Get the module ID from the POST request and validate it as an integer
        $module_ids = filter_input(INPUT_POST, 'module_ids', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);
        $response = [];

        // Retrieve the module by its ID
        foreach ($module_ids as $module_id) {
            $module = self::get_module_by_id($module_id);

            // If the module cannot be found, return an error response
            if (empty($module)) {
                $response[] = [
                    'success' => false,
                    'data' => [
                        'module_id' => $module_id,
                        'message' => __('Module cannot be found.', 'wpcloudplugins'),
                    ],
                ];

                continue;
            }

            // Parse the attributes from the module's post content
            $attributes = Shortcodes::parse_attributes($module->post_content);

            // Get the account associated with the module, if any
            $account = isset($attributes['account']) ? Accounts::instance()->get_account_by_id($attributes['account']) : Accounts::instance()->get_primary_account();

            // If the account cannot be found, return an empty success response
            if (empty($account)) {
                $response[] = [
                    'success' => false,
                    'data' => [
                        'module_id' => $module_id,
                        'path' => $attributes['dir'] ?? '',
                        'account_email' => esc_html__('Account not connected', 'wpcloudplugins').':'.$attributes['account'],
                    ],
                ];

                continue;
            }

            // If the 'dir' attribute is not set, return an empty success response
            if (!isset($attributes['dir'])) {
                $response[] = [
                    'success' => true,
                    'data' => [
                        'module_id' => $module_id,
                        'path' => 'Dropbox',
                        'account_email' => $account->get_email(),
                    ],
                ];

                continue;
            }

            // Set the current account in the application context
            App::set_current_account($account);

            // Get the cached node entry for the directory specified in the module's attributes
            $cached_node = Client::instance()->get_entry($attributes['dir'], false);

            // Determine the location path of the directory
            $path = '';
            if (!empty($cached_node)) {
                $path = $cached_node->get_path('/');
            } else {
                $path = esc_html__('Invalid', 'wpcloudplugins').':'.$attributes['dir'];
            }

            // Is it a dynamic folder?
            if (isset($attributes['userfolders'])) {
                $path = esc_html__('Dynamic Folders', 'wpcloudplugins').' ('.$path.')';
            }

            // Return the location path as a success response
            $response[] = [
                'success' => true,
                'data' => [
                    'module_id' => $module_id,
                    'path' => $path,
                    'account_email' => $account->get_email(),
                ],
            ];
        }

        \wp_send_json([
            'success' => true,
            'data' => $response,
        ]);
    }

    /**
     * Get a module by its ID.
     *
     * @param int $module_id - Module ID
     *
     * @return false|WP_Post - The module post or false if not found
     */
    public static function get_module_by_id($module_id)
    {
        $post = \get_post($module_id);

        if (empty($post)) {
            return false;
        }

        if (self::$post_type !== $post->post_type) {
            return false;
        }

        return $post;
    }

    /**
     * Get the default module.
     *
     * @return WP_Post - The default module post
     */
    public static function get_default_module()
    {
        $post = \get_page_by_path(self::$post_type.'-default-present', OBJECT, self::$post_type);
        if (empty($post)) {
            return self::install_default_module();
        }

        return $post;
    }

    /**
     * Export all modules.
     *
     * @return null|array - List of modules or null if none found
     */
    public static function export()
    {
        global $wpdb;

        $query = $wpdb->prepare("SELECT * FROM `{$wpdb->posts}` WHERE post_type = %s", self::$post_type);

        // Execute the query
        $results = $wpdb->get_results($query);

        if (empty($results)) {
            return null;
        }

        return $results;
    }

    /**
     * Install the default module.
     *
     * @return WP_Post - The installed default module post
     */
    public static function install_default_module()
    {
        // Create default module present
        return self::create_module([
            'post_name' => self::$post_type.'-default-present',
            'post_title' => __('### Default Module ###', 'wpcloudplugins'),
            'post_content' => Shortcodes::parse_shortcode([
                'mode' => 'files',
                'viewrole' => 'all',
                'downrole' => 'all',
                'status' => 'draft',
            ]),
        ]);
    }

    /**
     * Uninstall all modules.
     */
    public static function uninstall()
    {
        global $wpdb;

        $query = $wpdb->prepare("DELETE FROM `{$wpdb->posts}` WHERE post_type = %s", self::$post_type);

        // Execute the query
        $wpdb->query($query);
    }
}
