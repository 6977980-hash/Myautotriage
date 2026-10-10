<?php
/**
 * Template Name: Tool - Claim Triage
 *
 * Three questions that point to the next step on a car insurance claim.
 *
 * @package MyAutoTriage
 */

get_header();
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'Tell us where your car insurance claim stands. We\'ll show the one thing to do next, the tool or letter for it, your state\'s deadlines, and what the adjuster\'s words usually mean.', 'myautotriage' ); ?></p>
		</div>

		<?php mat_claim_triage_form(); ?>

		<div class="mat-page__content">
			<?php the_content(); ?>
			<h2><?php esc_html_e( 'How a car insurance claim usually goes', 'myautotriage' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'Report the accident and collect evidence: photos, the police report number, witnesses.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'The insurer acknowledges the claim, inspects the car and investigates fault.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'It accepts or denies the claim and makes an offer: a repair estimate or, for a total loss, the car\'s actual cash value.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'You accept, or answer with a written counter-offer and evidence. Appraisal, a complaint to the state insurance department or small claims court are the next steps if you can\'t agree.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Before you sign a release, check you\'ve claimed everything: rental or loss of use, diminished value, towing and storage.', 'myautotriage' ); ?></li>
			</ol>
		</div>

		<?php mat_tool_disclaimer( __( 'This points you to general next steps. It is not legal advice, and your policy and your state\'s law decide what applies.', 'myautotriage' ) ); ?>
	</div>
</div>
<?php
get_footer();
