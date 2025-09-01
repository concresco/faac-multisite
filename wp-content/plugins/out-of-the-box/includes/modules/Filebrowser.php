<?php

/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox\Modules;

use TheLion\OutoftheBox\API;
use TheLion\OutoftheBox\App;
use TheLion\OutoftheBox\Cache;
use TheLion\OutoftheBox\CacheRequest;
use TheLion\OutoftheBox\Client;
use TheLion\OutoftheBox\Core;
use TheLion\OutoftheBox\Entry;
use TheLion\OutoftheBox\EntryAbstract;
use TheLion\OutoftheBox\Helpers;
use TheLion\OutoftheBox\Processor;
use TheLion\OutoftheBox\Restrictions;
use TheLion\OutoftheBox\Settings;
use TheLion\OutoftheBox\User;

defined('ABSPATH') || exit;

class Filebrowser
{
    public static $enqueued_scripts = false;
    protected $_folder;
    protected $_items;
    protected $_search = false;
    protected $_parentfolders = [];

    public function get_files_list()
    {
        $this->_folder = Client::instance()->get_folder(null, true);

        if (false !== $this->_folder) {
            $this->renderFileList();
        }
    }

    public function search_files()
    {
        if ('POST' !== $_SERVER['REQUEST_METHOD'] || !User::can_search()) {
            exit(-1);
        }

        $this->_search = true;
        $_REQUEST['query'] = wp_kses(stripslashes($_REQUEST['query']), 'strip');
        $this->_folder = Client::instance()->search($_REQUEST['query']);

        if (false !== $this->_folder) {
            $this->renderFileList();
        }
    }

    public function setFolder($folder)
    {
        $this->_folder = $folder;
    }

    public function renderFileList()
    {
        // Create HTML Filelist
        $filelist_html = '';

        $breadcrumb_class = ('1' === Processor::instance()->get_shortcode_option('show_breadcrumb')) ? 'has-breadcrumb' : 'no-breadcrumb';
        $fileinfo_class = ('1' === Processor::instance()->get_shortcode_option('fileinfo_on_hover')) ? 'has-fileinfo-on-hover' : '';

        $filelist_html = "<div class='files {$breadcrumb_class} {$fileinfo_class}'>";
        $filelist_html .= "<div class='folders-container'>";

        $filescount = 0;
        $folderscount = 0;

        // Add 'back to Previous folder' if needed
        if (
            (false === $this->_search)
            && ('' !== $this->_folder->get_path())
            && (strtolower($this->_folder->get_path()) !== strtolower(Processor::instance()->get_root_folder()))
        ) {
            $location = str_replace('\\', '/', dirname(Processor::instance()->get_requested_path()));

            $parent_folder_entry = new Entry();
            $parent_folder_entry->set_id('Previous Folder');
            $parent_folder_entry->set_name(esc_html__('Previous folder', 'wpcloudplugins'));
            $parent_folder_entry->set_basename(esc_html__('Previous folder', 'wpcloudplugins'));
            $parent_folder_entry->set_path($location);
            $parent_folder_entry->set_path_display($location);
            $parent_folder_entry->set_is_dir(true);
            $parent_folder_entry->set_parent_folder(true);
            $parent_folder_entry->set_icon(OUTOFTHEBOX_ROOTPATH.'/css/icons/32x32/prev.png');

            $filelist_html .= $this->renderDir($parent_folder_entry);
        }

        // Don't return any results for empty searches in the Search Box
        if ('search' === Processor::instance()->get_shortcode_option('mode') && empty($_REQUEST['query']) && (strtolower($this->_folder->get_path()) === strtolower(Processor::instance()->get_root_folder()))) {
            $this->_folder->children = [];
        }

        // Limit the number of files if needed
        if ('-1' !== Processor::instance()->get_shortcode_option('max_files') && $this->_folder->has_children()) {
            $children = $this->_folder->get_children();
            $children_sliced = array_slice($children, 0, (int) Processor::instance()->get_shortcode_option('max_files'));
            $this->_folder->set_children($children_sliced);
        }

        if ($this->_folder->has_children()) {
            foreach ($this->_folder->get_children() as $item) {
                // Render folder div
                if ($item->is_dir()) {
                    $filelist_html .= $this->renderDir($item);

                    if (!$item->is_parent_folder() || '1' !== Processor::instance()->get_shortcode_option('show_breadcrumb')) {
                        ++$folderscount;
                    }
                }
            }
        }

        if (false === $this->_search) {
            $filelist_html .= $this->renderNewFolder();
        }

        $filelist_html .= "</div><div class='files-container'>";

        if ($this->_folder->has_children()) {
            foreach ($this->_folder->get_children() as $item) {
                // Render files div
                if ($item->is_file()) {
                    $filelist_html .= $this->renderFile($item);
                    ++$filescount;
                }
            }
        }

        $filelist_html .= '</div></div>';

        // Create HTML Filelist title
        $file_path = '<ol class="wpcp-breadcrumb">';
        $folder_path = array_filter(explode('/', Processor::instance()->get_requested_path()));
        $root_folder = Processor::instance()->get_root_folder();
        $current_folder = basename(Processor::instance()->get_requested_path());
        $current_folder = empty($current_folder) ? '/' : $current_folder;
        $location = '';

        $pos = stripos($this->_folder->get_path_display(), $root_folder);
        $root_text = false !== $pos ? basename(substr($this->_folder->get_path_display(), 0, $pos + strlen($root_folder))) : basename($root_folder);
        $root_text = '1' === Processor::instance()->get_shortcode_option('use_custom_roottext') ? Processor::instance()->get_shortcode_option('root_text') : $root_text;

        $file_path .= "<li class='first-breadcrumb'><a href='javascript:void(0)".rawurlencode($location)."' class='folder current_folder'  data-url='".rawurlencode('/')."'>{$root_text}</a></li>";

        if (count($folder_path) > 0 && (false === $this->_search || 'parent' === Processor::instance()->get_shortcode_option('searchfrom'))) {
            foreach ($folder_path as $parent_folder) {
                $location .= '/'.$parent_folder;

                if ($parent_folder === $current_folder && '' !== $this->_folder->get_name()) {
                    $file_path .= "<li><a href='javascript:void(0)' class='folder'  data-url='".rawurlencode($location)."'>".$this->_folder->get_name().'</a></li>';
                } else {
                    $file_path .= "<li><a href='javascript:void(0)' class='folder'  data-url='".rawurlencode($location)."'>".$parent_folder.'</a></li>';
                }
            }
        }

        if (true === $this->_search) {
            $file_path .= "<li><a href='javascript:void(0)' class='folder'>".sprintf(esc_html__('Results for %s', 'wpcloudplugins'), "'".htmlentities($_REQUEST['query'])."'").'</a></li>';
        }

        $file_path .= '</ol>';

        $raw_path = '';
        if (
            Helpers::check_user_role(Settings::get('permissions_add_shortcodes'))
            || Helpers::check_user_role(Settings::get('permissions_add_links'))
            || Helpers::check_user_role(Settings::get('permissions_add_embedded'))
        ) {
            $raw_path = (null !== $this->_folder->get_path()) ? $this->_folder->get_path() : '';
        }

        $response = json_encode([
            'lastpath' => rawurlencode(Processor::instance()->get_last_path()),
            'rawpath' => $raw_path,
            'accountId' => '0' === Processor::instance()->get_shortcode_option('popup') ? App::get_current_account()->get_uuid() : App::get_current_account()->get_id(),
            'lastFolder' => $this->_folder->get_id(),
            'virtual' => false,
            'breadcrumb' => $file_path,
            'folderscount' => $folderscount,
            'filescount' => $filescount,
            'html' => $filelist_html,
        ]);

        $cached_request = new CacheRequest();
        $cached_request->add_cached_response($response);

        header('Content-Type: application/json');
        echo $response;

        exit;
    }

