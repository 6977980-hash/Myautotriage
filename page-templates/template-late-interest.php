<?php
/**
 * Template Name: Tool - Late Claim Payment Interest Calculator
 *
 * Simple interest on a claim the insurer paid late, with the statutory
 * rates we have verified (Texas, Michigan) and a custom rate for others.
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-li', MAT_URI . '/assets/js/calculators/late-interest.js', array( 'mat-tools' ), MAT_VERSION, true );
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'Insurer paid your claim late? Estimate the interest some state laws add to a late claim payment, and the total to ask for.', 'myautotriage' ); ?></p>
		</div>

		<form id="mat-li-form" class="mat-tool-panel" data-deadline-url="<?php echo esc_url( mat_url_for_slug( 'claim-payment-deadline-by-state' ) ); ?>" novalidate>
			<div class="mat-field">
				<label for="mat-li-rule"><?php esc_html_e( 'State rule', 'myautotriage' ); ?></label>
				<select id="mat-li-rule">
					<option value="TX"><?php esc_html_e( 'Texas: 18% a year (Tex. Ins. Code § 542.060)', 'myautotriage' ); ?></option>
					<option value="MI"><?php esc_html_e( 'Michigan: 12% a year (MCL § 500.2006)', 'myautotriage' ); ?></option>
					<option value="other" selected><?php esc_html_e( 'Another state: I\'ll enter the rate', 'myautotriage' ); ?></option>
				</select>
				<span class="mat-field__hint"><?php esc_html_e( 'Many states charge interest or penalties on late claim payments at different rates. Your state insurance department or the statute named on our state page will say which.', 'myautotriage' ); ?></span>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-li-amount"><?php esc_html_e( 'Claim amount paid late (USD)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-li-amount" min="1" step="0.01" placeholder="e.g. 8500" required>
				</div>
				<div class="mat-field">
					<label for="mat-li-rate"><?php esc_html_e( 'Interest rate (% a year)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-li-rate" min="0" max="50" step="0.01" placeholder="e.g. 10">
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-li-due"><?php esc_html_e( 'Date payment was due', 'myautotriage' ); ?></label>
					<input type="date" id="mat-li-due" required>
					<span class="mat-field__hint" id="mat-li-due-hint"><?php esc_html_e( 'The statutory deadline; our claim deadline lookup can work it out.', 'myautotriage' ); ?></span>
				</div>
				<div class="mat-field">
					<label for="mat-li-paid"><?php esc_html_e( 'Date paid (leave blank if still unpaid)', 'myautotriage' ); ?></label>
					<input type="date" id="mat-li-paid">
				</div>
			</div>
			<p id="mat-li-error" class="mat-form-error" role="alert" hidden></p>
			<div class="mat-tool-actions">
				<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Calculate interest', 'myautotriage' ); ?></button>
			</div>
		</form>

		<div id="mat-li-result" class="mat-result-box" hidden></div>

		<?php mat_tool_disclaimer( __( 'Late-payment interest depends on the claim type, who is claiming and the exact statute; insurers often dispute when the clock started. This is an estimate of simple interest, not a legal calculation.', 'myautotriage' ) ); ?>

		<div class="mat-page__content">
			<?php the_content(); ?>
			<h2><?php esc_html_e( 'The two rules built in', 'myautotriage' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Texas: an insurer that misses a prompt-payment deadline owes the claim plus interest at 18% a year as damages, and reasonable attorney\'s fees (Tex. Ins. Code § 542.060). This applies to claims by the policyholder or a beneficiary under the policy. Weather-related property claims under Chapter 542A use a lower rate.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Michigan: benefits not paid on time bear simple interest at 12% a year from 60 days after the insurer received satisfactory proof of loss (MCL § 500.2006). A third-party claimant gets it only when liability is not reasonably in dispute and a court finds the refusal was in bad faith.', 'myautotriage' ); ?></li>
			</ul>
			<h2><?php esc_html_e( 'How to ask for it', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'Write to the adjuster, cite the statute, give the date payment was due and the interest you calculated, and ask for it to be paid with the claim. If the insurer refuses, include it in a complaint to your state department of insurance.', 'myautotriage' ); ?></p>
		</div>

		<?php mat_tool_extras( 'insurance-late-payment-interest-calculator' ); ?>

		<?php
		mat_faq_block( array_merge( array(
			array(
				'question' => __( 'Does every state charge interest on late insurance payments?', 'myautotriage' ),
				'answer'   => __( 'Many do, but the rate, the start date and who can claim it differ a lot. Some states use a fixed rate, some tie it to the court judgment rate, and some only allow it after a lawsuit. Check your state statute before you claim a number.', 'myautotriage' ),
			),
			array(
				'question' => __( 'Is late-payment interest taxable?', 'myautotriage' ),
				'answer'   => __( 'Interest paid on a claim is generally treated as interest income for tax purposes, unlike the claim payment itself. Ask a tax professional if the amount is significant.', 'myautotriage' ),
			),
		), mat_tool_extra_faqs( 'insurance-late-payment-interest-calculator' ) ) );
		?>
	</div>
</div>
<?php
get_footer();
