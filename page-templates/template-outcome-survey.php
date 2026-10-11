<?php
/**
 * Template Name: Claim Outcome Survey
 *
 * Six questions about a finished (or stalled) claim: the first offer, the
 * final amount and what the visitor did in between. Answers feed the
 * Lowball Index; see inc/claim-outcomes.php.
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-outcomes', MAT_URI . '/assets/js/outcome-survey.js', array(), MAT_VERSION, true );
$mat_out_choices = mat_outcomes_choices();
$mat_out_index   = home_url( user_trailingslashit( MAT_OUTCOMES_INDEX_SLUG ) );
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'Insurers know how often people accept a low first offer. Claimants don\'t. Tell us what you were first offered and what you finally got, and we\'ll publish the totals as the Lowball Index so the next person knows whether pushing back is worth it.', 'myautotriage' ); ?></p>
		</div>

		<p class="mat-diary-privacy"><?php esc_html_e( 'Anonymous: no name, email, claim number or IP address is stored. We publish only totals for groups of at least 30 answers.', 'myautotriage' ); ?> <a href="<?php echo esc_url( get_privacy_policy_url() ?: home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy policy', 'myautotriage' ); ?></a></p>

		<form id="mat-out-form" class="mat-tool-panel" novalidate data-endpoint="<?php echo esc_url( rest_url( 'mat/v1/outcome' ) ); ?>">
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-out-type"><?php esc_html_e( 'What kind of claim?', 'myautotriage' ); ?></label>
					<select id="mat-out-type" name="claim_type" required>
						<option value=""><?php esc_html_e( 'Choose…', 'myautotriage' ); ?></option>
						<?php foreach ( $mat_out_choices['claim_type'] as $k => $label ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="mat-field">
					<label for="mat-out-party"><?php esc_html_e( 'Whose insurer paid?', 'myautotriage' ); ?></label>
					<select id="mat-out-party" name="party" required>
						<option value=""><?php esc_html_e( 'Choose…', 'myautotriage' ); ?></option>
						<?php foreach ( $mat_out_choices['party'] as $k => $label ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-out-first"><?php esc_html_e( 'First offer ($)', 'myautotriage' ); ?></label>
					<input type="text" inputmode="decimal" id="mat-out-first" name="first_offer" placeholder="e.g. 11,200" required>
				</div>
				<div class="mat-field">
					<label for="mat-out-final"><?php esc_html_e( 'Final amount paid, or latest offer ($)', 'myautotriage' ); ?></label>
					<input type="text" inputmode="decimal" id="mat-out-final" name="final_amount" placeholder="e.g. 13,450" required>
				</div>
			</div>
			<div class="mat-field">
				<label for="mat-out-status"><?php esc_html_e( 'Is the claim settled?', 'myautotriage' ); ?></label>
				<select id="mat-out-status" name="status" required>
					<option value=""><?php esc_html_e( 'Choose…', 'myautotriage' ); ?></option>
					<?php foreach ( $mat_out_choices['status'] as $k => $label ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<fieldset class="mat-out-steps">
				<legend><?php esc_html_e( 'What did you do after the first offer? (tick all that apply)', 'myautotriage' ); ?></legend>
				<?php foreach ( $mat_out_choices['steps'] as $k => $label ) : ?>
					<label><input type="checkbox" name="steps" value="<?php echo esc_attr( $k ); ?>"> <?php echo esc_html( $label ); ?></label>
				<?php endforeach; ?>
				<p class="mat-field__hint"><?php esc_html_e( 'Leave all blank if you accepted without pushing back.', 'myautotriage' ); ?></p>
			</fieldset>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-out-insurer"><?php esc_html_e( 'Insurance company (optional)', 'myautotriage' ); ?></label>
					<select id="mat-out-insurer" name="insurer">
						<option value=""><?php esc_html_e( 'Prefer not to say', 'myautotriage' ); ?></option>
						<?php foreach ( $mat_out_choices['insurer'] as $name ) : ?>
							<option value="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="mat-field">
					<label for="mat-out-state"><?php esc_html_e( 'State (optional)', 'myautotriage' ); ?></label>
					<select id="mat-out-state" name="state">
						<option value=""><?php esc_html_e( 'Prefer not to say', 'myautotriage' ); ?></option>
						<?php foreach ( mat_state_laws() as $mat_out_state ) : ?>
							<option value="<?php echo esc_attr( $mat_out_state['code'] ); ?>"><?php echo esc_html( $mat_out_state['name'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="mat-out-hp" aria-hidden="true">
				<label for="mat-out-website">Website</label>
				<input type="text" id="mat-out-website" name="website" tabindex="-1" autocomplete="off">
			</div>
			<p id="mat-out-error" class="mat-form-error" role="alert" hidden></p>
			<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Send my answers', 'myautotriage' ); ?></button>
		</form>

		<div id="mat-out-thanks" class="mat-triage-result" hidden tabindex="-1">
			<p class="mat-triage-result__label"><?php esc_html_e( 'Thank you', 'myautotriage' ); ?></p>
			<h2 class="mat-triage-result__title" id="mat-out-thanks-title"></h2>
			<p><?php esc_html_e( 'Your answers are in. Every answer makes the numbers more useful for the next person staring at a low offer.', 'myautotriage' ); ?></p>
			<a class="mat-btn mat-btn--primary" href="<?php echo esc_url( $mat_out_index ); ?>"><?php esc_html_e( 'See the Lowball Index', 'myautotriage' ); ?></a>
		</div>

		<section class="mat-page__content">
			<h2><?php esc_html_e( 'Why we ask', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'Most advice about insurance offers says "always negotiate" without numbers behind it. Adjusters see thousands of claims; you see one. Pooling first offers and final amounts shows how far offers usually move, and whether a written counter-offer, an appraisal or a complaint to the state made a difference.', 'myautotriage' ); ?></p>
			<p><?php esc_html_e( 'Still negotiating? Your latest offer counts too: pick "Still open" and we keep it out of the settled totals.', 'myautotriage' ); ?></p>
		</section>
	</div>
</div>
<?php
get_footer();
