<?php
/**
 * Insurer complaint scorecard: a hub ranking the big auto insurers by
 * complaints to state regulators, and one page per insurer.
 *
 * The numbers come from assets/js/data/insurer-complaints.json, built by
 * bin/build-insurer-complaints.py from the Texas Department of Insurance
 * and New York DFS open-data portals and the California Department of
 * Insurance's auto complaint report. Rerun it once a year.
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
	$keys  = array(
		'tx' => 'index_confirmed',
		'ny' => 'upheld',
		'ca' => 'justified',
	);
	$count = (int) $s[ $keys[ $state ] ];
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
	if ( ', THE' === substr( $name, -5 ) ) {
		$name = 'THE ' . substr( $name, 0, -5 );
	}
	$name = ucwords( strtolower( $name ), " \t-(" );
	$name = preg_replace_callback( '/\b(Usaa|Geico|Lm)\b/', function ( $m ) {
		return strtoupper( $m[1] );
	}, $name );
	return preg_replace_callback( '/(?<=\s)(Of|And|The|For)\b/', function ( $m ) {
		return strtolower( $m[1] );
	}, $name );
}

/**
 * "State Farm's", "Farmers'".
 */
function mat_insurer_possessive( $name ) {
	return "s" === substr( $name, -1 ) ? $name . "'" : $name . "'s";
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
	$ca    = mat_insurer_score( $insurer, 'ca' );
	if ( $tx && 'few' !== $tx['verdict'] ) {
		/* translators: 1: year, 2: insurer's (possessive), 3: comparison, 4: complaints */
		$parts[] = sprintf( __( 'In Texas in %1$s, %2$s rate of confirmed auto complaints per policy was %3$s for all auto insurers (%4$s confirmed complaints).', 'myautotriage' ), $data['tx_year'], mat_insurer_possessive( $insurer['name'] ), mat_insurer_index_words( $tx['index'] ), number_format_i18n( $tx['count'] ) );
	}
	if ( $ny && 'few' !== $ny['verdict'] ) {
		/* translators: 1: year, 2: insurer's (possessive), 3: comparison, 4: complaints */
		$parts[] = sprintf( __( 'In New York\'s %1$s ranking, %2$s rate of upheld complaints per premium dollar was %3$s (%4$s upheld complaints).', 'myautotriage' ), $data['ny_year'], mat_insurer_possessive( $insurer['name'] ), mat_insurer_index_words( $ny['index'] ), number_format_i18n( $ny['count'] ) );
	}
	if ( $ca && 'few' !== $ca['verdict'] ) {
		/* translators: 1: year, 2: insurer's (possessive), 3: comparison, 4: complaints */
		$parts[] = sprintf( __( 'In California\'s %1$s report on the 50 largest auto insurers, %2$s rate of justified complaints per insured vehicle was %3$s (%4$s justified complaints).', 'myautotriage' ), $data['ca_year'], mat_insurer_possessive( $insurer['name'] ), mat_insurer_index_words( $ca['index'] ), number_format_i18n( $ca['count'] ) );
	}
	if ( ! $parts ) {
		/* translators: %s: insurer */
		return sprintf( __( '%s had too few complaints in the Texas, New York and California data to compare fairly with other insurers.', 'myautotriage' ), $insurer['name'] );
	}
	return implode( ' ', $parts );
}

/**
 * Facts only this insurer's numbers support, so each page says something
 * the others can't: which companies are counted, how its complaint mix
 * differs from the market, how the index moved, and where its New York
 * and California companies rank. Returns paragraphs of plain text.
 */
