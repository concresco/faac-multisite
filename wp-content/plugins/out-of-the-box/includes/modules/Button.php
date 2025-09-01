<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       3.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox\Modules;

use TheLion\OutoftheBox\Helpers;
use TheLion\OutoftheBox\Modules;
use TheLion\OutoftheBox\Processor;
use TheLion\OutoftheBox\Restrictions;
use TheLion\OutoftheBox\User;

defined('ABSPATH') || exit;

class Button
{
    /**
     * Get the list of items to embed.
     *
     * @return array list of items to embed
     */
    public static function get_list()
    {
        // Get the module ID from the shortcode options
        $module_id = Processor::instance()->get_shortcode_option('id');

        // Retrieve items for the module
        $items = Modules::get_items($module_id);

        // Sort and return the list of items
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
<div class='wpcp-module OutoftheBox wpcp-button' data-token='<?php echo Processor::instance()->get_listtoken(); ?>'>
    <div style="width:<?php echo $maxwidth; ?>;max-width:<?php echo $maxwidth; ?>; position:relative;">
        <?php Password::render();

        // Get the list of items to render
        $items = self::get_list();

        ?>
        <div class="wpcp-download-list"><?php
        // Render each item
        foreach ($items as $item) {
            self::render_item($item);
        }
        ?>
        </div>
    </div>
</div>
<?php

        echo \ob_get_clean();
    }

    /**
     * Render the item on the page.
     *
     * @param array $item item data to render
     */
    public static function render_item($item)
    {
        $entry_id = $item['id'];
        $account_id = $item['account_id'];
        $lastpath = Processor::instance()->get_last_path();
        $listtoken = Processor::instance()->get_listtoken();

        // Create download URL

        $download_url = OUTOFTHEBOX_ADMIN_URL."?action=outofthebox-download&account_id={$account_id}&listtoken={$listtoken}&OutoftheBoxpath=".rawurlencode($entry_id).'&lastpath='.rawurlencode($lastpath).'&dl=1';

        // Check if the item is a directory and if it can be downloaded as a zip
        if ($item['is_dir']) {
            $download_url = null;
            if ('1' === Processor::instance()->get_shortcode_option('can_download_zip')) {
                $download_url = OUTOFTHEBOX_ADMIN_URL."?action=outofthebox-create-zip&type=do-zip&account_id={$account_id}&request_id={$entry_id}&files[]={$entry_id}&listtoken={$listtoken}&_ajax_nonce=".wp_create_nonce('outofthebox-create-zip');
            }
        }

        // Check if the user can download and if the download limit has not been reached
        $download_url = (User::can_download() && !Restrictions::has_reached_download_limit($entry_id)) ? $download_url : null;

        $disabled = '';
        if (empty($download_url)) {
            $disabled = 'disabled';
        }

        $download_name = $item['is_dir'] ? $item['name'].'.zip' : $item['name'];

        ?>
<div class="wpcp-download-item">
    <!-- Icon -->
    <div class="wpcp-download-item-icon"><img src="<?php echo \esc_url($item['icon']); ?>" alt="" /></div>

    <!-- Content (title and description) -->
    <div class="wpcp-download-item-content">
        <h3>
            <a class="<?php echo $disabled; ?>" href="<?php echo $download_url; ?>" title="<?php echo \esc_attr($item['basename']); ?>" download="<?php echo \esc_attr($download_name); ?>" <?php echo $disabled; ?>><?php echo \esc_attr($item['name']); ?>
            </a>
        </h3>
        <?php if (!empty($item['size'])) {?>
        <p><?php echo \esc_attr(Helpers::bytes_to_size_1024($item['size'])); ?></p>
        <?php } ?>
    </div>

    <!-- Download Button -->
    <a type="button" class="button <?php echo $disabled; ?>" href="<?php echo $download_url; ?>" title="<?php echo \esc_attr($item['basename']); ?>" download="<?php echo \esc_attr($download_name); ?>" <?php echo $disabled; ?>>
        <i class='eva eva-download-outline eva-lg'></i>&nbsp;<?php echo \esc_html__('Download', 'wpcloudplugins'); ?>
    </a>
</div>
<?php
    }
}
