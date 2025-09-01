<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       3.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox\Modules;

use TheLion\OutoftheBox\App;
use TheLion\OutoftheBox\Modules;
use TheLion\OutoftheBox\Processor;

defined('ABSPATH') || exit;

class Embed
{
    public static $supported_extensions = [];

    /**
     * Get the list of items to embed.
     *
     * @return array sorted list of items to embed
     */
    public static function get_list()
    {
        // Get the module ID from the shortcode options
        $module_id = Processor::instance()->get_shortcode_option('id');

        // Retrieve items for the module
        $items = Modules::get_items($module_id);

        // Return sorted list of items
        return Processor::instance()->sort_filelist($items);
    }

    /**
     * Render the embedded items on the page.
     *
     * @param mixed $attributes
     */
    public static function render($attributes = [])
    {
        // Get the maximum width from the shortcode options
        $maxwidth = Processor::instance()->get_shortcode_option('maxwidth');
        \ob_start();
        ?>
<div class='wpcp-module OutoftheBox wpcp-embed' data-token='<?php echo Processor::instance()->get_listtoken(); ?>'>
    <div style="width:<?php echo $maxwidth; ?>;max-width:<?php echo $maxwidth; ?>; position:relative;">
        <?php Password::render();

        // Get the list of items to embed
        $items = self::get_list();

        // Render each item
        foreach ($items as $item) {
            if ($item['is_dir']) {
                continue;
            }

            self::render_item($item);
        }
        ?>
    </div>
</div>
<?php

        echo \ob_get_clean();
    }

    /**
     * Render the item on the page.
     *
     * @param array $item item details to render
     */
    public static function render_item($item)
    {
        // Shortcode Options
        $embed_type = Processor::instance()->get_shortcode_option('embed_type');
        $embed_ratio = str_replace(':', '/', Processor::instance()->get_shortcode_option('embed_ratio'));
        $direct_embed = Processor::instance()->get_shortcode_option('embed_direct_media');
        $show_filenames = Processor::instance()->get_shortcode_option('show_filenames');

        // Item details
        $entry_id = $item['id'];
        $account_id = App::get_current_account()->get_uuid();
        $embed_url = $item['embed_url'];

        // Render Document name
        if ($show_filenames) {
            echo '<h3>'.$item['basename'].'</h3>';
        }

        // Embed Media Files Direct
        if ('1' === $direct_embed && self::is_media_file($item)) {
            echo "<div style='width:100%; max-width:100%;'>";

            if (false !== strpos($item['mimetype'], 'video/')) {
                ?><video controls preload="metadata" style="width: 100%;">
    <source src="<?php echo \esc_url($embed_url); ?>" type="<?php echo \esc_attr($item['mimetype']); ?>">
</video><?php
            } elseif (false !== strpos($item['mimetype'], 'audio/')) {
                ?><audio controls preload="metadata" style="width: 100%;">
    <source src="<?php echo \esc_url($embed_url); ?>" type="<?php echo \esc_attr($item['mimetype']); ?>">
</audio>
<?php
            } else {
                ?><img src=" <?php echo \esc_url($embed_url); ?>" loading="lazy" alt="<?php echo \esc_attr($item['basename']); ?>" referrerpolicy="no-referrer" style="max-width: 100%" title="<?php echo \esc_attr($item['basename']).'| '.$item['description']; ?>" ;><?php
            }

            echo '</div>';

            return;
        }

        if (empty($item['embed_url'])) {
            return;
        }

        ?> <iframe src=" <?php echo \esc_url($embed_url); ?>" height="480" style="width:100%;aspect-ratio:<?php echo \esc_attr($embed_ratio); ?>;height:auto;max-height:100vh;border:none;overflow: hidden;" class="oftb-embedded" allowfullscreen loading="lazy" referrerpolicy="no-referrer" title="<?php echo $item['basename']; ?>"></iframe><?php

    }

    /**
     * Check if item is a media file.
     *
     * @param array $item item details to check
     *
     * @return bool true if item is a media file, false otherwise
     */
    public static function is_media_file($item)
    {
        $extension = $item['extension'] ?? '';
        $mimetype = $item['mimetype'] ?? '';

        $allowedextensions = ['jpg', 'jpeg', 'gif', 'png', 'webp', 'heic', 'mp3', 'm4a', 'ogg', 'oga', 'wav', 'mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'webm'];
        $allowedimimetypes = ['audio/mpeg', 'audio/mp4', 'audio/ogg', 'audio/x-wav', 'video/mp4', 'video/ogg', 'video/webm'];

        if (!empty($extension) && in_array($extension, $allowedextensions)) {
            return true;
        }

        return in_array($mimetype, $allowedimimetypes);
    }
}
