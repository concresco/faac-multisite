<?php

namespace TheLion\OutoftheBox\MediaPlayers;

// Exit if accessed directly.
defined('ABSPATH') || exit;

use TheLion\OutoftheBox\App;
use TheLion\OutoftheBox\Helpers;
use TheLion\OutoftheBox\Processor;
use TheLion\OutoftheBox\Settings;
use TheLion\OutoftheBox\User;

$mode = Processor::instance()->get_shortcode_option('mode');

$classes = '';
if ('0' === Processor::instance()->get_shortcode_option('playlistthumbnails')) {
    $classes .= 'no-thumbnails ';
}

if ('0' === Processor::instance()->get_shortcode_option('show_filedate')) {
    $classes .= 'no-date ';
}

if ('1' === Processor::instance()->get_shortcode_option('playlist_search')) {
    $classes .= 'has-search ';
}

$max_width = Processor::instance()->get_shortcode_option('maxwidth');
$aspect_ratio = str_replace(':', '/', Processor::instance()->get_shortcode_option('media_ratio'));
$max_height_playlist = Processor::instance()->get_shortcode_option('maxheight');
$show_playlist = Processor::instance()->get_shortcode_option('showplaylist');
$show_playlistonstart = Processor::instance()->get_shortcode_option('showplaylistonstart');
$playlist_inline = Processor::instance()->get_shortcode_option('playlistinline');
$playlist_autoplay = Processor::instance()->get_shortcode_option('playlistautoplay');
$playlist_loop = Processor::instance()->get_shortcode_option('playlistloop');
$controls = implode(',', Processor::instance()->get_shortcode_option('mediabuttons'));
$autoplay = Processor::instance()->get_shortcode_option('autoplay');

$ads_active = '1' === Processor::instance()->get_shortcode_option('ads') && !Helpers::check_user_role(Settings::get('mediaplayer_ads_hide_role'));
$ads_tag_url = OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-getads&account_id='.App::get_current_account()->get_uuid().'&listtoken='.Processor::instance()->get_listtoken();
$ads_can_skip = '1' === Processor::instance()->get_shortcode_option('ads_skipable');

$shortcode_ads_skip_after_seconds = Processor::instance()->get_shortcode_option('ads_skipable_after');
$ads_skip_after_seconds = (empty($shortcode_ads_skip_after_seconds) ? Settings::get('mediaplayer_ads_skipable_after') : $shortcode_ads_skip_after_seconds);
?><div class="wpcp__main-container wpcp__loading wpcp__<?php echo $mode; ?> <?php echo $classes; ?>" style="width:100%; max-width:<?php echo $max_width; ?>;--wpcp-data-aspect-ratio:<?php echo $aspect_ratio; ?>;" data-show-playlist="<?php echo $show_playlist; ?>" data-open-playlist="<?php echo $show_playlistonstart; ?>" data-playlist-inline="<?php echo $playlist_inline; ?>" data-playlist-autoplay="<?php echo $playlist_autoplay; ?>" data-playlist-loop="<?php echo $playlist_loop; ?>" data-controls="<?php echo $controls; ?>" data-ads-tag-url="<?php echo ($ads_active) ? $ads_tag_url : ''; ?>" data-ads-skip="<?php echo ($ads_can_skip && ((int) $ads_skip_after_seconds > -1)) ? $ads_skip_after_seconds : '-1'; ?>" data-max-height="<?php echo empty($max_height_playlist) ? 'none' : $max_height_playlist; ?>" data-aspect-ratio="<?php echo $aspect_ratio; ?>" data-zip-download="<?php echo User::can_download_zip() ? '1' : '0'; ?>">
    <div class="loading initialize"><svg class="loader-spinner" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10"></circle>
        </svg></div>
    <<?php echo $mode; ?> <?php echo ('1' === $autoplay) ? 'autoplay' : ''; ?> preload="metadata" playsinline webkit-playsinline></<?php echo $mode; ?>>
</div>
<?php if ('video' === $mode && 'responsive' !== $aspect_ratio) {?>
<style>
<?php echo '#OutoftheBox-'.Processor::instance()->get_listtoken();
    ?>.wpcp__container.wpcp__video,
<?php echo '#OutoftheBox-'.Processor::instance()->get_listtoken();
    ?>.wpcp__main-container video,
<?php echo '#OutoftheBox-'.Processor::instance()->get_listtoken();
    ?>.wpcp__main-container.wpcp__loading.wpcp__video,
<?php echo '#OutoftheBox-'.Processor::instance()->get_listtoken();

    ?>.wpcp__playlist-item-thumbnail>img {
    aspect-ratio: <?php echo $aspect_ratio;
    ?> !important;
}
</style>
<?php
}
