<?php
if (function_exists('acf_add_local_field_group')) :

    acf_add_local_field_group(array(
        'key' => 'group_62b04d3a1361e',
        'title' => 'Generic page fields',
        'fields' => array(
            array(
                'key' => 'field_62b04dcce5a84',
                'label' => 'Breadcrumbs',
                'name' => 'breadcrumbs',
                'type' => 'repeater',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '100',
                    'class' => '',
                    'id' => '',
                ),
                'collapsed' => 'field_62b04defe5a85',
                'min' => 0,
                'max' => 0,
                'layout' => 'block',
                'button_label' => '',
                'sub_fields' => array(
                    array(
                        'key' => 'field_62b04defe5a85',
                        'label' => 'Text',
                        'name' => 'text',
                        'type' => 'text',
                        'instructions' => 'The text you want to appear in the breadcrumb',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '50',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => 'Es. About us',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                    array(
                        'key' => 'field_62b04e10e5a87',
                        'label' => 'Url',
                        'name' => 'url',
                        'type' => 'text',
                        'instructions' => 'Leave blank if the breadcrumb is not to be associated with any page, or if it is the last part of the breadcrumb',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '50',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => 'Es. About us',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                ),
            ),
            array(
                'key' => 'field_62b0654259a4d',
                'label' => 'Additional content',
                'name' => 'additionalContent',
                'type' => 'wysiwyg',
                'instructions' => 'Located below the page header image',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '100',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
                'delay' => 0,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'page',
                ),
                array(
                    'param' => 'page_template',
                    'operator' => '==',
                    'value' => 'contact-page.php',
                ),
                array(
                    'param' => 'page_template',
                    'operator' => '==',
                    'value' => 'homepage.php',
                ),
                array(
                    'param' => 'page_template',
                    'operator' => '==',
                    'value' => 'archive-list.php',
                ),
            ),
            array(
                array(
                    'param' => 'page_template',
                    'operator' => '==',
                    'value' => 'default',
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
