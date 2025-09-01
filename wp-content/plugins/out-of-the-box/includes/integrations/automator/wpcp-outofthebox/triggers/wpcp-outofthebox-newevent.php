<?php

use Uncanny_Automator\Recipe;

/**
 * Class WPCP_OUTOFTHEBOX_NEWEVENT.
 */
class WPCP_OUTOFTHEBOX_NEWEVENT
{
    use Recipe\Triggers;

    /**
     * Set up Automator trigger constructor.
     */
    public function __construct()
    {
        $this->integration = 'wpcp-outofthebox';
        $this->trigger_code = 'WPCP_OUTOFTHEBOX_NEW_EVENT';
        $this->trigger_meta = 'WPCP_OUTOFTHEBOX_EVENT_TYPE';
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

    protected function prepare_to_run($data)
    {
        $this->set_conditional_trigger(true);
    }

    /**
     * Check Event Type against the trigger meta.
     *
     * @param $args
     */
    protected function trigger_conditions(...$args)
    {
        $this->do_find_any(true); // Support "Any event" option

        // Find the tag in trigger meta
        $this->do_find_this($this->get_trigger_meta());

        $this->do_find_in([$args[0][0]]);
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
        $this->set_sentence(sprintf(esc_attr__('The plugin registers the following event: {{Select event:%1$s}}', 'wpcloudplugins-automator'), $this->get_trigger_meta()));
        // Translators: Some information for translators
        $this->set_readable_sentence(esc_attr__('The plugin registers a {{new event}}', 'wpcloudplugins-automator'));

        $this->add_action('outofthebox_event_added', 20, 4);

        $options = [
            Automator()->helpers->recipe->wpcp_outofthebox->options->list_events(null, $this->trigger_meta),
        ];

        $this->set_options($options); // Adding options so that {{a page:%1$s}} could display it in Recipe UI

        $this->register_trigger();
    }
}
