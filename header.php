<?php
/**
 * The header for the theme.
 *
 * @package MyAutoTriage
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0f4c5c">
<link rel="icon" href="<?php echo esc_url( MAT_URI . '/assets/images/favicon.ico' ); ?>" sizes="any">
<link rel="icon" type="image/svg+xml" href="<?php echo esc_url( MAT_URI . '/assets/images/favicon.svg' ); ?>">
<link rel="apple-touch-icon" href="<?php echo esc_url( MAT_URI . '/assets/images/apple-touch-icon.png' ); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="mat-skip-link screen-reader-text" href="#mat-content"><?php esc_html_e( 'Skip to content', 'myautotriage' ); ?></a>

<header class="mat-site-header">
	<div class="mat-container mat-site-header__inner">
		<a class="mat-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php bloginfo( 'name' ); ?>">
			<img class="mat-brand__logo" src="<?php echo esc_url( MAT_URI . '/assets/images/logo.svg' ); ?>" width="36" height="36" alt="" loading="eager">
			<span class="mat-brand__text"><?php bloginfo( 'name' ); ?></span>
		</a>

		<button class="mat-nav-toggle" aria-expanded="false" aria-controls="mat-primary-menu">
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'myautotriage' ); ?></span>
			<span class="mat-nav-toggle__bar"></span>
			<span class="mat-nav-toggle__bar"></span>
			<span class="mat-nav-toggle__bar"></span>
		</button>

		<nav class="mat-primary-nav" id="mat-primary-menu" aria-label="<?php esc_attr_e( 'Primary', 'myautotriage' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'mat-menu',
					'fallback_cb'    => 'mat_default_primary_menu',
				) );
			} else {
				mat_default_primary_menu();
			}
			?>
		</nav>
	</div>
</header>

<?php if ( ! is_front_page() ) : ?>
<div class="mat-container">
	<?php mat_breadcrumbs(); ?>
</div>
<?php endif; ?>

<main id="mat-content" class="mat-site-main">