    public function renderDir(Entry $item)
    {
        $return = '';

        $classmoveable = (User::can_move_folders()) ? 'moveable' : '';

        $isparent = $item->is_parent_folder();
        $has_access = $item->has_access();
        $style = ($isparent) ? ' pf previous ' : '';
        $accessible = ($has_access) ? '' : ' not-accessible ';

        $return .= "<div class='entry folder {$classmoveable}  {$style} {$accessible}' data-id='".$item->get_id()."' data-url='".rawurlencode($item->get_path_display())."' data-name='".htmlspecialchars($item->get_basename(), ENT_QUOTES | ENT_HTML401, 'UTF-8')."'>\n";
        $return .= "<div class='entry_block'>\n";
        $return .= "<div class='entry-info'>";

        if (!$isparent && $has_access) {
            $return .= $this->renderCheckBox($item);
        }

        $thumburl = $item->get_icon_retina();
        $return .= "<div class='entry-info-icon'><div class='preloading'></div><img class='preloading' src='".OUTOFTHEBOX_ROOTPATH."/css/images/transparant.png' data-src='{$thumburl}' data-src-retina='{$thumburl}' alt=''/></div>";

        $return .= "<div class='entry-info-name'>";
        if ($has_access) {
            $return .= "<a href='javascript:void(0);' class='entry_link' title='{$item->get_basename()}'>";
        }
        $return .= '<span>';
        $return .= (($isparent) ? '<strong>'.esc_html__('Previous folder', 'wpcloudplugins').'</strong>' : $item->get_name()).' </span>';
        $return .= '</span>';
        if ($has_access) {
            $return .= '</a>';
        }
        $return .= '</div>';

        if (!$isparent && $has_access) {
            $return .= $this->renderItemSelect($item);
            $return .= $this->renderDownload($item);
            $return .= $this->renderDescription($item);
            $return .= $this->renderActionMenu($item);
        }

        $return .= "</div>\n";

        $return .= "</div>\n";
        $return .= "</div>\n";

        return apply_filters('outofthebox_render_filebrowser_entry', $return, $item, $this, Processor::instance());
    }

