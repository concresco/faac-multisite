<?php
/*
Template name: Product Page
/**
 * Changes tag title.
 *
 * @param string $title        The current title.
 * @param object $presentation The presentation object containing the necessary data.
 *
 * @return string The altered title tag.
 */

use Timber\Timber;

$context = Timber::get_context();

$pageFields = get_fields();

//===========================================================
// Display info
//===========================================================
$context['header']['isHome'] = true; // to have white logo and nav menu

// PRODUCT INFO
// language information
if((get_bloginfo('language') == 'de-DE')) {
    $currentLanguage = 'de-AT';
} else if(get_bloginfo('language') == 'nl-NL' && strpos($_SERVER['HTTP_HOST'],'faacbv.com')){
    $currentLanguage = 'nl-BV';
} else {
    $currentLanguage = get_bloginfo('language');
}

$sidebar_list = json_decode(file_get_contents(API_PIM . 'sidebar?lang=' . $currentLanguage), true);

/** detect categorys for check if exists  */
$request_uri = explode("/", $_SERVER['REQUEST_URI']);

$request_uri = array_filter($request_uri);
array_pop($request_uri);

if (($key = array_search(__( 'products','faactheme' ), $request_uri)) !== false) {
    unset($request_uri[$key]);
}
if (($key = array_search(__( 'p','faactheme' ), $request_uri)) !== false) {
    unset($request_uri[$key]);
}

$productID = ($_REQUEST['product_id']) ? $_REQUEST['product_id'] : get_query_var( 'product_id' );

$product = json_decode(file_get_contents(API_PIM . 'product?' . 'lang=' . $currentLanguage . '&product_id=' . $productID), true);

/** check if is product valid */
if(!is_array($product)) {
    /** riprisitnare */
    /* wp_redirect( '/404-page/', 302 );
    exit(); */
    /* header("HTTP/1.0 404 Not Found");
    header("Location: /404-page/");
    exit(); */
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    include( get_query_template( '404' ) );
    die();
}
/** end check */

/** set variable for check if slug product is correct, else redirect to 404 */
$check_slug_product = array_pop($request_uri);
/** end set */

/** check if is old url with /product/?product_id= */
if($check_slug_product == __( 'product','faactheme' )){
    /** inserire redirect 301 allo slug corretto del prodotto */
    $urlCorrect = '/'. __( 'products','faactheme' );

    foreach($product['breadcrumbs'] as $key => $value){
        if(!empty($value['text'])){
           $urlCorrect .= '/'.sanitize_title(stripAccents($value['text']));
        }
    }
    $urlCorrect .= '/'.sanitize_title(stripAccents($product['title'])).'/p/'.$productID.'/';

    wp_redirect( $urlCorrect, 301 );
    exit();
}
/** end check */

//array_pop($request_uri);

foreach ($request_uri as $value) {
    //echo $value . ' - ' . __( 'product','faactheme' ) . ' ';
    if(!empty($value)) {
        if ($value != __( 'products','faactheme' )){
        //echo '<!--pre>'.$value.' - '.get_url_category($sidebar_list, '/' . $value, 'ID_cat').'</pre--><br>';
            if(empty(get_url_category($sidebar_list, '/' . $value, 'ID_cat'))){
                /** riprisitnare */
                /* wp_redirect( '/404-page/', 302 );
                exit(); */
                global $wp_query;
                $wp_query->set_404();
                status_header(404);
                include( get_query_template( '404' ) );
                die();
            }
        }
    }
    
}

// API URL Example: https://pim-staging.iaki.it/api/product?lang=en-GB&product_id=120

/* echo '<!--pre>';
echo API_PIM . 'product?' . 'lang=' . $currentLanguage . '&product_id=' . $productID;
echo '</pre-->'; */

$productTitle = (!is_null($product['seo']['title'])) ? $product['seo']['title'] : $product['title'];
$productDescrition = (!is_null($product['seo']['description'])) ? $product['seo']['description'] : $product['subtitle'];
$productNoIndex = ($product['seo']['noindex']) ? $product['seo']['noindex'] : 'false';

if($productNoIndex == 'true'){
    add_filter('wpseo_robots', '__return_false');
}

function change_product_title( $title, $presentation ) {
    global $productTitle;
    if(strpos($presentation->model->permalink, '/'.__( 'p','faactheme' ).'/') !== false) {
        return  $productTitle.' - '.get_bloginfo( 'name' );
    } 
    return  $productTitle.' - '.get_bloginfo( 'name' );
    //return $title;
}
  
add_filter( 'wpseo_title', 'change_product_title', 10, 2 );
add_filter( 'wpseo_opengraph_title', 'change_product_title', 10, 2 );

