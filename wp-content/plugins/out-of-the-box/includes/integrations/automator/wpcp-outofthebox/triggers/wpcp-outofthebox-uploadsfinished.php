<?php

use Uncanny_Automator\Recipe;

/**
 * Class WPCP_OUTOFTHEBOX_UPLOADSFINISHED.
 */
class WPCP_OUTOFTHEBOX_UPLOADSFINISHED
{
    use Recipe\Triggers;

    /**
     * Set up Automator trigger constructor.
     */
    public function __construct()
    {
        $this->integration = 'wpcp-outofthebox';
        $this->trigger_code = 'WPCP_OUTOFTHEBOX_UPLOADSFINISHED';
        $this->trigger_meta = 'WPCP_OUTOFTHEBOX_USERS';
        $this->setup_trigger();
    }

    /**
     * @return bool
     */
    public function validate_trigger(...$args)
    {
        return true;
    }

    public function do_continue_anon_trigger(...$args)
    {
        return true;
    }

    /**
     * @param $notation
     * @param $subject
     * @param $value_in_trigger
     * @param mixed $where
     * @param mixed $condition
     *
     * @return false|int
     */
    public function conditions_matched($notation, $where, $condition)
    {
        // Conditions met if 'Everyone' is set
        if (in_array('-1', $where)) {
            return true;
        }

        $matches = \array_intersect($condition, $where);

        return (bool) $matches;
    }

    public function validate_conditions(...$args)
    {
        $wp_user = wp_get_current_user();

        if (empty($wp_user) || (!$wp_user instanceof \WP_User)) {
            $user = ['-1'];
        } else {
            $user = array_merge([$wp_user->ID], $wp_user->roles);
        }

        // Find the text in email subject
        return $this->find_all($this->trigger_recipes())
            ->where([$this->get_trigger_meta()])
            ->match([$user])
            ->format(['json_decode'])
            ->get()
        ;
    }

    protected function prepare_to_run($data)
    {
        $this->set_conditional_trigger(true);
    }

    /**
     * Define and register the trigger by pushing it into the Automator object.
     */
    protected function setup_trigger()
    {
        $this->set_integration($this->integration);
        $this->set_trigger_code($this->trigger_code);
        $this->set_trigger_meta($this->trigger_meta);
        $this->set_is_login_required(false);
        $this->set_trigger_type('anonymous');
        $this->set_is_pro(false);
        // Translators: Some information for translators
        $this->set_sentence(sprintf(esc_attr__('{{An user:%1$s}} has finished uploading files', 'wpcloudplugins-automator'), $this->get_trigger_meta()));
        // Translators: Some information for translators
        $this->set_readable_sentence(esc_attr__('{{An user}} has finished uploading files', 'wpcloudplugins-automator'));

        $this->add_action('outofthebox_upload_post_process', 20, 2);

        $options = [
            Automator()->helpers->recipe->wpcp_outofthebox->options->list_users(null, $this->trigger_meta),
        ];

        $this->set_options($options); // Adding options so that {{a page:%1$s}} could display it in Recipe UI

        $this->register_trigger();
    }
}
