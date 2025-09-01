<?php
/*
Template name: Blog
*/

$context = Timber::get_context();
global $post;

$pageFields = get_fields();


get_header();

$context['post'] = new Timber\Post();
//$context['breadcrumbs'] = $pageFields['breadcrumbs'];
//$context['additionalContent'] = $pageFields['additionalContent'];

//$baseUrl = get_home_url() . '/news';
//$baseUrl = get_home_url() . __( '/news','faactheme' );
$baseUrl = rtrim(get_permalink(),'/');
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$postPerPage = 8;

$original_query = $wp_query;
$wp_query = null;

$args =
    array(
        'post_type' => 'post',
        //'category_name' => 'news',
        'posts_per_page' => $postPerPage,
        'orderby'   => 'date',
        'order' => 'DESC',
        'paged' => $paged,
    );

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


Timber::render('/views/templates/archive/index.twig', $context);

get_footer();
