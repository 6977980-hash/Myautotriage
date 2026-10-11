<?php
/**
 * Template Name: Tool - Deductible Recovery Calculator
 *
 * Paid your deductible after an accident someone else caused? Works out
 * how much of it you should get back through your insurer's subrogation,
 * what to do at each stage, and writes the status request to send.
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-dr', MAT_URI . '/assets/js/calculators/deductible-recovery.js', array( 'mat-tools' ), MAT_VERSION, true );
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'You used your own collision coverage, paid the deductible, and the accident wasn\'t (all) your fault. Your insurer can now collect from the other driver\'s insurer, and your deductible should come back with it. See how much to expect, where you are in the process, and what to send.', 'myautotriage' ); ?></p>
		</div>

		<form id="mat-dr-form" class="mat-tool-panel" novalidate>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-dr-deductible"><?php esc_html_e( 'Deductible you paid (USD)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-dr-deductible" min="1" max="10000" step="1" placeholder="e.g. 1000" required>
				</div>
				<div class="mat-field">
					<label for="mat-dr-fault"><?php esc_html_e( 'Your share of the fault (%)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-dr-fault" min="0" max="100" step="1" value="0">
					<span class="mat-field__hint"><?php esc_html_e( '0 if the other driver was fully at fault.', 'myautotriage' ); ?></span>
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-dr-paid"><?php esc_html_e( 'Date your insurer paid the repair (or total loss)', 'myautotriage' ); ?></label>
					<input type="date" id="mat-dr-paid" required>
				</div>
				<div class="mat-field">
					<label for="mat-dr-collected"><?php esc_html_e( 'Share your insurer has collected so far (%)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-dr-collected" min="0" max="100" step="1" placeholder="Leave blank if you don't know">
					<span class="mat-field__hint"><?php esc_html_e( 'For a partial recovery or a settlement; blank assumes a full recovery.', 'myautotriage' ); ?></span>
				</div>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-dr-name"><?php esc_html_e( 'Your name', 'myautotriage' ); ?></label>
					<input type="text" id="mat-dr-name" placeholder="Jordan Smith">
				</div>
				<div class="mat-field">
					<label for="mat-dr-claim"><?php esc_html_e( 'Your claim number', 'myautotriage' ); ?></label>
					<input type="text" id="mat-dr-claim" placeholder="e.g. 2026-0001234">
				</div>
			</div>
			<p id="mat-dr-error" class="mat-form-error" role="alert" hidden></p>
			<div class="mat-tool-actions">
				<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Check my deductible', 'myautotriage' ); ?></button>
			</div>
		</form>

		<div id="mat-dr-result" class="mat-result-box" hidden></div>

		<div id="mat-dr-preview-wrap" hidden>
			<h2><?php esc_html_e( 'Message to your insurer', 'myautotriage' ); ?></h2>
			<div id="mat-dr-preview" class="mat-letter-preview"></div>
			<div class="mat-tool-actions">
				<button type="button" id="mat-dr-print" class="mat-btn mat-btn--primary"><?php esc_html_e( 'Print / Save as PDF', 'myautotriage' ); ?></button>
				<button type="button" id="mat-dr-copy" class="mat-btn mat-btn--ghost"><?php esc_html_e( 'Copy text', 'myautotriage' ); ?></button>
			</div>
		</div>

		<?php mat_tool_disclaimer( __( 'The timing stages are typical, not legal deadlines. How subrogation recoveries are shared depends on your state\'s rules and your policy. Watch the deadline for suing the other driver yourself: insurers sometimes drop a recovery without telling you.', 'myautotriage' ) ); ?>

		<div class="mat-page__content">
			<?php the_content(); ?>
			<h2><?php esc_html_e( 'How you get your deductible back', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'When your insurer pays for damage someone else caused, it takes over your right to collect from them. This is called subrogation. It asks the at-fault driver\'s insurer to repay what it paid, and to include your deductible in that demand.', 'myautotriage' ); ?></p>
			<p><?php esc_html_e( 'The NAIC\'s model claims regulation, which many states have adopted in some form, says insurers must include your deductible in subrogation demands when you ask, and must share recoveries with you on a proportionate basis unless your deductible has been recovered some other way. It also bars deducting the insurer\'s expenses from your share unless an outside attorney is hired, and then only a pro rata share.', 'myautotriage' ); ?></p>
			<h2><?php esc_html_e( 'How the amount is worked out', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'Expected refund = deductible × (100% − your share of fault) × the share your insurer actually collected. If you were 20% at fault and the insurer collected everything it was owed, a $1,000 deductible comes back as $800.', 'myautotriage' ); ?></p>
			<h2><?php esc_html_e( 'Or claim it yourself', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'You don\'t have to wait. You can claim the deductible directly from the at-fault driver\'s insurer, as part of a property damage claim that also covers your rental costs and diminished value. Tell your own insurer if you do, so you aren\'t paid twice.', 'myautotriage' ); ?></p>
		</div>

		<?php mat_tool_extras( 'deductible-recovery-calculator' ); ?>

		<?php
		mat_faq_block( array_merge( array(
			array(
				'question' => __( 'How long does it take to get my deductible back?', 'myautotriage' ),
				'answer'   => __( 'Often a few weeks to a few months when fault is clear and the other driver is insured. It can take a year or more if fault is disputed and the insurers go to inter-company arbitration. Ask your insurer for a status update in writing if you haven\'t heard anything in two to three months.', 'myautotriage' ),
			),
			array(
				'question' => __( 'Will I get my whole deductible back?', 'myautotriage' ),
				'answer'   => __( 'Only if the other driver was fully at fault and your insurer recovers in full. If you share some fault, or the insurer settles for less, you usually get the same proportion of your deductible.', 'myautotriage' ),
			),
			array(
				'question' => __( 'What if my insurer decides not to pursue subrogation?', 'myautotriage' ),
				'answer'   => __( 'Ask them to confirm it in writing, then claim the deductible from the at-fault driver\'s insurer yourself or file in small claims court, before your state\'s deadline for property damage lawsuits.', 'myautotriage' ),
			),
		), mat_tool_extra_faqs( 'deductible-recovery-calculator' ) ) );
		mat_render_sources( array(
			array(
				'label' => __( 'NAIC Unfair Property/Casualty Claims Settlement Practices Model Regulation (Model 902), Section 8D', 'myautotriage' ),
				'url'   => 'https://content.naic.org/sites/default/files/model-law-902.pdf',
			),
		) );
		?>
	</div>
</div>
<?php
get_footer();
