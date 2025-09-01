<?php

/*
Template name: Contact Page
*/

//use Timber\Timber;

$context = Timber::get_context();

$pageFields = get_fields();

get_header();

//echo '<h1>Contact page</h1>';

$context['titling']['pageTitle'] = get_the_title();
$context['titling']['paragraph'] = apply_filters('the_content', $post->post_content, $post->ID);
$context['titling']['breadcrumbs'] = $pageFields['breadcrumbs'];

//showDebugInfo($pageFields['breadcrumbs']);
//===========================================================
// Form organism
//===========================================================

// $context['formdata'] = json_decode(file_get_contents(get_template_directory_uri() . '/views/organisms/form/index.json'), true);
// $context['formdata'] = $pageFields['formdata'];

//$context['infoboxes'] = json_decode(file_get_contents(get_template_directory().'/views/organisms/infoBoxes/index.json'), true);

//===========================================================
// FLEXIBLE CONTENT
//===========================================================
$context['flexibleContent'] = $pageFields['flexible_content'];

//===========================================================
// BOX MULTI INFORMATION IN FLEXIBLE CONTENT
//===========================================================

for ($k=0; $k< sizeof($context['flexibleContent']); $k++){

    if ($context['flexibleContent'][$k]['acf_fc_layout'] == 'multibox_layout'){
    
        $i = 0;
        $multibox = array();
        foreach($context['flexibleContent'][$k]['multi_boxes_repeter'] as $singleBox) {
            
            $el = array();
            
            $el['title'] = $singleBox['title'];
            $el['paragraph'] = $singleBox['paragraph'];
            $el['infoboxes'] = array(    
                "equal" => $singleBox['infoboxes']['equal'],
                "boxes" => array()
            );
            foreach ($singleBox['infoboxes']['boxes'] as $item) {
                $el['infoboxes']['boxes'][] = array(
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
            array_push($multibox, $el);
            $i++;
        }
        $context['flexibleContent'][$k]['flex_multibox'] = $multibox;
    }
}
//showDebugInfo($pageFields['flexible_content']);

Timber::render('templates/contactPage/index.twig', $context);

get_footer();