<?php
/**
 * Extra on-page content for each tool: a worked example with real numbers,
 * additional FAQs, and "what to do next" links into the matching letter
 * generator or guide.
 *
 * Kept in the theme (not post content) so every tool page gets it without
 * editing pages in wp-admin, and so the visible FAQ and the FAQPage schema
 * stay in sync through mat_faq_block().
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Per-tool extras, keyed by page slug. Every key is optional.
 *
 * - example: array( 'title' => string, 'html' => trusted HTML )
 * - faqs:    array of array( 'question' => string, 'answer' => string )
 * - next:    array of array( 'slug' => page/post slug, 'label' => string )
 */
function mat_get_tool_extras() {
	return array(

		'diminished-value-calculator' => array(
			'example' => array(
				'title' => __( 'Worked example', 'myautotriage' ),
				'html'  => '<p>' . __( 'A sedan worth $24,000 before the crash, with 32,000 miles and moderate damage to structure and panels:', 'myautotriage' ) . '</p>'
					. '<ul>'
					. '<li>' . __( '10% cap: $24,000 × 10% = $2,400', 'myautotriage' ) . '</li>'
					. '<li>' . __( 'Damage factor (moderate): $2,400 × 0.50 = $1,200', 'myautotriage' ) . '</li>'
					. '<li>' . __( 'Mileage factor (20,000–39,999 miles): $1,200 × 0.80 = $960', 'myautotriage' ) . '</li>'
					. '</ul>'
					. '<p>' . __( 'The 17c estimate is $960. The same car with 12,000 miles and major damage would come out at $2,400 × 0.75 × 1.00 = $1,800. If the car already had unrelated accident history, insurers commonly halve the figure, so $960 becomes $480.', 'myautotriage' ) . '</p>'
					. '<p>' . __( 'Treat the result as a floor for your negotiation, not a ceiling. On newer or low-mileage cars, an independent appraisal based on real resale comparisons often supports a higher number.', 'myautotriage' ) . '</p>',
			),
			'faqs' => array(
				array(
					'question' => __( 'Can I claim diminished value if the accident was my fault?', 'myautotriage' ),
					'answer'   => __( "Usually not. Diminished value is normally claimed from the at-fault driver's insurer. Georgia is the best-known exception, where policyholders can recover diminished value from their own insurer. Check your state's rules and your policy wording.", 'myautotriage' ),
				),
				array(
					'question' => __( 'How long do I have to file a diminished value claim?', 'myautotriage' ),
					'answer'   => __( "You are generally limited by your state's statute of limitations for property damage, which differs by state. Insurers also expect the claim reasonably soon after repairs are finished, so file once you have the final repair invoice.", 'myautotriage' ),
				),
				array(
					'question' => __( 'What evidence makes a diminished value claim stronger?', 'myautotriage' ),
					'answer'   => __( 'The final repair invoice showing the extent of the damage, photos, the vehicle history report showing the accident, and either comparable listings or an independent appraisal showing how accident history lowers resale value for your model.', 'myautotriage' ),
				),
			),
			'next' => array(
				array( 'slug' => 'diminished-value-demand-letter-generator', 'label' => __( 'Turn your number into a diminished value demand letter', 'myautotriage' ) ),
				array( 'slug' => 'how-to-file-diminished-value-claim', 'label' => __( 'Read: how to file a diminished value claim, step by step', 'myautotriage' ) ),
			),
		),

		'total-loss-threshold-calculator' => array(
			'example' => array(
				'title' => __( 'Worked example', 'myautotriage' ),
				'html'  => '<p>' . __( 'Your car\'s actual cash value is $12,000 and the repair estimate is $9,000. That is 75% of its value.', 'myautotriage' ) . '</p>'
					. '<ul>'
					. '<li>' . __( 'New York (75% threshold): $9,000 reaches 75%, so the car meets the total loss threshold.', 'myautotriage' ) . '</li>'
					. '<li>' . __( 'Texas (100% threshold): $9,000 is below 100% of value, so the threshold is not met and the car would normally be repaired.', 'myautotriage' ) . '</li>'
					. '<li>' . __( 'Georgia (Total Loss Formula): add the estimated salvage value. If salvage is $3,500, then $9,000 + $3,500 = $12,500, which exceeds $12,000, so the formula points to a total loss.', 'myautotriage' ) . '</li>'
					. '</ul>'
					. '<p>' . __( 'Insurers can usually choose to total a car below the threshold if repairing it is not economical. The threshold tells you when they must, which matters most when you think a car is being repaired that should have been totaled, or the other way round.', 'myautotriage' ) . '</p>',
			),
			'faqs' => array(
				array(
					'question' => __( 'Can I keep my car if it is declared a total loss?', 'myautotriage' ),
					'answer'   => __( 'Often yes. The insurer deducts the salvage value from your payout and you keep the car, but it will usually get a salvage title, which affects registration, insurance and resale. Rules differ by state.', 'myautotriage' ),
				),
				array(
					'question' => __( "What if I think the insurer's actual cash value is too low?", 'myautotriage' ),
					'answer'   => __( 'Ask for the valuation report and the comparable vehicles used, check them against real local listings for the same trim and mileage, and respond in writing with your own comparables. Many policies also include an appraisal clause for value disputes.', 'myautotriage' ),
				),
			),
			'next' => array(
				array( 'slug' => 'totaled-car-payout-too-low-negotiate', 'label' => __( 'Read: how to negotiate a total loss payout that seems too low', 'myautotriage' ) ),
				array( 'slug' => 'insurance-underpayment-demand-letter-generator', 'label' => __( 'Dispute a low offer with an underpayment demand letter', 'myautotriage' ) ),
				array( 'slug' => 'gap-insurance-shortfall-calculator', 'label' => __( 'Still owe on a loan? Check your GAP shortfall', 'myautotriage' ) ),
			),
		),

		'gap-insurance-shortfall-calculator' => array(
			'example' => array(
				'title' => __( 'Worked example', 'myautotriage' ),
				'html'  => '<p>' . __( 'Your loan payoff is $21,500. The insurer values the car at $17,000 and you have a $1,000 deductible, so the insurance check is $16,000.', 'myautotriage' ) . '</p>'
					. '<ul>'
					. '<li>' . __( 'Total gap: $21,500 − $16,000 = $5,500', 'myautotriage' ) . '</li>'
					. '<li>' . __( 'If your GAP policy covers the deductible, it pays the full $5,500 and you owe nothing.', 'myautotriage' ) . '</li>'
					. '<li>' . __( 'If it excludes the deductible, GAP pays $4,500 and you still owe the lender $1,000.', 'myautotriage' ) . '</li>'
					. '</ul>'
					. '<p>' . __( 'Past-due payments, late fees and products rolled into the loan, like an extended warranty, are common exclusions. If a warranty was financed into the loan, ask the seller for a prorated refund of the unused part.', 'myautotriage' ) . '</p>',
			),
			'faqs' => array(
				array(
					'question' => __( 'Do I keep paying my car loan while the GAP claim is processed?', 'myautotriage' ),
					'answer'   => __( 'Yes, in most cases. Missed payments during the claim can be excluded from GAP coverage and can hurt your credit, so keep paying until the lender confirms the loan is closed.', 'myautotriage' ),
				),
				array(
					'question' => __( 'What documents does a GAP claim need?', 'myautotriage' ),
					'answer'   => __( "Usually the insurer's total loss settlement letter, the loan payoff statement, your payment history, the original purchase or finance contract, and the GAP agreement itself.", 'myautotriage' ),
				),
			),
			'next' => array(
				array( 'slug' => 'gap-insurance-total-loss-still-owe-money', 'label' => __( 'Read: GAP insurance and total loss, will you still owe money?', 'myautotriage' ) ),
				array( 'slug' => 'total-loss-threshold-calculator', 'label' => __( 'Check whether your car should be totaled', 'myautotriage' ) ),
			),
		),

		'deductible-vs-premium-calculator' => array(
			'example' => array(
				'title' => __( 'Worked example', 'myautotriage' ),
				'html'  => '<p>' . __( 'You caused a minor accident. The repair estimate is $1,400 and your deductible is $500. Your insurer is likely to add about $350 a year to your premium for 3 years after an at-fault claim.', 'myautotriage' ) . '</p>'
					. '<ul>'
					. '<li>' . __( 'Filing a claim: $500 deductible + ($350 × 3 years) = $1,550', 'myautotriage' ) . '</li>'
					. '<li>' . __( 'Paying it yourself: $1,400', 'myautotriage' ) . '</li>'
					. '</ul>'
					. '<p>' . __( 'Paying yourself saves about $150 here. With a $3,000 repair the answer flips: the claim costs $1,550 against $3,000 out of pocket. Ask your insurer or agent what surcharge an at-fault claim triggers on your policy, because it varies a lot by insurer and state.', 'myautotriage' ) . '</p>',
			),
			'faqs' => array(
				array(
					'question' => __( 'Is it worth filing a claim for a cracked windshield?', 'myautotriage' ),
					'answer'   => __( 'Glass is often handled differently. Many policies have a separate or zero glass deductible, and many insurers do not surcharge glass-only claims. Check your policy before paying out of pocket.', 'myautotriage' ),
				),
				array(
					'question' => __( 'Should I tell my insurer about an accident even if I pay for it myself?', 'myautotriage' ),
					'answer'   => __( 'Most policies require prompt notice of any accident. Reporting is not the same as filing a claim, and failing to report can cause problems if the other driver files against you later.', 'myautotriage' ),
				),
			),
			'next' => array(
				array( 'slug' => 'should-i-file-a-claim-minor-accident', 'label' => __( 'Read: should you file a claim for a minor accident?', 'myautotriage' ) ),
				array( 'slug' => 'what-to-do-after-a-car-accident-checklist', 'label' => __( 'Read: what to do after a car accident, step by step', 'myautotriage' ) ),
			),
		),

		'car-insurance-refund-calculator' => array(
			'example' => array(
				'title' => __( 'Worked example', 'myautotriage' ),
				'html'  => '<p>' . __( 'You paid $1,200 for a 12-month policy that started on January 1, 2026, and you cancel on May 1, 2026.', 'myautotriage' ) . '</p>'
					. '<ul>'
					. '<li>' . __( 'Days in the term: 365. Days used: 120. Days left: 245.', 'myautotriage' ) . '</li>'
					. '<li>' . __( 'Unused premium: $1,200 × 245 ÷ 365 = $805.48', 'myautotriage' ) . '</li>'
					. '<li>' . __( 'Pro-rata refund: $805.48', 'myautotriage' ) . '</li>'
					. '<li>' . __( 'Short-rate refund with a 10% penalty: $805.48 × 0.90 = $724.93', 'myautotriage' ) . '</li>'
					. '</ul>'
					. '<p>' . __( 'If the insurer also charges a $50 cancellation fee, subtract it from either figure.', 'myautotriage' ) . '</p>',
			),
			'next' => array(
				array( 'slug' => 'deductible-vs-premium-calculator', 'label' => __( 'Thinking about a claim instead? Compare deductible vs. premium increase', 'myautotriage' ) ),
				array( 'slug' => 'gap-insurance-shortfall-calculator', 'label' => __( 'Car totaled? Check what you still owe on the loan', 'myautotriage' ) ),
			),
		),

		'claim-payment-deadline-by-state' => array(
			'faqs' => array(
				array(
					'question' => __( 'When does the clock start?', 'myautotriage' ),
					'answer'   => __( 'It depends on the stage. Acknowledgment deadlines usually run from when the insurer receives notice of your claim. Decision deadlines often run from when they receive a complete proof of loss or all requested information, which is why insurers sometimes ask for more documents.', 'myautotriage' ),
				),
				array(
					'question' => __( 'Are these deadlines in calendar days or business days?', 'myautotriage' ),
					'answer'   => __( 'It varies by state, and the table says which. Texas, for example, uses business days, while California uses calendar days.', 'myautotriage' ),
				),
			),
			'next' => array(
				array( 'slug' => 'how-long-insurance-company-must-pay-claim', 'label' => __( 'Read: how long does an insurance company have to pay your claim?', 'myautotriage' ) ),
				array( 'slug' => 'demand-letter-generator', 'label' => __( 'Insurer missed a deadline? Send a demand letter', 'myautotriage' ) ),
			),
		),

		'demand-letter-generator' => array(
			'faqs' => array(
				array(
					'question' => __( 'How long should I give the insurer to respond?', 'myautotriage' ),
					'answer'   => __( "A deadline of 14 to 30 days is common and reasonable. Check your state's claim-handling deadlines too, since the insurer may already be required to respond sooner.", 'myautotriage' ),
				),
				array(
					'question' => __( 'Does sending a demand letter mean I am suing?', 'myautotriage' ),
					'answer'   => __( 'No. A demand letter is a formal written request that documents your claim and your number. It often settles the matter without court, and if it does not, it shows a judge you tried to resolve it first.', 'myautotriage' ),
				),
			),
			'next' => array(
				array( 'slug' => 'how-to-negotiate-insurance-adjuster', 'label' => __( 'Read: how to negotiate with an insurance adjuster', 'myautotriage' ) ),
				array( 'slug' => 'small-claims-court-vs-insurance-claim', 'label' => __( 'Read: small claims court vs. your insurance company', 'myautotriage' ) ),
			),
		),

		'appeal-letter-generator' => array(
			'example' => array(
				'title' => __( 'Example: answering a "late notice" denial', 'myautotriage' ),
				'html'  => '<p>' . __( 'Your claim was denied because the insurer says you reported the accident late. A strong appeal quotes the exact denial reason, then answers it with evidence:', 'myautotriage' ) . '</p>'
					. '<ul>'
					. '<li>' . __( 'The date and method you first notified the insurer (call log, app screenshot, email).', 'myautotriage' ) . '</li>'
					. '<li>' . __( 'The policy wording on notice, and why your timing was reasonable (for example, injuries or the other driver\'s insurer being contacted first).', 'myautotriage' ) . '</li>'
					. '<li>' . __( 'Whether the delay actually harmed their investigation. In many states an insurer must show prejudice from late notice before denying for it.', 'myautotriage' ) . '</li>'
					. '</ul>',
			),
			'faqs' => array(
				array(
					'question' => __( 'How long do I have to appeal a denied claim?', 'myautotriage' ),
					'answer'   => __( 'Check your denial letter and policy for an internal appeal window, and act quickly either way. Separately, your state statute of limitations limits how long you have to take the dispute further.', 'myautotriage' ),
				),
				array(
					'question' => __( 'What if the internal appeal fails?', 'myautotriage' ),
					'answer'   => __( "You can file a complaint with your state department of insurance, use your policy's appraisal clause for value disputes, go to small claims court for smaller amounts, or talk to an attorney about bad faith if the denial had no reasonable basis.", 'myautotriage' ),
				),
			),
			'next' => array(
				array( 'slug' => 'car-insurance-claim-denied-what-to-do', 'label' => __( 'Read: car insurance claim denied? Here is what to do', 'myautotriage' ) ),
				array( 'slug' => 'claim-payment-deadline-by-state', 'label' => __( "Check your state's claim deadlines", 'myautotriage' ) ),
			),
		),
	);
}