function change_product_description( $description ) {
    global $productDescrition;
    
    $description = $productDescrition;
    
    return  $description;
}
  
add_filter( 'wpseo_metadesc', 'change_product_description', 10, 2 );
add_filter( 'wpseo_opengraph_desc', 'change_product_description', 10, 2 );

function change_filter_canonical( $canonical ) {

    $canonical = home_url( $_SERVER['REQUEST_URI'] );
  
    return $canonical;
}
  
add_filter( 'wpseo_canonical', 'change_filter_canonical' );

get_header();


$context['productInfo']['breadcrumbs'] = $product['breadcrumbs'];
//$context['productInfo']['prefixbreadurl'] = __( '/products','faactheme' );
$context['productInfo']['title'] = $product['title'];

/** check if slug product is correct, else redirect to 404 */
if($check_slug_product != sanitize_title(stripAccents($context['productInfo']['title']))){
    /** riprisitnare */
    /* wp_redirect( '/404-page/', 302 );
    exit(); */
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    include( get_query_template( '404' ) );
    die();
}
/** end check */

$context['productInfo']['subtitle'] = $product['subtitle'];
$context['productInfo']['content'] = $product['content'];

array_unshift($context['productInfo']['breadcrumbs'], array("text"=>__( 'Products','faactheme' )));

$check_for_404_cat = array();
foreach($context['productInfo']['breadcrumbs'] as $key => $value){
    $context['productInfo']['breadcrumbs'][$key]['slug'] = sanitize_title(stripAccents($context['productInfo']['breadcrumbs'][$key]['text']));
    if($context['productInfo']['breadcrumbs'][$key]['text'] != __( 'Products','faactheme' )){
        $check_for_404_cat[] = $context['productInfo']['breadcrumbs'][$key]['slug'];
    }
}

/** check if category in slug is correct, else redirect to 404 */
$new_request_uri = array();
foreach ($request_uri as $key => $value) {
    $new_request_uri[] = $value;
}
if(count($new_request_uri) !== count($check_for_404_cat)){
    /** riprisitnare ??? */
    /*     wp_redirect( '/404-page/', 302 );
    exit(); */
}
if($new_request_uri !== $check_for_404_cat){
    /** riprisitnare ??? */
    /*     wp_redirect( '/404-page/', 302 );
    exit(); */
}


foreach($product['gallery'] as $slide){
    $sizeImg = getimagesize($slide['image_url']);
    // soluzione -> mettere nel PIM se è un'immagine del prodoto stesso
    $context['productInfo']['gallery'][] = array(
        'img' => $slide['image_url'],
        'caption' => strip_tags($slide['text']),
        'style' => 'object-fit: cover;',
        'width' => $sizeImg[0],
        'height' => $slide[1],
    );
}
if(isset($product['bimSection'])){
    $context['productInfo']['bimSection'] = array(
        // "title" => __( 'BIM object','faactheme' ),
        "title" => '',
        "bg_image" => get_template_directory_uri() . "/assets/images/stock/bim-section-products-faac.jpg",
        "bg_color" => "bg-color-white",
        "content" => $product['bimSection'],
    );
}
if(isset($product['videoGallery'])){
    $context['productInfo']['videoGallery'] = $product['videoGallery'];
}
if(isset($product['dataSection'])){
    $context['productInfo']['dataSection'] = $product['dataSection'];

    foreach($product['dataSection']['accordion'] as $key => $value ){
        
        foreach($value as $keyz => $valuez ){
            //echo '<!--pre>'.$keyz.' - '.$valuez.'<pre-->';
            if($keyz == 'accordionBody' && $valuez == ''){
                unset($context['productInfo']['dataSection']['accordion'][$key ]);
            }
                if($keyz == 'accordionTitle' && $valuez == 'Technical Description'){
                    $context['productInfo']['dataSection']['accordion'][$key]['accordionTitle'] = __( 'Technical description','faactheme' );
                }
                if($keyz == 'accordionTitle' && $valuez == 'Automatically green'){
                    $context['productInfo']['dataSection']['accordion'][$key]['accordionTitle'] = __( 'Notes','faactheme' );
                }
                if($keyz == 'accordionTitle' && $valuez == 'Downloads'){
                    $context['productInfo']['dataSection']['accordion'][$key]['accordionTitle'] = __( 'Download','faactheme' );
                    foreach($context['productInfo']['dataSection']['accordion'][$key]['accordionBody'] as $keyz => $valuez){
                        $context['productInfo']['dataSection']['accordion'][$key]['accordionBody'][$keyz]['productId'] = $productID;
                    }
                    if(is_user_logged_in()){
                        $zip = openssl_encrypt(str_replace('api/','',API_PIM) . 'storage/uploads/zip/' . $currentLanguage . '/' . $productID . '.zip|1',  URL_CIPHERING, URL_ENCRYPTION_KEY, "0", URL_ENCRYPTION_IV);
                        $context['productInfo']['dataSection']['accordion'][$key]['download_all'] = '/downloadmanager/?dcat=Download%20all&pid='.$productID.'&f='.$zip;
                        $context['productInfo']['dataSection']['accordion'][$key]['download_all_text'] = __( 'Download all','faactheme' );
                    } else {
                        $context['productInfo']['dataSection']['accordion'][$key]['download_all'] = '/log-in/';
                        $context['productInfo']['dataSection']['accordion'][$key]['download_all_text'] = __( 'Sign in to download all','faactheme' );
                    }
                }
        }
    }
}

