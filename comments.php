<?php
/**
 * Minimal comments template.
 *
 * @package MyAutoTriage
 */

if ( post_password_required() ) {
	return;
}
?>
<div id="comments" class="mat-comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="mat-comments__title">
			<?php
			$count = get_comments_number();
			/* translators: %d: number of comments */
			printf( esc_html( _n( '%d comment', '%d comments', $count, 'myautotriage' ) ), (int) $count );
			?>
		</h2>
		<ol class="mat-comment-list">
			<?php
			wp_list_comments( array(
				'style'      => 'ol',
				'short_ping' => true,
			) );
			?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php if ( comments_open() ) : ?>
		<?php comment_form(); ?>
	<?php else : ?>
		<p class="mat-comments__closed"><?php esc_html_e( 'Comments are closed.', 'myautotriage' ); ?></p>
	<?php endif; ?>
</div>
