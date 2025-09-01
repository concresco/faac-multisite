<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

defined('ABSPATH') || exit;

?>
<div class="wpcp-browser-container wpcp-no-tree" style="width:<?php echo $shortcode['maxwidth']; ?>;max-width:<?php echo $shortcode['maxwidth']; ?>;<?php echo (!empty($shortcode['maxheight'])) ? 'max-height:'.$shortcode['maxheight'].';overflow: hidden;' : ''; ?>">
    <div class="wpcp-browser-container-tree" data-show-tree='<?php echo $shortcode['show_tree']; ?>'>
        <div class="wpcp-container-overlay"></div>
        <div class="nav-header">
            <a class="nav-tree-toggle entry-info-button" tabindex="0" title="<?php esc_html_e('Display folder tree', 'wpcloudplugins'); ?>">
                <i class="eva eva-arrowhead-left-outline"></i>
            </a>
        </div>
        <div class="wpcp-browser-tree"></div>
    </div>
    <div class="wpcp-browser-container-content">
        <div class="nav-header OutoftheBox" id="search-<?php echo Processor::instance()->get_listtoken(); ?>">
            <div class="search-wrapper">
                <a class="search-icon search-submit" href="javascript:;"><i class="eva eva-search"></i></a>
                <input class="search-input" name="q" type="search" autocomplete="off" size="40" aria-label="<?php esc_html_e('Search', 'wpcloudplugins'); ?>" placeholder="<?php echo esc_html__('Search for files', 'wpcloudplugins').(('1' === $shortcode['searchcontents'] && '1' === $shortcode['show_files']) ? ' '.esc_html__('and content', 'wpcloudplugins') : ''); ?>" />
                <a class="search-remove" href="javascript:;" title="<?php esc_html_e('Clear', 'wpcloudplugins'); ?>"><i class="eva eva-close"></i></a>
            </div>
        </div>
        <div class="wpcp-container-content">
            <div class="loading initialize"><?php
        $loaders = Settings::get('loaders');

switch ($loaders['style']) {
    case 'custom':
        break;

    case 'beat':
        ?>
                <div class='loader-beat'></div>
                <?php
        break;

    case 'spinner':
        ?>
                <svg class="loader-spinner" viewBox="25 25 50 50">
                    <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10"></circle>
                </svg>
                <?php
        break;
}
?>
            </div>
            <div class="ajax-filelist" style="<?php echo (!empty($shortcode['maxheight'])) ? 'max-height:'.$shortcode['maxheight'].';overflow-y: scroll;overflow-x: hidden;' : ''; ?>">&nbsp;</div>
            <div class="scroll-to-top">
                <button class="scroll-to-top-action button button-round-icon secondary button-round-icon-lg button-shadow-3" type="button" title="<?php esc_html_e('Scroll to top', 'wpcloudplugins'); ?>" aria-expanded="false"><i class="eva eva-arrow-upward-outline eva-2x"></i></button>
            </div>
            <div class="wpcp-browser-container-info">
                <div class="wpcp-container-overlay"></div>
                <div class="wpcp-info-content">
                    <div class="wpcp-info-close entry-info-button">
                        <i class="eva eva-close eva-lg" aria-hidden="true"></i>
                    </div>
                    <div class="wpcp-info-thumbnail"></div>
                    <div class="wpcp-info-list"></div>
                </div>
            </div>
        </div>
    </div>
</div>