<?php

const THEMENAME = 'faac';

//=====================================================================
//  REWRITE RULES FOR PRODUCT
//=====================================================================

add_filter('query_vars', function ($qvars) {
  $qvars[] = 'cat';
  $qvars[] = 'subcat';
  $qvars[] = 'subsubcat';
  return $qvars;
});

function custom_rewrite_rule()
{

  $titlePageProduct = __( 'Product','faactheme' );
  $titlePagecategory = __( 'Products Category','faactheme' );
  //$rulePageProduct = __( 'product','faactheme' );
  $rulePageProduct = __( 'p','faactheme' );
  $rulePagecategory = __( 'products','faactheme' );

  $pageProduct = get_page_by_title($titlePageProduct);
  $pageCategory = get_page_by_title($titlePagecategory);

  //redirect to product page
  //add_rewrite_rule('^'.$rulePageProduct.'/(.*)/(.*)/(.*)/(.*)/(.*)/?$', 'index.php?page_id=' . $pageProduct->ID, 'top');
  //add_rewrite_rule('^'.$rulePageProduct.'/(.*)/(.*)/(.*)/(.*)/?$', 'index.php?page_id=' . $pageProduct->ID, 'top');
  //add_rewrite_rule('^'.$rulePageProduct.'/(.*)/(.*)/(.*)/?$', 'index.php?page_id=' . $pageProduct->ID, 'top');
  //add_rewrite_rule('^'.$rulePageProduct.'/(.*)/(.*)/?$', 'index.php?page_id=' . $pageProduct->ID, 'top');
  //add_rewrite_rule('^'.$rulePageProduct.'/(.*)/?$', 'index.php?page_id=' . $pageProduct->ID, 'top');
  add_rewrite_tag('%product_id%','([^&]+)','product_id=');
  add_rewrite_rule('^(.*)/'.$rulePageProduct.'/([^/]*)/?', 'index.php?page_id=' . $pageProduct->ID . '&product_id=$matches[2]', 'top');
  add_rewrite_rule('^'.$rulePageProduct.'/([^/]*)/?', 'index.php?page_id=' . $pageProduct->ID . '&product_id=$matches[1]', 'top');

  /** remove temporary rewrite url category */
  //redirect to sub-sub-category page
    ////// add_rewrite_rule('^products/(.*)/(.*)/(.*)/?$', 'index.php?page_id=' . $pageCategory->ID . '&cat=$matches[1]&subcat=$matches[2]&subsubcat=$matches[3]', 'top');
  //add_rewrite_rule('^'.$rulePagecategory.'/(.*)/(.*)/(.*)/?$', 'index.php?page_id=' . $pageCategory->ID, 'top');

  //redirect to sub-category page
    ////// /add_rewrite_rule('^products/(.*)/(.*)/?$', 'index.php?page_id=' . $pageCategory->ID . '&cat=$matches[1]&subcat=$matches[2]', 'top');
  //add_rewrite_rule('^'.$rulePagecategory.'/(.*)/(.*)/?$', 'index.php?page_id=' . $pageCategory->ID, 'top');

  //redirect to category page
    ////// add_rewrite_rule('^products/(.*)/?$', 'index.php?page_id=' . $pageCategory->ID . '&cat=$matches[1]', 'top');
    //add_rewrite_rule('^'.$rulePagecategory.'/(.*)/?$', 'index.php?page_id=' . $pageCategory->ID, 'top');
  /** */
}
add_action('init', 'custom_rewrite_rule', 10, 0);

add_filter( 'query_vars', function( $query_vars ) {
  $query_vars[] = 'product_id';
  return $query_vars;
} );



//=====================================================================
//  Menu Support
//=====================================================================
if ( ! function_exists( 'menus_setup' ) ) {
  function menus_setup() {
    register_nav_menus( array(
      'menu-1' => esc_html__( 'Top', THEMENAME ),
      'menu-2' => esc_html__( 'Main', THEMENAME ),
      'menu-3' => esc_html__( 'Footer', THEMENAME )
    ) );
  }
}
add_action( 'after_setup_theme', 'menus_setup' );

