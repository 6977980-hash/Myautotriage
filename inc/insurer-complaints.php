<?php
/**
 * Insurer complaint scorecard: a hub ranking the big auto insurers by
 * complaints to state regulators, and one page per insurer.
 *
 * The numbers come from assets/js/data/insurer-complaints.json, built by
 * bin/build-insurer-complaints.py from the Texas Department of Insurance
 * and New York DFS open-data portals. Rerun it once a year.
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MAT_INSURER_HUB_SLUG', 'insurer-complaints' );
// Bump to create pages for insurers added to the data file.
define( 'MAT_INSURER_PAGES_VERSION', '1' );
// Below this many complaints a group is too small to compare fairly.
define( 'MAT_INSURER_MIN_COMPLAINTS', 10 );

function mat_insurer_data() {
	static $data = null;
	if ( null === $data ) {
		$data = mat_read_theme_json( 'assets/js/data/insurer-complaints.json' );
	}
	return $data;
}

function mat_insurers() {
	$data = mat_insurer_data();
	return isset( $data['groups'] ) ? $data['groups'] : array();
}

function mat_insurer_hub_url() {
	return home_url( user_trailingslashit( MAT_INSURER_HUB_SLUG ) );
}

function mat_insurer_url( $insurer ) {
	return home_url( user_trailingslashit( MAT_INSURER_HUB_SLUG . '/' . $insurer['slug'] ) );
}

function mat_insurer_by_name( $name ) {
	foreach ( mat_insurers() as $insurer ) {
		if ( strtolower( $insurer['name'] ) === strtolower( $name ) ) {
			return $insurer;
		}
	}
	return null;
}

/**
 * The insurer shown on the current page, or null.
 */
function mat_current_insurer( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || 'page' !== $post->post_type || ! $post->post_parent ) {
		return null;
	}
	$parent = get_post( $post->post_parent );
	if ( ! $parent || MAT_INSURER_HUB_SLUG !== $parent->post_name ) {
		return null;
	}
	foreach ( mat_insurers() as $insurer ) {
		if ( $insurer['slug'] === $post->post_name ) {
			return $insurer;
		}
	}
	return null;
}

/**
 * One state's result for an insurer, ready to print: the index, how many
 * complaints it rests on, and a plain-English verdict. Null when the
 * insurer has no data in that state.
 */
function mat_insurer_score( $insurer, $state ) {
	if ( empty( $insurer[ $state ] ) || null === $insurer[ $state ]['index'] ) {
		return null;
	}
	$s     = $insurer[ $state ];
	$count = 'tx' === $state ? (int) $s['index_confirmed'] : (int) $s['upheld'];
	$index = (float) $s['index'];
	if ( $count < MAT_INSURER_MIN_COMPLAINTS ) {
		$verdict = 'few';
		$label   = __( 'Too few complaints to compare', 'myautotriage' );
	} elseif ( $index < 0.8 ) {
		$verdict = 'better';
		$label   = __( 'Fewer complaints than average', 'myautotriage' );
	} elseif ( $index <= 1.25 ) {
		$verdict = 'average';
		$label   = __( 'About average', 'myautotriage' );
	} else {
		$verdict = 'worse';
		$label   = __( 'More complaints than average', 'myautotriage' );
	}
	return array(
		'index'   => $index,
		'count'   => $count,
		'verdict' => $verdict,
		'label'   => $label,
	);
}

/**
 * "0.33" read as a sentence: "a third of the average", "2.7 times the average".
 */
function mat_insurer_index_words( $index ) {
	if ( $index <= 0 ) {
		return __( 'no upheld complaints', 'myautotriage' );
	}
	if ( $index >= 1.15 ) {
		/* translators: %s: multiple, e.g. 2.7 */
		return sprintf( __( '%s times the average', 'myautotriage' ), number_format_i18n( $index, $index >= 10 ? 0 : 1 ) );
	}
	if ( $index > 0.87 ) {
		return __( 'close to the average', 'myautotriage' );
	}
	/* translators: %s: percentage */
	return sprintf( __( '%s of the average', 'myautotriage' ), round( $index * 100 ) . '%' );
}

/**
 * Texas lists company names in capitals; show them in title case.
 */
function mat_insurer_company_name( $name ) {
	if ( strtoupper( $name ) !== $name ) {
		return $name;
	}
	$name = ucwords( strtolower( $name ), " \t-(" );
	$name = preg_replace_callback( '/\b(Usaa|Geico|Lm)\b/', function ( $m ) {
		return strtoupper( $m[1] );
	}, $name );
	return preg_replace_callback( '/(?<=\s)(Of|And|The|For)\b/', function ( $m ) {
		return strtolower( $m[1] );
	}, $name );
}

function mat_insurer_pct( $part, $whole ) {
	return $whole ? round( $part / $whole * 100 ) . '%' : '–';
}

/**
 * Short answer for an insurer page, built from the numbers.
 */
