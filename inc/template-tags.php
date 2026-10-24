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
	} elseif ( is_home() ) {
		echo ' <span aria-hidden="true">/</span> <span aria-current="page">' . esc_html__( 'Blog', 'myautotriage' ) . '</span>';
	} elseif ( is_category() || is_tag() || is_tax() ) {
		echo ' <span aria-hidden="true">/</span> <a href="' . esc_url( home_url( '/blog/' ) ) . '">' . esc_html__( 'Blog', 'myautotriage' ) . '</a>';
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
			'title'   => __( 'Claim Triage: What Should I Do Next?', 'myautotriage' ),
			'excerpt' => __( 'Answer three questions to get your next step, the right tool or letter, and your state\'s deadlines.', 'myautotriage' ),
			'slug'    => 'claim-triage',
			'meta'    => __( 'Not sure what to do next on your car insurance claim? Answer three questions to get the next step, the right tool or letter, and your state\'s claim deadlines.', 'myautotriage' ),
			'seo_title' => __( 'Car Insurance Claim: What to Do Next (Free Triage Tool)', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Claim Diary', 'myautotriage' ),
			'excerpt' => __( 'Log every call and email on your claim, see your state\'s deadlines as dates, and get a timeline for a complaint.', 'myautotriage' ),
			'slug'    => 'claim-diary',
			'meta'    => __( 'Free car insurance claim log: record every call and promise, see your state\'s claim deadlines as dates, and export a timeline for a complaint.', 'myautotriage' ),
			'seo_title' => __( 'Insurance Claim Diary: Free Claim Log With Deadline Alerts', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Rental Car Extension Letter', 'myautotriage' ),
			'excerpt' => __( 'Insurer ending your rental before the car is fixed? Get a letter asking for the extra days, with the reason.', 'myautotriage' ),
			'slug'    => 'rental-car-extension-letter',
			'meta'    => __( 'Insurance ending your rental car before repairs are done? Free letter asking the adjuster for a rental extension, with the extra days and the reason.', 'myautotriage' ),
			'seo_title' => __( 'Insurance Rental Car Extension Letter (Free Template)', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Deductible Recovery Calculator', 'myautotriage' ),
			'excerpt' => __( 'Not your fault but you paid the deductible? See how much should come back and what to ask your insurer.', 'myautotriage' ),
			'slug'    => 'deductible-recovery-calculator',
			'meta'    => __( 'Paid your deductible after an accident that wasn\'t your fault? See how much should come back through subrogation and what to send your insurer.', 'myautotriage' ),
			'seo_title' => __( 'How to Get Your Deductible Back: Subrogation Calculator', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Recorded Statement Checklist', 'myautotriage' ),
			'excerpt' => __( 'Adjuster wants to record your statement? See whether you have to, how to prepare and what not to say.', 'myautotriage' ),
			'slug'    => 'recorded-statement-checklist',
			'meta'    => __( 'Insurance adjuster asking for a recorded statement? Find out whether you have to give one, a checklist to prepare, what not to say, and a message to send.', 'myautotriage' ),
			'seo_title' => __( 'Recorded Statement to Insurance: Checklist and What Not to Say', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Diminished Value (17c) Calculator', 'myautotriage' ),
			'excerpt' => __( 'Estimate how much value your car lost after an accident, using the same 17c formula insurers reference.', 'myautotriage' ),
			'slug'    => 'diminished-value-calculator',
			'meta'    => __( "Free diminished value calculator using the 17c formula. Estimate how much resale value your car lost after an accident, then turn it into a demand letter.", 'myautotriage' ),
			'seo_title' => __( 'Free Diminished Value Calculator (17c Formula)', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Total Loss Threshold Calculator', 'myautotriage' ),
			'excerpt' => __( "See whether your state's total loss rule means your insurer should declare your car a total loss.", 'myautotriage' ),
			'slug'    => 'total-loss-threshold-calculator',
			'meta'    => __( "Is your car totaled? Check your state's total loss threshold or formula with your repair estimate and car value. All 50 states and DC, with citations.", 'myautotriage' ),
			'seo_title' => __( 'Is My Car Totaled? Total Loss Threshold Calculator', 'myautotriage' ),
		),
		array(
			'title'   => __( 'GAP Insurance Shortfall Calculator', 'myautotriage' ),
			'excerpt' => __( 'Find out if GAP insurance actually covers the gap between your loan balance and your payout.', 'myautotriage' ),
			'slug'    => 'gap-insurance-shortfall-calculator',
			'meta'    => __( "See how much you'd still owe after a total loss: loan payoff minus the insurer's payout. Find out whether GAP insurance covers the difference.", 'myautotriage' ),
			'seo_title' => __( 'GAP Insurance Calculator: Will You Still Owe Money?', 'myautotriage' ),
		),
		array(
			'title'   => __( 'File-a-Claim Break-Even Calculator', 'myautotriage' ),
			'excerpt' => __( 'Compare your deductible against paying out of pocket before you file a small claim.', 'myautotriage' ),
			'slug'    => 'deductible-vs-premium-calculator',
			'meta'    => __( "Should you file a claim or pay yourself? Compare your deductible plus a multi-year premium increase with the repair cost in seconds.", 'myautotriage' ),
			'seo_title' => __( 'Should I File a Claim? Deductible vs. Premium Calculator', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Car Insurance Refund Calculator', 'myautotriage' ),
			'excerpt' => __( 'Estimate how much prepaid premium you get back when you cancel a policy early, pro-rata or short-rate.', 'myautotriage' ),
			'slug'    => 'car-insurance-refund-calculator',
			'meta'    => __( "Free car insurance refund calculator: see how much premium you get back when you cancel early, pro-rata or short-rate, after any cancellation fee.", 'myautotriage' ),
			'seo_title' => __( 'Car Insurance Refund Calculator: Pro-Rata vs. Short-Rate', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Car Accident Lawsuit Deadline Calculator', 'myautotriage' ),
			'excerpt' => __( 'Find the last day to sue after a car accident in your state, for injuries or car damage, and how many days are left.', 'myautotriage' ),
			'slug'    => 'car-accident-lawsuit-deadline-calculator',
			'meta'    => __( "How long do you have to sue after a car accident? Enter your state and accident date to get the statute of limitations deadline for injury and car damage.", 'myautotriage' ),
			'seo_title' => __( 'Car Accident Statute of Limitations Calculator by State', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Comparative Fault Payout Calculator', 'myautotriage' ),
			'excerpt' => __( 'Partly at fault? See how much you can still recover from the other driver under your state\'s fault rule.', 'myautotriage' ),
			'slug'    => 'comparative-fault-calculator',
			'meta'    => __( "Partly at fault for a car accident? Enter your state, damages and fault percentage to see what you can recover under comparative or contributory negligence.", 'myautotriage' ),
			'seo_title' => __( 'Comparative Negligence Calculator: Partly at Fault Payout', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Michigan Mini-Tort Calculator', 'myautotriage' ),
			'excerpt' => __( 'Michigan crash? See how much of your deductible or uncovered damage, up to $3,000, the at-fault driver owes.', 'myautotriage' ),
			'slug'    => 'michigan-mini-tort-calculator',
			'meta'    => __( 'Michigan mini-tort calculator: see how much you can recover from the at-fault driver for car damage your insurance didn\'t pay, up to $3,000, after your share of fault.', 'myautotriage' ),
			'seo_title' => __( 'Michigan Mini-Tort Calculator: Get Your Deductible Back', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Loss of Use Calculator', 'myautotriage' ),
			'excerpt' => __( 'Work out what the at-fault insurer owes for the days you were without your car, rental or not.', 'myautotriage' ),
			'slug'    => 'loss-of-use-calculator',
			'meta'    => __( "Without your car after an accident? Calculate the loss of use to claim from the at-fault driver's insurer: days without the car times a comparable rental rate.", 'myautotriage' ),
			'seo_title' => __( 'Loss of Use Calculator: Claim for Days Without Your Car', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Total Loss Valuation Checker', 'myautotriage' ),
			'excerpt' => __( 'Check the comparables, adjustments, tax and fees in your total loss valuation report and see how much is in question.', 'myautotriage' ),
			'slug'    => 'total-loss-valuation-checker',
			'meta'    => __( 'Total loss offer too low? Check your CCC, Mitchell or Audatex report for distant comparables, condition deductions and missing tax and fees, then counter.', 'myautotriage' ),
			'seo_title' => __( 'Total Loss Valuation Checker: Is Your Totaled Car Offer Too Low?', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Late Claim Payment Interest Calculator', 'myautotriage' ),
			'excerpt' => __( 'Estimate the interest owed when an insurer pays your claim after the legal deadline.', 'myautotriage' ),
			'slug'    => 'insurance-late-payment-interest-calculator',
			'meta'    => __( "Insurance company paid your claim late? Calculate late-payment interest, including Texas's 18% and Michigan's 12% statutory rates, and the total to ask for.", 'myautotriage' ),
			'seo_title' => __( 'Late Insurance Claim Payment Interest Calculator', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Claim Payment Deadline Lookup', 'myautotriage' ),
			'excerpt' => __( 'Look up how many days your state gives an insurer to acknowledge, decide, and pay your claim.', 'myautotriage' ),
			'slug'    => 'claim-payment-deadline-by-state',
			'meta'    => __( "How long does insurance have to pay a claim? Look up your state's deadlines to acknowledge, decide and pay a car insurance claim, with the law cited.", 'myautotriage' ),
			'seo_title' => __( 'How Long Does Insurance Have to Pay a Claim? By State', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Auto Insurance Demand Letter Generator', 'myautotriage' ),
			'excerpt' => __( 'Generate a professional demand letter for property damage, underpayment, or diminished value in minutes.', 'myautotriage' ),
			'slug'    => 'demand-letter-generator',
			'meta'    => __( "Free car insurance demand letter generator. Fill in your claim details and get a ready-to-send letter for property damage, underpayment or diminished value.", 'myautotriage' ),
			'seo_title' => __( 'Free Car Insurance Demand Letter Generator', 'myautotriage' ),
		),
		array(
			'title'   => __( 'Claim Denial Appeal Letter Generator', 'myautotriage' ),
			'excerpt' => __( 'Turn a denied auto insurance claim into a clear, evidence-backed written appeal.', 'myautotriage' ),
			'slug'    => 'appeal-letter-generator',
			'meta'    => __( "Car insurance claim denied? Generate a free appeal letter that answers the insurer's stated reason, lists your evidence and sets a response deadline.", 'myautotriage' ),
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
		home_url( '/' . MAT_STATE_HUB_SLUG . '/' ) => __( 'State Laws', 'myautotriage' ),
		home_url( '/blog/' )           => __( 'Guides', 'myautotriage' ),
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
 * The site's founder and editor: the named person who checks the guides,
 * state pages and tools. Shown in bylines, the author box, the About and
 * Editorial Policy pages, and as a schema.org Person.
 */
function mat_editor() {
	return array(
		'name'     => 'Ali Ahmad',
		'job'      => __( 'Founder and Editor', 'myautotriage' ),
		'linkedin' => 'https://www.linkedin.com/in/ali-ahmad-chaudhry-12777486/',
		'bio'      => __( 'Ali Ahmad founded MyAutoTriage and edits its guides, state law pages and free claim tools. Each page is checked against the state insurance codes, regulator data and published claim-handling rules it cites, and updated when those rules change. MyAutoTriage is an independent educational site, so nothing here is legal or insurance advice.', 'myautotriage' ),
	);
}

/**
 * Where the editor's profile lives on this site: the card on the About page.
 */
function mat_editor_url() {
	$about = mat_url_for_slug( 'about-us' );
	return ( $about ? $about : home_url( '/about-us/' ) ) . '#editor';
}

/**
 * The editor's name linked to their profile on the About page.
 */
function mat_editor_link() {
	$e = mat_editor();
	return '<a href="' . esc_url( mat_editor_url() ) . '">' . esc_html( $e['name'] ) . '</a>';
}

/**
 * "Last reviewed <date> by Ali Ahmad, editor." for the state, insurer and
 * adjuster-phrase pages. $format is a translated sprintf string with the
 * date as %1$s and the editor's name as %2$s.
 */
function mat_editor_reviewed_line( $format, $date ) {
	return sprintf( esc_html( $format ), esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date ) ) ), mat_editor_link() );
}

/**
 * Profile card for the editor (About page, Editorial Policy page).
 */
function mat_editor_card() {
	$e = mat_editor();
	return '<aside id="editor" class="mat-author-box mat-editor-card" aria-label="' . esc_attr__( 'Who edits MyAutoTriage', 'myautotriage' ) . '">'
		. '<h2 class="mat-author-box__name">' . esc_html( $e['name'] ) . '</h2>'
		. '<p class="mat-editor-card__job">' . esc_html( $e['job'] . ', MyAutoTriage' ) . '</p>'
		. '<p>' . esc_html( $e['bio'] ) . '</p>'
		. '<p><a href="' . esc_url( $e['linkedin'] ) . '" rel="noopener me" target="_blank">' . esc_html__( 'Ali Ahmad on LinkedIn', 'myautotriage' ) . '</a></p>'
		. '</aside>';
}

/**
 * Add the editor card to the About and Editorial Policy pages. Those pages
 * live in the database, so adding it here keeps one bio in one place.
 */
function mat_editor_card_on_pages( $content ) {
	if ( ! is_page( array( 'about-us', 'editorial-policy' ) ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	if ( is_page( 'editorial-policy' ) ) {
		$intro = '<h2>' . esc_html__( 'Who edits our content', 'myautotriage' ) . '</h2>'
			. '<p>' . esc_html__( 'Every guide, state law page and tool on MyAutoTriage is checked and kept up to date by our editor before and after it goes live.', 'myautotriage' ) . '</p>';
		// The card on the About page carries id="editor"; don't repeat the id.
		return $content . $intro . str_replace( ' id="editor"', '', mat_editor_card() );
	}
	return $content . '<h2>' . esc_html__( 'Who runs MyAutoTriage', 'myautotriage' ) . '</h2>' . mat_editor_card();
}
add_filter( 'the_content', 'mat_editor_card_on_pages', 15 );

/**
 * True when a post was edited at least a day after it was published, so
 * the byline shows "Updated <date>" only for real revisions.
 */
function mat_post_was_updated( $post = null ) {
	$published = (int) get_post_time( 'U', true, $post );
	$modified  = (int) get_post_modified_time( 'U', true, $post );
	return $modified - $published > DAY_IN_SECONDS;
}

/**
 * Shown inside a tool's form when JavaScript is off, since every tool
 * works in the browser.
 */
function mat_noscript_note( $kind = 'calculator' ) {
	$text = 'tool' === $kind
		? __( 'This tool needs JavaScript turned on.', 'myautotriage' )
		: __( 'This calculator needs JavaScript turned on.', 'myautotriage' );
	echo '<noscript><p class="mat-form-error">' . esc_html( $text ) . '</p></noscript>';
}
