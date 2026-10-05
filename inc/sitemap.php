<?php
/**
 * Sitemap: one sitemap only, WordPress core's /wp-sitemap.xml.
 *
 * The theme used to serve its own /sitemap.xml as well, which listed a
 * different set of URLs (no categories) and got 301-redirected to
 * /sitemap.xml/ by WordPress's trailing-slash canonical redirect. Two
 * overlapping sitemaps just send search engines mixed signals, so
 * /sitemap.xml now permanently redirects to the core sitemap (keeps any
 * old Search Console submission working).
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mat_sitemap_rewrite() {
	add_rewrite_rule( '^sitemap\.xml$', 'index.php?mat_sitemap=1', 'top' );
}
add_action( 'init', 'mat_sitemap_rewrite' );

function mat_sitemap_query_vars( $vars ) {
	$vars[] = 'mat_sitemap';
	return $vars;
}
add_filter( 'query_vars', 'mat_sitemap_query_vars' );

/**
 * Priority 1 so this runs before core's redirect_canonical() adds the
 * trailing slash.
 */
function mat_sitemap_redirect() {
	if ( ! get_query_var( 'mat_sitemap' ) ) {
		return;
	}
	wp_safe_redirect( home_url( '/wp-sitemap.xml' ), 301 );
	exit;
}
add_action( 'template_redirect', 'mat_sitemap_redirect', 1 );

/**
 * Leave the users sitemap out of /wp-sitemap.xml: it only lists the author
 * archive, which has no content of its own (see inc/cleanup.php).
 */
function mat_sitemap_providers( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'mat_sitemap_providers', 10, 2 );

/**
 * Flush rewrite rules once when the theme activates so /sitemap.xml works
 * immediately without the admin needing to re-save permalinks. (Also see
 * inc/activation.php for the rest of first-run setup.)
 */
function mat_sitemap_flush_on_switch() {
	mat_sitemap_rewrite();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'mat_sitemap_flush_on_switch' );

/**
 * robots.txt — served through WordPress's own virtual-file filter rather
 * than a static file, so it keeps working even if the host doesn't let you
 * drop a physical robots.txt in the web root.
 */
function mat_robots_txt( $output, $public ) {
	if ( '0' == $public ) {
		return $output; // Respect "Discourage search engines" setting.
	}
	$lines = array(
		'User-agent: *',
		'Allow: /',
		'Disallow: /wp-admin/',
		'Allow: /wp-admin/admin-ajax.php',
		'',
		'Sitemap: ' . home_url( '/wp-sitemap.xml' ),
	);
	return implode( "\n", $lines ) . "\n";
}
add_filter( 'robots_txt', 'mat_robots_txt', 10, 2 );
