<?php
/**
 * Template Name: Adjuster Phrases Hub
 *
 * Lists every "what the adjuster said" page.
 *
 * @package MyAutoTriage
 */

get_header();
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'Adjusters use the same handful of phrases on almost every car insurance claim. Pick the one you heard to see what it usually means, what the claim-handling rules say, and a reply you can copy.', 'myautotriage' ); ?></p>
		</div>

		<div class="mat-page__content">
			<?php the_content(); ?>
			<ul class="mat-adj-list">
				<?php foreach ( mat_adjuster_phrases() as $phrase ) : ?>
					<li>
						<a href="<?php echo esc_url( mat_adjuster_url( $phrase ) ); ?>"><?php echo esc_html( '"' . $phrase['phrase'] . '"' ); ?></a>
						<span><?php echo esc_html( $phrase['short'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
			<h2><?php esc_html_e( 'Three habits that help with any adjuster', 'myautotriage' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Get it in writing. After a phone call, send a short email that sums up what was said and ask them to correct anything wrong.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Ask for the reason. Claim-handling rules expect insurers to explain denials and offers, so ask what the decision is based on.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Keep dates. Most deadlines run from when the insurer received your claim or proof of loss.', 'myautotriage' ); ?></li>
			</ul>
		</div>

		<?php mat_tool_disclaimer( __( 'These pages explain common claim-handling rules in general terms. It is not legal advice, and your state\'s law and your policy decide what applies.', 'myautotriage' ) ); ?>
	</div>
</div>
<?php
get_footer();
