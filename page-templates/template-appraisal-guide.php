<?php
/**
 * Template Name: Guide - Appraisal Clause
 *
 * How the appraisal clause in a car insurance policy works, how to invoke
 * it, who pays, and the states that set their own appraisal rules.
 *
 * @package MyAutoTriage
 */

get_header();

$mat_states = array(
	array(
		'state' => 'Texas',
		'rule'  => __( 'Personal auto policies must include an appraisal provision for disputes only about the amount of loss (policies issued or renewed from January 1, 2026). The Texas Department of Insurance sets the timing by rule.', 'myautotriage' ),
		'cite'  => __( 'Tex. Ins. Code ch. 1813 (S.B. 458, 2025)', 'myautotriage' ),
	),
	array(
		'state' => 'Washington',
		'rule'  => __( 'Auto policies with physical damage coverage must include appraisal (issued or renewed from January 1, 2026). Each side names a disinterested appraiser within 10 days of the written demand; if the appraisers can\'t pick an umpire within 15 days, the Insurance Commissioner names one. Appraisals are due within 30 days. Each side pays its appraiser and half the umpire.', 'myautotriage' ),
		'cite'  => __( 'RCW 48.18.620', 'myautotriage' ),
	),
	array(
		'state' => 'Oregon',
		'rule'  => __( 'When the policy has an appraisal clause, the appraiser must hold an Oregon vehicle appraiser certificate (or another state\'s licence), and the insurer must repay your reasonable appraisal costs if the final award is higher than its last offer.', 'myautotriage' ),
		'cite'  => __( 'ORS 742.466', 'myautotriage' ),
	),
	array(
		'state' => 'Rhode Island',
		'rule'  => __( 'An insured or a claimant can demand independent appraisal of a damaged vehicle. The insurer\'s appraiser must inspect within 4 business days; the umpire decides within 5 days. If you start it and the award is more than 25% above the insurer\'s first offer, the insurer pays all your appraisal costs; otherwise the umpire cost is split.', 'myautotriage' ),
		'cite'  => __( 'R.I. Gen. Laws § 27-10.4-1', 'myautotriage' ),
	),
	array(
		'state' => 'Illinois',
		'rule'  => __( 'From July 1, 2027, auto policies with physical damage coverage must include appraisal, and you can invoke it without the insurer\'s consent. Until then, it depends on your policy.', 'myautotriage' ),
		'cite'  => __( '215 ILCS 5/398 (Public Act 104-0767)', 'myautotriage' ),
	),
);