    public function renderFile(Entry $item)
    {
        $link = $this->renderFileNameLink($item);
        $title = $link['filename'].((('1' === Processor::instance()->get_shortcode_option('show_filesize')) && ($item->get_size() > 0)) ? ' ('.Helpers::bytes_to_size_1024($item->get_size()).')' : '');

        $classmoveable = (User::can_move_files()) ? 'moveable' : '';

        $thumbnail_url = ($item->has_own_thumbnail() ? Client::instance()->get_thumbnail($item, true, 640, 480) : $item->get_icon_retina());

        $return = '';
        $return .= "<div class='entry file {$classmoveable}' data-id='".$item->get_id()."' data-url='".rawurlencode($item->get_path_display())."' data-name='".htmlspecialchars($item->get_name(), ENT_QUOTES | ENT_HTML401, 'UTF-8')."'>\n";
        $return .= "<div class='entry_block'>\n";

        $return .= "<div class='entry_thumbnail'><div class='entry_thumbnail-view-bottom'><div class='entry_thumbnail-view-center'>\n";

        $return .= "<div class='preloading'></div>";
        $return .= "<img class='preloading' src='".OUTOFTHEBOX_ROOTPATH."/css/images/transparant.png' data-src='".$thumbnail_url."' data-src-retina='".$thumbnail_url."' data-src-backup='".$item->get_icon_retina()."' alt='{$title}'/>";
        $return .= "</div></div></div>\n";

        if ($duration = $item->get_media('duration')) {
            $return .= "<div class='entry-duration'><i class='eva eva-arrow-right ' ></i> ".Helpers::convert_ms_to_time($duration).'</div>';
        }

        // Audio files can play inline without lightbox
        $inline_player = '';
        if (User::can_preview() && in_array($item->get_extension(), ['mp3', 'm4a', 'ogg', 'oga', 'flac', 'wav'])) {
            $stream_url = OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-preview&OutoftheBoxpath='.rawurlencode($item->get_path()).'&lastpath='.rawurlencode(Processor::instance()->get_last_path()).'&account_id='.App::get_current_account()->get_uuid().'&listtoken='.Processor::instance()->get_listtoken();

            if (Client::instance()->has_temporarily_link($item)) {
                $cached_entry = Cache::instance()->get_node_by_id($item->get_id());
                $stream_url = $cached_entry->get_temporarily_link();
            }

            $inline_player .= "<div class='entry-inline-player' data-src='{$stream_url}' type='{$item->get_mimetype()}'><i class='eva eva-play-circle-outline eva-lg'></i> <i class='eva eva-pause-circle-outline eva-lg'></i> <i class='eva eva-volume-up-outline eva-lg eva-pulse'></i>";
            $inline_player .= '</div>';
        }

        $return .= "<div class='entry-info'>";
        $return .= $this->renderCheckBox($item);
        $return .= "<div class='entry-info-icon ".(!empty($inline_player) ? 'entry-info-icon-has-player' : '')."'><img src='".$item->get_icon()."'/>{$inline_player}</div>";
        $return .= "<div class='entry-info-name'>";

        $return .= '<a '.$link['url'].' '.$link['target']." class='entry_link ".$link['class']."' ".$link['onclick']." title='".$title."' ".$link['lightbox']." data-name='".$link['filename']."' data-entry-id='{$item->get_id()}'>";

        $return .= '<span>'.$link['filename'].'</span>';

        $return .= '</a>';

        $return .= '</div>';

        $return .= $this->renderItemEmbed($item);
        $return .= $this->renderItemSelect($item);
        $return .= $this->renderModifiedDate($item);
        $return .= $this->renderSize($item);
        $return .= $this->renderThumbnailHover($item);
        $return .= $this->renderDownload($item);
        $return .= $this->renderDescription($item);
        $return .= $this->renderActionMenu($item);
        $return .= "</div>\n";

        $return .= $link['lightbox_inline'];

        $return .= "</div>\n";
        $return .= "</div>\n";

        return apply_filters('outofthebox_render_filebrowser_entry', $return, $item, $this, Processor::instance());
    }

    public function renderSize(EntryAbstract $item)
    {
        if ('1' === Processor::instance()->get_shortcode_option('show_filesize')) {
            $size = ($item->get_size() > 0) ? Helpers::bytes_to_size_1024($item->get_size()) : '&nbsp;';

            return "<div class='entry-info-size entry-info-metadata'>".$size.'</div>';
        }
    }

    public function renderModifiedDate(EntryAbstract $item)
    {
        if ('1' === Processor::instance()->get_shortcode_option('show_filedate')) {
            return "<div class='entry-info-modified-date entry-info-metadata'>".$item->get_last_edited_str().'</div>';
        }
    }

