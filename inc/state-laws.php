<?php
/**
 * State claim-law pages: /car-insurance-claim-laws/ and one child page per
 * state (/car-insurance-claim-laws/california/ ...).
 *
 * The pages are ordinary WordPress pages, so they get the sitemap,
 * canonical and breadcrumbs for free; their content comes from the two
 * state data files the tools already use (claim deadlines and total-loss
 * thresholds), so a correction to the data updates the tool and the page
 * together.
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MAT_STATE_HUB_SLUG', 'car-insurance-claim-laws' );

/**
 * Merged per-state data, keyed by state code and sorted by name.
 */
function mat_state_laws() {
	static $laws = null;
	if ( null !== $laws ) {
		return $laws;
	}
	$laws      = array();
	$deadlines = mat_read_theme_json( 'assets/js/data/claim-deadlines.json' );
	$totals    = mat_read_theme_json( 'assets/js/data/total-loss-thresholds.json' );
	if ( empty( $deadlines['all_states'] ) ) {
		return $laws;
	}
	foreach ( $deadlines['all_states'] as $code => $name ) {
		$laws[ $code ] = array(
			'code'       => $code,
			'name'       => $name,
			'slug'       => sanitize_title( $name ),
			'deadlines'  => isset( $deadlines['states'][ $code ] ) ? $deadlines['states'][ $code ] : null,
			'total_loss' => isset( $totals['states'][ $code ] ) ? $totals['states'][ $code ] : null,
		);
	}
	uasort( $laws, function ( $a, $b ) {
		return strcmp( $a['name'], $b['name'] );
	} );
	return $laws;
}

function mat_read_theme_json( $relative ) {
	$file = MAT_DIR . '/' . $relative;
	if ( ! is_readable( $file ) ) {
		return array();
	}
	$data = json_decode( file_get_contents( $file ), true ); // phpcs:ignore -- local theme file
	return is_array( $data ) ? $data : array();
}

function mat_state_laws_meta() {
	$deadlines = mat_read_theme_json( 'assets/js/data/claim-deadlines.json' );
	return array(
		'reviewed' => isset( $deadlines['reviewed'] ) ? $deadlines['reviewed'] : '',
		'default'  => isset( $deadlines['default'] ) ? $deadlines['default'] : array(),
	);
}

/**
 * The state shown on the current page, or null when this isn't a state page.
 */
function mat_current_state( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || 'page' !== $post->post_type || ! $post->post_parent ) {
		return null;
	}
	$parent = get_post( $post->post_parent );
	if ( ! $parent || MAT_STATE_HUB_SLUG !== $parent->post_name ) {
		return null;
	}
	foreach ( mat_state_laws() as $state ) {
		if ( $state['slug'] === $post->post_name ) {
			return $state;
		}
	}
	return null;
}

function mat_state_url( $state ) {
	return home_url( user_trailingslashit( MAT_STATE_HUB_SLUG . '/' . $state['slug'] ) );
}

function mat_state_hub_url() {
	return home_url( user_trailingslashit( MAT_STATE_HUB_SLUG ) );
}

/**
 * Plain-English total-loss rule for a state, e.g. "75% of actual cash value".
 */
function mat_state_total_loss_rule( $state ) {
	$tl = $state['total_loss'];
	if ( ! $tl ) {
		return '';
	}
	if ( 'percentage' === $tl['type'] && ! empty( $tl['threshold'] ) ) {
		/* translators: %d: percentage */
		return sprintf( __( 'Repairs cost %d%% or more of the car\'s actual cash value', 'myautotriage' ), round( $tl['threshold'] * 100 ) );
	}
	if ( 'formula' === $tl['type'] ) {
		return __( 'Repair cost plus salvage value meets or exceeds the car\'s actual cash value (total loss formula)', 'myautotriage' );
	}
	return __( 'The insurer decides when repairs are uneconomical; state law sets no fixed percentage', 'myautotriage' );
}

/**
 * Short label for tables: "75% of value", "Total loss formula", "Insurer decides".
 */
