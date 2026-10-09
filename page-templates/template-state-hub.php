<?php
/**
 * Template Name: State - Claim Laws Hub
 *
 * Lists every state with its key claim rules and links to the state pages.
 *
 * @package MyAutoTriage
 */

get_header();
$meta = mat_state_laws_meta();
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'Every state sets its own rules for how fast an insurer must handle a car insurance claim and when a damaged car counts as a total loss. Pick your state for its deadlines, total-loss rule, the law behind each one, and free tools to back up your claim.', 'myautotriage' ); ?></p>
			<?php if ( $meta['reviewed'] ) : ?>
				<p class="mat-field__hint">
					<?php
					/* translators: %s: date */
					printf( esc_html__( 'Last reviewed %s by the MyAutoTriage Editorial Team.', 'myautotriage' ), esc_html( date_i18n( get_option( 'date_format' ), strtotime( $meta['reviewed'] ) ) ) );
					?>
				</p>
			<?php endif; ?>
		</div>

		<div class="mat-page__content">
			<?php the_content(); ?>
			<div class="mat-table-wrap">
				<table class="mat-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'State', 'myautotriage' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Accept or deny a claim', 'myautotriage' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Total loss rule', 'myautotriage' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( mat_state_laws() as $state ) : ?>
							<?php
							$tl = $state['total_loss'];
							if ( ! $tl ) {
								$rule = '';
							} elseif ( 'percentage' === $tl['type'] && ! empty( $tl['threshold'] ) ) {
								/* translators: %d: percentage */
								$rule = sprintf( __( '%d%% of value', 'myautotriage' ), round( $tl['threshold'] * 100 ) );
							} else {
								$rule = __( 'Total loss formula', 'myautotriage' );
							}
							?>
							<tr>
								<th scope="row"><a href="<?php echo esc_url( mat_state_url( $state ) ); ?>"><?php echo esc_html( $state['name'] ); ?></a></th>
								<td><?php echo esc_html( $state['deadlines'] ? $state['deadlines']['decide'] : __( 'See state page', 'myautotriage' ) ); ?></td>
								<td><?php echo esc_html( $rule ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<h2><?php esc_html_e( 'How to use these rules', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'Deadlines usually run from the date the insurer receives your proof of loss, so send documents in writing and keep copies. If an insurer misses a deadline, a short written reminder that names the rule often gets things moving; if not, your state department of insurance takes complaints for free.', 'myautotriage' ); ?></p>
			<p><?php esc_html_e( '"Total loss formula" states total a car when the repair cost plus its salvage value reaches the car\'s actual cash value. Percentage states use a fixed share of the car\'s value instead.', 'myautotriage' ); ?></p>
		</div>

		<?php mat_tool_disclaimer( __( 'State rules change and some apply only to certain claim types. Confirm anything important with your state department of insurance or a licensed attorney.', 'myautotriage' ) ); ?>
	</div>
</div>
<?php
get_footer();
