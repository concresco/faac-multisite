<?php

// this is an example of how to setup custom url pages
// for testing purposes and special endpoints in website

namespace IAKI\TestPage;

add_filter('init', 'IAKI\TestPage\registerRewriteRule');
add_filter('template_include', 'IAKI\TestPage\templateInclude');

const ROUTENAME = 'TestPage';

function registerRewriteRule()
{
  $routeName = ROUTENAME;

  add_rewrite_rule("{$routeName}/?$", "index.php?{$routeName}", "top");
  add_rewrite_tag("%{$routeName}%", "([^&]+)");
}

function setDocumentTitle()
{
  // prevent yoast overwriting the title
  add_filter('pre_get_document_title', function ($title) {
      return '';
  }, 99);

  // set custom title and keep the default separator and site name
  add_filter('document_title_parts', function ($title) {
      $title['title'] = 'Base Style';
      return $title;
  }, 99);
}

function templateInclude($template)
{
  global $wp_query;

  if (isset($wp_query->query_vars[ROUTENAME])) {
      setDocumentTitle();
      add_filter('wp_robots', 'wp_robots_no_robots');
      return get_template_directory() . '/test-page.php';
  }

  return $template;
}