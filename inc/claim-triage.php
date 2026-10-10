<?php
/**
 * Claim Triage: three questions (state, whose insurer, what's happening)
 * that point to the next step, the right tool and letter, and the state's
 * deadlines. Shown on the homepage and on its own page.
 *
 * The situations below only reference existing pages; links whose page is
 * missing are dropped when the data is built, so a missing page never
 * produces a dead link.
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keys: label (what the visitor picks), title (headline of the answer),
 * first (the one thing to do now: slug, optional query, label, why),
 * then (later steps), phrases (adjuster phrase slugs), show (which state
 * facts matter: claim, totalloss, fault, lawsuit), own/other (extra note
 * by whose insurer it is).
 */
function mat_claim_triage_situations() {
	return array(
		'just-happened' => array(
			'label'   => __( 'The accident just happened', 'myautotriage' ),
			'title'   => __( 'Protect the claim before you talk numbers', 'myautotriage' ),
			'first'   => array( 'slug' => 'what-to-do-after-a-car-accident-checklist', 'label' => __( 'Follow the after-accident checklist', 'myautotriage' ), 'why' => __( 'Photos, the police report number and witness details are hardest to get later and decide fault.', 'myautotriage' ) ),
			'then'    => array(
				array( 'slug' => 'should-i-file-a-claim-minor-accident', 'label' => __( 'Decide whether to file or pay yourself', 'myautotriage' ) ),
				array( 'slug' => 'deductible-vs-premium-calculator', 'label' => __( 'Compare the claim against your deductible and a rate increase', 'myautotriage' ) ),
				array( 'slug' => 'car-accident-lawsuit-deadline-calculator', 'label' => __( 'Note the last day to sue, in case it comes to that', 'myautotriage' ) ),
			),
			'phrases' => array( 'we-need-a-recorded-statement', 'file-with-your-own-insurance' ),
			'show'    => array( 'claim', 'fault' ),
		),
		'waiting'       => array(
			'label'   => __( 'The insurer is slow or not answering', 'myautotriage' ),
			'title'   => __( 'Put the deadline in writing', 'myautotriage' ),
			'first'   => array( 'slug' => 'claim-payment-deadline-by-state', 'label' => __( 'Look up the exact deadlines for your claim', 'myautotriage' ), 'why' => __( 'A written follow-up that names the rule and the date usually gets a file moving.', 'myautotriage' ) ),
			'then'    => array(
				array( 'slug' => 'claim-diary', 'label' => __( 'Log every call and email in a claim diary with deadline alerts', 'myautotriage' ) ),
				array( 'slug' => 'demand-letter-generator', 'label' => __( 'Send a demand letter with a response date', 'myautotriage' ) ),
				array( 'slug' => 'insurance-late-payment-interest-calculator', 'label' => __( 'Paid late? Work out the interest', 'myautotriage' ) ),
				array( 'slug' => 'demand-letter-generator', 'query' => array( 'type' => 'doi-complaint' ), 'label' => __( 'Still nothing? Complain to your state insurance department', 'myautotriage' ) ),
			),
			'phrases' => array( 'your-claim-is-under-investigation' ),
			'show'    => array( 'claim' ),
		),
		'low-repair'    => array(
			'label'   => __( 'The repair offer is too low', 'myautotriage' ),
			'title'   => __( 'Answer the number with evidence', 'myautotriage' ),
			'first'   => array( 'slug' => 'insurance-underpayment-demand-letter-generator', 'label' => __( 'Write a counter-offer with your own estimate', 'myautotriage' ), 'why' => __( 'Insurers move on a specific, documented number, not on "it\'s too low".', 'myautotriage' ) ),
			'then'    => array(
				array( 'slug' => 'how-to-negotiate-insurance-adjuster', 'label' => __( 'Read: how to negotiate with the adjuster', 'myautotriage' ) ),
				array( 'slug' => 'total-loss-threshold-calculator', 'label' => __( 'Check whether the car should be totaled instead', 'myautotriage' ) ),
				array( 'slug' => 'demand-letter-generator', 'query' => array( 'type' => 'appraisal' ), 'label' => __( 'Still apart? Invoke the appraisal clause', 'myautotriage' ) ),
			),
			'phrases' => array( 'this-is-our-final-offer', 'we-use-aftermarket-parts', 'betterment-deduction', 'use-our-preferred-shop' ),
			'show'    => array( 'claim', 'totalloss' ),
		),
		'total-loss'    => array(
			'label'   => __( 'My car was totaled and the offer is low', 'myautotriage' ),
			'title'   => __( 'Check the valuation report line by line', 'myautotriage' ),
			'first'   => array( 'slug' => 'total-loss-valuation-checker', 'label' => __( 'Check the valuation report', 'myautotriage' ), 'why' => __( 'Negotiation adjustments, far-away comparables, condition deductions and missing tax and fees are where most of the gap hides.', 'myautotriage' ) ),
			'then'    => array(
				array( 'slug' => 'demand-letter-generator', 'query' => array( 'type' => 'total-loss' ), 'label' => __( 'Send a total loss counter-offer', 'myautotriage' ) ),
				array( 'slug' => 'gap-insurance-shortfall-calculator', 'label' => __( 'Still owe on the loan? Check the GAP shortfall', 'myautotriage' ) ),
				array( 'slug' => 'demand-letter-generator', 'query' => array( 'type' => 'appraisal' ), 'label' => __( 'Still apart? Invoke the appraisal clause', 'myautotriage' ) ),
			),
			'phrases' => array( 'this-is-our-final-offer', 'rental-coverage-is-ending', 'sign-this-release' ),
			'show'    => array( 'totalloss', 'claim' ),
		),
		'owe-loan'      => array(
			'label'   => __( 'Car totaled and I owe more than it\'s worth', 'myautotriage' ),
			'title'   => __( 'Work out the gap before the loan does', 'myautotriage' ),
			'first'   => array( 'slug' => 'gap-insurance-shortfall-calculator', 'label' => __( 'Calculate what you\'ll still owe', 'myautotriage' ), 'why' => __( 'Knowing the gap tells you how much a higher car value or a GAP claim is worth to you.', 'myautotriage' ) ),
			'then'    => array(
				array( 'slug' => 'total-loss-valuation-checker', 'label' => __( 'Raise the car\'s value first: check the valuation report', 'myautotriage' ) ),
				array( 'slug' => 'car-insurance-refund-calculator', 'label' => __( 'Get back unused premium and add-ons', 'myautotriage' ) ),
				array( 'slug' => 'gap-insurance-total-loss-still-owe-money', 'label' => __( 'Read: GAP insurance and a total loss', 'myautotriage' ) ),
			),
			'phrases' => array( 'this-is-our-final-offer' ),
			'show'    => array( 'totalloss' ),
		),
		'denied'        => array(
			'label'   => __( 'My claim was denied', 'myautotriage' ),
			'title'   => __( 'Get the reason in writing, then appeal it', 'myautotriage' ),
			'first'   => array( 'slug' => 'appeal-letter-generator', 'label' => __( 'Write an appeal of the denial', 'myautotriage' ), 'why' => __( 'A denial should name the policy provision it relies on; your appeal answers that provision with evidence.', 'myautotriage' ) ),
			'then'    => array(
				array( 'slug' => 'car-insurance-claim-denied-what-to-do', 'label' => __( 'Read: what to do when a claim is denied', 'myautotriage' ) ),
				array( 'slug' => 'demand-letter-generator', 'query' => array( 'type' => 'doi-complaint' ), 'label' => __( 'Complain to your state insurance department', 'myautotriage' ) ),
				array( 'slug' => 'car-accident-lawsuit-deadline-calculator', 'label' => __( 'Check the last day to sue', 'myautotriage' ) ),
			),
			'phrases' => array( 'file-with-your-own-insurance', 'you-were-partly-at-fault' ),
			'show'    => array( 'claim', 'lawsuit' ),
		),
		'fault'         => array(
			'label'   => __( 'They say I was partly at fault', 'myautotriage' ),
			'title'   => __( 'See what the split costs you, then challenge it', 'myautotriage' ),
			'first'   => array( 'slug' => 'comparative-fault-calculator', 'label' => __( 'Calculate what the fault split does to your payout', 'myautotriage' ), 'why' => __( 'In some states one point of fault changes the payout a little; in others it can block it entirely.', 'myautotriage' ) ),
			'then'    => array(
				array( 'slug' => 'appeal-letter-generator', 'label' => __( 'Dispute the liability decision in writing', 'myautotriage' ) ),
				array( 'slug' => 'small-claims-court-vs-insurance-claim', 'label' => __( 'Read: let a small claims judge decide fault', 'myautotriage' ) ),
			),
			'phrases' => array( 'you-were-partly-at-fault' ),
			'show'    => array( 'fault', 'lawsuit' ),
		),
		'no-car'        => array(
			'label'   => __( 'I\'m without a car or my rental is ending', 'myautotriage' ),
			'title'   => __( 'Claim every day you\'re without the car', 'myautotriage' ),
			'first'   => array( 'slug' => 'loss-of-use-calculator', 'label' => __( 'Calculate the loss of use to claim', 'myautotriage' ), 'why' => __( 'Days without a car have a value, at a comparable rental rate, even if you didn\'t rent.', 'myautotriage' ) ),
			'then'    => array(
				array( 'slug' => 'property-damage-demand-letter-generator', 'label' => __( 'Add it to a property damage demand', 'myautotriage' ) ),
			),
			'phrases' => array( 'rental-coverage-is-ending' ),
			'show'    => array( 'claim' ),
			'own'     => __( 'On your own policy, rental reimbursement stops at the daily and total limits you bought. Loss of use beyond that is claimed from the at-fault driver\'s insurer.', 'myautotriage' ),
		),
		'worth-less'    => array(
			'label'   => __( 'My car is fixed but worth less now', 'myautotriage' ),
			'title'   => __( 'Claim the diminished value', 'myautotriage' ),
			'first'   => array( 'slug' => 'diminished-value-calculator', 'label' => __( 'Estimate the diminished value', 'myautotriage' ), 'why' => __( 'A car with an accident history sells for less even after a good repair. Get a number before you ask.', 'myautotriage' ) ),
			'then'    => array(
				array( 'slug' => 'diminished-value-demand-letter-generator', 'label' => __( 'Send a diminished value demand', 'myautotriage' ) ),
				array( 'slug' => 'how-to-file-diminished-value-claim', 'label' => __( 'Read: how to file a diminished value claim', 'myautotriage' ) ),
			),
			'phrases' => array( 'sign-this-release' ),
			'show'    => array( 'lawsuit' ),
			'own'     => __( 'Diminished value is usually claimed from the at-fault driver\'s insurer. Most policies don\'t pay it on your own collision claim; a few states differ.', 'myautotriage' ),
		),
		'uninsured'     => array(
			'label'   => __( 'The other driver has no insurance', 'myautotriage' ),
			'title'   => __( 'Turn to your own uninsured motorist coverage', 'myautotriage' ),
			'first'   => array( 'slug' => 'uninsured-motorist-claim-guide', 'label' => __( 'Follow the uninsured motorist claim guide', 'myautotriage' ), 'why' => __( 'Your own UM/UMPD or collision coverage is usually the fastest way to get paid.', 'myautotriage' ) ),
			'then'    => array(
				array( 'slug' => 'small-claims-demand-letter-generator', 'label' => __( 'Demand payment from the driver before small claims court', 'myautotriage' ) ),
				array( 'slug' => 'car-accident-lawsuit-deadline-calculator', 'label' => __( 'Check the last day to sue the driver', 'myautotriage' ) ),
			),
			'phrases' => array( 'we-need-a-recorded-statement' ),
			'show'    => array( 'claim', 'lawsuit' ),
		),
	);
}

