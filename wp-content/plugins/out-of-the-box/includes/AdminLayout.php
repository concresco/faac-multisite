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

class AdminLayout
{
    public static $setting_value_location = 'database';

    public static function render_field($field_key, $field)
    {
        $field_type = $field['type'];
        $field['key'] = $field_key;

        if (isset($field['fields'])) {
            foreach ($field['fields'] as $child_field_key => $child_field) {
                $field['fields'][$child_field_key]['value'] = $child_field['default'] ?? null;
                if (null !== self::get_setting_value($child_field_key, $child_field)) {
                    $field['fields'][$child_field_key]['value'] = self::get_setting_value($child_field_key, $child_field);
                }
            }
        }

        if (method_exists(__CLASS__, 'render_simple_'.$field_type)) {
            self::{'render_simple_'.$field_type}($field);

            return;
        }
        if (method_exists(__CLASS__, 'render_'.$field_type)) {
            self::{'render_'.$field_type}($field);

            return;
        }
        if ('panel' === $field_type) {
            AdminLayout::render_open_panel($field);

            foreach ($field['fields'] as $child_field_key => $child_field) {
                self::render_field($child_field_key, $child_field);
            }

            AdminLayout::render_close_panel();

            return;
        }

        if ('toggle_container' === $field_type) {
            AdminLayout::render_open_toggle_container($field);

            foreach ($field['fields'] as $child_field_key => $child_field) {
                self::render_field($child_field_key, $child_field);
            }

            AdminLayout::render_close_toggle_container();

            return;
        }

        do_action('outofthebox_render_setting', $field_type, $field);
    }

    public static function render_nav_tab($settings)
    {
        $icon = $settings['icon_svg'] ?? '<path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />'; ?>
<a href="#" data-nav-tab="wpcp-<?php echo $settings['key']; ?>" class="hover:bg-gray-50 hover:text-brand-color-900 group active:text-brand-color-900 focus:text-brand-color-900 group flex items-center px-2 py-1 text-sm font-medium rounded-md focus:outline-hidden focus:ring-1 focus:ring-offset-1 focus:ring-brand-color-900 <?php echo self::get_modules_classes($settings); ?>">
    <svg class="text-gray-400 group-hover:text-brand-color-900 active:text-brand-color-900 focus:text-brand-color-900 mr-3 shrink-0 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
        <?php echo $icon; ?>
    </svg>
    <?php
    echo $settings['title'];

        if (isset($settings['beta'])) {
            ?>
    <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900 italic">
        <?php echo 'Beta'; ?>
    </span>
    <?php
        }
        ?>
</a>
<?php
    }

    public static function render_nav_panel_open($settings)
    {
        ?>
<div data-nav-panel="wpcp-<?php echo $settings['key']; ?>" class="!hidden duration-200 space-y-6">
    <?php
    }

    public static function render_nav_panel_close()
    {
        ?>
</div>
<?php
    }

    public static function render_open_panel($settings)
    {
        $is_accordion = $settings['accordion'] ?? false; ?>
<div id="<?php echo isset($settings['key']) ? 'wpcp-'.$settings['key'] : ''; ?>" class="wpcp-panel bg-white shadow-(--shadow-5) sm:rounded-md mb-6 <?php echo self::get_modules_classes($settings); ?>">
    <div class="px-4 py-5 sm:p-6">
        <div class="wpcp-panel-header cursor-pointer">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-2xl font-semibold text-gray-900 flex items-center">
                        <?php
                        echo $settings['title'];

        if (isset($settings['beta'])) {
            ?>
                        <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900 italic">
                            <?php echo 'Beta'; ?>
                        </span>
                        <?php
        }
        ?>
                    </h3>
                    <?php
                    if (!empty($settings['description'])) {
                        ?>
                    <div class="text-base text-gray-500 max-w-xl py-4"><?php echo $settings['description']; ?></div>
                    <?php
                    } ?>
                </div>
                <?php if ($is_accordion) {
                    ?>
                <div class="shrink-0 mt-1 ml-4 h-6 w-6">
                    <div class='wpcp-panel-header-opened block'>
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                        </svg>
                    </div>
                    <div class='wpcp-panel-header-closed block'>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
                <?php
                } ?>
            </div>
        </div>
        <div class="wpcp-panel-content <?php echo ($is_accordion) ? 'wpcp-panel-accordion mt-6' : ''; ?> ">
            <?php
    }

    public static function render_close_panel()
    {
        ?>
        </div>
    </div>
</div>
<?php
    }

    public static function render_open_toggle_container($settings)
    {
        $margin_left = empty($settings['indent']) ? '' : 'ml-8';
        ?>
<div id="<?php echo esc_attr($settings['key']); ?>" class="wpcp-toggle-panel mb-6 mt-3 border-l-4 <?php echo $margin_left; ?> border-brand-color-700 <?php echo self::get_modules_classes($settings); ?>">
    <div class="px-6 sm:py-3 -mt-2 bg-gray-300/10">
        <?php
    }

    public static function render_close_toggle_container()
    {
        ?>
    </div>
</div>
<?php
    }

    public static function render_simple_textbox($settings)
    {
        $db_value = $settings['value'] ?? self::get_setting_value($settings['key'], $settings);

        $placeholder = $settings['default'] ?? '';
        if (isset($settings['placeholder'])) {
            $placeholder = $settings['placeholder'];
        }
        if (empty($db_value) && false === empty($settings['deprecated'])) {
            return;
        }
        ?>

<div class="mt-2 mb-4 sm:flex sm:items-start sm:justify-between <?php echo self::get_modules_classes($settings); ?>">
    <div class="flex-grow flex flex-col gap-2 max-w-xl">
        <div class="text-base text-gray-900 flex items-center">
            <?php echo $settings['title'];

        if (isset($settings['account_types'])) {
            foreach ($settings['account_types'] as $account_type) {
                ?>
            <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900">
                <svg class="mr-1.5 h-2 w-2 text-brand-color-900" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                Dropbox <?php echo ucfirst($account_type); ?>
            </span>
            <?php
            }
        }
        if (isset($settings['tags'])) {
            foreach ($settings['tags'] as $tags) {
                foreach ($tags as $tag) {
                    ?>
            <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900">
                <svg class="mr-1.5 h-2 w-2 text-brand-color-900" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                <?php echo ucfirst($tag); ?>
            </span>
            <?php
                }
            }
        } ?>
        </div>
        <input type="text" name="<?php echo esc_attr($settings['key']); ?>" id="<?php echo esc_attr($settings['key']); ?>" class="wpcp-input-textbox block w-full shadow-xs focus:ring-brand-color-700 focus:border-brand-color-700 sm:text-sm border border-gray-300 p-2 rounded-md" value="<?php echo esc_attr($db_value); ?>" data-default-value="<?php echo esc_attr($settings['default']); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" autocomplete="off">
        <div class="text-sm text-gray-400"><?php echo $settings['description']; ?></div>
        <?php if (isset($settings['notice'])) {
            self::render_notice($settings['notice'], $settings['notice_class']);
        } ?>
    </div>
</div>
<?php
    }

    public static function render_simple_textarea($settings)
    {
        $db_value = $settings['value'] ?? self::get_setting_value($settings['key'], $settings);

        if (empty($db_value) && false === empty($settings['deprecated'])) {
            return;
        }
        ?>

<div class="mt-2 mb-4 sm:flex sm:justify-between flex-col gap-4 <?php echo self::get_modules_classes($settings); ?>">
    <div class="flex-grow flex flex-col max-w-xl">
        <div class="text-base text-gray-900 flex items-center">
            <?php echo $settings['title'];

        if (isset($settings['account_types'])) {
            foreach ($settings['account_types'] as $account_type) {
                ?>
            <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900">
                <svg class="mr-1.5 h-2 w-2 text-brand-color-900" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                Dropbox <?php echo ucfirst($account_type); ?>
            </span>
            <?php
            }
        }
        if (isset($settings['tags'])) {
            foreach ($settings['tags'] as $tags) {
                foreach ($tags as $tag) {
                    ?>
            <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900">
                <svg class="mr-1.5 h-2 w-2 text-brand-color-900" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                <?php echo ucfirst($tag); ?>
            </span>
            <?php
                }
            }
        } ?>
        </div>
        <div class="text-sm text-gray-400"><?php echo $settings['description']; ?></div>
        <?php if (isset($settings['notice'])) {
            self::render_notice($settings['notice'], $settings['notice_class']);
        } ?>
    </div>
    <div class="shrink-0 flex grow">
        <textarea rows="<?php echo esc_attr($settings['rows']); ?> " type="text" name="<?php echo esc_attr($settings['key']); ?>" id="<?php echo esc_attr($settings['key']); ?>" data-default-value="<?php echo esc_attr($settings['default']); ?>" class="wpcp-input-textarea max-w-xl block w-full shadow-xs focus:ring-brand-color-700 focus:border-brand-color-700 sm:text-sm border border-gray-300 rounded-md"><?php echo esc_html($db_value); ?></textarea>
    </div>
</div>
<?php
    }

