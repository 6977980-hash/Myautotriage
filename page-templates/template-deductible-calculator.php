<?php
/**
 * Template Name: Tool - File a Claim Break-Even Calculator
 *
 * State surcharge rules (only where a state sets them publicly) come from
 * assets/js/data/claim-surcharge-rules.json.
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-ded', MAT_URI . '/assets/js/calculators/deductible-vs-premium.js', array( 'mat-tools' ), MAT_VERSION, true );

$mat_ded_rules  = mat_read_theme_json( 'assets/js/data/claim-surcharge-rules.json' );
$mat_ded_rules  = isset( $mat_ded_rules['states'] ) ? $mat_ded_rules['states'] : array();
$mat_ded_states = array();
foreach ( mat_state_laws() as $mat_ded_state ) {
	$mat_ded_states[ $mat_ded_state['code'] ] = array_merge(
		array( 'name' => $mat_ded_state['name'] ),
		isset( $mat_ded_rules[ $mat_ded_state['code'] ] ) ? $mat_ded_rules[ $mat_ded_state['code'] ] : array()
	);
}
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'Compare paying for minor damage yourself against filing a claim, once your deductible and a likely multi-year premium increase are both counted.', 'myautotriage' ); ?></p>
		</div>
		<?php mat_tool_short_answer( 'deductible-vs-premium-calculator' ); ?>

		<form id="mat-ded-form" class="mat-tool-panel" data-states="<?php echo esc_attr( wp_json_encode( $mat_ded_states ) ); ?>" novalidate>
			<?php mat_noscript_note(); ?>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-ded-repair"><?php esc_html_e( 'Estimated repair cost (USD)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-ded-repair" min="1" step="1" placeholder="e.g. 1800" required>
				</div>
				<div class="mat-field">
					<label for="mat-ded-deductible"><?php esc_html_e( 'Your deductible (USD)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-ded-deductible" min="0" step="1" placeholder="e.g. 500" required>
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-ded-state"><?php esc_html_e( 'Your state (optional)', 'myautotriage' ); ?></label>
					<select id="mat-ded-state">
						<option value=""><?php esc_html_e( 'Choose a state', 'myautotriage' ); ?></option>
						<?php foreach ( $mat_ded_states as $mat_ded_code => $mat_ded_info ) : ?>
							<option value="<?php echo esc_attr( $mat_ded_code ); ?>"><?php echo esc_html( $mat_ded_info['name'] ); ?></option>
						<?php endforeach; ?>
					</select>
					<span class="mat-field__hint"><?php esc_html_e( 'California, Massachusetts and North Carolina set public rules for at-fault surcharges; we apply them.', 'myautotriage' ); ?></span>
				</div>
				<div class="mat-field">
					<label for="mat-ded-premium"><?php esc_html_e( 'Current annual premium (USD, optional)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-ded-premium" min="0" step="1" placeholder="e.g. 1500">
					<span class="mat-field__hint"><?php esc_html_e( 'Used to estimate the increase in North Carolina.', 'myautotriage' ); ?></span>
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-ded-surcharge"><?php esc_html_e( 'Estimated annual premium increase if you file (USD)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-ded-surcharge" min="0" step="1" placeholder="e.g. 250">
					<span class="mat-field__hint"><?php esc_html_e( 'Ask your agent: outside North Carolina, the increase depends on your insurer\'s own rating plan. In North Carolina, leave this blank and we estimate it from the state plan.', 'myautotriage' ); ?></span>
				</div>
				<div class="mat-field">
					<label for="mat-ded-years"><?php esc_html_e( 'How many years the increase typically lasts', 'myautotriage' ); ?></label>
					<input type="number" id="mat-ded-years" min="1" max="7" step="1" value="3">
				</div>
			</div>
			<div class="mat-tool-actions">
				<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Compare my options', 'myautotriage' ); ?></button>
			</div>
		</form>

		<div id="mat-ded-result" class="mat-result-box" hidden></div>

		<?php mat_tool_disclaimer( __( 'Actual surcharge amounts and duration vary by insurer, state, and driving record — call your agent for an exact number before deciding.', 'myautotriage' ) ); ?>

		<div class="mat-page__content"><?php the_content(); ?></div>

		<?php mat_tool_extras( 'deductible-vs-premium-calculator' ); ?>

		<?php
		mat_faq_block( array_merge( array(
			array(
				'question' => __( 'Will filing one small claim really raise my rate for years?', 'myautotriage' ),
				'answer'   => __( "It depends on your insurer and state, but a single at-fault claim commonly affects your premium for 3-5 years (sometimes called a claim's 'surcharge period'), even after you've paid it off.", 'myautotriage' ),
			),
			array(
				'question' => __( 'Does a not-at-fault claim still raise my premium?', 'myautotriage' ),
				'answer'   => __( "In most states, a claim where you weren't at fault shouldn't raise your premium — but 'claim surcharging' rules differ by state and insurer, so it's worth confirming with your agent.", 'myautotriage' ),
			),
		), mat_tool_extra_faqs( 'deductible-vs-premium-calculator' ) ) );
		?>
	</div>
</div>
<?php
get_footer();
