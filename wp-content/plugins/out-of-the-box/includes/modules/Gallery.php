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
use TheLion\OutoftheBox\Entry;
use TheLion\OutoftheBox\Helpers;
use TheLion\OutoftheBox\Processor;
use TheLion\OutoftheBox\Restrictions;
use TheLion\OutoftheBox\Settings;
use TheLion\OutoftheBox\User;

defined('ABSPATH') || exit;

class Gallery
{
    public static $enqueued_scripts = false;
    protected $_folder;
    protected $_items;
    protected $_search = false;

    public function get_images_list()
    {
        $recursive = ('1' === Processor::instance()->get_shortcode_option('folderthumbs'));
        $this->_folder = Client::instance()->get_folder(null, true, $recursive);

        if (false !== $this->_folder) {
            $this->renderImagesList();
        }
    }

    public function search_image_files()
    {
        if ('POST' !== $_SERVER['REQUEST_METHOD'] || !User::can_search()) {
            exit(-1);
        }

        $this->_search = true;
        $_REQUEST['query'] = wp_kses(stripslashes($_REQUEST['query']), 'strip');
        $this->_folder = Client::instance()->search($_REQUEST['query']);

        if (false !== $this->_folder) {
            $this->renderImagesList();
        }
    }

    public function setFolder($folder)
    {
        $this->_folder = $folder;
    }

    public function renderImagesList()
    {
        // Create HTML Filelist
        $imageslist_html = '';

        $filescount = 0;
        $folderscount = 0;

        // Add 'back to Previous folder' if needed
        if ((false === $this->_search) && (strtolower($this->_folder->get_path()) !== strtolower(Processor::instance()->get_root_folder()))) {
            $location = str_replace('\\', '/', dirname(Processor::instance()->get_requested_path()));

            $parent_folder_entry = new Entry();
            $parent_folder_entry->set_id('Previous Folder');
            $parent_folder_entry->set_name(esc_html__('Previous folder', 'wpcloudplugins'));
            $parent_folder_entry->set_path($location);
            $parent_folder_entry->set_path_display($location);
            $parent_folder_entry->set_is_dir(true);
            $parent_folder_entry->set_parent_folder(true);
            $parent_folder_entry->set_icon(Settings::get('icon_set').'128x128/prev.png');
        }

        if ('-1' !== Processor::instance()->get_shortcode_option('max_files') && $this->_folder->has_children()) {
            $children = $this->_folder->get_children();
            $children_sliced = array_slice($children, 0, (int) Processor::instance()->get_shortcode_option('max_files'));
            $this->_folder->set_children($children_sliced);
        }

        if ($this->_folder->has_children()) {
            $imageslist_html = "<div class='images image-collage'>";
            foreach ($this->_folder->get_children() as $item) {
                // Render folder div
                if ($item->is_dir()) {
                    $imageslist_html .= $this->renderDir($item);

                    if (!$item->is_parent_folder()) {
                        ++$folderscount;
                    }
                }
            }
        }

        $imageslist_html .= $this->renderNewFolder();

        if ($this->_folder->has_children()) {
            foreach ($this->_folder->get_children() as $item) {
                // Render file div
                if ($item->is_file()) {
                    $imageslist_html .= $this->renderFile($item);
                    ++$filescount;
                }
            }

            $imageslist_html .= '</div>';
        }

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

        $file_path .= "<li class='first-breadcrumb'><a href='javascript:void(0)' class='folder current_folder'  data-url='".rawurlencode('/')."'>{$root_text}</a></li>";

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

        $response = json_encode([
            'lastpath' => rawurlencode(Processor::instance()->get_last_path()),
            'accountId' => '0' === Processor::instance()->get_shortcode_option('popup') ? App::get_current_account()->get_uuid() : App::get_current_account()->get_id(),
            'lastFolder' => $this->_folder->get_id(),
            'virtual' => false,
            'breadcrumb' => $file_path,
            'folderscount' => $folderscount,
            'filescount' => $filescount,
            'html' => $imageslist_html,
        ]);

        $cached_request = new CacheRequest();
        $cached_request->add_cached_response($response);

        header('Content-Type: application/json');
        echo $response;

        exit;
    }