    public static function render_simple_number($settings)
    {
        $db_value = $settings['value'] ?? self::get_setting_value($settings['key'], $settings);
        $placeholder = $settings['default'] ?? '';
        if (isset($settings['placeholder'])) {
            $placeholder = $settings['placeholder'];
        }

        if (empty($db_value) && false === empty($settings['deprecated'])) {
            return;
        }

        $step = esc_attr($settings['step'] ?? 1);
        $max = esc_attr($settings['max'] ?? null);
        $min = esc_attr($settings['min'] ?? 0);

        $width = esc_attr($settings['width'] ?? 'w-20');

        $icon_svg = self::render_icon($settings); ?>

<div class="mt-2 mb-3 sm:flex sm:items-start sm:justify-between <?php echo self::get_modules_classes($settings); ?>">
    <div class="flex-grow flex flex-col max-w-xl">
        <div class="text-base text-gray-900 flex items-center">
            <?php echo $settings['title'];

        if (isset($settings['account_types'])) {
            foreach ($settings['account_types'] as $account_type) {
                ?>
            <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900">
                <svg class="mr-1.5 h-2 w-2 text-brand-color-900" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                Dropbox <?php echo ucfirst($account_type); ?>
            </span>
            <?php
            }
        }
        if (isset($settings['tags'])) {
            foreach ($settings['tags'] as $tags) {
                foreach ($tags as $tag) {
                    ?>
            <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900">
                <svg class="mr-1.5 h-2 w-2 text-brand-color-900" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                <?php echo ucfirst($tag); ?>
            </span>
            <?php
                }
            }
        } ?>
        </div>
        <div class="text-sm text-gray-400 <?php echo !empty($icon_svg) ? 'ml-8' : ''; ?>"><?php echo $settings['description']; ?></div>
        <?php if (isset($settings['notice'])) {
            self::render_notice($settings['notice'], $settings['notice_class']);
        } ?>
    </div>

    <input type="number" name="<?php echo esc_attr($settings['key']); ?>" id="<?php echo esc_attr($settings['key']); ?>" class="wpcp-input-textbox <?php echo $width; ?> shadow-xs focus:ring-brand-color-700 focus:border-brand-color-700 sm:text-sm border border-gray-300 p-2 rounded-md" value="<?php echo esc_attr($db_value); ?>" data-default-value="<?php echo esc_attr($settings['default']); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" step="<?php echo $step; ?>" <?php echo !is_null($min) ? " min='{$min}'" : ''; ?> <?php echo !is_null($max) ? " max='{$max}'" : ''; ?>>
</div>
<?php
    }

    public static function render_wpeditor($settings, $code_editor = false, $code_editor_settings = [])
    {
        $db_value = self::get_setting_value($settings['key'], $settings);
        $db_value = (empty($db_value) ? $settings['default'] : $db_value);

        $wpeditor_settings = $settings['wpeditor'];
        $wpeditor_settings['editor_class'] = 'wpcp-input-wpeditor block w-full sm:text-sm rounded-md bg-gray-50 '.(($code_editor) ? 'wpcp-input-codemirror' : '');

        if (empty($db_value) && false === empty($settings['deprecated'])) {
            return;
        }
        ?>

<div class="mt-2 mb-4 sm:flex sm:justify-between flex-col <?php echo self::get_modules_classes($settings); ?>">
    <div class="flex-grow flex flex-col max-w-xl">
        <div class="text-base text-gray-900 flex items-center">
            <?php echo $settings['title'];

        if (isset($settings['account_types'])) {
            foreach ($settings['account_types'] as $account_type) {
                ?>
            <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900">
                <svg class="mr-1.5 h-2 w-2 text-brand-color-900" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                <?php echo ucfirst($account_type); ?>
            </span>
            <?php
            }
        }
        if (isset($settings['tags'])) {
            foreach ($settings['tags'] as $tags) {
                foreach ($tags as $tag) {
                    ?>
            <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900">
                <svg class="mr-1.5 h-2 w-2 text-brand-color-900" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                <?php echo ucfirst($tag); ?>
            </span>
            <?php
                }
            }
        } ?>
        </div>
        <div class="text-sm text-gray-400"><?php echo $settings['description']; ?></div>
        <?php if (isset($settings['notice'])) {
            self::render_notice($settings['notice'], $settings['notice_class']);
        } ?>
    </div>
    <div class="shrink-0 grow mt-4">
        <?php
              ob_start();
        wp_editor(esc_textarea($db_value), $settings['key'], $wpeditor_settings);
        echo ob_get_clean();

        if ($code_editor) {
            wp_enqueue_code_editor($code_editor_settings);
        }
        ?>
    </div>
</div>
<?php
    }

    public static function render_simple_select($settings)
    {
        $db_value = $settings['value'] ?? self::get_setting_value($settings['key'], $settings);
        $first_value = reset($settings['options']);
        $has_toggle = isset($first_value['toggle_container']);
        $is_ddslickbox = isset($settings['type']) && 'ddslickbox' === $settings['type'];

        if (empty($db_value) && false === empty($settings['deprecated'])) {
            return;
        }

        $db_value = ((empty($db_value) && !empty($settings['default'])) ? $settings['default'] : $db_value);

        ?>
<div class="mt-2 <?php echo !$has_toggle ? 'mb-4' : 'mb-2'; ?> sm:flex sm:items-start sm:justify-between <?php echo self::get_modules_classes($settings); ?>">
    <div class="flex-grow flex flex-col gap-2 max-w-xl">
        <div class="text-base text-gray-900 flex items-center">
            <?php echo $settings['title'];

        if (isset($settings['account_types'])) {
            foreach ($settings['account_types'] as $account_type) {
                ?>
            <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900">
                <svg class="mr-1.5 h-2 w-2 text-brand-color-900" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                Dropbox <?php echo ucfirst($account_type); ?>
            </span>
            <?php
            }
        }
        if (isset($settings['tags'])) {
            foreach ($settings['tags'] as $tags) {
                foreach ($tags as $tag) {
                    ?>
            <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900">
                <svg class="mr-1.5 h-2 w-2 text-brand-color-900" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                <?php echo ucfirst($tag); ?>
            </span>
            <?php
                }
            }
        } ?>
        </div>
        <div>
            <select id="<?php echo esc_attr($settings['key']); ?>" name="<?php echo esc_attr($settings['key']); ?>" class="<?php echo $is_ddslickbox ? 'ddslickbox' : ''; ?> wpcp-input-select block w-full shadow-xs text-base focus:outline-hidden focus:ring-brand-color-700 focus:border-brand-color-700 sm:text-sm border border-gray-300 rounded-md p-2" data-default-value="<?php echo esc_attr($settings['default']); ?>">
                <?php
                  foreach ($settings['options'] as $value => $item) {
                      $selected = ($value === $db_value) ? 'selected="selected"' : '';
                      $disabled = isset($item['disabled']) && $item['disabled'] ? 'disabled="disabled"' : '';
                      $toggle_element = $item['toggle_container'] ?? ''; ?>
                <option value="<?php echo esc_attr($value); ?>" <?php echo $selected; ?> <?php echo $disabled; ?> data-toggle-element="<?php echo $toggle_element; ?>" data-description="" data-imagesrc="<?php echo $is_ddslickbox ? $item['imagesrc'] : ''; ?>"><?php echo $item['title']; ?></option>
                <?php
                  } ?>
            </select>
            <?php
                if ($is_ddslickbox) {
                    ?>
            <input type="hidden" name="<?php echo esc_attr($settings['key']); ?>" id="<?php echo esc_attr($settings['key']); ?>" value="<?php echo esc_attr($db_value); ?>" class="wpcp-input-hidden" data-default-value="<?php echo esc_attr($settings['default']); ?>">
            <?php
                } ?>
        </div>
        <div class="text-sm text-gray-400"><?php echo $settings['description']; ?></div>
        <?php if (isset($settings['notice'])) {
            self::render_notice($settings['notice'], $settings['notice_class']);
        } ?>
    </div>
</div>
<?php
    }

