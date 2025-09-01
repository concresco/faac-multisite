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
use TheLion\OutoftheBox\CacheRequest;
use TheLion\OutoftheBox\Client;
use TheLion\OutoftheBox\Entry;
use TheLion\OutoftheBox\Helpers;
use TheLion\OutoftheBox\Processor;
use TheLion\OutoftheBox\Restrictions;
use TheLion\OutoftheBox\Settings;
use TheLion\OutoftheBox\User;

defined('ABSPATH') || exit;

class Mediaplayer
{
    protected $_folder;
    protected $_items;

    public function get_media_list()
    {
        $this->_folder = Client::instance()->get_folder(null, false, true, false);

        if (false !== $this->_folder) {
            // Create Gallery array
            $this->_items = $this->createItems();

            if (count($this->_items) > 0) {
                $response = json_encode($this->_items);

                $cached_request = new CacheRequest();
                $cached_request->add_cached_response($response);

                header('Content-Type: application/json');
                echo $response;
            }
        }

        exit;
    }

    public function setFolder($folder)
    {
        $this->_folder = $folder;
    }

    public function createItems()
    {
        $covers = [];
        $captions = [];

        // Add covers and Captions
        if ($this->_folder->has_children()) {
            foreach ($this->_folder->get_children() as $child) {
                if (!isset($child->extension)) {
                    continue;
                }

                if (in_array(strtolower($child->extension), ['png', 'jpg', 'jpeg'])) {
                    // Add images to cover array
                    $covers[$child->get_basename()] = $child;
                } elseif (in_array(strtolower($child->extension), ['vtt', 'srt'])) {
                    /*
                     * SRT | VTT files are supported for captions:.
                     *
                     * Filename: Videoname.Caption Label.Language.VTT|SRT
                     */

                    preg_match('/(?<name>.*).(?<label>\w*).(?<language>\w*)\.(srt|vtt)$/Uu', $child->get_name(), $match, PREG_UNMATCHED_AS_NULL, 0);

                    if (empty($match) || empty($match['language'])) {
                        continue;
                    }

                    $video_name = $match['name'];

                    if (!isset($captions[$video_name])) {
                        $captions[$video_name] = [];
                    }

                    if (false === array_search($match['label'], array_column($captions[$video_name], 'label'))) {
                        $captions[$video_name][] = [
                            'label' => $match['label'],
                            'language' => $match['language'],
                            'src' => OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-stream&OutoftheBoxpath='.rawurlencode($child->get_id()).'&dl=1&caption=1&account_id='.App::get_current_account()->get_uuid().'&listtoken='.Processor::instance()->get_listtoken(),
                        ];
                    }
                }
            }
        }

        $files = [];

        // Create Filelist array
        if ($this->_folder->has_children()) {
            foreach ($this->_folder->get_children() as $child) {
                if (false === $this->is_media_file($child) || false === Processor::instance()->_is_entry_authorized($child)) {
                    continue;
                }

                $basename = $child->get_basename();
                $foldername = basename(dirname($child->get_path_display()));
                $extension = $child->get_extension();

                if (isset($covers[$basename])) {
                    $poster = Client::instance()->get_thumbnail($covers[$basename], true, 480, 640);
                } elseif (isset($covers[$foldername])) {
                    $poster = Client::instance()->get_thumbnail($covers[$foldername], true, 480, 640);
                } else {
                    $poster = Client::instance()->get_thumbnail($child, true, 480, 640);
                }

                $folder_str = dirname($child->get_path_display());
                $folder_str = trim(str_replace('\\', '/', $folder_str), '/');
                $path = trim($folder_str.'/'.$basename, '/');

                // combine same files with different extensions
                if (!isset($files[$path])) {
                    $source_url = OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-stream&OutoftheBoxpath='.rawurlencode($child->get_id()).'&lastpath='.rawurlencode(Processor::instance()->get_last_path()).'&account_id='.App::get_current_account()->get_uuid().'&listtoken='.Processor::instance()->get_listtoken();
                    if ('Yes' !== Settings::get('google_analytics')) {
                        $cached_source_url = get_transient('outofthebox_stream_'.$child->get_id().'_'.$child->get_extension());
                        if (false !== $cached_source_url && false === filter_var($cached_source_url, FILTER_VALIDATE_URL)) {
                            $source_url = $cached_source_url;
                        }
                    }

                    $last_edited = $child->get_last_edited();
                    $localtime = get_date_from_gmt(date('Y-m-d H:i:s', $last_edited));

                    $files[$path] = [
                        'title' => $basename,
                        'name' => $path,
                        'path_display' => $child->get_path_display(),
                        'artist' => '',
                        'is_dir' => false,
                        'folder' => $folder_str,
                        'poster' => $poster,
                        'thumb' => $poster,
                        'size' => $child->get_size(),
                        'id' => $child->get_id(),
                        'last_edited' => $last_edited,
                        'last_edited_date_str' => !empty($last_edited) ? date_i18n(get_option('date_format'), strtotime($localtime)) : '',
                        'last_edited_time_str' => !empty($last_edited) ? date_i18n(get_option('time_format'), strtotime($localtime)) : '',
                        'download' => (User::can_download() && !Restrictions::has_reached_download_limit($child->get_id())) ? str_replace('outofthebox-stream', 'outofthebox-download', $source_url) : false,
                        'share' => User::can_share(),
                        'deeplink' => User::can_deeplink(),
                        'source' => $source_url,
                        'captions' => isset($captions[$basename]) ? $captions[$basename] : [],
                        'type' => Helpers::get_mimetype($extension),
                        'extension' => $extension,
                        'height' => $child->get_media('height'),
                        'width' => $child->get_media('width'),
                        'duration' => $child->get_media('duration') * 1000, // ms to sec,
                        'linktoshop' => ('' !== Processor::instance()->get_shortcode_option('linktoshop')) ? Processor::instance()->get_shortcode_option('linktoshop') : false,
                    ];
                }
            }

            $files = Processor::instance()->sort_filelist($files);
        }

        if ('-1' !== Processor::instance()->get_shortcode_option('max_files')) {
            $files = array_slice($files, 0, Processor::instance()->get_shortcode_option('max_files'));
        }

        return $files;
    }