    public function renderCheckBox(EntryAbstract $item)
    {
        $checkbox = '';

        if ('0' === Processor::instance()->get_shortcode_option('show_header')) {
            return $checkbox;
        }

        if ($item->is_dir()) {
            if (
                in_array(Processor::instance()->get_shortcode_option('popup'), ['links', 'selector'])
                || User::can_download_zip()
                 || User::can_delete_folders()
                  || User::can_move_folders()
                  || User::can_copy_folders()
            ) {
                $checkbox .= "<div class='entry-info-button entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='".$item->get_id()."' id='checkbox-".Processor::instance()->get_listtoken()."-{$item->get_id()}'/><label for='checkbox-".Processor::instance()->get_listtoken()."-{$item->get_id()}'></label></div>";
            }
        } else {
            if (
                in_array(Processor::instance()->get_shortcode_option('popup'), ['links', 'embedded', 'selector'])
                || User::can_download_zip()
                 || User::can_delete_files()
                  || User::can_move_files()
                  || User::can_copy_files()
            ) {
                $checkbox .= "<div class='entry-info-button entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='".$item->get_id()."' id='checkbox-".Processor::instance()->get_listtoken()."-{$item->get_id()}'/><label for='checkbox-".Processor::instance()->get_listtoken()."-{$item->get_id()}'></label></div>";
            }
        }

        return $checkbox;
    }

    public function renderFileNameLink(Entry $item)
    {
        $class = '';
        $url = '';
        $target = '';
        $onclick = '';
        $lightbox = '';
        $lightbox_inline = '';
        $datatype = 'iframe';
        $filename = ('1' === Processor::instance()->get_shortcode_option('show_ext')) ? $item->get_name() : $item->get_basename();

        // Check if user is allowed to preview the file
        $usercanpreview = User::can_preview() && '1' !== Processor::instance()->get_shortcode_option('forcedownload');

        if (
            $item->is_dir()
            || false === $item->get_can_preview_by_cloud()
            || false === User::can_view()
        ) {
            $usercanpreview = false;
        }

        // Check if user is allowed to preview the file
        if ($usercanpreview) {
            $url = OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-preview&OutoftheBoxpath='.rawurlencode($item->get_path()).'&lastpath='.rawurlencode(Processor::instance()->get_last_path()).'&account_id='.App::get_current_account()->get_uuid().'&listtoken='.Processor::instance()->get_listtoken();

            // Display Direct links for image and media files
            if (in_array($item->get_extension(), ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
                $datatype = 'image';
                if (Client::instance()->has_temporarily_link($item)) {
                    $cached_entry = Cache::instance()->get_node_by_id($item->get_id());
                    $url = $cached_entry->get_temporarily_link();
                } elseif (Client::instance()->has_shared_link($item)) {
                    $url = API::convert_to_raw_url(Client::instance()->get_shared_link($item), $item->get_extension());
                }

                // Use preview thumbnail or raw  file
                if (
                    ('default' === Processor::instance()->get_shortcode_option('lightbox_imagesource') && 'thumbnail' === Settings::get('loadimages'))
                    || 'thumbnail' === Processor::instance()->get_shortcode_option('lightbox_imagesource')
                    || false === User::can_download()) {
                    $url = Client::instance()->get_thumbnail($item, true, 1024, 768);
                }
            } elseif (in_array($item->get_extension(), ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'ogg', 'oga', 'wav', 'flac'])) {
                $datatype = 'inline';

                if (Client::instance()->has_temporarily_link($item)) {
                    $cached_entry = Cache::instance()->get_node_by_id($item->get_id());
                    $url = $cached_entry->get_temporarily_link();
                }
            }

            // Check if we need to preview inline
            if ('1' === Processor::instance()->get_shortcode_option('previewinline')) {
                $class = 'ilightbox-group';
                $onclick = "sendAnalyticsOFTB('Preview', '{$item->get_name()}');";

                // Lightbox Settings
                $lightbox = "rel='ilightbox[".Processor::instance()->get_listtoken()."]' ";
                $lightbox .= 'data-type="'.$datatype.'"';

                switch ($datatype) {
                    case 'image':
                        $lightbox .= ' data-options="thumbnail: \''.Client::instance()->get_thumbnail($item, true, 256, 256).'\'"';

                        break;

                    case 'inline':
                        if (empty($url)) {
                            $url = OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-preview&OutoftheBoxpath='.rawurlencode($item->get_path()).'&lastpath='.rawurlencode(Processor::instance()->get_last_path()).'&account_id='.App::get_current_account()->get_uuid().'&listtoken='.Processor::instance()->get_listtoken();
                        }

                        $id = 'ilightbox_'.Processor::instance()->get_listtoken().'_'.md5($item->get_id());
                        $html5_element = (false === strpos($item->get_mimetype(), 'video')) ? 'audio' : 'video';
                        $icon = ($item->has_own_thumbnail() ? Client::instance()->get_thumbnail($item, true, 0, 256, 256) : $item->get_icon_large());
                        $icon_256 = ($item->has_own_thumbnail() ? Client::instance()->get_thumbnail($item, true, 256, 256) : $item->get_icon_large());
                        $lightbox .= ' data-options="mousewheel: false, swipe:false, thumbnail: \''.$icon.'\'"';
                        $download = 'controlsList="nodownload"';

                        $lightbox_inline = '<div id="'.$id.'" class="html5_player" style="display:none;"><'.$html5_element.' controls '.$download.' preload="metadata"  poster="'.$icon_256.'"> <source data-src="'.$url.'" type="'.$item->get_mimetype().'">'.esc_html__('Your browser does not support HTML5. You can only download this file', 'wpcloudplugins').'</'.$html5_element.'></div>';
                        $url = '#'.$id;

                        break;

                    case 'iframe':
                        $icon_128 = ($item->has_own_thumbnail() ? Client::instance()->get_thumbnail($item, true, 256, 256) : $item->get_icon_large());
                        $lightbox .= ' data-options="mousewheel: false, thumbnail: \''.$icon_128.'\'"';

                        // no break
                    default:
                        break;
                }
            } else {
                if (!in_array($item->get_extension(), ['mp3', 'm4a', 'ogg', 'oga', 'flac', 'wav'])) {
                    $class = 'entry_action_external_view';
                    $target = '_blank';
                    $onclick = "sendAnalyticsOFTB('Preview  (new window)', '{$item->get_name()}');";
                } else {
                    $url = '#';
                    $class = 'use_inline_player';
                }
            }
        } elseif (('0' === Processor::instance()->get_shortcode_option('popup')) && User::can_download()) {
            // Check if user is allowed to download file

            $url = OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-download&OutoftheBoxpath='.rawurlencode($item->get_path()).'&lastpath='.rawurlencode(Processor::instance()->get_last_path()).'&account_id='.App::get_current_account()->get_uuid().'&listtoken='.Processor::instance()->get_listtoken();
            $class = 'entry_action_download';

            $target = ('url' === $item->get_extension() || 'web' === $item->get_extension()) ? '"_blank"' : $target;
        }

        if ('selector' === Processor::instance()->get_shortcode_option('popup')) {
            $class = 'entry-select-item';
            $url = '';
        }

        if ('shortcode_builder' === Processor::instance()->get_shortcode_option('popup')) {
            $url = '';
        }

        if (Processor::instance()->is_mobile() && 'iframe' === $datatype) {
            $lightbox = '';
            $class = 'entry_action_external_view';
            $target = '_blank';
            $onclick = "sendAnalyticsOFTB('Preview  (new window)', '{$item->get_name()}');";
        }

        if (!empty($url)) {
            $url = "href='".$url."'";
        }
        if (!empty($target)) {
            $target = "target='".$target."'";
        }
        if (!empty($onclick)) {
            $onclick = 'onclick="'.$onclick.'"';
        }

        return ['filename' => htmlspecialchars($filename, ENT_COMPAT | ENT_HTML401 | ENT_QUOTES, 'UTF-8'), 'class' => $class, 'url' => $url, 'lightbox' => $lightbox, 'lightbox_inline' => $lightbox_inline, 'target' => $target, 'onclick' => $onclick];
    }

