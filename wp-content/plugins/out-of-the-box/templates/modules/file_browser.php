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
<div class="wpcp-browser-container wpcp-no-tree wpcp-browser--init" style="width:<?php echo $shortcode['maxwidth']; ?>;max-width:<?php echo $shortcode['maxwidth']; ?>;<?php echo (!empty($shortcode['maxheight'])) ? 'max-height:'.$shortcode['maxheight'].';overflow: hidden;' : ''; ?>">
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
        <?php
        if ('1' === $shortcode['show_header']) {
            ?>
        <div class="nav-header <?php echo ('1' === $shortcode['show_breadcrumb']) ? 'nav-header-has-breadcrumb' : ''; ?>"><?php if ('1' === $shortcode['show_breadcrumb']) { ?>
            <a class="nav-tree-toggle entry-info-button" tabindex="0" title="<?php esc_html_e('Display folder tree', 'wpcloudplugins'); ?>">
                <i class="eva eva-arrowhead-left-outline"></i>
            </a>
            <a class="nav-home entry-info-button" title="<?php esc_html_e('Go back to the start folder', 'wpcloudplugins'); ?>" tabindex="0">
                <i class="eva eva-home-outline"></i>
            </a>
            <div class="nav-title"><?php esc_html_e('Loading...', 'wpcloudplugins'); ?></div>
            <?php
        }
            if (User::can_search()) {
                ?>
            <a class="nav-search entry-info-button" tabindex="0">
                <i class="eva eva-search"></i>
            </a>

            <div class="tippy-content-holder search-wrapper">
                <a class="search-icon search-submit" href="javascript:;"><i class="eva eva-search"></i></a>
                <input class="search-input" name="q" type="search" autocomplete="off" size="40" aria-label="<?php esc_html_e('Search', 'wpcloudplugins'); ?>" placeholder="<?php echo esc_html__('Search for files', 'wpcloudplugins').(('1' === $shortcode['searchcontents'] && '1' === $shortcode['show_files']) ? ' '.esc_html__('and content', 'wpcloudplugins') : ''); ?>" />
                <a class="search-remove" href="javascript:;" title="<?php esc_html_e('Clear', 'wpcloudplugins'); ?>"><i class="eva eva-close"></i></a>
            </div>
            <?php
            } ?>

            <a class="nav-sort entry-info-button" title="<?php esc_html_e('Sort options', 'wpcloudplugins'); ?>">
                <i class="eva eva-options"></i>
            </a>
            <div class="tippy-content-holder sort-div">
                <ul class='nav-sorting-list'>
                    <li><a class="nav-sorting-field nav-name <?php echo ('name' === $shortcode['sort_field']) ? 'sort-selected' : ''; ?>" data-field="name" title="<?php esc_html_e('Name', 'wpcloudplugins'); ?>">
                            <?php esc_html_e('Name', 'wpcloudplugins'); ?>
                        </a>
                    </li>
                    <li><a class="nav-sorting-field nav-size <?php echo ('size' === $shortcode['sort_field']) ? 'sort-selected' : ''; ?>" data-field="size" title="<?php esc_html_e('Size', 'wpcloudplugins'); ?>">
                            <?php esc_html_e('Size', 'wpcloudplugins'); ?>
                        </a>
                    </li>
                    <li><a class="nav-sorting-field nav-modified <?php echo ('modified' === $shortcode['sort_field']) ? 'sort-selected' : ''; ?>" data-field="modified" title="<?php esc_html_e('Modified', 'wpcloudplugins'); ?>">
                            <?php esc_html_e('Modified', 'wpcloudplugins'); ?>
                        </a>
                    </li>
                    <li class="list-separator">&nbsp;</li>
                    <li><a class="nav-sorting-order nav-asc <?php echo ('asc' === $shortcode['sort_order']) ? 'sort-selected' : ''; ?>" data-order="asc" title="<?php esc_html_e('Ascending', 'wpcloudplugins'); ?>">
                            <?php esc_html_e('Ascending', 'wpcloudplugins'); ?>
                        </a>
                    </li>
                    <li><a class="nav-sorting-order nav-desc <?php echo ('desc' === $shortcode['sort_order']) ? 'sort-selected' : ''; ?>" data-order="desc" title="<?php esc_html_e('Descending', 'wpcloudplugins'); ?>">
                            <?php esc_html_e('Descending', 'wpcloudplugins'); ?>
                        </a>
                    </li>
                </ul>
            </div>

            <a class="nav-gear entry-info-button" title="<?php esc_html_e('More actions', 'wpcloudplugins'); ?>">
                <i class="eva eva-more-vertical-outline"></i>
            </a>
            <div class="tippy-content-holder gear-div" data-token="<?php echo Processor::instance()->get_listtoken(); ?>">
                <ul data-id="<?php echo Processor::instance()->get_listtoken(); ?>">

                    <?php
                $need_separator = false;

            if (User::can_add_folders()) {
                $need_separator = true; ?>
                    <li><a class="nav-new-folder newfolder" data-mimetype='' title="<?php esc_html_e('Add folder', 'wpcloudplugins'); ?>"><i class="eva eva-folder-add-outline eva-lg"></i><?php esc_html_e('Add folder', 'wpcloudplugins'); ?></a></li>
                    <?php
            }

            if (User::can_create_document()) {
                $need_separator = true; ?>
                    <li class="has-menu">
                        <a class="nav-new-entry" title="<?php esc_html_e('Create document', 'wpcloudplugins'); ?>"><i class="eva eva-file-add-outline eva-lg"></i><?php esc_html_e('Create document', 'wpcloudplugins'); ?><i class="eva eva-chevron-right eva-lg"></i></a>
                        <ul>
                        </ul>
                    </li>
                    <?php
            }

            if (User::can_upload()) {
                $need_separator = true; ?>
                    <li><a class="nav-upload" title="<?php esc_html_e('Upload files', 'wpcloudplugins'); ?>"><i class="eva eva-upload-outline eva-lg "></i><?php esc_html_e('Upload files', 'wpcloudplugins'); ?></a></li>
                    <?php
            }

            if (User::can_move_folders() || User::can_move_files() || User::can_delete_files() || User::can_delete_folders() || User::can_download_zip() || in_array(Processor::instance()->get_shortcode_option('popup'), ['links', 'embedded', 'selector'])) {
                if ($need_separator) {
                    ?><li class='list-separator'></li><?php
                }

                $need_separator = true; ?>
                    <li><a class='select-all' title='" <?php esc_html_e('Select all', 'wpcloudplugins'); ?>"'><i class='eva eva-radio-button-on eva-lg'></i><?php esc_html_e('Select all', 'wpcloudplugins'); ?></a></li>
                    <li style="display:none"><a class='deselect-all' title='" <?php esc_html_e('Deselect all', 'wpcloudplugins'); ?>"'><i class='eva eva-radio-button-off eva-lg'></i><?php esc_html_e('Deselect all', 'wpcloudplugins'); ?></a></li>
                    <?php
            }

            if (User::can_download_zip()) {
                $need_separator = true; ?>
                    <li><a class="all-files-to-zip" download><i class='eva eva-download eva-lg'></i><?php esc_html_e('Download folder', 'wpcloudplugins'); ?></a></li>
                    <li><a class="selected-files-to-zip" download><i class='eva eva-download eva-lg'></i><?php esc_html_e('Download selected', 'wpcloudplugins'); ?></a></li>
                    <?php
            }

            if (User::can_move_folders() || User::can_move_files()) {
                $need_separator = true; ?>
                    <li><a class='selected-files-move' title='" <?php esc_html_e('Move to', 'wpcloudplugins'); ?>"'><i class='eva eva-corner-down-right eva-lg'></i><?php esc_html_e('Move to', 'wpcloudplugins'); ?></a></li>
                    <?php
            }

            if (User::can_copy_folders() || User::can_copy_files()) {
                $need_separator = true; ?>
                    <li><a class='selected-files-copy' title='" <?php esc_html_e('Copy to', 'wpcloudplugins'); ?>"'><i class='eva eva-copy-outline eva-lg'></i><?php esc_html_e('Copy to', 'wpcloudplugins'); ?></a></li>
                    <?php
            }

            if (User::can_delete_files() || User::can_delete_folders()) {
                $need_separator = true; ?>
                    <li><a class="selected-files-delete" title="<?php esc_html_e('Delete', 'wpcloudplugins'); ?>"><i class="eva eva-trash-2-outline eva-lg "></i><?php esc_html_e('Delete', 'wpcloudplugins'); ?></a></li>
                    <?php
            }

            if (User::can_deeplink()) {
                $need_separator = true; ?>
                    <li><a class='entry_action_deeplink_folder' title='<?php esc_html_e('Direct link', 'wpcloudplugins'); ?>'><i class='eva eva-link eva-lg'></i><?php esc_html_e('Direct link', 'wpcloudplugins'); ?></a></li>
                    <?php
            }
            if (User::can_share()) {
                $need_separator = true; ?>
                    <li><a class='entry_action_shortlink_folder' title='<?php esc_html_e('Share folder', 'wpcloudplugins'); ?>'><i class='eva eva-share-outline eva-lg'></i><?php esc_html_e('Share folder', 'wpcloudplugins'); ?></a></li>
                    <?php
            }

            if (User::can_import()) {
                $need_separator = true; ?>
                    <li class='list-separator'></li>
                    <li><a class='selected-files-import' title='" <?php esc_html_e('Add to Media Library', 'wpcloudplugins'); ?>"'><i class='eva eva-log-in-outline eva-lg'></i><?php esc_html_e('Add to Media Library', 'wpcloudplugins'); ?></a></li>
                    <?php
            }

            if ($need_separator) {
                ?><li class='list-separator'></li><?php
            }

            if ('1' === $shortcode['allow_switch_view']) {
                ?>
                    <li style="display: none"><a class="nav-layout nav-layout-grid" title="<?php esc_html_e('Thumbnails view', 'wpcloudplugins'); ?>">
                            <i class="eva eva-grid-outline eva-lg"></i><?php esc_html_e('Thumbnails view', 'wpcloudplugins'); ?>
                        </a>
                    </li>
                    <li><a class="nav-layout nav-layout-list" title="<?php esc_html_e('List view', 'wpcloudplugins'); ?>">
                            <i class="eva eva-list-outline eva-lg"></i><?php esc_html_e('List view', 'wpcloudplugins'); ?>
                        </a>
                    </li>
                    <?php
            }
            ?>

                    <li class='gear-menu-no-options' style="display: none"><a><i class='eva eva-info-outline eva-lg '></i><?php esc_html_e('No options...', 'wpcloudplugins'); ?></a></li>
                </ul>
            </div>
            <?php
            if ('1' === $shortcode['show_refreshbutton']) {
                ?>
            <a class="nav-refresh entry-info-button" title="<?php esc_html_e('Refresh', 'wpcloudplugins'); ?>">
                <i class="eva eva-sync"></i>
            </a>
            <?php
            }

            if ('0' === $shortcode['single_account']) {
                $current_account = Accounts::instance()->get_account_by_id($attributes['data-account-id']);
                $primary_account = Accounts::instance()->get_primary_account();

                if (null === $current_account) {
                    echo "<div class='nav-account-selector' data-account-id='{$primary_account->get_uuid()}' title='{$primary_account->get_name()}'><img src='{$primary_account->get_image()}' onerror='this.src=\"".OUTOFTHEBOX_ROOTPATH."/css/images/usericon.png\"' /><div class='nav-account-selector-info'><div class='nav-account-selector-name'>{$primary_account->get_name()}</div><div class='nav-account-selector-email'>{$primary_account->get_email()}</div></div></div>\n";
                } else {
                    echo "<div class='nav-account-selector' data-account-id='{$current_account->get_uuid()}' title='{$current_account->get_name()}'><img src='{$current_account->get_image()}' onerror='this.src=\"".OUTOFTHEBOX_ROOTPATH."/css/images/usericon.png\"' /><div class='nav-account-selector-info'><div class='nav-account-selector-name'>{$current_account->get_name()}</div><div class='nav-account-selector-email'>{$current_account->get_email()}</div></div></div>\n";
                }

                echo "<div class='nav-account-selector-content'>";

                foreach (Accounts::instance()->list_accounts() as $account_id => $account) {
                    $is_active = ($account_id === $attributes['data-account-id'] || $account->get_uuid() === $attributes['data-account-id']) ? 'account-active' : '';
                    echo "<div class='nav-account-selector {$is_active}' data-account-id='{$account->get_uuid()}' title='{$account->get_name()}'><img src='{$account->get_image()}' onerror='this.src=\"".OUTOFTHEBOX_ROOTPATH."/css/images/usericon.png\"' /><div class='nav-account-selector-info'><div class='nav-account-selector-name'>{$account->get_name()} <span>{$account->get_type()}</span></div><div class='nav-account-selector-email'>{$account->get_email()}</div></div><i class='nav-account-selector-tick eva eva-checkmark-outline eva-lg'></i></div>\n";
                }
                echo '</div>';
            } ?>
        </div><?php
        } ?>
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
            <div class="ajax-filelist" style="<?php echo (!empty($shortcode['maxheight'])) ? 'max-height:'.$shortcode['maxheight'].';overflow-y: scroll;overflow-x: hidden;' : ''; ?>">
                <div class='files'>
                    <div class="folders-container"><?php Skeleton::get_browser_placeholders('folder', 3); ?></div>
                    <div class="files-container"><?php Skeleton::get_browser_placeholders('file', 5); ?></div>
                </div>
            </div>
            <div class="scroll-to-top">
                <button class="scroll-to-top-action button button-round-icon secondary button-round-icon-lg button-shadow-3" type="button" title="<?php esc_html_e('Scroll to top', 'wpcloudplugins'); ?>" aria-expanded="false"><i class="eva eva-arrow-upward-outline eva-2x"></i></button>
                <?php
            if (User::can_upload()) {
                ?>
                <button class="fileupload-add-button button button-round-icon button-round-icon-lg button-shadow-3" type="button" title="<?php esc_html_e('Upload files', 'wpcloudplugins'); ?>"><i class="eva eva-plus-outline eva-2x"></i></button>
                <?php } ?>
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