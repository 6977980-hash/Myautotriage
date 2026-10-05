<?php
/**
 * Reusable template helpers shared by header/footer/page templates.
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visible breadcrumb trail. Mirrors mat_schema_breadcrumbs() in inc/seo.php
 * so the JSON-LD and the on-screen trail always agree.
 */
function mat_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	echo '<nav class="mat-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'myautotriage' ) . '">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'myautotriage' ) . '</a>';

	if ( is_singular( 'post' ) ) {
		echo ' <span aria-hidden="true">/</span> <a href="' . esc_url( home_url( '/blog/' ) ) . '">' . esc_html__( 'Blog', 'myautotriage' ) . '</a>';
		echo ' <span aria-hidden="true">/</span> <span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_page() ) {
		$ancestors = get_post_ancestors( get_the_ID() );
		foreach ( array_reverse( $ancestors ) as $ancestor_id ) {
			echo ' <span aria-hidden="true">/</span> <a href="' . esc_url( get_permalink( $ancestor_id ) ) . '">' . esc_html( get_the_title( $ancestor_id ) ) . '</a>';
		}
		echo ' <span aria-hidden="true">/</span> <span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_category() || is_tag() || is_tax() ) {
		echo ' <span aria-hidden="true">/</span> <span aria-current="page">' . esc_html( single_term_title( '', false ) ) . '</span>';
	} elseif ( is_search() ) {
		echo ' <span aria-hidden="true">/</span> <span aria-current="page">' . esc_html__( 'Search results', 'myautotriage' ) . '</span>';
	}

	echo '</nav>';
}

/**
 * Estimated reading time, shown on blog posts — small AEO/UX signal that
 * also happens to be something answer engines like to quote.
 */
function mat_reading_time( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$content = get_post_field( 'post_content', $post_id );
	$words   = str_word_count( wp_strip_all_tags( $content ) );
	$minutes = max( 1, (int) round( $words / 200 ) );
	/* translators: %d: number of minutes */
	return sprintf( _n( '%d min read', '%d min read', $minutes, 'myautotriage' ), $minutes );
}

/**
 * A tool card: used on the homepage and the /tools hub page. $tool is an
 * associative array: title, excerpt, url, icon (inline SVG markup).
 */
function mat_tool_card( $tool ) {
	?>
	<a class="mat-tool-card" href="<?php echo esc_url( $tool['url'] ); ?>">
		<span class="mat-tool-card__icon" aria-hidden="true"><?php echo $tool['icon']; // phpcs:ignore -- trusted inline SVG defined in theme ?></span>
		<span class="mat-tool-card__body">
			<span class="mat-tool-card__title"><?php echo esc_html( $tool['title'] ); ?></span>
			<span class="mat-tool-card__excerpt"><?php echo esc_html( $tool['excerpt'] ); ?></span>
		</span>
		<span class="mat-tool-card__arrow" aria-hidden="true">&rarr;</span>
	</a>
	<?php
}

/**
 * Related posts by shared category, falling back to most recent — keeps
 * every article internally linked to its neighbours without manual upkeep.
 */
function mat_related_posts( $post_id, $count = 3 ) {
	$cats = wp_get_post_categories( $post_id );
	$args = array(
		'post__not_in'   => array( $post_id ),
		'posts_per_page' => $count,
		'ignore_sticky_posts' => 1,
	);
	if ( ! empty( $cats ) ) {
		$args['category__in'] = $cats;
	}
	$related = get_posts( $args );
	if ( empty( $related ) ) {
		return;
	}
	echo '<section class="mat-related" aria-label="' . esc_attr__( 'Related articles', 'myautotriage' ) . '">';
	echo '<h2>' . esc_html__( 'Related reading', 'myautotriage' ) . '</h2>';
	echo '<div class="mat-related__grid">';
	foreach ( $related as $p ) {
		setup_postdata( $p );
		?>
		<a class="mat-related__card" href="<?php echo esc_url( get_permalink( $p ) ); ?>">
			<?php if ( has_post_thumbnail( $p ) ) : ?>
				<?php
				// 'full' rather than the 320x220 'mat-thumb' crop: these images
				// are 1200x630 with the post title baked in, and that crop's
				// aspect ratio cut off the left/right edges of the text.
				echo get_the_post_thumbnail( $p, 'full', array( 'loading' => 'lazy' ) );
				?>
			<?php endif; ?>
			<span class="mat-related__title"><?php echo esc_html( get_the_title( $p ) ); ?></span>
		</a>
		<?php
	}
	wp_reset_postdata();
	echo '</div></section>';
}

/**
 * Central registry of every tool on the site: used to build the /tools hub,
 * the homepage grid, the footer "Tools" menu fallback, and the sitemap
 * priority boost. Single source of truth so a new tool only has to be added
 * here once.
 */
