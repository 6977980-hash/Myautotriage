<?php
/**
 * Single blog post template.
 *
 * @package MyAutoTriage
 */

get_header();
while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'mat-single-post' ); ?>>
		<div class="mat-container mat-narrow">
			<header class="mat-single-post__header">
				<?php
				$cats = get_the_category();
				if ( ! empty( $cats ) ) :
					?>
					<a class="mat-post-card__cat" href="<?php echo esc_url( get_category_link( $cats[0]->term_id ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></a>
				<?php endif; ?>
				<h1><?php the_title(); ?></h1>
				<div class="mat-single-post__meta">
					<span><?php esc_html_e( 'By', 'myautotriage' ); ?> <a href="<?php echo esc_url( mat_url_for_slug( 'editorial-policy' ) ?: home_url( '/' ) ); ?>"><?php echo esc_html( mat_author_name() ); ?></a></span>
					<span aria-hidden="true">&middot;</span>
					<?php if ( mat_post_was_updated() ) : ?>
						<span><?php esc_html_e( 'Updated', 'myautotriage' ); ?> <time datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>"><?php echo esc_html( get_the_modified_date() ); ?></time></span>
					<?php else : ?>
						<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					<?php endif; ?>
					<span aria-hidden="true">&middot;</span>
					<span><?php echo esc_html( mat_reading_time() ); ?></span>
				</div>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="mat-single-post__thumb">
					<?php the_post_thumbnail( 'large', array( 'loading' => 'eager' ) ); ?>
				</div>
			<?php endif; ?>

			<div class="mat-single-post__content">
				<?php the_content(); ?>
			</div>

			<aside class="mat-author-box" aria-label="<?php esc_attr_e( 'About the author', 'myautotriage' ); ?>">
				<p class="mat-author-box__name"><?php echo esc_html( mat_author_name() ); ?></p>
				<p><?php esc_html_e( 'MyAutoTriage guides are written and updated by our editorial team from state insurance regulations, policy language and published claim-handling rules. We are independent: we do not sell insurance, handle claims or take referral fees from law firms or appraisers.', 'myautotriage' ); ?>
				<a href="<?php echo esc_url( mat_url_for_slug( 'editorial-policy' ) ?: home_url( '/' ) ); ?>"><?php esc_html_e( 'How we research and update content', 'myautotriage' ); ?></a></p>
			</aside>

			<div class="mat-single-post__footer">
				<p class="mat-footer-disclaimer"><?php esc_html_e( 'This article is for general education, not legal or insurance advice. Rules vary by state and by policy — verify anything important with your insurer or a licensed attorney.', 'myautotriage' ); ?></p>
			</div>

			<?php mat_related_posts( get_the_ID() ); ?>

			<?php if ( comments_open() || get_comments_number() ) : ?>
				<?php comments_template(); ?>
			<?php endif; ?>
		</div>
	</article>
	<?php
endwhile;
get_footer();
