<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       3.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox\Modules;

use TheLion\OutoftheBox\Modules;
use TheLion\OutoftheBox\Processor;
use TheLion\OutoftheBox\Restrictions;
use TheLion\OutoftheBox\User;

defined('ABSPATH') || exit;

class ItemList
{
    /**
     * Get the list of items.
     *
     * @return array the sorted list of items
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
     * Render the list of items on the page.
     *
     * @param mixed $attributes
     */
    public static function render($attributes = [])
    {
        // Get the maximum width from the shortcode options
        $maxwidth = Processor::instance()->get_shortcode_option('maxwidth');
        \ob_start();
        ?>
<div class='wpcp-module OutoftheBox wpcp-list' data-token='<?php echo Processor::instance()->get_listtoken(); ?>'>
    <div class="files-container" style="width:<?php echo $maxwidth; ?>;max-width:<?php echo $maxwidth; ?>;">
        <?php Password::render();

        // Get the list of items
        $items = self::get_list();

        // Render each item
        foreach ($items as $item) {
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
     * @param mixed $item the item to render
     */
    public static function render_item($item)
    {
        // Extract item details
        $entry_id = $item['id'];
        $account_id = $item['account_id'];
        $lastpath = Processor::instance()->get_last_path();
        $listtoken = Processor::instance()->get_listtoken();

        // Create links
        $preview_url = OUTOFTHEBOX_ADMIN_URL."?action=outofthebox-preview&account_id={$account_id}&listtoken={$listtoken}&OutoftheBoxpath=".rawurlencode($entry_id).'&lastpath='.rawurlencode($lastpath);
        $download_url = OUTOFTHEBOX_ADMIN_URL."?action=outofthebox-download&account_id={$account_id}&listtoken={$listtoken}&OutoftheBoxpath=".rawurlencode($entry_id).'&lastpath='.rawurlencode($lastpath).'&dl=1';
        $edit_url = null;

        // Adjust links for directories
        if ($item['is_dir']) {
            $download_url = null;
            $edit_url = null;
            if ('1' === Processor::instance()->get_shortcode_option('can_download_zip')) {
                $download_url = OUTOFTHEBOX_ADMIN_URL."?action=outofthebox-create-zip&type=do-zip&account_id={$account_id}&request_id={$entry_id}&files[]={$entry_id}&listtoken={$listtoken}&_ajax_nonce=".wp_create_nonce('outofthebox-create-zip');
            }
        }

        // Check user permissions and restrictions
        $download_url = (User::can_download() && !Restrictions::has_reached_download_limit($entry_id)) ? $download_url : null;
        $preview_url = ($item['has_preview'] && User::can_preview()) ? str_replace(['edit?', 'preview?'], 'view?', $preview_url) : null;

        // Set default link for filename
        $default_action = Processor::instance()->get_shortcode_option('onclick');
        $default_url = null;
        if ('edit' === $default_action && !empty($edit_url)) {
            $default_url = $edit_url;
        } elseif ('download' === $default_action && !empty($download_url)) {
            $default_url = $download_url;
        } elseif ('preview' === $default_action && !empty($preview_url)) {
            $default_url = $preview_url;
        }

        ?>
<div class="entry entry-info">
    <div class="entry-info-icon">
        <img src="<?php echo \esc_url($item['icon']); ?>" alt="">
    </div>
    <div class="entry-info-name">
        <?php if (!empty($default_url)) { ?>
        <a href="<?php echo \esc_url($default_url); ?>" rel="noopener" class="entry_link" target="_blank">
            <span>
                <?php echo \esc_html($item['name']); ?>
            </span>
        </a>
        <?php } else { ?>
        <span>
            <?php echo \esc_html($item['name']); ?>
        </span>
        <?php }?>
    </div>

    <?php if (!empty($edit_url)) { ?>
    <div class="entry-info-button" tabindex="0">
        <a class="entry_action_edit" rel="nofollow noopener" href="<?php echo \esc_url($edit_url); ?>" target="_blank">
            <i class="eva eva-edit-outline eva-lg"></i>
        </a>
    </div>
    <?php } ?>
    <?php if (!empty($preview_url)) { ?>
    <div class="entry-info-button" tabindex="0">
        <a class="entry_action_view" rel="noopener" href="<?php echo \esc_url($preview_url); ?>">
            <i class="eva eva-eye eva-lg"></i>
        </a>
    </div>
    <?php } ?>
    <?php if (!empty($download_url)) {
        $download_name = $item['is_dir'] ? $item['name'].'.zip' : $item['name'];
        ?>
    <div class="entry-info-button" tabindex="0">
        <a class="entry_action_download" rel="nofollow noopener" href="<?php echo \esc_url($download_url); ?>" download="<?php echo \esc_attr($download_name); ?>" data-name="<?php echo \esc_attr($item['name']); ?>" title="Download">
            <i class="eva eva-download eva-lg"></i>
        </a>
    </div>
    <?php } ?>

</div>
<?php
    }
}
