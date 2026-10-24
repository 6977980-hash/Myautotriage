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
	$version = '14';
	if ( $version === get_option( 'mat_new_pages_version' ) ) {
		return;
	}
	// Mark it done first so two simultaneous first requests can't both
	// create the page.
	update_option( 'mat_new_pages_version', $version );
	$new_slugs = array( 'car-insurance-refund-calculator', 'car-accident-lawsuit-deadline-calculator', 'comparative-fault-calculator', 'loss-of-use-calculator', 'insurance-late-payment-interest-calculator', 'total-loss-valuation-checker', 'claim-triage', 'claim-diary', 'rental-car-extension-letter', 'deductible-recovery-calculator', 'recorded-statement-checklist', 'michigan-mini-tort-calculator', 'insurance-appraisal-clause', 'diminished-value-by-state', 'total-loss-sales-tax-by-state', 'pip-claim-deadlines-by-state' );
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
	$version = '7';
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

	// "17c" is paragraph 17(c) of the Mabry v. State Farm settlement order,
	// not a claims-manual paragraph.
	$dv = get_page_by_path( 'how-to-file-diminished-value-claim', OBJECT, 'post' );
	$old = 'after the paragraph number in the underlying claims manual';
	if ( $dv && false !== strpos( $dv->post_content, $old ) ) {
		wp_update_post( array(
			'ID'           => $dv->ID,
			'post_content' => str_replace( $old, 'after paragraph 17(c) of the settlement order in that case', $dv->post_content ),
		) );
	}

	// Audit 2026-10-24 phase 3: trust pages agree on ads (none yet) and one
	// email, worked examples add up, anecdotes are labelled hypothetical,
	// and appraisal is only offered on your own policy. Each pattern only
	// matches the old text, so re-running changes nothing.
	foreach ( mat_content_fixes_2026_10_24() as $fix ) {
		$post = get_page_by_path( $fix[0], OBJECT, $fix[1] );
		if ( ! $post ) {
			continue;
		}
		$content = preg_replace( $fix[2], $fix[3], $post->post_content );
		if ( null !== $content && $content !== $post->post_content ) {
			wp_update_post( array(
				'ID'           => $post->ID,
				'post_content' => $content,
			) );
		}
	}

	// October 2026 rewrite: the weakest guides get a new body, excerpt and
	// meta description. A rewritten body opens with <p class="mat-lede">,
	// so a post that already has one is left alone.
	foreach ( mat_guide_rewrites() as $slug => $guide ) {
		$post = get_page_by_path( $slug, OBJECT, 'post' );
		if ( ! $post || false !== strpos( $post->post_content, 'mat-lede' ) ) {
			continue;
		}
		wp_update_post( array(
			'ID'           => $post->ID,
			'post_content' => mat_guide_rewrite_content( $slug ),
			'post_excerpt' => $guide['excerpt'],
		) );
		update_post_meta( $post->ID, '_mat_meta_description', $guide['meta'] );
	}

	// Audit 2026-10-24 S12: the guides lose filler adverbs and open with an
	// "At a glance" table. Both steps check for their own result first.
	foreach ( mat_guide_glance_tables() as $slug => $glance ) {
		$post = get_page_by_path( $slug, OBJECT, 'post' );
		if ( ! $post ) {
			continue;
		}
		$content = mat_tidy_guide_text( $post->post_content );
		if ( false === strpos( $content, 'mat-glance' ) ) {
			$content = preg_replace( '#</p>#', '</p>' . mat_glance_table_html( $glance ), $content, 1 );
		}
		if ( $content && $content !== $post->post_content ) {
			wp_update_post( array(
				'ID'           => $post->ID,
				'post_content' => $content,
			) );
		}
	}

	mat_add_primary_menu_links();
}

/**
 * Drop filler words that pad the guides without adding meaning.
 */
function mat_tidy_guide_text( $html ) {
	$rules = array(
		'#one of the more genuinely close decisions#' => 'one of the closest decisions',
		'#confident — genuinely confident, not just hopeful —#u' => 'sure, not just hopeful,',
		'# (genuinely|actually|simply)\b#' => '',
	);
	return preg_replace( array_keys( $rules ), array_values( $rules ), $html );
}