    public static function render_simple_radio_group($settings)
    {
        $db_value = $settings['value'] ?? self::get_setting_value($settings['key'], $settings);
        $db_value = (empty($db_value) ? $settings['default'] : $db_value);

        if (empty($db_value) && false === empty($settings['deprecated'])) {
            return;
        }
        ?>

<div class="mt-2 mb-4 sm:flex sm:items-start sm:justify-between <?php echo self::get_modules_classes($settings); ?>">
    <div class="flex-grow flex flex-col max-w-full">
        <div class="text-base text-gray-900 flex items-center">
            <?php echo $settings['title'];

        if (isset($settings['account_types'])) {
            foreach ($settings['account_types'] as $account_type) {
                ?>
            <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900">
                <svg class="mr-1.5 h-2 w-2 text-brand-color-900" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                <?php echo ucfirst($account_type); ?>
            </span>
            <?php
            }
        }
        if (isset($settings['tags'])) {
            foreach ($settings['tags'] as $tags) {
                foreach ($tags as $tag) {
                    ?>
            <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900">
                <svg class="mr-1.5 h-2 w-2 text-brand-color-900" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                <?php echo ucfirst($tag); ?>
            </span>
            <?php
                }
            }
        } ?>
        </div>
        <div class="text-sm text-gray-400"><?php echo isset($settings['description']) ? $settings['description'] : ''; ?></div>

        <div>
            <fieldset class="mt-4" data-default-value="<?php echo esc_attr($settings['default']); ?>">
                <legend class="hidden"><?php echo esc_attr($settings['title']); ?></legend>
                <div class="flex flex-col gap-2">
                    <?php
                  foreach ($settings['options'] as $value => $item) {
                      $selected = ($value === $db_value) ? 'checked="checked"' : '';
                      $toggle_element = $item['toggle_container'] ?? ''; ?>

                    <label class="relative flex items-center gap-4 rounded-md group-hover:text-brand-color-900 cursor-pointer" for="<?php echo $settings['key'].'-'.$value; ?>">
                        <div class="flex items-center h-5">
                            <input id="<?php echo $settings['key'].'-'.$value; ?>" name="<?php echo esc_attr($settings['key']); ?>" type="radio" <?php echo $selected; ?> class="wpcp-input-radio focus:ring-brand-color-700 h-4 w-4 text-brand-color-900 border-gray-300" data-toggle-element="<?php echo $toggle_element; ?>" data-value="<?php echo esc_attr($value); ?>" aria-describedby="<?php echo $settings['key'].'-'.$value; ?>-description">
                        </div>
                        <?php
                            if (isset($item['icon'])) {
                                ?>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg <?php echo $item['color']; ?>">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                <?php echo $item['icon']; ?>
                            </svg>
                        </div>
                        <?php
                            }
                      ?>
                        <div class="text-sm">
                            <p class="font-medium text-gray-700"><?php echo $item['title']; ?></p>
                            <p id="<?php echo $settings['key'].'-'.$value; ?>-description" class="text-gray-500"><?php echo isset($item['description']) ? $item['description'] : ''; ?></p>
                        </div>
                    </label>
                    <?php
                  } ?>
                </div>
            </fieldset>
        </div>

        <?php if (isset($settings['notice'])) {
            self::render_notice($settings['notice'], $settings['notice_class']);
        } ?>
    </div>
</div>
<?php
    }

    public static function render_simple_checkbox_group($settings)
    {
        $db_values = $settings['value'] ?? self::get_setting_value($settings['key'], $settings);

        if (is_string($db_values)) {
            $db_values = [$db_values];
        }

        $db_values = (empty($db_values) ? $settings['default'] : $db_values);

        if (empty($db_values) && false === empty($settings['deprecated'])) {
            return;
        }

        if (in_array('all', $db_values)) {
            $db_values = array_keys($settings['options']);
        }
        ?>

<div class="mt-2 mb-4 sm:flex sm:items-start sm:justify-between <?php echo self::get_modules_classes($settings); ?>">
    <div class="flex-grow flex flex-col max-w-full">
        <div class="text-base text-gray-900 flex items-center">
            <?php echo $settings['title'];

        if (isset($settings['account_types'])) {
            foreach ($settings['account_types'] as $account_type) {
                ?>
            <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900">
                <svg class="mr-1.5 h-2 w-2 text-brand-color-900" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                Dropbox <?php echo ucfirst($account_type); ?>
            </span>
            <?php
            }
        }
        if (isset($settings['tags'])) {
            foreach ($settings['tags'] as $tags) {
                foreach ($tags as $tag) {
                    ?>
            <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900">
                <svg class="mr-1.5 h-2 w-2 text-brand-color-900" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                <?php echo ucfirst($tag); ?>
            </span>
            <?php
                }
            }
        } ?>
        </div>
        <div class="text-sm text-gray-400"><?php echo $settings['description']; ?></div>

        <div>
            <fieldset class="mt-4" data-default-value="<?php echo implode(',', $settings['default']); ?>">
                <legend class="hidden"><?php echo esc_attr($settings['title']); ?></legend>
                <div class="flex gap-x-4 gap-y-2 flex-wrap">
                    <?php
                  foreach ($settings['options'] as $value => $item) {
                      $selected = (in_array($value, $db_values)) ? 'checked="checked"' : ''; ?>

                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input id="<?php echo $settings['key'].'-'.$value; ?>" name="<?php echo esc_attr($settings['key']); ?>" type="checkbox" <?php echo $selected; ?> class="wpcp-input-checkbox focus:ring-brand-color-700 h-4 w-4 text-brand-color-900 border-gray-300" aria-describedby="<?php echo $settings['key'].'-'.$value; ?>-description" data-value="<?php echo esc_attr($value); ?>">
                        </div>
                        <div class="ml-2 text-sm">
                            <label for="<?php echo $settings['key'].'-'.$value; ?>" class="font-medium text-gray-700"><?php echo $item['title']; ?></label>
                            <p id="<?php echo $settings['key'].'-'.$value; ?>-description" class="text-gray-500"><?php echo isset($item['description']) ? $item['description'] : ''; ?></p>
                        </div>
                    </div>
                    <?php
                  } ?>
                </div>
            </fieldset>
        </div>

        <?php if (isset($settings['notice'])) {
            self::render_notice($settings['notice'], $settings['notice_class']);
        } ?>
    </div>
</div>
<?php
    }

    public static function render_simple_checkbox_button_group($settings)
    {
        $db_values = $settings['value'] ?? self::get_setting_value($settings['key'], $settings);

        if (!is_array($db_values) && !is_null($db_values)) {
            $db_values = explode('|', $db_values);
        }

        $db_values = (empty($db_values) ? $settings['default'] : $db_values);

        if (empty($db_values) && false === empty($settings['deprecated'])) {
            return;
        }

        ?>
<div class="mt-2 mb-4 sm:flex sm:items-start sm:justify-between <?php echo self::get_modules_classes($settings); ?>">
    <div class="flex-grow flex flex-col max-w-full">
        <div class="text-base text-gray-900 flex items-center">
            <?php echo $settings['title'];

        if (isset($settings['account_types'])) {
            foreach ($settings['account_types'] as $account_type) {
                ?>
            <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900">
                <svg class="mr-1.5 h-2 w-2 text-brand-color-900" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                Dropbox <?php echo ucfirst($account_type); ?>
            </span>
            <?php
            }
        }
        if (isset($settings['tags'])) {
            foreach ($settings['tags'] as $tags) {
                foreach ($tags as $tag) {
                    ?>
            <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900">
                <svg class="mr-1.5 h-2 w-2 text-brand-color-900" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                <?php echo ucfirst($tag); ?>
            </span>
            <?php
                }
            }
        } ?>
        </div>
        <div class="text-sm text-gray-400"><?php echo $settings['description']; ?></div>

        <div>
            <fieldset class="mt-4" data-default-value="<?php echo esc_attr(implode('|', $settings['default'])); ?>">
                <legend class="hidden"><?php echo esc_attr($settings['title']); ?></legend>
                <div class="flex items-start justify-start gap-x-2 gap-y-2 flex-wrap">
                    <?php
                  foreach ($settings['options'] as $value => $item) {
                      $selected = (in_array($value, $db_values) || (isset($db_values[$value]) && 'Yes' === $db_values[$value])) ? 'checked="checked"' : ''; ?>

                    <button type="button" class="wpcp-input-checkbox-icon-button relative inline-flex items-center border <?php echo ($selected) ? ' bg-gray-50 border-brand-color-900' : ''; ?> bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 hover:text-brand-color-900 focus:outline-hiddenrounded-sm p-2">
                        <?php
                            if (isset($item['icon'])) {
                                // Has Icon
                                ?>
                        <span class="sr-only"><?php echo $item['title']; ?></span>
                        <?php echo $item['icon'];
                            } else {
                                // Title Only
                                ?>
                        <span><?php echo $item['title']; ?></span>
                        <?php
                            }
                      ?>

                        <input id="<?php echo $settings['key'].'['.$value.']'; ?>" name="<?php echo esc_attr($settings['key']); ?>" type="checkbox" <?php echo $selected; ?> class="wpcp-input-checkbox hidden" aria-describedby="<?php echo $settings['key'].'-'.$value; ?>-description" data-value="<?php echo esc_attr($value); ?>">
                    </button>
                    <?php
                  } ?>
                </div>
            </fieldset>
        </div>

        <?php if (isset($settings['notice'])) {
            self::render_notice($settings['notice'], $settings['notice_class']);
        } ?>
    </div>
</div>
<?php
    }