function mat_insurer_profile( $insurer ) {
	$data  = mat_insurer_data();
	$paras = array();
	$tx_co = array();
	$ny_co = array();
	$ca_co = array();
	foreach ( $insurer['companies'] as $co ) {
		if ( 'TX' === $co['state'] ) {
			$tx_co[] = $co;
		} elseif ( 'NY' === $co['state'] ) {
			$ny_co[] = $co;
		} else {
			$ca_co[] = $co;
		}
	}

	if ( $tx_co ) {
		usort( $tx_co, function ( $a, $b ) {
			return $b['policies'] - $a['policies'];
		} );
		$total = max( 1, (int) $insurer['tx']['policies'] );
		$drives = $tx_co[0]['policies'] * 2 > $total;
		/* translators: 1: number of companies, 2: insurer, 3: largest company, 4: its share, 5: policies */
		$paras[] = sprintf( 1 === count( $tx_co ) ? __( 'In Texas this page counts 1 company selling as %2$s: %3$s, with %5$s auto policies in force.', 'myautotriage' ) : ( $drives ? __( 'In Texas this page counts %1$s companies selling as %2$s. The largest, %3$s, holds %4$s of the brand\'s %5$s auto policies in force there, so its record drives the score.', 'myautotriage' ) : __( 'In Texas this page counts %1$s companies selling as %2$s, with %5$s auto policies in force between them; the largest, %3$s, holds %4$s.', 'myautotriage' ) ), number_format_i18n( count( $tx_co ) ), $insurer['name'], mat_insurer_company_name( $tx_co[0]['name'] ), mat_insurer_pct( $tx_co[0]['policies'], $total ), number_format_i18n( $total ) );
	}

	$tx = isset( $insurer['tx'] ) ? $insurer['tx'] : null;
	if ( $tx && ! empty( $tx['complaints'] ) && $tx['complaints'] >= MAT_INSURER_MIN_COMPLAINTS ) {
		$market = array();
		foreach ( $data['tx_market']['reasons'] as $r ) {
			$market[ $r['reason'] ] = $r['count'];
		}
		foreach ( $tx['reasons'] as $r ) {
			if ( ! isset( $market[ $r['reason'] ] ) ) {
				continue;
			}
			$own = $r['count'] / $tx['complaints'];
			$all = $market[ $r['reason'] ] / $data['tx_market']['complaints'];
			if ( abs( $own - $all ) < 0.05 ) {
				continue;
			}
			/* translators: 1: reason, 2: share for insurer, 3: insurer, 4: market share */
			$paras[] = sprintf( __( '"%1$s" came up in %2$s of Texas complaints about %3$s, against %4$s for all Texas auto insurers.', 'myautotriage' ), $r['reason'], mat_insurer_pct( $r['count'], $tx['complaints'] ), $insurer['name'], mat_insurer_pct( $market[ $r['reason'] ], $data['tx_market']['complaints'] ) );
			break;
		}
	}

	if ( $tx && ! empty( $tx['prev'] ) && $tx['index_confirmed'] >= MAT_INSURER_MIN_COMPLAINTS && $tx['prev']['confirmed'] >= MAT_INSURER_MIN_COMPLAINTS ) {
		$diff = $tx['index'] - $tx['prev']['index'];
		if ( abs( $diff ) < 0.1 ) {
			/* translators: 1: insurer's (possessive), 2: earlier year, 3: earlier index, 4: year, 5: index */
			$paras[] = sprintf( __( '%1$s Texas complaint index barely moved: %3$s in %2$s and %5$s in %4$s.', 'myautotriage' ), mat_insurer_possessive( $insurer['name'] ), $tx['prev']['year'], number_format_i18n( $tx['prev']['index'], 2 ), $data['tx_year'], number_format_i18n( $tx['index'], 2 ) );
		} else {
			/* translators: 1: insurer's (possessive), 2: rose or fell, 3: earlier year, 4: earlier index, 5: year, 6: index, 7: earlier complaints, 8: complaints */
			$paras[] = sprintf( __( '%1$s Texas complaint index %2$s from %4$s in %3$s to %6$s in %5$s (%7$s confirmed complaints, then %8$s).', 'myautotriage' ), mat_insurer_possessive( $insurer['name'] ), $diff > 0 ? __( 'rose', 'myautotriage' ) : __( 'fell', 'myautotriage' ), $tx['prev']['year'], number_format_i18n( $tx['prev']['index'], 2 ), $data['tx_year'], number_format_i18n( $tx['index'], 2 ), number_format_i18n( $tx['prev']['confirmed'] ), number_format_i18n( $tx['index_confirmed'] ) );
		}
	}

	if ( $tx && $tx['index'] > 5 ) {
		$next = null;
		foreach ( mat_insurers() as $other ) {
			$score = $other['slug'] !== $insurer['slug'] ? mat_insurer_score( $other, 'tx' ) : null;
			if ( $score && 'few' !== $score['verdict'] && ( ! $next || $score['index'] > $next[1] ) ) {
				$next = array( $other['name'], $score['index'] );
			}
		}
		/* translators: 1: index, 2: confirmed complaints, 3: policies */
		$line = sprintf( __( 'An index of %1$s is far above any other brand on this site. It is the figure as published by the Texas Department of Insurance, and it rests on %2$s confirmed complaints against %3$s policies, so it is not a small-sample swing.', 'myautotriage' ), number_format_i18n( $tx['index'], 2 ), number_format_i18n( $tx['index_confirmed'] ), number_format_i18n( $tx['policies'] ) );
		if ( $next ) {
			/* translators: 1: insurer, 2: index */
			$line .= ' ' . sprintf( __( 'The next highest is %1$s at %2$s.', 'myautotriage' ), $next[0], number_format_i18n( $next[1], 2 ) );
		}
		$paras[] = $line;
	}

	if ( $ny_co ) {
		$ranks = wp_list_pluck( $ny_co, 'rank' );
		$min   = min( $ranks );
		$max   = max( $ranks );
		$paras[] = $min === $max
			/* translators: 1: insurer's (possessive), 2: rank, 3: companies ranked, 4: year */
			? sprintf( __( 'In New York\'s %4$s ranking, %1$s company placed %2$s of %3$s auto insurers (1 = fewest upheld complaints for its premium).', 'myautotriage' ), mat_insurer_possessive( $insurer['name'] ), number_format_i18n( $min ), number_format_i18n( $data['ny_market']['companies'] ), $data['ny_year'] )
			/* translators: 1: number of companies, 2: insurer, 3: best rank, 4: worst rank, 5: companies ranked, 6: year */
			: sprintf( __( 'In New York\'s %6$s ranking, the %1$s companies selling as %2$s placed between %3$s and %4$s of %5$s auto insurers (1 = fewest upheld complaints for its premium).', 'myautotriage' ), number_format_i18n( count( $ny_co ) ), $insurer['name'], number_format_i18n( $min ), number_format_i18n( $max ), number_format_i18n( $data['ny_market']['companies'] ), $data['ny_year'] );
	}

	if ( $ca_co ) {
		$ranks = wp_list_pluck( $ca_co, 'rank' );
		$min   = min( $ranks );
		$max   = max( $ranks );
		$paras[] = $min === $max
			/* translators: 1: insurer's (possessive), 2: rank, 3: companies ranked, 4: year */
			? sprintf( __( 'In California\'s %4$s report, %1$s company placed %2$s of the %3$s largest auto insurers (1 = fewest justified complaints for the vehicles it insures).', 'myautotriage' ), mat_insurer_possessive( $insurer['name'] ), number_format_i18n( $min ), number_format_i18n( $data['ca_market']['companies'] ), $data['ca_year'] )
			/* translators: 1: number of companies, 2: insurer, 3: best rank, 4: worst rank, 5: companies ranked, 6: year */
			: sprintf( __( 'In California\'s %6$s report, the %1$s companies selling as %2$s placed between %3$s and %4$s of the %5$s largest auto insurers (1 = fewest justified complaints for the vehicles it insures).', 'myautotriage' ), number_format_i18n( count( $ca_co ) ), $insurer['name'], number_format_i18n( $min ), number_format_i18n( $max ), number_format_i18n( $data['ca_market']['companies'] ), $data['ca_year'] );
		$ca   = $insurer['ca'];
		$prev = (int) reset( $ca['justified_prev'] );
		if ( max( $prev, $ca['justified'] ) >= MAT_INSURER_MIN_COMPLAINTS && abs( $ca['justified'] - $prev ) >= max( 10, $prev ) ) {
			/* translators: 1: insurer's (possessive), 2: complaints, 3: year, 4: earlier complaints, 5: earlier year */
			$paras[] = sprintf( __( 'The California Department of Insurance counted %2$s justified complaints against %1$s California companies in %3$s, against %4$s in %5$s.', 'myautotriage' ), mat_insurer_possessive( $insurer['name'] ), number_format_i18n( $ca['justified'] ), $data['ca_year'], number_format_i18n( $prev ), key( $ca['justified_prev'] ) );
		}
	}

	return $paras;
}