//=====================================================================
//  Custom Post Type
//  create new post type 'Resellers'
//=====================================================================
function create_posttype()
{

  register_post_type( 'resellers',
    // CPT Options
    array(
      'labels' => array(
        'name' => __('Resellers','faactheme'),
        'singular_name' => __('Reseller','faactheme')
      ),
      'public' => true,
      'has_archive' => true,
      'menu_icon' => 'dashicons-store',
      'rewrite' => array('slug' => 'resellers'),
      'show_in_rest' => true,
      'supports' => array('title', 'editor', 'custom-fields')

    )
  );
}
// Hooking up our function to theme setup
add_action('init', 'create_posttype');


//=====================================================================
//  Scripts and Styles
//=====================================================================
function iaki_custom_scripts() {

  $data = '20230111';
  $ver = '1.2.7';
  // styles
  wp_enqueue_style( 'iaki-swiper', get_template_directory_uri() . '/assets/vendor/swiper/swiper-bundle.min.css', array(), '8.1.4' );

  // custom styles must be the last ones
  wp_enqueue_style( 'custom-style', get_template_directory_uri() . '/assets/dist/css/faac.css', array(), $ver );

  //scripts
  wp_enqueue_script( 'iaki-jquery', get_template_directory_uri() . '/assets/vendor/jquery/dist/jquery.min.js');
  wp_enqueue_script( 'iaki-bootstrap-popper', get_template_directory_uri().'/assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js', array(), $data, true );
  //wp_enqueue_script( 'iaki-bootstrap', get_template_directory_uri(). '/assets/vendor/bootstrap/dist/js/bootstrap.min.js', array(), $data, true );
  wp_enqueue_script( 'iaki-swiper', get_template_directory_uri().'/assets/vendor/swiper/swiper-bundle.js', array(), $data, true );

  // custom scripts must be the last ones
  //wp_enqueue_script( 'custom-script', get_template_directory_uri().'/assets/dist/js/script.min.js', array(), $data, true );
  wp_enqueue_script( 'custom-script', get_template_directory_uri().'/assets/js/script.js', array(), $data, true );

}
add_action( 'wp_enqueue_scripts', 'iaki_custom_scripts' );

// acf_add_options_page
if( function_exists('acf_add_options_page') ) {
  acf_add_options_page(array(
    'page_title' 	=> 'Theme Options',
    //'menu_title'	=> 'Theme Settings',
    'menu_slug' 	=> 'theme-general-settings',
    'redirect'		=> false
  ));
}

// Add excerpt for page
add_post_type_support( 'page', 'excerpt' );

//=====================================================================
//  Auxiliary Functions
//=====================================================================
require get_template_directory() . '/inc/helpers.php';
require get_template_directory() . '/inc/rewrite.php';
require get_template_directory() . '/inc/timber.php';

//=====================================================================
//  Advanced Custom Fields
//=====================================================================
require get_template_directory() . '/inc/acf/activities-slider.php';
require get_template_directory() . '/inc/acf/block-list-content.php';
require get_template_directory() . '/inc/acf/blog-excerpts.php';
require get_template_directory() . '/inc/acf/box-multi-information.php';
require get_template_directory() . '/inc/acf/contact-form.php';
require get_template_directory() . '/inc/acf/contact-page.php';
require get_template_directory() . '/inc/acf/generic-page-fields.php';
require get_template_directory() . '/inc/acf/homepage.php';
require get_template_directory() . '/inc/acf/logos.php';
require get_template_directory() . '/inc/acf/multi-page-builder.php';
//require get_template_directory() . '/inc/acf/multilanguage.php';
require get_template_directory() . '/inc/acf/reseller-postal-codes.php';
require get_template_directory() . '/inc/acf/social-link-option.php';
require get_template_directory() . '/inc/acf/timeline.php';

//=====================================================================
//  Send mail custom form
//=====================================================================

