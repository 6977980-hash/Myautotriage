<?php
/**
 * Template Name: Tool - Claim Denial Appeal Letter Generator
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-ap', MAT_URI . '/assets/js/generators/appeal-letter.js', array(), MAT_VERSION, true );
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'Turn a denied auto insurance claim into a clear, evidence-backed written appeal — free, no sign-up.', 'myautotriage' ); ?></p>
		</div>

		<form id="mat-ap-form" class="mat-tool-panel" novalidate>
			<h2 style="font-size:1.05rem;"><?php esc_html_e( 'Your information', 'myautotriage' ); ?></h2>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-ap-name"><?php esc_html_e( 'Your full name', 'myautotriage' ); ?></label>
					<input type="text" id="mat-ap-name" placeholder="Jordan Smith">
				</div>
				<div class="mat-field">
					<label for="mat-ap-contact"><?php esc_html_e( 'Your phone and/or email', 'myautotriage' ); ?></label>
					<input type="text" id="mat-ap-contact" placeholder="(555) 555-0101 · jordan@example.com">
				</div>
			</div>
			<div class="mat-field">
				<label for="mat-ap-address"><?php esc_html_e( 'Your mailing address', 'myautotriage' ); ?></label>
				<input type="text" id="mat-ap-address" placeholder="123 Main St, Springfield, ST 00000">
			</div>

			<h2 style="font-size:1.05rem;"><?php esc_html_e( 'Claim &amp; denial details', 'myautotriage' ); ?></h2>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-ap-insurer"><?php esc_html_e( "Insurance company's name and address", 'myautotriage' ); ?></label>
					<input type="text" id="mat-ap-insurer" placeholder="Acme Insurance Co., Claims Review Dept., PO Box 1, City, ST 00000">
				</div>
				<div class="mat-field">
					<label for="mat-ap-claim"><?php esc_html_e( 'Claim number', 'myautotriage' ); ?></label>
					<input type="text" id="mat-ap-claim" placeholder="e.g. 2026-0001234">
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-ap-policy"><?php esc_html_e( 'Policy number (optional)', 'myautotriage' ); ?></label>
					<input type="text" id="mat-ap-policy" placeholder="e.g. POL-00998877">
				</div>
				<div class="mat-field">
					<label for="mat-ap-denialdate"><?php esc_html_e( 'Date of denial letter', 'myautotriage' ); ?></label>
					<input type="date" id="mat-ap-denialdate">
				</div>
			</div>
			<div class="mat-field">
				<label for="mat-ap-reason"><?php esc_html_e( 'Reason the insurer gave for denying your claim', 'myautotriage' ); ?></label>
				<textarea id="mat-ap-reason" placeholder="Quote or closely paraphrase the reason from your denial letter."></textarea>
			</div>
			<div class="mat-field">
				<label for="mat-ap-rebuttal"><?php esc_html_e( 'Why you believe that reason is wrong or incomplete', 'myautotriage' ); ?></label>
				<textarea id="mat-ap-rebuttal" placeholder="Be specific: cite policy language, dates, facts, or evidence that contradict the denial reason."></textarea>
			</div>
			<div class="mat-field">
				<label for="mat-ap-evidence"><?php esc_html_e( "Documents you're attaching", 'myautotriage' ); ?></label>
				<input type="text" id="mat-ap-evidence" placeholder="e.g. repair estimate, photos, police report, witness statement">
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-ap-amount"><?php esc_html_e( 'Amount you are requesting (USD, optional)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-ap-amount" min="0" step="1" placeholder="e.g. 6200">
				</div>
				<div class="mat-field">
					<label for="mat-ap-deadline"><?php esc_html_e( 'Response deadline (days)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-ap-deadline" min="3" max="60" step="1" value="30">
				</div>
			</div>

			<div class="mat-tool-actions">
				<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Generate my appeal letter', 'myautotriage' ); ?></button>
			</div>
		</form>

		<div id="mat-ap-preview-wrap" hidden>
			<h2><?php esc_html_e( 'Your appeal letter', 'myautotriage' ); ?></h2>
			<div id="mat-ap-preview" class="mat-letter-preview"></div>
			<div class="mat-tool-actions">
				<button type="button" id="mat-ap-print" class="mat-btn mat-btn--primary"><?php esc_html_e( 'Print / Save as PDF', 'myautotriage' ); ?></button>
				<button type="button" id="mat-ap-copy" class="mat-btn mat-btn--ghost"><?php esc_html_e( 'Copy text', 'myautotriage' ); ?></button>
			</div>
		</div>

		<?php mat_tool_disclaimer( __( "Most insurers give you a limited window to appeal internally before you must go to your state's insurance regulator or court — check your denial letter for that deadline.", 'myautotriage' ) ); ?>

		<div class="mat-page__content"><?php the_content(); ?></div>

		<?php mat_tool_extras( 'appeal-letter-generator' ); ?>

		<?php
		mat_faq_block( array_merge( array(
			array(
				'question' => __( 'How many times can I appeal a denied claim?', 'myautotriage' ),
				'answer'   => __( "This depends on your insurer and your state's regulations. Most insurers offer at least one internal appeal; after that, your options usually include a state insurance department complaint, mediation, arbitration (if your policy requires it), or a lawsuit.", 'myautotriage' ),
			),
			array(
				'question' => __( "What if I don't have all the documentation yet?", 'myautotriage' ),
				'answer'   => __( "Send your appeal by the deadline with what you have, note that additional documentation will follow, and continue gathering it. Missing the appeal deadline is usually worse than submitting an incomplete-but-timely appeal.", 'myautotriage' ),
			),
		), mat_tool_extra_faqs( 'appeal-letter-generator' ) ) );
		?>
	</div>
</div>
<?php
get_footer();
