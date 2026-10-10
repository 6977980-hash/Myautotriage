<?php
/**
 * Template Name: Tool - Total Loss Valuation Checker
 *
 * Checks the numbers in an insurer's total loss valuation report (CCC,
 * Mitchell, Audatex and similar) against the NAIC model rule most state
 * total loss regulations follow, and hands the result to the total loss
 * counter-offer letter.
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-tlv', MAT_URI . '/assets/js/calculators/valuation-checker.js', array( 'mat-tools' ), MAT_VERSION, true );

$mat_tlv_letter = mat_url_for_slug( 'demand-letter-generator' );
$mat_tlv_hub    = (bool) get_page_by_path( MAT_STATE_HUB_SLUG );
$mat_tlv_states = array();
foreach ( mat_state_laws() as $mat_tlv_state ) {
	$mat_tlv_states[ $mat_tlv_state['code'] ] = array(
		'name' => $mat_tlv_state['name'],
		'url'  => $mat_tlv_hub ? mat_state_url( $mat_tlv_state ) : '',
	);
}
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'Got a total loss valuation report that looks low? Enter its numbers and the comparable cars it used. We check them line by line, show where money was taken off, and turn the result into a counter-offer letter.', 'myautotriage' ); ?></p>
		</div>

		<form id="mat-tlv-form" class="mat-tool-panel" data-letter-url="<?php echo esc_url( $mat_tlv_letter ? add_query_arg( 'type', 'total-loss', $mat_tlv_letter ) : '' ); ?>" data-states="<?php echo esc_attr( wp_json_encode( $mat_tlv_states ) ); ?>" novalidate>
			<h2 class="mat-tool-panel__heading"><?php esc_html_e( '1. The valuation summary', 'myautotriage' ); ?></h2>
			<p class="mat-field__hint"><?php esc_html_e( 'These are usually on the first page of the report, under a heading like "Valuation summary" or "Settlement".', 'myautotriage' ); ?></p>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-tlv-base"><?php esc_html_e( 'Base or market value (USD)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-tlv-base" min="1" step="0.01" placeholder="e.g. 16400" required>
					<span class="mat-field__hint"><?php esc_html_e( 'The value before condition adjustments.', 'myautotriage' ); ?></span>
				</div>
				<div class="mat-field">
					<label for="mat-tlv-condition"><?php esc_html_e( 'Condition adjustment taken off (USD)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-tlv-condition" min="0" step="0.01" placeholder="e.g. 650">
					<span class="mat-field__hint"><?php esc_html_e( 'Enter it as a positive number. Leave blank if none.', 'myautotriage' ); ?></span>
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-tlv-other"><?php esc_html_e( 'Other deductions (prior damage, etc.)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-tlv-other" min="0" step="0.01" placeholder="e.g. 0">
				</div>
				<div class="mat-field">
					<label for="mat-tlv-deductible"><?php esc_html_e( 'Your deductible', 'myautotriage' ); ?></label>
					<input type="number" id="mat-tlv-deductible" min="0" step="0.01" placeholder="e.g. 500">
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-tlv-tax"><?php esc_html_e( 'Sales tax included in the offer', 'myautotriage' ); ?></label>
					<input type="number" id="mat-tlv-tax" min="0" step="0.01" placeholder="e.g. 0">
				</div>
				<div class="mat-field">
					<label for="mat-tlv-fees"><?php esc_html_e( 'Title and registration fees included', 'myautotriage' ); ?></label>
					<input type="number" id="mat-tlv-fees" min="0" step="0.01" placeholder="e.g. 0">
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-tlv-offer"><?php esc_html_e( 'Final amount offered to you (optional)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-tlv-offer" min="0" step="0.01" placeholder="e.g. 15250">
					<span class="mat-field__hint"><?php esc_html_e( 'We check that the report\'s numbers add up to it.', 'myautotriage' ); ?></span>
				</div>
				<div class="mat-field">
					<label for="mat-tlv-taxrate"><?php esc_html_e( 'Your local sales tax rate (%, optional)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-tlv-taxrate" min="0" max="15" step="0.001" placeholder="e.g. 7.25">
					<span class="mat-field__hint"><?php esc_html_e( 'Lets us estimate the tax a replacement car would cost you.', 'myautotriage' ); ?></span>
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-tlv-state"><?php esc_html_e( 'Your state (optional)', 'myautotriage' ); ?></label>
					<select id="mat-tlv-state">
						<option value=""><?php esc_html_e( 'Choose a state', 'myautotriage' ); ?></option>
						<?php foreach ( $mat_tlv_states as $mat_tlv_code => $mat_tlv_info ) : ?>
							<option value="<?php echo esc_attr( $mat_tlv_code ); ?>"><?php echo esc_html( $mat_tlv_info['name'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="mat-field">
					<label for="mat-tlv-mileage"><?php esc_html_e( 'Your car\'s mileage (optional)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-tlv-mileage" min="0" max="999999" step="1" placeholder="e.g. 61000">
				</div>
			</div>

			<h2 class="mat-tool-panel__heading"><?php esc_html_e( '2. The comparable cars in the report', 'myautotriage' ); ?></h2>
			<p class="mat-field__hint"><?php esc_html_e( 'Enter each comparable vehicle the report lists, up to five. Use the advertised (list) price, and put any "projected sold", "typical negotiation" or "price adjustment" the report took off that car in its own box.', 'myautotriage' ); ?></p>
			<?php for ( $mat_tlv_i = 1; $mat_tlv_i <= 5; $mat_tlv_i++ ) : ?>
				<?php if ( 3 === $mat_tlv_i ) : ?>
					<details class="mat-more-fields">
						<summary><?php esc_html_e( 'More comparables (3 to 5)', 'myautotriage' ); ?></summary>
				<?php endif; ?>
				<fieldset class="mat-tlv-comp">
					<legend><?php echo esc_html( sprintf( /* translators: %d: comparable number */ __( 'Comparable %d', 'myautotriage' ), $mat_tlv_i ) ); ?></legend>
					<div class="mat-field-row">
						<div class="mat-field">
							<label for="mat-tlv-c<?php echo (int) $mat_tlv_i; ?>-price"><?php esc_html_e( 'Advertised price', 'myautotriage' ); ?></label>
							<input type="number" id="mat-tlv-c<?php echo (int) $mat_tlv_i; ?>-price" min="1" step="0.01">
						</div>
						<div class="mat-field">
							<label for="mat-tlv-c<?php echo (int) $mat_tlv_i; ?>-adj"><?php esc_html_e( 'Projected sold / negotiation adjustment', 'myautotriage' ); ?></label>
							<input type="number" id="mat-tlv-c<?php echo (int) $mat_tlv_i; ?>-adj" min="0" step="0.01" placeholder="<?php esc_attr_e( 'Amount taken off', 'myautotriage' ); ?>">
						</div>
					</div>
					<div class="mat-field-row">
						<div class="mat-field">
							<label for="mat-tlv-c<?php echo (int) $mat_tlv_i; ?>-miles"><?php esc_html_e( 'Distance from you (miles)', 'myautotriage' ); ?></label>
							<input type="number" id="mat-tlv-c<?php echo (int) $mat_tlv_i; ?>-miles" min="0" max="5000" step="1">
						</div>
						<div class="mat-field">
							<label for="mat-tlv-c<?php echo (int) $mat_tlv_i; ?>-odo"><?php esc_html_e( 'Its mileage', 'myautotriage' ); ?></label>
							<input type="number" id="mat-tlv-c<?php echo (int) $mat_tlv_i; ?>-odo" min="0" max="999999" step="1">
						</div>
					</div>
					<div class="mat-field mat-field--check">
						<label><input type="checkbox" id="mat-tlv-c<?php echo (int) $mat_tlv_i; ?>-diff"> <?php esc_html_e( 'Different trim, options, body style or model year from my car', 'myautotriage' ); ?></label>
					</div>
				</fieldset>
				<?php if ( 5 === $mat_tlv_i ) : ?>
					</details>
				<?php endif; ?>
			<?php endfor; ?>

			<details class="mat-more-fields">
				<summary><?php esc_html_e( '3. Cars you found for sale yourself (optional, makes your counter-offer stronger)', 'myautotriage' ); ?></summary>
				<p class="mat-field__hint"><?php esc_html_e( 'Asking prices of the same make, model, year and trim with similar mileage, for sale near you now. Save each listing as a PDF or screenshot: you will enclose them with your letter.', 'myautotriage' ); ?></p>
				<div class="mat-field-row">
					<div class="mat-field">
						<label for="mat-tlv-y1"><?php esc_html_e( 'Listing 1 price', 'myautotriage' ); ?></label>
						<input type="number" id="mat-tlv-y1" min="1" step="0.01">
					</div>
					<div class="mat-field">
						<label for="mat-tlv-y2"><?php esc_html_e( 'Listing 2 price', 'myautotriage' ); ?></label>
						<input type="number" id="mat-tlv-y2" min="1" step="0.01">
					</div>
				</div>
				<div class="mat-field">
					<label for="mat-tlv-y3"><?php esc_html_e( 'Listing 3 price', 'myautotriage' ); ?></label>
					<input type="number" id="mat-tlv-y3" min="1" step="0.01">
				</div>
			</details>

			<p id="mat-tlv-error" class="mat-form-error" role="alert" hidden></p>
			<div class="mat-tool-actions">
				<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Check my valuation', 'myautotriage' ); ?></button>
			</div>
			<p class="mat-field__hint"><?php esc_html_e( 'Everything you type stays in your browser. Nothing is sent to us.', 'myautotriage' ); ?></p>
		</form>

		<div id="mat-tlv-result" class="mat-result-box mat-tlv-result" hidden></div>

		<?php mat_tool_disclaimer( __( 'This tool checks the report against the NAIC model total loss rule that many states based their regulations on. Your state\'s rule, your policy and the facts of your claim decide what you are owed. The figures are a starting point for negotiation, not a guaranteed amount.', 'myautotriage' ) ); ?>

		<div class="mat-page__content">
			<?php the_content(); ?>
			<h2><?php esc_html_e( 'What the rule says a total loss payment should cover', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'The NAIC Unfair Property/Casualty Claims Settlement Practices Model Regulation, which many state total loss rules follow, says a cash settlement for a totaled car should be based on the actual cost to buy a comparable car, less your deductible, including all applicable taxes, license fees and other fees to transfer ownership (Section 8A(2)).', 'myautotriage' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'Comparable means the same make, a similar body style, similar options and mileage, in as good or better condition.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'The cost can come from two or more comparable cars in your local market area, available now or in the last 90 days, or from nearby areas only when none are available locally.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Any deduction, including for condition, must be "measurable, discernible, itemized and specified as to dollar amount", and the basis for the settlement must be fully explained to you (Section 8A(3)).', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'If you tell the insurer within 35 days of receiving the payment that you can\'t buy a comparable car for that amount, it must reopen the claim (Section 8A(2)(e)), unless it already showed you a specific comparable car, with its VIN, that you could have bought for the value it set.', 'myautotriage' ); ?></li>
			</ul>
			<h2><?php esc_html_e( 'The adjustments people most often dispute', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'Projected sold or typical negotiation adjustments lower each comparable\'s advertised price to what the software predicts it would sell for. Class actions in several states challenge these adjustments; some courts have dismissed similar cases, and none of that changes your own claim automatically. You can still ask the insurer for the data behind each adjustment and point out that you would have to pay the advertised price to replace your car.', 'myautotriage' ); ?></p>
			<p><?php esc_html_e( 'Condition adjustments rate your car as below average. Ask how the condition was judged, who inspected it, and which photos support each rating, and send maintenance records, recent repairs and photos from before the loss.', 'myautotriage' ); ?></p>
		</div>

		<?php mat_tool_extras( 'total-loss-valuation-checker' ); ?>

		<?php
		mat_faq_block( array_merge( array(
			array(
				'question' => __( 'How do I get my total loss valuation report?', 'myautotriage' ),
				'answer'   => __( 'Ask the adjuster in writing for a complete copy of the valuation report, including every comparable vehicle and every adjustment. Insurers generally have to explain the basis of a total loss settlement, and the report is the document that does it.', 'myautotriage' ),
			),
			array(
				'question' => __( 'What is a projected sold adjustment?', 'myautotriage' ),
				'answer'   => __( 'A reduction the valuation software applies to a comparable car\'s advertised price, based on its estimate of how much buyers negotiate off dealer prices. It lowers the comparable\'s value and therefore your car\'s value. You can ask the insurer to justify it or to value the comparables at their advertised prices.', 'myautotriage' ),
			),
			array(
				'question' => __( 'Should a total loss payment include sales tax?', 'myautotriage' ),
				'answer'   => __( 'Under the NAIC model rule, a cash settlement includes all applicable taxes and the fees to transfer ownership of a comparable car. Many states follow this, but some only pay tax once you buy a replacement, and the rules for leased cars and third-party claims can differ. Check your state\'s regulation.', 'myautotriage' ),
			),
			array(
				'question' => __( 'What if the insurer won\'t change the valuation?', 'myautotriage' ),
				'answer'   => __( 'Send a written counter-offer with your evidence, then consider invoking the appraisal clause in your policy, filing a complaint with your state department of insurance, or small claims court for third-party claims within the limit.', 'myautotriage' ),
			),
		), mat_tool_extra_faqs( 'total-loss-valuation-checker' ) ) );
		?>
	</div>
</div>
<?php
get_footer();
