<?php
/**
 * Claim Outcome Survey and the Lowball Index.
 *
 * Visitors who finished a claim can share, anonymously, the first offer,
 * the final amount and what they did in between. Answers go into one
 * custom table with no name, email or IP address. The Lowball Index page
 * publishes only aggregates, and only for groups with at least
 * MAT_OUTCOMES_MIN answers, so no single answer can be picked out.
 *
 * Spam: a honeypot field, a minimum time on the form, sanity limits on the
 * amounts, and a per-visitor rate limit keyed on a salted hash of the IP
 * that lives in a transient for an hour (the IP itself is never stored).
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MAT_OUTCOMES_DB_VERSION', '1' );
define( 'MAT_OUTCOMES_MIN', 30 );
define( 'MAT_OUTCOMES_SURVEY_SLUG', 'claim-outcome-survey' );
define( 'MAT_OUTCOMES_INDEX_SLUG', 'lowball-index' );

function mat_outcomes_table() {
	global $wpdb;
	return $wpdb->prefix . 'mat_claim_outcomes';
}

function mat_outcomes_install() {
	if ( MAT_OUTCOMES_DB_VERSION === get_option( 'mat_outcomes_db_version' ) ) {
		return;
	}
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table   = mat_outcomes_table();
	$charset = $wpdb->get_charset_collate();
	dbDelta( "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		created_month char(7) NOT NULL,
		state char(2) NOT NULL DEFAULT '',
		insurer varchar(40) NOT NULL DEFAULT '',
		claim_type varchar(20) NOT NULL,
		party varchar(10) NOT NULL,
		first_offer decimal(10,2) NOT NULL,
		final_amount decimal(10,2) NOT NULL,
		steps varchar(120) NOT NULL DEFAULT '',
		status varchar(10) NOT NULL,
		flagged tinyint(1) NOT NULL DEFAULT 0,
		PRIMARY KEY  (id),
		KEY claim_type (claim_type),
		KEY insurer (insurer)
	) {$charset};" );
	update_option( 'mat_outcomes_db_version', MAT_OUTCOMES_DB_VERSION );
}
add_action( 'wp_loaded', 'mat_outcomes_install' );

function mat_outcomes_choices() {
	return array(
		'claim_type' => array(
			'total-loss' => __( 'Total loss (car was totaled)', 'myautotriage' ),
			'repair'     => __( 'Repair or property damage', 'myautotriage' ),
			'dv'         => __( 'Diminished value', 'myautotriage' ),
			'other'      => __( 'Something else', 'myautotriage' ),
		),
		'party'      => array(
			'own'   => __( 'My own insurer', 'myautotriage' ),
			'other' => __( 'The other driver\'s insurer', 'myautotriage' ),
		),
		'status'     => array(
			'settled' => __( 'Settled or paid', 'myautotriage' ),
			'open'    => __( 'Still open, this is the latest offer', 'myautotriage' ),
		),
		'steps'      => array(
			'counter'   => __( 'Sent a written counter-offer or demand letter', 'myautotriage' ),
			'evidence'  => __( 'Sent my own estimates or comparable listings', 'myautotriage' ),
			'appraisal' => __( 'Invoked the appraisal clause', 'myautotriage' ),
			'complaint' => __( 'Complained to the state insurance department', 'myautotriage' ),
			'lawyer'    => __( 'Hired a lawyer or public adjuster', 'myautotriage' ),
			'court'     => __( 'Went to small claims or other court', 'myautotriage' ),
		),
		'insurer'    => array(
			'State Farm', 'Progressive', 'GEICO', 'Allstate', 'USAA', 'Liberty Mutual', 'Farmers', 'Nationwide',
			'Travelers', 'American Family', 'Erie', 'Auto-Owners', 'Kemper', 'Mercury', 'Other',
		),
	);
}

/**
 * Validate and store one answer. Returns true or a WP_Error.
 */
