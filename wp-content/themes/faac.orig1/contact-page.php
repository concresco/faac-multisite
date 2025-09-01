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
if($context['flexibleContent']) {
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
        if ($context['flexibleContent'][$k]['acf_fc_layout'] == 'reseller_layout') {

            $context['flexibleContent'][$k]['resellers_search'] = json_decode(file_get_contents(get_template_directory_uri() . '/views/organisms/resellersSearchBlock/index.json'), true);

            $context['flexibleContent'][$k]['resellers_search'] = array(
                'sectionTitle' => $pageFields['flexible_content'][$k]['resellers_search']['sectionTitle'],
                'paragraph' => $pageFields['flexible_content'][$k]['resellers_search']['paragraph'],
                'paragraphClass' => $pageFields['flexible_content'][$k]['resellers_search']['paragraphClass'],
                'search_form' => array(
                    'formId' => 'resellers_search',
                    'formMethod' => 'POST',
                    'formAction' => '#resellers_search',
                    'formClass' => 'needs-validation border-0',
                    'rows' => array(
                        0 => array(
                            'title' => false,
                            'class' => '',
                            'field' => array(
                                'type' => 'text',
                                'mandatory' => true,
                                'name' => 'cap',
                                'placeholder' => 'insert cap',
                                'label' => $pageFields['flexible_content'][$k]['resellers_search']['search_form']['rows']['field']['label'],
                            )
                        ),
                    ),
                    'submit_Area_Fields' => array(
                        0 => array(
                            'style' => 'text-center',
                            'type' => 'submit',
                            'text' => $pageFields['flexible_content'][$k]['resellers_search']['search_form']['submit_Area_Fields']['text'],
                            'innerStyle' => 'btn btn-lg btn-primary',
                        ),
                    ),
                    'additionalContent' => false,
                ),
            );
            if (isset($_POST['cap'])){
                $cap = $_POST['cap'];
                $loop = new WP_Query(array('post_type' => 'resellers'));
                $resellers = $loop->posts;

                foreach ($resellers as $reseller) {
                    $res_addInfo = get_fields($reseller->ID);

                    foreach ($res_addInfo['cap_range'] as $range) {
                        $start = $range['cap_start'];
                        $end = $range['cap_end'];
            
                        if (intval($cap) >= intval($start) && intval($cap) <= intval($end)) {
                            $context['flexibleContent'][$k]['resellers_search']['resellers'][] = array(
                                'name' => $reseller->post_title,
                                'content' => $reseller->post_content,
                            );
                        }
                    }
                }
                // set default reseller in case of invalid postcode
                if (empty($context['flexibleContent'][$k]['resellers_search']['resellers'])){
                    foreach ($resellers as $reseller) {
                        $res_addInfo = get_fields($reseller->ID);
                        if(!empty($res_addInfo['default_reseller'])) {
                            
                            $context['flexibleContent'][$k]['resellers_search']['resellers'][] = array(
                                'name' => $reseller->post_title,
                                'content' => $reseller->post_content,
                            );
                        }
                    }
                }
            }
        }
        

    }
}


//showDebugInfo($pageFields['flexible_content']);

foreach($context['flexibleContent'] as $keys => $values){
    foreach($values as $key => $value){
        if($key == 'formdata'){
            $context['flexibleContent'][$keys]['formdata']['textWait'] = __( 'Please wait...','faactheme' );
            $context['flexibleContent'][$keys]['formdata']['errFileSize'] = __( 'File size too large error, 8MB limit','faactheme' );
            $context['flexibleContent'][$keys]['formdata']['LabelFileSize'] = __( 'Maximum file size: 8MB','faactheme' );
            $context['flexibleContent'][$keys]['formdata']['errFileType'] = __( 'Unsupported file type error:  only .gif, .png, .jpg, .jpeg, .doc, .docx, .xls, .xlsx, .ppt, .pptx, .odt, .ods, .odp, .pdf, .zip, .rar, .7zip are supported!','faactheme' );
        }
    }
}
/* 
echo '<!--pre>';
print_r($context['flexibleContent']);
echo '</pre-->'; */
/* Aggiunta codice per visualizzare navigazione categorie-prodotti dal PIM - 20042023 */