/**
 * The demand-letter variants share one template, so they share the main
 * generator's extras.
 */
function mat_tool_extras_key( $slug ) {
	if ( false !== strpos( $slug, 'demand-letter-generator' ) ) {
		return 'demand-letter-generator';
	}
	return $slug;
}

function mat_tool_extra_faqs( $slug ) {
	$extras = mat_get_tool_extras();
	$key    = mat_tool_extras_key( $slug );
	return isset( $extras[ $key ]['faqs'] ) ? $extras[ $key ]['faqs'] : array();
}

/**
 * Resolve a slug to a URL: pages first, then posts. Returns '' when the
 * target doesn't exist, so a missing page never produces a dead link.
 */
function mat_url_for_slug( $slug ) {
	$page = get_page_by_path( $slug );
	if ( $page ) {
		return get_permalink( $page );
	}
	$posts = get_posts( array(
		'name'           => $slug,
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
	) );
	return $posts ? get_permalink( $posts[0] ) : '';
}

/**
 * Print the worked example and the next-step links for a tool page.
 */
function mat_tool_extras( $slug ) {
	$extras = mat_get_tool_extras();
	$key    = mat_tool_extras_key( $slug );
	if ( empty( $extras[ $key ] ) ) {
		return;
	}
	$tool = $extras[ $key ];

	if ( ! empty( $tool['example'] ) ) {
		echo '<section class="mat-page__content mat-tool-example">';
		echo '<h2>' . esc_html( $tool['example']['title'] ) . '</h2>';
		echo wp_kses_post( $tool['example']['html'] );
		echo '</section>';
	}

	if ( ! empty( $tool['next'] ) ) {
		$links = array();
		foreach ( $tool['next'] as $next ) {
			if ( $next['slug'] === $slug ) {
				continue;
			}
			$url = mat_url_for_slug( $next['slug'] );
			if ( $url ) {
				$links[] = '<li><a href="' . esc_url( $url ) . '">' . esc_html( $next['label'] ) . '</a></li>';
			}
		}
		if ( $links ) {
			echo '<section class="mat-page__content mat-next-steps" aria-label="' . esc_attr__( 'What to do next', 'myautotriage' ) . '">';
			echo '<h2>' . esc_html__( 'What to do next', 'myautotriage' ) . '</h2>';
			echo '<ul>' . implode( '', $links ) . '</ul>'; // phpcs:ignore -- escaped above
			echo '</section>';
		}
	}
}

