<?php
/**
 * Post card used on the blog archive, homepage "latest posts", and index.php.
 *
 * @package MyAutoTriage
 */
?>
<article <?php post_class( 'mat-post-card' ); ?>>
	<a class="mat-post-card__thumb" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php
			// Uses the 'full' size (not the 600x400 'mat-card' crop): the blog
			// thumbnails are 1200x630 with the article's title baked into the
			// image itself, and 600x400 (a 3:2 crop) cuts off the left/right
			// edges of that text. 'full' + the matching 1200/630 CSS
			// aspect-ratio on .mat-post-card__thumb shows the whole image.
			the_post_thumbnail( 'full', array( 'loading' => 'lazy', 'alt' => get_the_title() ) );
			?>
		<?php else : ?>
			<span class="mat-post-card__thumb-fallback" aria-hidden="true"><?php echo mat_icon( 'document' ); ?></span>
		<?php endif; ?>
	</a>
	<div class="mat-post-card__body">
		<?php
		$cats = get_the_category();
		if ( ! empty( $cats ) ) :
			?>
			<span class="mat-post-card__cat"><?php echo esc_html( $cats[0]->name ); ?></span>
		<?php endif; ?>
		<h2 class="mat-post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p class="mat-post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '…' ) ); ?></p>
		<div class="mat-post-card__meta">
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			<span aria-hidden="true">&middot;</span>
			<span><?php echo esc_html( mat_reading_time() ); ?></span>
		</div>
	</div>
</article>
