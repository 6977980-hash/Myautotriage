<?php
/**
 * Template Name: Lowball Index
 *
 * Published totals from the Claim Outcome Survey. A group is shown only
 * once it has MAT_OUTCOMES_MIN settled answers; until then the page says
 * how many answers are in so far.
 *
 * @package MyAutoTriage
 */

get_header();
$mat_lb_stats   = mat_outcomes_stats();
$mat_lb_choices = mat_outcomes_choices();
$mat_lb_survey  = mat_outcomes_survey_url();

$mat_lb_pct = function ( $v ) {
	$p = round( $v * 100 );
	return ( $p > 0 ? '+' : '' ) . $p . '%';
};
$mat_lb_row = function ( $label, $s ) use ( $mat_lb_pct ) {
	echo '<tr><th scope="row">' . esc_html( $label ) . '</th>';
	echo '<td>' . (int) $s['n'] . '</td>';
	if ( isset( $s['median_change'] ) ) {
		echo '<td>' . esc_html( $mat_lb_pct( $s['median_change'] ) ) . '</td>';
		echo '<td>' . esc_html( round( $s['share_up'] * 100 ) . '%' ) . '</td>';
		// A split with only a handful of answers is noise, so it waits for 10.
		echo '<td>' . ( $s['pushed_n'] >= 10 ? esc_html( $mat_lb_pct( $s['pushed_median'] ) ) : '–' ) . '</td>';
		echo '<td>' . ( $s['quiet_n'] >= 10 ? esc_html( $mat_lb_pct( $s['quiet_median'] ) ) : '–' ) . '</td>';
	} else {
		/* translators: %d: answers still needed */
		echo '<td colspan="4">' . esc_html( sprintf( __( 'Needs %d more answers', 'myautotriage' ), MAT_OUTCOMES_MIN - (int) $s['n'] ) ) . '</td>';
	}
	echo '</tr>';
};
$mat_lb_head = '<thead><tr><th scope="col">' . esc_html__( 'Group', 'myautotriage' ) . '</th><th scope="col">' . esc_html__( 'Answers', 'myautotriage' ) . '</th><th scope="col">' . esc_html__( 'Median change from first offer', 'myautotriage' ) . '</th><th scope="col">' . esc_html__( 'Share that went up', 'myautotriage' ) . '</th><th scope="col">' . esc_html__( 'Median if they pushed back', 'myautotriage' ) . '</th><th scope="col">' . esc_html__( 'Median if they didn\'t', 'myautotriage' ) . '</th></tr></thead>';
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'How much do car insurance claim offers move between the first number and the final payment? The Lowball Index answers that from real claims our readers report anonymously through the Claim Outcome Survey.', 'myautotriage' ); ?></p>
		</div>

		<?php if ( ! $mat_lb_stats['all'] ) : ?>
			<div class="mat-triage-result">
				<p class="mat-triage-result__label"><?php esc_html_e( 'Collecting answers', 'myautotriage' ); ?></p>
				<h2 class="mat-triage-result__title">
					<?php
					/* translators: 1: settled answers so far, 2: answers needed */
					echo esc_html( sprintf( _n( '%1$d settled claim reported so far. We publish from %2$d.', '%1$d settled claims reported so far. We publish from %2$d.', $mat_lb_stats['settled'], 'myautotriage' ), $mat_lb_stats['settled'], MAT_OUTCOMES_MIN ) );
					?>
				</h2>
				<p><?php esc_html_e( 'We won\'t publish a number until enough answers back it up. If you have settled a car insurance claim, your answer brings the first results closer.', 'myautotriage' ); ?></p>
				<a class="mat-btn mat-btn--primary" href="<?php echo esc_url( $mat_lb_survey ); ?>"><?php esc_html_e( 'Add my claim (30 seconds)', 'myautotriage' ); ?></a>
			</div>
		<?php else : ?>
			<div class="mat-triage-result">
				<p class="mat-triage-result__label"><?php esc_html_e( 'All settled claims', 'myautotriage' ); ?></p>
				<h2 class="mat-triage-result__title">
					<?php
					$mat_lb_m = $mat_lb_stats['all']['median_change'];
					if ( abs( $mat_lb_m ) < 0.005 ) {
						/* translators: %d: number of answers */
						$mat_lb_line = sprintf( __( 'Across %d settled claims, the median final payment matched the first offer.', 'myautotriage' ), $mat_lb_stats['all']['n'] );
					} else {
						/* translators: 1: number of answers, 2: percentage, 3: higher or lower */
						$mat_lb_line = sprintf( __( 'Across %1$d settled claims, the median final payment was %2$s %3$s than the first offer.', 'myautotriage' ), $mat_lb_stats['all']['n'], round( abs( $mat_lb_m ) * 100 ) . '%', $mat_lb_m > 0 ? __( 'higher', 'myautotriage' ) : __( 'lower', 'myautotriage' ) );
					}
					echo esc_html( $mat_lb_line );
					?>
				</h2>
				<p>
					<?php
					/* translators: %s: percentage */
					echo esc_html( sprintf( __( '%s of claimants ended up with more than the first offer.', 'myautotriage' ), round( $mat_lb_stats['all']['share_up'] * 100 ) . '%' ) );
					?>
				</p>
			</div>

			<div class="mat-page__content mat-lb-tables">
				<h2><?php esc_html_e( 'By type of claim', 'myautotriage' ); ?></h2>
				<div class="mat-table-wrap"><table class="mat-table"><?php echo $mat_lb_head; // phpcs:ignore -- escaped above ?><tbody>
					<?php
					foreach ( $mat_lb_choices['claim_type'] as $k => $label ) {
						if ( ! empty( $mat_lb_stats['by_type'][ $k ] ) ) {
							$mat_lb_row( $label, $mat_lb_stats['by_type'][ $k ] );
						}
					}
					?>
				</tbody></table></div>
				<?php if ( $mat_lb_stats['by_insurer'] ) : ?>
					<h2><?php esc_html_e( 'By insurance company', 'myautotriage' ); ?></h2>
					<div class="mat-table-wrap"><table class="mat-table"><?php echo $mat_lb_head; // phpcs:ignore ?><tbody>
						<?php
						foreach ( $mat_lb_stats['by_insurer'] as $name => $s ) {
							$mat_lb_row( $name, $s );
						}
						?>
					</tbody></table></div>
				<?php endif; ?>
				<p><a class="mat-btn mat-btn--primary" href="<?php echo esc_url( $mat_lb_survey ); ?>"><?php esc_html_e( 'Add my claim', 'myautotriage' ); ?></a></p>
			</div>
		<?php endif; ?>

		<section class="mat-page__content">
			<h2><?php esc_html_e( 'How the numbers are worked out', 'myautotriage' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Change from first offer = (final payment − first offer) ÷ first offer, for each claim. We show the median, the middle answer, so one huge settlement can\'t skew it.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( '"Pushed back" means the claimant reported at least one step: a written counter-offer, their own estimates or comparables, appraisal, a state complaint, a lawyer or public adjuster, or court.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Only settled claims count. Answers about claims that are still open are stored but left out of these numbers.', 'myautotriage' ); ?></li>
				<?php /* translators: %d: minimum answers */ ?>
				<li><?php echo esc_html( sprintf( __( 'A group (a claim type or an insurer) appears only once it has %d settled answers.', 'myautotriage' ), MAT_OUTCOMES_MIN ) ); ?></li>
				<li><?php esc_html_e( 'The pushed-back and didn\'t-push-back columns show a number once that part of the group has at least 10 answers.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Answers where the final amount is more than 10 times, or less than a tenth of, the first offer are treated as probable typos and left out.', 'myautotriage' ); ?></li>
			</ul>
			<p><?php esc_html_e( 'These are self-reported answers from people who found this site, not a random sample. People who pushed back may be more likely to answer, and claims differ in ways six questions can\'t capture. Treat the numbers as a guide to what\'s common, not a prediction for your claim.', 'myautotriage' ); ?></p>
		</section>
	</div>
</div>
<?php
get_footer();
