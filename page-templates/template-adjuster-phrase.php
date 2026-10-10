<?php
/**
 * Template Name: Adjuster Phrase
 *
 * One thing an adjuster commonly says: what it means, the rules behind it,
 * and a reply to copy. Content comes from inc/adjuster-phrases.php.
 *
 * @package MyAutoTriage
 */

get_header();
$phrase = mat_current_adjuster_phrase();
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p class="mat-field__hint">
				<?php
				/* translators: %s: date */
				printf( esc_html__( 'Last reviewed %s by the MyAutoTriage Editorial Team.', 'myautotriage' ), esc_html( date_i18n( get_option( 'date_format' ), strtotime( MAT_ADJUSTER_REVIEWED ) ) ) );
				if ( mat_reviewer_byline() ) {
					echo ' ' . mat_reviewer_byline() . '.'; // phpcs:ignore -- escaped in mat_reviewer_byline()
				}
				?>
			</p>
		</div>

		<?php if ( $phrase ) : ?>
			<blockquote class="mat-adj-quote">
				<p class="mat-adj-quote__label"><?php esc_html_e( 'What the adjuster said', 'myautotriage' ); ?></p>
				<p class="mat-adj-quote__text"><?php echo esc_html( '"' . $phrase['phrase'] . '"' ); ?></p>
			</blockquote>

			<div class="mat-short-answer">
				<p class="mat-short-answer__label"><?php esc_html_e( 'Short answer', 'myautotriage' ); ?></p>
				<p><?php echo esc_html( $phrase['short'] ); ?></p>
			</div>

			<div class="mat-page__content">
				<?php the_content(); ?>
				<h2><?php esc_html_e( 'What it usually means', 'myautotriage' ); ?></h2>
				<?php foreach ( $phrase['means'] as $para ) : ?>
					<p><?php echo esc_html( $para ); ?></p>
				<?php endforeach; ?>

				<h2><?php esc_html_e( 'What the rules say', 'myautotriage' ); ?></h2>
				<ul>
					<?php foreach ( $phrase['rights'] as $item ) : ?>
						<li><?php echo esc_html( $item ); ?></li>
					<?php endforeach; ?>
				</ul>
				<p class="mat-field__hint"><?php esc_html_e( 'The NAIC model act and regulation are templates that most states have adopted in some form, often with different deadlines or wording. Your state\'s own law and your policy are what apply.', 'myautotriage' ); ?></p>

				<h2><?php esc_html_e( 'What to say back', 'myautotriage' ); ?></h2>
				<p><?php esc_html_e( 'Reply in writing (email is fine) so you have a record. Fill in the brackets:', 'myautotriage' ); ?></p>
				<div class="mat-adj-reply">
					<p id="mat-adj-reply-text" class="mat-adj-reply__text"><?php echo esc_html( $phrase['reply'] ); ?></p>
					<button type="button" class="mat-btn mat-btn--ghost mat-btn--sm" data-mat-copy="mat-adj-reply-text"><?php esc_html_e( 'Copy reply', 'myautotriage' ); ?></button>
				</div>
			</div>

			<?php
			$links = array();
			foreach ( $phrase['tools'] as $tool ) {
				$url = mat_url_for_slug( $tool['slug'] );
				if ( $url && ! empty( $tool['query'] ) ) {
					$url = add_query_arg( $tool['query'], $url );
				}
				if ( $url ) {
					$links[] = '<li><a href="' . esc_url( $url ) . '">' . esc_html( $tool['label'] ) . '</a></li>';
				}
			}
			if ( $links ) :
				?>
				<section class="mat-page__content mat-next-steps" aria-label="<?php esc_attr_e( 'Free tools for this', 'myautotriage' ); ?>">
					<h2><?php esc_html_e( 'Free tools for this', 'myautotriage' ); ?></h2>
					<ul><?php echo implode( '', $links ); // phpcs:ignore -- escaped above ?></ul>
				</section>
			<?php endif; ?>

			<?php mat_faq_block( $phrase['faqs'] ); ?>

			<section class="mat-page__content mat-next-steps" aria-label="<?php esc_attr_e( 'Other things adjusters say', 'myautotriage' ); ?>">
				<h2><?php esc_html_e( 'Other things adjusters say', 'myautotriage' ); ?></h2>
				<ul>
					<?php foreach ( mat_adjuster_phrases() as $other ) : ?>
						<?php if ( $other['slug'] !== $phrase['slug'] ) : ?>
							<li><a href="<?php echo esc_url( mat_adjuster_url( $other ) ); ?>"><?php echo esc_html( '"' . $other['phrase'] . '"' ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</section>

			<?php mat_render_sources( mat_adjuster_sources( $phrase ) ); ?>
		<?php endif; ?>

		<?php mat_tool_disclaimer( __( 'This page explains common claim-handling rules in general terms. It is not legal advice. For an injury claim or a large dispute, talk to a licensed attorney.', 'myautotriage' ) ); ?>
	</div>
</div>
<?php
get_footer();