function mat_insurer_summary( $insurer ) {
	$data  = mat_insurer_data();
	$parts = array();
	$tx    = mat_insurer_score( $insurer, 'tx' );
	$ny    = mat_insurer_score( $insurer, 'ny' );
	if ( $tx && 'few' !== $tx['verdict'] ) {
		/* translators: 1: year, 2: insurer, 3: comparison, 4: complaints */
		$parts[] = sprintf( __( 'In Texas in %1$s, %2$s\'s rate of confirmed auto complaints per policy was %3$s for all auto insurers (%4$s confirmed complaints).', 'myautotriage' ), $data['tx_year'], $insurer['name'], mat_insurer_index_words( $tx['index'] ), number_format_i18n( $tx['count'] ) );
	}
	if ( $ny && 'few' !== $ny['verdict'] ) {
		/* translators: 1: year, 2: insurer, 3: comparison, 4: complaints */
		$parts[] = sprintf( __( 'In New York\'s %1$s ranking, %2$s\'s rate of upheld complaints per premium dollar was %3$s (%4$s upheld complaints).', 'myautotriage' ), $data['ny_year'], $insurer['name'], mat_insurer_index_words( $ny['index'] ), number_format_i18n( $ny['count'] ) );
	}
	if ( ! $parts ) {
		/* translators: %s: insurer */
		return sprintf( __( '%s had too few complaints in the Texas and New York data to compare fairly with other insurers.', 'myautotriage' ), $insurer['name'] );
	}
	return implode( ' ', $parts );
}

function mat_insurer_sources() {
	$data = mat_insurer_data();
	return array(
		array(
			/* translators: %s: year */
			'label' => sprintf( __( 'Texas Department of Insurance, Complaint indexes and policy counts for insurance companies (%s, automobile)', 'myautotriage' ), $data['tx_year'] ),
			'url'   => 'https://data.texas.gov/dataset/Complaint-indexes-and-policy-counts-for-insurance-c/pa9u-9s9w',
		),
		array(
			'label' => __( 'Texas Department of Insurance, Insurance complaints: All data', 'myautotriage' ),
			'url'   => 'https://data.texas.gov/dataset/Insurance-complaints-All-data/ubdr-4uff',
		),
		array(
			/* translators: %s: year */
			'label' => sprintf( __( 'New York Department of Financial Services, Automobile Insurance Company Complaint Rankings (filing year %s)', 'myautotriage' ), $data['ny_year'] ),
			'url'   => 'https://data.ny.gov/Government-Finance/Automobile-Insurance-Company-Complaint-Rankings-Be/h2wd-9xfe',
		),
		array(
			'label' => __( 'NAIC, How to file a complaint with your state insurance department', 'myautotriage' ),
			'url'   => 'https://content.naic.org/consumer/how-to-file-complaint',
		),
	);
}

/**
 * Tools to point people to when an insurer is dragging its feet.
 */
function mat_insurer_next_steps() {
	$letter = mat_url_for_slug( 'demand-letter-generator' );
	$steps  = array(
		array( mat_url_for_slug( 'claim-triage' ), __( 'Not sure what to do next? Answer three questions in the claim triage', 'myautotriage' ) ),
		array( mat_url_for_slug( 'claim-diary' ), __( 'Log every call and promise in a claim diary with deadline alerts', 'myautotriage' ) ),
		array( $letter ? add_query_arg( 'type', 'doi-complaint', $letter ) : '', __( 'Write a complaint to your state insurance department', 'myautotriage' ) ),
		array( mat_url_for_slug( 'total-loss-valuation-checker' ), __( 'Check a total loss valuation report for errors', 'myautotriage' ) ),
		array( function_exists( 'mat_adjuster_hub_url' ) ? mat_adjuster_hub_url() : '', __( 'What the adjuster\'s words really mean, with replies to copy', 'myautotriage' ) ),
	);
	return array_filter( $steps, function ( $s ) {
		return ! empty( $s[0] );
	} );
}

/**
 * Create the hub and insurer pages. Runs on wp_loaded, so deploying the
 * theme is enough.
 */
function mat_ensure_insurer_pages() {
	if ( MAT_INSURER_PAGES_VERSION === get_option( 'mat_insurer_pages_version' ) || ! mat_insurers() ) {
		return;
	}
	update_option( 'mat_insurer_pages_version', MAT_INSURER_PAGES_VERSION );

	$hub = get_page_by_path( MAT_INSURER_HUB_SLUG );
	if ( ! $hub ) {
		$hub_id = wp_insert_post( array(
			'post_title'   => __( 'Car Insurance Company Complaint Ratings', 'myautotriage' ),
			'post_name'    => MAT_INSURER_HUB_SLUG,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'page',
		) );
		if ( ! $hub_id || is_wp_error( $hub_id ) ) {
			return;
		}
	} else {
		$hub_id = $hub->ID;
	}
	update_post_meta( $hub_id, '_wp_page_template', 'page-templates/template-insurer-hub.php' );

	foreach ( mat_insurers() as $i => $insurer ) {
		if ( get_page_by_path( MAT_INSURER_HUB_SLUG . '/' . $insurer['slug'] ) ) {
			continue;
		}
		$post_id = wp_insert_post( array(
			/* translators: %s: insurer */
			'post_title'   => sprintf( __( '%s Claim Complaints', 'myautotriage' ), $insurer['name'] ),
			'post_name'    => $insurer['slug'],
			'post_parent'  => $hub_id,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'menu_order'   => $i,
		) );
		if ( $post_id && ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, '_wp_page_template', 'page-templates/template-insurer.php' );
		}
	}
}
add_action( 'wp_loaded', 'mat_ensure_insurer_pages' );

function mat_insurer_seo_title( $insurer ) {
	/* translators: %s: insurer */
	return sprintf( __( '%s Auto Claim Complaints: Ratings From State Regulators', 'myautotriage' ), $insurer['name'] );
}

function mat_insurer_meta( $insurer ) {
	/* translators: %s: insurer */
	return sprintf( __( 'How many auto claim complaints %s gets compared with other insurers, what people complain about, and how often complaining got them paid. Texas and New York regulator data.', 'myautotriage' ), $insurer['name'] );
}