/**
 * The state deadline data as a server-rendered table, so search engines
 * and answer engines can read it without running the lookup script.
 */
function mat_claim_deadline_table() {
	$file = MAT_DIR . '/assets/js/data/claim-deadlines.json';
	if ( ! is_readable( $file ) ) {
		return;
	}
	$data = json_decode( file_get_contents( $file ), true ); // phpcs:ignore -- local theme file
	if ( empty( $data['states'] ) ) {
		return;
	}
	$states = $data['states'];
	uasort( $states, function ( $a, $b ) {
		return strcmp( $a['name'], $b['name'] );
	} );

	echo '<section class="mat-page__content">';
	echo '<h2>' . esc_html__( 'Claim deadlines by state', 'myautotriage' ) . '</h2>';
	echo '<div class="mat-table-wrap"><table class="mat-table">';
	echo '<thead><tr><th scope="col">' . esc_html__( 'State', 'myautotriage' ) . '</th><th scope="col">' . esc_html__( 'Acknowledge your claim', 'myautotriage' ) . '</th><th scope="col">' . esc_html__( 'Accept or deny', 'myautotriage' ) . '</th><th scope="col">' . esc_html__( 'Pay after settlement', 'myautotriage' ) . '</th></tr></thead><tbody>';
	foreach ( $states as $state ) {
		echo '<tr><th scope="row">' . esc_html( $state['name'] ) . '</th><td>' . esc_html( $state['acknowledge'] ) . '</td><td>' . esc_html( $state['decide'] ) . '</td><td>' . esc_html( $state['pay'] ) . '</td></tr>';
	}
	echo '</tbody></table></div>';
	if ( ! empty( $data['default'] ) ) {
		echo '<p>' . esc_html__( 'Other states generally follow the NAIC model rules:', 'myautotriage' ) . ' '
			. esc_html( $data['default']['acknowledge'] ) . ' '
			. esc_html( $data['default']['decide'] ) . ' '
			. esc_html( $data['default']['pay'] ) . '</p>';
	}
	if ( ! empty( $data['source'] ) ) {
		echo '<p class="mat-field__hint">' . esc_html( $data['source'] ) . '</p>';
	}
	echo '</section>';
}

