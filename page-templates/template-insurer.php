<?php
/**
 * Template Name: Insurer Complaints
 *
 * One insurer's complaint record with the Texas and New York regulators:
 * how it compares, what people complained about, and how often a
 * complaint got them paid. Data: inc/insurer-complaints.php.
 *
 * @package MyAutoTriage
 */

get_header();
$mat_ins      = mat_current_insurer();
$mat_ins_data = mat_insurer_data();
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<?php if ( $mat_ins ) : ?>
				<p class="mat-field__hint">
					<?php
					/* translators: %s: date */
					printf( esc_html__( 'Regulator data last updated %s by the MyAutoTriage Editorial Team.', 'myautotriage' ), esc_html( date_i18n( get_option( 'date_format' ), strtotime( $mat_ins_data['built'] ) ) ) );
					?>
				</p>
			<?php endif; ?>
		</div>

		<?php if ( $mat_ins ) : ?>
			<?php
			$mat_ins_tx = mat_insurer_score( $mat_ins, 'tx' );
			$mat_ins_ny = mat_insurer_score( $mat_ins, 'ny' );
			?>
			<div class="mat-short-answer">
				<p class="mat-short-answer__label"><?php esc_html_e( 'Short answer', 'myautotriage' ); ?></p>
				<p><?php echo esc_html( mat_insurer_summary( $mat_ins ) ); ?></p>
			</div>

			<div class="mat-ic-cards">
				<?php
				foreach ( array(
					'tx' => array( __( 'Texas', 'myautotriage' ), $mat_ins_data['tx_year'], $mat_ins_tx ),
					'ny' => array( __( 'New York', 'myautotriage' ), $mat_ins_data['ny_year'], $mat_ins_ny ),
				) as $mat_ins_key => $mat_ins_card ) :
					?>
					<div class="mat-ic-card">
						<p class="mat-ic-card__state"><?php echo esc_html( $mat_ins_card[0] . ' ' . $mat_ins_card[1] ); ?></p>
						<?php if ( $mat_ins_card[2] ) : ?>
							<p class="mat-ic-card__num"><?php echo 'few' === $mat_ins_card[2]['verdict'] ? '–' : esc_html( number_format_i18n( $mat_ins_card[2]['index'], 2 ) ); ?></p>
							<p><span class="mat-ic-badge mat-ic-badge--<?php echo esc_attr( $mat_ins_card[2]['verdict'] ); ?>"><?php echo esc_html( $mat_ins_card[2]['label'] ); ?></span></p>
							<p class="mat-field__hint">
								<?php
								echo esc_html( 'tx' === $mat_ins_key
									/* translators: 1: confirmed complaints, 2: policies */
									? sprintf( __( '%1$s confirmed complaints, %2$s auto policies. Average = 1.00.', 'myautotriage' ), number_format_i18n( $mat_ins['tx']['index_confirmed'] ), number_format_i18n( $mat_ins['tx']['policies'] ) )
									/* translators: 1: upheld complaints, 2: premium */
									: sprintf( __( '%1$s upheld complaints on $%2$s million of premium. Average = 1.00.', 'myautotriage' ), number_format_i18n( $mat_ins['ny']['upheld'] ), number_format_i18n( $mat_ins['ny']['premium_millions'] ) ) );
								?>
							</p>
						<?php else : ?>
							<p><?php esc_html_e( 'Not in this state\'s data.', 'myautotriage' ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="mat-page__content">
				<?php the_content(); ?>
				<?php if ( ! empty( $mat_ins['tx']['complaints'] ) ) : ?>
					<?php /* translators: 1: insurer, 2: year */ ?>
					<h2><?php echo esc_html( sprintf( __( 'What Texas drivers complained about (%2$s)', 'myautotriage' ), $mat_ins['name'], $mat_ins_data['tx_year'] ) ); ?></h2>
					<?php /* translators: 1: number of complaints, 2: insurer, 3: year */ ?>
					<p><?php echo esc_html( sprintf( _n( 'In %3$s the Texas Department of Insurance received %1$s auto complaint about %2$s. The most common reasons:', 'In %3$s the Texas Department of Insurance received %1$s auto complaints about %2$s. The most common reasons:', $mat_ins['tx']['complaints'], 'myautotriage' ), number_format_i18n( $mat_ins['tx']['complaints'] ), $mat_ins['name'], $mat_ins_data['tx_year'] ) ); ?></p>
					<ol>
						<?php foreach ( $mat_ins['tx']['reasons'] as $mat_ins_r ) : ?>
							<?php /* translators: 1: reason, 2: share of complaints */ ?>
							<li><?php echo esc_html( sprintf( __( '%1$s (%2$s of complaints)', 'myautotriage' ), $mat_ins_r['reason'], mat_insurer_pct( $mat_ins_r['count'], $mat_ins['tx']['complaints'] ) ) ); ?></li>
						<?php endforeach; ?>
					</ol>
					<p class="mat-field__hint"><?php esc_html_e( 'One complaint can list more than one reason. These counts include every complaint received, not only confirmed ones.', 'myautotriage' ); ?></p>

					<?php if ( $mat_ins['tx']['claim_complaints'] >= MAT_INSURER_MIN_COMPLAINTS ) : ?>
						<h2><?php esc_html_e( 'Did complaining get people paid?', 'myautotriage' ); ?></h2>
						<p>
							<?php
							/* translators: 1: share, 2: number of complaints, 3: insurer, 4: statewide share */
							echo esc_html( sprintf( __( '%1$s of the %2$s claim complaints about %3$s ended with the claim settled or more money paid, by TDI\'s record of how each was resolved. For all Texas auto insurers the figure was %4$s.', 'myautotriage' ), mat_insurer_pct( $mat_ins['tx']['claim_paid'], $mat_ins['tx']['claim_complaints'] ), number_format_i18n( $mat_ins['tx']['claim_complaints'] ), $mat_ins['name'], mat_insurer_pct( $mat_ins_data['tx_market']['claim_paid'], $mat_ins_data['tx_market']['claim_complaints'] ) ) );
							?>
						</p>
					<?php endif; ?>
				<?php endif; ?>

				<?php /* translators: %s: insurer */ ?>
				<h2><?php echo esc_html( sprintf( __( 'If %s is stalling or lowballing your claim', 'myautotriage' ), $mat_ins['name'] ) ); ?></h2>
				<ol>
					<li><?php esc_html_e( 'Put it in writing. Ask the adjuster by email for the reason for any delay or offer, and the policy language or valuation report behind it.', 'myautotriage' ); ?></li>
					<li><?php esc_html_e( 'Keep a dated record of every call, promise and document. State claim rules set deadlines for acknowledging, deciding and paying claims, and a record shows when they were missed.', 'myautotriage' ); ?></li>
					<li><?php esc_html_e( 'Send a written counter-offer with your own evidence: estimates, comparable cars, receipts.', 'myautotriage' ); ?></li>
					<li><?php esc_html_e( 'If that goes nowhere, file a complaint with your state insurance department. It\'s free, and the insurer has to answer it in writing.', 'myautotriage' ); ?></li>
				</ol>
			</div>

			<section class="mat-page__content mat-next-steps" aria-label="<?php esc_attr_e( 'Free tools for this', 'myautotriage' ); ?>">
				<h2><?php esc_html_e( 'Free tools for this', 'myautotriage' ); ?></h2>
				<ul>
					<?php foreach ( mat_insurer_next_steps() as $mat_ins_step ) : ?>
						<li><a href="<?php echo esc_url( $mat_ins_step[0] ); ?>"><?php echo esc_html( $mat_ins_step[1] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</section>

			<details class="mat-page__content mat-ic-companies">
				<?php /* translators: %s: insurer */ ?>
				<summary><?php echo esc_html( sprintf( __( 'Companies counted as %s', 'myautotriage' ), $mat_ins['name'] ) ); ?></summary>
				<ul>
					<?php foreach ( $mat_ins['companies'] as $mat_ins_co ) : ?>
						<?php /* translators: 1: company, 2: state, 3: NAIC code */ ?>
						<li><?php echo esc_html( sprintf( __( '%1$s (%2$s, NAIC %3$s)', 'myautotriage' ), mat_insurer_company_name( $mat_ins_co['name'] ), $mat_ins_co['state'], $mat_ins_co['naic'] ) ); ?></li>
					<?php endforeach; ?>
				</ul>
			</details>

			<?php
			mat_faq_block( array(
				array(
					/* translators: %s: insurer */
					'question' => sprintf( __( 'Does %s have a lot of complaints?', 'myautotriage' ), $mat_ins['name'] ),
					'answer'   => mat_insurer_summary( $mat_ins ),
				),
				array(
					/* translators: %s: insurer */
					'question' => sprintf( __( 'How do I file a complaint against %s?', 'myautotriage' ), $mat_ins['name'] ),
					'answer'   => __( 'First ask for a supervisor or the claims manager in writing and keep a copy. If that doesn\'t fix it, file a free complaint with the insurance department in the state where your policy was issued or the accident happened. Include your claim number, a dated timeline and what you want the insurer to do.', 'myautotriage' ),
				),
				array(
					'question' => __( 'What does a complaint index of 1.00 mean?', 'myautotriage' ),
					'answer'   => __( '1.00 is the average for all auto insurers in that state, adjusted for size. A score of 0.50 means half as many complaints as expected for the company\'s size; 2.00 means twice as many.', 'myautotriage' ),
				),
			) );
			mat_render_sources( mat_insurer_sources() );
			?>

			<section class="mat-page__content mat-next-steps" aria-label="<?php esc_attr_e( 'Other insurers', 'myautotriage' ); ?>">
				<h2><?php esc_html_e( 'Compare other insurers', 'myautotriage' ); ?></h2>
				<ul class="mat-ic-others">
					<li><a href="<?php echo esc_url( mat_insurer_hub_url() ); ?>"><?php esc_html_e( 'All insurers side by side', 'myautotriage' ); ?></a></li>
					<?php foreach ( mat_insurers() as $mat_ins_other ) : ?>
						<?php if ( $mat_ins_other['slug'] !== $mat_ins['slug'] ) : ?>
							<li><a href="<?php echo esc_url( mat_insurer_url( $mat_ins_other ) ); ?>"><?php echo esc_html( $mat_ins_other['name'] ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</section>
			<?php mat_outcomes_cta(); ?>
		<?php endif; ?>

		<?php mat_tool_disclaimer( __( 'Complaint ratings come from state regulators\' published data and describe past complaints, not how any one claim will go. They are not a recommendation to buy or avoid any insurer.', 'myautotriage' ) ); ?>
	</div>
</div>
<?php
get_footer();
