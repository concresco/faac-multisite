<?php
/*
Template name: Category Posts Page
*/

$context = Timber::get_context();
global $post;

$pageFields = get_fields();

$tags = get_terms( array(
    'taxonomy'   => 'post_tag',
    'hide_empty' => false,
) );

/* echo '<!--pre>';
print_r($tags);
echo '</pre-->'; */

function change_filter_canonical( $canonical ) {

    $canonical = explode('?', home_url( $_SERVER['REQUEST_URI'] ));
  
    return $canonical[0];
}
  
add_filter( 'wpseo_canonical', 'change_filter_canonical' );

get_header();

$context['post'] = new Timber\Post();
//$context['breadcrumbs'] = $pageFields['breadcrumbs'];
//$context['additionalContent'] = $pageFields['additionalContent'];

//$baseUrl = get_home_url() . '/news';
//$baseUrl = get_home_url() . __( '/news','faactheme' );
$baseUrl = rtrim(get_permalink(),'/');
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$postPerPage = 8;
//$postPerPage = 2;

$original_query = $wp_query;
$wp_query = null;

($pageFields['category_post']) ? $category = $pageFields['category_post'] : $category = '';

$formCriteria = array('ind','pt');

$tax_q = array('relation' => 'AND');
//	$filterName = false;
for ($i = 0; $i < count($formCriteria); $i++) {

    if (isset($_GET[$formCriteria[$i]])) {
        $tax_q[] = array(
            'taxonomy' => 'post_tag',
            'field' => 'slug',
            'terms' => $_GET[$formCriteria[$i]], //get_query_var($c),
            'compare' => 'IN'
        );
    }
}

$args =
    array(
        'post_type' => 'post',
        //'category__in' => array($category),
        'posts_per_page' => $postPerPage,
        'orderby'   => 'date',
        'order' => 'DESC',
        'paged' => $paged,
        'tax_query' => array(
            array(
                'taxonomy' => 'category',
                'field' => 'id',
                'terms' => $category
            ),
			$tax_q
        )
    );

/* echo '<pre>';
print_r($args);
echo '</pre>';
exit(); */

$wp_query = new WP_Query($args);
$paginationCount = $wp_query->max_num_pages;

$posts = $wp_query->posts;

// The Loop
if (have_posts()) :
    while (have_posts()) : the_post();

    
        $context['blog_archive']['postList'][] =
        array(
            'post_title' => get_the_title($post->ID),
            'thumbnails' => array(
                'url' => get_the_post_thumbnail_url($post->ID, 'large'),
            ),
            'post_url' => get_the_permalink($post->ID),
        );
    endwhile;

    // CUSTOM PAGINATION
    $context['blog_archive']['pagination'] = getCustomPagination($paged, $paginationCount, null, $baseUrl);
    //the_posts_pagination();

endif;
$wp_query = null;
$wp_query = $original_query;
wp_reset_postdata();

/* echo '<pre>';
print_r($_GET['ind']);
echo '</pre>'; */

if($pageFields['view_filter']){
    
    $context['blog_archive']['tags'] = array();
    $context['blog_archive']['tags']['titleindustry'] = __( 'Filter by industry','faactheme' );
    $context['blog_archive']['tags']['titleproducttype'] = __( 'Filter by product type','faactheme' );
    $context['blog_archive']['tags']['copyButton'] = __( 'Filter','faactheme' );

    foreach($tags as $tag){
        /* echo '<!--pre>';
        print_r($tag);
        echo '</pre-->'; */
        if(strpos($tag->name, __( 'industry:','faactheme' )) !== false){
            $context['blog_archive']['tags']['industry'][] = array(
              'name' => str_replace(__( 'industry:','faactheme' ),'',$tag->name),
              'slug' => $tag->slug,
              'checked' => (isset($_GET['ind']) && in_array($tag->slug, $_GET['ind'])) ? true : false,
            );
        } else if(strpos($tag->name, __( 'producttype:','faactheme' )) !== false){
            $context['blog_archive']['tags']['producttype'][] = array(
                'name' => str_replace(__( 'producttype:','faactheme' ),'',$tag->name),
                'slug' => $tag->slug,
                'checked' => (isset($_GET['pt']) && in_array($tag->slug, $_GET['pt'])) ? true : false,
              );
        }
    }
}

Timber::render('/views/templates/archive/index.twig', $context);

get_footer();
