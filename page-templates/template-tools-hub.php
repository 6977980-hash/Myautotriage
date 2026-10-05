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
</div>
<?php
get_footer();
