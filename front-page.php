<?php
/**
 * Homepage.
 *
 * @package MyAutoTriage
 */

get_header();

$tagline = get_theme_mod( 'mat_tagline_override' );
if ( ! $tagline ) {
	$tagline = __( 'Free calculators and letter generators for every stage of a car insurance claim — figure out what your claim is worth, what your state requires, and what to say when an insurer underpays or denies it.', 'myautotriage' );
}
?>

<section class="mat-hero">
	<div class="mat-container mat-hero__inner">
		<h1 class="mat-hero__title"><?php esc_html_e( 'Triage your car insurance claim before the insurer does.', 'myautotriage' ); ?></h1>
		<p class="mat-hero__subtitle"><?php echo esc_html( $tagline ); ?></p>
		<div class="mat-hero__actions">
			<a class="mat-btn mat-btn--primary mat-btn--lg" href="#mat-triage-title"><?php esc_html_e( 'Find my next step', 'myautotriage' ); ?></a>
			<a class="mat-btn mat-btn--ghost mat-btn--lg" href="<?php echo esc_url( home_url( '/tools/' ) ); ?>"><?php esc_html_e( 'Browse all free tools', 'myautotriage' ); ?></a>
		</div>
		<p class="mat-hero__trust"><?php esc_html_e( 'No sign-up. No email wall on the calculators. Just answers.', 'myautotriage' ); ?></p>
	</div>
</section>

<section class="mat-section mat-section--alt" aria-labelledby="mat-triage-title">
	<div class="mat-container mat-narrow">
		<h2 class="mat-section__title" id="mat-triage-title"><?php esc_html_e( 'Where does your claim stand?', 'myautotriage' ); ?></h2>
		<p class="mat-section__lead"><?php esc_html_e( 'Answer three questions to get the one thing to do next, the right tool or letter, and your state\'s deadlines.', 'myautotriage' ); ?></p>
		<?php mat_claim_triage_form(); ?>
	</div>
</section>

<section class="mat-section" aria-label="<?php esc_attr_e( 'Free tools', 'myautotriage' ); ?>">
	<div class="mat-container">
		<h2 class="mat-section__title"><?php esc_html_e( 'Start with a free tool', 'myautotriage' ); ?></h2>
		<p class="mat-section__lead"><?php esc_html_e( 'Every tool below runs instantly in your browser. Nothing is saved or sent anywhere.', 'myautotriage' ); ?></p>
		<div class="mat-tool-card-grid">
			<?php
			$icon_map = array(
				'diminished-value-calculator'        => 'calculator',
				'total-loss-threshold-calculator'    => 'car',
				'gap-insurance-shortfall-calculator' => 'shield',
				'deductible-vs-premium-calculator'   => 'calculator',
				'claim-payment-deadline-by-state'    => 'map',
				'demand-letter-generator'            => 'letter',
				'appeal-letter-generator'            => 'document',
			);
			foreach ( mat_get_tools_registry() as $tool ) :
				$page = get_page_by_path( $tool['slug'] );
				if ( ! $page ) {
					continue;
				}
				mat_tool_card( array(
					'title'   => $tool['title'],
					'excerpt' => $tool['excerpt'],
					'url'     => get_permalink( $page ),
					'icon'    => mat_icon( isset( $icon_map[ $tool['slug'] ] ) ? $icon_map[ $tool['slug'] ] : 'calculator' ),
				) );
			endforeach;
			?>
		</div>
	</div>
</section>

