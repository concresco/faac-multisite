<?php

namespace TheLion\OutoftheBox\Integrations;

use TheLion\OutoftheBox\Helpers;
use TheLion\OutoftheBox\Placeholders;
use TheLion\OutoftheBox\Settings;

defined('ABSPATH') || exit;

add_filter('outofthebox_render_formfield_data', [__NAMESPACE__.'\FormHelpers', 'render_form_value'], 10, 2);

class FormHelpers
{
    public static function render_form_value($data, $ashtml)
    {
        if (empty($data)) {
            return $data;
        }

        $uploaded_files = json_decode($data, true);

        if (empty($uploaded_files) || (0 === count($uploaded_files))) {
            return $data;
        }

        // Sort array by name, alphabetically ascending
        usort($uploaded_files, function ($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });

        // Update deprecated fields
        $uploaded_files = self::update_deprecated($uploaded_files);

        // Render Text List
        if (!$ashtml) {
            $template = Settings::instance()->get('forms_upload_list_text_template');
            if (empty($template)) {
                $template = self::get_default_text_template();
            }

            return nl2br(self::apply_placeholders($template, $uploaded_files, false));
        }

        // Render HTML List
        $template = Settings::instance()->get('forms_upload_list_html_template');
        if (empty($template)) {
            $template = self::get_default_html_template();
        }

        $html = self::apply_placeholders($template, $uploaded_files, true);

        return trim(preg_replace('/\s+/', ' ', $html));
    }

    public static function get_default_html_template()
    {
        ob_start();

        ?>
You have <a href="%folder_cloud_preview_url%">uploaded <strong>%number_of_files%</strong> file(s)</a>
<table style="width: 100%; border-collapse: collapse; border-spacing: 0;">
    {{#each files}}
    <tr style="border-bottom: 1px solid #ddd;">
        <td style="width: 16px; text-align: center; padding:3px 5px 3px 5px;border:none;vertical-align: middle;">
            <img src="%file_icon%" alt="" style="width:16px;height:16px;outline:none;border:0;display:block;">
        </td>
        <td style="padding:3px 0px 3px 0px;border:none;vertical-align: middle;">
            <a href="%file_cloud_preview_url%" style="text-decoration: none;" title="%file_name%">%file_name%</a>
            %file_description%
        </td>
        <td style="padding:3px 0px 3px 0px;border:none; text-align: right; color: #666; width: 60px;vertical-align: middle;">
            %file_size%
        </td>
    </tr>
    {{/each}}
</table><?php

        return ob_get_clean();
    }

    public static function get_default_text_template()
    {
        ob_start();

        ?>{{#each files}}• %file_name% (%file_size%){{/each}}<?php

    return ob_get_clean();
    }

    /**
     * Apply placeholders to the template.
     *
     * @param string     $template the template string
     * @param null|array $files    the files array
     * @param bool       $ashtml   whether to render as HTML
     *
     * @return string the template with placeholders replaced
     */
    public static function apply_placeholders($template, $files = [], $ashtml = true)
    {
        // Replace global placeholders
        $return = strtr($template, [
            '%number_of_files%' => count($files),
        ]);

        // Extract the template between {{#each files}} and {{/each}}
        preg_match('/{{#each files}}(.*?){{\/each}}/s', $return, $matches);
        $file_template = $matches[1];

        $file_html = '';
        if (!empty($file_template)) {
            foreach ($files as $file) {
                if ($file) {
                    // Replace file-specific placeholders
                    $file_html .= strtr($file_template, [
                        '%file_name%' => $file['name'],
                        '%file_size%' => $file['size'],
                        '%file_icon%' => Helpers::get_default_icon($file['type'], false),
                        '%file_description%' => (!empty($file['description'])) ? '<br/><div style="font-weight:normal; max-height: 200px; overflow-y: auto;word-break: break-word; font-size:80%; padding-top:3px">'.nl2br($file['description']).'</div>' : '',
                        '%file_cloud_preview_url%' => urldecode($file['preview_url']),
                        '%file_cloud_shared_url%' => urldecode($file['shared_url']),
                        '%file_absolute_path%' => $file['absolute_path'] ?? '',
                        '%file_relative_path%' => $file['path'] ?? '',
                    ]);

                    if (!$ashtml) {
                        $file_html .= "\r\n";
                    }
                }
            }
        }

        // Replace folder-specific placeholders
        $return = strtr($return, [
            '%folder_name%' => basename($file['folder_absolute_path'] ?? ''),
            '%folder_cloud_preview_url%' => urldecode($file['folder_preview_url'] ?? ''),
            '%folder_cloud_shared_url%' => urldecode($file['folder_shared_url'] ?? ''),
            '%folder_absolute_path%' => $file['folder_absolute_path'] ?? '',
            '%folder_relative_path%' => $file['folder_relative_path'] ?? '',
        ]);

        // Replace the {{#each files}}...{{/each}} block with the generated HTML
        $return = preg_replace('/{{#each files}}.*?{{\/each}}/s', $file_html, $return);

        // Apply the global placeholders
        return Placeholders::apply($return);
    }

    public static function update_deprecated($files = [])
    {
        foreach ($files as &$file) {
            if (isset($file['link'])) {
                $file['preview_url'] = $file['link'];
                $file['shared_url'] = $file['link'];
            }
            unset($file['link']);

            if (isset($file['folderurl'])) {
                $file['folder_preview_url'] = $file['folderurl'];
                $file['folder_shared_url'] = $file['folderurl'];
            }
            unset($file['folderurl']);
        }

        return $files;
    }
}