function mat_glance_table_html( $glance ) {
	$html = '<div class="mat-glance mat-table-wrap"><table class="mat-table"><caption>' . esc_html__( 'At a glance', 'myautotriage' ) . '</caption><thead><tr><th>' . esc_html( $glance['head'][0] ) . '</th><th>' . esc_html( $glance['head'][1] ) . '</th></tr></thead><tbody>';
	foreach ( $glance['rows'] as $row ) {
		$html .= '<tr><td>' . esc_html( $row[0] ) . '</td><td>' . esc_html( $row[1] ) . '</td></tr>';
	}
	return $html . '</tbody></table></div>';
}

/**
 * One summary table per guide, keyed by post slug.
 */
function mat_guide_glance_tables() {
	return array(
		'what-to-do-after-a-car-accident-checklist' => array(
			'head' => array( __( 'When', 'myautotriage' ), __( 'What to do', 'myautotriage' ) ),
			'rows' => array(
				array( __( 'At the scene', 'myautotriage' ), __( 'Get safe, call 911 if anyone is hurt, photograph the cars, plates and road, and swap names, insurers and policy numbers.', 'myautotriage' ) ),
				array( __( 'Same day', 'myautotriage' ), __( 'Report it to your insurer, see a doctor if you feel any pain, and write down what happened while you remember it.', 'myautotriage' ) ),
				array( __( 'First week', 'myautotriage' ), __( 'Get the police report, a repair estimate, and start a claim diary of every call.', 'myautotriage' ) ),
				array( __( 'Before your state\'s deadline', 'myautotriage' ), __( 'File the claim (and PIP in no-fault states); the lawsuit deadline still runs while you negotiate.', 'myautotriage' ) ),
			),
		),
		'should-i-file-a-claim-minor-accident' => array(
			'head' => array( __( 'Situation', 'myautotriage' ), __( 'Usually', 'myautotriage' ) ),
			'rows' => array(
				array( __( 'The other driver was at fault', 'myautotriage' ), __( 'Claim from their insurer: no deductible, and normally no surcharge on you.', 'myautotriage' ) ),
				array( __( 'Anyone was hurt', 'myautotriage' ), __( 'Report it to your insurer; injuries can surface days later.', 'myautotriage' ) ),
				array( __( 'Your fault, damage close to your deductible', 'myautotriage' ), __( 'Paying the shop yourself is often cheaper than the surcharge.', 'myautotriage' ) ),
				array( __( 'Your fault, damage well above deductible plus surcharge', 'myautotriage' ), __( 'File; run the break-even calculator to be sure.', 'myautotriage' ) ),
			),
		),
		'car-insurance-claim-denied-what-to-do' => array(
			'head' => array( __( 'Step', 'myautotriage' ), __( 'What to do', 'myautotriage' ) ),
			'rows' => array(
				array( __( '1. Get the reason in writing', 'myautotriage' ), __( 'Ask for the exact policy wording and facts the denial relies on.', 'myautotriage' ) ),
				array( __( '2. Check it against your policy', 'myautotriage' ), __( 'Many denials rest on a wrong fact or an exclusion read too broadly.', 'myautotriage' ) ),
				array( __( '3. Appeal in writing', 'myautotriage' ), __( 'Answer the stated reason with evidence and set a response date.', 'myautotriage' ) ),
				array( __( '4. Escalate', 'myautotriage' ), __( 'Complaint to your state insurance department, appraisal (amount disputes only), small claims or a lawyer.', 'myautotriage' ) ),
			),
		),
		'how-to-file-diminished-value-claim' => array(
			'head' => array( __( 'Question', 'myautotriage' ), __( 'Short answer', 'myautotriage' ) ),
			'rows' => array(
				array( __( 'Who pays?', 'myautotriage' ), __( 'Usually the at-fault driver\'s insurer. Your own insurer only in a few states (Georgia, Oregon, Vermont, Washington) unless your policy excludes it.', 'myautotriage' ) ),
				array( __( 'How much?', 'myautotriage' ), __( 'Insurers start with 17c: at most 10% of the car\'s value, cut for damage and mileage. A market appraisal is often higher.', 'myautotriage' ) ),
				array( __( 'When?', 'myautotriage' ), __( 'After the repair is finished, within your state\'s property damage deadline.', 'myautotriage' ) ),
				array( __( 'Evidence', 'myautotriage' ), __( 'Final repair invoice, photos, history report, and an appraisal or dealer quotes.', 'myautotriage' ) ),
			),
		),
		'totaled-car-payout-too-low-negotiate' => array(
			'head' => array( __( 'Check in the valuation report', 'myautotriage' ), __( 'What to look for', 'myautotriage' ) ),
			'rows' => array(
				array( __( 'Comparable cars', 'myautotriage' ), __( 'Same trim, options and mileage band, and nearby; drop ones that aren\'t.', 'myautotriage' ) ),
				array( __( 'Condition deductions', 'myautotriage' ), __( 'Each one itemised with a dollar amount and evidence.', 'myautotriage' ) ),
				array( __( 'Negotiation or "projected sold" adjustments', 'myautotriage' ), __( 'Ask for the data behind them; you pay the advertised price to replace the car.', 'myautotriage' ) ),
				array( __( 'Sales tax, title and registration', 'myautotriage' ), __( 'Owed in most states, up front or after you buy a replacement.', 'myautotriage' ) ),
			),
		),
		'gap-insurance-total-loss-still-owe-money' => array(
			'head' => array( __( 'Part of what you owe', 'myautotriage' ), __( 'Who usually pays', 'myautotriage' ) ),
			'rows' => array(
				array( __( 'The car\'s actual cash value', 'myautotriage' ), __( 'Your insurer, less your deductible', 'myautotriage' ) ),
				array( __( 'Loan balance above that value', 'myautotriage' ), __( 'GAP, up to its limit (often 125% to 150% of the value)', 'myautotriage' ) ),
				array( __( 'Your deductible', 'myautotriage' ), __( 'You, unless the GAP contract covers it', 'myautotriage' ) ),
				array( __( 'Late payments, fees, rolled-in add-ons', 'myautotriage' ), __( 'Usually you', 'myautotriage' ) ),
			),
		),
		'how-to-negotiate-insurance-adjuster' => array(
			'head' => array( __( 'Do', 'myautotriage' ), __( 'Don\'t', 'myautotriage' ) ),
			'rows' => array(
				array( __( 'Ask how the offer was calculated, in writing', 'myautotriage' ), __( 'Accept or reject a number on the first call', 'myautotriage' ) ),
				array( __( 'Counter with a specific number and evidence', 'myautotriage' ), __( 'Say "this seems low" without support', 'myautotriage' ) ),
				array( __( 'Confirm every call by email', 'myautotriage' ), __( 'Rely on what was said on the phone', 'myautotriage' ) ),
				array( __( 'Know your next step if talks stall', 'myautotriage' ), __( 'Sign a release before the full damage is known', 'myautotriage' ) ),
			),
		),
		'uninsured-motorist-claim-guide' => array(
			'head' => array( __( 'Situation', 'myautotriage' ), __( 'Coverage that usually applies', 'myautotriage' ) ),
			'rows' => array(
				array( __( 'At-fault driver had no insurance, you were hurt', 'myautotriage' ), __( 'Uninsured motorist bodily injury (UM)', 'myautotriage' ) ),
				array( __( 'Their limits are too low for your injuries', 'myautotriage' ), __( 'Underinsured motorist (UIM)', 'myautotriage' ) ),
				array( __( 'Damage to your car from an uninsured driver', 'myautotriage' ), __( 'UM property damage (UMPD) or your collision coverage', 'myautotriage' ) ),
				array( __( 'Hit and run', 'myautotriage' ), __( 'UM, often only with physical contact and a prompt police report', 'myautotriage' ) ),
			),
		),
		'how-long-insurance-company-must-pay-claim' => array(
			'head' => array( __( 'Deadline', 'myautotriage' ), __( 'Common rule', 'myautotriage' ) ),
			'rows' => array(
				array( __( 'Acknowledge your claim', 'myautotriage' ), __( 'Often 10 to 15 days; about a quarter of states just say "promptly"', 'myautotriage' ) ),
				array( __( 'Accept or deny', 'myautotriage' ), __( 'Often 15 to 30 days after proof of loss; some states set no number', 'myautotriage' ) ),
				array( __( 'Pay', 'myautotriage' ), __( 'From 5 business days after a settlement to 60 days after proof of loss, in states that set a number', 'myautotriage' ) ),
				array( __( 'If it is late', 'myautotriage' ), __( 'Write, then complain to your state insurance department; some states add interest', 'myautotriage' ) ),
			),
		),
		'small-claims-court-vs-insurance-claim' => array(
			'head' => array( __( 'Choose', 'myautotriage' ), __( 'When', 'myautotriage' ) ),
			'rows' => array(
				array( __( 'Insurance claim', 'myautotriage' ), __( 'The at-fault driver is insured and their insurer is engaging, even slowly.', 'myautotriage' ) ),
				array( __( 'Small claims court', 'myautotriage' ), __( 'The insurer has stopped moving, the driver is paying out of pocket, or the amount is under your state\'s limit and fault is clear.', 'myautotriage' ) ),
				array( __( 'Neither yet', 'myautotriage' ), __( 'Fault is disputed with little evidence, or the driver has nothing to collect from.', 'myautotriage' ) ),
				array( __( 'Either way', 'myautotriage' ), __( 'Watch your state\'s deadline to sue; negotiation doesn\'t pause it.', 'myautotriage' ) ),
			),
		),
	);
}