function mat_outcomes_save( $in ) {
	$choices = mat_outcomes_choices();
	if ( ! empty( $in['website'] ) ) {
		return new WP_Error( 'spam', 'Thanks!' ); // Honeypot: look successful, store nothing.
	}
	if ( empty( $in['elapsed'] ) || (int) $in['elapsed'] < 3000 ) {
		return new WP_Error( 'too_fast', __( 'That was quick. Please check your answers and send again.', 'myautotriage' ) );
	}
	$type   = isset( $in['claim_type'] ) ? (string) $in['claim_type'] : '';
	$party  = isset( $in['party'] ) ? (string) $in['party'] : '';
	$status = isset( $in['status'] ) ? (string) $in['status'] : '';
	if ( ! isset( $choices['claim_type'][ $type ], $choices['party'][ $party ], $choices['status'][ $status ] ) ) {
		return new WP_Error( 'invalid', __( 'Please answer the claim type, whose insurer and whether it is settled.', 'myautotriage' ) );
	}
	$first = isset( $in['first_offer'] ) ? (float) $in['first_offer'] : 0;
	$final = isset( $in['final_amount'] ) ? (float) $in['final_amount'] : 0;
	if ( $first < 1 || $first > 500000 || $final < 0 || $final > 500000 ) {
		return new WP_Error( 'amounts', __( 'Please enter both amounts in dollars, up to $500,000.', 'myautotriage' ) );
	}
	$state = isset( $in['state'] ) ? strtoupper( (string) $in['state'] ) : '';
	if ( '' !== $state && ! array_key_exists( $state, mat_state_laws() ) ) {
		$state = '';
	}
	$insurer = isset( $in['insurer'] ) && in_array( $in['insurer'], $choices['insurer'], true ) ? $in['insurer'] : '';
	$steps   = array();
	if ( ! empty( $in['steps'] ) && is_array( $in['steps'] ) ) {
		foreach ( $in['steps'] as $s ) {
			if ( isset( $choices['steps'][ $s ] ) ) {
				$steps[] = $s;
			}
		}
	}

	// One salted, hashed key per visitor per hour; the IP itself is never kept.
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : ''; // phpcs:ignore
	$key = 'mat_out_' . substr( hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) ), 0, 20 );
	$n   = (int) get_transient( $key );
	if ( $n >= 3 ) {
		return new WP_Error( 'rate', __( 'Thanks, we already have your answers. You can send more in an hour.', 'myautotriage' ) );
	}
	set_transient( $key, $n + 1, HOUR_IN_SECONDS );

	// Answers that are probably typos stay out of the published numbers.
	$flagged = ( $final > 0 && ( $final / $first > 10 || $final / $first < 0.1 ) ) ? 1 : 0;

	global $wpdb;
	$ok = $wpdb->insert( mat_outcomes_table(), array(
		'created_month' => gmdate( 'Y-m' ),
		'state'         => $state,
		'insurer'       => $insurer,
		'claim_type'    => $type,
		'party'         => $party,
		'first_offer'   => round( $first, 2 ),
		'final_amount'  => round( $final, 2 ),
		'steps'         => implode( ',', array_unique( $steps ) ),
		'status'        => $status,
		'flagged'       => $flagged,
	) );
	delete_transient( 'mat_outcomes_stats' );
	return $ok ? true : new WP_Error( 'db', __( 'Sorry, that didn\'t save. Please try again later.', 'myautotriage' ) );
}

function mat_outcomes_rest() {
	register_rest_route( 'mat/v1', '/outcome', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => function ( WP_REST_Request $req ) {
			$r = mat_outcomes_save( $req->get_json_params() ?: $req->get_params() );
			if ( is_wp_error( $r ) ) {
				if ( 'spam' === $r->get_error_code() ) {
					return array( 'ok' => true );
				}
				return new WP_REST_Response( array( 'ok' => false, 'message' => $r->get_error_message() ), 400 );
			}
			return array( 'ok' => true );
		},
	) );
}
add_action( 'rest_api_init', 'mat_outcomes_rest' );

function mat_median( $values ) {
	sort( $values );
	$n = count( $values );
	if ( ! $n ) {
		return 0;
	}
	$mid = (int) floor( $n / 2 );
	return $n % 2 ? $values[ $mid ] : ( $values[ $mid - 1 ] + $values[ $mid ] ) / 2;
}

/**
 * Summary for one group of answers: count, median change from the first
 * offer, share that went up, and the same split by whether a written
 * counter-offer or evidence was sent.
 */
function mat_outcomes_summary( $rows ) {
	$change = array();
	$up     = 0;
	$pushed = array();
	$quiet  = array();
	foreach ( $rows as $r ) {
		$pct      = ( $r->final_amount - $r->first_offer ) / $r->first_offer;
		$change[] = $pct;
		if ( $r->final_amount > $r->first_offer ) {
			$up++;
		}
		if ( preg_match( '/counter|evidence|appraisal|complaint|lawyer|court/', $r->steps ) ) {
			$pushed[] = $pct;
		} else {
			$quiet[] = $pct;
		}
	}
	return array(
		'n'             => count( $rows ),
		'median_change' => mat_median( $change ),
		'share_up'      => count( $rows ) ? $up / count( $rows ) : 0,
		'pushed_n'      => count( $pushed ),
		'pushed_median' => mat_median( $pushed ),
		'quiet_n'       => count( $quiet ),
		'quiet_median'  => mat_median( $quiet ),
	);
}