/**
 * Everything the triage script needs, with links resolved to URLs.
 */
function mat_claim_triage_data() {
	$link = function ( $item ) {
		$url = mat_url_for_slug( $item['slug'] );
		if ( $url && ! empty( $item['query'] ) ) {
			$url = add_query_arg( $item['query'], $url );
		}
		return $url ? array( 'url' => $url, 'label' => $item['label'] ) : null;
	};
	$phrases = array();
	if ( function_exists( 'mat_adjuster_phrases' ) && get_page_by_path( MAT_ADJUSTER_HUB_SLUG ) ) {
		foreach ( mat_adjuster_phrases() as $p ) {
			$phrases[ $p['slug'] ] = array( 'url' => mat_adjuster_url( $p ), 'label' => '"' . $p['phrase'] . '"' );
		}
	}

	$situations = array();
	foreach ( mat_claim_triage_situations() as $key => $s ) {
		$first = $link( $s['first'] );
		if ( ! $first ) {
			continue;
		}
		$first['why'] = $s['first']['why'];
		$out          = array(
			'title'   => $s['title'],
			'first'   => $first,
			'then'    => array_values( array_filter( array_map( $link, $s['then'] ) ) ),
			'phrases' => array(),
			'show'    => $s['show'],
			'own'     => isset( $s['own'] ) ? $s['own'] : '',
		);
		foreach ( $s['phrases'] as $slug ) {
			if ( isset( $phrases[ $slug ] ) ) {
				$out['phrases'][] = $phrases[ $slug ];
			}
		}
		$situations[ $key ] = $out;
	}

	$states   = array();
	$has_hub  = (bool) get_page_by_path( MAT_STATE_HUB_SLUG );
	foreach ( mat_state_laws() as $state ) {
		$d    = $state['deadlines'];
		$item = array(
			'name' => $state['name'],
			'url'  => $has_hub ? mat_state_url( $state ) : '',
		);
		if ( $d ) {
			$item['claim'] = array(
				'acknowledge' => mat_state_within( $d['acknowledge'] ),
				'decide'      => mat_state_within( $d['decide'] ),
				'pay'         => mat_state_within( $d['pay'] ),
			);
		}
		if ( $state['total_loss'] ) {
			$item['totalloss'] = mat_state_total_loss_rule( $state );
		}
		if ( mat_state_fault_rule( $state ) ) {
			$item['fault'] = mat_state_fault_rule( $state ) . ': ' . mat_state_fault_rule( $state, true );
		}
		if ( ! empty( $state['facts']['lawsuit_deadline']['injury_years'] ) ) {
			$sol           = $state['facts']['lawsuit_deadline'];
			$item['lawsuit'] = array(
				'injury'   => mat_years( $sol['injury_years'] ),
				'property' => ! empty( $sol['property_years'] ) ? mat_years( $sol['property_years'] ) : '',
			);
		}
		$states[ $state['code'] ] = $item;
	}

	return array(
		'situations' => $situations,
		'states'     => $states,
	);
}