/**
 * Slug, post type, pattern, replacement.
 */
function mat_content_fixes_2026_10_24() {
	return array(
		array( 'gap-insurance-total-loss-still-owe-money', 'post', '#leaving a shortfall of \\$6,800\\..*?simply handled\\.#s', 'leaving \\$6,800 still owed on the loan. GAP is meant to cover the \\$6,300 between the payoff and the car\'s value; the other \\$500 is your deductible, which many GAP contracts don\'t cover. Check both lines against your specific GAP contract\'s terms before assuming it\'s simply handled.' ),
		array( 'gap-insurance-total-loss-still-owe-money', 'post', '#Take a slightly different scenario: a \\$31,000 loan.*?the whole time\\.#s', 'Take a slightly different scenario: a \\$31,000 loan payoff on a vehicle two years into a six-year term, an insurer ACV settlement of \\$22,400, a \\$1,000 deductible, and a GAP contract that covers loans up to 150% of the car\'s value but has no deductible-coverage provision. The insurer pays the lender \\$21,400 after the deductible, leaving \\$9,600 on the loan. GAP pays the difference between the payoff and the car\'s value, \\$8,600, which fits under the 150% limit (\\$33,600 here). The remaining \\$1,000 is the deductible, and because this contract doesn\'t cover it, the driver still owes that \\$1,000 to the lender. A contract that also covered the deductible would have left nothing to pay; one with a 125% limit (\\$28,000 here) would have left another \\$3,000 uncovered on top of the deductible, despite the driver having GAP coverage the whole time. The GAP calculator on this site itemises exactly this split when you enter the limit and deductible terms from your contract.' ),
		array( 'totaled-car-payout-too-low-negotiate', 'post', '#A short example of how the whole process plays out#s', 'A hypothetical example of how the process plays out' ),
		array( 'totaled-car-payout-too-low-negotiate', 'post', '#A driver whose five-year-old sedan was totaled.*?no attorney\\.#s', 'Here is a hypothetical example with round numbers, to show the steps rather than to promise a result. A five-year-old sedan is totaled and the first offer is \\$13,000. The Total Loss Threshold Calculator confirms the total-loss decision fits the state\'s rule. Comparable listings in the same mileage band and region show six similar cars listed between \\$14,500 and \\$16,000. A short written response citing five of them, plus a request for the insurer\'s own comparable report, is the kind of specific, evidence-based ask that gives an adjuster a reason to revise the offer; a vague complaint doesn\'t. How much an offer moves, if at all, varies by insurer, state and vehicle.' ),
		array( 'how-long-insurance-company-must-pay-claim', 'post', '#shows the specific, sourced numbers for a set of states.*?either way, it turns#s', 'shows the specific, sourced deadlines for all 50 states and DC, each with the rule it comes from, and it turns' ),
		array( 'how-long-insurance-company-must-pay-claim', 'post', '#To make this concrete: a policyholder whose claim sat.*?direct follow-up alone\\.#s', 'Here is a hypothetical example of how this usually works. Suppose a claim sits without a decision for 55 days in a state with a 30-day decision deadline. The policyholder files a complaint with the state\'s department of insurance, attaching a dated timeline and copies of unanswered follow-up emails. The department forwards the complaint to the insurer and asks for a written response by a set date. The complaint itself doesn\'t decide the claim, but the insurer now has to explain the delay in writing to its regulator, and that is often what moves a stalled file.' ),
		array( 'how-to-file-diminished-value-claim', 'post', '#If the response still doesn.t move, the appraisal clause discussed elsewhere.*?total-loss valuation disputes\\.#s', 'If the response still doesn\'t move, your next step depends on whose insurer you\'re claiming against. Most diminished value claims are third-party claims against the at-fault driver\'s insurer, and there the appraisal clause doesn\'t apply, because it is part of your own policy, not theirs: the usual options are a complaint to your state department of insurance or a small claims case against the at-fault driver. If you\'re claiming under your own policy (where your state allows first-party diminished value), check whether its appraisal clause covers this kind of dispute; some policies limit appraisal to the amount of a covered loss, and some insurers argue diminished value isn\'t covered at all.' ),
		array( 'how-to-file-diminished-value-claim', 'post', '#support a demand letter or, if needed, an appraisal-clause dispute\\.#s', 'support a demand letter, a small claims case or, on a claim under your own policy, an appraisal-clause dispute.' ),
		array( 'how-to-negotiate-insurance-adjuster', 'post', '#appraisal clause, if the dispute is fundamentally about value;#s', 'appraisal clause, if the dispute is about value on a claim under your own policy;' ),
		array( 'about-us', 'page', '#MyAutoTriage is an independent, ad-supported educational publisher\\.#s', 'MyAutoTriage is an independent educational publisher.' ),
		array( 'about-us', 'page', '#This site is supported by display advertising\\. Advertising does not influence#s', 'This site does not show ads yet, and we take no money from insurers, law firms, body shops or lead buyers. We plan to add display advertising (Google AdSense) to pay for running the site; when we do, this page and our Privacy Policy will say so, and advertising will never influence' ),
		array( 'editorial-policy', 'page', '#This site displays third-party advertising, including through Google AdSense\\. Advertisers have no influence#s', 'This site does not display advertising yet. If we add third-party ads (we plan to use Google AdSense), advertisers will have no influence' ),
		array( 'privacy-policy', 'page', '#<li><strong>Blog comments:</strong>.*?</li>#s', '' ),
		array( 'privacy-policy', 'page', '#hello@myautotriage\\.com#s', 'contact@myautotriage.com' ),
		array( 'privacy-policy', 'page', '#We use a small number of cookies required for the site to function \\(for example, remembering your comment details if requested\\)\\.#s', 'The site sets no cookies for ordinary visitors (site administrators who log in get the standard WordPress login cookies). The claim diary uses your browser\'s local storage, described below, which stays on your device.' ),
		array( 'privacy-policy', 'page', '#Blog comments are retained until you or we remove them\\.#s', 'Comments are turned off on this site. Claim outcome survey answers are kept so they can be counted in the Lowball Index; they hold no name, email or IP address.' ),
		array( 'privacy-policy', 'page', '#You may ask us to delete a comment you left, or ask what information we hold about you,#s', 'You may ask what information we hold about you,' ),
		array( 'privacy-policy', 'page', '#September 18, 2026#s', 'October 24, 2026' ),
	);
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
