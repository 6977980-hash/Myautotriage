<?php
/**
 * Lightweight, hand-written on-page SEO + AEO (answer-engine) + schema layer.
 *
 * No SEO plugin is required — this file outputs the title tag, meta
 * description, canonical URL, Open Graph / Twitter Card tags, and JSON-LD
 * structured data (Organization, WebSite, BreadcrumbList, Article,
 * SoftwareApplication for tools, and FAQPage where a page defines FAQs).
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mat_truncate_meta( $text, $limit = 155 ) {
	$text = trim( $text );
	if ( '' === $text ) {
		return $text;
	}
	if ( function_exists( 'mb_strlen' ) ? mb_strlen( $text ) <= $limit : strlen( $text ) <= $limit ) {
		return $text;
	}
	$truncated = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, $limit ) : substr( $text, 0, $limit );
	$last_space = function_exists( 'mb_strrpos' ) ? mb_strrpos( $truncated, ' ' ) : strrpos( $truncated, ' ' );
	if ( false !== $last_space ) {
		$truncated = function_exists( 'mb_substr' ) ? mb_substr( $truncated, 0, $last_space ) : substr( $truncated, 0, $last_space );
	}
	return rtrim( $truncated, " .,;:-\xE2\x80\x94" ) . '…';
}

/**
 * Build a clean, human meta description: prefer an explicit per-page value
 * (post meta '_mat_meta_description'), fall back to the excerpt, fall back
 * to a trimmed version of the content.
 */
function mat_get_meta_description() {
	// Check the front page FIRST: a static front page is also is_singular()
	// (it's a Page), so that generic branch below must not intercept it —
	// otherwise the homepage always falls back to raw page content/excerpt
	// instead of the intended site description.
	if ( is_front_page() || is_home() ) {
		$front_id = (int) get_option( 'page_on_front' );
		if ( $front_id ) {
			$custom = get_post_meta( $front_id, '_mat_meta_description', true );
			if ( $custom ) {
				return $custom;
			}
		}
		return get_bloginfo( 'description' ) ?: __( 'Free calculators and letter generators for car insurance claims: diminished value, total loss, GAP shortfall, claim deadlines, and denied or underpaid claims.', 'myautotriage' );
	}

	if ( is_singular() ) {
		global $post;
		$custom = get_post_meta( $post->ID, '_mat_meta_description', true );
		if ( $custom ) {
			return $custom;
		}
		if ( has_excerpt( $post ) ) {
			return mat_truncate_meta( wp_strip_all_tags( get_the_excerpt( $post ) ) );
		}
		// wp_strip_all_tags() alone collapses adjacent block-level tags with no
		// separating space (e.g. "...formula works</h2><p>After a repair..."
		// becomes "...formula worksAfter a repair..."), so normalize common
		// block-level tag boundaries to a space BEFORE stripping tags, then
		// truncate at a clean word boundary instead of a fixed word count.
		$content = preg_replace( '#</(h[1-6]|p|div|li|ul|ol|br|tr|blockquote)>#i', ' ', $post->post_content );
		$content = wp_strip_all_tags( $content );
		$content = preg_replace( '/\s+/', ' ', trim( $content ) );
		return mat_truncate_meta( $content );
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$desc = term_description();
		if ( $desc ) {
			return mat_truncate_meta( wp_strip_all_tags( $desc ) );
		}
	}

	return get_bloginfo( 'description' );
}

/**
 * The single canonical title string used for both <title> and og:title, so
 * the two never drift apart.
 */
function mat_get_seo_title() {
	// Check the front page FIRST: a static front page is also is_singular()
	// (it's a Page), so the generic "page" branch below must not intercept
	// it — otherwise the homepage's <title> always shows the raw page title
	// ("Home | Site Name") instead of "Site Name — tagline".
	if ( is_front_page() || is_home() ) {
		return get_bloginfo( 'name' ) . ' — ' . get_bloginfo( 'description' );
	}
	if ( is_singular() ) {
		global $post;
		$custom = get_post_meta( $post->ID, '_mat_meta_title', true );
		if ( $custom ) {
			return $custom;
		}
		return get_the_title( $post ) . ' | ' . get_bloginfo( 'name' );
	}
	if ( is_category() || is_tag() || is_tax() ) {
		return single_term_title( '', false ) . ' | ' . get_bloginfo( 'name' );
	}
	if ( is_search() ) {
		/* translators: %s: search query */
		return sprintf( __( 'Search results for "%s"', 'myautotriage' ), get_search_query() ) . ' | ' . get_bloginfo( 'name' );
	}
	if ( is_404() ) {
		return __( 'Page not found', 'myautotriage' ) . ' | ' . get_bloginfo( 'name' );
	}
	return wp_get_document_title();
}