/**
 * Print the triage form and its empty result box.
 */
function mat_claim_triage_form() {
	$situations = mat_claim_triage_situations();
	wp_enqueue_script( 'mat-triage', MAT_URI . '/assets/js/claim-triage.js', array(), MAT_VERSION, true );
	?>
	<form id="mat-triage-form" class="mat-tool-panel mat-triage" data-triage="<?php echo esc_attr( wp_json_encode( mat_claim_triage_data() ) ); ?>" novalidate>
		<div class="mat-field">
			<label for="mat-triage-situation"><?php esc_html_e( 'What\'s happening with your claim?', 'myautotriage' ); ?></label>
			<select id="mat-triage-situation" required>
				<option value=""><?php esc_html_e( 'Choose what fits best', 'myautotriage' ); ?></option>
				<?php foreach ( $situations as $key => $s ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $s['label'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="mat-field-row">
			<div class="mat-field">
				<label for="mat-triage-state"><?php esc_html_e( 'Your state', 'myautotriage' ); ?></label>
				<select id="mat-triage-state">
					<option value=""><?php esc_html_e( 'Skip for now', 'myautotriage' ); ?></option>
					<?php foreach ( mat_state_laws() as $state ) : ?>
						<option value="<?php echo esc_attr( $state['code'] ); ?>"><?php echo esc_html( $state['name'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="mat-field">
				<label for="mat-triage-party"><?php esc_html_e( 'Whose insurance are you claiming from?', 'myautotriage' ); ?></label>
				<select id="mat-triage-party">
					<option value="other"><?php esc_html_e( 'The other driver\'s', 'myautotriage' ); ?></option>
					<option value="own"><?php esc_html_e( 'My own', 'myautotriage' ); ?></option>
					<option value="unsure"><?php esc_html_e( 'Not sure yet', 'myautotriage' ); ?></option>
				</select>
			</div>
		</div>
		<p id="mat-triage-error" class="mat-form-error" role="alert" hidden></p>
		<div class="mat-tool-actions">
			<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Show my next step', 'myautotriage' ); ?></button>
		</div>
	</form>
	<div id="mat-triage-result" class="mat-triage-result" hidden></div>
	<?php
}
