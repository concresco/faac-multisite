<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package faac
 */

$context = Timber::get_context();
$pageFields = get_fields();

get_header();

$context['post'] = new Timber\Post();
$content['post']['post_content'] = get_the_excerpt();
//$context['content']['html'] = get_the_content();
$content = apply_filters( 'the_content', get_the_content() );
$context['content']['html'] = $content;


//===========================================================
// Add more elements here
//===========================================================


Timber::render('templates/single/index.twig', $context);


get_footer();
