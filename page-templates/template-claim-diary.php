<?php
/**
 * Template Name: Tool - Claim Diary
 *
 * A log of every call, email and document on a claim, kept in the
 * visitor's browser. It works out the state's claim deadlines from the
 * dates entered, flags missed promises and long silences, and turns the
 * log into a timeline for a letter or complaint.
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-diary', MAT_URI . '/assets/js/claim-diary.js', array( 'mat-tools' ), MAT_VERSION, true );

$mat_diary_states = array();
foreach ( mat_state_laws() as $mat_diary_state ) {
	$item = array( 'name' => $mat_diary_state['name'] );
	if ( $mat_diary_state['deadlines'] ) {
		$item['acknowledge'] = $mat_diary_state['deadlines']['acknowledge'];
		$item['decide']      = $mat_diary_state['deadlines']['decide'];
		$item['pay']         = $mat_diary_state['deadlines']['pay'];
	}
	if ( ! empty( $mat_diary_state['facts']['lawsuit_deadline']['injury_years'] ) ) {
		$item['sol'] = $mat_diary_state['facts']['lawsuit_deadline'];
	}
	$mat_diary_states[ $mat_diary_state['code'] ] = $item;
}
$mat_diary_letter = mat_url_for_slug( 'demand-letter-generator' );
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'Log every call, email and document on your car insurance claim. The diary works out your state\'s deadlines, warns you when the insurer goes quiet or misses a promise, and turns everything into a dated timeline for a letter or a complaint.', 'myautotriage' ); ?></p>
		</div>

		<div id="mat-diary" data-states="<?php echo esc_attr( wp_json_encode( $mat_diary_states ) ); ?>" data-letter-url="<?php echo esc_url( $mat_diary_letter ? add_query_arg( 'type', 'doi-complaint', $mat_diary_letter ) : '' ); ?>">
			<p class="mat-diary-privacy"><?php esc_html_e( 'Your diary is saved only in this browser on this device. Nothing is sent to us. Use "Download a backup" to keep a copy or move it to another device.', 'myautotriage' ); ?></p>

			<form id="mat-diary-claim" class="mat-tool-panel" novalidate>
				<h2 class="mat-tool-panel__heading"><?php esc_html_e( 'Your claim', 'myautotriage' ); ?></h2>
				<div class="mat-field-row">
					<div class="mat-field">
						<label for="mat-diary-insurer"><?php esc_html_e( 'Insurance company', 'myautotriage' ); ?></label>
						<input type="text" id="mat-diary-insurer" placeholder="e.g. Acme Insurance">
					</div>
					<div class="mat-field">
						<label for="mat-diary-claimno"><?php esc_html_e( 'Claim number', 'myautotriage' ); ?></label>
						<input type="text" id="mat-diary-claimno" placeholder="e.g. 2026-0001234">
					</div>
				</div>
				<div class="mat-field-row">
					<div class="mat-field">
						<label for="mat-diary-adjuster"><?php esc_html_e( 'Adjuster name and contact', 'myautotriage' ); ?></label>
						<input type="text" id="mat-diary-adjuster" placeholder="e.g. Sam Rivera, (555) 555-0101">
					</div>
					<div class="mat-field">
						<label for="mat-diary-state"><?php esc_html_e( 'Your state', 'myautotriage' ); ?></label>
						<select id="mat-diary-state">
							<option value=""><?php esc_html_e( 'Choose a state', 'myautotriage' ); ?></option>
							<?php foreach ( $mat_diary_states as $code => $info ) : ?>
								<option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $info['name'] ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="mat-field-row">
					<div class="mat-field">
						<label for="mat-diary-party"><?php esc_html_e( 'Claiming from', 'myautotriage' ); ?></label>
						<select id="mat-diary-party">
							<option value="own"><?php esc_html_e( 'My own insurer', 'myautotriage' ); ?></option>
							<option value="other"><?php esc_html_e( 'The other driver\'s insurer', 'myautotriage' ); ?></option>
						</select>
					</div>
					<div class="mat-field">
						<label for="mat-diary-loss"><?php esc_html_e( 'Date of the accident', 'myautotriage' ); ?></label>
						<input type="date" id="mat-diary-loss">
					</div>
				</div>
				<div class="mat-field-row">
					<div class="mat-field">
						<label for="mat-diary-reported"><?php esc_html_e( 'Date you reported the claim', 'myautotriage' ); ?></label>
						<input type="date" id="mat-diary-reported">
					</div>
					<div class="mat-field">
						<label for="mat-diary-pol"><?php esc_html_e( 'Date the insurer got your proof of loss', 'myautotriage' ); ?></label>
						<input type="date" id="mat-diary-pol">
						<span class="mat-field__hint"><?php esc_html_e( 'The signed form or the full set of documents it asked for. Leave blank if not sent yet.', 'myautotriage' ); ?></span>
					</div>
				</div>
				<p class="mat-field__hint" id="mat-diary-saved" aria-live="polite"></p>
			</form>

			<section id="mat-diary-alerts" class="mat-diary-alerts" aria-labelledby="mat-diary-alerts-title">
				<h2 id="mat-diary-alerts-title"><?php esc_html_e( 'Deadlines and warnings', 'myautotriage' ); ?></h2>
				<div id="mat-diary-alerts-list"></div>
			</section>

			<form id="mat-diary-entry" class="mat-tool-panel" novalidate>
				<h2 class="mat-tool-panel__heading"><?php esc_html_e( 'Add an entry', 'myautotriage' ); ?></h2>
				<div class="mat-field-row">
					<div class="mat-field">
						<label for="mat-diary-e-date"><?php esc_html_e( 'Date', 'myautotriage' ); ?></label>
						<input type="date" id="mat-diary-e-date" required>
					</div>
					<div class="mat-field">
						<label for="mat-diary-e-type"><?php esc_html_e( 'What happened', 'myautotriage' ); ?></label>
						<select id="mat-diary-e-type">
							<option value="sent"><?php esc_html_e( 'I contacted them (call, email, letter)', 'myautotriage' ); ?></option>
							<option value="received"><?php esc_html_e( 'They contacted me', 'myautotriage' ); ?></option>
							<option value="docs"><?php esc_html_e( 'I sent documents', 'myautotriage' ); ?></option>
							<option value="inspection"><?php esc_html_e( 'Inspection or appraisal', 'myautotriage' ); ?></option>
							<option value="offer"><?php esc_html_e( 'Offer or estimate received', 'myautotriage' ); ?></option>
							<option value="payment"><?php esc_html_e( 'Payment received', 'myautotriage' ); ?></option>
							<option value="denial"><?php esc_html_e( 'Denial received', 'myautotriage' ); ?></option>
							<option value="note"><?php esc_html_e( 'Note to self', 'myautotriage' ); ?></option>
						</select>
					</div>
				</div>
				<div class="mat-field">
					<label for="mat-diary-e-summary"><?php esc_html_e( 'What was said or sent', 'myautotriage' ); ?></label>
					<textarea id="mat-diary-e-summary" placeholder="e.g. Called Sam Rivera. Said the estimate would be approved this week and the rental extended to the 20th."></textarea>
				</div>
				<div class="mat-field-row">
					<div class="mat-field">
						<label for="mat-diary-e-promise"><?php esc_html_e( 'Something promised by a date? (optional)', 'myautotriage' ); ?></label>
						<input type="text" id="mat-diary-e-promise" placeholder="e.g. Approve the estimate">
					</div>
					<div class="mat-field">
						<label for="mat-diary-e-due"><?php esc_html_e( 'Promised by', 'myautotriage' ); ?></label>
						<input type="date" id="mat-diary-e-due">
					</div>
				</div>
				<p id="mat-diary-e-error" class="mat-form-error" role="alert" hidden></p>
				<div class="mat-tool-actions">
					<button type="submit" class="mat-btn mat-btn--primary"><?php esc_html_e( 'Add to diary', 'myautotriage' ); ?></button>
				</div>
			</form>

			<section class="mat-diary-log" aria-labelledby="mat-diary-log-title">
				<h2 id="mat-diary-log-title"><?php esc_html_e( 'Timeline', 'myautotriage' ); ?></h2>
				<ol id="mat-diary-list" class="mat-diary-list"></ol>
				<p id="mat-diary-empty" class="mat-field__hint"><?php esc_html_e( 'No entries yet. Add your first call or email above: even a short note with the date helps later.', 'myautotriage' ); ?></p>
				<div class="mat-tool-actions">
					<button type="button" id="mat-diary-copy" class="mat-btn mat-btn--ghost mat-btn--sm"><?php esc_html_e( 'Copy timeline', 'myautotriage' ); ?></button>
					<button type="button" id="mat-diary-complaint" class="mat-btn mat-btn--ghost mat-btn--sm"><?php esc_html_e( 'Use in a complaint letter', 'myautotriage' ); ?></button>
					<button type="button" id="mat-diary-backup" class="mat-btn mat-btn--ghost mat-btn--sm"><?php esc_html_e( 'Download a backup', 'myautotriage' ); ?></button>
					<label class="mat-btn mat-btn--ghost mat-btn--sm mat-diary-import"><?php esc_html_e( 'Restore a backup', 'myautotriage' ); ?><input type="file" id="mat-diary-import" accept="application/json,.json"></label>
					<button type="button" id="mat-diary-clear" class="mat-btn mat-btn--ghost mat-btn--sm"><?php esc_html_e( 'Clear diary', 'myautotriage' ); ?></button>
				</div>
				<p id="mat-diary-confirm" class="mat-diary-confirm" hidden>
					<?php esc_html_e( 'Delete every entry and claim detail from this browser?', 'myautotriage' ); ?>
					<button type="button" id="mat-diary-confirm-yes" class="mat-btn mat-btn--primary mat-btn--sm"><?php esc_html_e( 'Yes, clear it', 'myautotriage' ); ?></button>
					<button type="button" id="mat-diary-confirm-no" class="mat-btn mat-btn--ghost mat-btn--sm"><?php esc_html_e( 'Keep it', 'myautotriage' ); ?></button>
				</p>
				<p id="mat-diary-status" class="mat-field__hint" aria-live="polite"></p>
				<textarea id="mat-diary-timeline" class="mat-diary-timeline" readonly hidden aria-label="<?php esc_attr_e( 'Timeline text', 'myautotriage' ); ?>"></textarea>
			</section>
		</div>

		<?php mat_tool_disclaimer( __( 'Deadline dates are worked out from the rule text in our state data, counting business days as Monday to Friday without holidays. Some rules have conditions or exceptions, so check the statute on your state page before relying on a date.', 'myautotriage' ) ); ?>

		<div class="mat-page__content">
			<?php the_content(); ?>
			<h2><?php esc_html_e( 'Why keep a claim diary', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'Most claim deadlines run from a date: when you reported the claim, when the insurer got your proof of loss, when it said it would pay. A complaint to your state insurance department, an appraisal or a small claims case all go better with a dated record of who said what. Adjusters change and phone calls are forgotten; your diary isn\'t.', 'myautotriage' ); ?></p>
			<h2><?php esc_html_e( 'What to log', 'myautotriage' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Every call: the date, who you spoke to, and what they said they would do and by when.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Every email, letter and document you send, and the date the insurer received it.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Inspections, estimates, offers, payments and denials, with the amounts.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'After a phone call, send a short email that sums it up, and log that too.', 'myautotriage' ); ?></li>
			</ul>
		</div>

		<?php mat_tool_extras( 'claim-diary' ); ?>
	</div>
</div>
<?php
get_footer();
