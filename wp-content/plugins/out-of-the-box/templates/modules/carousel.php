<?php

namespace TheLion\OutoftheBox;

defined('ABSPATH') || exit;

$show_filedate = '1' === Processor::instance()->get_shortcode_option('show_filedate');
$show_filenames = '1' === Processor::instance()->get_shortcode_option('show_filenames');
$show_descriptions = '1' === Processor::instance()->get_shortcode_option('show_descriptions');
$description_position = Processor::instance()->get_shortcode_option('description_position');

?>
<!-- Start Slider  --->
<div class="wpcp-carousel <?php echo ('inline' === $description_position) ? 'wpcp-carousel-item-description-inline' : ''; ?>">
    <!-- Main Slider Container --->
    <div id="wpcp-carousel-<?php echo Processor::instance()->get_listtoken(); ?>" class="wpcp-carousel-main-container" data-axis="<?php echo Processor::instance()->get_shortcode_option('axis'); ?>" data-gutter="<?php echo ('' === Processor::instance()->get_shortcode_option('padding')) ? Settings::get('layout_gap') : Processor::instance()->get_shortcode_option('padding'); ?>" data-nav="<?php echo Processor::instance()->get_shortcode_option('navigation_dots'); ?>" data-navigation-arrows="<?php echo Processor::instance()->get_shortcode_option('navigation_arrows'); ?>" data-items="<?php echo Processor::instance()->get_shortcode_option('slide_items'); ?>" data-center="<?php echo Processor::instance()->get_shortcode_option('slide_center'); ?>" data-auto-size="<?php echo Processor::instance()->get_shortcode_option('slide_auto_size'); ?>" data-slideBy="<?php echo Processor::instance()->get_shortcode_option('slide_by'); ?>" data-speed="<?php echo Processor::instance()->get_shortcode_option('slide_speed'); ?>" data-autoplay="<?php echo Processor::instance()->get_shortcode_option('carousel_autoplay'); ?>" data-autoplayTimeout="<?php echo Processor::instance()->get_shortcode_option('pausetime'); ?>" data-autoplayHoverPause="<?php echo Processor::instance()->get_shortcode_option('hoverpause'); ?>" data-autoplayDirection="<?php echo Processor::instance()->get_shortcode_option('direction'); ?>"></div>
    <!-- End Main Slider Container --->
    <!-- Slide Template --->
    <div class="wpcp-carousel-item-template">
        <div class="wpcp-carousel-item">
            <div class="wpcp-carousel-item-holder">
                <!-- Slide Image Preloading --->
                <div class="preloading"></div>
                <!-- Slide Image --->
                <div class="wpcp-carousel-item-bg preloading"></div>
                <!-- Slide Overlay --->
                <div class="wpcp-carousel-item-overlay"></div>
                <!-- Slide Content --->
                <div class="wpcp-carousel-item-content">
                    <div class="wpcp-carousel-item-text">
                        <!-- Slide Content Metadata--->
                        <ul class="wpcp-carousel-item-metadata"><?php if ($show_filedate) { ?>
                            <li class="wpcp-carousel-item-date">
                                <i class="eva eva-clock-outline"></i>
                                <span></span>
                            </li>

                            <?php } ?>
                            <!-- Actions --->
                            <li class="wpcp-carousel-item-actions">
                                <?php if ($show_descriptions && 'button' === $description_position) { ?>
                                <div class="entry-info-button entry-description-button" aria-expanded="false">
                                    <i class="eva eva-info-outline eva-lg"></i>
                                    <div class="tippy-content-holder">
                                        <div class="description-textbox">
                                            <div class="description-text"></div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                  }
if (User::can_share()) { ?>
                                <div class="entry-info-button entry_action_shortlink" title="<?php esc_html_e('Share', 'wpcloudplugins'); ?>" tabindex="0">
                                    <i class="eva eva-share-outline eva-lg"></i>
                                </div>
                                <?php }
if (User::can_download()) { ?>
                                <div class="entry-info-button entry_action_download" title="<?php esc_html_e('Download', 'wpcloudplugins'); ?>" tabindex="0">
                                    <a href="" class="entry_action_download" title="<?php esc_html_e('Download', 'wpcloudplugins'); ?>">
                                        <i class="eva eva-download eva-lg"></i>
                                    </a>
                                </div>
                                <?php }
if (User::can_preview()) { ?>
                                <div class="entry-info-button entry_action_view" title="<?php esc_html_e('Preview', 'wpcloudplugins'); ?>" tabindex="0">
                                    <i class="eva eva-eye-outline eva-lg"></i>
                                </div>
                                <?php } ?>
                            </li>
                            <!-- End Actions --->
                        </ul>
                        <!-- Slide Content Title --->
                        <?php if ($show_filenames) { ?>
                        <div class="wpcp-carousel-item-title"><span></span></div>
                        <?php } ?>
                        <!-- Slide Content Description --->
                        <?php if ($show_descriptions && 'button' !== $description_position) { ?>
                        <div class="wpcp-carousel-item-description"></div>
                        <?php } ?>
                    </div>
                </div>
                <!-- End Slide Content --->
            </div>
        </div>
    </div>
    <!-- End Slide Template --->
    <!-- Slider Preloading --->
    <div class="wpcp-carousel-preloading loading initialize">
        <?php
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
    <!-- End Slider Preloading --->
</div>
<style>
  <?php if ('horizontal' === Processor::instance()->get_shortcode_option('axis')) {?>
  #OutoftheBox-<?php echo Processor::instance()->get_listtoken(); ?> .wpcp-carousel-item {
    height:<?php echo Processor::instance()->get_shortcode_option('slide_height'); ?>;
  }
  <?php } elseif ('vertical' === Processor::instance()->get_shortcode_option('axis') && '0' === Processor::instance()->get_shortcode_option('slide_auto_size')) {?>
    #OutoftheBox-<?php echo Processor::instance()->get_listtoken(); ?> .wpcp-carousel-item {
      height:<?php echo Processor::instance()->get_shortcode_option('slide_height'); ?>;
    }
  <?php } ?>
    #OutoftheBox-<?php echo Processor::instance()->get_listtoken(); ?> .wpcp-carousel:not(.wpcp-carousel-loaded) {
      height:<?php echo Processor::instance()->get_shortcode_option('slide_height'); ?>;
    }
    <?php if ('' !== Processor::instance()->get_shortcode_option('border_radius')) {?>
  #OutoftheBox-<?php echo Processor::instance()->get_listtoken(); ?> .wpcp-carousel-item-holder {
    border-radius: <?php echo Processor::instance()->get_shortcode_option('border_radius'); ?>px !important;
  }
  <?php
    }
?>
</style>
<!-- End Slider  --->