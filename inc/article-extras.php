<?php
/**
 * Per-article "Short answer" boxes and source lists.
 *
 * Article bodies live in the database, so the extras are kept here by post
 * slug and printed by single.php: a 40–60 word answer above the body (the
 * part answer engines quote) and the public sources the guide relies on
 * below it. Posts without an entry simply show neither.
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mat_get_article_extras() {
	$naic_900 = array(
		'label' => 'NAIC Unfair Claims Settlement Practices Model Act (Model 900)',
		'url'   => 'https://content.naic.org/sites/default/files/model-law-900.pdf',
	);
	$naic_complaint = array(
		'label' => 'NAIC: How to file a complaint with your state insurance department',
		'url'   => 'https://content.naic.org/consumer/how-to-file-complaint',
	);
	$ca_2695_7 = array(
		'label' => 'California Code of Regulations, title 10, § 2695.7: Standards for prompt, fair and equitable settlements',
		'url'   => 'https://www.law.cornell.edu/regulations/california/10-CCR-2695.7',
	);
	$iii_file = array(
		'label' => 'Insurance Information Institute: How to file an auto insurance claim',
		'url'   => 'https://www.iii.org/article/how-to-file-an-auto-insurance-claim',
	);

	return array(
		'what-to-do-after-a-car-accident-checklist' => array(
			'short_answer' => 'Check for injuries and call 911 if anyone is hurt, move to safety, then call the police and get a report number. Photograph both cars and the scene, swap license, insurance and contact details without discussing fault, get witness names, and notify your insurer promptly, even if you may not claim.',
			'sources'      => array(
				array(
					'label' => 'Insurance Information Institute: What to do at the scene of an accident',
					'url'   => 'https://www.iii.org/article/what-to-do-at-the-scene-of-an-accident',
				),
				$iii_file,
			),
		),
		'should-i-file-a-claim-minor-accident' => array(
			'short_answer' => 'Compare what the insurer would actually pay (repair cost minus your deductible) with the rate increase an at-fault claim usually brings, which often lasts three to five years. If the surcharge over that period is larger, paying out of pocket is usually cheaper. Injuries or the other driver being at fault change the answer.',
			'sources'      => array(
				$iii_file,
			),
		),
		'car-insurance-claim-denied-what-to-do' => array(
			'short_answer' => 'Read the exact reason and policy section the denial cites, then gather evidence that answers that specific reason. Send a written appeal with the evidence and a response deadline. If the insurer won\'t move, file a free complaint with your state department of insurance; unreasonable denials can amount to bad faith.',
			'sources'      => array(
				$ca_2695_7,
				$naic_900,
				$naic_complaint,
			),
		),
		'how-to-file-diminished-value-claim' => array(
			'short_answer' => 'Diminished value is the resale value a car loses because an accident is on its history report, even after a perfect repair. Most states only allow it as a claim against the at-fault driver\'s insurer. Get an independent appraisal or use the 17c formula as a floor, then send a written demand with your evidence.',
			'sources'      => array(
				array(
					'label' => 'State Farm Mutual Automobile Insurance Co. v. Mabry, 556 S.E.2d 114 (Ga. 2001), the Georgia case behind the 17c formula',
					'url'   => '',
				),
				$naic_complaint,
			),
		),
		'totaled-car-payout-too-low-negotiate' => array(
			'short_answer' => 'Ask for the full valuation report, check the mileage, trim, options and condition it used, and find three or more comparable cars for sale near you. Send a written counter-offer with those listings. If you still disagree, your policy\'s appraisal clause lets each side hire an appraiser, with an umpire deciding.',
			'sources'      => array(
				array(
					'label' => 'Maryland Insurance Administration: Understanding total loss',
					'url'   => 'https://insurance.maryland.gov/Consumer/Pages/total-loss.aspx',
				),
				$naic_complaint,
			),
		),
		'gap-insurance-total-loss-still-owe-money' => array(
			'short_answer' => 'Your insurer pays the car\'s actual cash value, not your loan balance. Subtract the settlement (after your deductible) from your payoff amount to see the shortfall. GAP coverage is meant to pay that difference, but many contracts exclude the deductible, past-due payments or add-ons rolled into the loan, and you usually can\'t buy GAP after a loss.',
			'sources'      => array(
				array(
					'label' => 'Consumer Financial Protection Bureau: What is guaranteed asset protection (GAP) insurance?',
					'url'   => 'https://www.consumerfinance.gov/ask-cfpb/what-is-guaranteed-asset-protection-gap-insurance-en-797/',
				),
				array(
					'label' => 'Consumer Financial Protection Bureau: Am I required to buy GAP insurance to get an auto loan?',
					'url'   => 'https://www.consumerfinance.gov/ask-cfpb/am-i-required-to-purchase-an-extended-warranty-or-guaranteed-asset-protection-gap-insurance-from-a-lender-or-dealer-to-get-an-auto-loan-en-807/',
				),
			),
		),
		'how-to-negotiate-insurance-adjuster' => array(
			'short_answer' => 'Work out your own number first from repair estimates, comparable listings and receipts. Keep calls factual, avoid guessing or admitting fault, and answer a low offer with specific evidence instead of frustration. Put every counter-offer and verbal agreement in writing, and escalate to a supervisor or your state insurance department if it stalls.',
			'sources'      => array(
				$naic_900,
				$ca_2695_7,
				$naic_complaint,
			),
		),
		'uninsured-motorist-claim-guide' => array(
			'short_answer' => 'Uninsured motorist coverage pays when the at-fault driver has no insurance and, in many states, in a hit-and-run; underinsured coverage pays when their limits run out. File a police report, notify your own insurer quickly, and document damages as for any claim. Check your policy for arbitration and deadlines, which can differ from a normal claim.',
			'sources'      => array(
				array(
					'label' => 'Insurance Information Institute: Facts and statistics, uninsured motorists',
					'url'   => 'https://www.iii.org/fact-statistic/facts-statistics-uninsured-motorists',
				),
				array(
					'label' => 'Insurance Information Institute: Protect yourself against uninsured motorists',
					'url'   => 'https://www.iii.org/article/protect-yourself-against-uninsured-motorists',
				),
			),
		),
		'how-long-insurance-company-must-pay-claim' => array(
			'short_answer' => 'It depends on your state. Most states set three deadlines: acknowledging the claim, accepting or denying it, and paying once it\'s settled. California, for example, requires a decision within 40 days of proof of claim. The clock usually starts when the insurer has complete proof of loss, so get that confirmed in writing.',
			'sources'      => array(
				$naic_900,
				$ca_2695_7,
				$naic_complaint,
			),
		),
		'small-claims-court-vs-insurance-claim' => array(
			'short_answer' => 'Small claims court suits a modest property-damage dispute within your state\'s dollar limit, when the insurer won\'t budge or an uninsured driver stops responding. You usually sue the at-fault driver, not their insurer. Send a final written demand first, file before the statute of limitations runs out, and bring organized evidence.',
			'sources'      => array(
				array(
					'label' => 'California Courts self-help guide: Small claims in California (an example of the guide every state court publishes)',
					'url'   => 'https://www.courts.ca.gov/selfhelp-smallclaims.htm',
				),
			),
		),
	);
}

function mat_article_extras( $post = null ) {
	$post   = get_post( $post );
	$extras = mat_get_article_extras();
	return ( $post && isset( $extras[ $post->post_name ] ) ) ? $extras[ $post->post_name ] : array();
}

function mat_article_short_answer() {
	$extras = mat_article_extras();
	if ( empty( $extras['short_answer'] ) ) {
		return;
	}
	?>
	<div class="mat-short-answer">
		<p class="mat-short-answer__label"><?php esc_html_e( 'Short answer', 'myautotriage' ); ?></p>
		<p><?php echo esc_html( $extras['short_answer'] ); ?></p>
	</div>
	<?php
}

function mat_article_sources() {
	$extras = mat_article_extras();
	if ( ! empty( $extras['sources'] ) ) {
		mat_render_sources( $extras['sources'] );
	}
}

/**
 * Source URLs for the Article schema's "citation" property.
 */
function mat_article_citations( $post = null ) {
	$extras = mat_article_extras( $post );
	if ( empty( $extras['sources'] ) ) {
		return array();
	}
	return array_values( array_filter( wp_list_pluck( $extras['sources'], 'url' ) ) );
}
