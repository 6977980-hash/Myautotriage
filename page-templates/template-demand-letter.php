<?php
/**
 * Template Name: Tool - Demand Letter Generator
 *
 * One generator engine, reused by several keyword-targeted landing pages
 * (different slugs, same template) — the slug picks the default letter
 * type, headline, and intro copy below, per the site's internal-linking
 * plan: one JS engine, several SEO landing pages.
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-dl', MAT_URI . '/assets/js/generators/demand-letter.js', array( 'mat-tools' ), MAT_VERSION, true );

$variants = array(
	'demand-letter-generator' => array(
		'type'    => 'general',
		'lead'    => __( 'Generate a professional demand letter for any car accident property damage claim in a few minutes — free, no sign-up.', 'myautotriage' ),
	),
	'no-injury-demand-letter-generator' => array(
		'type'    => 'no-injury',
		'lead'    => __( "Generate a no-injury car accident demand letter that makes clear you're claiming property damage only — free, no sign-up.", 'myautotriage' ),
	),
	'property-damage-demand-letter-generator' => array(
		'type'    => 'property-damage',
		'lead'    => __( "Generate a property-damage demand letter for your car accident claim, backed by your repair estimate and photos.", 'myautotriage' ),
	),
	'insurance-underpayment-demand-letter-generator' => array(
		'type'    => 'underpayment',
		'lead'    => __( "Generate a demand letter that formally disputes a lowball or underpaid settlement offer.", 'myautotriage' ),
	),
	'diminished-value-demand-letter-generator' => array(
		'type'    => 'diminished-value',
		'lead'    => __( 'Generate a diminished value demand letter — pairs perfectly with the 17c diminished value calculator.', 'myautotriage' ),
	),
	'small-claims-demand-letter-generator' => array(
		'type'    => 'small-claims',
		'lead'    => __( 'Generate a final demand letter before filing in small claims court.', 'myautotriage' ),
	),
);

$slug    = get_post_field( 'post_name' );
$variant = isset( $variants[ $slug ] ) ? $variants[ $slug ] : $variants['demand-letter-generator'];
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php echo esc_html( $variant['lead'] ); ?></p>
		</div>

		<form id="mat-dl-form" class="mat-tool-panel" novalidate>
			<div class="mat-field">
				<label for="mat-dl-type"><?php esc_html_e( 'Letter type', 'myautotriage' ); ?></label>
				<select id="mat-dl-type">
					<option value="general" <?php selected( $variant['type'], 'general' ); ?>><?php esc_html_e( 'General car accident demand letter', 'myautotriage' ); ?></option>
					<option value="no-injury" <?php selected( $variant['type'], 'no-injury' ); ?>><?php esc_html_e( 'No-injury (property damage only)', 'myautotriage' ); ?></option>
					<option value="property-damage" <?php selected( $variant['type'], 'property-damage' ); ?>><?php esc_html_e( 'Property damage claim', 'myautotriage' ); ?></option>
					<option value="underpayment" <?php selected( $variant['type'], 'underpayment' ); ?>><?php esc_html_e( 'Disputing an underpaid settlement', 'myautotriage' ); ?></option>
					<option value="diminished-value" <?php selected( $variant['type'], 'diminished-value' ); ?>><?php esc_html_e( 'Diminished value claim', 'myautotriage' ); ?></option>
					<option value="small-claims" <?php selected( $variant['type'], 'small-claims' ); ?>><?php esc_html_e( 'Final demand before small claims court', 'myautotriage' ); ?></option>
					<option value="total-loss" <?php selected( $variant['type'], 'total-loss' ); ?>><?php esc_html_e( 'Total loss counter-offer (dispute the car\'s value)', 'myautotriage' ); ?></option>
					<option value="appraisal" <?php selected( $variant['type'], 'appraisal' ); ?>><?php esc_html_e( 'Invoke the appraisal clause', 'myautotriage' ); ?></option>
					<option value="doi-complaint" <?php selected( $variant['type'], 'doi-complaint' ); ?>><?php esc_html_e( 'Complaint to the state department of insurance', 'myautotriage' ); ?></option>
				</select>
				<span class="mat-field__hint"><?php esc_html_e( 'Appraisal applies to claims under your own policy, when you and the insurer agree the loss is covered but disagree on the amount. Most insurance departments also take complaints through an online form; the letter text works there too.', 'myautotriage' ); ?></span>
			</div>

			<h2 style="font-size:1.05rem;"><?php esc_html_e( 'Your information', 'myautotriage' ); ?></h2>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-dl-name"><?php esc_html_e( 'Your full name', 'myautotriage' ); ?></label>
					<input type="text" id="mat-dl-name" placeholder="Jordan Smith">
				</div>
				<div class="mat-field">
					<label for="mat-dl-contact"><?php esc_html_e( 'Your phone and/or email', 'myautotriage' ); ?></label>
					<input type="text" id="mat-dl-contact" placeholder="(555) 555-0101 · jordan@example.com">
				</div>
			</div>
			<div class="mat-field">
				<label for="mat-dl-address"><?php esc_html_e( 'Your mailing address', 'myautotriage' ); ?></label>
				<input type="text" id="mat-dl-address" placeholder="123 Main St, Springfield, ST 00000">
			</div>

			<h2 style="font-size:1.05rem;"><?php esc_html_e( 'Claim details', 'myautotriage' ); ?></h2>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-dl-insurer"><?php esc_html_e( "Insurance company's name and address", 'myautotriage' ); ?></label>
					<input type="text" id="mat-dl-insurer" placeholder="Acme Insurance Co., Claims Dept., PO Box 1, City, ST 00000">
				</div>
				<div class="mat-field">
					<label for="mat-dl-claim"><?php esc_html_e( 'Claim number', 'myautotriage' ); ?></label>
					<input type="text" id="mat-dl-claim" placeholder="e.g. 2026-0001234">
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-dl-adjuster"><?php esc_html_e( 'Adjuster name (optional)', 'myautotriage' ); ?></label>
					<input type="text" id="mat-dl-adjuster" placeholder="e.g. Sam Rivera">
				</div>
				<div class="mat-field">
					<label for="mat-dl-atfault"><?php esc_html_e( 'At-fault driver name', 'myautotriage' ); ?></label>
					<input type="text" id="mat-dl-atfault" placeholder="e.g. Taylor Lee">
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-dl-date"><?php esc_html_e( 'Date of accident', 'myautotriage' ); ?></label>
					<input type="date" id="mat-dl-date">
				</div>
				<div class="mat-field">
					<label for="mat-dl-location"><?php esc_html_e( 'Location of accident', 'myautotriage' ); ?></label>
					<input type="text" id="mat-dl-location" placeholder="e.g. Main St & 5th Ave, Springfield, ST">
				</div>
			</div>
			<div class="mat-field">
				<label for="mat-dl-narrative"><?php esc_html_e( 'Briefly describe what happened and the damage (2-4 sentences)', 'myautotriage' ); ?></label>
				<textarea id="mat-dl-narrative" placeholder="e.g. Your insured ran a red light and struck the front passenger side of my vehicle. The impact caused significant damage to the front bumper, headlight, and fender, as documented in the enclosed repair estimate."></textarea>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-dl-amount"><?php esc_html_e( 'Amount you are demanding (USD)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-dl-amount" min="0" step="1" placeholder="e.g. 4200">
				</div>
				<div class="mat-field">
					<label for="mat-dl-deadline"><?php esc_html_e( 'Response deadline (days)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-dl-deadline" min="3" max="60" step="1" value="14">
				</div>
			</div>

			<p id="mat-dl-error" class="mat-form-error" role="alert" hidden></p>
			<div class="mat-tool-actions">
				<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Generate my letter', 'myautotriage' ); ?></button>
			</div>
		</form>

		<div id="mat-dl-preview-wrap" hidden>
			<h2><?php esc_html_e( 'Your letter', 'myautotriage' ); ?></h2>
			<div id="mat-dl-preview" class="mat-letter-preview"></div>
			<div class="mat-tool-actions">
				<button type="button" id="mat-dl-print" class="mat-btn mat-btn--primary"><?php esc_html_e( 'Print / Save as PDF', 'myautotriage' ); ?></button>
				<button type="button" id="mat-dl-copy" class="mat-btn mat-btn--ghost"><?php esc_html_e( 'Copy text', 'myautotriage' ); ?></button>
			</div>
		</div>

		<?php mat_tool_disclaimer( __( 'This generator produces a first draft based on what you enter. Review it carefully, attach your supporting documents, and consider legal review before sending a large or disputed claim.', 'myautotriage' ) ); ?>

		<div class="mat-page__content"><?php the_content(); ?></div>

		<?php mat_tool_extras( get_post_field( 'post_name' ) ); ?>

		<?php
		mat_faq_block( array_merge( array(
			array(
				'question' => __( 'How should I send my demand letter?', 'myautotriage' ),
				'answer'   => __( 'Send it by email and by certified mail with return receipt requested, so you have proof of delivery and a timestamp for your deadline.', 'myautotriage' ),
			),
			array(
				'question' => __( 'What should I attach to the letter?', 'myautotriage' ),
				'answer'   => __( 'Attach your repair estimate(s), photos of the damage, the police report if one exists, and any relevant receipts. The stronger your documentation, the stronger your position.', 'myautotriage' ),
			),
			array(
				'question' => __( "What if the insurer doesn't respond by my deadline?", 'myautotriage' ),
				'answer'   => __( 'Follow up in writing, consider filing a complaint with your state department of insurance, and — for larger or disputed amounts — consult a consumer or personal injury attorney about next steps, including small claims court.', 'myautotriage' ),
			),
		), mat_tool_extra_faqs( get_post_field( 'post_name' ) ) ) );
		?>
	</div>
</div>
<?php
get_footer();
