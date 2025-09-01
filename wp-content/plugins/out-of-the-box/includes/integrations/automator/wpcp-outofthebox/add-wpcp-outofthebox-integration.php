<?php

use Uncanny_Automator\Recipe;

/**
 * Class Add_Wpcp_OutoftheBox_Integration.
 */
class Add_Wpcp_OutoftheBox_Integration
{
    use Recipe\Integrations;

    /**
     * Add_Wpcp_OutoftheBox_Integration constructor.
     */
    public function __construct()
    {
        $this->setup();
    }

    protected function setup()
    {
        $this->set_integration('wpcp-outofthebox');
        $this->set_external_integration(true);
        $this->set_name('Dropbox');
        $this->set_icon('dropbox_logo.svg');
        $this->set_icon_path(OUTOFTHEBOX_ROOTDIR.'/css/images/');
        $this->set_plugin_file_path(OUTOFTHEBOX_ROOTDIR.'/out-of-the-box.php');
    }

    /**
     * @return bool
     */
    public function plugin_active()
    {
        return true;
    }
}
