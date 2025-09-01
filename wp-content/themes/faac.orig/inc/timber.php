<?php
// set templates directory
Timber::$dirname = array(
  'views',
  'views/pages'
);

/**
 * Adds variables to main context.
 */
add_filter('timber_context', 'add_to_context');
function add_to_context($data){

  $data['isLoggedIn'] = is_user_logged_in();

  /* Base url for use with assets included inside the theme*/
  $data['baseUrl'] = get_template_directory_uri() . '/';

  $data['cookies'] = $_COOKIE;

  $data['searchAction'] = home_url('/');

  global $wp;

  // get current url with query string. (unused)
  $currentUrl = home_url($wp->request);
  $data['currentUrl'] = $currentUrl;
  $data['homeUrl'] = home_url();
  $data['isAdmin'] = is_admin_bar_showing();
  //------------------------------------------
  // Global Assets
  //------------------------------------------

  $data['header'] =  get_field('themeAssets', 'option');
  $data['footer']['themeAssets'] =  $data['header'];

  $data['legals'] = '© 1965 - ' . date('Y') . ' FAAC SPA Soc. Unipersonale P.IVA 03820731200 – All rights reserved';


  //------------------------------------------
  // 'Top' Menu
  //------------------------------------------

  $menuObj = wpse_nav_menu_2_tree('Top');
  $data['header']['topMenu'] = [];
  if (is_array($menuObj)) {
    foreach ($menuObj as $m) {
      if (empty($m->menu_item_parent)) {
          $data['header']['topMenu'][$m->ID]['text'] = $m->title;
          $data['header']['topMenu'][$m->ID]['url'] = $m->url;
          $data['header']['topMenu'][$m->ID]['target'] = $m->target;
      }
    }
  }

  //------------------------------------------
  // 'Main' Menu
  //------------------------------------------
  $menuObj = wpse_nav_menu_2_tree('Main');
  $data['header']['mainMenu'] = [];
  $data['header']['homeUrl'] = $data['homeUrl'];

  // debug
  $data['header']['rawMenu']['items'] = $menuObj;

  $data['header']['rawMenu']['queriedObjectId'] = get_queried_object_id();

  $ind = 0;
  if (is_array($menuObj)) {
    $qObj = get_queried_object_id();
    foreach ($menuObj as $m) {
      $current = ($m->object_id == $qObj || ($_SERVER['REQUEST_URI'] == parse_url($m->url, PHP_URL_PATH)));
      if (empty($m->menu_item_parent)) {
        $data['header']['mainMenu'][$ind]['text'] = $m->title;
        $data['header']['mainMenu'][$ind]['url'] = $m->url;
        if ($current) {
          $data['header']['mainMenu'][$ind]['active'] = true;
        }
      }
      if (isset($m->submenu)) {
        $n = 0;
        foreach ($m->submenu as $submenuItem) {
          $current = ($submenuItem->object_id == $qObj);
          $data['header']['mainMenu'][$ind]['submenu'][] = [
            "text" => $submenuItem->title,
            "url" => $submenuItem->url,
            "active" => $current,
            
          ];
          /** sub menu of second level */
          if (isset($submenuItem->submenu)) {

            foreach($submenuItem->submenu as $submenuSecondItem){
              //$data['header']['mainMenu'][$ind]['submenu'][$n]['submenu'][] = $submenuItem->submenu;
              $data['header']['mainMenu'][$ind]['submenu'][$n]['submenu'][] = [
                  $current = ($submenuSecondItem->object_id == $qObj),
                  "text" => $submenuSecondItem->title,
                  "url" => $submenuSecondItem->url,
                  "active" => $current,
              ];
            }
          }
          /** */

          $n++;
        }
      }
      $ind++;
    }
  }
  // echo '<pre>';
  // print_r($data['header']['mainMenu']);
  // echo '</pre>';
  
  // language menu
    $data['language'] = get_field('language', 'option');
    if(ENV !== NULL && ENV == 'local'){
      $language_url = array(
      'staging.faacentrancesolutions.co.uk',
      'staging.faacentrancesolutions.fr',
      'staging.faac.nl',
      'staging.faac.si',
      'staging.faac.co.at',
      'staging.faac.hu'
      );
    } else {
      $sites = get_sites();
      $language_url = array();
      foreach($sites as $site) {
        array_push($language_url, $site->domain);
        // $sites_info = get_blog_details($site->blog_id);
        // echo '<!--pre>';
        // print_r($sites_info);
        // echo '</pre-->';
      }
    };

    $data['languageActive'] = $data['language'][0]['value'];

    for ($i = 0; $i< sizeof($data['language']);
    $i++){
      $data['language'][$i]['url'] = 'https://'.$language_url[$i];
      if($language_url[$i] == parse_url( get_site_url(), PHP_URL_HOST )){
        $data['languageActive'] = $data['language'][$i]['value'];
      }
    }

  //showDebugInfo($data['language']);

  // END MAIN MENU


  //------------------------------------------
  // 'Footer' Menu
  //------------------------------------------

  // social link into footer
  $data['slogan'] = get_field('slogan', 'option');
  $data['socialLinks'] = get_field('socialLinks', 'option');

  //$data['footer'] = json_decode(file_get_contents(get_template_directory() . '/views/organisms/footer/index.json'), true);
  $menuObj = wpse_nav_menu_2_tree('footer');
  $data['footer']['footerMenu'] = [];

  // debug
  $data['footer']['rawMenu']['items'] = $menuObj;

  $data['footer']['rawMenu']['queriedObjectId'] = get_queried_object_id();

  $ind = 0;
  if (is_array($menuObj)) {
    $qObj = get_queried_object_id();
    foreach ($menuObj as $m) {
      $current = ($m->object_id == $qObj || ($_SERVER['REQUEST_URI'] == parse_url($m->url, PHP_URL_PATH)));
      if (empty($m->menu_item_parent)) {
        $data['footer']['footerMenu'][$ind]['text'] = $m->title;
        $data['footer']['footerMenu'][$ind]['url'] = $m->url;
        if ($current) {
          $data['footer']['footerMenu'][$ind]['active'] = true;
        }
      }
      if (isset($m->submenu)) {
        foreach ($m->submenu as $submenuItem) {
          $current = ($submenuItem->object_id == $qObj);
          $data['footer']['footerMenu'][$ind]['submenu'][] = [
            "text" => $submenuItem->title,
            "url" => $submenuItem->url,
            "active" => $current
          ];
        }
      }
      $ind++;
    }
  }
  // $data['footer']['search_form'] = [
  //   "action" => home_url(),
  //   "input_text" => "Cerca nel sito",
  //   "text_btn" => "Cerca"
  // ];

  //END FOOTER
  return $data;
}
