<?php
/*
Template name: Category Products Page
*/

use Timber\Timber;

$context = Timber::get_context();

$pageFields = get_fields();

global $wp;
//echo home_url( $wp->request );

//===========================================================
// Display info
//===========================================================
$context['header']['isHome'] = true; // to have white logo and nav menu
//$context['archivePage'] = json_decode(file_get_contents(get_template_directory_uri() . '/views/sections/archiveSection/index.json'), true);
//$currentLanguage = get_bloginfo('language');
// language information
if((get_bloginfo('language') == 'de-DE')) {
    $currentLanguage = 'de-AT';
//} else if(get_bloginfo('language') == 'nl-NL' && strpos($_SERVER['HTTP_HOST'],'faac.nl')){
} else if(strpos($_SERVER['HTTP_HOST'],'faac.nl')){
    $currentLanguage = 'nl-BV';
} else {
    $currentLanguage = get_bloginfo('language');
}
// Use cached API response to avoid 429 errors
$sidebar_list = get_cached_api_response(API_PIM . 'sidebar?lang=' . $currentLanguage, 3600);
if (empty($sidebar_list)) {
    // Fallback to empty array if API fails
    $sidebar_list = array('archiveFilter' => array());
}

/* foreach ($sidebar_list['archiveFilter'] as $archiveFilter) :
    if($archiveFilter['type'] == 'list'):
        foreach ($archiveFilter['category'] as $category) :
            echo 'category - ' .$category['mainTitle'].' - '. $category['ID_cat'] . '<br>';
            foreach ($category['subCategory'] as $subCategory) :
                echo 'subCategory - ' .$subCategory['mainTitle'].' - '. $subCategory['ID_cat'] . '<br>';
                foreach ($subCategory['subSubCategory'] as $subSubCategory) :
                    echo 'subSubCategory - ' .$subSubCategory['mainTitle'].' - '. $subSubCategory['ID_cat'] . '<br>';
                endforeach;
            endforeach;
        endforeach;
    endif;
endforeach; */

/* echo '<!--pre>';
print_r($sidebar_list);
echo'</pre-->'; */

// API example to category or product list
// https://pimiaki.faac.help/api/family?lang=fr-FR&cat=1&subcat=1&subsubcat=1


$search = '';
$add_url = '';

$request_uri = explode("/", $_SERVER['REQUEST_URI']);
$request_uri = array_filter($request_uri);
/* echo '<!--pre>';
print_r($request_uri);
echo'</pre-->'; */

if(count($request_uri) == 1 && array_shift(array_values($request_uri)) == __( 'products','faactheme' )){
    wp_redirect( '/'.__( 'products','faactheme' ).'/'.$sidebar_list['archiveFilter'][0]['category'][0]['url'], 301 );
    exit();
}

foreach ($request_uri as $key => $value) {
        /** check if exists category, else redirect to 404  */
        if(!empty($value) && $value != __( 'products','faactheme' )){
            //echo '<!--pre>'.$value.' - '.get_url_category($sidebar_list, '/' . $value, 'ID_cat').'</pre--><br>';
            if(empty(get_url_category($sidebar_list, '/' . $value, 'ID_cat'))){
                wp_redirect( '/404-page/', 301 );
                exit();
            }
        }
        /** end check */
        if ($key == (count($request_uri))) $search = '/' . $value;
}
//echo $search;

$add_url = get_url_category($sidebar_list, $search, 'ID_cat');

/** inserire QUI la funzione per ricavare gli ID delle varie categorie in base allo slug **/

//if ($add_url == '') $add_url = '&cat=1';

// Use cached API response to avoid 429 errors
$archiveList = get_cached_api_response(API_PIM . 'family?lang=' . $currentLanguage . $add_url, 3600);
if (empty($archiveList)) {
    // Fallback structure if API fails
    $archiveList = array(
        'hero' => array('post' => array('title' => '', 'excerpt' => '', 'thumbnail' => array())),
        'archive' => array('archiveElement' => array('list' => array()))
    );
}

//echo '<!--pre>'.API_PIM . 'family?lang=' . $currentLanguage . $add_url.'</pre-->';

/* if($search != '/'.__( 'products','faactheme' ) && !is_array($archiveList)) {
    wp_redirect( '/404-page/', 301 );
    exit();
} */

