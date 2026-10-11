<?php
/**
 * Template Name: Tool - Recorded Statement Checklist
 *
 * Before an adjuster records your statement: whether you have to give one,
 * a checklist to prepare, what to avoid saying, and a message to send.
 * The answer changes with whose insurer is asking and whether anyone was
 * hurt; the checklist itself is on the page for everyone.
 *
 * @package MyAutoTriage
 */

get_header();
wp_enqueue_script( 'mat-rs', MAT_URI . '/assets/js/generators/recorded-statement.js', array( 'mat-tools' ), MAT_VERSION, true );
$mat_rs_phrase = home_url( user_trailingslashit( 'what-the-adjuster-said/we-need-a-recorded-statement' ) );
?>
<div class="mat-tool">
	<div class="mat-container mat-narrow">
		<div class="mat-tool__intro">
			<h1><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'An adjuster wants to record your account of the accident. Answer three questions to see whether you have to, and get a checklist and a message to send. It takes a minute and can save you from a statement that cuts your payout.', 'myautotriage' ); ?></p>
		</div>

		<form id="mat-rs-form" class="mat-tool-panel" novalidate>
			<div class="mat-field">
				<label for="mat-rs-who"><?php esc_html_e( 'Who wants the recorded statement?', 'myautotriage' ); ?></label>
				<select id="mat-rs-who">
					<option value="other"><?php esc_html_e( 'The other driver\'s insurance company', 'myautotriage' ); ?></option>
					<option value="own"><?php esc_html_e( 'My own insurance company', 'myautotriage' ); ?></option>
					<option value="unsure"><?php esc_html_e( 'I\'m not sure', 'myautotriage' ); ?></option>
				</select>
			</div>
			<div class="mat-field-row">
				<div class="mat-field">
					<label for="mat-rs-injury"><?php esc_html_e( 'Was anyone hurt?', 'myautotriage' ); ?></label>
					<select id="mat-rs-injury">
						<option value="no"><?php esc_html_e( 'No, damage only', 'myautotriage' ); ?></option>
						<option value="yes"><?php esc_html_e( 'Yes, or not sure yet', 'myautotriage' ); ?></option>
					</select>
				</div>
				<div class="mat-field">
					<label for="mat-rs-fault"><?php esc_html_e( 'Is fault disputed?', 'myautotriage' ); ?></label>
					<select id="mat-rs-fault">
						<option value="no"><?php esc_html_e( 'No, it\'s clear the other driver caused it', 'myautotriage' ); ?></option>
						<option value="yes"><?php esc_html_e( 'Yes, or they haven\'t said', 'myautotriage' ); ?></option>
					</select>
				</div>
			</div>
			<div class="mat-tool-actions">
				<button type="submit" class="mat-btn mat-btn--primary mat-btn--lg"><?php esc_html_e( 'Show what to do', 'myautotriage' ); ?></button>
			</div>
		</form>

		<div id="mat-rs-result" class="mat-triage-result" hidden></div>

		<div class="mat-page__content">
			<?php the_content(); ?>
			<h2><?php esc_html_e( 'Before the call', 'myautotriage' ); ?></h2>
			<ul class="mat-checklist">
				<li><?php esc_html_e( 'Find out who the adjuster works for and write down their name, company and claim number.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Ask what the statement is for and what topics they will cover. You can ask for the questions in writing.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Pick a time that suits you. You don\'t have to do it on the first call.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Have the police report number, your photos, the date, time and place, and the other driver\'s details in front of you.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Write down the facts in order while they are fresh: where you were going, the lane, the light, what you saw, what happened, what was said.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'If anyone was hurt, think about talking to a lawyer first. Many give a free first consultation.', 'myautotriage' ); ?></li>
			</ul>
			<h2><?php esc_html_e( 'During the call', 'myautotriage' ); ?></h2>
			<ul class="mat-checklist">
				<li><?php esc_html_e( 'Answer only the question asked, in short sentences. Silence is fine.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Stick to what you know. "I don\'t know" and "I\'m not sure" are complete answers.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Don\'t estimate speeds, distances or times you didn\'t measure.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Don\'t say you\'re "fine" or "not hurt". Say you are still being checked, or describe what you feel.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Don\'t apologize or guess about who caused it; describe what happened.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Don\'t agree to a summary that isn\'t quite right. Correct it on the record.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Don\'t authorize access to all your medical records. A limited release for accident-related care is enough, if any.', 'myautotriage' ); ?></li>
			</ul>
			<h2><?php esc_html_e( 'After the call', 'myautotriage' ); ?></h2>
			<ul class="mat-checklist">
				<li><?php esc_html_e( 'Ask for a copy or transcript of the recording.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Send a short email the same day listing anything you need to correct or add.', 'myautotriage' ); ?></li>
				<li><?php esc_html_e( 'Log the call in your claim diary: date, adjuster, what was asked and promised.', 'myautotriage' ); ?></li>
			</ul>
			<p><a href="<?php echo esc_url( $mat_rs_phrase ); ?>"><?php esc_html_e( 'More on "We need a recorded statement": what it means and the rules behind it', 'myautotriage' ); ?></a></p>
		</div>

		<?php mat_tool_extras( 'recorded-statement-checklist' ); ?>

		<?php
		mat_faq_block( array_merge( array(
			array(
				'question' => __( 'Do I have to give a recorded statement to the other driver\'s insurance?', 'myautotriage' ),
				'answer'   => __( 'Generally no. You have no contract with them, so the cooperation clause in a policy doesn\'t bind you. Refusing everything can slow your claim, so offering a written statement with photos and the police report is a common middle ground.', 'myautotriage' ),
			),
			array(
				'question' => __( 'Can my own insurer deny my claim if I refuse a recorded statement?', 'myautotriage' ),
				'answer'   => __( 'Possibly. Most auto policies require you to cooperate with your insurer\'s investigation, which can include a statement or an examination under oath. You can still prepare, ask for the questions in writing and ask for a copy.', 'myautotriage' ),
			),
			array(
				'question' => __( 'Can I ask for the questions in writing instead?', 'myautotriage' ),
				'answer'   => __( 'Yes, you can always ask. Many adjusters will accept a written statement, especially on a property-damage-only claim where fault is clear.', 'myautotriage' ),
			),
		), mat_tool_extra_faqs( 'recorded-statement-checklist' ) ) );
		mat_render_sources( array(
			array(
				'label' => __( 'Maine Bureau of Insurance: Auto claims FAQ (recorded statements and the duty to cooperate)', 'myautotriage' ),
				'url'   => 'https://www.maine.gov/pfr/insurance/frequently-asked-questions/auto-claims',
			),
		) );
		mat_tool_disclaimer( __( 'This checklist is general information, not legal advice. If anyone was injured or fault is seriously disputed, consider speaking with a licensed attorney before giving any statement.', 'myautotriage' ) );
		?>
	</div>
</div>
<?php
get_footer();
