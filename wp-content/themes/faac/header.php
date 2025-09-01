<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package faac
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<title><?php wp_title(''); ?></title>

	<?php
	// GTM ID create variable empty
    $gtmID = "";

	if(strpos($_SERVER['SERVER_NAME'], "faacentrancesolutions.co.uk") !== false) {
		// GTM ID for UK
		$gtmID = "GTM-TKWSC64";
	}
    if(strpos($_SERVER['SERVER_NAME'], "faacentrancesolutions.fr") !== false) {
		// GTM ID for FR
		$gtmID = "GTM-PVWCJ9Q";
    }
    if(strpos($_SERVER['SERVER_NAME'], "faac.at") !== false) {
		// GTM ID for AU
		$gtmID = "GTM-TTV83FS";
    }
    if(strpos($_SERVER['SERVER_NAME'], "faac.hu") !== false) {
		// GTM ID for HU
		$gtmID = "GTM-NJN62JM";
    }
    if(strpos($_SERVER['SERVER_NAME'], "faacdoorsandshutters.co.uk") !== false) {
		// GTM ID for UK-Scotland
		$gtmID = "GTM-W4PMH68";
    }    
    if(strpos($_SERVER['SERVER_NAME'], "faac-automatischedeuren.nl") !== false) {
		// GTM ID for NL
		$gtmID = "GTM-WTHMSCC";
    }
    //if(strpos($_SERVER['SERVER_NAME'], "faacbv.com") !== false) {
    if(strpos($_SERVER['SERVER_NAME'], "faac.nl") !== false) {
		// GTM ID for BV
		$gtmID = "GTM-PX72LZP";
    }
	if(strpos($_SERVER['SERVER_NAME'], "entrancesolutions.faac.au") !== false) {
		// GTM ID for AU
		$gtmID = "GTM-PC6DL5JC";
    }

	if(!empty($gtmID)){
	?>
		<!-- Google Tag Manager -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','<?php echo $gtmID; ?>');</script>
		<!-- End Google Tag Manager -->
	<?php
	}
	?>

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	
	<?php
	if(!empty($gtmID)){
	?>
		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $gtmID; ?>"
		height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->
	<?php
	}
	?>

	<?php wp_body_open(); ?>
	<div id="page" class="faac">
		<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'faac' ); ?></a>

