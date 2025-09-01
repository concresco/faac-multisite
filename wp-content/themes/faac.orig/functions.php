<?php

const THEMENAME = 'faac';

//=====================================================================
//  REWRITE RULES FOR PRODUCT
//=====================================================================
// function custom_rewrite_rule()
// {

//   $titlePageProduct = 'Product page';
//   $titlePagecategory = 'Products category page';

//   $pageProduct = get_page_by_title($titlePageProduct);
//   $pageCategory = get_page_by_title($titlePagecategory);

//   //redirect to product page
//   add_rewrite_rule('^products/([^/]*)/([^/]*)/([^/]*)/([^/]*)/?', 'index.php?page_id=' . $pageProduct->ID, 'top');
//   //redirect to sub-sub-category page
//   add_rewrite_rule('^products/([^/]*)/([^/]*)/([^/]*)/?', 'index.php?page_id=' . $pageCategory->ID . '&category=$matches[1]&sub-category=$matches[2]&sub-sub-category=$matches[3]', 'top');
//   //redirect to sub-category page
//   add_rewrite_rule('^products/([^/]*)/([^/]*)/?', 'index.php?page_id=' . $pageCategory->ID . '&category=$matches[1]&sub-category=$matches[2]', 'top');
//   //redirect to category page
//   add_rewrite_rule('^products/([^/]*)/?', 'index.php?page_id=' . $pageCategory->ID, 'top');
// }
// add_action('init', 'custom_rewrite_rule', 10, 0);



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
//  Scripts and Styles
//=====================================================================
function iaki_custom_scripts() {

  $data = '20221014';
  $ver = '1.2.1';
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
  wp_enqueue_script( 'custom-script', get_template_directory_uri().'/assets/dist/js/script.min.js', array(), $data, true );

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
require get_template_directory() . '/inc/acf/blog-excerpts.php';
require get_template_directory() . '/inc/acf/box-multi-information.php';
require get_template_directory() . '/inc/acf/contact-form.php';
require get_template_directory() . '/inc/acf/contact-page.php';
require get_template_directory() . '/inc/acf/generic-page-fields.php';
require get_template_directory() . '/inc/acf/homepage.php';
require get_template_directory() . '/inc/acf/logos.php';
require get_template_directory() . '/inc/acf/multi-page-builder.php';
require get_template_directory() . '/inc/acf/multilanguage.php';
require get_template_directory() . '/inc/acf/social-link-option.php';
require get_template_directory() . '/inc/acf/timeline.php';

//=====================================================================
//  Send mail custom form
//=====================================================================

add_action('wp_ajax_sendFormMessage', 'sendFormMessage');
add_action('wp_ajax_nopriv_sendFormMessage', 'sendFormMessage');

function sendFormMessage(){

  parse_str($_POST['formdata'], $terms);

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
    if($key != 'subjectEmail' && $key != 'sendTo'){
      $message .= "Field '$key': '$value'<br><br>";
    }
  }

  if(wp_mail($sendTo, $subject, $message, $headers)){
    $return = 'mail_sent';
  } else {
    $return = 'error';
  }

  require_once FLAMINGO_PLUGIN_DIR . '/includes/class-inbound-message.php';

  global $wpdb, $post;

  $post_content = $_POST['firstName']."\n".$email."\n".$subject."\n".$message."\n";

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

  foreach($terms as $key => $value) {
    if($key != 'subjectEmail' && $key != 'sendTo'){
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
    //$AkeyIub = "pbNEkB7DWrbyACi8hjFT5VESCIKmylut";
    $AkeyIub = "GkNoiKyD8Hsii2Ef2BFF06L3xaubfivh";
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

  //echo $return;
  echo $return.' - '.$response.' - '.$AkeyIub;

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
