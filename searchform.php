<?php
/**
 * Search form.
 *
 * @package MyAutoTriage
 */
?>
<form role="search" method="get" class="mat-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="mat-search-field"><?php esc_html_e( 'Search for:', 'myautotriage' ); ?></label>
	<input type="search" id="mat-search-field" class="mat-search-form__field" placeholder="<?php esc_attr_e( 'Search guides & tools…', 'myautotriage' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
	<button type="submit" class="mat-btn mat-btn--primary"><?php esc_html_e( 'Search', 'myautotriage' ); ?></button>
</form>
