<?php
/**
 * Template Name: Tools Hub
 *
 * @package MyAutoTriage
 */

get_header();
?>
<div class="mat-container mat-narrow" style="text-align:center; padding-top:2.2em;">
	<h1><?php the_title(); ?></h1>
	<div class="mat-page__content"><?php the_content(); ?></div>
</div>

<div class="mat-container" style="padding-bottom:3em;">
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

	// Tools grouped by where the visitor is in a claim, the same stages the
	// claim triage asks about. A tool missing from every group lands in
	// "More tools" so a new registry entry never disappears from the hub.
	$stages = array(
		'start'   => array(
			'title' => __( 'Not sure where to start', 'myautotriage' ),
			'intro' => __( 'Answer three questions for your next step, then keep a dated record of every call.', 'myautotriage' ),
			'slugs' => array( 'claim-triage', 'claim-diary' ),
		),
		'accident' => array(
			'title' => __( 'It just happened', 'myautotriage' ),
			'intro' => __( 'Before you file or give a statement: whether to claim at all, how fault splits, and how long you have to sue.', 'myautotriage' ),
			'slugs' => array( 'recorded-statement-checklist', 'deductible-vs-premium-calculator', 'comparative-fault-calculator', 'michigan-mini-tort-calculator', 'car-accident-lawsuit-deadline-calculator' ),
		),
		'offer'   => array(
			'title' => __( 'Repairs, rental and an offer that looks low', 'myautotriage' ),
			'intro' => __( 'What the at-fault insurer owes beyond the repair bill, and a letter that asks for it.', 'myautotriage' ),
			'slugs' => array( 'loss-of-use-calculator', 'rental-car-extension-letter', 'diminished-value-calculator', 'deductible-recovery-calculator', 'demand-letter-generator' ),
		),
		'totaled' => array(
			'title' => __( 'My car was totaled', 'myautotriage' ),
			'intro' => __( 'Check the total-loss call, the valuation report behind the offer, and what you would still owe on the loan.', 'myautotriage' ),
			'slugs' => array( 'total-loss-threshold-calculator', 'total-loss-valuation-checker', 'gap-insurance-shortfall-calculator' ),
		),
		'stalled' => array(
			'title' => __( 'The claim is late or denied', 'myautotriage' ),
			'intro' => __( 'Your state\'s payment deadlines, the interest owed on a late payment, and a written appeal.', 'myautotriage' ),
			'slugs' => array( 'claim-payment-deadline-by-state', 'insurance-late-payment-interest-calculator', 'appeal-letter-generator' ),
		),
		'policy'  => array(
			'title' => __( 'Cancelling or switching a policy', 'myautotriage' ),
			'intro' => __( 'How much prepaid premium comes back when you cancel early.', 'myautotriage' ),
			'slugs' => array( 'car-insurance-refund-calculator' ),
		),
	);

	$tools_by_slug = array();
	foreach ( mat_get_tools_registry() as $tool ) {
		$page = get_page_by_path( $tool['slug'] );
		if ( $page ) {
			$tool['url']                    = get_permalink( $page );
			$tools_by_slug[ $tool['slug'] ] = $tool;
		}
	}
	$placed = array();
	foreach ( $stages as $key => $stage ) {
		$stages[ $key ]['tools'] = array();
		foreach ( $stage['slugs'] as $slug ) {
			if ( isset( $tools_by_slug[ $slug ] ) ) {
				$stages[ $key ]['tools'][] = $tools_by_slug[ $slug ];
				$placed[ $slug ]           = true;
			}
		}
	}
	$rest = array_diff_key( $tools_by_slug, $placed );
	if ( $rest ) {
		$stages['more'] = array(
			'title' => __( 'More tools', 'myautotriage' ),
			'intro' => '',
			'tools' => array_values( $rest ),
		);
	}
	$stages = array_filter( $stages, function ( $stage ) {
		return ! empty( $stage['tools'] );
	} );
	?>
	<nav class="mat-stage-nav" aria-label="<?php esc_attr_e( 'Jump to a claim stage', 'myautotriage' ); ?>">
		<?php foreach ( $stages as $key => $stage ) : ?>
			<a href="#stage-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $stage['title'] ); ?></a>
		<?php endforeach; ?>
	</nav>

	<?php foreach ( $stages as $key => $stage ) : ?>
		<section class="mat-stage" id="stage-<?php echo esc_attr( $key ); ?>">
			<h2><?php echo esc_html( $stage['title'] ); ?></h2>
			<?php if ( $stage['intro'] ) : ?>
				<p class="mat-stage__intro"><?php echo esc_html( $stage['intro'] ); ?></p>
			<?php endif; ?>
			<div class="mat-tool-card-grid">
				<?php
				foreach ( $stage['tools'] as $tool ) {
					mat_tool_card( array(
						'title'   => $tool['title'],
						'excerpt' => $tool['excerpt'],
						'url'     => $tool['url'],
						'icon'    => mat_icon( isset( $icon_map[ $tool['slug'] ] ) ? $icon_map[ $tool['slug'] ] : 'calculator' ),
					) );
				}
				?>
			</div>
		</section>
	<?php endforeach; ?>

	<?php
	$variants = array(
		'no-injury-demand-letter-generator'              => __( 'No-injury accident (property damage only)', 'myautotriage' ),
		'property-damage-demand-letter-generator'        => __( 'Property damage claim', 'myautotriage' ),
		'insurance-underpayment-demand-letter-generator' => __( 'Lowball or underpaid settlement', 'myautotriage' ),
		'diminished-value-demand-letter-generator'       => __( 'Diminished value claim', 'myautotriage' ),
		'small-claims-demand-letter-generator'           => __( 'Final demand before small claims court', 'myautotriage' ),
	);
	$links = array();
	foreach ( $variants as $slug => $label ) {
		$url = mat_url_for_slug( $slug );
		if ( $url ) {
			$links[] = '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
		}
	}
	if ( $links ) :
		?>
		<section class="mat-container mat-narrow mat-page__content">
			<h2><?php esc_html_e( 'Demand letters for your situation', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'The same free generator, pre-set for the most common claim situations:', 'myautotriage' ); ?></p>
			<ul><?php echo implode( '', $links ); // phpcs:ignore -- escaped above ?></ul>
		</section>
	<?php endif; ?>
</div>
<?php
get_footer();
