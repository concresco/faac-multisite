<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       3.1
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox\Modules;

use TheLion\OutoftheBox\Client;
use TheLion\OutoftheBox\Entry;
use TheLion\OutoftheBox\EntryAbstract;
use TheLion\OutoftheBox\Helpers;
use TheLion\OutoftheBox\Processor;
use TheLion\OutoftheBox\Settings;

defined('ABSPATH') || exit;

class Proofing extends Filebrowser
{
    public $has_labels = false;

    public function __construct()
    {
        $has_label_option = Processor::instance()->get_shortcode_option('proofing_use_labels');
        if ('1' === $has_label_option || ('' === $has_label_option && 'Yes' === Settings::get('proofing_use_labels'))) {
            $this->has_labels = true;
        }
    }

    public function renderFile(Entry $item)
    {
        $link = $this->renderFileNameLink($item);
        $title = $link['filename'].((('1' === Processor::instance()->get_shortcode_option('show_filesize')) && ($item->get_size() > 0)) ? ' ('.Helpers::bytes_to_size_1024($item->get_size()).')' : '');

        $thumbnail_url = ($item->has_own_thumbnail() ? Client::instance()->get_thumbnail($item, true, 640, 480) : $item->get_icon_retina());

        $return = '';
        $return .= "<div class='entry file' data-id='".$item->get_id()."' data-name='".htmlspecialchars($item->get_basename(), ENT_QUOTES | ENT_HTML401, 'UTF-8')."'>\n";
        $return .= "<div class='entry_block'>\n";

        $return .= "<div class='entry_thumbnail'><div class='entry_thumbnail-view-bottom'><div class='entry_thumbnail-view-center'>\n";
        $return .= "<div class='preloading'></div>";
        $return .= "<img class='preloading' src='".OUTOFTHEBOX_ROOTPATH."/css/images/transparant.png' data-src='".$thumbnail_url."' data-src-retina='".$thumbnail_url."' data-src-backup='".$item->get_icon_retina()."' alt='{$title}'/>";
        $return .= "</div></div></div>\n";

        $return .= "<div class='entry-info' data-id='".$item->get_id()."'>";
        $return .= "<div class='entry-info-name'>";

        $return .= '<a '.$link['url'].' '.$link['target']." class='entry_link ".$link['class']."' ".$link['onclick']." title='".$title."' ".$link['lightbox']." data-name='".$link['filename']."' data-entry-id='{$item->get_id()}'>";

        $return .= '<span>'.$link['filename'].'</span>';
        $return .= '</a>';

        $return .= '</div>';

        $return .= $this->renderModifiedDate($item);
        $return .= $this->renderSize($item);
        $return .= $this->renderLabel($item);

        $return .= $this->renderCheckBox($item);

        $return .= "</div>\n";

        $return .= $link['lightbox_inline'];

        $return .= "</div>\n";
        $return .= "</div>\n";

        return $return;
    }

    public function renderCheckBox(EntryAbstract $item)
    {
        if ($item->is_dir()) {
            return '';
        }

        return "<div class='entry-info-button entry_checkbox'><input type='checkbox' name='selected-files[]' class='selected-files' value='".$item->get_id()."' id='checkbox-".Processor::instance()->get_listtoken()."-{$item->get_id()}'/><label for='checkbox-".Processor::instance()->get_listtoken()."-{$item->get_id()}'></label></div>";
    }

    public function renderLabel(EntryAbstract $item)
    {
        if ($item->is_dir() || false === $this->has_labels) {
            return '';
        }

        return "<div class='entry-info-button entry-label-button' title='".esc_html__('Add Label', 'wpcloudplugins')."' tabindex='0'><i class='eva eva-bookmark eva-lg'></i></div>\n";
    }

    public static function render($attributes = [])
    {
        $attributes['data-max-items'] = Processor::instance()->get_shortcode_option('proofing_max_items');
        Filebrowser::render($attributes);

        self::enqueue_scripts();

        include_once sprintf('%s/templates/modules/proofing.php', OUTOFTHEBOX_ROOTDIR);
    }

    public static function enqueue_scripts()
    {
        wp_enqueue_script('OutoftheBox.Proofing');
    }
}
