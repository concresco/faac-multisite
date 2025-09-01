<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox\Modules;

use TheLion\OutoftheBox\App;
use TheLion\OutoftheBox\Cache;
use TheLion\OutoftheBox\CacheRequest;
use TheLion\OutoftheBox\Client;
use TheLion\OutoftheBox\Core;
use TheLion\OutoftheBox\Entry;
use TheLion\OutoftheBox\Processor;
use TheLion\OutoftheBox\Restrictions;
use TheLion\OutoftheBox\Settings;
use TheLion\OutoftheBox\User;

defined('ABSPATH') || exit;

class Carousel
{
    public static $enqueued_scripts = false;
    protected $_folder;

    public function get_images_list()
    {
        $this->_folder = Client::instance()->get_folder(null, false, true, false);

        if (false === $this->_folder) {
            return json_encode([
                'images' => [],
                'total' => 0,
            ]);
        }

        $images = $this->get_images();

        $data = [
            'images' => $images,
            'total' => count($images),
        ];

        if ($data['total'] > 0) {
            $response = json_encode($data);

            $cached_request = new CacheRequest();
            $cached_request->add_cached_response($response);

            header('Content-Type: application/json');
            echo $response;
        }

        exit;
    }

    public function get_images()
    {
        $images = [];

        foreach ($this->_folder->get_children() as $entry) {
            if ($entry->is_dir()) {
                continue;
            }

            // Check if entry has thumbnail
            if (!$entry->has_own_thumbnail()) {
                continue;
            }

            $images[] = $entry;
        }

        $images = Processor::instance()->sort_filelist($images);
        $data = [];

        if ('-1' !== Processor::instance()->get_shortcode_option('max_files')) {
            $images = array_slice($images, 0, Processor::instance()->get_shortcode_option('max_files'));
        }

        foreach ($images as $entry) {
            $cached_entry = Cache::instance()->get_node_by_id($entry->get_id());

            $download_url = User::can_download() && !Restrictions::has_reached_download_limit($entry->get_id()) ? OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-download&OutoftheBoxpath='.rawurlencode($entry->get_path()).'&lastpath='.rawurlencode(Processor::instance()->get_last_path()).'&account_id='.App::get_current_account()->get_uuid().'&listtoken='.Processor::instance()->get_listtoken().'&dl=1' : null;

            $lightbox_url = null;
            if (User::can_preview()) {
                if (
                    ('default' === Processor::instance()->get_shortcode_option('lightbox_imagesource') && 'thumbnail' === Settings::get('loadimages'))
                     || 'thumbnail' === Processor::instance()->get_shortcode_option('lightbox_imagesource')
                     || false === User::can_download()) {
                    $lightbox_url = Client::instance()->get_thumbnail($entry, true, 1024, 768);
                } else {
                    $lightbox_url = $download_url;
                }
            }

            $data[] = [
                'id' => $entry->get_id(),
                'name' => htmlspecialchars($entry->get_basename(), ENT_COMPAT | ENT_HTML401 | ENT_QUOTES, 'UTF-8'),
                'width' => empty($cached_entry) ? null : $cached_entry->get_media_info('width'),
                'height' => empty($cached_entry) ? null : $cached_entry->get_media_info('height'),
                'last_edited_time' => $entry->get_last_edited(),
                'last_edited_time_str' => $entry->get_last_edited_str(),
                'url' => Client::instance()->get_thumbnail($entry, true, 1024, 768),
                'description' => htmlentities(nl2br($entry->get_description(), ENT_QUOTES | ENT_HTML401)),
                'preloaded' => false,
                'download_url' => $download_url,
                'lightbox_link' => $lightbox_url,
            ];
        }

        return $data;
    }

    public static function render($attributes = [])
    {
        self::enqueue_scripts();

        $shortcode = Processor::instance()->get_shortcode();

        $attributes += [
            'data-list' => 'carousel',
            'data-query' => $shortcode['searchterm'],
            'data-lightboxnav' => $shortcode['lightbox_navigation'],
            'data-lightboxthumbs' => $shortcode['lightbox_thumbnails'],
            'data-lightboxopen' => $shortcode['lightbox_open'],
            'data-slideshow' => $shortcode['slideshow'],
            'data-pausetime' => $shortcode['pausetime'],
        ];

        echo "<div class='wpcp-module OutoftheBox carousel jsdisabled' ".Module::parse_attributes($attributes).'>';

        Password::render();

        include sprintf('%s/templates/modules/carousel.php', OUTOFTHEBOX_ROOTDIR);

        echo '</div>';
    }

    public static function enqueue_scripts()
    {
        if (true === self::$enqueued_scripts) {
            return;
        }

        Core::instance()->load_scripts();
        Core::instance()->load_styles();

        wp_enqueue_script('OutoftheBox.Carousel');
        wp_enqueue_style('ilightbox');
        wp_enqueue_style('ilightbox-skin-outofthebox');

        self::$enqueued_scripts = true;
    }
}
