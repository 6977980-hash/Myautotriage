<?php
/**
 * Template Name: Tool - Comparative Fault Payout Calculator
 *
 * How much of your damages you can recover from the other driver when you
 * were partly at fault, using the state's fault rule
 * (assets/js/data/state-claim-facts.json).
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-cf', MAT_URI . '/assets/js/calculators/fault-payout.js', array( 'mat-tools' ), MAT_VERSION, true );
$hub = get_page_by_path( MAT_STATE_HUB_SLUG ) ? trailingslashit( mat_state_hub_url() ) : '';
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'Partly at fault for the accident? See how much of your damages you can still recover from the other driver under your state\'s fault rule.', 'myautotriage' ); ?></p>
		</div>

		<form id="mat-cf-form" class="mat-tool-panel" data-json="<?php echo esc_url( MAT_URI . '/assets/js/data/state-claim-facts.json' ); ?>" data-hub-url="<?php echo esc_url( $hub ); ?>" novalidate>
			<div class="mat-field">
				<label for="mat-cf-state"><?php esc_html_e( 'State where the accident happened', 'myautotriage' ); ?></label>
				<select id="mat-cf-state" required>
					<option value=""><?php esc_html_e( 'Choose your state', 'myautotriage' ); ?></option>
					<?php foreach ( mat_state_laws() as $state ) : ?>
						<option value="<?php echo esc_attr( $state['code'] ); ?>"><?php echo esc_html( $state['name'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-cf-damages"><?php esc_html_e( 'Your total damages (USD)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-cf-damages" min="1" step="1" placeholder="e.g. 12000" required>
					<span class="mat-field__hint"><?php esc_html_e( 'Repairs, rental, medical bills, lost wages and other losses together.', 'myautotriage' ); ?></span>
				</div>
				<div class="mat-field">
					<label for="mat-cf-fault"><?php esc_html_e( 'Your share of the fault (%)', 'myautotriage' ); ?></label>
					<input type="number" id="mat-cf-fault" min="0" max="100" step="1" placeholder="e.g. 20" required>
					<span class="mat-field__hint"><?php esc_html_e( 'What the adjuster says, or your own estimate. Try a few numbers.', 'myautotriage' ); ?></span>
				</div>
			</div>
			<p id="mat-cf-error" class="mat-form-error" role="alert" hidden></p>
			<div class="mat-tool-actions">
				<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Calculate my recovery', 'myautotriage' ); ?></button>
			</div>
		</form>

		<div id="mat-cf-result" class="mat-result-box" hidden></div>

		<?php mat_tool_disclaimer( __( 'Fault percentages are argued, not fixed: an adjuster\'s first number is an opening position, and a judge or jury decides if the case goes to court. This tool applies the general state rule; some claim types have exceptions.', 'myautotriage' ) ); ?>

		<div class="mat-page__content">
			<?php the_content(); ?>
			<h2><?php esc_html_e( 'The four fault systems', 'myautotriage' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Pure contributory negligence (Alabama, Maryland, North Carolina, Virginia and DC): any fault of your own, even 1%, can bar recovery from the other driver.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Pure comparative fault: you recover your damages minus your percentage of fault, even if you were mostly to blame.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Modified comparative fault, 50% bar: reduced by your share, and nothing at 50% or more.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Modified comparative fault, 51% bar: reduced by your share, and nothing once you are more at fault than the other side.', 'myautotriage' ); ?></li>
			</ul>
			<p><?php esc_html_e( 'South Dakota uses its own slight/gross negligence test. Every state\'s rule, with its citation, is on our state pages.', 'myautotriage' ); ?></p>
			<h2><?php esc_html_e( 'Why a few percentage points matter', 'myautotriage' ); ?></h2>
			<p><?php esc_html_e( 'On a $20,000 claim, every 10% of fault the adjuster puts on you costs $2,000. Near the 50% line it can cost everything. Photos of the scene, the police report, witness names and dashcam video are what move the percentage, so send them with a written reply when you dispute the insurer\'s fault decision.', 'myautotriage' ); ?></p>
		</div>

		<?php mat_tool_extras( 'comparative-fault-calculator' ); ?>

		<?php
		mat_faq_block( array_merge( array(
			array(
				'question' => __( 'Who decides how much each driver was at fault?', 'myautotriage' ),
				'answer'   => __( 'In a claim, each insurer\'s adjuster makes a liability decision from the police report, statements, photos and traffic law. If you disagree, dispute it in writing with your evidence; if the claim goes to court, the judge or jury decides.', 'myautotriage' ),
			),
			array(
				'question' => __( 'Does my own insurance pay if I was partly at fault?', 'myautotriage' ),
				'answer'   => __( 'Your collision coverage pays for your car minus the deductible regardless of fault, and your insurer may then recover part of it from the other driver\'s insurer. Fault rules decide what you can claim from the other driver.', 'myautotriage' ),
			),
		), mat_tool_extra_faqs( 'comparative-fault-calculator' ) ) );
		?>
	</div>
</div>
<?php
get_footer();
