<!DOCTYPE html>

<html <?php language_attributes(); ?> class="no-js">
<head>
	
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	
	
	<?php
	
	$nectar_options = get_nectar_theme_options();
	
	if ( ! empty( $nectar_options['responsive'] ) && '1' === $nectar_options['responsive'] ) { 
		echo '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />';
	} else { 
		echo '<meta name="viewport" content="width=1200" />';
	} 
	
	// Shortcut icon fallback.
	if ( ! empty( $nectar_options['favicon'] ) && ! empty( $nectar_options['favicon']['url'] ) ) {
		echo '<link rel="shortcut icon" href="'. esc_url( nectar_options_img( $nectar_options['favicon'] ) ) .'" />';
	}
	
	wp_head();
	
	?>
	<meta name="google-site-verification" content="ASv1EIYOWSI5fKEFnsHV4pVBKydjK_N3amYg_3H3oB0" />
	<meta name="facebook-domain-verification" content="t2m4cllaqg7sg10nnyn6js7yamcfd5" />
	
	<!--REMOVER NOFOLLOW-->
	<script>
jQuery('document').ready(function(){
        jQuery("a[rel~='nofollow']").each(function(){
                jQuery(this).attr('rel', jQuery(this).attr('rel').replace('nofollow',''));
        }); 
}); 
</script>
	<!-- FIM-->

	<!-- AMP GDPR -->
	<!-- <amp-user-notification id="my-notification" class="sample-notification" layout="nodisplay">
<span>We use cookies by browsing, you consent to use of cookies &nbsp;&nbsp;Cookie Notice
<a href="https://netnaps.com/cookie-notification/">Cookie-Notification</a><button on="tap:my-notification.dismiss">Accept <span class="dashicons dashicons-yes"></span></button></span>
</amp-user-notification> -->

		<!-- FIM-->

</head>

<?php

$nectar_header_options = nectar_get_header_variables();

?>

<body <?php body_class(); ?> <?php nectar_body_attributes(); ?>>

<script type="text/javascript" async src="https://d335luupugsy5fi2.cloudfront.net/js/loader-scripts/dfbd4141-d8b2-4a81-b798-167befaafa8a-loader.js" ></script>


<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MZNNZXH"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
	
	<?php
	
	nectar_hook_after_body_open();
	
	nectar_hook_before_header_nav();
	
	// Boxed theme option opening div.
	if ( $nectar_header_options['n_boxed_style'] ) {
		echo '<div id="boxed">';
	}
	
	get_template_part( 'includes/partials/header/header-space' );
	
	?>
	
	<div id="header-outer" <?php nectar_header_nav_attributes(); ?>>
		
		<?php
		
		get_template_part( 'includes/partials/header/secondary-navigation' );
		
		if ('ascend' !== $nectar_header_options['theme_skin'] && 
			'left-header' !== $nectar_header_options['header_format']) {
			get_template_part( 'includes/header-search' );
		}
		
		get_template_part( 'includes/partials/header/header-menu' );
		
		
		?>
		
	</div>
	
	<?php
	
	if ( ! empty( $nectar_options['enable-cart'] ) && '1' === $nectar_options['enable-cart'] ) {
		get_template_part( 'includes/partials/header/woo-slide-in-cart' );
	}
	
	if ( 'ascend' === $nectar_header_options['theme_skin'] || 
		'left-header' === $nectar_header_options['header_format'] && 
		'false' !== $nectar_header_options['header_search'] ) {
 		get_template_part( 'includes/header-search' );
	}
	
	?>
	
	<div id="ajax-content-wrap">
		
		<?php
		
		nectar_hook_after_outer_wrap_open();
