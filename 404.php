<?php
/**
 * 404 template.
 *
 * @package MyAutoTriage
 */

get_header();
?>
<div class="mat-container mat-narrow">
	<div class="mat-empty-state">
		<h1><?php esc_html_e( '404 — Page not found', 'myautotriage' ); ?></h1>
		<p><?php esc_html_e( "The page you're looking for has moved or never existed. Search the site, or jump to a free tool below.", 'myautotriage' ); ?></p>
		<?php get_search_form(); ?>
		<div class="mat-tool-card-grid">
			<?php
			foreach ( array_slice( mat_get_tools_registry(), 0, 4 ) as $tool ) :
				$page = get_page_by_path( $tool['slug'] );
				if ( ! $page ) {
					continue;
				}
				mat_tool_card( array(
					'title'   => $tool['title'],
					'excerpt' => $tool['excerpt'],
					'url'     => get_permalink( $page ),
					'icon'    => mat_icon( 'calculator' ),
				) );
			endforeach;
			?>
		</div>
	</div>
</div>
<?php
get_footer();