$mat_demand = __( "[Your name]\n[Policy number] / [Claim number]\n[Date]\n\nTo [insurer name], claims department:\n\nWe have not been able to agree on the amount of loss on this claim. Your offer is $[their amount]; my estimate is $[your amount], supported by the enclosed [estimate / comparable vehicles / appraisal].\n\nUnder the appraisal provision of my policy, I am demanding an appraisal of the amount of loss. My appraiser is [name, company, phone, email]. Please name your appraiser in writing within [number of days in your policy or state law, for example 20] days.\n\nThis demand is only about the amount of loss. I am not waiving any other rights under the policy or the law.\n\n[Signature]", 'myautotriage' );
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'You and your own insurer agree the damage is covered but can\'t agree on the number. The appraisal clause hands that one question to two appraisers and, if needed, an umpire.', 'myautotriage' ); ?></p>
		</div>

		<div class="mat-short-answer">
			<p class="mat-short-answer__label"><?php esc_html_e( 'Short answer', 'myautotriage' ); ?></p>
			<p><?php esc_html_e( 'Most auto policies let either side demand appraisal in writing when you disagree on the amount of loss, such as the repair cost or a total loss value. Each side hires its own appraiser and pays for them; the two pick an umpire and split that cost; any two of the three set the binding amount. It works only under your own policy and only for the amount, not for whether something is covered.', 'myautotriage' ); ?></p>
		</div>

		<div class="mat-page__content">
			<?php the_content(); ?>

			<h2><?php esc_html_e( 'When appraisal fits, and when it doesn\'t', 'myautotriage' ); ?></h2>
			<div class="mat-table-wrap">
				<table class="mat-table">
					<thead><tr><th><?php esc_html_e( 'Situation', 'myautotriage' ); ?></th><th><?php esc_html_e( 'Appraisal?', 'myautotriage' ); ?></th></tr></thead>
					<tbody>
						<tr><td><?php esc_html_e( 'Your insurer\'s total loss value is too low (collision or comprehensive claim)', 'myautotriage' ); ?></td><td><?php esc_html_e( 'Yes, the most common use', 'myautotriage' ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Your insurer\'s repair estimate is lower than the shop\'s', 'myautotriage' ); ?></td><td><?php esc_html_e( 'Yes, if the disagreement is about cost', 'myautotriage' ); ?></td></tr>
						<tr><td><?php esc_html_e( 'The insurer says the damage isn\'t covered, or denies the claim', 'myautotriage' ); ?></td><td><?php esc_html_e( 'No: that is a coverage dispute. Appeal in writing, complain to your state insurance department, or see a lawyer', 'myautotriage' ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Claim against the other driver\'s insurer', 'myautotriage' ); ?></td><td><?php esc_html_e( 'No, except in Rhode Island: their policy\'s clause isn\'t yours. Use a demand letter, a complaint, or small claims court', 'myautotriage' ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Diminished value under your own policy', 'myautotriage' ); ?></td><td><?php esc_html_e( 'Only if your state and policy allow first-party diminished value; many policies exclude it', 'myautotriage' ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Dispute about who was at fault', 'myautotriage' ); ?></td><td><?php esc_html_e( 'No', 'myautotriage' ); ?></td></tr>
					</tbody>
				</table>
			</div>

			<h2><?php esc_html_e( 'How the clause usually reads', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'The widely used standard personal auto policy says, in short: if you and the insurer don\'t agree on the amount of loss, either may demand appraisal; each picks a competent, independent appraiser; the two appraisers pick an umpire; the appraisers state the actual cash value and the amount of loss separately and send any differences to the umpire; a decision agreed by any two is binding; each side pays its own appraiser and shares the umpire\'s and other appraisal costs equally; and the insurer doesn\'t give up any rights by agreeing to appraisal.', 'myautotriage' ); ?></p>
			<p><?php esc_html_e( 'Insurers write their own versions, and some policies have no appraisal clause at all or make it non-binding. Find the "Appraisal" heading in the physical damage part of your policy (often Part D) before you send anything.', 'myautotriage' ); ?></p>

			<h2><?php esc_html_e( 'How to invoke it, step by step', 'myautotriage' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'Put your number in writing first: a repair estimate, or comparable cars for sale for a total loss, and ask the adjuster to explain theirs. Many disputes settle here.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Read the clause for deadlines (some require the demand within a set time after proof of loss) and for how appraisers are chosen.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Hire an appraiser who has no stake in the result. Ask for a flat fee in writing: a fee that grows with the award can get the appraiser challenged as not independent.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Send the written demand (below) by email and a trackable method, and log the date in your claim diary.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'The two appraisers compare figures. If they agree, that is the amount. If not, they choose an umpire, and whichever figure the umpire agrees with (or a figure two of them sign) is binding.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'The insurer pays the award under the policy, less your deductible and any amount already paid.', 'myautotriage' ); ?></li>
			</ol>

			<h2><?php esc_html_e( 'Appraisal demand to copy', 'myautotriage' ); ?></h2>
			<?php mat_copy_block( 'mat-appraisal-demand', $mat_demand ); ?>
			<p class="mat-field__hint"><?php esc_html_e( 'The demand letter generator can also write this for you: choose "Invoke the appraisal clause".', 'myautotriage' ); ?></p>

			<h2><?php esc_html_e( 'What it costs', 'myautotriage' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Your appraiser: you pay, under most clauses. Get a written quote before you start; fees vary by appraiser and vehicle.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'The umpire: usually split 50/50, and only needed if the two appraisers can\'t agree.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Oregon and Rhode Island make the insurer repay your appraisal costs when the award beats its offer by enough (see the table below).', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Worth it when the gap is clearly larger than the fee. For a gap of a few hundred dollars, a written counter-offer and a complaint to your state insurance department usually cost less.', 'myautotriage' ); ?></li>
			</ul>

			<h2><?php esc_html_e( 'States with their own appraisal rules', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'In other states, your policy\'s wording decides whether appraisal exists and how it works.', 'myautotriage' ); ?></p>
			<div class="mat-table-wrap">
				<table class="mat-table">
					<thead><tr><th><?php esc_html_e( 'State', 'myautotriage' ); ?></th><th><?php esc_html_e( 'Rule', 'myautotriage' ); ?></th><th><?php esc_html_e( 'Law', 'myautotriage' ); ?></th></tr></thead>
					<tbody>
						<?php foreach ( $mat_states as $row ) : ?>
							<tr><td><?php echo esc_html( $row['state'] ); ?></td><td><?php echo esc_html( $row['rule'] ); ?></td><td><?php echo esc_html( $row['cite'] ); ?></td></tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<h2><?php esc_html_e( 'If your policy has no appraisal clause', 'myautotriage' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Send a written counter-offer with evidence and ask for the valuation report and every deduction in writing.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'File a complaint with your state insurance department; the insurer has to answer it in writing.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'For smaller amounts, small claims court; for an unreasonable refusal to pay, talk to a lawyer about bad faith.', 'myautotriage' ); ?></li>
			</ul>
		</div>

		<?php mat_tool_disclaimer( '', 'page' ); ?>

		<?php
		$mat_next = array(
			'demand-letter-generator'          => __( 'Write the appraisal demand with the letter generator', 'myautotriage' ),
			'total-loss-valuation-checker'     => __( 'Check the total loss valuation report first', 'myautotriage' ),
			'claim-diary'                      => __( 'Log the demand and every reply in a claim diary', 'myautotriage' ),
			MAT_STATE_HUB_SLUG                 => __( 'Your state\'s claim deadlines and complaint office', 'myautotriage' ),
		);
		$mat_links = array();
		foreach ( $mat_next as $slug => $label ) {
			$url = mat_url_for_slug( $slug );
			if ( $url ) {
				$mat_links[] = '<li><a href="' . esc_url( 'demand-letter-generator' === $slug ? add_query_arg( 'type', 'appraisal', $url ) : $url ) . '">' . esc_html( $label ) . '</a></li>';
			}
		}
		if ( $mat_links ) :
			?>
			<section class="mat-page__content">
				<h2><?php esc_html_e( 'What to do next', 'myautotriage' ); ?></h2>
				<ul><?php echo implode( '', $mat_links ); // phpcs:ignore -- escaped above ?></ul>
			</section>
		<?php endif; ?>

		<?php
		mat_faq_block( array(
			array(
				'question' => __( 'Is an appraisal award binding?', 'myautotriage' ),
				'answer'   => __( 'Under most clauses, yes, on the amount of loss, once two of the three (the two appraisers, or one appraiser and the umpire) agree. It can usually be challenged only for things like fraud, a biased appraiser or a decision outside the question of amount.', 'myautotriage' ),
			),
			array(
				'question' => __( 'Can the insurer refuse appraisal?', 'myautotriage' ),
				'answer'   => __( 'If your policy has the clause and the dispute is about the amount of loss, the insurer generally has to take part. It can argue the dispute is really about coverage. Texas, Washington and, from July 2027, Illinois require the clause in auto policies.', 'myautotriage' ),
			),
			array(
				'question' => __( 'How long does an auto appraisal take?', 'myautotriage' ),
				'answer'   => __( 'Often a few weeks. Washington gives each side 10 days to name an appraiser and 30 days to finish; Rhode Island requires the insurer\'s inspection within 4 business days. Elsewhere the timing is in your policy.', 'myautotriage' ),
			),
			array(
				'question' => __( 'Can I use appraisal against the other driver\'s insurer?', 'myautotriage' ),
				'answer'   => __( 'Generally no, because the clause is a contract between that insurer and its own customer. Rhode Island is the exception: its law gives claimants the right too. Elsewhere, use a demand letter, a complaint to the insurance department or small claims court.', 'myautotriage' ),
			),
		) );

		mat_render_sources( array(
			array( 'label' => __( 'Washington: RCW 48.18.620, automobile insurance appraisal clause', 'myautotriage' ), 'url' => 'https://app.leg.wa.gov/RCW/default.aspx?cite=48.18.620' ),
			array( 'label' => __( 'Rhode Island: R.I. Gen. Laws § 27-10.4-1', 'myautotriage' ), 'url' => 'https://webserver.rilegislature.gov/Statutes/TITLE27/27-10.4/27-10.4-1.htm' ),
			array( 'label' => __( 'Texas: Insurance Code chapter 1813 (S.B. 458, 89th Legislature)', 'myautotriage' ), 'url' => 'https://statutes.capitol.texas.gov/Docs/IN/htm/IN.1813.htm' ),
			array( 'label' => __( 'Oregon: ORS 742.466', 'myautotriage' ), 'url' => 'https://oregon.public.law/statutes/ors_742.466' ),
			array( 'label' => __( 'Illinois: Public Act 104-0767 (215 ILCS 5/398)', 'myautotriage' ), 'url' => 'https://ilga.gov/Legislation/PublicActs/View/104-0767' ),
		) );
		?>
	</div>
</div>
<?php
get_footer();