    public function is_media_file(Entry $entry)
    {
        if ($entry->is_dir()) {
            return false;
        }

        $extension = $entry->get_extension();
        $mimetype = $entry->get_mimetype();

        if ('audio' === Processor::instance()->get_shortcode_option('mode')) {
            $allowedextensions = ['mp3', 'm4a', 'ogg', 'oga', 'wav'];
            $allowedimimetypes = ['audio/mpeg', 'audio/mp4', 'audio/ogg', 'audio/x-wav'];
        } else {
            $allowedextensions = ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'webm'];
            $allowedimimetypes = ['video/mp4', 'video/ogg', 'video/webm'];
        }

        if (!empty($extension) && in_array($extension, $allowedextensions)) {
            return true;
        }

        return in_array($mimetype, $allowedimimetypes);
    }

    public static function render($attributes = [])
    {
        $shortcode = Processor::instance()->get_shortcode();
        $mediaplayer = self::load_skin($shortcode['mediaskin']);

        $attributes += [
            'data-list' => 'media',
            'data-layout' => $shortcode['filelayout'],
        ];

        echo "<div class='wpcp-module OutoftheBox media ".$shortcode['mode']." jsdisabled' ".Module::parse_attributes($attributes).'>';

        Password::render();

        $mediaplayer->load_player();

        echo '</div>';
    }

    public static function load_skin($mediaplayer = null)
    {
        if (empty($mediaplayer)) {
            $mediaplayer = Settings::get('mediaplayer_skin');
        }

        if (file_exists(OUTOFTHEBOX_ROOTDIR.'/skins/'.$mediaplayer.'/Player.php')) {
            require_once OUTOFTHEBOX_ROOTDIR.'/skins/'.$mediaplayer.'/Player.php';
        } else {
            Helpers::log_error(sprintf('Media Player Skin %s is missing', $mediaplayer), 'MediaPlayer', null, __LINE__);

            return self::load_skin(null);
        }

        try {
            $class = '\TheLion\OutoftheBox\MediaPlayers\\'.$mediaplayer;

            return new $class();
        } catch (\Exception $ex) {
            Helpers::log_error(sprintf('Media Player Skin %s is invalid', $mediaplayer), 'MediaPlayer', null, __LINE__);

            return false;
        }
    }
}