function mat_get_canonical_url() {
	if ( is_singular() ) {
		return get_permalink();
	}
	global $wp;
	return home_url( add_query_arg( array(), $wp->request ) );
}

/**
 * Expose the theme's custom SEO overrides (title/description) to the REST
 * API, so they can be set from outside the classic editor (no dedicated
 * meta box exists yet) — e.g. scripted updates, or a future settings UI.
 */
function mat_register_seo_meta() {
	$args = array(
		'type'          => 'string',
		'single'        => true,
		'show_in_rest'  => true,
		'auth_callback' => function () {
			return current_user_can( 'edit_posts' );
		},
	);
	register_post_meta( 'page', '_mat_meta_title', $args );
	register_post_meta( 'page', '_mat_meta_description', $args );
	register_post_meta( 'post', '_mat_meta_title', $args );
	register_post_meta( 'post', '_mat_meta_description', $args );
}
add_action( 'init', 'mat_register_seo_meta' );

/**
 * Output everything in <head>. Hooked at priority 1 so it appears before
 * anything else a plugin might add.
 */
function mat_head_seo() {
	$title       = mat_get_seo_title();
	$description = mat_get_meta_description();
	$canonical   = mat_get_canonical_url();
	$site_name   = get_bloginfo( 'name' );

	echo "\n<!-- MyAutoTriage SEO -->\n";
	echo '<title>' . esc_html( $title ) . "</title>\n";
	echo '<meta name="description" content="' . esc_attr( $description ) . "\" />\n";
	echo '<link rel="canonical" href="' . esc_url( $canonical ) . "\" />\n";
	echo '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />' . "\n";

	// Open Graph
	echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . "\" />\n";
	echo '<meta property="og:type" content="' . esc_attr( is_singular( 'post' ) ? 'article' : 'website' ) . "\" />\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . "\" />\n";
	echo '<meta property="og:description" content="' . esc_attr( $description ) . "\" />\n";
	echo '<meta property="og:url" content="' . esc_url( $canonical ) . "\" />\n";
	echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . "\" />\n";

	$share_image = mat_get_share_image_url();
	if ( $share_image ) {
		echo '<meta property="og:image" content="' . esc_url( $share_image ) . "\" />\n";
		echo '<meta property="og:image:width" content="1200" />' . "\n";
		echo '<meta property="og:image:height" content="630" />' . "\n";
	}

	// Twitter Card
	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . "\" />\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $description ) . "\" />\n";
	if ( $share_image ) {
		echo '<meta name="twitter:image" content="' . esc_url( $share_image ) . "\" />\n";
	}

	if ( is_singular( 'post' ) ) {
		echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . "\" />\n";
		echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . "\" />\n";
	}

	echo "<!-- /MyAutoTriage SEO -->\n";
}
add_action( 'wp_head', 'mat_head_seo', 1 );

/**
 * Find a usable share image: per-post featured image, else the theme's
 * generated default OG image.
 */
function mat_get_share_image_url() {
	if ( is_singular() && has_post_thumbnail() ) {
		$img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
		if ( $img ) {
			return $img[0];
		}
	}
	$default = MAT_URI . '/assets/images/social-share-default.jpg';
	return $default;
}

/* ------------------------------------------------------------------------
 * JSON-LD structured data (schema.org) — this is what feeds AEO/GEO:
 * Google's AI Overviews, ChatGPT/Perplexity-style answer engines, and rich
 * results all read this before they read your prose.
 * ---------------------------------------------------------------------- */

function mat_schema_organization() {
	$logo = MAT_URI . '/assets/images/logo-schema.png';
	return array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
		'logo'  => array(
			'@type' => 'ImageObject',
			'url'   => $logo,
			'width' => 512,
			'height' => 512,
		),
	);
}

function mat_schema_website() {
	return array(
		'@type'           => 'WebSite',
		'@id'             => home_url( '/#website' ),
		'url'             => home_url( '/' ),
		'name'            => get_bloginfo( 'name' ),
		'description'     => get_bloginfo( 'description' ),
		'publisher'       => array( '@id' => home_url( '/#organization' ) ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);
}

function mat_schema_breadcrumbs() {
	$items = array();
	$position = 1;

	$items[] = array(
		'@type'    => 'ListItem',
		'position' => $position++,
		'name'     => __( 'Home', 'myautotriage' ),
		'item'     => home_url( '/' ),
	);

	if ( is_singular( 'post' ) ) {
		$cats = get_the_category();
		if ( ! empty( $cats ) ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => __( 'Blog', 'myautotriage' ),
				'item'     => home_url( '/blog/' ),
			);
		}
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $position++,
			'name'     => get_the_title(),
			'item'     => get_permalink(),
		);
	} elseif ( is_page() && ! is_front_page() ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $position++,
			'name'     => get_the_title(),
			'item'     => get_permalink(),
		);
	}

	if ( count( $items ) < 2 ) {
		return null;
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	);
}

