<?php

/*
Template name: Homepage
*/

//use Timber\Timber;

$context = Timber::get_context();

$pageFields = get_fields();

get_header();

//echo '<h1>Homepage</h1>';




//===========================================================
// Slider Header Homepage
//===========================================================
$context['header']['isHome'] = true;
$context['swiperheader']['isHome'] = true;
$context['swiperheader']['homeSlider'] = $pageFields['homeSlider'];


//===========================================================
// HOME PAGE SECTION
//===========================================================
// product line list
$context['productLinesSection'] = $pageFields['productLinesSection'];
// slider news
$context['sliderNews'] = $pageFields['sliderNews'];
// Three card section
$context['cardsSection'] = $pageFields['cards_section_home'];

//===========================================================
// BOX MULTI INFORMATION
//===========================================================

$context['infoboxes']['equal'] = $pageFields['info_boxes']['equal'];

foreach ($pageFields['info_boxes']['boxes'] as $item ) {
    $context['infoboxes']['boxes'][] = array(
        "title_group" => array(
            "over_title" => $item['title_group']['over_title'],
            "title" => $item['title_group']['title']
        ),
        "content" => $item['content'],
        "cta" => array(
            "url" => $item['cta']['url'],
            "text" => $item['cta']['text'],
            "spanText" => true,
            "style" => 'btn read-more linear-in'
        )
    );
}
//===========================================================
// ACTIVITIES SLIDER
//===========================================================

//$context['activitiesSection'] = json_decode(file_get_contents(get_template_directory().'/views/sections/activitiesSliderHome/index.json'), true);
//$context['activitiesSection'] = $pageFields['activitiesSection'];
$context['activitiesSection'] = array(
    'activities_section_title' => $pageFields['activitiesSection']['activities_section_title'],
    'activities_title_color' => $pageFields['activitiesSection']['activities_title_color'],
    'activities_section_description' => $pageFields['activitiesSection']['activities_section_description'],
    'activities_description_color' => $pageFields['activitiesSection']['activities_description_color'],
    'sectionBg' => $pageFields['activitiesSection']['sectionBg'],
    //'alignTitling' => $pageFields['activitiesSection']['alignTitling'],
    'add_description' => $pageFields['activitiesSection']['add_description'],
    'postDescription' => $pageFields['activitiesSection']['postDescription'],
    'navigationColor' => 'white'
);
$context['activitiesSection']['sliderActivities'] = array();
foreach ($pageFields['activitiesSection']['activities_slider'] as $slide) {
    $context['activitiesSection']['activitiesSlider']['sliderActivities'][] = array(
        "img" => $slide['img'],
        "description" => $slide['description'],
        "url" => $slide['url'],
    );
}

Timber::render('templates/homepage/index.twig', $context);

//===========================================================
// Add more elements here
//===========================================================


get_footer();