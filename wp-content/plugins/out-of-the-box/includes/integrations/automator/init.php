<?php

namespace TheLion\OutoftheBox\Integrations;

defined('ABSPATH') || exit;

class Automator
{
    /**
     * @var string
     */
    public $integration_code = 'wpcp-outofthebox';

    /**
     * @var string
     */
    public $directory;

    public function __construct()
    {
        $this->directory = __DIR__.DIRECTORY_SEPARATOR.$this->integration_code;
        add_action('automator_configuration_complete', [$this, 'add_this_integration']);
    }

    /**
     * Add integration and all related files to Automator so that it shows up under Triggers / Actions.
     *
     * @return null|bool
     */
    public function add_this_integration()
    {
        if (!function_exists('automator_add_integration')) {
            wp_die('automator_add_integration() function not found. Please upgrade Uncanny Automator to version 3.0+');
        }

        \automator_add_integration($this->directory);

        if (empty($this->integration_code) || empty($this->directory)) {
            return false;
        }

        \automator_add_integration_directory($this->integration_code, $this->directory);
    }
}

new Automator();
