<?php
/**
 * MyAutoTriage theme bootstrap.
 *
 * Hand-written, dependency-free WordPress theme. No page builder, no bundled
 * plugin, no external CDN calls at runtime — everything the front end needs
 * ships inside the theme so the site stays fast and self-contained.
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MAT_VERSION', '1.0.11' );
define( 'MAT_DIR', get_template_directory() );
define( 'MAT_URI', get_template_directory_uri() );

/* ------------------------------------------------------------------------
 * Theme setup
 * ---------------------------------------------------------------------- */
function mat_setup() {
	load_theme_textdomain( 'myautotriage', MAT_DIR . '/languages' );

	// Note: 'title-tag' support is intentionally NOT added here. This theme
	// prints its own <title> tag (see mat_head_seo() in inc/seo.php) with
	// custom per-page/front-page logic; adding 'title-tag' support as well
	// would make WordPress core print a second, conflicting <title> tag.
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'wp-block-styles' );

	// All post thumbnails are generated/uploaded at 1200x630 with the post
	// title baked into the image, so 'mat-card' and 'mat-thumb' are kept at
	// that same 40:21 ratio — a narrower crop (the old 600x400 / 320x220)
	// cuts off the left/right edges of that in-image text. Templates that
	// display these thumbnails use 'full' directly for existing uploads
	// (see template-parts/content-card.php and inc/template-tags.php); these
	// sizes remain registered for any future admin usage that expects them.
	set_post_thumbnail_size( 1200, 630, true );
	add_image_size( 'mat-card', 600, 315, true );
	add_image_size( 'mat-thumb', 320, 168, true );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'myautotriage' ),
		'footer'  => __( 'Footer Menu', 'myautotriage' ),
	) );
}
add_action( 'after_setup_theme', 'mat_setup' );

/* ------------------------------------------------------------------------
 * Assets — one small stylesheet, one small script, no external requests
 * ---------------------------------------------------------------------- */
function mat_assets() {
	wp_enqueue_style( 'mat-main', MAT_URI . '/assets/css/main.css', array(), MAT_VERSION );
	wp_enqueue_script( 'mat-main', MAT_URI . '/assets/js/main.js', array(), MAT_VERSION, true );
	// Shared validation/formatting helpers; tool templates list it as a dependency.
	wp_register_script( 'mat-tools', MAT_URI . '/assets/js/tool-utils.js', array(), MAT_VERSION, true );

	// Per-page tool scripts are enqueued individually from each page template
	// so a visitor reading a blog post never downloads calculator code they
	// are not using — keeps pages lightweight.
}
add_action( 'wp_enqueue_scripts', 'mat_assets' );

function mat_register_tool_script( $handle, $src_relative, $data_handle = null, $data_relative = null ) {
	if ( $data_handle && $data_relative ) {
		wp_register_script( $data_handle, MAT_URI . $data_relative, array(), MAT_VERSION, true );
	}
	$deps = $data_handle ? array( $data_handle ) : array();
	wp_register_script( $handle, MAT_URI . $src_relative, $deps, MAT_VERSION, true );
}

/* ------------------------------------------------------------------------
 * Includes
 * ---------------------------------------------------------------------- */
require MAT_DIR . '/inc/seo.php';
require MAT_DIR . '/inc/sitemap.php';
require MAT_DIR . '/inc/indexnow.php';
require MAT_DIR . '/inc/cleanup.php';
require MAT_DIR . '/inc/tool-extras.php';
require MAT_DIR . '/inc/article-extras.php';
require MAT_DIR . '/inc/state-laws.php';
require MAT_DIR . '/inc/customizer.php';
require MAT_DIR . '/inc/template-tags.php';
require MAT_DIR . '/inc/content-seed.php';
require MAT_DIR . '/inc/seed-articles.php';
require MAT_DIR . '/inc/adsense-ads-txt.php';

/* ------------------------------------------------------------------------
 * Sensible defaults
 * ---------------------------------------------------------------------- */

// Excerpt length + "read more" marker used across archive/related-post cards.
add_filter( 'excerpt_length', function () { return 26; } );
add_filter( 'excerpt_more', function () { return '&hellip;'; } );

// Disable the emoji script/style — one less request, one less external DNS
// lookup to s.w.org, and one less thing an ad-review process can flag.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

// Remove the REST API link, RSD link, and WP version from <head> — smaller
// head, fewer fingerprinting hooks, no functional loss for a content site.
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'template_redirect', 'wp_shortlink_header', 11 );

// Lazy-load is native in modern WordPress core; make sure it stays on.
add_filter( 'wp_lazy_loading_enabled', '__return_true' );

// Baseline security response headers. Conservative on purpose: no CSP or
// HSTS here, since either can silently break the AdSense ad script or lock
// the site into HTTPS-only in a way that needs hosting-level testing first
// — those two are left for the host's control panel / .htaccess, reviewed
// by a human before being turned on. These four are safe defaults with no
// realistic downside for a content site with no third-party iframe embeds.
function mat_security_headers( $wp ) {
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
}
add_action( 'send_headers', 'mat_security_headers' );

/**
 * Keep cached HTML short-lived. The host's CDN was holding pages for 7 days
 * (max-age=604800), so a deploy took up to a week to reach visitors and
 * crawlers. An hour is still plenty of caching for a site this size.
 * Theme assets keep their long cache: their URLs change with MAT_VERSION.
 */
function mat_html_cache_headers() {
	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : 'GET';
	if ( is_user_logged_in() || ! in_array( $method, array( 'GET', 'HEAD' ), true ) ) {
		return;
	}
	header( 'Cache-Control: public, max-age=3600' );
	header_remove( 'Expires' );
}
add_action( 'template_redirect', 'mat_html_cache_headers', 0 );

/* ------------------------------------------------------------------------
 * Nicer defaults for a tool/calculator site
 * ---------------------------------------------------------------------- */

// Shortcode so any page or post can drop a "related tool" callout without a
// developer editing a template.
function mat_tool_cta_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'url'   => '#',
		'label' => __( 'Try the free tool', 'myautotriage' ),
		'title' => '',
	), $atts, 'mat_tool_cta' );

	ob_start();
	?>
	<div class="mat-cta">
		<?php if ( $atts['title'] ) : ?>
			<p class="mat-cta__title"><?php echo esc_html( $atts['title'] ); ?></p>
		<?php endif; ?>
		<a class="mat-btn mat-btn--primary" href="<?php echo esc_url( $atts['url'] ); ?>"><?php echo esc_html( $atts['label'] ); ?></a>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'mat_tool_cta', 'mat_tool_cta_shortcode' );

/**
 * A disclaimer box, reused on every calculator/generator page. Centralised
 * so the wording only has to be reviewed/updated in one place.
 */
function mat_tool_disclaimer( $extra = '' ) {
	echo '<div class="mat-disclaimer" role="note">';
	echo '<strong>' . esc_html__( 'Not legal, financial, or insurance advice.', 'myautotriage' ) . '</strong> ';
	echo esc_html__( 'This tool gives a general, educational estimate only. Insurance laws, deadlines, and formulas vary by state, by policy, and by insurer, and they change over time. Verify anything important with your insurance policy, your state department of insurance, or a licensed attorney before relying on it.', 'myautotriage' );
	if ( $extra ) {
		echo ' ' . esc_html( $extra );
	}
	echo '</div>';
}

/**
 * Theme activation: creates the pages/menu this theme expects so a fresh
 * install is a working site immediately, not a blank slate. Runs once and
 * is safe to run again (checks for existing content by slug first).
 */
require MAT_DIR . '/inc/activation.php';
register_activation_hook_compat();