/* echo '<!--pre>';
print_r($context['productInfo']['dataSection']['accordion']);
echo '<pre-->'; */

/* 
$context['productInfo']['dataSection']['accordion']['2'] = array(
        "ID" => "downloads_section",
        "accordionType" => "download",
        "accordionTitle" => "Download",
        "accordionBody" => array(
            0 => array(
                "category_name" => "Product leaflet prova",
                "file_list" => array(
                    0 => array(
                        "name_file" => "Product leaflet - RD3 and RD4 - EN",
                        "type_file" => "PDF",
                        "size_file" => "5MB",
                        "url_file" => "/media/pdf/Scheda-tecnica-portone-XY.pdf"
                    ),
                ),
            ),
            1 => array(
                "category_name" => "User manual",
                "file_list" => array(
                    0 => array(
                        "name_file" => "User manual - RD3 and RD4 - EN",
                        "type_file" => "PDF",
                        "size_file" => "3MB",
                        "url_file" => "/media/pdf/Scheda-tecnica-portone-XY.pdf"
                    ),
                    1 => array(
                        "name_file" => "Manuale utente - RD3 and RD4 - IT",
                        "type_file" => "PDF",
                        "size_file" => "3MB",
                        "url_file" => "/media/pdf/Scheda-tecnica-portone-XY.pdf"
                    ),
                ),
            ),
        ),
);
 */
