<?php
/**
 * Template Name: Tool - Rental Extension Letter
 *
 * A letter asking the insurer to extend a rental car while repairs or a
 * total loss decision are still pending, with the day count and, on the
 * visitor's own policy, a check against the rental coverage limits.
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-re', MAT_URI . '/assets/js/generators/rental-extension.js', array( 'mat-tools' ), MAT_VERSION, true );
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'The insurer set a date to end your rental, but your car isn\'t fixed and the delay isn\'t your fault. Fill this in to get a dated, polite letter asking for an extension, with the reason and the number of extra days.', 'myautotriage' ); ?></p>
		</div>

		<form id="mat-re-form" class="mat-tool-panel" novalidate>
			<div class="mat-field">
				<span class="mat-field__label" id="mat-re-party-label"><?php esc_html_e( 'Who is paying for the rental?', 'myautotriage' ); ?></span>
				<div class="mat-radio-group" role="radiogroup" aria-labelledby="mat-re-party-label">
					<label><input type="radio" name="mat-re-party" value="other" checked> <?php esc_html_e( 'The other driver\'s insurer', 'myautotriage' ); ?></label>
					<label><input type="radio" name="mat-re-party" value="own"> <?php esc_html_e( 'My own policy (rental reimbursement)', 'myautotriage' ); ?></label>
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-re-end"><?php esc_html_e( 'Date the insurer says the rental ends', 'myautotriage' ); ?></label>
					<input type="date" id="mat-re-end" required>
				</div>
				<div class="mat-field">
					<label for="mat-re-ready"><?php esc_html_e( 'Date the car should be ready (or the claim settled)', 'myautotriage' ); ?></label>
					<input type="date" id="mat-re-ready" required>
					<span class="mat-field__hint"><?php esc_html_e( 'Ask the shop for its latest estimate, in writing if you can.', 'myautotriage' ); ?></span>
				</div>
			</div>
			<div class="mat-field">
				<label for="mat-re-reason"><?php esc_html_e( 'Why the car isn\'t ready', 'myautotriage' ); ?></label>
				<select id="mat-re-reason">
					<option value="parts"><?php esc_html_e( 'Parts are on back order', 'myautotriage' ); ?></option>
					<option value="supplement"><?php esc_html_e( 'The shop is waiting for the insurer to approve a supplement', 'myautotriage' ); ?></option>
					<option value="inspection"><?php esc_html_e( 'The insurer hasn\'t inspected the car yet', 'myautotriage' ); ?></option>
					<option value="totalloss"><?php esc_html_e( 'The insurer hasn\'t decided whether it\'s a total loss', 'myautotriage' ); ?></option>
					<option value="payment"><?php esc_html_e( 'Total loss: still waiting for a fair offer or the payment', 'myautotriage' ); ?></option>
					<option value="repairs"><?php esc_html_e( 'The repairs are simply taking longer than first estimated', 'myautotriage' ); ?></option>
				</select>
			</div>
			<div class="mat-field-row mat-re-own" hidden>
				<div class="mat-field">
					<label for="mat-re-cap-days"><?php esc_html_e( 'Maximum days on your policy', 'myautotriage' ); ?></label>
					<input type="number" id="mat-re-cap-days" min="1" max="365" step="1" placeholder="e.g. 30">
					<span class="mat-field__hint"><?php esc_html_e( 'On your declarations page, e.g. "$40/day, 30 days max".', 'myautotriage' ); ?></span>
				</div>
				<div class="mat-field">
					<label for="mat-re-start"><?php esc_html_e( 'Date the rental started', 'myautotriage' ); ?></label>
					<input type="date" id="mat-re-start">
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-re-name"><?php esc_html_e( 'Your name', 'myautotriage' ); ?></label>
					<input type="text" id="mat-re-name" placeholder="Jordan Smith">
				</div>
				<div class="mat-field">
					<label for="mat-re-claim"><?php esc_html_e( 'Claim number', 'myautotriage' ); ?></label>
					<input type="text" id="mat-re-claim" placeholder="e.g. 2026-0001234">
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-re-adjuster"><?php esc_html_e( 'Adjuster name', 'myautotriage' ); ?></label>
					<input type="text" id="mat-re-adjuster" placeholder="e.g. Sam Rivera">
				</div>
				<div class="mat-field">
					<label for="mat-re-shop"><?php esc_html_e( 'Repair shop', 'myautotriage' ); ?></label>
					<input type="text" id="mat-re-shop" placeholder="e.g. Main Street Collision">
				</div>
			</div>
			<p id="mat-re-error" class="mat-form-error" role="alert" hidden></p>
			<div class="mat-tool-actions">
				<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Write my extension request', 'myautotriage' ); ?></button>
			</div>
		</form>

		<div id="mat-re-preview-wrap" hidden>
			<div id="mat-re-summary" class="mat-result-box"></div>
			<h2><?php esc_html_e( 'Your letter', 'myautotriage' ); ?></h2>
			<div id="mat-re-preview" class="mat-letter-preview"></div>
			<div class="mat-tool-actions">
				<button type="button" id="mat-re-print" class="mat-btn mat-btn--primary"><?php esc_html_e( 'Print / Save as PDF', 'myautotriage' ); ?></button>
				<button type="button" id="mat-re-copy" class="mat-btn mat-btn--ghost"><?php esc_html_e( 'Copy text', 'myautotriage' ); ?></button>
			</div>
		</div>

		<?php mat_tool_disclaimer( __( 'Send the request by email before the rental end date and keep the reply. This letter is a template, not legal advice; your policy and your state\'s law decide what is owed.', 'myautotriage' ) ); ?>

		<div class="mat-page__content">
			<?php the_content(); ?>
			<h2><?php esc_html_e( 'Whose insurer is paying changes the rules', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'If the other driver caused the accident, their insurer owes you loss of use: generally the cost of a comparable rental for the time reasonably needed to repair or replace your car. A cutoff date picked from the shop\'s first estimate isn\'t the limit when the delay comes from back-ordered parts or the insurer\'s own slow approvals. Connecticut\'s insurance department, for example, says loss of use covers the period reasonably required to repair or replace the vehicle.', 'myautotriage' ); ?></p>
			<p><?php esc_html_e( 'On your own policy, rental reimbursement pays up to the daily and total limits you bought, such as $40 a day for 30 days. Once those run out, the extra days are yours to claim from the at-fault driver\'s insurer, if someone else caused the accident.', 'myautotriage' ); ?></p>
			<h2><?php esc_html_e( 'Make the request stick', 'myautotriage' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Send it before the end date, by email, so there is a timestamp.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Attach the shop\'s note on the delay: a parts back-order notice or a supplement request date.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Ask for any refusal in writing, with the date the insurer considers reasonable and why.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'If they cut it off anyway, keep paying receipts or a log of days without a car and claim them as loss of use.', 'myautotriage' ); ?></li>
			</ul>
		</div>

		<?php mat_tool_extras( 'rental-car-extension-letter' ); ?>

		<?php
		mat_faq_block( array_merge( array(
			array(
				'question' => __( 'Can the insurance company end my rental before my car is fixed?', 'myautotriage' ),
				'answer'   => __( 'On your own policy, yes, once you reach the coverage limits. From the at-fault driver\'s insurer, they owe loss of use for a reasonable repair or replacement time; if they cut the rental off early, you can still claim the extra days from them, backed by the shop\'s records of the delay.', 'myautotriage' ),
			),
			array(
				'question' => __( 'How long does the rental last when my car is totaled?', 'myautotriage' ),
				'answer'   => __( 'Usually until a few days after the insurer makes a reasonable total loss offer or pays it, not until you find another car. If the offer is unreasonably low or slow, say so in writing and ask for the rental to continue until it is resolved.', 'myautotriage' ),
			),
			array(
				'question' => __( 'What if I\'m paying for the extra rental days myself?', 'myautotriage' ),
				'answer'   => __( 'Keep every receipt. If another driver caused the accident, add those days to your property damage claim against their insurer as loss of use.', 'myautotriage' ),
			),
		), mat_tool_extra_faqs( 'rental-car-extension-letter' ) ) );
		mat_render_sources( array(
			array(
				'label' => __( 'Connecticut Insurance Department Bulletin CL-1-07: Loss of use in third-party auto claims', 'myautotriage' ),
				'url'   => 'https://portal.ct.gov/cid/department-resources/bulletins/claims-bulletins/bulletin-cl-1-07',
			),
			array(
				'label' => __( 'Maine Bureau of Insurance: Auto claims FAQ', 'myautotriage' ),
				'url'   => 'https://www.maine.gov/pfr/insurance/frequently-asked-questions/auto-claims',
			),
		) );
		?>
	</div>
</div>
<?php
get_footer();
