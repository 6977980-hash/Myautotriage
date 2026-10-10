<?php
/**
 * IndexNow: tell Bing (and the other IndexNow engines: Yandex, Seznam,
 * Naver) about new and changed URLs the moment they change, instead of
 * waiting for the next crawl. ChatGPT search and Copilot answer from Bing's
 * index, so this matters for AI answers as much as for Bing itself.
 *
 * - The key is generated once and served at /{key}.txt, which is how the
 *   engines check that a submission really comes from this site.
 * - Publishing, updating or trashing a post or page submits its URL.
 * - Much of the site is built by the theme (tool pages, state pages), so a
 *   deploy changes pages without any post being saved: the first request
 *   after a new theme version schedules one submission of every URL.
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MAT_INDEXNOW_ENDPOINT', 'https://api.indexnow.org/indexnow' );

function mat_indexnow_key() {
	$key = get_option( 'mat_indexnow_key' );
	if ( ! $key || ! preg_match( '/^[a-f0-9]{32}$/', $key ) ) {
		$key = md5( wp_generate_password( 64, true, true ) );
		update_option( 'mat_indexnow_key', $key, false );
	}
	return $key;
}

/**
 * Serve /{key}.txt. Runs on template_redirect (the path is a 404 to
 * WordPress), so no rewrite rule or physical file is needed.
 */
function mat_indexnow_key_file() {
	global $wp;
	$key = mat_indexnow_key();
	if ( $key . '.txt' !== $wp->request ) {
		return;
	}
	status_header( 200 );
	header( 'Content-Type: text/plain; charset=UTF-8' );
	echo $key; // phpcs:ignore -- 32 hex characters
	exit;
}
add_action( 'template_redirect', 'mat_indexnow_key_file', 1 );

/**
 * Submit up to 10,000 URLs in one request. Non-blocking: the engines answer
 * 200/202 and there is nothing useful to do with a failure except try again
 * on the next change.
 */
function mat_indexnow_submit( $urls ) {
	if ( '0' === (string) get_option( 'blog_public' ) || wp_get_environment_type() !== 'production' ) {
		return;
	}
	$home  = home_url( '/' );
	$host  = wp_parse_url( $home, PHP_URL_HOST );
	$urls  = array_values( array_unique( array_filter( (array) $urls, function ( $url ) use ( $host ) {
		return $url && wp_parse_url( $url, PHP_URL_HOST ) === $host;
	} ) ) );
	if ( ! $urls ) {
		return;
	}
	$key = mat_indexnow_key();
	wp_remote_post( MAT_INDEXNOW_ENDPOINT, array(
		'blocking' => false,
		'timeout'  => 5,
		'headers'  => array( 'Content-Type' => 'application/json; charset=utf-8' ),
		'body'     => wp_json_encode( array(
			'host'        => $host,
			'key'         => $key,
			'keyLocation' => home_url( '/' . $key . '.txt' ),
			'urlList'     => array_slice( $urls, 0, 10000 ),
		), JSON_UNESCAPED_SLASHES ),
	) );
}

/**
 * Every public URL the site wants indexed: the home page, published pages
 * and posts, and categories that have posts.
 */
function mat_indexnow_all_urls() {
	$urls = array( home_url( '/' ) );
	$ids  = get_posts( array(
		'post_type'      => array( 'page', 'post' ),
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'has_password'   => false,
	) );
	foreach ( $ids as $id ) {
		$urls[] = get_permalink( $id );
	}
	foreach ( get_categories( array( 'hide_empty' => true ) ) as $cat ) {
		$urls[] = get_category_link( $cat );
	}
	return $urls;
}

function mat_indexnow_on_status_change( $new_status, $old_status, $post ) {
	if ( ! in_array( $post->post_type, array( 'post', 'page' ), true ) || wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) ) {
		return;
	}
	if ( 'publish' !== $new_status && 'publish' !== $old_status ) {
		return;
	}
	// A trashed post's permalink gets a __trashed suffix; the old public URL
	// is the one engines need to recheck (it now answers 404).
	$url = get_permalink( $post );
	if ( 'trash' === $new_status ) {
		$url = home_url( user_trailingslashit( str_replace( '__trashed', '', $post->post_name ) ) );
	}
	mat_indexnow_submit( array( $url ) );
}
add_action( 'transition_post_status', 'mat_indexnow_on_status_change', 10, 3 );

/**
 * After a deploy, submit everything once (from cron, so the visitor whose
 * request noticed the new version doesn't wait for it).
 */
function mat_indexnow_on_deploy() {
	if ( MAT_VERSION === get_option( 'mat_indexnow_version' ) ) {
		return;
	}
	update_option( 'mat_indexnow_version', MAT_VERSION );
	if ( ! wp_next_scheduled( 'mat_indexnow_submit_all' ) ) {
		wp_schedule_single_event( time() + 60, 'mat_indexnow_submit_all' );
	}
}
add_action( 'wp_loaded', 'mat_indexnow_on_deploy', 20 );

function mat_indexnow_submit_all() {
	mat_indexnow_submit( mat_indexnow_all_urls() );
}
add_action( 'mat_indexnow_submit_all', 'mat_indexnow_submit_all' );