function mat_state_total_loss_label( $state ) {
	$tl = $state['total_loss'];
	if ( ! $tl ) {
		return '';
	}
	if ( 'percentage' === $tl['type'] && ! empty( $tl['threshold'] ) ) {
		/* translators: %d: percentage */
		return sprintf( __( '%d%% of value', 'myautotriage' ), round( $tl['threshold'] * 100 ) );
	}
	return 'formula' === $tl['type'] ? __( 'Total loss formula', 'myautotriage' ) : __( 'Insurer decides (no fixed %)', 'myautotriage' );
}

/**
 * Turn a deadline value into a phrase that follows a verb:
 * "15 calendar days" -> "within 15 calendar days",
 * "No fixed deadline (a reasonable time)" -> "with no fixed deadline (a reasonable time)".
 */
function mat_state_within( $text ) {
	if ( preg_match( '/^\d/', $text ) ) {
		return 'within ' . $text;
	}
	if ( 0 === stripos( $text, 'No fixed deadline' ) ) {
		return 'with no fixed deadline' . substr( $text, strlen( 'No fixed deadline' ) );
	}
	if ( 0 === stripos( $text, 'No deadline in state law' ) ) {
		return 'with no deadline set in state law';
	}
	return '(' . $text . ')';
}

function mat_state_seo_title( $state ) {
	/* translators: %s: state name */
	return sprintf( __( '%s Car Insurance Claim Laws: Deadlines & Total Loss', 'myautotriage' ), $state['name'] );
}

function mat_state_meta_description( $state ) {
	$d = $state['deadlines'];
	if ( $d ) {
		/* translators: 1: state name, 2: decision deadline, 3: total loss rule */
		return mat_truncate_meta( sprintf( __( '%1$s car insurance claim rules: insurers must accept or deny a claim %2$s. Total loss rule: %3$s. With legal citations.', 'myautotriage' ), $state['name'], mat_state_within( $d['decide'] ), lcfirst( mat_state_total_loss_label( $state ) ) ) );
	}
	/* translators: %s: state name */
	return sprintf( __( '%s car insurance claim rules: how long insurers have to handle your claim, when a car is a total loss, and free tools to back up your claim.', 'myautotriage' ), $state['name'] );
}

/**
 * Create the hub page and the 51 state pages once. Runs on wp_loaded like
 * mat_ensure_new_pages(), so deploying the theme is enough.
 */
