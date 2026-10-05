<?php
/**
 * The main template file — fallback for any request more specific
 * templates don't handle.
 *
 * @package MyAutoTriage
 */

get_header();
?>
<div class="mat-container mat-narrow">
	<?php if ( have_posts() ) : ?>
		<div class="mat-post-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', 'card' );
			endwhile;
			?>
		</div>
		<?php mat_pagination(); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content', 'none' ); ?>
	<?php endif; ?>
</div>
<?php
get_footer();