    public static function render_simple_checkbox($settings)
    {
        $is_checked = (null !== self::get_setting_value($settings['key'], $settings)) ? self::get_setting_value($settings['key'], $settings) : $settings['default'];
        $toggle = $settings['toggle_container'] ?? '';

        $icon_svg = self::render_icon($settings); ?>
<div class="mt-2 mb-3 sm:flex sm:items-start sm:justify-between <?php echo self::get_modules_classes($settings); ?>">
    <div class="flex-grow flex flex-col max-w-xl">
        <div class="text-base text-gray-900 flex items-center">
            <?php echo $icon_svg; ?>
            <?php echo $settings['title'];

        if (isset($settings['account_types'])) {
            foreach ($settings['account_types'] as $account_type) {
                ?>
            <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900">
                <svg class="mr-1.5 h-2 w-2 text-brand-color-900" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                Dropbox <?php echo ucfirst($account_type); ?>
            </span>
            <?php
            }
        }
        if (isset($settings['tags'])) {
            foreach ($settings['tags'] as $tags) {
                foreach ($tags as $tag) {
                    ?>
            <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900">
                <svg class="mr-1.5 h-2 w-2 text-brand-color-900" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3" />
                </svg>
                <?php echo ucfirst($tag); ?>
            </span>
            <?php
                }
            }
        } ?>
        </div>
        <div class="text-sm text-gray-400 <?php echo !empty($icon_svg) ? 'ml-8' : ''; ?>"><?php echo $settings['description']; ?></div>
        <?php if (isset($settings['notice'])) {
            self::render_notice($settings['notice'], $settings['notice_class']);
        } ?>
    </div>

    <!-- Enabled: "bg-brand-color-900", Not Enabled: "bg-gray-200" -->
    <button type="button" class="wpcp-input-checkbox-button bg-gray-200 relative inline-flex shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-brand-color-700" role="switch" aria-checked="false" data-toggle-element="<?php echo $toggle; ?>">
        <!-- Enabled: "translate-x-5", Not Enabled: "translate-x-0" -->
        <span class="wpcp-input-checkbox-button-container translate-x-0 pointer-events-none relative inline-block h-5 w-5 rounded-full bg-white shadow-sm transform ring-0 transition ease-in-out duration-200">
            <!-- Enabled: "opacity-0 ease-out duration-100", Not Enabled: "opacity-100 ease-in duration-200" -->
            <span class="wpcp-input-checkbox-button-off opacity-100 ease-in duration-200 absolute inset-0 h-full w-full flex items-center justify-center transition-opacity" aria-hidden="true">
                <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 12 12">
                    <path d="M4 8l2-2m0 0l2-2M6 6L4 4m2 2l2 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>
            <!-- Enabled: "opacity-100 ease-in duration-200", Not Enabled: "opacity-0 ease-out duration-100" -->
            <span class="wpcp-input-checkbox-button-on opacity-0 ease-out duration-100 absolute inset-0 h-full w-full flex items-center justify-center transition-opacity" aria-hidden="true">
                <svg class="h-3 w-3 text-brand-color-900" fill="currentColor" viewBox="0 0 12 12">
                    <path d="M3.707 5.293a1 1 0 00-1.414 1.414l1.414-1.414zM5 8l-.707.707a1 1 0 001.414 0L5 8zm4.707-3.293a1 1 0 00-1.414-1.414l1.414 1.414zm-7.414 2l2 2 1.414-1.414-2-2-1.414 1.414zm3.414 2l4-4-1.414-1.414-4 4 1.414 1.414z" />
                </svg>
            </span>
        </span>
        <input type="checkbox" class="hidden" id="<?php echo esc_attr($settings['key']); ?>" name="<?php echo esc_attr($settings['key']); ?>" <?php echo ($is_checked) ? 'checked="checked"' : ''; ?> data-default-value="<?php echo esc_attr($settings['default']); ?>" />
    </button>
</div>
<?php
    }

    public static function render_simple_action_button($settings)
    {
        ?>
<div class="mt-2 mb-4 sm:flex sm:items-center sm:justify-between">
    <div class="flex-grow flex flex-col">
        <div class="text-base text-gray-900 flex items-center">
            <?php echo $settings['title']; ?>
        </div>
        <div class="text-sm text-gray-400 hover:text-gray-500 max-w-xl"><?php echo $settings['description']; ?></div>
    </div>
    <div class="inline-flex shrink-0">
        <button id='<?php echo $settings['key']; ?>' type="button" class="wpcp-button-primary"><?php echo $settings['button_text']; ?></button>
    </div>
</div>
<?php
    }

    public static function render_notice($notice, $type)
    {
        switch ($type) {
            case 'warning':
                $container_class = 'bg-yellow-50 border-yellow-400 ';
                $icon_class = 'text-yellow-400';
                $icon = '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />';
                $text_class = 'text-yellow-700';

                break;

            case 'error':
                $container_class = 'bg-red-50 border-red-400 ';
                $icon_class = 'text-red-400';
                $icon = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />';
                $text_class = 'text-red-700';

                break;

            case 'info':
            default:
                $container_class = 'bg-blue-50 border-blue-400 ';
                $icon_class = 'text-blue-400';
                $icon = '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />';
                $text_class = 'text-blue-700';

                break;
        } ?>
<div class="<?php echo $container_class; ?> border-l-4 p-4 mt-4">
    <div class="flex">
        <div class="shrink-0">
            <svg class="h-5 w-5 <?php echo $icon_class; ?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <?php echo $icon; ?>
            </svg>
        </div>
        <div class="ml-3">
            <p class="text-sm <?php echo $text_class; ?>">
                <?php echo $notice; ?>
            </p>
        </div>
    </div>
</div>

<?php
    }

