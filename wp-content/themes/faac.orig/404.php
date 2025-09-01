<?php

/**
 * The template for displaying 404 pages (not found)
 * Template name: 404 Page
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package faac
 */
$my404 = get_page_by_title('Page 404', OBJECT);
//print_r($my404);


$context = Timber::get_context();

get_header();
?>

<section class="404-page">
	<div class="container-fluid my-3">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="ht-250 adaptive"></div>
					<h1 class="page-title color-blue"><?php echo $my404->post_title; ?></h1>
				</div>
			</div>
			<div class="row position-relative ht-150 release-mobile z-index-10">
				<div class="post-content col-xs-12 col-md-8 col-xl-6 bg-color-white position-absolute z-index-20 py-4">
					<div class="full-width">
						<p><?php echo $my404->post_content; ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<?php

Timber::render('templates/404/index.twig', $context);

get_footer();
