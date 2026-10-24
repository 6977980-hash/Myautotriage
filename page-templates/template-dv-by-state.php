<?php
/**
 * Template Name: Guide - Diminished Value by State
 *
 * Whether each state lets you recover post-repair diminished value from
 * the at-fault driver (third-party) and from your own insurer
 * (first-party), with the case or law behind it. Data:
 * assets/js/data/diminished-value-rules.json.
 *
 * @package MyAutoTriage
 */

get_header();

$mat_dv_data = mat_read_theme_json( 'assets/js/data/diminished-value-rules.json' );
$mat_laws    = mat_state_laws();
$mat_counts  = array( 'first_party' => array(), 'third_party' => array() );
foreach ( $mat_laws as $mat_state ) {
	if ( empty( $mat_state['dv'] ) ) {
		continue;
	}
	foreach ( array( 'first_party', 'third_party' ) as $mat_party ) {
		$mat_status = $mat_state['dv'][ $mat_party ];
		$mat_counts[ $mat_party ][ $mat_status ] = isset( $mat_counts[ $mat_party ][ $mat_status ] ) ? $mat_counts[ $mat_party ][ $mat_status ] + 1 : 1;
	}
}
$mat_fp_yes = array();
foreach ( $mat_laws as $mat_state ) {
	if ( ! empty( $mat_state['dv'] ) && 'yes' === $mat_state['dv']['first_party'] ) {
		$mat_fp_yes[] = $mat_state['name'];
	}
}
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'Your car was repaired but is now worth less because it has an accident on its record. Who pays for that lost value depends on whose insurer you claim from and which state you are in.', 'myautotriage' ); ?></p>
		</div>

		<div class="mat-short-answer">
			<p class="mat-short-answer__label"><?php esc_html_e( 'Short answer', 'myautotriage' ); ?></p>
			<p>
				<?php
				printf(
					/* translators: 1: count of states, 2: list of states */
					esc_html__( 'If the other driver caused the crash, courts in most states let you claim diminished value from their insurer on top of the repair bill, if you can prove it. From your own insurer it is much harder: courts in %1$d states have said standard policies don\'t pay it, and only %2$s require it unless the policy clearly excludes it.', 'myautotriage' ),
					(int) ( isset( $mat_counts['first_party']['no'] ) ? $mat_counts['first_party']['no'] : 0 ),
					esc_html( implode( ', ', array_slice( $mat_fp_yes, 0, -1 ) ) . ' and ' . end( $mat_fp_yes ) )
				);
				?>
			</p>
		</div>

		<div class="mat-page__content">
			<?php the_content(); ?>

			<h2><?php esc_html_e( 'Two kinds of claim', 'myautotriage' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Third-party: you claim from the at-fault driver\'s insurer. This is a claim in tort, where the usual rule is that you are owed the repair cost plus any value the car still lost after a proper repair, up to its pre-accident value.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'First-party: you claim from your own collision or comprehensive coverage. This depends on the policy\'s wording; most say the insurer will "repair or replace", and most courts read that as paying for the repair only. Many policies now exclude diminished value in so many words.', 'myautotriage' ); ?></li>
			</ul>

			<h2><?php esc_html_e( 'The rule in every state', 'myautotriage' ); ?></h2>
			<div class="mat-table-wrap">
				<table class="mat-table">
					<thead><tr>
						<th><?php esc_html_e( 'State', 'myautotriage' ); ?></th>
						<th><?php esc_html_e( 'From the at-fault driver\'s insurer', 'myautotriage' ); ?></th>
						<th><?php esc_html_e( 'From your own insurer', 'myautotriage' ); ?></th>
					</tr></thead>
					<tbody>
						<?php foreach ( $mat_laws as $mat_state ) : ?>
							<?php
							if ( empty( $mat_state['dv'] ) ) {
								continue;
							}
							$mat_dv = $mat_state['dv'];
							?>
							<tr id="<?php echo esc_attr( strtolower( $mat_state['code'] ) ); ?>">
								<td><a href="<?php echo esc_url( mat_state_url( $mat_state ) ); ?>"><?php echo esc_html( $mat_state['name'] ); ?></a></td>
								<td><strong><?php echo esc_html( mat_dv_status_label( $mat_dv['third_party'], 'third_party' ) ); ?></strong><br><span class="mat-field__hint"><?php echo esc_html( $mat_dv['third_party_note'] ); ?></span></td>
								<td><strong><?php echo esc_html( mat_dv_status_label( $mat_dv['first_party'], 'first_party' ) ); ?></strong><br><span class="mat-field__hint"><?php echo esc_html( $mat_dv['first_party_note'] ); ?></span></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<p class="mat-field__hint">
				<?php
				/* translators: %s: date */
				printf( esc_html__( 'Rules checked %s. These are the leading cases we found; a newer ruling or your policy\'s own wording can change the answer. "No court ruling found" means just that, not that the claim is barred.', 'myautotriage' ), esc_html( date_i18n( get_option( 'date_format' ), strtotime( isset( $mat_dv_data['reviewed'] ) ? $mat_dv_data['reviewed'] : 'now' ) ) ) );
				?>
			</p>

			<h2><?php esc_html_e( 'How to make the claim', 'myautotriage' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'Wait for the repair to finish and get the final invoice; the claim is for value lost after a proper repair.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Get evidence of the loss: an independent appraisal or dealer trade-in quotes with and without the accident history. The 17c formula is the insurer\'s starting point, not the market.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Send a written demand to the at-fault driver\'s insurer with the invoice, photos and the appraisal.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'If they refuse, file a complaint with your state insurance department or sue in small claims court before the property damage deadline runs out.', 'myautotriage' ); ?></li>
			</ol>
		</div>

		<?php mat_tool_disclaimer( '', 'page' ); ?>

		<?php
		$mat_next = array(
			'diminished-value-calculator'              => __( 'Estimate your diminished value with the 17c calculator', 'myautotriage' ),
			'diminished-value-demand-letter-generator' => __( 'Write a diminished value demand letter', 'myautotriage' ),
			'how-to-file-diminished-value-claim'       => __( 'Read: how to file a diminished value claim, step by step', 'myautotriage' ),
			'car-accident-lawsuit-deadline-calculator' => __( 'Check the deadline to sue for car damage', 'myautotriage' ),
		);
		$mat_links = array();
		foreach ( $mat_next as $slug => $label ) {
			$url = mat_url_for_slug( $slug );
			if ( $url ) {
				$mat_links[] = '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
			}
		}
		if ( $mat_links ) :
			?>
			<section class="mat-page__content">
				<h2><?php esc_html_e( 'What to do next', 'myautotriage' ); ?></h2>
				<ul><?php echo implode( '', $mat_links ); // phpcs:ignore -- escaped above ?></ul>
			</section>
		<?php endif; ?>

		<?php
		mat_faq_block( array(
			array(
				'question' => __( 'Which states allow diminished value claims against your own insurer?', 'myautotriage' ),
				/* translators: %s: list of states */
				'answer'   => sprintf( __( '%s, unless the policy clearly excludes it. Georgia goes furthest: since State Farm v. Mabry (2001) insurers there must assess diminished value on physical damage claims. In most other states, courts have read standard "repair or replace" wording as paying for the repair only.', 'myautotriage' ), implode( ', ', array_slice( $mat_fp_yes, 0, -1 ) ) . ' and ' . end( $mat_fp_yes ) ),
			),
			array(
				'question' => __( 'Can I claim diminished value if the accident wasn\'t my fault?', 'myautotriage' ),
				'answer'   => __( 'In most states, yes, from the at-fault driver\'s insurer, as part of your property damage claim. You have to show the car is worth less after the repair, usually with an independent appraisal. Michigan limits the whole car damage claim to $3,000 (the mini-tort).', 'myautotriage' ),
			),
			array(
				'question' => __( 'Does the 17c formula decide my diminished value?', 'myautotriage' ),
				'answer'   => __( 'No. 17c came from a Georgia class action settlement and is what many insurers use as a starting point. It caps the loss at 10% of the car\'s value. A real market appraisal often comes out higher, especially for newer cars.', 'myautotriage' ),
			),
		) );

		mat_render_sources( isset( $mat_dv_data['sources'] ) ? $mat_dv_data['sources'] : array() );
		?>
	</div>
</div>
<?php
get_footer();