    public static function render_folder_selectbox($settings)
    {
        if (isset($_GET['dir'])) {
            $settings['shortcode_attr']['startpath'] = self::get_setting_value('dir', $settings);
        }

        if ('usertemplatedir' === $settings['key']) {
            $settings['shortcode_attr']['startpath'] = self::get_setting_value('usertemplatedir', $settings);
        }

        if (isset($_GET['account'])) {
            $settings['shortcode_attr']['startaccount'] = self::get_setting_value('account', $settings);
            App::set_current_account_by_id($settings['shortcode_attr']['startaccount']);
        }

        // Resolve User Folder. Not needed for some settings
        $settings['resolve_userfolder'] = isset($settings['resolve_userfolder']) ? $settings['resolve_userfolder'] : true;

        // Module configuration
        $module_default_options = [
            'mode' => 'files',
            'singleaccount' => '0',
            'dir' => '/',
            'filelayout' => 'list',
            'maxheight' => '350px',
            'showfiles' => '0',
            'hoverthumbs' => '0',
            'filesize' => '0',
            'filedate' => '0',
            'upload' => '0',
            'delete' => '0',
            'rename' => '0',
            'addfolder' => '0',
            'showfiles' => '0',
            'downloadrole' => 'none',
            'candownloadzip' => '0',
            'showsharelink' => '0',
            'preview' => '0',
            'popup' => 'personal_folders_backend',
            'search' => '0',
        ];

        $module_options = array_merge($module_default_options, $settings['shortcode_attr']);

        // Back-End Personal Folders
        $user_folder_backend = apply_filters('outofthebox_use_user_folder_backend', Settings::get('userfolder_backend'));
        if ('No' !== $user_folder_backend && (!isset($settings['apply_backend_personal_folder']) || true === $settings['apply_backend_personal_folder'])) {
            $module_options['userfolders'] = $user_folder_backend;

            $private_root_folder = Settings::get('userfolder_backend_auto_root');

            if ('/' === $module_options['startpath']) {
                $module_options['startpath'] = null;
            }

            if ('auto' === $user_folder_backend && !empty($private_root_folder) && isset($private_root_folder['id'])) {
                if (!isset($private_root_folder['account']) || empty($private_root_folder['account'])) {
                    $main_account = Accounts::instance()->get_primary_account();
                    $module_options['account'] = $main_account->get_id();
                } else {
                    $module_options['account'] = $private_root_folder['account'];
                }

                $module_options['dir'] = $private_root_folder['id'];

                if (!isset($private_root_folder['view_roles']) || empty($private_root_folder['view_roles'])) {
                    $private_root_folder['view_roles'] = ['none'];
                }
                $module_options['viewuserfoldersrole'] = implode('|', $private_root_folder['view_roles']);
            }

            if (true === $settings['resolve_userfolder'] && 'manual' === $user_folder_backend && !empty($module_options['startpath'])) {
                $module_options['startpath'] = \str_ireplace(UserFolders::get_manually_linked_folder_for_user(get_current_user_id()), '', $module_options['startpath']);
            }
        }

        // Load the module
        $html_module = Processor::instance()->create_from_shortcode($module_options);

        if (empty(Processor::instance()->options)) {
            self::render_notice(
                esc_html__('The selected account is no longer available, or there are currently no accounts linked to the plugin. Please make sure that the plugin has active accounts and re-create the shortcode.', 'wpcloudplugins'),
                'warning'
            );

            return;
        }

        // Input values
        $folder_id = (!empty($module_options['startpath'])) ? $module_options['startpath'] : '';
        if (true === $settings['resolve_userfolder'] && 'manual' === $user_folder_backend && !empty($module_options['startpath'])) {
            $folder_id = Helpers::clean_folder_path(UserFolders::get_manually_linked_folder_for_user(get_current_user_id()).$module_options['startpath']);
        }

        $folder_account = App::get_current_account()->get_id();
        $folder_data = (!empty($module_options['startpath'])) ? Client::instance()->get_folder($folder_id, false) : '';

        if (empty($folder_data)) {
            if (!empty($module_options['startpath'])) {
                self::render_notice(
                    esc_html__('The selected folder is no longer available. Please reselect a top folder.', 'wpcloudplugins'),
                    'warning'
                );

                $folder_path = esc_html__('Folder location not longer available', 'wpcloudplugins');
            } else {
                $folder_path = esc_html__('Select folder location', 'wpcloudplugins');
            }
        } else {
            $folder_path = $folder_data->get_path();
        } ?>
<div class="mt-2 mb-4 sm:flex sm:items-center sm:justify-between <?php echo self::get_modules_classes($settings); ?>">
    <div class="flex-grow flex flex-col">
        <div class="text-base text-gray-900 flex items-center">
            <?php echo $settings['title']; ?>
        </div>
        <div class="text-sm text-gray-400 max-w-xl"><?php echo $settings['description']; ?></div>
        <div class="wpcp-folder-selector mt-2">
            <div class="flex grow justify-items-stretch space-x-1">
                <?php
                        $is_hidden_class = (false === $settings['inline']) ? '' : 'hidden';
        ?>
                <input class="wpcp-folder-selector-current <?php echo $is_hidden_class; ?> wpcp-input-textbox max-w-xl flex-1shadow-xs focus:ring-brand-color-700 focus:border-brand-color-700 sm:text-sm border border-gray-300 rounded-md mr-2 p-2 select-all" type="text" value="<?php echo $folder_path; ?>" readonly="readonly">
                <input class="wpcp-folder-selector-input-account wpcp-input-hidden" type='hidden' value='<?php echo $folder_account; ?>' name='<?php echo $settings['key']; ?>[account]' id='<?php echo $settings['key']; ?>[account]' />
                <input class="wpcp-folder-selector-input-id wpcp-input-hidden" type='hidden' value='<?php echo $folder_id; ?>' name='<?php echo $settings['key']; ?>[id]' id='<?php echo $settings['key']; ?>[id]' />
                <input class="wpcp-folder-selector-input-name wpcp-input-hidden" type='hidden' value='<?php echo $folder_path; ?>' name='<?php echo $settings['key']; ?>[name]' id='<?php echo $settings['key']; ?>[name]' />
                <?php if (false === $settings['inline']) { ?>
                <button type="button" class="wpcp-button-primary select_folder wpcp-folder-selector-button"><?php esc_html_e('Select folder', 'wpcloudplugins'); ?></button>
                <button type="button" class="wpcp-button-primary wpcp-folder-clear-button"><?php esc_html_e('Reset', 'wpcloudplugins'); ?></button>
                <?php } ?>
            </div>
            <div class="mt-4">
                <div id='<?php echo $settings['key']; ?>-selector' class='wpcp-folder-selector-embed bg-white rounded-md shadow-(--shadow-5)' style='<?php echo ($settings['inline']) ? '' : 'clear:both;display:none;'; ?>'>
                    <?php echo $html_module;

        if ('personal_folders_backend' === $module_options['popup']) {           ?>
                    <div class="mt-5">
                        <button type="button" class="wpcp-button-primary wpcp-dialog-entry-select inline-flex justify-center w-full"><?php esc_html_e('Select'); ?></button>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    }

    public static function render_items_selectbox($settings)
    {
        if (isset($_GET['account'])) {
            $settings['shortcode_attr']['startaccount'] = self::get_setting_value('account', $settings);
            App::set_current_account_by_id($settings['shortcode_attr']['startaccount']);
        }

        $items = [];
        if (!empty($_GET['items'])) {
            $entry_ids = array_filter(\explode('|', self::get_setting_value('items', $settings)));

            foreach ($entry_ids as $entry_id) {
                $entry = Client::instance()->get_entry($entry_id, false);
                if (empty($entry)) {
                    continue;
                }

                $items[$entry_id] = $entry;
            }
        }

        // Resolve User Folder. Not needed for some settings
        $settings['resolve_userfolder'] = isset($settings['resolve_userfolder']) ? $settings['resolve_userfolder'] : true;

        // Module configuration
        $module_default_options = [
            'mode' => 'files',
            'singleaccount' => '0',
            'dir' => '/',
            'filelayout' => 'list',
            'maxheight' => '350px',
            'hoverthumbs' => '0',
            'filesize' => '0',
            'filedate' => '0',
            'upload' => '0',
            'delete' => '0',
            'rename' => '0',
            'addfolder' => '0',
            'downloadrole' => 'none',
            'candownloadzip' => '0',
            'showsharelink' => '0',
            'preview' => '0',
            'popup' => 'selector',
        ];

        $module_options = array_merge($module_default_options, $settings['shortcode_attr']);

        // Back-End Personal Folders
        $user_folder_backend = apply_filters('outofthebox_use_user_folder_backend', Settings::get('userfolder_backend'));
        if ('No' !== $user_folder_backend && (!isset($settings['apply_backend_personal_folder']) || true === $settings['apply_backend_personal_folder'])) {
            $module_options['userfolders'] = $user_folder_backend;

            $private_root_folder = Settings::get('userfolder_backend_auto_root');

            if ('/' === $module_options['startpath']) {
                $module_options['startpath'] = null;
            }

            if ('auto' === $user_folder_backend && !empty($private_root_folder) && isset($private_root_folder['id'])) {
                if (!isset($private_root_folder['account']) || empty($private_root_folder['account'])) {
                    $main_account = Accounts::instance()->get_primary_account();
                    $module_options['account'] = $main_account->get_id();
                } else {
                    $module_options['account'] = $private_root_folder['account'];
                }

                $module_options['dir'] = $private_root_folder['id'];

                if (!isset($private_root_folder['view_roles']) || empty($private_root_folder['view_roles'])) {
                    $private_root_folder['view_roles'] = ['none'];
                }
                $module_options['viewuserfoldersrole'] = implode('|', $private_root_folder['view_roles']);
            }

            if (true === $settings['resolve_userfolder'] && 'manual' === $user_folder_backend && !empty($module_options['startpath'])) {
                $module_options['startpath'] = \str_ireplace(UserFolders::get_manually_linked_folder_for_user(get_current_user_id()), '', $module_options['startpath']);
            }
        }

        // Load the module
        $html_module = Processor::instance()->create_from_shortcode($module_options);

        if (empty(Processor::instance()->options)) {
            self::render_notice(
                esc_html__('The selected account is no longer available, or there are currently no accounts linked to the plugin. Please make sure that the plugin has active accounts and re-create the shortcode.', 'wpcloudplugins'),
                'warning'
            );

            return;
        }

        $folder_account = App::get_current_account()->get_id();

        ?>
<div class="mt-2 mb-4 sm:flex sm:items-center sm:justify-between <?php echo self::get_modules_classes($settings); ?>">
    <div class="flex-grow flex flex-col">
        <div class="text-base text-gray-900 flex items-center">
            <?php echo $settings['title']; ?>
        </div>
        <div class="text-sm text-gray-400  max-w-xl"><?php echo $settings['description']; ?></div>
        <div class="wpcp-items-selector wpcp-folder-selector mt-2">

            <div class="mt-4 flex flex-row gap-2">
                <div id='<?php echo $settings['key']; ?>-selector' class='wpcp-folder-selector-embed basis-1/2 bg-white rounded-md shadow-(--shadow-5)'>
                    <?php echo $html_module; ?>
                </div>
                <div id='<?php echo $settings['key']; ?>-selected-items' class="wpcp-selected-items basis-1/2 bg-white rounded-md shadow-(--shadow-5) max-h-[350px] overflow-y-auto overflow-x-hidden" data-max-items="<?php echo $settings['max_items'] ?? -1; ?>">
                    <table class="min-w-full divide-y divide-gray-300 ">
                        <thead class="">
                            <tr>
                                <th scope="col" colspan="3" class="sticky top-0 z-10 border-b border-gray-300 bg-brand-color-900 px-3 py-2  text-center text-sm font-semibold text-white">
                                    <?php esc_html_e('Selected Content', 'wpcloudplugins'); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <!--    Template Item -->
                            <tr class="wpcp-selected-item wpcp-selected-item-template hidden group text-gray-600 hover:text-gray-800 hover:bg-gray-100 cursor-pointer hover:shadow-(--shadow-5) rounded-lg w-full" data-account-id="" data-entry-id="">
                                <!-- Item Sort -->
                                <td class="whitespace-nowrap pl-3 py-1 text-sm w-8">
                                    <div class="flex items-center justify-center">
                                        <img class="wpcp-selected-item-icon w-4 h-4" src="" alt="">
                                    </div>
                                </td>
                                <!-- Item Details -->
                                <td class="wpcp-selected-item-name px-1 py-1 text-sm/6 font-semibold text-gray-900 group-hover:text-brand-color-900 truncate"></td>

                                <!--    Item Delete -->
                                <td class="relative whitespace-nowrap pr-3 py-1 text-right text-sm font-medium">
                                    <button type="button" class="wpcp-remove-selected-item wpcp-button-icon-only" title="<?php \esc_attr_e('Remove item from list', 'wpcloudplugins'); ?>">
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <!--    End Template Item -->
                            <?php foreach ($items as $entry_id => $cached_node) {?>
                            <!--    Item -->
                            <tr class="wpcp-selected-item group text-gray-600 hover:text-gray-800 hover:bg-gray-100 cursor-pointer hover:shadow-(--shadow-5) rounded-lg w-full" data-account-id="<?php echo $folder_account; ?>" data-entry-id="<?php echo $cached_node->get_id(); ?>">
                                <!-- Item Sort -->
                                <td class="whitespace-nowrap pl-3 py-1 text-sm w-8">
                                    <div class="flex items-center justify-center">
                                        <img class="wpcp-selected-item-icon w-4 h-4" src="<?php echo $cached_node->get_icon(); ?>" alt="">
                                    </div>
                                </td>
                                <!-- Item Details -->
                                <td class="wpcp-selected-item-name px-1 py-1 text-sm/6 font-semibold text-gray-900 group-hover:text-brand-color-900 truncate">
                                    <?php echo $cached_node->get_name(); ?>
                                </td>

                                <!--    Item Delete -->
                                <td class="relative whitespace-nowrap pr-3 py-1 text-right text-sm font-medium">
                                    <button type="button" class="wpcp-remove-selected-item wpcp-button-icon-only" title="<?php \esc_attr_e('Remove item from list', 'wpcloudplugins'); ?>">
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <!--    End Item -->
                            <?php } ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
        <?php if (isset($settings['notice'])) {
            self::render_notice($settings['notice'], $settings['notice_class']);
        } ?>
    </div>
</div>
<?php
    }

    public static function render_tags($settings)
    {
        $db_value = $settings['value'] ?? self::get_setting_value($settings['key'], $settings);

        if (!is_array($db_value) && !empty($db_value)) {
            $db_value = explode('|', $db_value ?? '');
        }

        if (!is_array($db_value)) {
            $db_value = $settings['default'];
        }

        // Workaround: Add temporarily selected value to prevent an empty selection in Tagify when only user ID 0 is selected
        if (empty($db_value)) {
            $db_value = ['_______PREVENT_EMPTY_______'];
        }

        if (empty($db_value) && false === empty($settings['deprecated'])) {
            return;
        }

        $icon_svg = self::render_icon($settings);

        // Set the whitelist used
        $whitelist = $settings['whitelist'] ?? 'permissions';
        $pattern = $settings['pattern'] ?? '';

        // Create value for imput field
        $value = implode(', ', $db_value); ?>
<div class="mt-2 mb-4 sm:flex sm:items-start sm:justify-between <?php echo self::get_modules_classes($settings); ?>">
    <div class="flex-grow flex flex-col gap-2 max-w-full">
        <div class="text-base text-gray-900 flex items-center">
            <?php echo $icon_svg; ?>
            <?php echo $settings['title'];

        if (isset($settings['help_text'])) {
            self::render_help_tip($settings['help_text']);
        } ?>
        </div>
        <div class="text-sm text-gray-400"><?php echo $settings['description']; ?></div>
        <input type="text" name="<?php echo esc_attr($settings['key']); ?>" id="<?php echo esc_attr($settings['key']); ?>" class="wpcp-tagify  wpcp-input-hidden w-full p-0 focus:ring-brand-color-700 focus:border-brand-color-700 focus:ring-2 focus:ring-offset-2 sm:text-sm border border-gray-300 rounded-md" value="<?php echo esc_attr($value); ?>" data-default-value="<?php echo esc_attr(implode('|', $settings['default'])); ?>" data-tagify-whitelist="<?php echo esc_attr($whitelist); ?>" pattern="<?php echo esc_attr($pattern); ?>" />
        <?php if (isset($settings['notice'])) {
            self::render_notice($settings['notice'], $settings['notice_class']);
        } ?>
    </div>
</div>
<?php
    }

    public static function render_share_buttons()
    {
        $buttons = self::get_setting_value('share_buttons'); ?>
<div class="shareon shareon-settings">
    <?php foreach ($buttons as $button => $value) {
        $title = ucfirst($button);
        echo "<a role='button' class='wpcp-shareon-toggle-button ".esc_attr($button).' shareon-'.esc_attr($value)." box-content' title='".esc_attr($title)."'></a>";
        echo "<input type='hidden' value='".esc_attr($value)."' id='share_buttons[".esc_attr($button)."]' name='share_buttons[".esc_attr($button)."]' class='wpcp-shareon-input'/>";
    } ?>
</div>
<?php
    }

    public static function render_image_selector($settings)
    {
        $db_value = $settings['value'] ?? self::get_setting_value($settings['key'], $settings);
        $placeholder = (!empty($settings['placeholder'])) ? $settings['placeholder'] : ''; ?>

<div class="mt-2 mb-4 sm:flex sm:items-center sm:justify-between">
    <div class="flex-grow flex flex-col gap-2 max-w-xl">
        <div class="text-base text-gray-900 flex items-center">
            <?php echo $settings['title']; ?>
        </div>
        <div class="inline-flex max-w-xl">
            <input type="text" name="<?php echo esc_attr($settings['key']); ?>" id="<?php echo esc_attr($settings['key']); ?>" class="wpcp-image-selector-input wpcp-input-textbox max-w-xl flex-1 block shadow-xs focus:ring-brand-color-700 focus:border-brand-color-700 sm:text-sm border border-gray-300 rounded-md mr-2 p-2" value="<?php echo esc_attr($db_value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>">
            <button type='button' class='wpcp-button-primary wpcp-image-selector-button mr-2' title='<?php esc_html_e('Upload or select a file from the media library.', 'wpcloudplugins'); ?>'><?php esc_html_e('Select Image', 'wpcloudplugins'); ?></button>
            <button type='button' class='wpcp-button-secondary wpcp-image-selector-default-button' title='<?php esc_html_e('Fallback to the default value.', 'wpcloudplugins'); ?>' data-default="<?php echo $settings['default']; ?>"><?php esc_html_e('Default', 'wpcloudplugins'); ?></button>
        </div>
        <div class="text-sm text-gray-400 hover:text-gray-500 max-w-xl"><?php echo $settings['description']; ?></div>
    </div>
    <div class="shrink-0 w-24">
        <img src="<?php echo esc_url($db_value); ?>" class="wpcp-image-selector-preview h-24 object-contain" alt="" />
    </div>
</div>
<?php
    }

    public static function render_color_selectors($colors)
    {
        $db_value = self::get_setting_value('colors');

        if (0 === count($colors)) {
            return '';
        } ?>


<?php
           foreach ($colors as $color_id => $color) {
               $value = isset($db_value[$color_id]) ? sanitize_text_field($db_value[$color_id]) : $color['default'];
               $alpha = $color['alpha'] ?? true; ?>
<div class="my-2 sm:flex max-w-xl">
    <div class="flex-grow flex sm:justify-between items-start">
        <div class="text-sm font-semibold text-gray-500 flex items-center">
            <?php echo $color['label']; ?>
        </div>
        <div>
            <input value='<?php echo $value; ?>' data-default-color='<?php echo $color['default']; ?>' name='colors[<?php echo $color_id; ?>]' id='colors[<?php echo $color_id; ?>]' type='text' class='wpcp-color-picker wpcp-input-hidden' data-alpha-enabled='<?php echo $alpha ? 'true' : 'false'; ?>'>
        </div>
    </div>
</div>
<?php
           }
    }

    public static function render_plugin_integrations()
    {
        $plugin_integrations = Integrations::list();

        usort($plugin_integrations, fn ($a, $b) => $b['available'] <=> $a['available']); ?>
<div class="">
    <ul class="divide-y divide-gray-100">
        <?php

                    foreach ($plugin_integrations as $integration) {
                        $is_checked = (null !== $integration['value']) ? $integration['value'] : $integration['default'];
                        ?>
        <li class="flex items-center justify-between gap-x-6 <?php echo ($integration['available']) ? '' : 'opacity-40 '; ?>">
            <div class="flex min-w-0 gap-x-4 items-center">
                <img class="h-12 w-12 flex-none" src="<?php echo OUTOFTHEBOX_ROOTPATH.'/includes/'.$integration['img']; ?>" alt="<?php echo $integration['title']; ?>">
                <div class="min-w-0 flex-auto">
                    <p class="text-base font-semibold leading-6 text-gray-900 group-hover:text-brand-color-900"><?php echo $integration['title']; ?>
                        <?php if (!empty($integration['beta'])) {?>
                        <span class="inline-flex items-center rounded-full bg-brand-color-100 mx-1 px-3 py-0.5 text-xs font-medium text-brand-color-900 italic"><?php echo 'Beta'; ?></span>
                        <?php } ?>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-5">
                <?php
                if (!empty($integration['has_settings']) && $integration['value']) {
                    ?>
                <a href="#wpcp-<?php echo esc_attr($integration['key']); ?>-settings" class="wpcp-button-secondary flex items-center text-sm  hover:bg-gray-50 hover:text-brand-color-900">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="-ml-1 mr-3 h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <?php esc_html_e('Settings', 'wpcloudplugins'); ?>
                </a>
                <?php
                }
                        ?>
                <div>
                    <?php
                        if ($integration['available']) {
                            ?>
                    <!-- Enabled: "bg-brand-color-900", Not Enabled: "bg-gray-200" -->
                    <button type="button" class="wpcp-input-checkbox-button bg-gray-200 relative inline-flex shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-brand-color-700" role="switch" aria-checked="false">
                        <!-- Enabled: "translate-x-5", Not Enabled: "translate-x-0" -->
                        <span class="wpcp-input-checkbox-button-container translate-x-0 pointer-events-none relative inline-block h-5 w-5 rounded-full bg-white shadow-sm transform ring-0 transition ease-in-out duration-200">
                            <!-- Enabled: "opacity-0 ease-out duration-100", Not Enabled: "opacity-100 ease-in duration-200" -->
                            <span class="wpcp-input-checkbox-button-off opacity-100 ease-in duration-200 absolute inset-0 h-full w-full flex items-center justify-center transition-opacity" aria-hidden="true">
                                <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 12 12">
                                    <path d="M4 8l2-2m0 0l2-2M6 6L4 4m2 2l2 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            <!-- Enabled: "opacity-100 ease-in duration-200", Not Enabled: "opacity-0 ease-out duration-100" -->
                            <span class="wpcp-input-checkbox-button-on opacity-0 ease-out duration-100 absolute inset-0 h-full w-full flex items-center justify-center transition-opacity" aria-hidden="true">
                                <svg class="h-3 w-3 text-brand-color-900" fill="currentColor" viewBox="0 0 12 12">
                                    <path d="M3.707 5.293a1 1 0 00-1.414 1.414l1.414-1.414zM5 8l-.707.707a1 1 0 001.414 0L5 8zm4.707-3.293a1 1 0 00-1.414-1.414l1.414 1.414zm-7.414 2l2 2 1.414-1.414-2-2-1.414 1.414zm3.414 2l4-4-1.414-1.414-4 4 1.414 1.414z" />
                                </svg>
                            </span>
                        </span>
                        <input type="checkbox" class="hidden" id="integrations[<?php echo esc_attr($integration['key']); ?>]" name="integrations[<?php echo esc_attr($integration['key']); ?>]" <?php echo ($is_checked) ? 'checked="checked"' : ''; ?> data-default-value="<?php echo esc_attr($integration['default']); ?>" />
                    </button>
                    <?php
                        } else {
                            ?>
                    <div class="flex-none rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-800 border border-solid border-red-800 hover:bg-red-100 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-offset-red-50 focus:ring-red-600"><?php \esc_html_e('Unavailable', 'wpcloudplugins'); ?></div>
                    <?php
                        }
                        ?>
                </div>
            </div>
        </li>
        <?php } ?>
    </ul>
</div>
<?php
    }

    public static function render_file_selector($settings)
    {
        ?>
<div class="mt-2 mb-4 sm:flex sm:items-center sm:justify-between">
    <div class="flex-grow flex flex-col gap-2 max-w-xl">
        <div class="text-base text-gray-900 flex items-center">
            <?php echo $settings['title']; ?>
        </div>
        <div class="text-sm text-gray-400 hover:text-gray-500 max-w-xl"><?php echo $settings['description']; ?></div>
        <div class="inline-flex max-w-xl">
            <input class="block w-full shadow-xs text-base focus:outline-hidden focus:ring-brand-color-700 focus:border-brand-color-700 sm:text-sm border border-gray-300 rounded-l-md p-0 file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:text-sm file:font-semibold file:bg-gray-200 file:text-brand-color-700 hover:file:bg-brand-color-100" type="file" name="<?php echo $settings['key']; ?>-file" id="<?php echo $settings['key']; ?>-file" accept="<?php echo $settings['accept']; ?>">
            <button id='<?php echo $settings['key']; ?>-button' type="button" class="wpcp-button-primary rounded-none rounded-r-md"><?php echo $settings['button_text']; ?></button>
        </div>
    </div>
</div>
<?php
    }

    public static function render_account_box($account, $read_only = true)
    {
        App::set_current_account($account);
        $app = App::instance();
        $app->get_sdk_client();

        // Check if Account has Access Token
        $has_access_token = $account->get_authorization()->has_access_token();

        // Check Authorization
        $transient_name = 'outofthebox_'.$account->get_id().'_is_authorized';
        $is_authorized = get_transient($transient_name);

        ?>
<li class="wpcp-account" data-account-id='<?php echo $account->get_id(); ?>' data-is-authorized="<?php echo $is_authorized ? 'true' : 'false'; ?>" data-has-token="<?php echo $has_access_token ? 'true' : 'false'; ?>">
    <div class="block hover:bg-gray-50">
        <div class="flex items-center px-4 py-4 sm:px-6">
            <div class="min-w-0 flex-1 flex items-center">
                <div class="shrink-0">
                    <img class="h-12 w-12 rounded-full" src="<?php echo $account->get_image(); ?>" alt="" onerror="this.src='<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/dropbox_logo.png'">
                </div>
                <div class="min-w-0 flex-1 px-4 items-center">
                    <div>
                        <p class="text-xl font-medium text-brand-color-900 truncate">
                            <?php echo $account->get_name(); ?>
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-0.5 text-xs font-medium text-gray-800">
                                <svg class="mr-1.5 h-2 w-2 text-gray-400" fill="currentColor" viewBox="0 0 8 8">
                                    <circle cx="4" cy="4" r="3" />
                                </svg>
                                Dropbox <?php echo ucfirst($account->get_type()); ?>
                            </span>
                        </p>
                        <p class="mt-2 flex items-center text-sm text-gray-500">
                            <!-- Heroicon name: outline/mail -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="truncate"><?php echo $account->get_email(); ?></span>
                        </p>
                        <p class="mt-2 flex items-center text-sm text-gray-500">
                            <!-- Heroicon name: outline/identification -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                            </svg>
                            <span class="truncate select-all"><?php echo $account->get_id(); ?></span>
                        </p>
                        <p class="mt-2 flex items-center text-sm text-gray-500">
                            <!-- Heroicon name: outline/storage -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                            </svg>
                            <span class="wpcp-account-storage-information"><?php esc_html_e('Calculating...', 'wpcloudplugins'); ?></span>
                        </p>
                        <div class="mt-2 mx-6" aria-hidden="true">
                            <div class="bg-gray-200 rounded-full overflow-hidden">
                                <div class="wpcp-account-storage-information-bar h-2 bg-brand-color-700 rounded-full" style="width: 0;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <?php if (false === $read_only) { ?>
                <button type="button" data-account-id='<?php echo $account->get_id(); ?>' data-url='<?php echo $app->get_auth_url(['force_reapprove' => 'true']); ?>' class=" wpcp-refresh-account-button wpcp-button-icon-only">
                    <!-- Heroicon name: solid/refresh -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                    </svg>
                </button>

                <button type="button" data-account-id='<?php echo $account->get_id(); ?>' data-force='true' class="wpcp-delete-account-button wpcp-button-icon-only">
                    <!-- Heroicon name: solid/trash -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </button>

                <button type="button" data-account-id='<?php echo $account->get_id(); ?>' data-force='false' class="wpcp-revoke-account-button wpcp-button-icon-only">
                    <!-- Heroicon name: solid/cancel -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </button>
                <?php
                } ?>
            </div>
        </div>
        <div class="wpcp-account-error hidden">
            <?php
                self::render_notice('<div class="wpcp-account-error-message"></div><pre class="wpcp-account-error-details text-wrap"></pre>', 'error'); ?>
        </div>
    </div>
</li>
<?php
    }

