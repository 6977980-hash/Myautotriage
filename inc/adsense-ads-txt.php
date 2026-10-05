<?php
/**
 * Virtual /ads.txt — required by Google AdSense so the domain can be
 * approved for monetisation. Only outputs a line once a Publisher ID has
 * been entered in Customizer, so an un-monetised install never ships a
 * fake ads.txt.
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mat_ads_txt_rewrite() {
	add_rewrite_rule( '^ads\.txt$', 'index.php?mat_ads_txt=1', 'top' );
}
add_action( 'init', 'mat_ads_txt_rewrite' );

function mat_ads_txt_query_vars( $vars ) {
	$vars[] = 'mat_ads_txt';
	return $vars;
}
add_filter( 'query_vars', 'mat_ads_txt_query_vars' );

function mat_ads_txt_output() {
	if ( ! get_query_var( 'mat_ads_txt' ) ) {
		return;
	}
	header( 'Content-Type: text/plain; charset=UTF-8' );

	$client_id = get_theme_mod( 'mat_adsense_client_id' );
	if ( $client_id ) {
		// Publisher ID is usually "ca-pub-XXXXXXXXXXXXXXXX" — ads.txt wants
		// just the numeric "pub-XXXXXXXXXXXXXXXX" part.
		$pub = str_replace( 'ca-', '', $client_id );
		echo "google.com, {$pub}, DIRECT, f08c47fec0942fa0\n";
	} else {
		echo "# Add your AdSense Publisher ID in Appearance > Customize > MyAutoTriage Settings\n";
		echo "# to generate this file automatically once your AdSense account exists.\n";
	}
	exit;
}
add_action( 'template_redirect', 'mat_ads_txt_output' );