if(isset($product['referenceSection'])){
    $context['productInfo']['referenceSection'] = $product['referenceSection'];
    foreach($context['productInfo']['referenceSection']['productsSlider'] as $key => $value){
        //echo $key.' - '.$value['url'].'<br>';
        $path = str_replace(home_url(),'',$value['url']);
        $pid = url_to_postid($path);
        $img = get_the_post_thumbnail_url($pid);
        $context['productInfo']['referenceSection']['productsSlider'][$key]['image'] = $img;
        $context['productInfo']['referenceSection']['productsSlider'][$key]['textBtn'] = __( 'Read more','faactheme' );
    }
}
/* echo '<!--pre>';
print_r($context['productInfo']['referenceSection']);
echo '</pre-->'; */
if(isset($product['relatedProductsSection'])){
    //$context['productInfo']['relatedProductsSection'] = $product['relatedProductsSection'];
    $prefix_url = str_replace(__( '/products/','faactheme' ),__( '/p/','faactheme' ), home_url($wp->request));
    $context['productInfo']['relatedProductsSection']['ID'] = $product['relatedProductsSection']['ID'];
    $context['productInfo']['relatedProductsSection']['accordion'] = array();
    //$x = 0;
    foreach($product['relatedProductsSection']['accordion'] as $key => $item){
        if($item['accordionBody'] == 1){
            $context['productInfo']['relatedProductsSection']['accordion'][$key]['ID'] = $item['ID'];
            $context['productInfo']['relatedProductsSection']['accordion'][$key]['accordionType'] = $item['accordionType'];
            $context['productInfo']['relatedProductsSection']['accordion'][$key]['accordionTitle'] = $item['accordionTitle'];
            $context['productInfo']['relatedProductsSection']['accordion'][$key]['accordionBody'] = ($item['accordionBody'] == 1) ? true : null;
            foreach($product['relatedProductsSection']['accordion'][$key]['productsList'] as $itemp){
                
                $productRelated = json_decode(file_get_contents(API_PIM . 'product?' . 'lang=' . $currentLanguage . '&product_id=' . $itemp['product_id']), true);
                
                $urlRelated = '/'. __( 'products','faactheme' );

                foreach($productRelated['breadcrumbs'] as $key2 => $value2){
                    if(!empty($value2['text'])){
                       $urlRelated .= '/'.sanitize_title(stripAccents($value2['text']));
                    }
                }
                $urlRelated .= '/'.sanitize_title(stripAccents($itemp['title'])).'/p/'.$itemp['product_id'].'/';

                $context['productInfo']['relatedProductsSection']['accordion'][$key]['productsList'][] = array(
                    'title' => $itemp['title'],
                    'excerpt' => $itemp['excerpt'],
                    'product_id' => $itemp['product_id'],
                    'thumbnail' => $itemp['thumbnail'],
                    'deepen' => array(
                        'text' => __( 'See product','faactheme' ),
                        'link' => $urlRelated
                    )
                );

               /*  echo '<pre>';
                print_r($context['productInfo']['relatedProductsSection']['accordion'][$key]['productsList']);
                echo '</pre>'; */

            }
        }
    }
/*     echo '<!--pre>';
    print_r($context['productInfo']['relatedProductsSection']);
    echo '<pre-->'; */
}
/*
$context['productInfo']['relatedProductsSection'] = array(
        'ID' => 'related',
        'accordion' => array(
            0 => array(
                'ID' => 'accessories',
                'accordionType' => 'list',
                'accordionTitle' => 'Accessories',
                "productsList" => array(
                    0 => array(
                        "title" => "Access controlled revolving doors",
                        "excerpt" => "Fully automated access controlled revolving doors. All glass revolving doors for a masterpiece entrance Fully automated access controlled revolving doors. All glass revolving doors for a masterpiece entrance.",
                        "thumbnail" => get_template_directory_uri() . "/assets/images/stock/building.jpg",
                        "deepen" => array(
                            "text" => "See product",
                            "link" => "#"
                        )
                    ),
                    1 => array(
                        "title" => "All glass revolving doors",
                        "excerpt" => "All glass revolving doors for a masterpiece entrance.",
                        "thumbnail" => get_template_directory_uri() . "/assets/images/stock/FAAC-AirSlide-copertina.jpg",
                        "deepen" => array(
                            "text" => "See product",
                            "link" => "#"
                        )
                    ),
                    2 => array(
                        "title" => "Compact revolving doors",
                        "excerpt" => "Compact revolving doors that fit your entrance needs.",
                        "thumbnail" => get_template_directory_uri() . "/assets/images/stock/ambasciata.jpg",
                        "deepen" => array(
                            "text" => "See product",
                            "link" => "#"
                        )
                    ),
                    3 => array(
                        "title" => "High capacity revolving doors",
                        "excerpt" => "Reliable high-capacity revolving doors.",
                        "thumbnail" => get_template_directory_uri() . "/assets/images/stock/automotive-faac.jpg",
                        "deepen" => array(
                            "text" => "See product",
                            "link" => "#"
                        )
                    ),
                ),
                "btnLoadMore" => array(
                    "text" => "Load More",
                    "url"=> "#loadmore"
                ),
            ),
            1 => array(
                'ID' => 'similar',
                'accordionType' => 'list',
                'accordionTitle' => 'Similar',
                "productsList" => array(
                    0 => array(
                        "title" => "Access controlled revolving doors",
                        "excerpt" => "Fully automated access controlled revolving doors. All glass revolving doors for a masterpiece entrance Fully automated access controlled revolving doors. All glass revolving doors for a masterpiece entrance.",
                        "thumbnail" => get_template_directory_uri() . "/assets/images/stock/ambasciata.jpg",
                        "deepen" => array(
                            "text" => "See product",
                            "link" => "#"
                        )
                    ),
                    1 => array(
                        "title" => "All glass revolving doors",
                        "excerpt" => "All glass revolving doors for a masterpiece entrance.",
                        "thumbnail" => get_template_directory_uri() . "/assets/images/stock/automotive-faac.jpg",
                        "deepen" => array(
                            "text" => "See product",
                            "link" => "#"
                        )
                    ),
                    2 => array(
                        "title" => "Compact revolving doors",
                        "excerpt" => "Compact revolving doors that fit your entrance needs.",
                        "thumbnail" => get_template_directory_uri() . "/assets/images/stock/FAAC-AirSlide-copertina.jpg",
                        "deepen" => array(
                            "text" => "See product",
                            "link" => "#"
                        )
                    ),
                    3 => array(
                        "title" => "High capacity revolving doors",
                        "excerpt" => "Reliable high-capacity revolving doors.",
                        "thumbnail" => get_template_directory_uri() . "/assets/images/stock/building.jpg",
                        "deepen" => array(
                            "text" => "See product",
                            "link" => "#"
                        )
                    ),
                ),
                "btnLoadMore" => array(
                    "text" => "Load More",
                    "url"=> "#loadmore"
                ),
            ),
        ),
    );

*/

//showDebugInfo($context['header']['mainMenu'], "top menu data");

Timber::render('templates/productPage/index.twig', $context);

//===========================================================
// Add more elements here
//===========================================================


get_footer();