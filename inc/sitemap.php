<?php
/**
 * Minimal hand-written XML sitemap + robots.txt rules.
 *
 * WordPress core ships its own sitemap at /wp-sitemap.xml since 5.5, which
 * is fine and left enabled — but many hosts/CDNs and older SEO checklists
 * still expect a plain /sitemap.xml, so this adds a simple, fast one built
 * directly from posts and pages, with no plugin dependency.
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

function mat_sitemap_output() {
	if ( ! get_query_var( 'mat_sitemap' ) ) {
		return;
	}

	header( 'Content-Type: application/xml; charset=UTF-8' );

	$urls = array();

	$front_page_id = (int) get_option( 'page_on_front' );

	$urls[] = array( 'loc' => home_url( '/' ), 'priority' => '1.0' );

	$pages = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'numberposts'    => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	) );
	$tool_slugs = wp_list_pluck( mat_get_tools_registry(), 'slug' );
	foreach ( $pages as $p ) {
		// The static front page is already added above as home_url( '/' );
		// skip it here so it isn't listed twice in the sitemap.
		if ( $front_page_id && $p->ID === $front_page_id ) {
			continue;
		}
		$is_tool = in_array( $p->post_name, $tool_slugs, true );
		$urls[]  = array(
			'loc'        => get_permalink( $p ),
			'lastmod'    => get_the_modified_date( 'c', $p ),
			'priority'   => $is_tool ? '0.9' : '0.7',
		);
	}

	$posts = get_posts( array(
		'post_type'   => 'post',
		'post_status' => 'publish',
		'numberposts' => -1,
	) );
	foreach ( $posts as $p ) {
		$urls[] = array(
			'loc'      => get_permalink( $p ),
			'lastmod'  => get_the_modified_date( 'c', $p ),
			'priority' => '0.6',
		);
	}

	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
	foreach ( $urls as $u ) {
		echo "\t<url>\n";
		echo "\t\t<loc>" . esc_url( $u['loc'] ) . "</loc>\n";
		if ( ! empty( $u['lastmod'] ) ) {
			echo "\t\t<lastmod>" . esc_html( $u['lastmod'] ) . "</lastmod>\n";
		}
		if ( ! empty( $u['priority'] ) ) {
			echo "\t\t<priority>" . esc_html( $u['priority'] ) . "</priority>\n";
		}
		echo "\t</url>\n";
	}
	echo '</urlset>';
	exit;
}
add_action( 'template_redirect', 'mat_sitemap_output' );

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
		'Sitemap: ' . home_url( '/sitemap.xml' ),
		'Sitemap: ' . home_url( '/wp-sitemap.xml' ),
	);
	return implode( "\n", $lines ) . "\n";
}
add_filter( 'robots_txt', 'mat_robots_txt', 10, 2 );
