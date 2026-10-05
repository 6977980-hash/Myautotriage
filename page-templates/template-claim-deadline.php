<?php
/**
 * Template Name: Tool - Claim Payment Deadline Lookup
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-cd', MAT_URI . '/assets/js/calculators/claim-deadline.js', array(), MAT_VERSION, true );
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'Look up how many days your state gives an insurer to acknowledge, decide on, and pay your claim.', 'myautotriage' ); ?></p>
		</div>

		<form id="mat-cd-form" class="mat-tool-panel" data-json="<?php echo esc_url( MAT_URI . '/assets/js/data/claim-deadlines.json' ); ?>" novalidate>
			<div class="mat-field">
				<label for="mat-cd-state"><?php esc_html_e( 'Your state', 'myautotriage' ); ?></label>
				<select id="mat-cd-state" required>
					<option value=""><?php esc_html_e( 'Loading states…', 'myautotriage' ); ?></option>
				</select>
			</div>
			<div class="mat-tool-actions">
				<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Look up deadlines', 'myautotriage' ); ?></button>
			</div>
		</form>

		<div id="mat-cd-result" class="mat-result-box" hidden></div>

		<?php mat_tool_disclaimer( __( 'Insurance claims-handling regulations are updated periodically — verify current deadlines with your state department of insurance.', 'myautotriage' ) ); ?>

		<div class="mat-page__content"><?php the_content(); ?></div>

		<?php
		mat_faq_block( array(
			array(
				'question' => __( 'What can I do if my insurer misses these deadlines?', 'myautotriage' ),
				'answer'   => __( "You can file a complaint with your state department of insurance, which regulates these timelines. A pattern of missed deadlines can also support a 'bad faith' claim in some states.", 'myautotriage' ),
			),
			array(
				'question' => __( 'Do these deadlines apply to health or home insurance too?', 'myautotriage' ),
				'answer'   => __( 'This tool focuses on auto insurance claims specifically. Many states use similar (sometimes identical) rules across insurance lines, but always confirm for your specific type of claim.', 'myautotriage' ),
			),
		) );
		?>
	</div>
</div>
<?php
get_footer();