function mat_get_tools_registry() {
	return array(
		array(
			'title'   => __( 'Diminished Value (17c) Calculator', 'myautotriage' ),
			'excerpt' => __( 'Estimate how much value your car lost after an accident, using the same 17c formula insurers reference.', 'myautotriage' ),
			'slug'    => 'diminished-value-calculator',
			'seo_title' => __( 'Free Diminished Value Calculator (17c Formula)', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Total Loss Threshold Calculator', 'myautotriage' ),
			'excerpt' => __( "See whether your state's total loss rule means your insurer should declare your car a total loss.", 'myautotriage' ),
			'slug'    => 'total-loss-threshold-calculator',
			'seo_title' => __( 'Is My Car Totaled? Total Loss Threshold Calculator', 'myautotriage' ),
		),
		array(
			'title'   => __( 'GAP Insurance Shortfall Calculator', 'myautotriage' ),
			'excerpt' => __( 'Find out if GAP insurance actually covers the gap between your loan balance and your payout.', 'myautotriage' ),
			'slug'    => 'gap-insurance-shortfall-calculator',
			'seo_title' => __( 'GAP Insurance Calculator: Will You Still Owe Money?', 'myautotriage' ),
		),
		array(
			'title'   => __( 'File-a-Claim Break-Even Calculator', 'myautotriage' ),
			'excerpt' => __( 'Compare your deductible against paying out of pocket before you file a small claim.', 'myautotriage' ),
			'slug'    => 'deductible-vs-premium-calculator',
			'seo_title' => __( 'Should I File a Claim? Deductible vs. Premium Calculator', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Car Insurance Refund Calculator', 'myautotriage' ),
			'excerpt' => __( 'Estimate how much prepaid premium you get back when you cancel a policy early, pro-rata or short-rate.', 'myautotriage' ),
			'slug'    => 'car-insurance-refund-calculator',
			'seo_title' => __( 'Car Insurance Refund Calculator: Pro-Rata vs. Short-Rate', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Claim Payment Deadline Lookup', 'myautotriage' ),
			'excerpt' => __( 'Look up how many days your state gives an insurer to acknowledge, decide, and pay your claim.', 'myautotriage' ),
			'slug'    => 'claim-payment-deadline-by-state',
			'seo_title' => __( 'How Long Does Insurance Have to Pay a Claim? By State', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Auto Insurance Demand Letter Generator', 'myautotriage' ),
			'excerpt' => __( 'Generate a professional demand letter for property damage, underpayment, or diminished value in minutes.', 'myautotriage' ),
			'slug'    => 'demand-letter-generator',
			'seo_title' => __( 'Free Car Insurance Demand Letter Generator', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Claim Denial Appeal Letter Generator', 'myautotriage' ),
			'excerpt' => __( 'Turn a denied auto insurance claim into a clear, evidence-backed written appeal.', 'myautotriage' ),
			'slug'    => 'appeal-letter-generator',
			'seo_title' => __( 'Insurance Claim Denied? Free Appeal Letter Generator', 'myautotriage' ),
		),
	);
}

/**
 * Simple, accessible pagination used on the blog archive/index templates.
 */
function mat_pagination() {
	the_posts_pagination( array(
		'mid_size'  => 1,
		'prev_text' => __( '&larr; Newer', 'myautotriage' ),
		'next_text' => __( 'Older &rarr;', 'myautotriage' ),
		'screen_reader_text' => __( 'Posts navigation', 'myautotriage' ),
	) );
}

/**
 * Fallback menu shown until the site owner creates a real menu under
 * Appearance > Menus — points to the pages the activation script already
 * created, so navigation works from the very first page load.
 */
function mat_default_primary_menu() {
	$links = array(
		home_url( '/' )                => __( 'Home', 'myautotriage' ),
		home_url( '/tools/' )          => __( 'Tools', 'myautotriage' ),
		home_url( '/blog/' )           => __( 'Blog', 'myautotriage' ),
		home_url( '/about-us/' )       => __( 'About', 'myautotriage' ),
		home_url( '/contact/' )        => __( 'Contact', 'myautotriage' ),
	);
	echo '<ul class="mat-menu">';
	foreach ( $links as $url => $label ) {
		echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * Simple inline SVG icon set (all original, drawn for this theme — no
 * third-party icon library, so there is nothing to license or attribute).
 */
function mat_icon( $name ) {
	$icons = array(
		'calculator' => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="4" y="2" width="16" height="20" rx="2" stroke="currentColor" stroke-width="1.6"/><rect x="7" y="5" width="10" height="4" rx="1" fill="currentColor"/><circle cx="8.5" cy="13" r="1.1" fill="currentColor"/><circle cx="12" cy="13" r="1.1" fill="currentColor"/><circle cx="15.5" cy="13" r="1.1" fill="currentColor"/><circle cx="8.5" cy="17" r="1.1" fill="currentColor"/><circle cx="12" cy="17" r="1.1" fill="currentColor"/><circle cx="15.5" cy="17" r="1.1" fill="currentColor"/></svg>',
		'document'   => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 2h9l5 5v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.6"/><path d="M15 2v5h5" stroke="currentColor" stroke-width="1.6"/><path d="M8 13h8M8 17h8M8 9h3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
		'clock'      => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><path d="M12 7v5l3.5 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>',
		'shield'     => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3l7 3v6c0 4.5-3 7.7-7 9-4-1.3-7-4.5-7-9V6l7-3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'car'        => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 16v-3l2-5a2 2 0 0 1 1.9-1.4h8.2A2 2 0 0 1 18 8l2 5v3" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><rect x="3" y="16" width="18" height="4" rx="1.2" stroke="currentColor" stroke-width="1.6"/><circle cx="7.5" cy="20" r="1.4" fill="currentColor"/><circle cx="16.5" cy="20" r="1.4" fill="currentColor"/></svg>',
		'letter'     => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="m4 6.5 8 6 8-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'map'        => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 4 4 6v14l5-2 6 2 5-2V4l-5 2-6-2Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M9 4v14M15 6v14" stroke="currentColor" stroke-width="1.6"/></svg>',
	);
	return isset( $icons[ $name ] ) ? $icons[ $name ] : '';
}

/**
 * Byline used on articles and in Article schema.
 */
function mat_author_name() {
	return __( 'MyAutoTriage Editorial Team', 'myautotriage' );
}

/**
 * True when a post was edited at least a day after it was published, so
 * the byline shows "Updated <date>" only for real revisions.
 */
function mat_post_was_updated( $post = null ) {
	$published = (int) get_post_time( 'U', true, $post );
	$modified  = (int) get_post_modified_time( 'U', true, $post );
	return $modified - $published > DAY_IN_SECONDS;
}
