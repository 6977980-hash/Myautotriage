<?php
/**
 * First-run setup: creates pages, the primary menu, categories, and the
 * launch blog posts (with attached images) so a fresh install is a working,
 * populated site the moment the theme is activated — not a blank shell.
 *
 * Every step checks for existing content first, so re-activating the theme
 * (or a host cloning the site) never creates duplicates.
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function register_activation_hook_compat() {
	add_action( 'after_switch_theme', 'mat_run_activation' );
}

function mat_run_activation() {
	mat_set_permalink_structure();
	$page_ids = mat_create_pages();
	mat_configure_front_page( $page_ids );
	mat_create_primary_menu( $page_ids );
	mat_create_categories_and_posts();
	flush_rewrite_rules();
}

/**
 * Clean "post name" permalinks — required for the tool URLs used throughout
 * this theme (e.g. /diminished-value-calculator/) to work as written.
 */
function mat_set_permalink_structure() {
	global $wp_rewrite;
	if ( get_option( 'permalink_structure' ) !== '/%postname%/' ) {
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
	}
}

/**
 * Creates every page in mat_seed_pages() that doesn't already exist by
 * slug. Returns an array of slug => post ID for every page (existing or
 * newly created), so later steps can reference them.
 */
function mat_create_pages() {
	$ids = array();
	foreach ( mat_seed_pages() as $page ) {
		$existing = get_page_by_path( $page['slug'] );
		if ( $existing ) {
			$ids[ $page['slug'] ] = $existing->ID;
			continue;
		}
		$post_id = wp_insert_post( array(
			'post_title'   => $page['title'],
			'post_name'    => $page['slug'],
			'post_content' => $page['content'],
			'post_status'  => 'publish',
			'post_type'    => 'page',
		) );
		if ( $post_id && ! is_wp_error( $post_id ) ) {
			if ( ! empty( $page['template'] ) ) {
				update_post_meta( $post_id, '_wp_page_template', $page['template'] );
			}
			$ids[ $page['slug'] ] = $post_id;
		}
	}
	return $ids;
}

/**
 * Sets the "Home" page as the static front page and "Blog" as the posts
 * page, and attaches the homepage FAQ schema (kept in sync with the
 * visible FAQ block front-page.php renders).
 */
function mat_configure_front_page( $ids ) {
	if ( empty( $ids['home'] ) || empty( $ids['blog'] ) ) {
		return;
	}
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $ids['home'] );
	update_option( 'page_for_posts', $ids['blog'] );
}

/**
 * Builds a primary navigation menu pointing at the pages this theme just
 * created, and assigns it to the 'primary' theme location. If the site
 * owner later creates their own menu and assigns it manually, this is
 * simply left in place and unused.
 */
function mat_create_primary_menu( $ids ) {
	$menu_name = __( 'Primary Menu', 'myautotriage' );
	$menu_id   = 0;
	$existing  = wp_get_nav_menu_object( $menu_name );

	if ( $existing ) {
		$menu_id = $existing->term_id;
	} else {
		$menu_id = wp_create_nav_menu( $menu_name );
	}

	if ( is_wp_error( $menu_id ) || ! $menu_id ) {
		return;
	}

	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! empty( $items ) ) {
		// Menu already has items (e.g. re-activation) — don't duplicate.
		$locations             = get_theme_mod( 'nav_menu_locations' );
		$locations['primary']  = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
		return;
	}

	$menu_slugs = array(
		'home'  => __( 'Home', 'myautotriage' ),
		'tools' => __( 'Tools', 'myautotriage' ),
		'blog'  => __( 'Blog', 'myautotriage' ),
		'about-us' => __( 'About', 'myautotriage' ),
		'contact'  => __( 'Contact', 'myautotriage' ),
	);

	foreach ( $menu_slugs as $slug => $label ) {
		if ( empty( $ids[ $slug ] ) ) {
			continue;
		}
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'     => $label,
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $ids[ $slug ],
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		) );
	}

	$locations            = get_theme_mod( 'nav_menu_locations' );
	$locations            = is_array( $locations ) ? $locations : array();
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Creates the launch categories and the ten launch articles, attaching a
 * unique featured image (with real alt text) to each from
 * assets/images/blog/.
 */
function mat_create_categories_and_posts() {
	foreach ( mat_seed_articles() as $article ) {
		if ( get_page_by_path( $article['slug'], OBJECT, 'post' ) ) {
			continue; // Already created — skip.
		}

		$cat_id = mat_get_or_create_category( $article['category'] );

		$post_id = wp_insert_post( array(
			'post_title'   => $article['title'],
			'post_name'    => $article['slug'],
			'post_content' => $article['content'],
			'post_excerpt' => $article['excerpt'],
			'post_status'  => 'publish',
			'post_type'    => 'post',
			'post_category'=> $cat_id ? array( $cat_id ) : array(),
		) );

		if ( $post_id && ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, '_mat_meta_description', $article['meta'] );
			mat_attach_seed_image( $post_id, $article['image'] );
		}
	}
}

function mat_get_or_create_category( $name ) {
	$term = get_term_by( 'name', $name, 'category' );
	if ( $term ) {
		return $term->term_id;
	}
	$result = wp_insert_term( $name, 'category' );
	if ( is_wp_error( $result ) ) {
		return 0;
	}
	return $result['term_id'];
}

/**
 * Copies a bundled image from assets/images/blog/ into the media library
 * and sets it as the post's featured image, with real alt text — so every
 * launch article ships with a unique, properly labelled image.
 */
