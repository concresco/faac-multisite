<?php
// Set ajax url
//------------------------------------------------------
add_action('wp_head','load_posts_ajaxurl');
function load_posts_ajaxurl() {
  ?>
  <script type="text/javascript">
    var ajaxPostsUrl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
    var readMoreTxt = "<?php echo __( 'Show more','faactheme' ); ?>";
    var readLessTxt = "<?php echo __( 'Show less','faactheme' ); ?>";
    var titleReadMoreTxt = "<?php echo __( 'Click to Show More','faactheme' ); ?>";
    var titleReadLessTxt = "<?php echo __( 'Click to Show Less','faactheme' ); ?>";
  </script>
  <?php
}

/**
 * Return nested (tree-like) structure from flat array
 */
function buildTree( array &$elements, $parentId = 0 )
{
  $branch = array();
  foreach ( $elements as &$element )
  {
    if ( $element->menu_item_parent == $parentId )
    {
      $children = buildTree( $elements, $element->ID );
      if ( $children )
        $element->submenu = $children;

      $branch[$element->ID] = $element;
      unset( $element );
    }
  }
  return $branch;
}
/**
 * Transform a navigational menu to it's tree structure
 *
 * @uses  buildTree()
 * @uses  wp_get_nav_menu_items()
 *
 * @param  String     $menud_id
 * @return array|null $tree
 */
function wpse_nav_menu_2_tree( $menu_id )
{
  $items = wp_get_nav_menu_items( $menu_id );
  return  $items ? buildTree( $items, 0 ) : null;
}

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function faac_body_classes( $classes ) {
  // Adds a class of hfeed to non-singular pages.
//  if ( ! is_singular() ) {
//    $classes[] = 'hfeed';
//  }
  $classes[] = 'faac'; // may be inserted in header.php
  return $classes;
}
add_filter( 'body_class', 'faac_body_classes' );

/**
 * Useful to force number of posts per page (pagination)
 */
// add_action( 'pre_get_posts', function ( $q )
// {
//   if( !is_admin()
//     && $q->is_main_query()
//     && $q->is_post_type_archive( 'product_cat' )
//   ) {
//     $q->set( 'posts_per_page', 9 );
//   }
// });

// From previous website
/**
 * getBreadcrumbs
 *
 * @param $postID
 *
 * @return Array - breadcrumb elements list
 */
function getBreadcrumbs($postID) {
  $breadcrumbs = array(
    array('text' => 'Home', 'url' => home_url())
  );

  $post_categories = wp_get_post_categories( $postID );
  // $categories = array();
  $counter = 0;
  foreach($post_categories as $c){
    $counter++;
    $cat = get_category( $c );
    $term_link = get_term_link( $cat );
    array_push($breadcrumbs, array( 'text' => $cat->name, 'url' => $term_link) );
  }
  $post_link = get_post_permalink($postID);
  $post_title = get_the_title($postID);
  array_push($breadcrumbs, array( 'text' => $post_title, 'url' => $post_link, 'isActive' => true ) );

  return $breadcrumbs;
}

/* function get ID_cat from json API for nav category products */

function in_array_product( $needle, $return, array $haystack ) {
  if ( ! is_array( $haystack ) ) return false;

  foreach ( $haystack as $key => $value ) {
    if ( sanitize_title(stripAccents($value)) == $needle ) {
      //return $value;
      return $haystack[$return];
    } else if ( is_array( $value ) ) {
      // multi search
      $key_result = in_array_multi( $needle, $return, $value );
      if ( $key_result !== false ) {
        return $key . '_' . $key_result;
                //return $key_result;
      }
    }
  }

  return false;
}


function in_array_multi( $needle, $return, array $haystack ) {
  if ( ! is_array( $haystack ) ) return false;

  foreach ( $haystack as $key => $value ) {
    if ( $value == $needle ) {
      //return $value;
      return $haystack[$return];
    } else if ( is_array( $value ) ) {
      // multi search
      $key_result = in_array_multi( $needle, $return, $value );
      if ( $key_result !== false ) {
        return $key . '_' . $key_result;
                //return $key_result;
      }
    }
  }

  return false;
}

function get_url_category($gfg_array, $search, $return) {

  $search_path = in_array_multi($search, $return, $gfg_array);

  $new_search = explode('_',$search_path);

  $url = '';

  foreach($new_search as $key => $value){
      if ($value == 'category') {
          $k1 = $new_search[$key+1];
          $url .= '&cat='.$gfg_array['archiveFilter'][0]['category'][$k1]['ID_cat'];
      }
      else if ($value == 'subCategory') {
          $k2 = $new_search[$key+1];
          $url .= '&subcat='.$gfg_array['archiveFilter'][0]['category'][$k1]['subCategory'][$k2]['ID_cat'];
      }
      else if ($value == 'subSubCategory') {
          $k3 = $new_search[$key+1];
          $url .= '&subsubcat='.$gfg_array['archiveFilter'][0]['category'][$k1]['subCategory'][$k2]['subSubCategory'][$k3]['ID_cat'];
      }
  }
  return $url;
}

function stripAccents($str) {
  return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}

/* end function for nav category products */


/**
 * @param mixed $data - Array of data to display on the debug page
 * @param string $title [optional] - a title for the data set
 *
 * @return void
 */
function showDebugInfo($data, $title = "Dati") {
  echo '<pre>';
  echo '<h3>'.$title.'</h3>';
  echo print_r($data);
  echo '</pre>';
}