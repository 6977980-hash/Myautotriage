<?php
/**
 * Template Name: Tool - Loss of Use Calculator
 *
 * What to claim for the days you were without your car after an accident
 * someone else caused.
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-lu', MAT_URI . '/assets/js/calculators/loss-of-use.js', array( 'mat-tools' ), MAT_VERSION, true );
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'Work out what to claim from the at-fault driver\'s insurer for the days you were without your car, whether or not you rented one.', 'myautotriage' ); ?></p>
		</div>

		<form id="mat-lu-form" class="mat-tool-panel" data-letter-url="<?php echo esc_url( mat_url_for_slug( 'property-damage-demand-letter-generator' ) ); ?>" novalidate>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-lu-from"><?php esc_html_e( 'First day without your car', 'myautotriage' ); ?></label>
					<input type="date" id="mat-lu-from" required>
					<span class="mat-field__hint"><?php esc_html_e( 'Usually the accident date, if the car couldn\'t be driven.', 'myautotriage' ); ?></span>
				</div>
				<div class="mat-field">
					<label for="mat-lu-to"><?php esc_html_e( 'Day you got it back (or were paid for a total loss)', 'myautotriage' ); ?></label>
					<input type="date" id="mat-lu-to" required>
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-lu-rate"><?php esc_html_e( 'Daily rate for a comparable rental (USD)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-lu-rate" min="1" max="1000" step="0.01" placeholder="e.g. 45" required>
					<span class="mat-field__hint"><?php esc_html_e( 'What you paid, or a local quote for a similar car if you didn\'t rent.', 'myautotriage' ); ?></span>
				</div>
				<div class="mat-field">
					<label for="mat-lu-paid"><?php esc_html_e( 'Days the insurer already paid a rental', 'myautotriage' ); ?></label>
					<input type="number" id="mat-lu-paid" min="0" step="1" value="0">
				</div>
			</div>
			<div class="mat-field">
				<label for="mat-lu-extra"><?php esc_html_e( 'Other costs from being without the car (USD)', 'myautotriage' ); ?></label>
				<input type="number" id="mat-lu-extra" min="0" step="0.01" placeholder="e.g. rideshare, bus fares">
			</div>
			<p id="mat-lu-error" class="mat-form-error" role="alert" hidden></p>
			<div class="mat-tool-actions">
				<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Calculate my loss of use', 'myautotriage' ); ?></button>
			</div>
		</form>

		<div id="mat-lu-result" class="mat-result-box" hidden></div>

		<?php mat_tool_disclaimer( __( 'Insurers pay loss of use for a reasonable repair or replacement time, and states differ on whether you must have rented a car to claim it. Keep receipts and a dated log of repair delays.', 'myautotriage' ) ); ?>

		<div class="mat-page__content">
			<?php the_content(); ?>
			<h2><?php esc_html_e( 'What loss of use covers', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'When another driver damages your car, their liability insurance owes you for the time you could not use it, not just for the repair. Most often that is a rental car of similar size and class; in many states you can claim the reasonable rental value even if you got by without renting.', 'myautotriage' ); ?></p>
			<h2><?php esc_html_e( 'How to get the full number paid', 'myautotriage' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Ask for a comparable vehicle: an SUV owner shouldn\'t be put in an economy car without the difference being paid.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Keep a log of delays you didn\'t cause, such as parts on backorder or the insurer\'s late inspection; those days are still the at-fault side\'s cost.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'For a total loss, the period usually runs until the insurer makes a reasonable settlement offer, not until you buy another car.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Your own policy\'s rental reimbursement coverage often has a daily cap (for example $30 a day for 30 days); the at-fault insurer owes the rest.', 'myautotriage' ); ?></li>
			</ul>
		</div>

		<?php mat_tool_extras( 'loss-of-use-calculator' ); ?>

		<?php
		mat_faq_block( array_merge( array(
			array(
				'question' => __( 'Can I claim loss of use if I didn\'t rent a car?', 'myautotriage' ),
				'answer'   => __( 'In many states, yes: courts allow the reasonable rental value for the time you were without the car. Some insurers only reimburse actual rental receipts, so ask in writing and cite your state\'s rule if they refuse.', 'myautotriage' ),
			),
			array(
				'question' => __( 'How many days of loss of use will the insurer pay?', 'myautotriage' ),
				'answer'   => __( 'A reasonable time to repair or replace the car. Delays caused by the insurer or by parts shortages usually still count; delays you caused, like not authorizing repairs, may not.', 'myautotriage' ),
			),
		), mat_tool_extra_faqs( 'loss-of-use-calculator' ) ) );
		?>
	</div>
</div>
<?php
get_footer();
