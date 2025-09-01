<?php

namespace TheLion\OutoftheBox\MediaPlayers;

// Exit if accessed directly.
defined('ABSPATH') || exit;

use TheLion\OutoftheBox\MediaplayerSkin;
use TheLion\OutoftheBox\Settings;

class Basic_Playlist_2020 extends MediaplayerSkin
{
    public $url;
    public $template_path = __DIR__.'/Template.php';

    public function __construct()
    {
        $this->url = plugins_url('', __FILE__);
    }

    public function load_scripts($dependency = [])
    {
        if (defined('OUTOFTHEBOX_SCRIPTS'.__CLASS__.'_LOADED')) {
            return;
        }

        if ('Yes' === Settings::get('mediaplayer_load_native_mediaelement')) {
            $dependency += ['wp-mediaelement'];
        }

        wp_register_script('Basic_Playlist.Library', $this->get_url().'/js/mediaelement-and-player.min.js', $dependency, OUTOFTHEBOX_VERSION);
        wp_register_script('OutoftheBox.Basic_Playlist.Player', $this->get_url().'/js/Player.js', ['WPCloudPlugins.Polyfill', 'Basic_Playlist.Library', 'WPCloudplugin.Libraries'], OUTOFTHEBOX_VERSION, true);

        wp_enqueue_script('OutoftheBox.Basic_Playlist.Player');

        $localize_library = [
            'language' => strtolower(strtok(determine_locale(), '_-')),
            'strings' => [
                'wpcp_mejs.plural-form' => 2,
                'wpcp_mejs.install-flash' => esc_html__('You are using a browser that does not have Flash player enabled or installed. Please turn on your Flash player plugin or download the latest version from https://get.adobe.com/flashplayer/'),
                'wpcp_mejs.fullscreen-off' => esc_html__('Turn off Fullscreen'),
                'wpcp_mejs.fullscreen-on' => esc_html__('Go Fullscreen'),
                'wpcp_mejs.download-video' => esc_html__('Download Video'),
                'wpcp_mejs.download-file' => esc_html__('Download', 'wpcloudplugins'),
                'wpcp_mejs.zipdownload' => esc_html__('Download playlist', 'wpcloudplugins'),
                'wpcp_mejs.share' => esc_html__('Share', 'wpcloudplugins'),
                'wpcp_mejs.deeplink' => esc_html__('Direct link', 'wpcloudplugins'),
                'wpcp_mejs.purchase' => esc_html__('Purchase', 'wpcloudplugins'),
                'wpcp_mejs.search' => esc_html__('Search', 'wpcloudplugins').'...',
                'wpcp_mejs.fullscreen' => esc_html__('Fullscreen'),
                'wpcp_mejs.time-jump-forward' => [esc_html__('Jump forward 30 second'), esc_html__('Jump forward %1 seconds')],
                'wpcp_mejs.loop' => esc_html__('Toggle Loop'),
                'wpcp_mejs.play' => esc_html__('Play'),
                'wpcp_mejs.pause' => esc_html__('Pause'),
                'wpcp_mejs.close' => esc_html__('Close'),
                'wpcp_mejs.playlist' => esc_html__('Close'),
                'wpcp_mejs.playlist-prev' => esc_html__('Previous'),
                'wpcp_mejs.playlist-next' => esc_html__('Next'),
                'wpcp_mejs.playlist-loop' => esc_html__('Loop'),
                'wpcp_mejs.playlist-shuffle' => esc_html__('Shuffle'),
                'wpcp_mejs.time-slider' => esc_html__('Time Slider'),
                'wpcp_mejs.time-help-text' => esc_html__('Use Left/Right Arrow keys to advance one second, Up/Down arrows to advance ten seconds.'),
                'wpcp_mejs.time-skip-back' => [esc_html__('Skip back 10 second'), esc_html__('Skip back %1 seconds')],
                'wpcp_mejs.captions-subtitles' => esc_html__('Captions/Subtitles'),
                'wpcp_mejs.captions-chapters' => esc_html__('Chapters'),
                'wpcp_mejs.none' => esc_html__('None'),
                'wpcp_mejs.mute-toggle' => esc_html__('Mute Toggle'),
                'wpcp_mejs.volume-help-text' => esc_html__('Use Up/Down Arrow keys to increase or decrease volume.'),
                'wpcp_mejs.unmute' => esc_html__('Unmute'),
                'wpcp_mejs.mute' => esc_html__('Mute'),
                'wpcp_mejs.volume-slider' => esc_html__('Volume Slider'),
                'wpcp_mejs.video-player' => esc_html__('Video Player'),
                'wpcp_mejs.audio-player' => esc_html__('Audio Player'),
                'wpcp_mejs.ad-skip' => esc_html__('Skip ad'),
                'wpcp_mejs.ad-skip-info' => [esc_html__('Skip in 1 second'), esc_html__('Skip in %1 seconds')],
                'wpcp_mejs.source-chooser' => esc_html__('Source Chooser'),
                'wpcp_mejs.stop' => esc_html__('Stop'),
                'wpcp_mejs.speed-rate' => esc_html__('Speed Rate'),
                'wpcp_mejs.live-broadcast' => esc_html__('Live Broadcast'),
                'wpcp_mejs.afrikaans' => esc_html__('Afrikaans'),
                'wpcp_mejs.albanian' => esc_html__('Albanian'),
                'wpcp_mejs.arabic' => esc_html__('Arabic'),
                'wpcp_mejs.belarusian' => esc_html__('Belarusian'),
                'wpcp_mejs.bulgarian' => esc_html__('Bulgarian'),
                'wpcp_mejs.catalan' => esc_html__('Catalan'),
                'wpcp_mejs.chinese' => esc_html__('Chinese'),
                'wpcp_mejs.chinese-simplified' => esc_html__('Chinese (Simplified)'),
                'wpcp_mejs.chinese-traditional' => esc_html__('Chinese (Traditional)'),
                'wpcp_mejs.croatian' => esc_html__('Croatian'),
                'wpcp_mejs.czech' => esc_html__('Czech'),
                'wpcp_mejs.danish' => esc_html__('Danish'),
                'wpcp_mejs.dutch' => esc_html__('Dutch'),
                'wpcp_mejs.english' => esc_html__('English'),
                'wpcp_mejs.estonian' => esc_html__('Estonian'),
                'wpcp_mejs.filipino' => esc_html__('Filipino'),
                'wpcp_mejs.finnish' => esc_html__('Finnish'),
                'wpcp_mejs.french' => esc_html__('French'),
                'wpcp_mejs.galician' => esc_html__('Galician'),
                'wpcp_mejs.german' => esc_html__('German'),
                'wpcp_mejs.greek' => esc_html__('Greek'),
                'wpcp_mejs.haitian-creole' => esc_html__('Haitian Creole'),
                'wpcp_mejs.hebrew' => esc_html__('Hebrew'),
                'wpcp_mejs.hindi' => esc_html__('Hindi'),
                'wpcp_mejs.hungarian' => esc_html__('Hungarian'),
                'wpcp_mejs.icelandic' => esc_html__('Icelandic'),
                'wpcp_mejs.indonesian' => esc_html__('Indonesian'),
                'wpcp_mejs.irish' => esc_html__('Irish'),
                'wpcp_mejs.italian' => esc_html__('Italian'),
                'wpcp_mejs.japanese' => esc_html__('Japanese'),
                'wpcp_mejs.korean' => esc_html__('Korean'),
                'wpcp_mejs.latvian' => esc_html__('Latvian'),
                'wpcp_mejs.lithuanian' => esc_html__('Lithuanian'),
                'wpcp_mejs.macedonian' => esc_html__('Macedonian'),
                'wpcp_mejs.malay' => esc_html__('Malay'),
                'wpcp_mejs.maltese' => esc_html__('Maltese'),
                'wpcp_mejs.norwegian' => esc_html__('Norwegian'),
                'wpcp_mejs.persian' => esc_html__('Persian'),
                'wpcp_mejs.polish' => esc_html__('Polish'),
                'wpcp_mejs.portuguese' => esc_html__('Portuguese'),
                'wpcp_mejs.romanian' => esc_html__('Romanian'),
                'wpcp_mejs.russian' => esc_html__('Russian'),
                'wpcp_mejs.serbian' => esc_html__('Serbian'),
                'wpcp_mejs.slovak' => esc_html__('Slovak'),
                'wpcp_mejs.slovenian' => esc_html__('Slovenian'),
                'wpcp_mejs.spanish' => esc_html__('Spanish'),
                'wpcp_mejs.swahili' => esc_html__('Swahili'),
                'wpcp_mejs.swedish' => esc_html__('Swedish'),
                'wpcp_mejs.tagalog' => esc_html__('Tagalog'),
                'wpcp_mejs.thai' => esc_html__('Thai'),
                'wpcp_mejs.turkish' => esc_html__('Turkish'),
                'wpcp_mejs.ukrainian' => esc_html__('Ukrainian'),
                'wpcp_mejs.vietnamese' => esc_html__('Vietnamese'),
                'wpcp_mejs.welsh' => esc_html__('Welsh'),
                'wpcp_mejs.yiddish' => esc_html__('Yiddish'),
            ],
        ];

        $localize_mediaplayer = [
            'player_url' => $this->get_url(),
        ];

        wp_localize_script('Basic_Playlist.Library', 'wpcp_mejsL10n', $localize_library);
        wp_localize_script('OutoftheBox.Basic_Playlist.Player', 'Default_Skin_vars', $localize_mediaplayer);

        define('OUTOFTHEBOX_SCRIPTS'.__CLASS__.'_LOADED', true);
    }

    public function load_styles()
    {
        $is_rtl_css = (is_rtl() ? '.rtl' : '');

        wp_register_style('OutoftheBox.Basic_Playlist.Player.CSS', $this->get_url().'/css/style'.$is_rtl_css.'.css', false, OUTOFTHEBOX_VERSION);
        wp_enqueue_style('OutoftheBox.Basic_Playlist.Player.CSS');
    }
}