    public function getThumbnailsForDir(Entry $item, $thumbnails = [], $totalthumbs = 3)
    {
        if ($item->has_children()) {
            // First select the thumbnails in the folder itself
            foreach ($item->get_children() as $folder_child) {
                if (count($thumbnails) === $totalthumbs) {
                    return $thumbnails;
                }

                if (true === $folder_child->has_own_thumbnail()) {
                    $thumbnails[] = $folder_child;
                }
            }

            // Secondly select the thumbnails in the folder sub folders
            foreach ($item->get_children() as $folder_child) {
                if (count($thumbnails) === $totalthumbs) {
                    return $thumbnails;
                }

                if ($folder_child->is_dir()) {
                    $thumbnails = $this->getThumbnailsForDir($folder_child, $thumbnails, $totalthumbs);
                }
            }
        }

        return $thumbnails;
    }

    public function renderDir(Entry $item)
    {
        $return = '';

        $target_height = Processor::instance()->get_shortcode_option('targetheight');
        $target_width = round($target_height * (4 / 3));

        $has_access = $item->has_access();
        $accessible = ($has_access) ? '' : ' not-accessible ';

        if ($item->is_parent_folder()) {
            $return .= "<div class='image-container image-folder' data-id='".$item->get_id()."' data-url='".rawurlencode($item->get_path_display())."' data-name='".$item->get_basename()."'>";
        } else {
            $classmoveable = (User::can_move_folders()) ? 'moveable' : '';
            $return .= "<div class='image-container image-folder entry {$classmoveable} {$accessible}' data-id='".$item->get_id()."' data-url='".rawurlencode($item->get_path_display())."' data-name='".$item->get_basename()."'>";
        }
        $return .= "<a href='javascript:void(0);' title='".$item->get_name()."'>";
        $return .= "<div class='preloading'></div>";
        $return .= "<img class='image-folder-img' src='".OUTOFTHEBOX_ROOTPATH."/css/images/transparant.png' width='{$target_width}' height='{$target_height}' style='width:{$target_width}px !important;height:{$target_height}px !important; '/>";

        if ('1' === Processor::instance()->get_shortcode_option('folderthumbs')) {
            $thumbnail_entries = $this->getThumbnailsForDir($item);

            if (count($thumbnail_entries) > 0) {
                foreach (array_reverse($thumbnail_entries) as $key => $entry) {
                    $i = $key + 1;
                    $thumbnail_url = Client::instance()->get_thumbnail($entry, true, round($target_width * 1.5), round($target_height * 1.5));
                    $return .= "<div class='folder-thumb thumb{$i}' style='width:".$target_width.'px;height:'.$target_height.'px;background-image: url('.$thumbnail_url.")'></div>";
                }
            }
        }

        $text = $item->get_name();
        $text = apply_filters('outofthebox_gallery_entry_text', $text, $item, $this);
        $return .= "<div class='folder-text'><span><i class='eva eva-folder'></i>&nbsp;&nbsp;".$text.'</span></div>';

        $return .= '</a>';

        if (!$item->is_parent_folder() && $has_access) {
            $return .= "<div class='entry-info'>";
            $return .= $this->renderDescription($item);
            $return .= $this->renderButtons($item);
            $return .= $this->renderActionMenu($item);

            if (
                '1' === Processor::instance()->get_shortcode_option('show_header')
                 && (User::can_download_zip() || User::can_delete_files() || User::can_move_files() || User::can_copy_files())
            ) {
                $return .= "<div class='entry_checkbox entry-info-button '><input type='checkbox' name='selected-files[]' class='selected-files' value='".$item->get_id()."' id='checkbox-info-".Processor::instance()->get_listtoken()."-{$item->get_id()}'/><label for='checkbox-info-".Processor::instance()->get_listtoken()."-{$item->get_id()}'></label></div>";
            }

            $return .= '</div>';
        }

        $return .= "<div class='entry-top-actions'>";

        $return .= $this->renderButtons($item);
        $return .= $this->renderActionMenu($item);

        if (
            '1' === Processor::instance()->get_shortcode_option('show_header')
             && (User::can_download_zip() || User::can_delete_files() || User::can_move_files() || User::can_copy_files())
        ) {
            $return .= "<div class='entry_checkbox entry-info-button '><input type='checkbox' name='selected-files[]' class='selected-files' value='".$item->get_id()."' id='checkbox-".Processor::instance()->get_listtoken()."-{$item->get_id()}'/><label for='checkbox-".Processor::instance()->get_listtoken()."-{$item->get_id()}'></label></div>";
        }

        $return .= '</div>';

        $return .= "</div>\n";

        return $return;
    }