/**
 * Published numbers, cached for an hour and cleared on each new answer.
 */
function mat_outcomes_stats() {
	$cached = get_transient( 'mat_outcomes_stats' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	global $wpdb;
	$table = mat_outcomes_table();
	$rows  = $wpdb->get_results( "SELECT claim_type, insurer, first_offer, final_amount, steps FROM {$table} WHERE flagged = 0 AND status = 'settled'" ); // phpcs:ignore -- no user input
	$rows  = is_array( $rows ) ? $rows : array();
	$by_type    = array();
	$by_insurer = array();
	foreach ( $rows as $r ) {
		$r->first_offer  = (float) $r->first_offer;
		$r->final_amount = (float) $r->final_amount;
		$by_type[ $r->claim_type ][] = $r;
		if ( $r->insurer && 'Other' !== $r->insurer ) {
			$by_insurer[ $r->insurer ][] = $r;
		}
	}
	$stats = array(
		'total'      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ), // phpcs:ignore
		'settled'    => count( $rows ),
		'all'        => count( $rows ) >= MAT_OUTCOMES_MIN ? mat_outcomes_summary( $rows ) : null,
		'by_type'    => array(),
		'by_insurer' => array(),
	);
	foreach ( $by_type as $k => $list ) {
		$stats['by_type'][ $k ] = count( $list ) >= MAT_OUTCOMES_MIN ? mat_outcomes_summary( $list ) : array( 'n' => count( $list ) );
	}
	foreach ( $by_insurer as $k => $list ) {
		if ( count( $list ) >= MAT_OUTCOMES_MIN ) {
			$stats['by_insurer'][ $k ] = mat_outcomes_summary( $list );
		}
	}
	set_transient( 'mat_outcomes_stats', $stats, HOUR_IN_SECONDS );
	return $stats;
}

function mat_outcomes_survey_url() {
	return home_url( user_trailingslashit( MAT_OUTCOMES_SURVEY_SLUG ) );
}

/**
 * A one-line invitation shown under tool results.
 */
function mat_outcomes_cta() {
	if ( ! get_page_by_path( MAT_OUTCOMES_SURVEY_SLUG ) ) {
		return;
	}
	echo '<p class="mat-outcomes-cta">' . esc_html__( 'Already settled a claim?', 'myautotriage' ) . ' <a href="' . esc_url( mat_outcomes_survey_url() ) . '">' . esc_html__( 'Share the first offer and what you finally got, anonymously, in 30 seconds', 'myautotriage' ) . '</a>. ' . esc_html__( 'It builds the Lowball Index: real numbers on how much offers move.', 'myautotriage' ) . '</p>';
}

/**
 * The privacy policy page is seeded once, so the survey's section is added
 * when the page is shown rather than by editing the saved page.
 */
