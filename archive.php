<?php
/**
 * Category/tag/date archive template.
 *
 * @package MyAutoTriage
 */

get_header();
?>
<div class="mat-container mat-narrow">
	<header class="mat-archive__header">
		<h1><?php the_archive_title(); ?></h1>
		<?php the_archive_description( '<div class="mat-archive__description">', '</div>' ); ?>
	</header>

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
