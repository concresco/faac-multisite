<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package faac
 */
$context = Timber::get_context();
$pageFields = get_fields();

get_header();

$context['post'] = new Timber\Post();
$context['breadcrumbs'] = $pageFields['breadcrumbs'];
$context['additionalContent'] = $pageFields['additionalContent'];

//===========================================================
// BUILDER MULTI PAGE WITH ACF
//===========================================================


$context['multi_page_builder'] = $pageFields['multi_page_builder'];


for ($k = 0; $k < sizeof($context['multi_page_builder']); $k++) {

    if ($context['multi_page_builder'][$k]['acf_fc_layout'] == 'activities_slider_layout') {

        $context['multi_page_builder'][$k]['activitiesSection'] = array(
            'activities_section_title' => $pageFields['multi_page_builder'][$k]['activitiesSection']['activities_section_title'],
            'activities_title_color' => $pageFields['multi_page_builder'][$k]['activitiesSection']['activities_title_color'],
            'activities_section_description' => $pageFields['multi_page_builder'][$k]['activitiesSection']['activities_section_description'],
            'activities_description_color' => $pageFields['multi_page_builder'][$k]['activitiesSection']['activities_description_color'],
            'sectionBg' => $pageFields['multi_page_builder'][$k]['activitiesSection']['sectionBg'],
            //'alignTitling' => $pageFields['multi_page_builder'][$k]['activitiesSection']['alignTitling'],
            'add_description' => $pageFields['multi_page_builder'][$k]['activitiesSection']['add_description'],
            'postDescription' => $pageFields['multi_page_builder'][$k]['activitiesSection']['postDescription']
        );
        $slider = array();

        foreach($pageFields['multi_page_builder'][$k]['activitiesSection']['activities_slider'] as $slide){

            $slider['sliderActivities'][] = array(
                "img" => $slide['img'],
                "description" => $slide['description'],
                "url" => $slide['url'],
            );
        }
        $context['multi_page_builder'][$k]['activitiesSection']['activitiesSlider'] = $slider;
    }
}

Timber::render('/views/templates/genericPage/index.twig', $context);

get_footer();