/**
 * Changes tag title.
 *
 * @param string $title        The current title.
 * @param object $presentation The presentation object containing the necessary data.
 *
 * @return string The altered title tag.
 */

 $categoryTitle = $archiveList['hero']['post']['title'];
 $categoryDescrition = $archiveList['hero']['post']['excerpt'];
 
 function change_category_title( $title, $presentation ) {
     global $categoryTitle;
/*      echo '<!--pre>';
     print_r($presentation);
     echo '<pre-->'; */
 
     if(strpos($presentation->model->permalink, '/'.__( 'products','faactheme' ).'/') !== false) {
        //return  __( 'Category','faactheme' ).' '.$categoryTitle.' - '.get_bloginfo( 'name' );
        return  $categoryTitle.' - '.get_bloginfo( 'name' );
     } 
     return $title;
 }
   
 add_filter( 'wpseo_title', 'change_category_title', 10, 2 );
 add_filter( 'wpseo_opengraph_title', 'change_category_title', 10, 2 );

function change_category_description( $description ) {
    global $categoryDescrition;

    $description = strip_tags($categoryDescrition);

    return $description;
}
  
add_filter( 'wpseo_metadesc', 'change_category_description', 10, 2 );
add_filter( 'wpseo_opengraph_desc', 'change_category_description', 10, 2 );

function change_filter_canonical( $canonical ) {

    $canonical = home_url( $_SERVER['REQUEST_URI'] );
  
    return $canonical;
}
  
add_filter( 'wpseo_canonical', 'change_filter_canonical' );
 
 get_header();

//$archiveList = json_decode(file_get_contents(API_PIM . 'family?lang=' . $currentLanguage . '&cat=1'), true);

 /*
if($subcat !== null){
    $archiveList = json_decode(file_get_contents(API_PIM . 'family?lang=' . $currentLanguage . '&cat=' . $cat . '&subcat=' . $subcat), true);
}
if($subsubcat !== null){
    $archiveList = json_decode(file_get_contents(API_PIM . 'family?lang=' . $currentLanguage .'&cat=' . $cat . '&subcat=' . $subcat .'&subsubcat=' . $subsubcat), true);
} */

/* if( (is_null($subcat) && is_null($subsubcat)) or ( isset($subcat) && isset($subsubcat))){
    $archiveList = json_decode(file_get_contents(API_PIM . 'family?lang=' . $currentLanguage . '&cat=' . $cat), true);
}elseif (is_null($subsubcat) or isset($subsubcat)) {
$archiveList = json_decode(file_get_contents(API_PIM . 'family?lang=' . $currentLanguage . '&cat=' . $cat . '&subcat=' . $subcat), true);
}else{
    $archiveList = json_decode(file_get_contents(API_PIM . 'family?lang=' . $currentLanguage .'&cat=' . $cat . '&subcat=' . $subcat .'&subsubcat=' . $subsubcat), true);
} */

//$archiveList = json_decode(file_get_contents(API_PIM . 'family?lang=' . $currentLanguage . '&cat=1'), true);

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

$context['categoryPage']['archive']['archiveFilter'] = $sidebar_list['archiveFilter'];
$context['categoryPage']['hero'] = $archiveList['hero'];
//$context['categoryPage']['hero']['prefixbreadurl'] = __( '/products','faactheme' );

$context['categoryPage']['hero']['breadcrumbs'][0] = array(
    "ID_cat" => "0",
    "text" => __( 'Products','faactheme' )
);
foreach($context['categoryPage']['hero']['breadcrumbs'] as $key => $value){
    $context['categoryPage']['hero']['breadcrumbs'][$key]['slug'] = sanitize_title(stripAccents($context['categoryPage']['hero']['breadcrumbs'][$key]['text']));
}

$context['categoryPage']['archive']['archiveFilter'][0]['current_url'] = $_SERVER['REQUEST_URI'];
$context['categoryPage']['archive']['archiveFilter'][0]['prefix_url'] = __( '/products','faactheme' );

/* echo '<!--pre>';
print_r($archiveList);
echo '</pre-->'; */

foreach($archiveList['archive']['archiveElement']['list'] as $item){
    /* $link = preg_replace('/\s+/', '-', $item['title']);
    $link = strtolower($link); */

    $link = sanitize_title(stripAccents($item['title']));
    
    if($item['ID_product'] == null){
        $btn_text = __( 'Explore category','faactheme' );
        $prefix_url = home_url($wp->request);
        $add_class = '';
        $add_id = '';
        $product_id = '';
    }else{
        $btn_text = __( 'See product','faactheme' );
        //$prefix_url = str_replace(__( '/products/','faactheme' ),__( '/p/','faactheme' ), home_url($wp->request));
        $prefix_url = home_url($wp->request);
        $add_class = ' send-product-id';
        $add_id = 'product-id-'.$item['ID_product'];
        $product_id = $item['ID_product'];
        //$link .= '?product_id='.$item['ID_product']; 
        $link .= '/p/'.$item['ID_product'].'/'; 
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
        )
    );
}

Timber::render('templates/categoryPage/index.twig', $context);

//===========================================================
// Add more elements here
//===========================================================


get_footer();
