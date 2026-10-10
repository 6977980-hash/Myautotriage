<?php
/**
 * Template Name: Tool - GAP Insurance Shortfall Calculator
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-gap', MAT_URI . '/assets/js/calculators/gap-shortfall.js', array( 'mat-tools' ), MAT_VERSION, true );
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( "Find the gap between what you still owe on your car and what your insurer is offering — and whether GAP coverage should close it.", 'myautotriage' ); ?></p>
		</div>

		<form id="mat-gap-form" class="mat-tool-panel" novalidate>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-gap-payoff"><?php esc_html_e( 'Remaining loan or lease payoff (USD)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-gap-payoff" min="1" step="1" placeholder="e.g. 21000" required>
				</div>
				<div class="mat-field">
					<label for="mat-gap-acv"><?php esc_html_e( "Insurer's actual cash value settlement (USD)", 'myautotriage' ); ?></label>
					<input type="number" id="mat-gap-acv" min="0" step="1" placeholder="e.g. 16500" required>
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-gap-deductible"><?php esc_html_e( 'Your comprehensive/collision deductible (USD)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-gap-deductible" min="0" step="1" placeholder="e.g. 500">
				</div>
				<div class="mat-field">
					<label for="mat-gap-has-gap"><?php esc_html_e( 'Do you have GAP coverage?', 'myautotriage' ); ?></label>
					<select id="mat-gap-has-gap">
						<option value="yes"><?php esc_html_e( 'Yes', 'myautotriage' ); ?></option>
						<option value="no"><?php esc_html_e( 'No', 'myautotriage' ); ?></option>
						<option value="unsure"><?php esc_html_e( 'Not sure', 'myautotriage' ); ?></option>
					</select>
				</div>
			</div>
			<details class="mat-more-fields">
				<summary><?php esc_html_e( 'Why GAP might not pay it all (optional details)', 'myautotriage' ); ?></summary>
				<p class="mat-field__hint"><?php esc_html_e( 'Fill in what you know from your loan statement and GAP contract to see what GAP should pay and what may be left for you.', 'myautotriage' ); ?></p>
				<div class="mat-field-row">
					<div class="mat-field">
						<label for="mat-gap-pastdue"><?php esc_html_e( 'Past-due payments and late fees (USD)', 'myautotriage' ); ?></label>
						<input type="number" id="mat-gap-pastdue" min="0" step="1" placeholder="e.g. 0">
					</div>
					<div class="mat-field">
						<label for="mat-gap-addons"><?php esc_html_e( 'Refunds due on add-ons in the loan (USD)', 'myautotriage' ); ?></label>
						<input type="number" id="mat-gap-addons" min="0" step="1" placeholder="e.g. 900">
						<span class="mat-field__hint"><?php esc_html_e( 'Extended warranty, service contract or credit insurance financed in the loan. GAP subtracts the refund you get when they are cancelled.', 'myautotriage' ); ?></span>
					</div>
				</div>
				<div class="mat-field-row">
					<div class="mat-field">
						<label for="mat-gap-ltv"><?php esc_html_e( 'GAP limit as % of car value (LTV cap)', 'myautotriage' ); ?></label>
						<input type="number" id="mat-gap-ltv" min="100" max="200" step="1" placeholder="e.g. 125">
						<span class="mat-field__hint"><?php esc_html_e( 'Many GAP contracts only cover a loan up to 125% to 150% of the car\'s value. Leave blank if yours has no cap.', 'myautotriage' ); ?></span>
					</div>
					<div class="mat-field">
						<label for="mat-gap-dedcap"><?php esc_html_e( 'Deductible GAP will cover, up to (USD)', 'myautotriage' ); ?></label>
						<input type="number" id="mat-gap-dedcap" min="0" step="1" placeholder="e.g. 0">
						<span class="mat-field__hint"><?php esc_html_e( 'Enter 0 if your GAP contract does not cover the deductible.', 'myautotriage' ); ?></span>
					</div>
				</div>
			</details>
			<div class="mat-tool-actions">
				<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Calculate my shortfall', 'myautotriage' ); ?></button>
			</div>
		</form>

		<div id="mat-gap-result" class="mat-result-box" hidden></div>

		<?php mat_tool_disclaimer( __( 'GAP contracts vary by lender and provider — always check your specific contract\'s exclusions.', 'myautotriage' ) ); ?>

		<div class="mat-page__content"><?php the_content(); ?></div>

		<?php mat_tool_extras( 'gap-insurance-shortfall-calculator' ); ?>

		<?php
		mat_faq_block( array_merge( array(
			array(
				'question' => __( 'Does GAP insurance cover my deductible too?', 'myautotriage' ),
				'answer'   => __( 'Some GAP policies cover a portion of your deductible (often capped around $500), most do not. Check your specific GAP contract or ask your GAP provider directly.', 'myautotriage' ),
			),
			array(
				'question' => __( "What if I don't have GAP insurance and there's a shortfall?", 'myautotriage' ),
				'answer'   => __( "You're typically still responsible for the remaining loan balance to your lender, even though the car is gone. Some lenders will negotiate a payment plan — contact them as soon as you know the settlement amount.", 'myautotriage' ),
			),
		), mat_tool_extra_faqs( 'gap-insurance-shortfall-calculator' ) ) );
		?>
	</div>
</div>
<?php
get_footer();