if($pageFields['category_products'] && ($pageFields['category_products']) != 'cat-0') {
    //$currentLanguage = get_bloginfo('language');
    // language information
    if((get_bloginfo('language') == 'de-DE')) {
        $currentLanguage = 'de-AT';
    } else if(get_bloginfo('language') == 'nl-NL' && strpos($_SERVER['HTTP_HOST'],'faacbv.com')){
        $currentLanguage = 'nl-BV';
    } else {
        $currentLanguage = get_bloginfo('language');
    }

    $context['categoryPage'] = array(
        'archive' => array(
            'activeFilter' => array(
                'FilterLabel' => 'Filters',
                /* 'filtersSelected' => array(
                    0 => array(
                        'filter' => 'Retail',
                        'url' => '#'
                    ),
                    1 => array(
                        'filter' => 'Hospitality',
                        'url' => '#'
                    ),
                    2 => array(
                        'filter' => 'Public administration',
                        'url' => '#'
                    ),
                    3 => array(
                        'filter' => 'Solutions to aid social distancing',
                        'url' => '#'
                    ),
                    4 => array(
                        'filter' => 'Make Doors Contactless Quickly ',
                        'url' => '#'
                    ),
                ), */
            ),
        ),
    );

    $sidebar_list = json_decode(file_get_contents(API_PIM . 'sidebar?lang=' . $currentLanguage), true);
    /** remove temporary filter section - 09052023 */
    unset($sidebar_list['archiveFilter'][1]);
    unset($sidebar_list['archiveFilter'][2]);
    /** end remove temporary filter section */

    $context['categoryPage']['archive']['archiveFilter'] = $sidebar_list['archiveFilter'];

    $add_url = str_replace(array("-","_"),array("=","&"),$pageFields['category_products']);

    $archiveList = json_decode(file_get_contents(API_PIM . 'family?lang=' . $currentLanguage . '&'.$add_url), true);

    $hero = $archiveList['hero'];
    //$context['categoryPage']['hero']['prefixbreadurl'] = __( '/products','faactheme' );

    $prefix_url = '/';
    $hero['breadcrumbs'][0] = array(
        "text" => __( 'Products','faactheme' )
    );
    foreach($hero['breadcrumbs'] as $key => $value){
        //$hero['breadcrumbs'][$key]['slug'] = sanitize_title(stripAccents($hero['breadcrumbs'][$key]['text']));
        $prefix_url .= sanitize_title(stripAccents($hero['breadcrumbs'][$key]['text'])).'/';
    }
    
    $context['categoryPage']['archive']['archiveFilter'][0]['current_url'] = $prefix_url;
    $context['categoryPage']['archive']['archiveFilter'][0]['prefix_url'] = __( '/products','faactheme' );

    foreach($archiveList['archive']['archiveElement']['list'] as $item){
        /* $link = preg_replace('/\s+/', '-', $item['title']);
        $link = strtolower($link); */

        $link = sanitize_title(stripAccents($item['title'])).'/';
        $prefix_url = home_url($wp->request);
        
        if($item['ID_product'] == null){
            $btn_text = __( 'Explore category','faactheme' );
            //$prefix_url = home_url($wp->request);
            $add_class = '';
            $add_id = '';
            $product_id = '';
        }else{
            $btn_text = __( 'See product','faactheme' );
            //$prefix_url = home_url($wp->request);
            $add_class = ' send-product-id';
            $add_id = 'product-id-'.$item['ID_product'];
            $product_id = $item['ID_product'];
            //$link .= '?product_id='.$item['ID_product']; 
            $link .= 'p/'.$item['ID_product'].'/'; 
            //$link .= '/';
        }
        $context['categoryPage']['archive']['archiveElement']['list'][] = array(
            'title' => $item['title'],
            'excerpt' => $item['excerpt'],
            'thumbnail' => $item['thumbnail'],
            'deepen' => array(
                'add_class' => $add_class,
                'add_id' => $add_id,
                'product_id' => $product_id,
                'text' => $btn_text,
                'link' => $prefix_url. '/' . $link,
                //'link' => $prefix_url.$link.'/',
            )
        );
    }
}    

/* echo '<pre>';
print_r($context['categoryPage']);
echo '</pre>'; */

Timber::render('templates/contactPage/index.twig', $context);

get_footer();