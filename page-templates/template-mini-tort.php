<?php
/**
 * Template Name: Tool - Michigan Mini-Tort Calculator
 *
 * How much a Michigan driver can recover from the at-fault driver for car
 * damage their own insurance didn't pay (MCL 500.3135(3)(e)).
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-mt', MAT_URI . '/assets/js/calculators/mini-tort.js', array( 'mat-tools' ), MAT_VERSION, true );
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'In Michigan you claim car damage from the at-fault driver through the "mini-tort", capped at $3,000. See how much you can ask for after what your own insurance paid.', 'myautotriage' ); ?></p>
		</div>
		<?php mat_tool_short_answer( 'michigan-mini-tort-calculator' ); ?>

		<form id="mat-mt-form" class="mat-tool-panel" data-letter-url="<?php echo esc_url( mat_url_for_slug( 'small-claims-demand-letter-generator' ) ); ?>" novalidate>
			<?php mat_noscript_note(); ?>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-mt-repair"><?php esc_html_e( 'Repair cost (or car value if totaled), USD', 'myautotriage' ); ?></label>
					<input type="number" id="mat-mt-repair" min="1" max="1000000" step="0.01" placeholder="e.g. 4200" required>
				</div>
				<div class="mat-field">
					<label for="mat-mt-collision"><?php esc_html_e( 'Did your own collision coverage pay?', 'myautotriage' ); ?></label>
					<select id="mat-mt-collision">
						<option value="yes"><?php esc_html_e( 'Yes, I paid only the deductible', 'myautotriage' ); ?></option>
						<option value="no"><?php esc_html_e( 'No, I have no collision coverage', 'myautotriage' ); ?></option>
					</select>
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field" id="mat-mt-deductible-field">
					<label for="mat-mt-deductible"><?php esc_html_e( 'Your collision deductible (USD)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-mt-deductible" min="0" max="100000" step="0.01" placeholder="e.g. 500">
				</div>
				<div class="mat-field">
					<label for="mat-mt-fault"><?php esc_html_e( 'Your share of the fault (%)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-mt-fault" min="0" max="100" step="1" value="0">
					<span class="mat-field__hint"><?php esc_html_e( 'Above 50% you can\'t recover anything.', 'myautotriage' ); ?></span>
				</div>
			</div>
			<div class="mat-field">
				<label for="mat-mt-insured"><?php esc_html_e( 'Was your car insured (no-fault PIP/PPI in force) at the time?', 'myautotriage' ); ?></label>
				<select id="mat-mt-insured">
					<option value="yes"><?php esc_html_e( 'Yes', 'myautotriage' ); ?></option>
					<option value="no"><?php esc_html_e( 'No', 'myautotriage' ); ?></option>
				</select>
			</div>
			<p id="mat-mt-error" class="mat-form-error" role="alert" hidden></p>
			<div class="mat-tool-actions">
				<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Calculate my mini-tort claim', 'myautotriage' ); ?></button>
			</div>
		</form>

		<div id="mat-mt-result" class="mat-result-box" hidden></div>

		<?php mat_tool_disclaimer( __( 'The mini-tort applies to Michigan accidents. It covers damage to your vehicle only; injury claims follow different rules.', 'myautotriage' ) ); ?>

		<div class="mat-page__content">
			<?php the_content(); ?>
			<h2><?php esc_html_e( 'How the Michigan mini-tort works', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'Michigan\'s no-fault law stops most lawsuits over car damage, with one exception: you can recover up to $3,000 for damage to your vehicle "to the extent that the damages are not covered by insurance" (MCL 500.3135(3)(e), for accidents after July 1, 2020). In practice that is usually your collision deductible, or the repair bill up to $3,000 if you carry no collision coverage.', 'myautotriage' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'Damages are reduced by your share of the fault, and you get nothing if you were more than 50% at fault (MCL 500.3135(4)(a)).', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'You can\'t recover if your own car was being driven without the required no-fault insurance (MCL 500.3135(4)(e)).', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'The claim must be brought in the small claims division of the district court when possible (MCL 500.3135(4)(c)).', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Mini-tort coverage is optional for the at-fault driver, so start by writing to their insurer; if they have no such coverage, the claim is against the driver personally.', 'myautotriage' ); ?></li>
			</ul>
			<h2><?php esc_html_e( 'How to claim it', 'myautotriage' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'Get the at-fault driver\'s name and insurer from the police report (UD-10).', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Send their insurer a short letter with the repair invoice, proof of your deductible and the police report, asking for the amount below.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'If they refuse or don\'t answer, file in small claims court in the district where the accident happened or the driver lives.', 'myautotriage' ); ?></li>
			</ol>
		</div>

		<?php mat_tool_extras( 'michigan-mini-tort-calculator' ); ?>

		<?php
		mat_faq_block( array_merge( array(
			array(
				'question' => __( 'Is the Michigan mini-tort $1,000 or $3,000?', 'myautotriage' ),
				'answer'   => __( '$3,000 for accidents after July 1, 2020; it was $1,000 before. The 2019 no-fault reform raised the cap (MCL 500.3135(3)(e)).', 'myautotriage' ),
			),
			array(
				'question' => __( 'Can I use the mini-tort if I was partly at fault?', 'myautotriage' ),
				'answer'   => __( 'Yes, if you were 50% at fault or less. The amount is cut by your share: at 20% fault, a $1,000 claim becomes $800. Above 50% you recover nothing.', 'myautotriage' ),
			),
		), mat_tool_extra_faqs( 'michigan-mini-tort-calculator' ) ) );
		?>
	</div>
</div>
<?php
get_footer();