/* add_action( 'wp_mail_failed', 'onMailError', 10, 1 );
    function onMailError( $wp_error ) {
        echo "<pre>";
        print_r($wp_error);
        echo "</pre>";
    } */ 

add_action('wp_ajax_sendFormMessage', 'sendFormMessage');
add_action('wp_ajax_nopriv_sendFormMessage', 'sendFormMessage');

function sendFormMessage(){

  //parse_str($_POST['formdata'], $terms);
  $terms = $_POST;

/*   print_r($terms);
  exit(); */

  $subject = $terms['subjectEmail'];
  $sendTo = $terms['sendTo'];
  $email = sanitize_email( $terms['emailAddress'] );
  //$headers = 'From: '.$sendTo ."\r\n".'Reply-To: '.$sendTo;

  $headers[] = 'Content-Type: text/html; charset=UTF-8';
  //$headers[] = 'From: '.$sendTo ."\r\n".'Reply-To: '.$sendTo;
  $headers[] = 'From: FAAC <'.$sendTo .'>';
  $headers[] = 'Reply-To: '.$email.' <'.$email.'>';

  $message = '';

  foreach($terms as $key => $value) {
    if($key != 'subjectEmail' && $key != 'sendTo' && $key != 'action'){
      $message .= "Field '$key': '$value'<br><br>";
    }
  }

  //if (isset($_FILES['attachment']) && !empty($_FILES['attachment'])) {
  if (isset($_FILES) && !empty($_FILES)) {  
    $attachment = array();
    if((get_bloginfo('language') == 'de-DE')) {
      $lang = 'de-AT';
    } else if(get_bloginfo('language') == 'nl-NL' && strpos($_SERVER['HTTP_HOST'],'faacbv.com')){
      $lang = 'nl-BV';
    } else {
      $lang = get_bloginfo('language');
    }
    for($i = 0; $i < count($_FILES); $i++) {
      if(isset($_FILES['file_'.$i]['name'])){
        $allowed =  array('gif', 'png' ,'jpg', 'jpeg', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp', 'pdf', 'zip', 'rar', '7z');
        $ext = pathinfo($_FILES['file_'.$i]['name'], PATHINFO_EXTENSION); 
        if(in_array($ext,$allowed) ) {
          if(move_uploaded_file( $_FILES['file_'.$i]['tmp_name'], WP_CONTENT_DIR.'/uploads/'.$lang.'_'.time().'_'.basename( $_FILES['file_'.$i]['name'] ) )) {
            $anexo = WP_CONTENT_DIR.'/uploads/'.$lang.'_'.time().'_'.basename( $_FILES['file_'.$i]['name'] );
            $terms['attachment'.$i] = $anexo;
            //$attachment["'".basename( $_FILES['file_'.$i]['name'])."'"] = "'".$anexo."'";
            array_push($attachment,$anexo);
            //$return = wp_mail( $e['to_mail'], $e['subject'], $e['message'], $headers, $anexo );
            //unlink($anexo);
          } else {
            $return = sprintf(__( 'File %s size too large error, 8MB limit','faactheme' ),$_FILES['file_'.$i]['name']);
            break;
          }
        } else {
            $return = sprintf(__( 'Unsupported %s file type error:  only .gif, .png, .jpg, .jpeg, .doc, .docx, .xls, .xlsx, .ppt, .pptx, .odt, .ods, .odp, .pdf, .zip, .rar, .7zip are supported!','faactheme' ),$_FILES['file_'.$i]['name']);
            break;
        }
      }  
    }

    if(wp_mail($sendTo, $subject, $message, $headers, $attachment)){
      $return = 'mail_sent';
    } else {
      $return = __( 'Error sending message','faactheme' );
    }

    /*
    foreach($attachment as $file){
      unlink($file);
    }
    */

  } else {

    if(wp_mail($sendTo, $subject, $message, $headers)){
      $return = 'mail_sent';
    } else {
      $return = __( 'Error sending message','faactheme' );
    }  
    //$return = wp_mail( $e['to_mail'], $e['subject'], $e['message'], $headers );
  }
  $response = '';
  if($return == 'mail_sent') {
    require_once FLAMINGO_PLUGIN_DIR . '/includes/class-inbound-message.php';

    global $wpdb, $post;

    $post_content = $terms['firstName']."\n".$email."\n".$subject."\n".$message."\n";

    //inserimento oggetto post e postmeta per Flamingo

    $add_msg_post = array(
        'post_title'    => ($subject) ? wp_strip_all_tags($subject) : 'Form contatti',
        'post_content'  => $post_content,
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_type' => 'flamingo_inbound'
    );

    $post_id = wp_insert_post( $add_msg_post );

    //add_post_meta( $post_id, '_spam_meta_time', time() );
    add_post_meta( $post_id, '_submission_status', $return);
    add_post_meta( $post_id, '_subject', wp_strip_all_tags($subject) );
    add_post_meta( $post_id, '_from', $terms['firstName']." <".$email.">" );
    add_post_meta( $post_id, '_from_name', $terms['firstName'] );
    add_post_meta( $post_id, '_from_email', $email );

    /* ciclo for sui campi personalizzati */
    $nFields = count($terms)-2;
    $fields = 'a:'.$nFields.':{';

    //print_r($terms);

    foreach($terms as $key => $value) {
      if($key != 'subjectEmail' && $key != 'sendTo' && $key != 'action'){
        add_post_meta( $post_id, '_field_'.$key, wp_strip_all_tags($value) );
        $fields .= 's:'.strlen($key).':"'.$key.'";N;';
      }
    }
    $fields .= '}';
    // echo 'SONO QUI';
    // exit();

    add_post_meta( $post_id, '_fields', wp_json_encode($fields));
    // add_post_meta( $post_id, '_field_your-name', $terms['name'] );
    // add_post_meta( $post_id, '_field_your-email', $email );
    // add_post_meta( $post_id, '_field_your-subject', wp_strip_all_tags($subject));
    // add_post_meta( $post_id, '_field_your-message', $message );
    // add_post_meta( $post_id, '_fields', strval('a:4:{s:9:"your-name";N;s:10:"your-email";N;s:12:"your-subject";N;s:12:"your-message";N;}'));
    /* fine ciclo for sui campi personalizzati */

    /* aggiungere il campo '_meta' */

    $meta = 'a:5:{s:9:"remote_ip";s:'.strlen(getUserIpAddr()).':"'.getUserIpAddr().'";';
    $meta .= 's:10:"user_agent";s:'.strlen($_SERVER['HTTP_USER_AGENT']).':"'.$_SERVER['HTTP_USER_AGENT'].'";';
    $meta .= 's:4:"date";s:'.strlen(date("Y-m-d")).':"'.date("Y-m-d").'";';
    $meta .= 's:4:"time";s:'.strlen(date("H:i")).':"'.date("H:i").'";';
    $meta .= 's:8:"site_url";s:'.strlen(get_site_url()).':"'.get_site_url().'";}';

    add_post_meta( $post_id, '_meta', wp_json_encode($meta));

    /* aggiungere il 'channel' -> la tassonomia */

    add_post_meta( $post_id, '_akismet', NULL );
    add_post_meta( $post_id, '_recaptcha', 'a:0:{}' );
    add_post_meta( $post_id, '_spam_log', 'a:0:{}' );
    add_post_meta( $post_id, '_consent', 'a:0:{}' );
    //add_post_meta( $post_id, '_hash', $this->hash );

    //$wpdb->query("UPDATE ".$wpdb->prefix."postmeta SET meta_value = SUBSTRING(meta_value,7,88) WHERE post_id = ".intval($post_id)." AND meta_key='_fields'");

    //Sends consent to IUBENDA

    // ApiKey Iubenda for UK
    $AkeyIub = "PKkPFEI0kvY3unU25mliSLrfWWxJCZLT";

    if(strpos($_SERVER['SERVER_NAME'], "faacentrancesolutions.fr") !== false) {
      // ApiKey Iubenda for FR
      $AkeyIub = "GkNoiKyD8Hsii2Ef2BFF06L3xaubfivh";
    }
    if(strpos($_SERVER['SERVER_NAME'], "faac.co.at") !== false) {
      // ApiKey Iubenda for AU
      $AkeyIub = "c7qcDXECFwBuf20D9BI3ryLgRufRtAIy";
    }
    if(strpos($_SERVER['SERVER_NAME'], "faac.hu") !== false) {
      // ApiKey Iubenda for AU
      $AkeyIub = "gizhBJEDkU2qoaYcPacbUg7TgfAyuLQ4";
    }
    if(strpos($_SERVER['SERVER_NAME'], "faac-automatischedeuren.nl") !== false) {
      // ApiKey Iubenda for AU
      $AkeyIub = "YTJO9c3y38cjWEEMbUyisM9fB21FHMTP";
    }
    if(strpos($_SERVER['SERVER_NAME'], "faacdoorsandshutters.co.uk") !== false) {
      // ApiKey Iubenda for AU
      $AkeyIub = "xvA8rusIJIRxkeADbOBNvn1VVdKsuGUR";
    }
    if(strpos($_SERVER['SERVER_NAME'], "faacbv.com") !== false) {
      // ApiKey Iubenda for AU
      $AkeyIub = "evyp3zM6tG0A1i2tq64lbJNlDJXbGJLN";
    }

    if (!isset($terms["surname"])) {
      $terms["surname"] = '';
    }

    $consent_data = array(
      "timestamp" => date('Y-m-d H:i:s'),
      "subject" => array(
        "full_name"=>$terms["firstName"].' '.$terms["surname"],
        "email" => $email,
      ),
      "legal_notices" => array(
          array(
            "identifier" => "cookie_policy"
          ),
          array(
            "identifier" => "privacy_policy"
          )
        ),
        "preferences" => array(
          "privacy_policy" => true
        ),
        "ip_address" => getUserIpAddr()
    );
    $req = curl_init();
    curl_setopt($req, CURLOPT_URL, 'https://consent.iubenda.com/consent');
    curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($req, CURLOPT_HTTPHEADER, array(
      'ApiKey: '.$AkeyIub,
      'Content-Type: application/json'
    ));
    curl_setopt($req, CURLOPT_POST, true);
    curl_setopt($req, CURLOPT_POSTFIELDS, json_encode($consent_data));
    $response = curl_exec($req);
  }  
  //echo $return;
  //echo $return.' - '.$response.' - '.$AkeyIub;
  echo $return.' - '.$response;

  wp_die();
}

//add_action('wp_mail_failed', 'log_mailer_errors', 10, 1);
function log_mailer_errors( $wp_error ){
  $fn = ABSPATH . '/mail.log'; // say you've got a mail.log file in your server root
  $fp = fopen($fn, 'a');
  fputs($fp, "Mailer Error: " . $wp_error->get_error_message() ."\n");
  fclose($fp);
}

function getUserIpAddr(){
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/** CUSTOM PAGINATION */
/**
 * @param int $currPage - current page number in pagination count
 * @param int $pagesCount - max number of results pages
 * @param $s - optional: search string; if missing, blog category pagination style is assumed
 * @param null $baseUrl - optional: given base url
 * @return array
 */
function getCustomPagination($currPage = 1, $pagesCount = 1, $s = null, $baseUrl = null, $suffix = '')
{
  global $wp;
  $key = isset($s) ? ('/?s=' . $s) : false;

  $prm = (strpos($_SERVER['REQUEST_URI'], '?') !== false) ? '/?' : '&';

  if (isset($_GET['tab'])) {
    $key .= "&tab=" . $_GET['tab'];
  }
  if (isset($_GET['ind'])) {
    for ($i = 0; $i < count($_GET['ind']); $i++) {
      $key .= $prm."ind%5B0%5D=" . $_GET['ind'][$i];
    }
    $prm = '&';
  }
  if (isset($_GET['pt'])) {
    for ($i = 0; $i < count($_GET['pt']); $i++) {
      $key .= $prm."pt%5B0%5D=" . $_GET['pt'][$i];
    }
  }

  if (!isset($baseUrl)) {
    $baseUrl = home_url($wp->request);
  }

  $paginationObj = array(
    "firstPage"       => __("First", "faactheme"),
    "isFirstPage"     => ($currPage == 1) ? true : false,
    "firstPageUrl"    => $key ? $baseUrl . $key . '&paged=1' : $baseUrl . $suffix . '/',
    "prevPage"        => $currPage == 1 ? null : ($currPage - 1),
    "prevPageUrl"     => $key ? $baseUrl . $key . '&paged=' . ($currPage - 1) : $baseUrl . '/page/' . ($currPage - 1) . $suffix . '/',
    "currentPage"     => $currPage,
    "currentPageUrl"  => $key ? $baseUrl . $key . '&paged=' . ($currPage) : $baseUrl . '/page/' . ($currPage) . $suffix . '/',
    "nextPage"        => $currPage == $pagesCount ? null : ($currPage + 1),
    "nextPageUrl"     => $key ? $baseUrl . $key . '&paged=' . ($currPage + 1) : $baseUrl . '/page/' . ($currPage + 1) . $suffix . '/',
    "isLastPage"      => $currPage == $pagesCount ? true : false,
    //"lastPage"        => __("Last", "faac") . " (" . $pagesCount . ")",
    "lastPage"        => __("Last", "faactheme"),
    "lastPageUrl"     => $key ? $baseUrl . $key . '&paged=' . ($pagesCount) : $baseUrl . '/page/' . $pagesCount . $suffix . '/'
  );
  return $paginationObj;
}

/** create custom sitemap for category product and products */
add_filter("wpseo_sitemap_index", "custom_sitemap_index");
add_action("init", "custom_sitemap_register");

function get_sitemap_type() {

	$sitemap_type = array(
    __( 'products','faactheme' ),
		//'category-product',
		//'products'
			);
	return $sitemap_type;

}

function custom_sitemap_index($sitemap_custom_items) {
	//global $wpseo_sitemaps;
	$sitemap_type = get_sitemap_type();
	foreach($sitemap_type as $type){
		$sitemap_url = home_url($type."-sitemap.xml");
		$sitemap_date = date(DATE_W3C);  # Current date and time in sitemap format.
		$custom_sitemap = "
		<sitemap>
		<loc>%s</loc>
		<lastmod>%s</lastmod>
		</sitemap>";
		$sitemap_custom_items .= sprintf($custom_sitemap, $sitemap_url, $sitemap_date);
	}
	return $sitemap_custom_items;
}

function custom_sitemap_register() {
    global $wpseo_sitemaps;
	  $sitemap_type = get_sitemap_type();
    if (isset($wpseo_sitemaps) && !empty($wpseo_sitemaps)) {
        foreach($sitemap_type as $type){
	        $wpseo_sitemaps->register_sitemap($type, function () use ($type) { custom_sitemap_generate($type); });
    	}
    }
}

/**
 * Generate CUSTOM sitemap XML body
 */
function custom_sitemap_generate($type) {
    global $wpseo_sitemaps;
    $data = get_data_sitemap($type);
    $urls = array();
    foreach ($data as $item) {
        if(!empty($item['permalink'])) {
	        $urls[]= $wpseo_sitemaps->renderer->sitemap_url(array(
	            "mod" => $item['datemod'],  # <lastmod></lastmod>
	            "loc" => $item['permalink'],  # <loc></loc>
	            "images" => array(
	                array(  # <image:image></image:image>
    	                "src" => $item['image_url'],  # <image:loc></image:loc>
        	            "title" => $item['image_title'],  # <image:title></image:title>
            	        "alt" => $item['image_title'],  # <image:caption></image:caption>
                	),
            	),
	        ));
    	}
    }
    $sitemap_body = "
<urlset
    xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
    xmlns:image=\"http://www.google.com/schemas/sitemap-image/1.1\"
    xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd\"
    xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">
%s
</urlset>";

    $sitemap = sprintf($sitemap_body, implode("\n", $urls));
    $wpseo_sitemaps->set_sitemap($sitemap);
}

function get_data_sitemap($type){

  if($type == __( 'products','faactheme' )){
    $item = array();
    //$currentLanguage = get_bloginfo('language');
    // language information
    if((get_bloginfo('language') == 'de-DE')) {
      $currentLanguage = 'de-AT';
    } else if(get_bloginfo('language') == 'nl-NL' && strpos($_SERVER['HTTP_HOST'],'faacbv.com')){
      $currentLanguage = 'nl-BV';
    } else {
      $currentLanguage = get_bloginfo('language');
    }
    $sidebar_list = json_decode(file_get_contents(API_PIM . 'sidebar?lang=' . $currentLanguage), true);

    $categorys = array($sidebar_list['archiveFilter'][0]['category']);

    $id_products = array();

    foreach($categorys as $category){
      foreach($category as $result){
        $add_url = '&cat=' . $result['ID_cat'];
        $url = home_url().'/'.__( 'products','faactheme' ).$result['url'];
        $title = $result['mainTitle'];

        $archiveList = json_decode(file_get_contents(API_PIM . 'family?lang=' . $currentLanguage . $add_url), true);
        //echo API_PIM . 'family?lang=' . $currentLanguage . $add_url.'<br>';
        $img = $archiveList['hero']['post']['thumbnail']['src'];

        if(is_array($archiveList)){
          $item[] = array(
            'image_url' => $img,
            'image_title' => $title,
            'permalink' => $url.'/',
            'datemod' => date('Y-m-d\TH:i:s+00:00')
          );

          /* echo '<pre>';
          print_r($archiveList['archive']['archiveElement']['list']);
          echo '</pre>'; */

          foreach($archiveList['archive']['archiveElement']['list'] as $list){
            if($list['ID_product'] !== null){
              $id_products[] = array(
               'id' => $list['ID_product'],
               'addurl' => $url.$url1.'/'
               );
            }
          }

          if($result['subCategory']){
            foreach($result['subCategory'] as $subresult){
              $add_url1 = $add_url.'&subcat=' . $subresult['ID_cat'];
              $url1 = $subresult['url'];
              $title = $subresult['mainTitle'];

              $archiveListSub = json_decode(file_get_contents(API_PIM . 'family?lang=' . $currentLanguage . $add_url1), true);
              //echo API_PIM . 'family?lang=' . $currentLanguage . $add_url1.'<br>';
              $img = $archiveListSub['hero']['post']['thumbnail']['src'];

             /*  echo '<pre>';
              print_r($archiveList['archive']['archiveElement']['list']);
              echo '</pre>'; */

              foreach($archiveListSub['archive']['archiveElement']['list'] as $list){
                if($list['ID_product'] !== null){
                   $id_products[] = array(
                    'id' => $list['ID_product'],
                    'addurl' => $url.$url1.'/'
                    );
                }
              }

              $item[] = array(
                'image_url' => $img,
                'image_title' => $title,
                'permalink' => $url.$url1.'/',
                'datemod' => date('Y-m-d\TH:i:s+00:00')
              );

              if($subresult['subSubCategory']){
                foreach($subresult['subSubCategory'] as $subsubresult){
                  $add_url2 = $add_url1.'&subsubcat=' . $subsubresult['ID_cat'];
                  $url2 = $subsubresult['url'];
                  $title = $subsubresult['mainTitle'];

                  $archiveListSubSub = json_decode(file_get_contents(API_PIM . 'family?lang=' . $currentLanguage . $add_url2), true);
                  //echo API_PIM . 'family?lang=' . $currentLanguage . $add_url2.'<br>';
                  $img = $archiveListSubSub['hero']['post']['thumbnail']['src'];

                  /* echo '<pre>';
                  print_r($archiveList['archive']['archiveElement']['list']);
                  echo '</pre>'; */

                  foreach($archiveListSubSub['archive']['archiveElement']['list'] as $list){
                    if($list['ID_product'] !== null){
                      $id_products[] = array(
                       'id' => $list['ID_product'],
                       'addurl' => $url.$url1.$url2.'/'
                       );
                    }
                  }

                  $item[] = array(
                    'image_url' => $img,
                    'image_title' => $title,
                    'permalink' => $url.$url1.$url2.'/',
                    'datemod' => date('Y-m-d\TH:i:s+00:00')
                  );
                }
              }
            }  
          }
        }
      }
/*       $archiveList = json_decode(file_get_contents(API_PIM . 'family?lang=' . $currentLanguage . $add_url), true);
      $img = $archiveList['hero']['post']['thumbnail']['src'];

      $item[] = array(
        'image_url' => $img,
        'image_title' => $title,
        'permalink' => $url,
        'datemod' => date('Y-m-d\TH:i:s+00:00')
      ); */
    }
/*     echo '<pre>';
    print_r($id_products);
    echo '</pre>'; */
    foreach($id_products as $id_product){
      $product = json_decode(file_get_contents(API_PIM . 'product?' . 'lang=' . $currentLanguage . '&product_id=' . $id_product['id']), true);
/*       $img = array();
      foreach($product['gallery'] as $gallery){
        $img[] = $gallery['image_url'];
      } */
      $item[] = array(
        'image_url' => $product['gallery'][0]['image_url'],
        'image_title' => $product['title'],
        'permalink' => $id_product['addurl'].sanitize_title($product['title']).'/p/'.$id_product['id'].'/',
        'datemod' => date('Y-m-d\TH:i:s+00:00')
      );
    }
  }
  return $item;

}

/** add dynamic value to ACF fields */
add_filter('acf/load_field/name=category_products', 'populateCatProdInAcfSelect');
    function populateCatProdInAcfSelect( $field ){
 
        // reset choices
        $field['choices'] = array();

        //$currentLanguage = get_bloginfo('language');
        // language information
        if((get_bloginfo('language') == 'de-DE')) {
          $currentLanguage = 'de-AT';
        } else if(get_bloginfo('language') == 'nl-NL' && strpos($_SERVER['HTTP_HOST'],'faacbv.com')){
          $currentLanguage = 'nl-BV';
        } else {
          $currentLanguage = get_bloginfo('language');
        }
        $sidebar_list = json_decode(file_get_contents(API_PIM . 'sidebar?lang=' . $currentLanguage), true);
         
        /* foreach ($sidebar_list['archiveFilter']['category'] as $key => $value) :
            $field['choices'][ $key ] = $value;
        endforeach; */
        $field['choices']['cat-0'] = 'Selezione la categoria';
        foreach ($sidebar_list['archiveFilter'] as $archiveFilter) :
          if($archiveFilter['type'] == 'list'):
              foreach ($archiveFilter['category'] as $category) :
                $field['choices'][ 'cat-'.$category['ID_cat'] ] = $category['mainTitle'];  
                //echo 'category - ' .$category['mainTitle'].' - '. $category['ID_cat'] . '<br>';
                  if($category['subCategory']):
                    foreach ($category['subCategory'] as $subCategory) :
                        $field['choices'][ 'cat-'.$category['ID_cat'].'_subcat-'.$subCategory['ID_cat'] ] = '- '.$subCategory['mainTitle'];
                        //echo 'subCategory - ' .$subCategory['mainTitle'].' - '. $subCategory['ID_cat'] . '<br>';
                        if($category['subSubCategory']):
                          foreach ($subCategory['subSubCategory'] as $subSubCategory) :
                            $field['choices'][ 'cat-'.$category['ID_cat'].'_subcat-'.$subCategory['ID_cat'].'_subsubcat-'.$subSubCategory['ID_cat'] ] = '- - '.$subSubCategory['mainTitle'];  
                            //echo 'subSubCategory - ' .$subSubCategory['mainTitle'].' - '. $subSubCategory['ID_cat'] . '<br>';
                          endforeach;
                        endif;
                    endforeach;
                  endif;
              endforeach;
          endif;
      endforeach;
 
        return $field;
    }
