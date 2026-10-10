<?php
/**
 * Template Name: Tool - Car Insurance Refund Calculator
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-rf', MAT_URI . '/assets/js/calculators/refund.js', array( 'mat-tools' ), MAT_VERSION, true );
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'Estimate how much of your prepaid car insurance premium you should get back when you cancel early, with a pro-rata or short-rate cancellation.', 'myautotriage' ); ?></p>
		</div>

		<form id="mat-rf-form" class="mat-tool-panel" novalidate>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-rf-premium"><?php esc_html_e( 'Premium you paid for the term (USD)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-rf-premium" min="1" step="0.01" placeholder="e.g. 1200" required>
					<span class="mat-field__hint"><?php esc_html_e( 'The full amount for the policy term, from your declarations page or receipt.', 'myautotriage' ); ?></span>
				</div>
				<div class="mat-field">
					<label for="mat-rf-term"><?php esc_html_e( 'Policy term', 'myautotriage' ); ?></label>
					<select id="mat-rf-term">
						<option value="6"><?php esc_html_e( '6 months', 'myautotriage' ); ?></option>
						<option value="12" selected><?php esc_html_e( '12 months', 'myautotriage' ); ?></option>
					</select>
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-rf-start"><?php esc_html_e( 'Policy start date', 'myautotriage' ); ?></label>
					<input type="date" id="mat-rf-start" required>
				</div>
				<div class="mat-field">
					<label for="mat-rf-cancel"><?php esc_html_e( 'Cancellation date', 'myautotriage' ); ?></label>
					<input type="date" id="mat-rf-cancel" required>
				</div>
			</div>
			<div class="mat-field">
				<label for="mat-rf-method"><?php esc_html_e( 'How your insurer calculates refunds', 'myautotriage' ); ?></label>
				<select id="mat-rf-method">
					<option value="prorata" selected><?php esc_html_e( 'Pro-rata (full unused premium back)', 'myautotriage' ); ?></option>
					<option value="shortrate"><?php esc_html_e( 'Short-rate (unused premium minus a penalty)', 'myautotriage' ); ?></option>
					<option value="both"><?php esc_html_e( "I don't know, show both", 'myautotriage' ); ?></option>
				</select>
				<span class="mat-field__hint"><?php esc_html_e( 'Your policy\'s cancellation section says which. If the insurer cancels the policy, refunds are normally pro-rata.', 'myautotriage' ); ?></span>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-rf-penalty"><?php esc_html_e( 'Short-rate penalty (% of unused premium)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-rf-penalty" min="0" max="50" step="1" value="10">
				</div>
				<div class="mat-field">
					<label for="mat-rf-fee"><?php esc_html_e( 'Flat cancellation fee, if any (USD)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-rf-fee" min="0" step="1" value="0">
				</div>
			</div>
			<div class="mat-tool-actions">
				<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Calculate my refund', 'myautotriage' ); ?></button>
			</div>
		</form>

		<div id="mat-rf-result" class="mat-result-box" hidden></div>

		<?php mat_tool_disclaimer( __( 'Cancellation rules, short-rate tables and fees differ by insurer and state. Ask your insurer for the exact refund amount and method in writing before you cancel.', 'myautotriage' ) ); ?>

		<div class="mat-page__content">
			<?php the_content(); ?>
			<h2><?php esc_html_e( 'Pro-rata vs. short-rate cancellation', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'A pro-rata refund returns the full unused part of your premium: if you cancel a $1,200 annual policy exactly halfway through, you get $600 back. A short-rate refund keeps a penalty on top of the premium you used, often around 10% of the unused premium, so the same cancellation returns about $540.', 'myautotriage' ); ?></p>
			<p><?php esc_html_e( 'Which one applies is set by your policy and state rules. Short-rate usually only applies when you cancel; when the insurer cancels or non-renews, the refund is normally pro-rata.', 'myautotriage' ); ?></p>
			<h2><?php esc_html_e( 'If you pay monthly', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'With monthly payments you have usually prepaid only the current month, so the refund is the unused days of that month, minus any fee. Enter that month\'s payment as the premium and use the billing period dates instead of the full term.', 'myautotriage' ); ?></p>
			<h2><?php esc_html_e( 'Before you cancel', 'myautotriage' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Have the new policy start on the same day the old one ends. A gap in coverage can raise future rates and, in most states, driving uninsured is illegal.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'If you have a car loan or lease, the lender requires continuous coverage and may need proof of the new policy.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Get the cancellation date and refund amount confirmed in writing; that date is what the refund is calculated from.', 'myautotriage' ); ?></li>
			</ul>
		</div>

		<?php mat_tool_extras( 'car-insurance-refund-calculator' ); ?>

		<?php
		mat_faq_block( array_merge( array(
			array(
				'question' => __( 'How long does a car insurance refund take?', 'myautotriage' ),
				'answer'   => __( 'Many insurers send refunds within a few weeks of cancellation, and some states set a maximum number of days. If yours is late, ask in writing for the date it was issued and the amount.', 'myautotriage' ),
			),
			array(
				'question' => __( 'Do I get a refund if I sell my car mid-policy?', 'myautotriage' ),
				'answer'   => __( 'Yes, if you cancel the policy (or remove that car) you are normally owed the unused premium for it, calculated from the cancellation or removal date.', 'myautotriage' ),
			),
			array(
				'question' => __( 'Can I get a refund after a total loss?', 'myautotriage' ),
				'answer'   => __( 'Often yes for the parts of coverage you no longer need once the car is gone. Ask the insurer to remove the vehicle as of the loss date and refund the unused premium; practices differ, especially for collision and comprehensive.', 'myautotriage' ),
			),
		), mat_tool_extra_faqs( 'car-insurance-refund-calculator' ) ) );
		?>
	</div>
</div>
<?php
get_footer();
