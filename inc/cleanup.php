<?php
/**
 * Small hardening and index-hygiene rules:
 *
 * - The WordPress user's display name and login slug must not leak through
 *   author archives, the public REST users endpoint, or oEmbed data.
 * - Blog posts don't take comments (no moderation, spam risk, thin UGC).
 * - URLs from the site's previous car-symptom version answer 410 Gone, so
 *   search engines drop them faster than a plain 404.
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Author archives just repeat the blog under a URL built from the login
 * name, so send them to the blog instead.
 */
function mat_redirect_author_archives() {
	if ( is_author() ) {
		wp_safe_redirect( home_url( '/blog/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'mat_redirect_author_archives', 1 );

/**
 * Hide /wp-json/wp/v2/users from visitors who can't edit posts (the block
 * editor still works for logged-in editors).
 */
function mat_restrict_rest_users( $endpoints ) {
	if ( current_user_can( 'edit_posts' ) ) {
		return $endpoints;
	}
	foreach ( array_keys( $endpoints ) as $route ) {
		if ( 0 === strpos( $route, '/wp/v2/users' ) ) {
			unset( $endpoints[ $route ] );
		}
	}
	return $endpoints;
}
add_filter( 'rest_endpoints', 'mat_restrict_rest_users' );

/**
 * oEmbed responses include the post author's display name and archive URL.
 */
function mat_oembed_hide_author( $data ) {
	unset( $data['author_name'], $data['author_url'] );
	return $data;
}
add_filter( 'oembed_response_data', 'mat_oembed_hide_author' );

/**
 * Close comments and pings everywhere on the front end.
 */
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );

/**
 * Path prefixes from the old car-symptom site. Anything under these that
 * WordPress can't find is gone for good.
 */
function mat_retired_path_prefixes() {
	return array(
		'symptom/',
		'symptom-category/',
		'symptom-library',
		'tire-size-calculator',
		'car-loan-emi-calculator',
		'fuel-mileage-calculator',
		'fuel-cost-calculator',
	);
}

function mat_gone_for_retired_urls() {
	if ( ! is_404() ) {
		return;
	}
	global $wp;
	$path = ltrim( (string) $wp->request, '/' );
	foreach ( mat_retired_path_prefixes() as $prefix ) {
		if ( 0 === strpos( $path, $prefix ) ) {
			status_header( 410 );
			return;
		}
	}
}
add_action( 'template_redirect', 'mat_gone_for_retired_urls', 1 );
