<?php
/**
 * Template Name: Guide - PIP Claim Deadlines by State
 *
 * Notice, payment and late-interest rules for personal injury protection
 * (no-fault) claims in the states that require PIP. Data:
 * assets/js/data/pip-deadlines.json.
 *
 * @package MyAutoTriage
 */

get_header();

$mat_pip_data = mat_read_theme_json( 'assets/js/data/pip-deadlines.json' );
$mat_laws     = mat_state_laws();
$mat_pip_rows = array_filter( $mat_laws, function ( $state ) {
	return ! empty( $state['pip'] );
} );
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'In no-fault and PIP states your own insurer pays your medical bills and lost wages after a crash, whoever caused it. These are the deadlines on your side and on the insurer\'s.', 'myautotriage' ); ?></p>
		</div>

		<div class="mat-short-answer">
			<p class="mat-short-answer__label"><?php esc_html_e( 'Short answer', 'myautotriage' ); ?></p>
			<p><?php esc_html_e( 'Tell your insurer about the accident in writing straight away: New York allows only 30 days, and Florida pays medical benefits only if you get care within 14 days. Once the insurer has proof of your bills, most PIP states give it 30 days to pay (New Jersey 60), and late payments usually earn interest set by state law, as much as 2% a month in New York.', 'myautotriage' ); ?></p>
		</div>

		<div class="mat-page__content">
			<?php the_content(); ?>

			<h2><?php esc_html_e( 'Deadlines in each PIP state', 'myautotriage' ); ?></h2>
			<p class="mat-stage-nav" style="justify-content:flex-start;">
				<?php foreach ( $mat_pip_rows as $mat_state ) : ?>
					<a href="#pip-<?php echo esc_attr( strtolower( $mat_state['code'] ) ); ?>"><?php echo esc_html( $mat_state['name'] ); ?></a>
				<?php endforeach; ?>
			</p>

			<?php foreach ( $mat_pip_rows as $mat_state ) : ?>
				<?php $mat_pip = $mat_state['pip']; ?>
				<section id="pip-<?php echo esc_attr( strtolower( $mat_state['code'] ) ); ?>" class="mat-stage">
					<h3><a href="<?php echo esc_url( mat_state_url( $mat_state ) ); ?>"><?php echo esc_html( $mat_state['name'] ); ?></a> <span class="mat-field__hint"><?php echo esc_html( $mat_pip['system'] ); ?></span></h3>
					<div class="mat-table-wrap">
						<table class="mat-table">
							<tbody>
								<?php
								$mat_rows = array(
									'notice' => __( 'Your deadline', 'myautotriage' ),
									'pay'    => __( 'Insurer must pay', 'myautotriage' ),
									'late'   => __( 'If it pays late', 'myautotriage' ),
									'suit'   => __( 'Deadline to sue for benefits', 'myautotriage' ),
								);
								foreach ( $mat_rows as $mat_key => $mat_label ) :
									if ( empty( $mat_pip[ $mat_key ] ) ) {
										continue;
									}
									?>
									<tr><th scope="row"><?php echo esc_html( $mat_label ); ?></th><td><?php echo esc_html( $mat_pip[ $mat_key ] ); ?></td></tr>
								<?php endforeach; ?>
								<tr><th scope="row"><?php esc_html_e( 'Law', 'myautotriage' ); ?></th><td><a href="<?php echo esc_url( $mat_pip['url'] ); ?>" rel="noopener" target="_blank"><?php echo esc_html( $mat_pip['cite'] ); ?></a></td></tr>
							</tbody>
						</table>
					</div>
				</section>
			<?php endforeach; ?>

			<p class="mat-field__hint">
				<?php
				/* translators: %s: date */
				printf( esc_html__( 'Checked %s against each state\'s statute. Where a row is missing, the law sets no specific number and your policy\'s notice clause applies, so give notice as soon as you can. Other states offer PIP or medical payments coverage as an option; your policy sets those deadlines.', 'myautotriage' ), esc_html( date_i18n( get_option( 'date_format' ), strtotime( isset( $mat_pip_data['reviewed'] ) ? $mat_pip_data['reviewed'] : 'now' ) ) ) );
				?>
			</p>

			<h2><?php esc_html_e( 'How to keep a PIP claim on track', 'myautotriage' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'Report the accident to your own insurer in writing, even if the other driver was at fault, and ask for the PIP application form.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'See a doctor quickly and keep every bill, receipt and note from your employer about missed work.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Send bills and wage proof as you get them, and log the date each was sent; the insurer\'s 30 days run from proof, not from the accident.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'If a payment is late, ask in writing for the payment plus the interest your state sets, then complain to your state insurance department.', 'myautotriage' ); ?></li>
			</ol>
		</div>

		<?php mat_tool_disclaimer( __( 'PIP limits, deductibles and coverage choices vary by policy.', 'myautotriage' ), 'page' ); ?>

		<?php
		$mat_next = array(
			'claim-diary'                                => __( 'Log every PIP bill and the date you sent it', 'myautotriage' ),
			'insurance-late-payment-interest-calculator' => __( 'Calculate the interest on a late payment', 'myautotriage' ),
			'claim-payment-deadline-by-state'            => __( 'Claim deadlines for car damage claims', 'myautotriage' ),
			'car-accident-lawsuit-deadline-calculator'   => __( 'Deadline to sue the at-fault driver', 'myautotriage' ),
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
				'question' => __( 'How long do I have to file a PIP claim?', 'myautotriage' ),
				'answer'   => __( 'It depends on the state and your policy. New York requires written notice within 30 days of the accident, Michigan within 1 year, Massachusetts within 2 years, and Maryland policies can\'t set less than 12 months. In Florida you must get medical care within 14 days to use PIP medical benefits at all.', 'myautotriage' ),
			),
			array(
				'question' => __( 'How long does the insurer have to pay a PIP claim?', 'myautotriage' ),
				'answer'   => __( 'Usually 30 days after it receives proof of the loss and the amount. New Jersey allows 60 days (and up to 45 more if it asks in writing). After that the payment is overdue and earns interest set by state law.', 'myautotriage' ),
			),
			array(
				'question' => __( 'Do I file PIP with my insurer if the other driver was at fault?', 'myautotriage' ),
				'answer'   => __( 'Yes. PIP is paid by your own policy whoever caused the crash. You may still claim from the at-fault driver for losses PIP doesn\'t cover, within your state\'s limits on suing.', 'myautotriage' ),
			),
		) );
		?>
	</div>
</div>
<?php
get_footer();