/**
 * The index FAQ, read with this insurer's own score.
 */
function mat_insurer_index_faq( $insurer ) {
	$answer = __( '1.00 is the average for all auto insurers in that state, adjusted for size. A score of 0.50 means half as many complaints as expected for the company\'s size; 2.00 means twice as many.', 'myautotriage' );
	$tx     = mat_insurer_score( $insurer, 'tx' );
	$score  = null;
	foreach ( array(
		'tx' => __( 'Texas', 'myautotriage' ),
		'ny' => __( 'New York', 'myautotriage' ),
		'ca' => __( 'California', 'myautotriage' ),
	) as $key => $state ) {
		$s = mat_insurer_score( $insurer, $key );
		if ( $s && 'few' !== $s['verdict'] ) {
			$score = array( $state, $s );
			break;
		}
	}
	if ( $score ) {
		/* translators: 1: insurer's (possessive), 2: state, 3: index, 4: comparison */
		$answer .= ' ' . sprintf( __( '%1$s %2$s score of %3$s means %4$s.', 'myautotriage' ), mat_insurer_possessive( $insurer['name'] ), $score[0], number_format_i18n( $score[1]['index'], 2 ), mat_insurer_index_words( $score[1]['index'] ) );
	}
	return $answer;
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
			/* translators: %s: year */
			'label' => sprintf( __( 'California Department of Insurance, Automobile Complaint Composite Report (complaint year %s)', 'myautotriage' ), $data['ca_year'] ),
			'url'   => 'https://www.insurance.ca.gov/01-consumers/120-company/03-concmplt/autocomposite.cfm',
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
	return sprintf( __( '%s Auto Claim Complaints: State Regulator Ratings', 'myautotriage' ), $insurer['name'] );
}

function mat_insurer_meta( $insurer ) {
	/* translators: %s: insurer */
	return sprintf( __( '%s auto claim complaints vs other insurers: what people complain about and how often it paid off. Texas, New York and California data.', 'myautotriage' ), $insurer['name'] );
}
