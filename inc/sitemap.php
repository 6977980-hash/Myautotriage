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

/**
 * /llms.txt — a short plain-text map of the site for AI crawlers and
 * answer engines (llmstxt.org format). Served from template_redirect on
 * the 404 for that path, so it works without a rewrite-rule flush.
 */
function mat_llms_txt() {
	global $wp;
	if ( 'llms.txt' !== $wp->request ) {
		return;
	}

	$lines   = array();
	$lines[] = '# ' . get_bloginfo( 'name' );
	$lines[] = '';
	$lines[] = '> ' . __( 'Free, independent calculators, letter generators and plain-English guides for US car insurance claims: diminished value, total loss, GAP shortfalls, claim deadlines by state, and denied or underpaid claims. We do not sell insurance, handle claims, or take referral fees.', 'myautotriage' );
	$lines[] = '';
	$lines[] = '## ' . __( 'Tools', 'myautotriage' );
	foreach ( mat_get_tools_registry() as $tool ) {
		$page = get_page_by_path( $tool['slug'] );
		if ( $page ) {
			$lines[] = '- [' . $tool['title'] . '](' . get_permalink( $page ) . '): ' . $tool['excerpt'];
		}
	}
	$lines[] = '';
	$lines[] = '## ' . __( 'Guides', 'myautotriage' );
	foreach ( get_posts( array( 'numberposts' => 50, 'post_status' => 'publish' ) ) as $p ) {
		$lines[] = '- [' . get_the_title( $p ) . '](' . get_permalink( $p ) . ')';
	}
	$lines[] = '';
	$lines[] = '## ' . __( 'Claim laws by state', 'myautotriage' );
	if ( get_page_by_path( MAT_STATE_HUB_SLUG ) ) {
		$lines[] = '- [' . __( 'Car insurance claim laws by state', 'myautotriage' ) . '](' . mat_state_hub_url() . ')';
		foreach ( mat_state_laws() as $state ) {
			$lines[] = '- [' . mat_state_seo_title( $state ) . '](' . mat_state_url( $state ) . ')';
		}
	}
	$lines[] = '';
	if ( get_page_by_path( MAT_ADJUSTER_HUB_SLUG ) ) {
		$lines[] = '## ' . __( 'What the adjuster said', 'myautotriage' );
		$lines[] = '- [' . __( 'Common adjuster phrases decoded', 'myautotriage' ) . '](' . mat_adjuster_hub_url() . ')';
		foreach ( mat_adjuster_phrases() as $phrase ) {
			$lines[] = '- [' . $phrase['seo_title'] . '](' . mat_adjuster_url( $phrase ) . '): ' . $phrase['short'];
		}
		$lines[] = '';
	}
	$lines[] = '## ' . __( 'About', 'myautotriage' );
	foreach ( array( 'about-us', 'editorial-policy', 'disclaimer' ) as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$lines[] = '- [' . get_the_title( $page ) . '](' . get_permalink( $page ) . ')';
		}
	}

	status_header( 200 );
	header( 'Content-Type: text/plain; charset=UTF-8' );
	echo wp_strip_all_tags( implode( "\n", $lines ) ) . "\n"; // phpcs:ignore -- plain text
	exit;
}
add_action( 'template_redirect', 'mat_llms_txt', 1 );