    public function renderThumbnailHover(Entry $item)
    {
        $thumbnail_url = ($item->has_own_thumbnail() ? Client::instance()->get_thumbnail($item, true, 640, 480) : $item->get_icon_retina());

        if (
            false === $item->has_own_thumbnail()
            || empty($thumbnail_url)
            || ('0' === Processor::instance()->get_shortcode_option('hover_thumbs'))) {
            return '';
        }

        $html = "<div class='entry-info-button entry-thumbnail-button  tabindex='0'><i class='eva eva-eye-outline eva-lg'></i>\n";
        $html .= "<div class='tippy-content-holder'>";

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function renderDownload(Entry $item)
    {
        $html = '';

        $usercanread = User::can_download() && ($item->is_file() || '1' === Processor::instance()->get_shortcode_option('can_download_zip'));
        $is_limit_reached = Restrictions::has_reached_download_limit($item->get_id()) ? 'disabled' : '';

        if (!$usercanread) {
            return $html;
        }

        $url = '';
        $target = '';

        if ($item->is_file()) {
            $url = OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-download&OutoftheBoxpath='.rawurlencode($item->get_path()).'&lastpath='.rawurlencode(Processor::instance()->get_last_path()).'&account_id='.App::get_current_account()->get_uuid().'&listtoken='.Processor::instance()->get_listtoken().'&dl=1';
            $target = ('url' === $item->get_extension() || 'web' === $item->get_extension()) ? 'target="_blank"' : '';
        }

        $html .= "<div class='entry-info-button entry-download-button' tabindex='0'>
        <a class='entry_action_download {$is_limit_reached}' ".(!empty($url) ? "href='{$url}'" : '').'  '.(!empty($target) ? $target : '')." download='".$item->get_name()."' data-name='".$item->get_name()."' title='".esc_html__('Download', 'wpcloudplugins')."'><i class='eva eva-download eva-lg'></i></a>\n";
        $html .= '</div>';

        return $html;
    }

    public function renderDescription(EntryAbstract $item)
    {
        $html = '';

        $metadata = [];

        if (!empty($item->get_description())) {
            $metadata['description'] = [
                'title' => '',
                'text' => nl2br($item->get_description()),
            ];
        }

        if ('1' === Processor::instance()->get_shortcode_option('show_filesize') && $item->get_size() > 0) {
            $metadata['size']
            = [
                'title' => '<i class="eva eva-info-outline"></i> '.esc_html__('File Size', 'wpcloudplugins'),
                'text' => Helpers::bytes_to_size_1024($item->get_size()),
            ];
        }

        if ('1' === Processor::instance()->get_shortcode_option('show_filedate') && !empty($item->get_last_edited_str(false))) {
            $metadata['modified']
            = [
                'title' => '<i class="eva eva-clock-outline"></i> '.esc_html__('Modified', 'wpcloudplugins'),
                'text' => $item->get_last_edited_str(false),
            ];
        }

        if ($this->_search) {
            $location = dirname($item->path_display);

            if (!empty($location) && '\\' !== $location) {
                $metadata['path']
                = [
                    'title' => "<i class='eva eva-folder-outline'></i> ".esc_html__('Location', 'wpcloudplugins'),
                    'text' => "<button class='button secondary folder search-location' data-url='".rawurlencode($location)."'> ".basename($location).'</button>',
                    'location' => $location,
                ];
            }
        }

        $metadata = apply_filters('outofthebox_filebrowser_set_description', $metadata, $item);

        $metadata = array_filter($metadata);

        if (empty($metadata)) {
            return '';
        }

        $html .= "<div class='entry-info-button entry-description-button -visible' tabindex='0' data-metadata='".base64_encode(\json_encode($metadata))."'><i class='eva eva-info-outline eva-lg'></i></div>";

        return $html;
    }

    public function renderItemEmbed(Entry $item)
    {
        if (
            'shortcode_builder' === Processor::instance()->get_shortcode_option('popup')
            && in_array($item->get_extension(), ['mp4', 'm4v', 'ogg', 'ogv', 'webmv', 'mp3', 'm4a', 'oga', 'wav', 'webm'])
        ) {
            return "<a class='entry-info-button entry-embed-item'><i class='eva eva-code eva-lg'></i></a>";
        }

        return '';
    }

    public function renderItemSelect(Entry $item)
    {
        $html = '';

        if (in_array(Processor::instance()->get_shortcode_option('popup'), ['personal_folders_selector', 'personal_folders_backend', 'selector'])) {
            $html .= "<div class='entry-info-button entry-select-item' title='".esc_html__('Select this item', 'wpcloudplugins')."'><i class='eva eva-checkmark-outline eva-lg'></i></div>";
        }

        return $html;
    }

    public function renderActionMenu(Entry $item)
    {
        $html = '';

        $usercanpreview = User::can_preview();

        if (
            $item->is_dir()
            || false === $item->get_can_preview_by_cloud()
            || 'zip' === $item->get_extension()
            || false === User::can_view()
        ) {
            $usercanpreview = false;
        }

        $usercanread = User::can_download() && ($item->is_file() || '1' === Processor::instance()->get_shortcode_option('can_download_zip'));
        $usercanshare = User::can_share() && true === $item->get_permission('canshare');
        $usercandeeplink = User::can_deeplink();

        $usercanrename = (($item->is_dir()) ? User::can_rename_folders() : User::can_rename_files()) && true === $item->get_permission('canrename');
        $usercanmove = (($item->is_dir()) ? User::can_move_folders() : User::can_move_files()) && true === $item->get_permission('canmove');
        $usercancopy = (($item->is_dir()) ? User::can_copy_folders() : User::can_copy_files());
        $usercandelete = (($item->is_dir()) ? User::can_delete_folders() : User::can_delete_files()) && true === $item->get_permission('candelete');

        $filename = (('1' === Processor::instance()->get_shortcode_option('show_ext')) ? $item->get_name() : $item->get_basename());

        // View
        $previewurl = OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-preview&OutoftheBoxpath='.rawurlencode($item->get_path()).'&lastpath='.rawurlencode(Processor::instance()->get_last_path()).'&account_id='.App::get_current_account()->get_uuid().'&listtoken='.Processor::instance()->get_listtoken();
        $onclick = "sendAnalyticsOFTB('Preview', '".$item->get_name()."');";

        if ($usercanpreview && '1' !== Processor::instance()->get_shortcode_option('forcedownload')) {
            if ($item->get_can_preview_by_cloud() && '1' === Processor::instance()->get_shortcode_option('previewinline')) {
                $html .= "<li><a class='entry_action_view' title='".esc_html__('Preview', 'wpcloudplugins')."'><i class='eva eva-eye-outline eva-lg'></i>&nbsp;".esc_html__('Preview', 'wpcloudplugins').'</a></li>';

                if ('1' === Processor::instance()->get_shortcode_option('previewnewtab')) {
                    $html .= "<li><a href='{$previewurl}' target='_blank' class='entry_action_external_view' onclick=\"{$onclick}\" title='".esc_html__('Preview in new tab', 'wpcloudplugins')."'><i class='eva eva-monitor-outline eva-lg'></i>&nbsp;".esc_html__('Preview in new tab', 'wpcloudplugins').'</a></li>';
                }
            } elseif ($item->get_can_preview_by_cloud()) {
                if ('1' === Processor::instance()->get_shortcode_option('previewinline')) {
                    $html .= "<li><a class='entry_action_view' title='".esc_html__('Preview', 'wpcloudplugins')."'><i class='eva eva-eye-outline eva-lg'></i>&nbsp;".esc_html__('Preview', 'wpcloudplugins').'</a></li>';
                }
                if ('1' === Processor::instance()->get_shortcode_option('previewnewtab')) {
                    $html .= "<li><a href='{$previewurl}' target='_blank' class='entry_action_external_view' onclick=\"{$onclick}\" title='".esc_html__('Preview in new tab', 'wpcloudplugins')."'><i class='eva eva-monitor-outline eva-lg'></i>&nbsp;".esc_html__('Preview in new tab', 'wpcloudplugins').'</a></li>';
                }
            }
        }

        // Deeplink
        if ($usercandeeplink) {
            $html .= "<li><a class='entry_action_deeplink' title='".esc_html__('Direct link', 'wpcloudplugins')."'><i class='eva eva-link eva-lg'></i>&nbsp;".esc_html__('Direct link', 'wpcloudplugins').'</a></li>';
        }

        // Shortlink
        if ($usercanshare) {
            $html .= "<li><a class='entry_action_shortlink' title='".esc_html__('Share', 'wpcloudplugins')."'><i class='eva eva-share-outline eva-lg'></i>&nbsp;".esc_html__('Share', 'wpcloudplugins').'</a></li>';
        }

        // Download
        $is_limit_reached = Restrictions::has_reached_download_limit($item->get_id()) ? 'disabled' : '';
        if ($usercanread) {
            if ($item->is_file()) {
                $target = ('url' === $item->get_extension() || 'web' === $item->get_extension()) ? 'target="_blank"' : '';
                $html .= "<li><a href='".OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-download&OutoftheBoxpath='.rawurlencode($item->get_path()).'&lastpath='.rawurlencode(Processor::instance()->get_last_path()).'&account_id='.App::get_current_account()->get_uuid().'&listtoken='.Processor::instance()->get_listtoken()."&dl=1' {$target} data-name='".$filename."' class='entry_action_download {$is_limit_reached}' title='".esc_html__('Download', 'wpcloudplugins')."'><i class='eva eva-download eva-lg'></i>&nbsp;".esc_html__('Download', 'wpcloudplugins').'</a></li>';
            } else {
                $html .= "<li><a class='entry_action_download {$is_limit_reached}' download='".$item->get_name()."' data-name='".$filename."' title='".esc_html__('Download', 'wpcloudplugins')."'><i class='eva eva-download eva-lg'></i>&nbsp;".esc_html__('Download', 'wpcloudplugins').'</a></li>';
            }
        }

        if (
            ($usercanpreview | $usercanread | $usercandeeplink | $usercanshare)
        && ($usercanrename || $usercanmove || $usercancopy)) {
            $html .= "<li class='list-separator'></li>";
        }

        if ($item->is_file() && '1' === Processor::instance()->get_shortcode_option('import')) {
            $html .= "<li><a class='entry_action_import' data-name='".$filename."' title='".esc_html__('Add to Media Library', 'wpcloudplugins')."'><i class='eva eva-log-in-outline eva-lg'></i>&nbsp;".esc_html__('Add to Media Library', 'wpcloudplugins').'</a></li>';

            $html .= "<li class='list-separator'></li>";
        }

        // Rename
        if ($usercanrename) {
            $html .= "<li><a class='entry_action_rename' title='".esc_html__('Rename', 'wpcloudplugins')."'><i class='eva eva-edit-2-outline eva-lg'></i>&nbsp;".esc_html__('Rename', 'wpcloudplugins').'</a></li>';
        }

        // Move
        if ($usercanmove) {
            $html .= "<li><a class='entry_action_move' title='".esc_html__('Move to', 'wpcloudplugins')."'><i class='eva eva-corner-down-right eva-lg'></i>&nbsp;".esc_html__('Move to', 'wpcloudplugins').'</a></li>';
        }

        // Copy
        if ($usercancopy) {
            $html .= "<li><a class='entry_action_copy' title='".esc_html__('Make a copy', 'wpcloudplugins')."'><i class='eva eva-copy-outline eva-lg'></i>&nbsp;".esc_html__('Make a copy', 'wpcloudplugins').'</a></li>';
        }

        // Delete
        if ($usercandelete) {
            $html .= "<li class='list-separator'></li>";
            $html .= "<li><a class='entry_action_delete' title='".esc_html__('Delete', 'wpcloudplugins')."'><i class='eva eva-trash-2-outline eva-lg'></i>&nbsp;".esc_html__('Delete', 'wpcloudplugins').'</a></li>';
        }

        $html = apply_filters('outofthebox_set_action_menu', $html, $item);

        if ('' !== $html) {
            return "<div class='entry-info-button entry-action-menu-button' title='".esc_html__('More actions', 'wpcloudplugins')."' tabindex='0'><i class='eva eva-more-vertical-outline'></i><div id='menu-".$item->get_id()."' class='entry-action-menu-button-content tippy-content-holder'><ul data-id='".$item->get_id()."' data-name='".$item->get_basename()."'>".$html."</ul></div></div>\n";
        }

        return $html;
    }

    public function renderNewFolder()
    {
        $return = '';

        if (
            false === User::can_add_folders()
            || false === $this->_folder->get_permission('canadd')
            || true === $this->_search
            || '1' === Processor::instance()->get_shortcode_option('show_breadcrumb')
        ) {
            return $return;
        }

        $icon_set = Settings::get('icon_set');
        $return .= "<div class='entry folder newfolder'>\n";
        $return .= "<div class='entry_block'>\n";
        $return .= "<div class='entry_thumbnail'><div class='entry_thumbnail-view-bottom'><div class='entry_thumbnail-view-center'>\n";
        $return .= "<a class='entry_link'><img class='preloading' src='".OUTOFTHEBOX_ROOTPATH."/css/images/transparant.png' data-src='".$icon_set.'128x128/folder-new.png'."' data-src-retina='".$icon_set.'256x256/folder-new.png'."'/></a>";
        $return .= "</div></div></div>\n";

        $return .= "<div class='entry-info'>";
        $return .= "<div class='entry-info-name'>";
        $return .= "<a href='javascript:void(0);' class='entry_link' title='".esc_html__('Add folder', 'wpcloudplugins')."'><div class='entry-name-view'>";
        $return .= '<span>'.esc_html__('Add folder', 'wpcloudplugins').'</span>';
        $return .= '</div></a>';
        $return .= "</div>\n";

        $return .= "</div>\n";
        $return .= "</div>\n";
        $return .= "</div>\n";

        return $return;
    }

    public static function render($attributes = [])
    {
        self::enqueue_scripts();

        $shortcode = Processor::instance()->get_shortcode();

        $attributes += [
            'data-list' => 'files',
            'data-query' => $shortcode['searchterm'],
            'data-lightboxnav' => $shortcode['lightbox_navigation'],
            'data-lightboxthumbs' => $shortcode['lightbox_thumbnails'],
            'data-layout' => $shortcode['filelayout'],
            'data-action' => $shortcode['popup'],
        ];

        echo "<div class='wpcp-module OutoftheBox files jsdisabled ".('grid' === $shortcode['filelayout'] ? 'wpcp-thumb-view' : 'wpcp-list-view')."' ".Module::parse_attributes($attributes).'  >';

        Password::render();
        LeadCapture::render();

        include sprintf('%s/templates/modules/file_browser.php', OUTOFTHEBOX_ROOTDIR);

        Upload::render();

        echo '</div>';
    }

    public static function render_search($attributes = [])
    {
        self::enqueue_scripts();

        $shortcode = Processor::instance()->get_shortcode();

        $attributes += [
            'data-list' => 'search',
            'data-query' => $shortcode['searchterm'],
            'data-lightboxnav' => $shortcode['lightbox_navigation'],
            'data-lightboxthumbs' => $shortcode['lightbox_thumbnails'],
            'data-layout' => $shortcode['filelayout'],
            'data-action' => $shortcode['popup'],
        ];

        echo "<div class='wpcp-module OutoftheBox files searchlist jsdisabled' ".Module::parse_attributes($attributes).'>';

        Password::render();

        include sprintf('%s/templates/modules/search.php', OUTOFTHEBOX_ROOTDIR);

        echo '</div>';
    }

    public static function enqueue_scripts()
    {
        if (true === self::$enqueued_scripts) {
            return;
        }

        Core::instance()->load_scripts();
        Core::instance()->load_styles();

        if (User::can_move_files() || User::can_move_folders()) {
            wp_enqueue_script('jquery-ui-droppable');
            wp_enqueue_script('jquery-ui-draggable');
        }

        wp_enqueue_script('jquery-effects-core');
        wp_enqueue_script('jquery-effects-fade');
        wp_enqueue_style('ilightbox');
        wp_enqueue_style('ilightbox-skin-outofthebox');

        self::$enqueued_scripts = true;
    }
}
