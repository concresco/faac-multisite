<?php

/*
Template name: Block list page
*/

//use Timber\Timber;

$context = Timber::get_context();

$pageFields = get_fields();

get_header();

//echo '<h1>Contact page</h1>';

$context['titling']['pageTitle'] = get_the_title();
$context['titling']['paragraph'] = apply_filters('the_content', $post->post_content, $post->ID);
//$context['titling']['breadcrumbs'] = $pageFields['breadcrumbs'];


//===========================================================
// BLOCK LIST CONTENT
//===========================================================

$context['block_list']['list'] = $pageFields['block_list'];


Timber::render('templates/blockListPage/index.twig', $context);

get_footer();