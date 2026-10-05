<?php
/**
 * Blog index template (used for the designated "posts page").
 *
 * @package MyAutoTriage
 */

get_header();
?>
<div class="mat-container mat-narrow">
	<header class="mat-archive__header">
		<h1><?php esc_html_e( 'Claim Guides & Insurance Explainers', 'myautotriage' ); ?></h1>
		<p class="mat-archive__description"><?php esc_html_e( 'Plain-English guides to filing, negotiating, and — when necessary — fighting a car insurance claim.', 'myautotriage' ); ?></p>
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
