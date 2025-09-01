<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package faac
 */

use Timber\Timber;

$context = Timber::get_context();

get_header();

$context['blogExcerpts'] = get_field('blogExcerpts','option');
// showDebugInfo($context['blogExcerpts']);
//===========================================================
// Add more elements here
//===========================================================
Timber::render('templates/single/index.twig', $context);


get_footer();