<?php if ( get_page_by_path( MAT_STATE_HUB_SLUG ) ) : ?>
<section class="mat-section" aria-labelledby="mat-states-title">
	<div class="mat-container">
		<h2 class="mat-section__title" id="mat-states-title"><?php esc_html_e( 'Car insurance claim laws by state', 'myautotriage' ); ?></h2>
		<p class="mat-section__lead">
			<?php esc_html_e( 'Claim deadlines and total loss rules for every state, with the law behind each one.', 'myautotriage' ); ?>
			<a href="<?php echo esc_url( mat_state_hub_url() ); ?>"><?php esc_html_e( 'Compare all states', 'myautotriage' ); ?></a>
		</p>
		<ul class="mat-state-list">
			<?php foreach ( mat_state_laws() as $state ) : ?>
				<li><a href="<?php echo esc_url( mat_state_url( $state ) ); ?>"><?php echo esc_html( $state['name'] ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php endif; ?>

<section class="mat-section mat-section--alt" aria-label="<?php esc_attr_e( 'How it works', 'myautotriage' ); ?>">
	<div class="mat-container">
		<h2 class="mat-section__title"><?php esc_html_e( 'Three steps to triage any claim', 'myautotriage' ); ?></h2>
		<div class="mat-steps">
			<div class="mat-step">
				<span class="mat-step__number">1</span>
				<h3><?php esc_html_e( 'Know what happened first', 'myautotriage' ); ?></h3>
				<p><?php esc_html_e( 'Read the right guide for your situation — an accident, a denial, an underpaid check, or a total loss — before you talk to an adjuster.', 'myautotriage' ); ?></p>
			</div>
			<div class="mat-step">
				<span class="mat-step__number">2</span>
				<h3><?php esc_html_e( 'Run the numbers', 'myautotriage' ); ?></h3>
				<p><?php esc_html_e( 'Use a calculator to get an independent, evidence-based estimate — diminished value, total loss threshold, or a GAP shortfall — before you accept an offer.', 'myautotriage' ); ?></p>
			</div>
			<div class="mat-step">
				<span class="mat-step__number">3</span>
				<h3><?php esc_html_e( 'Put it in writing', 'myautotriage' ); ?></h3>
				<p><?php esc_html_e( 'Generate a clear, professional letter — a demand letter or a denial appeal — that documents your number and your reasoning.', 'myautotriage' ); ?></p>
			</div>
		</div>
	</div>
</section>

<section class="mat-section" aria-label="<?php esc_attr_e( 'Latest guides', 'myautotriage' ); ?>">
	<div class="mat-container">
		<div class="mat-section__header-row">
			<h2 class="mat-section__title"><?php esc_html_e( 'Latest claim guides', 'myautotriage' ); ?></h2>
			<a class="mat-link" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'View all articles', 'myautotriage' ); ?> &rarr;</a>
		</div>
		<div class="mat-post-grid">
			<?php
			$latest = new WP_Query( array( 'posts_per_page' => 6, 'post_status' => 'publish' ) );
			if ( $latest->have_posts() ) :
				while ( $latest->have_posts() ) :
					$latest->the_post();
					get_template_part( 'template-parts/content', 'card' );
				endwhile;
				wp_reset_postdata();
			else :
				echo '<p>' . esc_html__( 'Articles are coming soon.', 'myautotriage' ) . '</p>';
			endif;
			?>
		</div>
	</div>
</section>

<?php
$home_faqs = array(
	array(
		'question' => __( 'Is MyAutoTriage affiliated with any insurance company?', 'myautotriage' ),
		'answer'   => __( 'No. MyAutoTriage is an independent, ad-supported educational resource. We do not sell insurance, adjust claims, or represent insurers or policyholders.', 'myautotriage' ),
	),
	array(
		'question' => __( 'Are the calculators free to use?', 'myautotriage' ),
		'answer'   => __( 'Yes. Every calculator and letter generator on this site is free, requires no account, and runs entirely in your browser.', 'myautotriage' ),
	),
	array(
		'question' => __( 'Can I use the generated letters as-is?', 'myautotriage' ),
		'answer'   => __( 'The generators produce a solid first draft based on the details you enter. Review it, add any facts specific to your claim, and consider having an attorney review anything involving a large sum or an injury before you send it.', 'myautotriage' ),
	),
);
?>
<section class="mat-section mat-section--alt">
	<div class="mat-container mat-narrow">
		<?php mat_faq_block( $home_faqs ); ?>
	</div>
</section>

<?php
get_footer();
