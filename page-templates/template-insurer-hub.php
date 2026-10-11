<?php
/**
 * Template Name: Insurer Complaint Hub
 *
 * The big auto insurers side by side by complaints to the Texas and New
 * York insurance regulators. Data: inc/insurer-complaints.php.
 *
 * @package MyAutoTriage
 */

get_header();
$mat_ic_data = mat_insurer_data();
$mat_ic_mkt  = isset( $mat_ic_data['tx_market'] ) ? $mat_ic_data['tx_market'] : array();
$mat_ic_cell = function ( $score ) {
	if ( ! $score ) {
		return '<td>' . esc_html__( 'Not sold or not listed', 'myautotriage' ) . '</td>';
	}
	$num = 'few' === $score['verdict'] ? '' : '<strong>' . esc_html( number_format_i18n( $score['index'], 2 ) ) . '</strong> ';
	return '<td>' . $num . '<span class="mat-ic-badge mat-ic-badge--' . esc_attr( $score['verdict'] ) . '">' . esc_html( $score['label'] ) . '</span></td>';
};
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'Which car insurance companies do state regulators get the most complaints about, for their size? We compared the biggest auto insurers using the latest official data from the Texas Department of Insurance and the New York Department of Financial Services.', 'myautotriage' ); ?></p>
			<p class="mat-field__hint">
				<?php
				/* translators: %s: date */
				printf( esc_html__( 'Data last updated %s.', 'myautotriage' ), esc_html( date_i18n( get_option( 'date_format' ), strtotime( $mat_ic_data['built'] ) ) ) );
				?>
			</p>
		</div>

		<?php if ( ! empty( $mat_ic_mkt['claim_complaints'] ) ) : ?>
			<div class="mat-short-answer">
				<p class="mat-short-answer__label"><?php esc_html_e( 'Does complaining work?', 'myautotriage' ); ?></p>
				<p>
					<?php
					/* translators: 1: year, 2: number of complaints, 3: percentage */
					echo esc_html( sprintf( __( 'Often, yes. Of the %2$s auto claim complaints the Texas Department of Insurance received in %1$s, %3$s ended with the claim settled or the insurer paying more, according to TDI\'s own record of how each complaint was resolved.', 'myautotriage' ), $mat_ic_data['tx_year'], number_format_i18n( $mat_ic_mkt['claim_complaints'] ), mat_insurer_pct( $mat_ic_mkt['claim_paid'], $mat_ic_mkt['claim_complaints'] ) ) );
					?>
				</p>
			</div>
		<?php endif; ?>

		<div class="mat-page__content">
			<h2><?php esc_html_e( 'Complaint ratings by insurer', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'A score of 1.00 is the average for all auto insurers in that state. Below 1 means fewer complaints than expected for the company\'s size; 2.00 means twice as many.', 'myautotriage' ); ?></p>
			<div class="mat-table-wrap">
				<table class="mat-table mat-ic-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Insurer', 'myautotriage' ); ?></th>
							<?php /* translators: %s: year */ ?>
							<th scope="col"><?php echo esc_html( sprintf( __( 'Texas %s', 'myautotriage' ), $mat_ic_data['tx_year'] ) ); ?></th>
							<?php /* translators: %s: year */ ?>
							<th scope="col"><?php echo esc_html( sprintf( __( 'New York %s', 'myautotriage' ), $mat_ic_data['ny_year'] ) ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( mat_insurers() as $mat_ic_ins ) : ?>
							<tr>
								<th scope="row"><a href="<?php echo esc_url( mat_insurer_url( $mat_ic_ins ) ); ?>"><?php echo esc_html( $mat_ic_ins['name'] ); ?></a></th>
								<?php
								echo $mat_ic_cell( mat_insurer_score( $mat_ic_ins, 'tx' ) ); // phpcs:ignore -- escaped in the closure
								echo $mat_ic_cell( mat_insurer_score( $mat_ic_ins, 'ny' ) ); // phpcs:ignore
								?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<?php if ( ! empty( $mat_ic_mkt['reasons'] ) ) : ?>
				<?php /* translators: %s: year */ ?>
				<h2><?php echo esc_html( sprintf( __( 'What drivers complained about most (Texas, %s)', 'myautotriage' ), $mat_ic_data['tx_year'] ) ); ?></h2>
				<ol>
					<?php foreach ( $mat_ic_mkt['reasons'] as $mat_ic_r ) : ?>
						<?php /* translators: 1: reason, 2: number of complaints */ ?>
						<li><?php echo esc_html( sprintf( __( '%1$s: %2$s complaints', 'myautotriage' ), $mat_ic_r['reason'], number_format_i18n( $mat_ic_r['count'] ) ) ); ?></li>
					<?php endforeach; ?>
				</ol>
				<?php /* translators: %s: number of complaints */ ?>
				<p><?php echo esc_html( sprintf( __( 'Out of %s auto complaints in all. One complaint can list more than one reason.', 'myautotriage' ), number_format_i18n( $mat_ic_mkt['complaints'] ) ) ); ?></p>
			<?php endif; ?>

			<h2><?php esc_html_e( 'How these ratings work', 'myautotriage' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Texas: the Texas Department of Insurance\'s complaint index. It divides a company\'s share of confirmed auto complaints by its share of auto policies in force. A confirmed complaint is one where TDI has information that the insurer broke an insurance law, rule or policy term, or where the complaint and the insurer\'s answer together suggest the insurer made a mistake or the complaint was valid (28 Tex. Admin. Code § 1.603).', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'New York: the Department of Financial Services ranks insurers by upheld complaints per million dollars of premium over two years. We divided each brand\'s rate by the rate for all ranked insurers, so 1.00 is the New York average too.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( '"Ended with the claim settled or the insurer paying more" counts complaints about claims (handling, delays, offers, denials, fault) where TDI recorded the result as claim settled, additional money received or additional payment expected.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Each brand combines the companies it sells through in that state (for example State Farm Mutual and State Farm County Mutual of Texas). Each insurer\'s page lists the companies included.', 'myautotriage' ); ?></li>
				<?php /* translators: %d: minimum complaints */ ?>
				<li><?php echo esc_html( sprintf( __( 'With fewer than %d complaints, one or two complaints swing the score, so we show "Too few complaints to compare" instead of a number.', 'myautotriage' ), MAT_INSURER_MIN_COMPLAINTS ) ); ?></li>
				<li><?php esc_html_e( 'Complaints measure how often things go wrong badly enough for people to go to the regulator. They don\'t measure prices, and a brand\'s record in one state can differ from another.', 'myautotriage' ); ?></li>
			</ul>
		</div>

		<section class="mat-page__content mat-next-steps" aria-label="<?php esc_attr_e( 'If your insurer is stalling or lowballing', 'myautotriage' ); ?>">
			<h2><?php esc_html_e( 'If your insurer is stalling or lowballing', 'myautotriage' ); ?></h2>
			<ul>
				<?php foreach ( mat_insurer_next_steps() as $mat_ic_step ) : ?>
					<li><a href="<?php echo esc_url( $mat_ic_step[0] ); ?>"><?php echo esc_html( $mat_ic_step[1] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</section>

		<?php
		mat_faq_block( array(
			array(
				'question' => __( 'Which car insurance company has the fewest complaints?', 'myautotriage' ),
				'answer'   => __( 'It depends on the state. In the table above, look for the lowest score with enough complaints to compare. Big national brands can score very differently in Texas and New York because they sell through different companies and agents in each state.', 'myautotriage' ),
			),
			array(
				'question' => __( 'Where do these numbers come from?', 'myautotriage' ),
				'answer'   => __( 'From the open-data portals of the Texas Department of Insurance and the New York Department of Financial Services. We don\'t use reviews or our own surveys for these ratings.', 'myautotriage' ),
			),
			array(
				'question' => __( 'How do I file a complaint against my car insurance company?', 'myautotriage' ),
				'answer'   => __( 'Every state insurance department takes complaints online, for free. Include your claim number, a dated timeline of calls and letters, and what you want the insurer to do. The department asks the insurer to respond in writing, usually within a few weeks.', 'myautotriage' ),
			),
			array(
				'question' => __( 'Why isn\'t my state shown?', 'myautotriage' ),
				'answer'   => __( 'Texas and New York publish complaint data per company in a form anyone can reuse. Many states publish only totals, or none at all. We will add more states as their data becomes available.', 'myautotriage' ),
			),
		) );
		mat_render_sources( mat_insurer_sources() );
		mat_outcomes_cta();
		?>
	</div>
</div>
<?php
get_footer();