function mat_attach_seed_image( $post_id, $image ) {
	if ( empty( $image['file'] ) ) {
		return;
	}
	$source_path = MAT_DIR . '/assets/images/blog/' . $image['file'];
	if ( ! file_exists( $source_path ) ) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$upload_dir = wp_upload_dir();
	$filename   = wp_unique_filename( $upload_dir['path'], $image['file'] );
	$dest_path  = trailingslashit( $upload_dir['path'] ) . $filename;

	if ( ! copy( $source_path, $dest_path ) ) {
		return;
	}

	$filetype   = wp_check_filetype( $filename, null );
	$attachment = array(
		'post_mime_type' => $filetype['type'],
		'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);

	$attach_id = wp_insert_attachment( $attachment, $dest_path, $post_id );
	if ( is_wp_error( $attach_id ) || ! $attach_id ) {
		return;
	}

	$attach_data = wp_generate_attachment_metadata( $attach_id, $dest_path );
	wp_update_attachment_metadata( $attach_id, $attach_data );

	if ( ! empty( $image['alt'] ) ) {
		update_post_meta( $attach_id, '_wp_attachment_image_alt', $image['alt'] );
	}

	set_post_thumbnail( $post_id, $attach_id );
}

/**
 * Pages added after launch. Activation only runs when the theme is
 * switched, so new tool pages are created once on the first request after
 * a deploy (by slug, so existing pages are never touched). Bump the
 * option value when adding another page here.
 */
function mat_ensure_new_pages() {
	// Bump the version when adding a slug below.
	$version = '7';
	if ( $version === get_option( 'mat_new_pages_version' ) ) {
		return;
	}
	// Mark it done first so two simultaneous first requests can't both
	// create the page.
	update_option( 'mat_new_pages_version', $version );
	$new_slugs = array( 'car-insurance-refund-calculator', 'car-accident-lawsuit-deadline-calculator', 'comparative-fault-calculator', 'loss-of-use-calculator', 'insurance-late-payment-interest-calculator', 'total-loss-valuation-checker', 'claim-triage' );
	foreach ( mat_seed_pages() as $page ) {
		if ( ! in_array( $page['slug'], $new_slugs, true ) || get_page_by_path( $page['slug'] ) ) {
			continue;
		}
		$post_id = wp_insert_post( array(
			'post_title'   => $page['title'],
			'post_name'    => $page['slug'],
			'post_content' => $page['content'],
			'post_status'  => 'publish',
			'post_type'    => 'page',
		) );
		if ( $post_id && ! is_wp_error( $post_id ) && ! empty( $page['template'] ) ) {
			update_post_meta( $post_id, '_wp_page_template', $page['template'] );
		}
	}
}
add_action( 'wp_loaded', 'mat_ensure_new_pages' );

/**
 * One-off corrections to content that lives in the database (seeded pages
 * are only written once, so fixing the seed alone doesn't change the live
 * page). Bump the version when adding a fix; each fix must be safe to
 * re-run.
 */
function mat_apply_content_fixes() {
	$version = '2';
	if ( $version === get_option( 'mat_content_fixes_version' ) ) {
		return;
	}
	update_option( 'mat_content_fixes_version', $version );

	// The tools hub said "Seven" tools after the refund calculator made eight.
	$tools = get_page_by_path( 'tools' );
	if ( $tools && false !== strpos( $tools->post_content, 'Seven free, no-signup tools' ) ) {
		wp_update_post( array(
			'ID'           => $tools->ID,
			'post_content' => str_replace( 'Seven free, no-signup tools', 'Free, no-signup tools', $tools->post_content ),
		) );
	}

	mat_add_primary_menu_links();
}

/**
 * The primary menu was built at launch (Home, Tools, About, Contact), so it
 * had no link to the state laws hub or the guides. Insert both after
 * "Tools", unless the menu already links to them.
 */
function mat_add_primary_menu_links() {
	$locations = get_nav_menu_locations();
	if ( empty( $locations['primary'] ) ) {
		return; // The fallback menu (mat_default_primary_menu) already has them.
	}
	$menu_id = $locations['primary'];
	$items   = wp_get_nav_menu_items( $menu_id );
	if ( ! is_array( $items ) ) {
		return;
	}

	$wanted = array();
	$hub    = get_page_by_path( MAT_STATE_HUB_SLUG );
	if ( $hub ) {
		$wanted[ $hub->ID ] = __( 'State Laws', 'myautotriage' );
	}
	$blog_id = (int) get_option( 'page_for_posts' );
	if ( $blog_id ) {
		$wanted[ $blog_id ] = __( 'Guides', 'myautotriage' );
	}
	foreach ( $items as $item ) {
		unset( $wanted[ (int) $item->object_id ] );
	}
	if ( ! $wanted ) {
		return;
	}

	// New order: everything up to and including "Tools", the new links,
	// then the rest.
	$tools  = get_page_by_path( 'tools' );
	$before = array();
	$after  = array();
	$seen   = ! $tools;
	foreach ( $items as $item ) {
		if ( $seen ) {
			$after[] = $item;
		} else {
			$before[] = $item;
			$seen     = $tools && (int) $item->object_id === $tools->ID;
		}
	}
	$position = 1;
	foreach ( $before as $item ) {
		mat_set_menu_item_order( $item, $position++ );
	}
	foreach ( $wanted as $page_id => $label ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'     => $label,
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $page_id,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-position'  => $position++,
		) );
	}
	foreach ( $after as $item ) {
		mat_set_menu_item_order( $item, $position++ );
	}
}

function mat_set_menu_item_order( $item, $position ) {
	if ( (int) $item->menu_order !== $position ) {
		wp_update_post( array(
			'ID'         => $item->ID,
			'menu_order' => $position,
		) );
	}
}
add_action( 'wp_loaded', 'mat_apply_content_fixes' );
