<?php
/**
 * Theme Customizer: the handful of settings a non-developer actually needs
 * — analytics/AdSense codes, contact email, and social links — without
 * touching PHP.
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mat_customize_register( $wp_customize ) {

	$wp_customize->add_section( 'mat_site_settings', array(
		'title'    => __( 'MyAutoTriage Settings', 'myautotriage' ),
		'priority' => 30,
	) );

	$wp_customize->add_setting( 'mat_tagline_override', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'mat_tagline_override', array(
		'label'   => __( 'Homepage hero tagline (optional)', 'myautotriage' ),
		'section' => 'mat_site_settings',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'mat_contact_email', array(
		'default'           => 'hello@myautotriage.com',
		'sanitize_callback' => 'sanitize_email',
	) );
	$wp_customize->add_control( 'mat_contact_email', array(
		'label'   => __( 'Public contact email', 'myautotriage' ),
		'section' => 'mat_site_settings',
		'type'    => 'email',
	) );

	$wp_customize->add_setting( 'mat_ga_measurement_id', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'mat_ga_measurement_id', array(
		'label'       => __( 'Google Analytics 4 Measurement ID (e.g. G-XXXXXXX)', 'myautotriage' ),
		'description' => __( 'Leave blank until you have created a GA4 property.', 'myautotriage' ),
		'section'     => 'mat_site_settings',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'mat_adsense_client_id', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'mat_adsense_client_id', array(
		'label'       => __( 'AdSense Publisher ID (e.g. ca-pub-1234567890123456)', 'myautotriage' ),
		'description' => __( 'Only add this once your AdSense account exists — it loads the site-verification script and ads.txt line automatically.', 'myautotriage' ),
		'section'     => 'mat_site_settings',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'mat_search_console_meta', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'mat_search_console_meta', array(
		'label'       => __( 'Google Search Console verification content (the value only, not the full tag)', 'myautotriage' ),
		'section'     => 'mat_site_settings',
		'type'        => 'text',
	) );

	foreach ( array( 'facebook', 'twitter', 'youtube', 'instagram' ) as $network ) {
		$wp_customize->add_setting( "mat_social_{$network}", array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( "mat_social_{$network}", array(
			/* translators: %s: social network name */
			'label'   => sprintf( __( '%s URL', 'myautotriage' ), ucfirst( $network ) ),
			'section' => 'mat_site_settings',
			'type'    => 'url',
		) );
	}
}
add_action( 'customize_register', 'mat_customize_register' );

/**
 * Output GA4 / Search Console / AdSense snippets. Each is genuinely
 * optional and only prints once a value has been entered — a fresh install
 * has zero third-party scripts, by design.
 */
function mat_head_thirdparty() {
	$search_console = get_theme_mod( 'mat_search_console_meta' );
	if ( $search_console ) {
		echo '<meta name="google-site-verification" content="' . esc_attr( $search_console ) . "\" />\n";
	}

	$ga_id = get_theme_mod( 'mat_ga_measurement_id' );
	if ( $ga_id ) {
		?>
		<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga_id ); ?>"></script>
		<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());
			gtag('config', '<?php echo esc_js( $ga_id ); ?>');
		</script>
		<?php
	}

	$adsense_id = get_theme_mod( 'mat_adsense_client_id' );
	if ( $adsense_id ) {
		?>
		<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?php echo esc_attr( $adsense_id ); ?>" crossorigin="anonymous"></script>
		<?php
	}
}
add_action( 'wp_head', 'mat_head_thirdparty', 20 );
