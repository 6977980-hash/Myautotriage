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
					if ( $d ) {
						/* translators: 1: state, 2: acknowledge, 3: decide, 4: pay */
						printf( esc_html__( 'In %1$s an insurer must acknowledge your claim within %2$s, accept or deny it within %3$s, and pay within %4$s.', 'myautotriage' ), esc_html( $state['name'] ), esc_html( lcfirst( $d['acknowledge'] ) ), esc_html( lcfirst( $d['decide'] ) ), esc_html( lcfirst( $d['pay'] ) ) );
						echo ' ';
					}
					if ( $state['total_loss'] ) {
						/* translators: %s: total loss rule */
						printf( esc_html__( 'A car is a total loss when: %s.', 'myautotriage' ), esc_html( lcfirst( mat_state_total_loss_rule( $state ) ) ) );
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
					<?php if ( ! empty( $d['citation'] ) ) : ?>
						<p class="mat-field__hint">
							<?php
							/* translators: %s: legal citation */
							printf( esc_html__( 'Rule: %s.', 'myautotriage' ), esc_html( $d['citation'] ) );
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
					<p><?php echo esc_html( mat_state_total_loss_rule( $state ) ); ?>.</p>
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
					<?php else : ?>
						<p><?php esc_html_e( 'Example: a car worth $15,000 with $3,000 of salvage value is a total loss once the repair estimate reaches $12,000, because $12,000 + $3,000 equals its value.', 'myautotriage' ); ?></p>
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

				<h2><?php esc_html_e( 'Free tools for your claim', 'myautotriage' ); ?></h2>
				<ul>
					<?php
					$tool_links = array(
						'total-loss-threshold-calculator' => __( 'Total loss threshold calculator', 'myautotriage' ),
						'claim-payment-deadline-by-state' => __( 'Claim deadline lookup for every state', 'myautotriage' ),
						'demand-letter-generator'         => __( 'Demand letter generator', 'myautotriage' ),
						'appeal-letter-generator'         => __( 'Claim denial appeal letter generator', 'myautotriage' ),
						'diminished-value-calculator'     => __( 'Diminished value calculator', 'myautotriage' ),
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
