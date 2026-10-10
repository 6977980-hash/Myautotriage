<?php
/**
 * Template Name: State - Car Insurance Claim Laws
 *
 * One page per state, filled from the state data files (see
 * inc/state-laws.php).
 *
 * @package MyAutoTriage
 */

get_header();
$state = mat_current_state();
$meta  = mat_state_laws_meta();
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<?php if ( $state ) : ?>
				<p>
					<?php
					/* translators: %s: state name */
					printf( esc_html__( 'How long insurers have to handle your car insurance claim in %s, when a car counts as a total loss, and what to do if the insurer falls behind.', 'myautotriage' ), esc_html( $state['name'] ) );
					?>
				</p>
				<?php if ( $meta['reviewed'] ) : ?>
					<p class="mat-field__hint">
						<?php
						/* translators: %s: date */
						printf( esc_html__( 'Last reviewed %s by the MyAutoTriage Editorial Team.', 'myautotriage' ), esc_html( date_i18n( get_option( 'date_format' ), strtotime( $meta['reviewed'] ) ) ) );
						if ( mat_reviewer_byline() ) {
							echo ' ' . mat_reviewer_byline() . '.'; // phpcs:ignore -- escaped in mat_reviewer_byline()
						}
						?>
					</p>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<?php if ( $state ) : ?>
			<?php $d = $state['deadlines']; ?>

			<div class="mat-short-answer">
				<p class="mat-short-answer__label"><?php esc_html_e( 'Short answer', 'myautotriage' ); ?></p>
				<p>
					<?php
					$has_days = $d && ( preg_match( '/^\d/', $d['acknowledge'] ) || preg_match( '/^\d/', $d['decide'] ) || preg_match( '/^\d/', $d['pay'] ) );
					if ( $d && ! $has_days ) {
						/* translators: %s: state */
						printf( esc_html__( '%s law sets no fixed number of days for an insurer to acknowledge, decide or pay a car insurance claim; it must still act promptly and in good faith.', 'myautotriage' ), esc_html( $state['name'] ) );
						echo ' ';
					} elseif ( $d ) {
						/* translators: 1: state, 2: acknowledge, 3: decide, 4: pay */
						printf( esc_html__( 'In %1$s an insurer must acknowledge your claim %2$s, accept or deny it %3$s, and pay %4$s.', 'myautotriage' ), esc_html( $state['name'] ), esc_html( mat_state_within( $d['acknowledge'] ) ), esc_html( mat_state_within( $d['decide'] ) ), esc_html( mat_state_within( $d['pay'] ) ) );
						echo ' ';
					}
					if ( $state['total_loss'] ) {
						/* translators: %s: total loss rule */
						printf( esc_html__( 'Total loss rule: %s.', 'myautotriage' ), esc_html( lcfirst( mat_state_total_loss_rule( $state ) ) ) );
					}
					if ( ! empty( $state['facts']['lawsuit_deadline']['injury_years'] ) ) {
						echo ' ';
						/* translators: %s: years */
						printf( esc_html__( 'Deadline to file an injury lawsuit: %s.', 'myautotriage' ), esc_html( mat_years( $state['facts']['lawsuit_deadline']['injury_years'] ) ) );
					}
					?>
				</p>
			</div>

			<div class="mat-page__content">
				<h2>
					<?php
					/* translators: %s: state name */
					printf( esc_html__( 'Claim-handling deadlines in %s', 'myautotriage' ), esc_html( $state['name'] ) );
					?>
				</h2>
				<?php if ( $d ) : ?>
					<div class="mat-table-wrap">
						<table class="mat-table">
							<tbody>
								<tr><th scope="row"><?php esc_html_e( 'Acknowledge your claim', 'myautotriage' ); ?></th><td><?php echo esc_html( $d['acknowledge'] ); ?></td></tr>
								<tr><th scope="row"><?php esc_html_e( 'Accept or deny it', 'myautotriage' ); ?></th><td><?php echo esc_html( $d['decide'] ); ?></td></tr>
								<tr><th scope="row"><?php esc_html_e( 'Pay after agreement', 'myautotriage' ); ?></th><td><?php echo esc_html( $d['pay'] ); ?></td></tr>
							</tbody>
						</table>
					</div>
					<?php if ( ! empty( $d['note'] ) ) : ?>
						<p><?php echo esc_html( $d['note'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $d['citation'] ) ) : ?>
						<p class="mat-field__hint">
							<?php
							if ( 0 === strpos( $d['citation'], 'No ' ) ) {
								echo esc_html( $d['citation'] ) . '.';
							} else {
								/* translators: %s: legal citation */
								printf( esc_html__( 'Rule: %s.', 'myautotriage' ), esc_html( $d['citation'] ) );
							}
							?>
						</p>
					<?php endif; ?>
				<?php elseif ( $meta['default'] ) : ?>
					<p><?php esc_html_e( 'We have not yet confirmed state-specific numbers here. Most states follow the NAIC model rules:', 'myautotriage' ); ?></p>
					<ul>
						<li><?php echo esc_html( $meta['default']['acknowledge'] ); ?></li>
						<li><?php echo esc_html( $meta['default']['decide'] ); ?></li>
						<li><?php echo esc_html( $meta['default']['pay'] ); ?></li>
					</ul>
				<?php endif; ?>
				<p><?php esc_html_e( 'The decision clock usually starts once the insurer has the proof of loss it asked for, not on the day of the accident. Ask the adjuster in writing what they still need, and keep a dated log of everything you send.', 'myautotriage' ); ?></p>

				<?php if ( $state['total_loss'] ) : ?>
					<h2>
						<?php
						/* translators: %s: state name */
						printf( esc_html__( 'When a car is totaled in %s', 'myautotriage' ), esc_html( $state['name'] ) );
						?>
					</h2>
					<p>
						<?php
						echo esc_html( mat_state_total_loss_rule( $state ) ) . '.';
						if ( ! empty( $state['total_loss']['note'] ) ) {
							echo ' ' . esc_html( $state['total_loss']['note'] );
						}
						?>
					</p>
					<?php
					$tl = $state['total_loss'];
					if ( 'percentage' === $tl['type'] && ! empty( $tl['threshold'] ) ) :
						$limit = 15000 * $tl['threshold'];
						?>
						<p>
							<?php
							/* translators: 1: percentage, 2: dollar amount */
							printf( esc_html__( 'Example: for a car worth $15,000, a repair estimate of %2$s or more (%1$d%%) makes it a total loss.', 'myautotriage' ), (int) round( $tl['threshold'] * 100 ), esc_html( '$' . number_format_i18n( $limit ) ) );
							?>
						</p>
					<?php elseif ( 'formula' === $tl['type'] ) : ?>
						<p><?php esc_html_e( 'Example: a car worth $15,000 with $3,000 of salvage value is a total loss once the repair estimate reaches $12,000, because $12,000 + $3,000 equals its value.', 'myautotriage' ); ?></p>
					<?php else : ?>
						<p><?php esc_html_e( 'In practice most insurers apply the total loss formula: a car worth $15,000 with $3,000 of salvage value is usually totaled once repairs reach about $12,000. Ask the adjuster which test they used and for the valuation report.', 'myautotriage' ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $tl['citation'] ) ) : ?>
						<p class="mat-field__hint">
							<?php
							/* translators: %s: legal citation */
							printf( esc_html__( 'Rule: %s.', 'myautotriage' ), esc_html( $tl['citation'] ) );
							?>
						</p>
					<?php endif; ?>
				<?php endif; ?>

				<h2><?php esc_html_e( 'If the insurer misses a deadline or lowballs you', 'myautotriage' ); ?></h2>
				<ol>
					<li><?php esc_html_e( 'Send a short written follow-up that names the deadline and asks for a decision by a specific date.', 'myautotriage' ); ?></li>
					<li><?php esc_html_e( 'If the offer is low, answer with evidence: repair estimates, comparable cars for sale, receipts.', 'myautotriage' ); ?></li>
					<li>
						<?php
						/* translators: %s: state name */
						printf( esc_html__( 'Still stuck? File a free complaint with the %s department of insurance.', 'myautotriage' ), esc_html( $state['name'] ) );
						?>
					</li>
				</ol>

				<?php
				$facts = $state['facts'];
				$sol   = isset( $facts['lawsuit_deadline'] ) ? $facts['lawsuit_deadline'] : array();
				$small = isset( $facts['small_claims'] ) ? $facts['small_claims'] : array();
				if ( mat_state_fault_rule( $state ) || $sol ) :
					?>
					<h2>
						<?php
						/* translators: %s: state name */
						printf( esc_html__( 'If you have to sue the other driver in %s', 'myautotriage' ), esc_html( $state['name'] ) );
						?>
					</h2>
					<p><?php esc_html_e( 'Most claims settle without a lawsuit, but these rules decide how much leverage you have when you claim against the at-fault driver\'s insurer.', 'myautotriage' ); ?></p>
					<div class="mat-table-wrap">
						<table class="mat-table">
							<tbody>
								<?php if ( mat_state_fault_rule( $state ) ) : ?>
									<tr><th scope="row"><?php esc_html_e( 'If you were partly at fault', 'myautotriage' ); ?></th><td><?php echo esc_html( mat_state_fault_rule( $state ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( ! empty( $sol['injury_years'] ) ) : ?>
									<tr><th scope="row"><?php esc_html_e( 'Deadline to sue for injuries', 'myautotriage' ); ?></th><td><?php echo esc_html( mat_years( $sol['injury_years'] ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( ! empty( $sol['property_years'] ) ) : ?>
									<tr><th scope="row"><?php esc_html_e( 'Deadline to sue for car damage', 'myautotriage' ); ?></th><td><?php echo esc_html( mat_years( $sol['property_years'] ) ); ?></td></tr>
								<?php endif; ?>
								<?php if ( ! empty( $small['limit'] ) ) : ?>
									<tr><th scope="row"><?php esc_html_e( 'Small claims court limit', 'myautotriage' ); ?></th><td><?php echo esc_html( '$' . number_format_i18n( $small['limit'] ) ); ?><?php echo empty( $small['note'] ) ? '' : ' (' . esc_html( $small['note'] ) . ')'; ?></td></tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
					<?php if ( mat_state_fault_rule( $state ) ) : ?>
						<p>
							<?php
							echo esc_html( mat_state_fault_rule( $state, true ) );
							if ( ! empty( $facts['fault']['note'] ) ) {
								echo ' ' . esc_html( $facts['fault']['note'] );
							}
							?>
						</p>
					<?php endif; ?>
					<?php if ( $sol ) : ?>
						<p>
							<?php
							esc_html_e( 'The lawsuit deadline (statute of limitations) usually runs from the accident date, and an open insurance claim does not pause it.', 'myautotriage' );
							if ( ! empty( $sol['note'] ) ) {
								echo ' ' . esc_html( $sol['note'] );
							}
							?>
						</p>
					<?php endif; ?>
					<?php if ( ! empty( $small['limit'] ) ) : ?>
						<p><?php esc_html_e( 'Small claims court is a cheap way to recover a deductible, a rental bill or diminished value without a lawyer, as long as the amount fits under the limit.', 'myautotriage' ); ?></p>
					<?php endif; ?>
					<p class="mat-field__hint">
						<?php
						$cites = array();
						foreach ( array( 'fault', 'lawsuit_deadline', 'small_claims' ) as $key ) {
							if ( ! empty( $facts[ $key ]['citation'] ) ) {
								$cites[] = $facts[ $key ]['citation'];
							}
						}
						/* translators: %s: legal citations */
						printf( esc_html__( 'Rules: %s.', 'myautotriage' ), esc_html( implode( '; ', $cites ) ) );
						?>
					</p>
				<?php endif; ?>

				<?php
				$neighbors = array();
				$laws      = mat_state_laws();
				$map       = mat_state_neighbors();
				foreach ( isset( $map[ $state['code'] ] ) ? $map[ $state['code'] ] : array() as $code ) {
					if ( isset( $laws[ $code ] ) ) {
						$neighbors[] = $laws[ $code ];
					}
				}
				if ( $neighbors ) :
					?>
					<h2><?php esc_html_e( 'Claim laws in nearby states', 'myautotriage' ); ?></h2>
					<p><?php esc_html_e( 'If the accident happened in another state, or the other driver is insured there, these rules may matter too:', 'myautotriage' ); ?></p>
					<ul>
						<?php foreach ( $neighbors as $neighbor ) : ?>
							<li>
								<a href="<?php echo esc_url( mat_state_url( $neighbor ) ); ?>"><?php echo esc_html( $neighbor['name'] ); ?></a>:
								<?php echo esc_html( mat_state_total_loss_label( $neighbor ) ); ?><?php echo $neighbor['deadlines'] ? esc_html( '; decision ' . mat_state_within( $neighbor['deadlines']['decide'] ) ) : ''; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<h2><?php esc_html_e( 'Free tools for your claim', 'myautotriage' ); ?></h2>
				<ul>
					<?php
					$tool_links = array(
						'total-loss-threshold-calculator' => __( 'Total loss threshold calculator', 'myautotriage' ),
						'claim-payment-deadline-by-state' => __( 'Claim deadline lookup for every state', 'myautotriage' ),
						'demand-letter-generator'         => __( 'Demand letter generator', 'myautotriage' ),
						'appeal-letter-generator'         => __( 'Claim denial appeal letter generator', 'myautotriage' ),
						'diminished-value-calculator'     => __( 'Diminished value calculator', 'myautotriage' ),
						'gap-insurance-shortfall-calculator' => __( 'GAP shortfall calculator', 'myautotriage' ),
					);
					foreach ( $tool_links as $slug => $label ) :
						$url = mat_url_for_slug( $slug );
						if ( ! $url ) {
							continue;
						}
						?>
						<li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a></li>
					<?php endforeach; ?>
					<li><a href="<?php echo esc_url( mat_state_hub_url() ); ?>"><?php esc_html_e( 'Claim laws in other states', 'myautotriage' ); ?></a></li>
				</ul>
			</div>

			<?php mat_tool_disclaimer( __( 'This page summarizes state rules for general education. Regulations change and some apply only to certain claim types; confirm anything important with your state department of insurance or a licensed attorney.', 'myautotriage' ) ); ?>

			<?php mat_faq_block( mat_state_faqs( $state ) ); ?>

			<?php mat_render_sources( mat_state_sources( $state ) ); ?>
		<?php else : ?>
			<div class="mat-page__content"><?php the_content(); ?></div>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
