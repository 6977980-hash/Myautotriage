<?php
/**
 * Default page template (used unless a page-templates/*.php is selected).
 *
 * @package MyAutoTriage
 */

get_header();
while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'mat-page' ); ?>>
		<div class="mat-container mat-narrow">
			<header class="mat-page__header">
				<h1><?php the_title(); ?></h1>
			</header>
			<div class="mat-page__content">
				<?php the_content(); ?>
			</div>
		</div>
	</article>
	<?php
endwhile;
get_footer();