function mat_schema_article() {
	if ( ! is_singular( 'post' ) ) {
		return null;
	}
	global $post;
	$image = mat_get_share_image_url();
	return array(
		'@type'            => 'Article',
		'headline'         => get_the_title(),
		'description'      => mat_get_meta_description(),
		'image'            => $image,
		'author'           => array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		),
		'publisher'        => array( '@id' => home_url( '/#organization' ) ),
		'datePublished'    => get_the_date( 'c', $post ),
		'dateModified'     => get_the_modified_date( 'c', $post ),
		'mainEntityOfPage' => array(
			'@type' => 'WebPage',
			'@id'   => get_permalink( $post ),
		),
	);
}

/**
 * Any page whose slug matches the central tools registry (see
 * mat_get_tools_registry() in inc/template-tags.php) gets marked up as a
 * free SoftwareApplication — this is what makes AI answer engines cite
 * "MyAutoTriage has a free calculator for X" instead of a competitor.
 */
function mat_schema_software_application() {
	if ( ! is_singular( 'page' ) ) {
		return null;
	}
	global $post;
	$tool_slugs = wp_list_pluck( mat_get_tools_registry(), 'slug' );
	if ( ! in_array( $post->post_name, $tool_slugs, true ) ) {
		return null;
	}
	return array(
		'@type'           => 'SoftwareApplication',
		'name'            => get_the_title(),
		'url'             => get_permalink(),
		'applicationCategory' => 'FinanceApplication',
		'operatingSystem' => 'Any (runs in browser)',
		'offers'          => array(
			'@type' => 'Offer',
			'price' => '0',
			'priceCurrency' => 'USD',
		),
		'description'     => mat_get_meta_description(),
	);
}

/**
 * A page defines its FAQs simply by calling mat_faq_block( $faqs ) while it
 * renders (see page-templates/*.php and front-page.php) — that function
 * both prints the visible <details> markup AND stashes the same array in
 * $GLOBALS['mat_page_faqs'], so the JSON-LD below can never drift out of
 * sync with what a visitor actually sees on the page.
 */
function mat_schema_faq() {
	if ( empty( $GLOBALS['mat_page_faqs'] ) ) {
		return null;
	}
	$faqs = $GLOBALS['mat_page_faqs'];
	$entities = array();
	foreach ( $faqs as $faq ) {
		if ( empty( $faq['question'] ) || empty( $faq['answer'] ) ) {
			continue;
		}
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => $faq['question'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $faq['answer'],
			),
		);
	}
	if ( empty( $entities ) ) {
		return null;
	}
	return array(
		'@type'      => 'FAQPage',
		'mainEntity' => $entities,
	);
}

function mat_output_schema() {
	$graph = array_filter( array(
		mat_schema_organization(),
		mat_schema_website(),
		mat_schema_breadcrumbs(),
		mat_schema_article(),
		mat_schema_software_application(),
		mat_schema_faq(),
	) );

	if ( empty( $graph ) ) {
		return;
	}

	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => array_values( $graph ),
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
}
add_action( 'wp_footer', 'mat_output_schema', 5 );

/**
 * FAQ block helper: page templates call mat_faq_block( $faqs ) with the same
 * array they saved to post meta, so the visible HTML and the JSON-LD always
 * match (a common AEO mistake is letting them drift apart).
 */
function mat_faq_block( $faqs ) {
	if ( empty( $faqs ) ) {
		return;
	}
	$GLOBALS['mat_page_faqs'] = $faqs;
	echo '<section class="mat-faq" aria-label="' . esc_attr__( 'Frequently asked questions', 'myautotriage' ) . '">';
	echo '<h2>' . esc_html__( 'Frequently asked questions', 'myautotriage' ) . '</h2>';
	foreach ( $faqs as $faq ) {
		echo '<details class="mat-faq__item">';
		echo '<summary>' . esc_html( $faq['question'] ) . '</summary>';
		echo '<div class="mat-faq__answer"><p>' . esc_html( $faq['answer'] ) . '</p></div>';
		echo '</details>';
	}
	echo '</section>';
}