function mat_ensure_state_pages() {
	if ( '1' === get_option( 'mat_state_pages_version' ) ) {
		return;
	}
	update_option( 'mat_state_pages_version', '1' );

	$hub = get_page_by_path( MAT_STATE_HUB_SLUG );
	if ( ! $hub ) {
		$hub_id = wp_insert_post( array(
			'post_title'   => __( 'Car Insurance Claim Laws by State', 'myautotriage' ),
			'post_name'    => MAT_STATE_HUB_SLUG,
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
	update_post_meta( $hub_id, '_wp_page_template', 'page-templates/template-state-hub.php' );

	foreach ( mat_state_laws() as $state ) {
		if ( get_page_by_path( MAT_STATE_HUB_SLUG . '/' . $state['slug'] ) ) {
			continue;
		}
		$post_id = wp_insert_post( array(
			/* translators: %s: state name */
			'post_title'   => sprintf( __( '%s Car Insurance Claim Laws', 'myautotriage' ), $state['name'] ),
			'post_name'    => $state['slug'],
			'post_parent'  => $hub_id,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'menu_order'   => 0,
		) );
		if ( $post_id && ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, '_wp_page_template', 'page-templates/template-state-laws.php' );
		}
	}
}
add_action( 'wp_loaded', 'mat_ensure_state_pages' );

/**
 * FAQ entries for a state page, built from its data.
 */
function mat_state_faqs( $state ) {
	$faqs = array();
	$d    = $state['deadlines'];
	if ( $d ) {
		$faqs[] = array(
			/* translators: %s: state name */
			'question' => sprintf( __( 'How long does an insurance company have to pay a claim in %s?', 'myautotriage' ), $state['name'] ),
			/* translators: 1: state name, 2: decide, 3: pay */
			'answer'   => sprintf( __( 'In %1$s the insurer must accept or deny the claim %2$s, and pay %3$s. The clock generally starts once the insurer has the proof of loss it asked for, so keep a dated record of what you sent.', 'myautotriage' ), $state['name'], mat_state_within( $d['decide'] ), mat_state_within( $d['pay'] ) ),
		);
	}
	if ( $state['total_loss'] ) {
		$faqs[] = array(
			/* translators: %s: state name */
			'question' => sprintf( __( 'When is a car considered totaled in %s?', 'myautotriage' ), $state['name'] ),
			/* translators: 1: state name, 2: rule */
			'answer'   => sprintf( __( 'In %1$s: %2$s.', 'myautotriage' ), $state['name'], lcfirst( mat_state_total_loss_rule( $state ) ) )
				. ( empty( $state['total_loss']['note'] ) ? '' : ' ' . $state['total_loss']['note'] )
				. ' ' . __( 'You can challenge the actual cash value the insurer uses.', 'myautotriage' ),
		);
	}
	$faqs[] = array(
		/* translators: %s: state name */
		'question' => sprintf( __( 'What can I do if my insurer misses a deadline in %s?', 'myautotriage' ), $state['name'] ),
		/* translators: %s: state name */
		'answer'   => sprintf( __( 'Send a written follow-up citing the rule and asking for a decision by a specific date. If that fails, file a free complaint with the %s department of insurance; a pattern of missed deadlines can also support a bad-faith claim.', 'myautotriage' ), $state['name'] ),
	);
	return $faqs;
}

/**
 * Sources list for a state page.
 */
function mat_state_sources( $state ) {
	$sources = array();
	foreach ( array( 'deadlines', 'total_loss' ) as $key ) {
		$item = $state[ $key ];
		if ( $item && ! empty( $item['citation'] ) ) {
			$sources[] = array(
				'label' => $item['citation'],
				'url'   => isset( $item['source_url'] ) ? $item['source_url'] : '',
			);
		}
	}
	$sources[] = array(
		'label' => __( 'NAIC: How to file a complaint with your state insurance department', 'myautotriage' ),
		'url'   => 'https://content.naic.org/consumer/how-to-file-complaint',
	);
	return $sources;
}

function mat_render_sources( $sources ) {
	if ( empty( $sources ) ) {
		return;
	}
	?>
	<section class="mat-sources" aria-labelledby="mat-sources-title">
		<h2 id="mat-sources-title"><?php esc_html_e( 'Sources', 'myautotriage' ); ?></h2>
		<ul>
			<?php foreach ( $sources as $source ) : ?>
				<li>
					<?php if ( ! empty( $source['url'] ) ) : ?>
						<a href="<?php echo esc_url( $source['url'] ); ?>" rel="noopener" target="_blank"><?php echo esc_html( $source['label'] ); ?></a>
					<?php else : ?>
						<?php echo esc_html( $source['label'] ); ?>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php
}

/**
 * Up to three bordering states for each state (by state code), used for
 * the "Nearby states" links on state pages. Alaska and Hawaii, which border
 * no state, point to the closest West Coast states.
 */
function mat_state_neighbors() {
	return array(
		'AL' => array( 'GA', 'FL', 'MS' ), 'AK' => array( 'WA', 'OR', 'HI' ), 'AZ' => array( 'CA', 'NV', 'NM' ),
		'AR' => array( 'TX', 'LA', 'MO' ), 'CA' => array( 'OR', 'NV', 'AZ' ), 'CO' => array( 'UT', 'NM', 'KS' ),
		'CT' => array( 'NY', 'MA', 'RI' ), 'DE' => array( 'MD', 'PA', 'NJ' ), 'DC' => array( 'MD', 'VA', 'DE' ),
		'FL' => array( 'GA', 'AL', 'SC' ), 'GA' => array( 'FL', 'SC', 'AL' ), 'HI' => array( 'CA', 'OR', 'AK' ),
		'ID' => array( 'WA', 'OR', 'MT' ), 'IL' => array( 'IN', 'WI', 'MO' ), 'IN' => array( 'IL', 'OH', 'MI' ),
		'IA' => array( 'IL', 'MN', 'NE' ), 'KS' => array( 'MO', 'NE', 'OK' ), 'KY' => array( 'OH', 'TN', 'IN' ),
		'LA' => array( 'TX', 'MS', 'AR' ), 'ME' => array( 'NH', 'MA', 'VT' ), 'MD' => array( 'VA', 'PA', 'DE' ),
		'MA' => array( 'NY', 'CT', 'NH' ), 'MI' => array( 'OH', 'IN', 'WI' ), 'MN' => array( 'WI', 'IA', 'ND' ),
		'MS' => array( 'LA', 'AL', 'TN' ), 'MO' => array( 'IL', 'KS', 'AR' ), 'MT' => array( 'ID', 'WY', 'ND' ),
		'NE' => array( 'IA', 'KS', 'CO' ), 'NV' => array( 'CA', 'AZ', 'UT' ), 'NH' => array( 'MA', 'VT', 'ME' ),
		'NJ' => array( 'NY', 'PA', 'DE' ), 'NM' => array( 'TX', 'AZ', 'CO' ), 'NY' => array( 'NJ', 'PA', 'CT' ),
		'NC' => array( 'SC', 'VA', 'GA' ), 'ND' => array( 'MN', 'SD', 'MT' ), 'OH' => array( 'PA', 'MI', 'IN' ),
		'OK' => array( 'TX', 'KS', 'AR' ), 'OR' => array( 'WA', 'CA', 'ID' ), 'PA' => array( 'NY', 'NJ', 'OH' ),
		'RI' => array( 'MA', 'CT', 'NY' ), 'SC' => array( 'NC', 'GA', 'TN' ), 'SD' => array( 'ND', 'NE', 'MN' ),
		'TN' => array( 'KY', 'GA', 'NC' ), 'TX' => array( 'OK', 'LA', 'NM' ), 'UT' => array( 'CO', 'NV', 'AZ' ),
		'VT' => array( 'NH', 'NY', 'MA' ), 'VA' => array( 'MD', 'NC', 'DC' ), 'WA' => array( 'OR', 'ID', 'CA' ),
		'WV' => array( 'VA', 'PA', 'OH' ), 'WI' => array( 'MN', 'IL', 'MI' ), 'WY' => array( 'MT', 'CO', 'UT' ),
	);
}

/**
 * Compact list of links to every state page (or just $codes), e.g. on
 * articles and tool pages that apply to every state.
 */
function mat_state_link_list( $codes = null ) {
	if ( ! get_page_by_path( MAT_STATE_HUB_SLUG ) ) {
		return;
	}
	$laws = mat_state_laws();
	echo '<ul class="mat-state-list">';
	foreach ( $laws as $code => $state ) {
		if ( null !== $codes && ! in_array( $code, $codes, true ) ) {
			continue;
		}
		echo '<li><a href="' . esc_url( mat_state_url( $state ) ) . '">' . esc_html( $state['name'] ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * "Rules in your state" block: the hub link plus every state, for pages
 * whose answer depends on the state.
 */
function mat_state_rules_block( $heading = '' ) {
	if ( ! get_page_by_path( MAT_STATE_HUB_SLUG ) ) {
		return;
	}
	?>
	<section class="mat-page__content mat-state-rules" aria-labelledby="mat-state-rules-title">
		<h2 id="mat-state-rules-title"><?php echo esc_html( $heading ?: __( 'The rules in your state', 'myautotriage' ) ); ?></h2>
		<p>
			<?php esc_html_e( 'Claim deadlines and total loss rules for every state, with the law behind each one:', 'myautotriage' ); ?>
			<a href="<?php echo esc_url( mat_state_hub_url() ); ?>"><?php esc_html_e( 'compare all states', 'myautotriage' ); ?></a>.
		</p>
		<?php mat_state_link_list(); ?>
	</section>
	<?php
}
