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
//===========================================================
// NEWS SECTION
//===========================================================

// displays 3 posts based on manual or latest choice
// set in theme option

$news = get_field('blogExcerpts', 'option');
//showDebugInfo($news);

$context['news']['sectionTitle'] = $news['title'];
$postSelected = $news['blogPosts'];

if ($news['postSelectionMethod'] == 'manual'){
    foreach($postSelected as $post) {
        $ID = $post['selectedPost'];
    
        $context['news']['postList'][] =
        array(
            'post_title' => get_the_title($ID),
            'thumbnails' => array(
                'url' => get_the_post_thumbnail_url($ID, 'large'),
            ),
            'post_url' => get_permalink($ID),
        );
    }
}
elseif ($news['postSelectionMethod'] == 'last'){
    $args = array(
        'numberposts' => 3
    );
    $last_posts = get_posts($args);
    foreach($last_posts as $post){
        $context['news']['postList'][] =
        array(
            'post_title' => get_the_title($post->ID),
            'thumbnails' => array(
                'url' => get_the_post_thumbnail_url($post->ID, 'large'),
            ),
            'post_url' => get_permalink($post->ID),
        );
    }
}
//
// END NEWS BLOCK
//===========================================================


Timber::render('templates/homepage/index.twig', $context);

//===========================================================
// Add more elements here
//===========================================================


get_footer();