    public function renderFile(Entry $item)
    {
        $target_height = Processor::instance()->get_shortcode_option('targetheight');

        // API call doesn't return image sizes bu default, so initially crop the images to get this working inside the gallyer grid)
        $height = $target_height;
        $width = $target_height;

        $thumbnail_url = Client::instance()->get_thumbnail($item, true, 0, round($height * 1.5), true);

        // If we do have dimension data available, use that instead
        $cached_entry = Cache::instance()->get_node_by_id($item->get_id());
        if (!empty($cached_entry)) {
            $media_height = $cached_entry->get_media_info('height');
            $media_width = $cached_entry->get_media_info('width');

            if (!empty($media_height) && !empty($media_width)) {
                $width = round(($target_height / $media_height) * $media_width);
                $thumbnail_url = Client::instance()->get_thumbnail($item, true, 0, round($height * 1.5));
            }
        }

        $classmoveable = (User::can_move_files()) ? 'moveable' : '';
        $return = "<div class='image-container entry {$classmoveable}' data-id='".$item->get_id()."' data-url='".rawurlencode($item->get_path_display())."' data-name='".$item->get_name()."'>";

        $url = OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-preview&OutoftheBoxpath='.rawurlencode($item->get_id()).'&lastpath='.rawurlencode(Processor::instance()->get_last_path()).'&account_id='.App::get_current_account()->get_uuid().'&listtoken='.Processor::instance()->get_listtoken();

        if (Client::instance()->has_shared_link($item)) {
            $url = API::convert_to_raw_url(Client::instance()->get_shared_link($item), $item->get_extension());
        }

        $lightbox_type = 'image';
        $lightbox_data = 'data-options="thumbnail: \''.$thumbnail_url.'\'"';
        if (in_array($item->get_extension(), ['mp4', 'm4v', 'ogg', 'ogv', 'webmv'])) {
            $url = \str_replace('preview', 'stream', $url);
            $lightbox_data = 'data-options="thumbnail: \''.$thumbnail_url.'\', mousewheel:false, html5video:{h264:\''.$url.'\', poster: \''.$thumbnail_url.'\' ,preload:\'auto\'}, videoType:\''.$item->get_mimetype().'\'"';
            $lightbox_type = 'video';
        }

        $class = 'ilightbox-group';
        $target = '';

        // If previewinline attribute is set, open image in new window
        if ('0' === Processor::instance()->get_shortcode_option('previewinline')) {
            $url = API::convert_to_raw_url($url, $item->get_extension());
            $class = '';
            $target = ' target="_blank" ';
        }

        // Use preview thumbnail or raw  file
        if (
            ('default' === Processor::instance()->get_shortcode_option('lightbox_imagesource') && 'thumbnail' === Settings::get('loadimages'))
                    || 'thumbnail' === Processor::instance()->get_shortcode_option('lightbox_imagesource')
                    || false === User::can_download()) {
            $url = Client::instance()->get_thumbnail($item, true, 1024, 768);
        }

        $description = htmlentities($item->get_description(), ENT_QUOTES | ENT_HTML401);
        $data_description = ((!empty($item->description)) ? "data-caption='{$description}'" : '');

        $return .= "<a href='".$url."' title='".htmlspecialchars($item->get_name(), ENT_COMPAT | ENT_HTML401 | ENT_QUOTES)."' {$target} class='{$class}' data-type='{$lightbox_type}' data-entry-id='{$item->get_id()}' {$lightbox_data} rel='ilightbox[".Processor::instance()->get_listtoken()."]' {$data_description}>";

        $return .= "<div class='preloading'></div>";
        $return .= "<img referrerPolicy='no-referrer' class='preloading' alt='{$description}' src='".OUTOFTHEBOX_ROOTPATH."/css/images/transparant.png' data-src='".$thumbnail_url."' data-src-retina='".$thumbnail_url."' width='{$width}' height='{$height}' style='width:{$width}px !important;height:{$height}px !important; '/>";

        $text = '';
        if ('1' === Processor::instance()->get_shortcode_option('show_filenames')) {
            $text = $item->get_basename();
            $text = apply_filters('outofthebox_gallery_entry_text', $text, $item, $this);
            $return .= "<div class='entry-text'><span>".$text.'</span></div>';
        }

        $return .= '</a>';

        if (false === empty($item->description)) {
            $return .= '<div class="entry-inline-description '.('1' === Processor::instance()->get_shortcode_option('show_descriptions_on_top') ? ' description-visible ' : '').('1' === Processor::instance()->get_shortcode_option('show_filenames') ? ' description-above-name ' : '').'"><span>'.nl2br($item->get_description()).'</span></div>';
        }

        $return .= "<div class='entry-info' data-id='{$item->get_id()}'>";
        $return .= "<div class='entry-info-name'>";
        $caption = apply_filters('outofthebox_gallery_lightbox_caption', $item->get_basename(), $item, $this);
        $return .= '<span>'.$caption.'</span></div>';
        $return .= $this->renderButtons($item);
        $return .= "</div>\n";

        $return .= "<div class='entry-top-actions'>";

        if ('1' === Processor::instance()->get_shortcode_option('show_filenames')) {
            $return .= $this->renderDescription($item);
        }

        $return .= $this->renderButtons($item);
        $return .= $this->renderActionMenu($item);

        if (
            '1' === Processor::instance()->get_shortcode_option('show_header')
             && (User::can_download_zip() || User::can_delete_files() || User::can_move_files() || User::can_copy_files())
        ) {
            $return .= "<div class='entry_checkbox entry-info-button '><input type='checkbox' name='selected-files[]' class='selected-files' value='".$item->get_id()."' id='checkbox-".Processor::instance()->get_listtoken()."-{$item->get_id()}'/><label for='checkbox-".Processor::instance()->get_listtoken()."-{$item->get_id()}'></label></div>";
        }

        $return .= '</div>';

        $return .= "</div>\n";

        return $return;
    }

