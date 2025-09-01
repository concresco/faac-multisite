<?php

namespace TheLion\OutoftheBox\Integrations;

use TheLion\OutoftheBox\Core;

defined('ABSPATH') || exit;

class FL_WPCP_OutoftheBox_Module extends \FLBuilderModule
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Dropbox',
            'description' => sprintf(\esc_html__('Insert your %s content', 'wpcloudplugins'), 'Dropbox'),
            'category' => 'WP Cloud Plugins',
            'dir' => OUTOFTHEBOX_ROOTDIR.'/includes/integrations/beaverbuilder/modules/wpcp_outofthebox_module/',
            'url' => OUTOFTHEBOX_ROOTPATH.'/includes/integrations/beaverbuilder/modules/wpcp_outofthebox_module/',
            'icon' => OUTOFTHEBOX_ROOTDIR.'/css/images/dropbox_logo.svg',
        ]);
    }

    public function get_icon($icon = '')
    {
        return file_get_contents($icon);
    }

    public function enqueue_scripts()
    {
        Core::instance()->load_scripts();
        Core::instance()->load_styles();

        wp_enqueue_script('WPCloudplugin.Libraries');
        wp_enqueue_script('OutoftheBox.ShortcodeBuilder');
        wp_enqueue_style('OutoftheBox');
    }
}

// Register the module and its form settings.
\FLBuilder::register_module('\TheLion\OutoftheBox\Integrations\FL_WPCP_OutoftheBox_Module', [
    'general' => [ // Tab
        'title' => esc_html__('General'), // Tab title
        'sections' => [ // Tab Sections
            'general' => [ // Section
                'title' => esc_html__('Module', 'wpcloudplugins'), // Section Title
                'fields' => [ // Section Fields
                    'raw_shortcode' => [
                        'type' => 'wpcp_outofthebox',
                        'label' => esc_html__('Module Configuration', 'wpcloudplugins'),
                        'default' => '',
                    ],
                ],
            ],
        ],
    ],
]);
