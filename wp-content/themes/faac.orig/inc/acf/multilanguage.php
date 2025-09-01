<?php
if (function_exists('acf_add_local_field_group')) :

    acf_add_local_field_group(array(
        'key' => 'group_629f587b7e226',
        'title' => 'Multilanguage',
        'fields' => array(
            array(
                'key' => 'field_629f629b20e9e',
                'label' => 'Language',
                'name' => 'language',
                'type' => 'checkbox',
                'instructions' => 'select all the languages you want to view for site translations',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(
                    'UK' => 'England',
                    'FR' => 'France',
                    'AT' => 'Austria',
                    'SI' => 'Slovenia',
                    'HU' => 'Hungary',
                    'NL' => 'Holland',
                ),
                'allow_custom' => 0,
                'default_value' => array(),
                'layout' => 'horizontal',
                'toggle' => 0,
                'return_format' => 'array',
                'save_custom' => 0,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'theme-general-settings',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
    ));

endif;