    public function renderDescription($item)
    {
        $html = '';

        if ($item->is_dir() && !$this->_search) {
            return $html;
        }

        $has_description = (false === empty($item->description));

        $metadata = [];
        if ('1' === Processor::instance()->get_shortcode_option('show_filedate')) {
            $metadata['modified'] = '<strong>'.esc_html__('Modified', 'wpcloudplugins').' <i class="eva eva-clock-outline"></i></strong><br/>'.$item->get_last_edited_str(false);
        }

        if ('1' === Processor::instance()->get_shortcode_option('show_filesize') && $item->get_size() > 0) {
            $metadata['size'] = '<strong>'.esc_html__('File Size', 'wpcloudplugins').' <i class="eva eva-info-outline"></i></strong><br/> '.Helpers::bytes_to_size_1024($item->get_size());
        }

        if (false === $has_description && empty($metadata) && !$this->_search) {
            return $html; // Don't display description button if there is no description and no metadata to display
        }

        $html .= "<div class='entry-info-button entry-description-button ".(($has_description) ? '-visible' : '')."' tabindex='0'><i class='eva eva-info-outline eva-lg'></i>\n";
        $html .= "<div class='tippy-content-holder'>";
        $html .= "<div class='description-textbox'>";
        $html .= "<div class='description-file-name'>".htmlspecialchars($item->get_name(), ENT_COMPAT | ENT_HTML401 | ENT_QUOTES, 'UTF-8').'</div>';
        $html .= ($has_description) ? "<div class='description-text'>".nl2br($item->get_description()).'</div>' : '';

        if (!empty($metadata)) {
            $html .= "<div class='description-file-info'>".implode('<br/><br/>', array_filter($metadata)).'</div>';
        }

        if ($this->_search) {
            $location = dirname($item->path_display);

            if (!empty($location)) {
                $html .= "<div class='description-file-info'><button class='button secondary folder search-location' data-url='".rawurlencode($location)."'><i class='eva eva-folder-outline'></i> ".basename($location).'</button></div>';
            }
        }

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function renderButtons($item)
    {
        $html = '';

        if (User::can_share()) {
            $html .= "<div class='entry-info-button entry_action_shortlink' title='".esc_html__('Share', 'wpcloudplugins')."' tabindex='0'><i class='eva eva-share-outline eva-lg'></i>\n";
            $html .= '</div>';
        }

        if (User::can_deeplink()) {
            $html .= "<div class='entry-info-button entry_action_deeplink' title='".esc_html__('Direct link', 'wpcloudplugins')."' tabindex='0'><i class='eva eva-link eva-lg'></i>\n";
            $html .= '</div>';
        }

        if (User::can_download() && $item->is_file()) {
            $is_limit_reached = Restrictions::has_reached_download_limit($item->get_id()) ? 'disabled' : '';
            $html .= "<div class='entry-info-button entry_action_download {$is_limit_reached}' title='".esc_html__('Download', 'wpcloudplugins')."' tabindex='0'><a href='".OUTOFTHEBOX_ADMIN_URL.'?action=outofthebox-download&OutoftheBoxpath='.rawurlencode($item->get_path()).'&lastpath='.rawurlencode(Processor::instance()->get_last_path()).'&account_id='.App::get_current_account()->get_uuid().'&listtoken='.Processor::instance()->get_listtoken()."&dl=1' download='".$item->get_name()."' class='entry_action_download' title='".esc_html__('Download', 'wpcloudplugins')."'><i class='eva eva-download eva-lg'></i></a>\n";
            $html .= '</div>';
        }

        return $html;
    }

    public function renderActionMenu($item)
    {
        $html = '';

        $usercanread = User::can_download() && ($item->is_file() || '1' === Processor::instance()->get_shortcode_option('can_download_zip'));
        $usercanrename = (($item->is_dir()) ? User::can_rename_folders() : User::can_rename_files()) && true === $item->get_permission('canrename');
        $usercanmove = (($item->is_dir()) ? User::can_move_folders() : User::can_move_files()) && true === $item->get_permission('canmove');
        $usercandelete = (($item->is_dir()) ? User::can_delete_folders() : User::can_delete_files()) && true === $item->get_permission('candelete');
        $usercancopy = (($item->is_dir()) ? User::can_copy_folders() : User::can_copy_files());

        // Download
        if ($usercanread && $item->is_dir() && '1' === Processor::instance()->get_shortcode_option('can_download_zip')) {
            $is_limit_reached = Restrictions::has_reached_download_limit($item->get_id()) ? 'disabled' : '';

            $html .= "<li><a class='entry_action_download {$is_limit_reached}' download='".$item->get_name()."' data-name='".$item->get_name()."' title='".esc_html__('Download', 'wpcloudplugins')."'><i class='eva eva-download eva-lg'></i>&nbsp;".esc_html__('Download', 'wpcloudplugins').'</a></li>';

            if ($usercanrename || $usercanmove) {
                $html .= "<li class='list-separator'></li>";
            }
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
        $html = '';

        if (
            false === User::can_add_folders()
            || false === $this->_folder->get_permission('canadd')
            || true === $this->_search
            || '1' === Processor::instance()->get_shortcode_option('show_breadcrumb')
        ) {
            return $html;
        }

        $height = Processor::instance()->get_shortcode_option('targetheight');
        $html .= "<div class='image-container image-folder image-add-folder grey newfolder'>";
        $html .= "<a title='".esc_html__('Add folder', 'wpcloudplugins')."'>";
        $html .= "<div class='folder-text'><span><i class='eva eva-folder-add-outline eva-lg'></i>&nbsp;&nbsp;".esc_html__('Add folder', 'wpcloudplugins').'</span></div>';
        $html .= "<img class='preloading' src='".OUTOFTHEBOX_ROOTPATH."/css/images/transparant.png' data-src='".plugins_url('css/images/gallery-add-folder.png', dirname(__FILE__))."' data-src-retina='".plugins_url('css/images/gallery-add-folder.png', dirname(__FILE__))."' width='{$height}' height='{$height}' style='width:".$height.'px;height:'.$height."px;'/>";
        $html .= '</a>';

        $html .= "</div>\n";

        return $html;
    }

    public static function render($attributes = [])
    {
        self::enqueue_scripts();

        $shortcode = Processor::instance()->get_shortcode();

        $attributes += [
            'data-list' => 'gallery',
            'data-query' => $shortcode['searchterm'],
            'data-lightboxnav' => $shortcode['lightbox_navigation'],
            'data-lightboxthumbs' => $shortcode['lightbox_thumbnails'],
            'data-lightboxopen' => $shortcode['lightbox_open'],
            'data-targetheight' => $shortcode['targetheight'],
            'data-slideshow' => $shortcode['slideshow'],
            'data-pausetime' => $shortcode['pausetime'],
        ];

        echo "<div class='wpcp-module OutoftheBox wpcp-gallery jsdisabled' ".Module::parse_attributes($attributes).'>';

        Password::render();

        include sprintf('%s/templates/modules/gallery.php', OUTOFTHEBOX_ROOTDIR);

        Upload::render();

        echo '</div>';
    }

    public static function enqueue_scripts()
    {
        Filebrowser::enqueue_scripts();
    }
}