    public static function render_progress_bar($settings)
    {
        ?>
<nav aria-label="Progress">
    <ol role="list" class="overflow-hidden">
        <?php $is_next_step_id = 1;
        foreach ($settings['steps'] as $step_id => $step) {
            $is_next_step_id = (true === $step['completed'] ? $step_id + 1 : $is_next_step_id);
            $is_last_step = $step_id === count($settings['steps']);

            ?>
        <li class="relative <?php echo ($is_last_step) ? '' : 'pb-10'; ?>">
            <?php if (!$is_last_step) {?>
            <div class="absolute left-4 top-4 -ml-px mt-0.5 h-full w-0.5 bg-brand-color-900" aria-hidden="true"></div>
            <?php } ?>
            <!-- Complete Step -->
            <a href="<?php echo esc_url($step['action_url'] ?? '#'); ?>" class="group relative flex items-start gap-4">
                <span class="flex h-9 items-center">

                    <?php if ($step['completed']) {?>
                    <span class="relative z-10 flex size-8 items-center justify-center rounded-full bg-brand-color-900">
                        <svg class="size-5 text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    <?php } else {
                        if ($is_next_step_id === $step_id) {
                            ?> <span class="relative z-10 flex size-8 items-center justify-center rounded-full border-2 border-brand-color-900 bg-white"> <span class="size-2.5 rounded-full bg-brand-color-900"></span> </span> <?php
                        } else {
                            ?> <span class="relative z-10 flex size-8 items-center justify-center rounded-full border-2 border-gray-300 bg-white group-hover:border-gray-400"> <span class="size-2.5 rounded-full bg-transparent group-hover:bg-gray-300"></span> </span> <?php
                        }
                        ?>
                    <?php }?>
                </span>
                <span class="flex min-w-0 flex-col font-normal">
                    <span class="text-lg font-medium"><?php echo \esc_html($step['title']); ?></span>
                    <span class="text-sm text-gray-500"><?php echo \esc_html($step['description']); ?></span>
                </span>
                <?php if ($is_next_step_id === $step_id && isset($step['action_url'], $step['action_title'])) { ?>
                <button class="wpcp-button-primary text-nowrap mt-2" type="button"><?php echo \esc_html($step['action_title']); ?></button>
                <?php } ?>
            </a>
        </li>
        <?php }?>
    </ol>
</nav>
<?php
    }

