<?php
/**
 * The footer for the theme.
 *
 * @package MyAutoTriage
 */
?>
</main><!-- .mat-site-main -->

<footer class="mat-site-footer">
	<div class="mat-container mat-site-footer__grid">
		<div class="mat-footer-col mat-footer-col--brand">
			<a class="mat-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img class="mat-brand__logo" src="<?php echo esc_url( MAT_URI . '/assets/images/logo.svg' ); ?>" width="32" height="32" alt="" loading="lazy">
				<span class="mat-brand__text"><?php bloginfo( 'name' ); ?></span>
			</a>
			<p><?php esc_html_e( 'Free calculators and letter generators that help you triage a car insurance claim: what to do first, what it\'s worth, and how to push back if it\'s underpaid or denied.', 'myautotriage' ); ?></p>
			<?php
			$socials = array(
				'facebook'  => __( 'Facebook', 'myautotriage' ),
				'twitter'   => __( 'X (Twitter)', 'myautotriage' ),
				'youtube'   => __( 'YouTube', 'myautotriage' ),
				'instagram' => __( 'Instagram', 'myautotriage' ),
			);
			$has_social = false;
			foreach ( $socials as $key => $label ) {
				if ( get_theme_mod( "mat_social_{$key}" ) ) {
					$has_social = true;
					break;
				}
			}
			if ( $has_social ) :
				?>
				<div class="mat-social">
					<?php foreach ( $socials as $key => $label ) :
						$url = get_theme_mod( "mat_social_{$key}" );
						if ( ! $url ) {
							continue;
						}
						?>
						<a href="<?php echo esc_url( $url ); ?>" rel="me noopener" target="_blank"><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<nav class="mat-footer-col" aria-label="<?php esc_attr_e( 'Tools', 'myautotriage' ); ?>">
			<h2><?php esc_html_e( 'Free Tools', 'myautotriage' ); ?></h2>
			<ul>
				<?php foreach ( mat_get_tools_registry() as $tool ) :
					$page = get_page_by_path( $tool['slug'] );
					if ( ! $page ) {
						continue;
					}
					?>
					<li><a href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php echo esc_html( $tool['title'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<nav class="mat-footer-col" aria-label="<?php esc_attr_e( 'Footer', 'myautotriage' ); ?>">
			<h2><?php esc_html_e( 'Site', 'myautotriage' ); ?></h2>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'mat-menu',
				) );
			} else {
				$pages = array( 'about-us', 'contact', 'editorial-policy', 'privacy-policy', 'terms-of-use', 'disclaimer' );
				echo '<ul>';
				foreach ( $pages as $slug ) {
					$page = get_page_by_path( $slug );
					if ( $page ) {
						echo '<li><a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html( get_the_title( $page ) ) . '</a></li>';
					}
				}
				echo '</ul>';
			}
			?>
		</nav>
	</div>

	<div class="mat-container mat-site-footer__bottom">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'myautotriage' ); ?></p>
		<p class="mat-footer-disclaimer"><?php esc_html_e( 'MyAutoTriage is an independent educational resource. We are not a law firm, insurance company, or insurance agency, and nothing on this site is legal, financial, or insurance advice.', 'myautotriage' ); ?></p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
