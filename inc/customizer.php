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

	$wp_customize->add_setting( 'mat_brand_profiles', array(
		'default'           => '',
		'sanitize_callback' => 'mat_sanitize_url_lines',
	) );
	$wp_customize->add_control( 'mat_brand_profiles', array(
		'label'       => __( 'Other official profiles (one URL per line)', 'myautotriage' ),
		'description' => __( 'LinkedIn company page, Crunchbase, Google Business Profile and similar. Added to the Organization schema as sameAs so search engines connect them to this site.', 'myautotriage' ),
		'section'     => 'mat_site_settings',
		'type'        => 'textarea',
	) );

	// Expert reviewer: nothing about a reviewer is shown anywhere until a
	// name is entered here.
	$wp_customize->add_section( 'mat_reviewer', array(
		'title'       => __( 'Expert Reviewer', 'myautotriage' ),
		'description' => __( 'A licensed adjuster, attorney or agent who fact-checks the guides and state pages. Leave the name empty to hide all reviewer details.', 'myautotriage' ),
		'priority'    => 31,
	) );
	$fields = array(
		'mat_reviewer_name'        => array( __( 'Reviewer name', 'myautotriage' ), 'text', 'sanitize_text_field' ),
		'mat_reviewer_credentials' => array( __( 'Credentials (e.g. Licensed P&C adjuster, Texas)', 'myautotriage' ), 'text', 'sanitize_text_field' ),
		'mat_reviewer_url'         => array( __( 'Profile URL (LinkedIn, firm bio or license lookup)', 'myautotriage' ), 'url', 'esc_url_raw' ),
		'mat_reviewer_bio'         => array( __( 'Short bio (one or two sentences)', 'myautotriage' ), 'textarea', 'sanitize_textarea_field' ),
	);
	foreach ( $fields as $id => $field ) {
		$wp_customize->add_setting( $id, array(
			'default'           => '',
			'sanitize_callback' => $field[2],
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $field[0],
			'section' => 'mat_reviewer',
			'type'    => $field[1],
		) );
	}
}
add_action( 'customize_register', 'mat_customize_register' );

function mat_sanitize_url_lines( $value ) {
	$urls = array();
	foreach ( preg_split( '/\s+/', (string) $value ) as $line ) {
		$url = esc_url_raw( trim( $line ) );
		if ( $url ) {
			$urls[] = $url;
		}
	}
	return implode( "\n", array_unique( $urls ) );
}

/**
 * The expert reviewer, or null when none is set.
 */
function mat_reviewer() {
	$name = trim( (string) get_theme_mod( 'mat_reviewer_name' ) );
	if ( '' === $name ) {
		return null;
	}
	return array(
		'name'        => $name,
		'credentials' => trim( (string) get_theme_mod( 'mat_reviewer_credentials' ) ),
		'url'         => (string) get_theme_mod( 'mat_reviewer_url' ),
		'bio'         => trim( (string) get_theme_mod( 'mat_reviewer_bio' ) ),
	);
}

/**
 * "Reviewed by Jane Doe, Licensed P&C adjuster" with a link when a profile
 * URL is set. Empty string when there is no reviewer.
 */
function mat_reviewer_byline() {
	$r = mat_reviewer();
	if ( ! $r ) {
		return '';
	}
	$name = $r['url'] ? '<a href="' . esc_url( $r['url'] ) . '" rel="noopener">' . esc_html( $r['name'] ) . '</a>' : esc_html( $r['name'] );
	return esc_html__( 'Reviewed by', 'myautotriage' ) . ' ' . $name . ( $r['credentials'] ? ', ' . esc_html( $r['credentials'] ) : '' );
}

/**
 * Official profile URLs for Organization sameAs: the social links plus the
 * extra profiles list.
 */
function mat_brand_same_as() {
	$urls = array();
	foreach ( array( 'facebook', 'twitter', 'youtube', 'instagram' ) as $network ) {
		$url = get_theme_mod( "mat_social_{$network}" );
		if ( $url ) {
			$urls[] = $url;
		}
	}
	foreach ( preg_split( '/\s+/', (string) get_theme_mod( 'mat_brand_profiles' ) ) as $url ) {
		if ( $url ) {
			$urls[] = $url;
		}
	}
	return array_values( array_unique( array_filter( array_map( 'esc_url_raw', $urls ) ) ) );
}

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