    public static function render_icon($settings)
    {
        if (empty($settings['icon_svg'])) {
            return null;
        }

        return '<svg class="text-gray-400 group-hover:text-brand-color-900 active:text-brand-color-900 focus:text-brand-color-900 mr-3 shrink-0 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">'.$settings['icon_svg'].'</svg>';
    }

    public static function render_help_tip($tip)
    {
        $tip = htmlspecialchars(
            wp_kses(
                html_entity_decode($tip),
                [
                    'br' => [],
                    'em' => [],
                    'strong' => [],
                    'small' => [],
                    'span' => [],
                    'ul' => [],
                    'li' => [],
                    'ol' => [],
                    'p' => [],
                ]
            )
        );

        return '<span class="wpcp-help-tip" title="'.$tip.'"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
  <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
</svg>
</span>';
    }

    public static function get_modules_classes($settings)
    {
        $modules = $settings['modules'] ?? ['all'];

        $classes = implode(' ', array_map(function ($value) { return 'wpcp-module-'.$value; }, $modules));

        if (isset($settings['deprecated']) && true === $settings['deprecated']) {
            $classes .= ' opacity-60 pointer-events-none ';
        }

        return "wpcp-module-classes {$classes} ";
    }

    public static function set_setting_value_location($setting_value_location)
    {
        self::$setting_value_location = $setting_value_location;
    }

    public static function get_setting_value($setting_key, $field_settings = null)
    {
        $value = null;

        switch (self::$setting_value_location) {
            case 'database':
                $value = Settings::get($setting_key);

                break;

            case 'database_network':
                $network_settings = get_site_option(Settings::$db_network_key, []);
                $value = array_key_exists($setting_key, $network_settings) ? $network_settings[$setting_key] : null;

                break;

            case 'GET':
                if (isset($_GET[$setting_key])) {
                    $raw_value = $_GET[$setting_key];

                    $value = \esc_attr(\stripslashes($raw_value));
                }

                if (isset($_GET[$setting_key.'role']) && 'none' === $_GET[$setting_key.'role']) {
                    $value = false;
                }

                break;

            default:
                break;
        }

        if (isset($field_settings['type']) && 'number' === $field_settings['type'] && null !== $value) {
            return (int) $value;
        }

        if ('1' === $value || 'Yes' === $value) {
            return true;
        }
        if ('0' === $value || 'No' === $value) {
            return false;
        }

        return $value;
    }
}
