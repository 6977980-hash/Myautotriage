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
 * <lastmod> for pages the theme builds: WordPress only knows when the page
 * row was saved (mostly the day it was created), not when its template,
 * script or data changed. inc/content-dates.json (bin/build-content-dates.py)
 * has the last real change per template from git; state pages also move
 * when their data is reviewed or a dated rule takes effect, and /blog/ when
 * a post changes.
 */
function mat_content_modified( $post ) {
	static $dates = null;
	if ( null === $dates ) {
		$file  = MAT_DIR . '/inc/content-dates.json';
		$dates = is_readable( $file ) ? json_decode( file_get_contents( $file ), true ) : array(); // phpcs:ignore -- local theme file
		$dates = is_array( $dates ) ? $dates : array();
	}
	$times = array( (int) get_post_modified_time( 'U', true, $post ) );
	$tpl   = (int) get_option( 'page_on_front' ) === (int) $post->ID ? 'front-page.php' : get_page_template_slug( $post );
	if ( $tpl && isset( $dates[ $tpl ] ) ) {
		$times[] = (int) strtotime( $dates[ $tpl ] );
	}
	if ( in_array( $tpl, array( 'page-templates/template-state-laws.php', 'page-templates/template-state-hub.php' ), true ) ) {
		static $meta = null;
		$meta = $meta ? $meta : mat_state_laws_meta();
		foreach ( array( $meta['reviewed'], $meta['switched'] ) as $day ) {
			if ( $day ) {
				$times[] = (int) strtotime( $day . ' 00:00:00 UTC' );
			}
		}
	}
	if ( (int) get_option( 'page_for_posts' ) === (int) $post->ID ) {
		$latest = get_posts( array( 'numberposts' => 1, 'orderby' => 'modified', 'fields' => 'ids' ) );
		if ( $latest ) {
			$times[] = (int) get_post_modified_time( 'U', true, $latest[0] );
		}
	}
	return max( $times );
}

function mat_sitemap_lastmod( $entry, $post, $post_type ) {
	if ( 'page' === $post_type ) {
		$entry['lastmod'] = gmdate( 'c', mat_content_modified( $post ) );
	}
	return $entry;
}
add_filter( 'wp_sitemaps_posts_entry', 'mat_sitemap_lastmod', 10, 3 );

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
	if ( get_page_by_path( MAT_INSURER_HUB_SLUG ) ) {
		$lines[] = '## ' . __( 'Insurer complaint ratings', 'myautotriage' );
		$lines[] = '- [' . __( 'Car insurance company complaint ratings (Texas and New York regulator data)', 'myautotriage' ) . '](' . mat_insurer_hub_url() . ')';
		foreach ( mat_insurers() as $insurer ) {
			$lines[] = '- [' . mat_insurer_seo_title( $insurer ) . '](' . mat_insurer_url( $insurer ) . '): ' . mat_insurer_summary( $insurer );
		}
		$lines[] = '';
	}
	$lowball = get_page_by_path( MAT_OUTCOMES_INDEX_SLUG );
	if ( get_page_by_path( MAT_OUTCOMES_SURVEY_SLUG ) ) {
		$lines[] = '## ' . __( 'Claim outcome data', 'myautotriage' );
		// The index is listed only once it has numbers to show.
		if ( $lowball && mat_outcomes_published() ) {
			$lines[] = '- [' . __( 'The Lowball Index', 'myautotriage' ) . '](' . get_permalink( $lowball ) . '): ' . __( 'How much car insurance claim offers move from the first offer to the final payment, from anonymous reader reports.', 'myautotriage' );
		}
		$lines[] = '- [' . __( 'Claim Outcome Survey', 'myautotriage' ) . '](' . mat_outcomes_survey_url() . ')';
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
