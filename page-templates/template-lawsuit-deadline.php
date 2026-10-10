<?php
/**
 * Template Name: Tool - Car Accident Lawsuit Deadline Calculator
 *
 * Statute of limitations date for an injury or vehicle-damage lawsuit,
 * from the same state data as the state pages
 * (assets/js/data/state-claim-facts.json).
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-sol', MAT_URI . '/assets/js/calculators/lawsuit-deadline.js', array( 'mat-tools' ), MAT_VERSION, true );
$hub = get_page_by_path( MAT_STATE_HUB_SLUG ) ? trailingslashit( mat_state_hub_url() ) : '';
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'Find the last day to file a lawsuit after a car accident in your state, for injuries or for damage to your car, and how many days you have left.', 'myautotriage' ); ?></p>
		</div>

		<form id="mat-sol-form" class="mat-tool-panel" data-json="<?php echo esc_url( MAT_URI . '/assets/js/data/state-claim-facts.json' ); ?>" data-hub-url="<?php echo esc_url( $hub ); ?>" novalidate>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-sol-state"><?php esc_html_e( 'State where the accident happened', 'myautotriage' ); ?></label>
					<select id="mat-sol-state" required>
						<option value=""><?php esc_html_e( 'Choose your state', 'myautotriage' ); ?></option>
						<?php foreach ( mat_state_laws() as $state ) : ?>
							<option value="<?php echo esc_attr( $state['code'] ); ?>"><?php echo esc_html( $state['name'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="mat-field">
					<label for="mat-sol-date"><?php esc_html_e( 'Date of the accident', 'myautotriage' ); ?></label>
					<input type="date" id="mat-sol-date" required>
				</div>
			</div>
			<div class="mat-field">
				<label for="mat-sol-type"><?php esc_html_e( 'What you would sue for', 'myautotriage' ); ?></label>
				<select id="mat-sol-type">
					<option value="both"><?php esc_html_e( 'Show both', 'myautotriage' ); ?></option>
					<option value="injury"><?php esc_html_e( 'Injuries', 'myautotriage' ); ?></option>
					<option value="property"><?php esc_html_e( 'Damage to my car', 'myautotriage' ); ?></option>
				</select>
			</div>
			<p id="mat-sol-error" class="mat-form-error" role="alert" hidden></p>
			<div class="mat-tool-actions">
				<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Find my deadline', 'myautotriage' ); ?></button>
			</div>
		</form>

		<div id="mat-sol-result" class="mat-result-box" hidden></div>

		<?php mat_tool_disclaimer( __( 'This is the general deadline in state law. Exceptions can shorten it (claims against a city or state agency often need a written notice within months) or extend it (a minor, or an injury discovered later). If the deadline is near, talk to a licensed attorney now.', 'myautotriage' ) ); ?>

		<div class="mat-page__content">
			<?php the_content(); ?>
			<h2><?php esc_html_e( 'What the lawsuit deadline means', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'Every state sets a statute of limitations: a fixed time after the accident to file a lawsuit against the at-fault driver. Miss it and the court will almost always dismiss the case, and the insurer knows it, so your negotiating leverage disappears with it.', 'myautotriage' ); ?></p>
			<p><?php esc_html_e( 'An open insurance claim does not pause the clock. Adjusters sometimes keep talking until the deadline has passed. If a fair settlement isn\'t in writing a few months before the date below, get legal advice.', 'myautotriage' ); ?></p>
			<h2><?php esc_html_e( 'Deadlines that come sooner', 'myautotriage' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Government vehicles: a crash with a city bus, police car or state truck usually needs a formal notice of claim within a short window, sometimes 30 to 180 days.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Your own policy: no-fault (PIP) benefits, uninsured motorist claims and the policy\'s own "suit against us" clause can have shorter limits than the statute.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Wrongful death claims often run from the date of death, not the accident, and may be shorter.', 'myautotriage' ); ?></li>
			</ul>
		</div>

		<?php mat_tool_extras( 'car-accident-lawsuit-deadline-calculator' ); ?>

		<?php
		mat_faq_block( array_merge( array(
			array(
				'question' => __( 'Does filing an insurance claim stop the statute of limitations?', 'myautotriage' ),
				'answer'   => __( 'No. Reporting the accident or negotiating with an adjuster does not pause the deadline to sue. Only filing a lawsuit in court (or a written agreement to extend, which insurers rarely sign) protects the claim.', 'myautotriage' ),
			),
			array(
				'question' => __( 'What if the deadline falls on a weekend or holiday?', 'myautotriage' ),
				'answer'   => __( 'Most states move a deadline that lands on a weekend or court holiday to the next business day, but do not count on it. File well before the date.', 'myautotriage' ),
			),
		), mat_tool_extra_faqs( 'car-accident-lawsuit-deadline-calculator' ) ) );
		?>
	</div>
</div>
<?php
get_footer();
