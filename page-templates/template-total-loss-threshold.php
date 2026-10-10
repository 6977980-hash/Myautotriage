<?php
/**
 * Template Name: Tool - Total Loss Threshold Calculator
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-tlt', MAT_URI . '/assets/js/calculators/total-loss-threshold.js', array( 'mat-tools' ), MAT_VERSION, true );
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( "See whether your car is likely to be declared a total loss, using your state's actual rule: a percentage threshold, the Total Loss Formula, or (where state law sets no number) the test most insurers use.", 'myautotriage' ); ?></p>
		</div>

		<form id="mat-tlt-form" class="mat-tool-panel" data-json="<?php echo esc_url( MAT_URI . '/assets/js/data/total-loss-thresholds.json' ); ?>" data-hub-url="<?php echo esc_url( get_page_by_path( MAT_STATE_HUB_SLUG ) ? trailingslashit( mat_state_hub_url() ) : '' ); ?>" data-gap-url="<?php echo esc_url( mat_url_for_slug( 'gap-insurance-shortfall-calculator' ) ); ?>" novalidate>
			<div class="mat-field">
				<label for="mat-tlt-state"><?php esc_html_e( 'Your state', 'myautotriage' ); ?></label>
				<select id="mat-tlt-state" required>
					<option value=""><?php esc_html_e( 'Loading states…', 'myautotriage' ); ?></option>
				</select>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-tlt-acv"><?php esc_html_e( 'Actual cash value before the accident (USD)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-tlt-acv" min="1" step="1" placeholder="e.g. 18000" required>
				</div>
				<div class="mat-field">
					<label for="mat-tlt-repair"><?php esc_html_e( 'Estimated repair cost (USD)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-tlt-repair" min="0" step="1" placeholder="e.g. 14000" required>
				</div>
			</div>
			<div class="mat-field">
				<label for="mat-tlt-salvage"><?php esc_html_e( 'Estimated salvage value (USD) — optional', 'myautotriage' ); ?></label>
				<input type="number" id="mat-tlt-salvage" min="0" step="1" placeholder="<?php esc_attr_e( 'Leave blank to use a default estimate', 'myautotriage' ); ?>">
				<span class="mat-field__hint"><?php esc_html_e( 'Only used in Total Loss Formula states. If unsure, leave blank — we\'ll estimate around 18% of the vehicle value.', 'myautotriage' ); ?></span>
			</div>
			<div class="mat-tool-actions">
				<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Check total loss status', 'myautotriage' ); ?></button>
			</div>
		</form>

		<div id="mat-tlt-result" class="mat-result-box" hidden></div>

		<?php mat_tool_disclaimer( __( 'Insurers use their own valuation software and repair estimate, which can differ from the numbers you enter here.', 'myautotriage' ) ); ?>

		<div class="mat-page__content"><?php the_content(); ?></div>

		<?php mat_tool_extras( 'total-loss-threshold-calculator' ); ?>

		<?php
		mat_faq_block( array_merge( array(
			array(
				'question' => __( "What's the difference between a percentage threshold and the Total Loss Formula?", 'myautotriage' ),
				'answer'   => __( 'A percentage-threshold state totals a car once repairs reach a set percentage of its value (for example 75%). A Total Loss Formula (TLF) state instead adds the repair cost to the estimated salvage value and totals the car if that combined figure meets or exceeds the full value. Where state law sets no number, the insurer decides when repairs are uneconomical, and most insurers use the Total Loss Formula.', 'myautotriage' ),
			),
			array(
				'question' => __( 'Can I ask my insurer to repair a car they want to total, or vice versa?', 'myautotriage' ),
				'answer'   => __( 'In most states you can push back on the valuation or the repair estimate, but the insurer generally decides whether to total a car based on the numbers. An independent appraisal is the usual way to challenge that decision.', 'myautotriage' ),
			),
		), mat_tool_extra_faqs( 'total-loss-threshold-calculator' ) ) );
		?>
	</div>
</div>
<?php
get_footer();
