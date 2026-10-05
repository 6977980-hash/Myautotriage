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
