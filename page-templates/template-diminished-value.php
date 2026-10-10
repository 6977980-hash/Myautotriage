<?php
/**
 * Template Name: Tool - Diminished Value Calculator
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-dv', MAT_URI . '/assets/js/calculators/diminished-value.js', array( 'mat-tools' ), MAT_VERSION, true );
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( "Estimate what your car's resale value dropped after an accident, using the industry-standard 17c formula — the same starting point insurers and independent appraisers use.", 'myautotriage' ); ?></p>
		</div>

		<?php
		$dv_letter_url = mat_url_for_slug( 'diminished-value-demand-letter-generator' );
		if ( ! $dv_letter_url && mat_url_for_slug( 'demand-letter-generator' ) ) {
			$dv_letter_url = add_query_arg( 'type', 'diminished-value', mat_url_for_slug( 'demand-letter-generator' ) );
		}
		?>
		<form id="mat-dv-form" class="mat-tool-panel" data-letter-url="<?php echo esc_url( $dv_letter_url ); ?>" novalidate>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-dv-value"><?php esc_html_e( 'Pre-accident market value (USD)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-dv-value" min="1" step="1" placeholder="e.g. 22000" required>
					<span class="mat-field__hint"><?php esc_html_e( 'Use a KBB, Edmunds, or NADA private-party value from just before the accident.', 'myautotriage' ); ?></span>
				</div>
				<div class="mat-field">
					<label for="mat-dv-mileage"><?php esc_html_e( 'Current odometer reading (miles)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-dv-mileage" min="0" step="1" placeholder="e.g. 38000" required>
				</div>
			</div>

			<div class="mat-field">
				<label for="mat-dv-damage"><?php esc_html_e( 'Damage severity', 'myautotriage' ); ?></label>
				<select id="mat-dv-damage">
					<option value="severe"><?php esc_html_e( 'Severe structural / frame damage', 'myautotriage' ); ?></option>
					<option value="major"><?php esc_html_e( 'Major damage to structure and panels', 'myautotriage' ); ?></option>
					<option value="moderate" selected><?php esc_html_e( 'Moderate damage to structure and panels', 'myautotriage' ); ?></option>
					<option value="minor"><?php esc_html_e( 'Minor damage, panels only (no structural damage)', 'myautotriage' ); ?></option>
					<option value="cosmetic"><?php esc_html_e( 'Cosmetic only (bumper, paint, minor panel)', 'myautotriage' ); ?></option>
				</select>
				<span class="mat-field__hint"><?php esc_html_e( 'Base this on the body shop or adjuster damage report, not how it feels to drive.', 'myautotriage' ); ?></span>
			</div>

			<div class="mat-tool-actions">
				<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Calculate diminished value', 'myautotriage' ); ?></button>
			</div>
		</form>

		<div id="mat-dv-result" class="mat-result-box" hidden></div>

		<?php if ( $dv_letter_url ) : ?>
			<div class="mat-cta">
				<p class="mat-cta__title"><?php esc_html_e( 'Got your number? Put it in writing.', 'myautotriage' ); ?></p>
				<a class="mat-btn mat-btn--accent" href="<?php echo esc_url( $dv_letter_url ); ?>"><?php esc_html_e( 'Generate a diminished value demand letter', 'myautotriage' ); ?></a>
			</div>
		<?php endif; ?>

		<?php mat_tool_disclaimer( __( 'Some states restrict or prohibit first-party diminished value claims against your own insurer — check your state before filing.', 'myautotriage' ) ); ?>

		<div class="mat-page__content">
			<?php the_content(); ?>
		</div>

		<?php mat_tool_extras( 'diminished-value-calculator' ); ?>

		<?php
		mat_faq_block( array_merge( array(
			array(
				'question' => __( 'What is the 17c formula?', 'myautotriage' ),
				'answer'   => __( "The 17c formula comes from Mabry v. State Farm (Georgia, 2001) and estimates diminished value as 10% of the vehicle's value, adjusted down by a damage-severity multiplier and a mileage multiplier. It's widely used by insurers as a starting baseline.", 'myautotriage' ),
			),
			array(
				'question' => __( 'Is the 17c number the most I can claim?', 'myautotriage' ),
				'answer'   => __( 'No. It is a conservative baseline. An independent appraisal that looks at actual comparable sales of similar vehicles with and without accident history often supports a higher number, especially on newer or low-mileage vehicles.', 'myautotriage' ),
			),
			array(
				'question' => __( 'Can I claim diminished value from my own insurance company?', 'myautotriage' ),
				'answer'   => __( "It depends on your state and your policy. Some states allow first-party diminished value claims, others only allow you to claim it from the at-fault driver's insurer (a third-party claim), and a few restrict it entirely.", 'myautotriage' ),
			),
		), mat_tool_extra_faqs( 'diminished-value-calculator' ) ) );
		?>
	</div>
</div>
<?php
get_footer();
