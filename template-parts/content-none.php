<?php
/**
 * Shown when no posts match — search results, empty archive, etc.
 *
 * @package MyAutoTriage
 */
?>
<div class="mat-empty-state">
	<h1><?php esc_html_e( 'Nothing found', 'myautotriage' ); ?></h1>
	<p><?php esc_html_e( "We couldn't find anything here. Try a different search, or jump straight to one of our free tools.", 'myautotriage' ); ?></p>
	<?php get_search_form(); ?>
	<div class="mat-tool-card-grid">
		<?php
		$tools = array_slice( mat_get_tools_registry(), 0, 3 );
		foreach ( $tools as $tool ) :
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
