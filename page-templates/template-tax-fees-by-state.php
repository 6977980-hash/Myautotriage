<?php
/**
 * Template Name: Guide - Total Loss Tax and Fees by State
 *
 * Whether each state makes the insurer add sales tax, title and
 * registration fees to a total loss settlement, up front or after you buy
 * a replacement. Data: assets/js/data/total-loss-tax-fees.json.
 *
 * @package MyAutoTriage
 */

get_header();

$mat_tf_data = mat_read_theme_json( 'assets/js/data/total-loss-tax-fees.json' );
$mat_laws    = mat_state_laws();
$mat_counts  = array();
foreach ( $mat_laws as $mat_state ) {
	if ( ! empty( $mat_state['tax_fees'] ) ) {
		$mat_key                = $mat_state['tax_fees']['tax'];
		$mat_counts[ $mat_key ] = isset( $mat_counts[ $mat_key ] ) ? $mat_counts[ $mat_key ] + 1 : 1;
	}
}
$mat_count = function ( $key ) use ( $mat_counts ) {
	return isset( $mat_counts[ $key ] ) ? (int) $mat_counts[ $key ] : 0;
};
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'Replacing a totaled car costs more than its sticker price: sales tax, a new title and registration. Whether the insurer has to pay those depends on your state.', 'myautotriage' ); ?></p>
		</div>

		<div class="mat-short-answer">
			<p class="mat-short-answer__label"><?php esc_html_e( 'Short answer', 'myautotriage' ); ?></p>
			<p>
				<?php
				printf(
					/* translators: 1-5: counts of states */
					esc_html__( 'In %1$d states a rule makes your own insurer include sales tax in a total loss cash settlement, usually with title and registration fees. In %2$d more it is repaid once you buy a replacement within 30 days, and in %3$d the regulator expects it without a written rule. In %4$d it depends on your policy, so ask for it in writing anyway, and %5$d have no state sales tax.', 'myautotriage' ),
					(int) $mat_count( 'required' ),
					(int) $mat_count( 'after_purchase' ),
					(int) $mat_count( 'dept_view' ),
					(int) $mat_count( 'policy' ),
					(int) $mat_count( 'none' )
				);
				?>
			</p>
		</div>

		<div class="mat-page__content">
			<?php the_content(); ?>

			<h2><?php esc_html_e( 'What the rule usually says', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'Most state rules follow the NAIC model claims regulation: a cash settlement for a totaled car is based on the cost of a comparable car, including all applicable taxes, license fees and other fees to transfer ownership, less your deductible. The tax is on the value of your car, not on the price of whatever you buy next.', 'myautotriage' ); ?></p>

			<h2><?php esc_html_e( 'The rule in every state', 'myautotriage' ); ?></h2>
			<div class="mat-table-wrap">
				<table class="mat-table">
					<thead><tr>
						<th><?php esc_html_e( 'State', 'myautotriage' ); ?></th>
						<th><?php esc_html_e( 'Sales tax (your insurer)', 'myautotriage' ); ?></th>
						<th><?php esc_html_e( 'Title and registration fees', 'myautotriage' ); ?></th>
						<th><?php esc_html_e( 'At-fault driver\'s insurer', 'myautotriage' ); ?></th>
					</tr></thead>
					<tbody>
						<?php foreach ( $mat_laws as $mat_state ) : ?>
							<?php
							if ( empty( $mat_state['tax_fees'] ) ) {
								continue;
							}
							$mat_tf = $mat_state['tax_fees'];
							?>
							<tr id="<?php echo esc_attr( strtolower( $mat_state['code'] ) ); ?>">
								<td><a href="<?php echo esc_url( mat_state_url( $mat_state ) ); ?>"><?php echo esc_html( $mat_state['name'] ); ?></a></td>
								<td><strong><?php echo esc_html( mat_tax_fees_label( 'tax', $mat_tf['tax'] ) ); ?></strong><br><span class="mat-field__hint"><?php echo esc_html( $mat_tf['note'] . ( $mat_tf['citation'] ? ' ' . $mat_tf['citation'] . '.' : '' ) ); ?></span></td>
								<td><?php echo esc_html( mat_tax_fees_label( 'fees', $mat_tf['fees'] ) ); ?></td>
								<td><?php echo esc_html( mat_tax_fees_label( 'third_party', $mat_tf['third_party'] ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<p class="mat-field__hint">
				<?php
				/* translators: %s: date */
				printf( esc_html__( 'Rules checked %s against a 50-state law firm survey (updated January 2026) and, for Ohio and Washington, the regulation text. "No specific rule" for the at-fault driver\'s insurer doesn\'t mean tax isn\'t owed: in a claim against the other driver you are owed what it costs to replace your car, and tax is part of that.', 'myautotriage' ), esc_html( date_i18n( get_option( 'date_format' ), strtotime( isset( $mat_tf_data['reviewed'] ) ? $mat_tf_data['reviewed'] : 'now' ) ) ) );
				?>
			</p>

			<h2><?php esc_html_e( 'How to get tax and fees paid', 'myautotriage' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'Check the settlement letter or valuation report for separate lines for sales tax, title and registration. If they are missing, ask in writing for them to be added and cite your state\'s rule from the table.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'In a 30-day state, keep the bill of sale for the replacement car and send it to the adjuster within 30 days of the settlement.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Ask for the tax at your local rate (state plus county or city), on the value of your totaled car.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'If the insurer still refuses, file a complaint with your state insurance department; several have cited insurers for leaving tax out.', 'myautotriage' ); ?></li>
			</ol>
		</div>

		<?php mat_tool_disclaimer( __( 'Leased cars, business vehicles and tax-exempt buyers often follow different rules.', 'myautotriage' ), 'page' ); ?>

		<?php
		$mat_next = array(
			'total-loss-valuation-checker'    => __( 'Check your valuation report for missing tax and fees', 'myautotriage' ),
			'total-loss-threshold-calculator' => __( 'Check whether the car should have been totaled', 'myautotriage' ),
			'gap-insurance-shortfall-calculator' => __( 'Still owe on the loan? Check your GAP shortfall', 'myautotriage' ),
			'insurance-appraisal-clause'      => __( 'Still can\'t agree on the value? Use the appraisal clause', 'myautotriage' ),
		);
		$mat_links = array();
		foreach ( $mat_next as $slug => $label ) {
			$url = mat_url_for_slug( $slug );
			if ( $url ) {
				$mat_links[] = '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
			}
		}
		if ( $mat_links ) :
			?>
			<section class="mat-page__content">
				<h2><?php esc_html_e( 'What to do next', 'myautotriage' ); ?></h2>
				<ul><?php echo implode( '', $mat_links ); // phpcs:ignore -- escaped above ?></ul>
			</section>
		<?php endif; ?>

		<?php
		mat_faq_block( array(
			array(
				'question' => __( 'Does insurance pay sales tax on a totaled car?', 'myautotriage' ),
				/* translators: %d: count of states */
				'answer'   => sprintf( __( 'In most states, yes. %d states have a rule requiring it in the cash settlement, a few repay it after you buy a replacement, and the rest leave it to your policy. The tax is on your totaled car\'s value, not on the price of the next car.', 'myautotriage' ), $mat_count( 'required' ) ),
			),
			array(
				'question' => __( 'Do I have to buy a new car to get the sales tax?', 'myautotriage' ),
				'answer'   => __( 'Only in states with a purchase rule, such as Illinois, Ohio and Hawaii, where you get it if you buy a replacement within 30 days. In most states that require tax, it belongs in the settlement whether or not you replace the car.', 'myautotriage' ),
			),
			array(
				'question' => __( 'Does the at-fault driver\'s insurer have to pay sales tax?', 'myautotriage' ),
				'answer'   => __( 'In many states the same claims rule covers third-party claims, and Louisiana\'s law says so outright. Elsewhere, the general rule that you are owed what it costs to replace your car usually includes tax, but you may have to argue for it.', 'myautotriage' ),
			),
		) );

		mat_render_sources( array_merge(
			isset( $mat_tf_data['sources'] ) ? $mat_tf_data['sources'] : array(),
			array(
				array( 'label' => __( 'Ohio Admin. Code 3901-1-54(H)(7)(f)', 'myautotriage' ), 'url' => 'https://codes.ohio.gov/ohio-administrative-code/rule-3901-1-54' ),
				array( 'label' => __( 'WAC 284-30-391', 'myautotriage' ), 'url' => 'https://app.leg.wa.gov/WAC/default.aspx?cite=284-30-391' ),
			)
		) );
		?>
	</div>
</div>
<?php
get_footer();