/**
 * Intro copy for category archives that have no description set in
 * wp-admin. The first sentence doubles as the meta description.
 */
function mat_category_intros() {
	return array(
		'accident-basics'             => __( 'What to do in the first hours and days after a car accident, in the order that protects your claim. These guides cover the scene, the police report, notifying insurers, and claims against uninsured drivers. Each one links to the free tools that help you put numbers and deadlines on your situation.', 'myautotriage' ),
		'filing-a-claim'              => __( 'How to decide whether to file a car insurance claim and how to file it well. These guides weigh the deductible against likely premium surcharges, explain what adjusters ask for, and show which documents make a claim easy to approve.', 'myautotriage' ),
		'claim-denials'               => __( 'What to do when a car insurance claim is denied. These guides explain common denial reasons, how to read a denial letter, how to write an appeal that answers the stated reason, and where to escalate if the appeal fails.', 'myautotriage' ),
		'total-loss-diminished-value' => __( 'Guides for totaled cars and lost resale value: how insurers decide a car is a total loss, how to challenge a low actual cash value, how GAP coverage works, and how to calculate and claim diminished value after repairs.', 'myautotriage' ),
		'negotiation'                 => __( 'How to negotiate a car insurance settlement without a lawyer. These guides cover building a specific, documented number, responding to lowball offers in writing, and the options you have when talks stall, including appraisal and small claims court.', 'myautotriage' ),
		'claim-timelines'             => __( 'How long car insurance claims take and what deadlines insurers must meet. These guides explain state claim-handling rules for acknowledging, deciding and paying claims, and what you can do when an insurer drags its feet.', 'myautotriage' ),
	);
}

function mat_category_intro( $term = null ) {
	$term = $term ?: get_queried_object();
	if ( ! $term || empty( $term->slug ) ) {
		return '';
	}
	$intros = mat_category_intros();
	return isset( $intros[ $term->slug ] ) ? $intros[ $term->slug ] : '';
}