function mat_outcomes_privacy_note( $content ) {
	if ( ! is_page( 'privacy-policy' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$extra = '<h2>' . esc_html__( 'Claim outcome survey', 'myautotriage' ) . '</h2>'
		. '<p>' . esc_html__( 'The one tool that sends anything to us is the optional claim outcome survey. If you choose to answer it, we store only the answers you give: claim type, whose insurer, the insurer\'s name if you pick one, your state if you pick one, the first offer, the final amount, the steps you took, and the month you answered. We do not store your name, email, claim number or IP address. We publish only totals and medians for groups of at least 30 answers, never individual answers. To limit spam, a scrambled one-way code made from your IP address is kept for one hour and then deleted.', 'myautotriage' ) . '</p>'
		. '<h2>' . esc_html__( 'Claim diary', 'myautotriage' ) . '</h2>'
		. '<p>' . esc_html__( 'The claim diary saves your entries in your own browser (local storage) so they are there when you come back. They are not sent to us. Clearing your browser data or using the diary\'s "Clear diary" button deletes them.', 'myautotriage' ) . '</p>';

	// Right after the paragraph about the tools, where a reader looks for it.
	foreach ( array( 'we cannot see what you enter', 'sends only the answers you choose to give it' ) as $marker ) {
		$at = strpos( $content, $marker );
		$end = false === $at ? false : strpos( $content, '</p>', $at );
		if ( false !== $end ) {
			return substr_replace( $content, $extra, $end + 4, 0 );
		}
	}
	return $content . $extra;
}
add_filter( 'the_content', 'mat_outcomes_privacy_note', 20 );

/**
 * Create the survey and index pages once.
 */
function mat_outcomes_pages() {
	if ( '1' === get_option( 'mat_outcomes_pages_version' ) ) {
		return;
	}
	update_option( 'mat_outcomes_pages_version', '1' );
	$pages = array(
		MAT_OUTCOMES_SURVEY_SLUG => array(
			__( 'Claim Outcome Survey', 'myautotriage' ),
			'page-templates/template-outcome-survey.php',
			__( 'Car Insurance Claim Survey: Share Your First Offer and Final Payout', 'myautotriage' ),
			__( 'Settled a car insurance claim? Share the first offer, the final payment and what you did in between, anonymously. Your answer builds the Lowball Index.', 'myautotriage' ),
		),
		MAT_OUTCOMES_INDEX_SLUG  => array(
			__( 'The Lowball Index', 'myautotriage' ),
			'page-templates/template-lowball-index.php',
			__( 'The Lowball Index: How Much Car Insurance Offers Go Up', 'myautotriage' ),
			__( 'How much do car insurance claim offers rise from the first offer to the final payment? Medians by claim type and insurer from anonymous reader reports.', 'myautotriage' ),
		),
	);
	foreach ( $pages as $slug => $p ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		$id = wp_insert_post( array(
			'post_title'   => $p[0],
			'post_name'    => $slug,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'page',
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_wp_page_template', $p[1] );
			update_post_meta( $id, '_mat_meta_title', $p[2] . ' | ' . get_bloginfo( 'name' ) );
			update_post_meta( $id, '_mat_meta_description', $p[3] );
		}
	}
}
add_action( 'wp_loaded', 'mat_outcomes_pages' );

/**
 * Tools > Claim outcomes: counts, the latest answers and a CSV download.
 */
function mat_outcomes_admin_menu() {
	add_management_page( __( 'Claim outcomes', 'myautotriage' ), __( 'Claim outcomes', 'myautotriage' ), 'manage_options', 'mat-claim-outcomes', 'mat_outcomes_admin_page' );
}
add_action( 'admin_menu', 'mat_outcomes_admin_menu' );

function mat_outcomes_admin_csv() {
	if ( ! isset( $_GET['page'], $_GET['mat_csv'] ) || 'mat-claim-outcomes' !== $_GET['page'] || ! current_user_can( 'manage_options' ) ) { // phpcs:ignore
		return;
	}
	check_admin_referer( 'mat_outcomes_csv' );
	global $wpdb;
	$table = mat_outcomes_table();
	$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id", ARRAY_A ); // phpcs:ignore
	header( 'Content-Type: text/csv; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename=claim-outcomes-' . gmdate( 'Y-m-d' ) . '.csv' );
	$out = fopen( 'php://output', 'w' );
	if ( $rows ) {
		fputcsv( $out, array_keys( $rows[0] ) );
		foreach ( $rows as $r ) {
			fputcsv( $out, $r );
		}
	}
	fclose( $out ); // phpcs:ignore
	exit;
}
add_action( 'admin_init', 'mat_outcomes_admin_csv' );

function mat_outcomes_admin_page() {
	global $wpdb;
	$table = mat_outcomes_table();
	$stats = mat_outcomes_stats();
	$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC LIMIT 50" ); // phpcs:ignore
	echo '<div class="wrap"><h1>' . esc_html__( 'Claim outcomes', 'myautotriage' ) . '</h1>';
	/* translators: 1: total answers, 2: settled answers used, 3: minimum per group */
	echo '<p>' . esc_html( sprintf( __( '%1$d answers in total, %2$d settled and not flagged (used in the Lowball Index). Groups are published from %3$d answers.', 'myautotriage' ), $stats['total'], $stats['settled'], MAT_OUTCOMES_MIN ) ) . '</p>';
	echo '<p><a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'tools.php?page=mat-claim-outcomes&mat_csv=1' ), 'mat_outcomes_csv' ) ) . '">' . esc_html__( 'Download all as CSV', 'myautotriage' ) . '</a></p>';
	echo '<table class="widefat striped"><thead><tr><th>ID</th><th>Month</th><th>State</th><th>Insurer</th><th>Type</th><th>Party</th><th>First offer</th><th>Final</th><th>Steps</th><th>Status</th><th>Flagged</th></tr></thead><tbody>';
	foreach ( (array) $rows as $r ) {
		echo '<tr>';
		foreach ( array( 'id', 'created_month', 'state', 'insurer', 'claim_type', 'party', 'first_offer', 'final_amount', 'steps', 'status', 'flagged' ) as $k ) {
			echo '<td>' . esc_html( $r->$k ) . '</td>';
		}
		echo '</tr>';
	}
	echo '</tbody></table></div>';
}
