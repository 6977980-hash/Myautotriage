<?php
/**
 * First-run content: the pages this theme expects to exist. Activation
 * (inc/activation.php) creates whichever of these don't already exist by
 * slug, so re-activating the theme is always safe and never duplicates.
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mat_seed_pages() {
	return array(
		array(
			'slug'     => 'home',
			'title'    => __( 'Home', 'myautotriage' ),
			'template' => '', // front-page.php is picked up automatically once this is set as the static front page.
			'content'  => '',
		),
		array(
			'slug'     => 'blog',
			'title'    => __( 'Blog', 'myautotriage' ),
			'template' => '', // home.php is picked up automatically once this is set as the posts page.
			'content'  => '',
		),
		array(
			'slug'     => 'tools',
			'title'    => __( 'Free Auto Insurance Claim Tools', 'myautotriage' ),
			'template' => 'page-templates/template-tools-hub.php',
			'content'  => '<p>' . __( 'Free, no-signup tools to help you triage a car insurance claim from start to finish — figure out what your claim is worth, what your state requires, and what to say when an insurer underpays or denies it.', 'myautotriage' ) . '</p>',
		),
		array(
			'slug'     => 'diminished-value-calculator',
			'title'    => __( 'Diminished Value (17c) Calculator', 'myautotriage' ),
			'template' => 'page-templates/template-diminished-value.php',
			'content'  => mat_seed_tool_body_dv(),
		),
		array(
			'slug'     => 'total-loss-threshold-calculator',
			'title'    => __( 'Total Loss Threshold Calculator', 'myautotriage' ),
			'template' => 'page-templates/template-total-loss-threshold.php',
			'content'  => mat_seed_tool_body_tlt(),
		),
		array(
			'slug'     => 'gap-insurance-shortfall-calculator',
			'title'    => __( 'GAP Insurance Shortfall Calculator', 'myautotriage' ),
			'template' => 'page-templates/template-gap-shortfall.php',
			'content'  => mat_seed_tool_body_gap(),
		),
		array(
			'slug'     => 'deductible-vs-premium-calculator',
			'title'    => __( 'File-a-Claim Break-Even Calculator', 'myautotriage' ),
			'template' => 'page-templates/template-deductible-calculator.php',
			'content'  => mat_seed_tool_body_deductible(),
		),
		array(
			'slug'     => 'car-accident-lawsuit-deadline-calculator',
			'title'    => __( 'Car Accident Lawsuit Deadline Calculator', 'myautotriage' ),
			'template' => 'page-templates/template-lawsuit-deadline.php',
			'content'  => '',
		),
		array(
			'slug'     => 'comparative-fault-calculator',
			'title'    => __( 'Comparative Fault Payout Calculator', 'myautotriage' ),
			'template' => 'page-templates/template-fault-payout.php',
			'content'  => '',
		),
		array(
			'slug'     => 'loss-of-use-calculator',
			'title'    => __( 'Loss of Use Calculator', 'myautotriage' ),
			'template' => 'page-templates/template-loss-of-use.php',
			'content'  => '',
		),
		array(
			'slug'     => 'claim-triage',
			'title'    => __( 'Claim Triage: What Should I Do Next?', 'myautotriage' ),
			'template' => 'page-templates/template-claim-triage.php',
			'content'  => '',
		),
		array(
			'slug'     => 'claim-diary',
			'title'    => __( 'Claim Diary', 'myautotriage' ),
			'template' => 'page-templates/template-claim-diary.php',
			'content'  => '',
		),
		array(
			'slug'     => 'total-loss-valuation-checker',
			'title'    => __( 'Total Loss Valuation Checker', 'myautotriage' ),
			'template' => 'page-templates/template-valuation-checker.php',
			'content'  => '',
		),
		array(
			'slug'     => 'insurance-late-payment-interest-calculator',
			'title'    => __( 'Late Claim Payment Interest Calculator', 'myautotriage' ),
			'template' => 'page-templates/template-late-interest.php',
			'content'  => '',
		),
		array(
			'slug'     => 'car-insurance-refund-calculator',
			'title'    => __( 'Car Insurance Refund Calculator', 'myautotriage' ),
			'template' => 'page-templates/template-refund-calculator.php',
			'content'  => '',
		),
		array(
			'slug'     => 'claim-payment-deadline-by-state',
			'title'    => __( 'Claim Payment Deadline Lookup', 'myautotriage' ),
			'template' => 'page-templates/template-claim-deadline.php',
			'content'  => mat_seed_tool_body_deadline(),
		),
		array(
			'slug'     => 'demand-letter-generator',
			'title'    => __( 'Auto Insurance Demand Letter Generator', 'myautotriage' ),
			'template' => 'page-templates/template-demand-letter.php',
			'content'  => mat_seed_tool_body_demand(),
		),
		array(
			'slug'     => 'no-injury-demand-letter-generator',
			'title'    => __( 'No-Injury Car Accident Demand Letter Generator', 'myautotriage' ),
			'template' => 'page-templates/template-demand-letter.php',
			'content'  => '<p>' . __( "Use this variant when you're only claiming property damage and there is no injury involved in your accident.", 'myautotriage' ) . '</p>',
		),
		array(
			'slug'     => 'property-damage-demand-letter-generator',
			'title'    => __( 'Property Damage Demand Letter Generator', 'myautotriage' ),
			'template' => 'page-templates/template-demand-letter.php',
			'content'  => '<p>' . __( 'Use this when you need to formally demand payment for vehicle repairs from an at-fault driver\'s insurer.', 'myautotriage' ) . '</p>',
		),
		array(
			'slug'     => 'insurance-underpayment-demand-letter-generator',
			'title'    => __( 'Insurance Underpayment Demand Letter Generator', 'myautotriage' ),
			'template' => 'page-templates/template-demand-letter.php',
			'content'  => '<p>' . __( 'Use this when your insurer has offered a settlement that does not reasonably cover your actual repair or replacement cost.', 'myautotriage' ) . '</p>',
		),
		array(
			'slug'     => 'diminished-value-demand-letter-generator',
			'title'    => __( 'Diminished Value Demand Letter Generator', 'myautotriage' ),
			'template' => 'page-templates/template-demand-letter.php',
			'content'  => '<p>' . __( 'Pair this with the Diminished Value Calculator to back your number with the standard 17c formula.', 'myautotriage' ) . '</p>',
		),
		array(
			'slug'     => 'small-claims-demand-letter-generator',
			'title'    => __( 'Small Claims Demand Letter Generator', 'myautotriage' ),
			'template' => 'page-templates/template-demand-letter.php',
			'content'  => '<p>' . __( 'Use this as your final written demand before filing in small claims court.', 'myautotriage' ) . '</p>',
		),
		array(
			'slug'     => 'appeal-letter-generator',
			'title'    => __( 'Claim Denial Appeal Letter Generator', 'myautotriage' ),
			'template' => 'page-templates/template-appeal-letter.php',
			'content'  => mat_seed_tool_body_appeal(),
		),
		array(
			'slug'     => 'about-us',
			'title'    => __( 'About Us', 'myautotriage' ),
			'template' => '',
			'content'  => mat_seed_page_about(),
		),
		array(
			'slug'     => 'contact',
			'title'    => __( 'Contact', 'myautotriage' ),
			'template' => '',
			'content'  => mat_seed_page_contact(),
		),
		array(
			'slug'     => 'editorial-policy',
			'title'    => __( 'Editorial Policy', 'myautotriage' ),
			'template' => '',
			'content'  => mat_seed_page_editorial(),
		),
		array(
			'slug'     => 'privacy-policy',
			'title'    => __( 'Privacy Policy', 'myautotriage' ),
			'template' => '',
			'content'  => mat_seed_page_privacy(),
		),
		array(
			'slug'     => 'terms-of-use',
			'title'    => __( 'Terms of Use', 'myautotriage' ),
			'template' => '',
			'content'  => mat_seed_page_terms(),
		),
		array(
			'slug'     => 'disclaimer',
			'title'    => __( 'Disclaimer', 'myautotriage' ),
			'template' => '',
			'content'  => mat_seed_page_disclaimer(),
		),
	);
}

function mat_seed_tool_body_dv() {
	return '<h2>' . __( 'How the 17c diminished value formula works', 'myautotriage' ) . '</h2>'
	. '<p>' . __( "After a repair, a car carries an accident history that most buyers and appraisal guides discount, even when the repair is invisible. Diminished value is the dollar difference between what your car would have sold for with a clean history and what it's worth now, with an accident on record.", 'myautotriage' ) . '</p>'
	. '<p>' . __( 'The 17c formula, from the 2001 Georgia case Mabry v. State Farm, is the most commonly referenced starting point: it caps the base loss at 10% of the vehicle\'s value, then reduces that by a damage-severity factor and a mileage factor. Insurers frequently use it (or a close variant) as their opening number.', 'myautotriage' ) . '</p>'
	. '<h2>' . __( 'How to strengthen a diminished value claim', 'myautotriage' ) . '</h2>'
	. '<ul><li>' . __( 'Get a repair invoice that clearly documents the extent of the damage.', 'myautotriage' ) . '</li><li>' . __( 'Consider an independent appraisal if the 17c number feels low relative to comparable vehicle listings.', 'myautotriage' ) . '</li><li>' . __( 'Check whether your state allows first-party diminished value claims before filing against your own insurer.', 'myautotriage' ) . '</li></ul>';
}

function mat_seed_tool_body_tlt() {
	return '<h2>' . __( 'Percentage threshold vs. Total Loss Formula', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'States regulate when an insurer must declare a vehicle a total loss in one of two ways. A percentage-threshold state sets a fixed percentage (commonly 70-100%) — once repair costs reach that share of the vehicle\'s value, it must be totaled. A Total Loss Formula (TLF) state instead adds the estimated repair cost to the estimated salvage value; if that combined number meets or exceeds the vehicle\'s value, it\'s a total loss.', 'myautotriage' ) . '</p>'
	. '<h2>' . __( "Why this matters for your claim", 'myautotriage' ) . '</h2>'
	. '<p>' . __( "If your car is close to the line, understanding your state's specific rule helps you evaluate whether your insurer's decision (to repair or to total) matches what the law actually requires — and gives you a factual basis to push back if it doesn't.", 'myautotriage' ) . '</p>';
}

function mat_seed_tool_body_gap() {
	return '<h2>' . __( 'What GAP insurance actually covers', 'myautotriage' ) . '</h2>'
	. '<p>' . __( "GAP (Guaranteed Asset Protection) coverage is designed to pay the difference between what you owe on a car loan or lease and what your standard insurance pays out after a total loss — because a car's actual cash value often falls faster than a loan balance, especially in the first few years.", 'myautotriage' ) . '</p>'
	. '<h2>' . __( 'Common exclusions to check', 'myautotriage' ) . '</h2>'
	. '<ul><li>' . __( 'Past-due loan payments at the time of the loss', 'myautotriage' ) . '</li><li>' . __( 'Extended warranties or service contracts rolled into the loan', 'myautotriage' ) . '</li><li>' . __( 'Unpaid finance or interest charges', 'myautotriage' ) . '</li><li>' . __( 'Your insurance deductible (covered by some GAP policies, not others)', 'myautotriage' ) . '</li></ul>';
}

function mat_seed_tool_body_deductible() {
	return '<h2>' . __( 'The real cost of filing a small claim', 'myautotriage' ) . '</h2>'
	. '<p>' . __( "For minor damage close to your deductible, the sticker cost of a claim (your deductible) is often not the full story — many insurers apply a surcharge to your premium for several years after an at-fault claim, which can add up to more than the repair itself.", 'myautotriage' ) . '</p>';
}

function mat_seed_tool_body_deadline() {
	return '<h2>' . __( 'Why claim deadlines exist', 'myautotriage' ) . '</h2>'
	. '<p>' . __( "Every state regulates how long an insurer can sit on your claim, through unfair claims settlement practices laws. These rules set deadlines for acknowledging a claim, deciding whether to accept or deny it, and paying out once you've agreed to a settlement.", 'myautotriage' ) . '</p>';
}

function mat_seed_tool_body_demand() {
	return '<h2>' . __( 'What makes a demand letter effective', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'A strong demand letter states the facts plainly, cites a specific dollar amount backed by documentation, and sets a clear deadline. Avoid emotional language — insurers respond to evidence and a credible signal that you understand your claim\'s value.', 'myautotriage' ) . '</p>';
}

function mat_seed_tool_body_appeal() {
	return '<h2>' . __( 'How to appeal a denied claim effectively', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'Start from the exact reason your insurer gave for the denial — vague pushback rarely works. Address that specific reason point by point, attach documentation that contradicts it, and keep a copy of everything you send.', 'myautotriage' ) . '</p>';
}

function mat_seed_page_about() {
	return '<p>' . __( 'MyAutoTriage was built around a simple observation: most drivers only learn how car insurance claims actually work in the middle of a stressful one. By then, they\'re trying to understand terms like "diminished value" and "total loss formula" for the first time, often while an adjuster is already waiting for an answer.', 'myautotriage' ) . '</p>'
	. '<p>' . __( "We built MyAutoTriage to close that gap: a set of free, independent calculators and letter generators, paired with plain-English guides, so you can understand your situation and put your position in writing without needing to hire help for every step.", 'myautotriage' ) . '</p>'
	. '<h2>' . __( 'What we are — and what we are not', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'MyAutoTriage is an independent, ad-supported educational publisher. We are not an insurance company, an insurance agency, a law firm, or a claims adjuster, and we do not handle claims on anyone\'s behalf. The tools on this site produce estimates and drafts based on general formulas and the information you enter — they are a starting point for your own research and decisions, not a substitute for professional advice.', 'myautotriage' ) . '</p>'
	. '<h2>' . __( 'How we make money', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'This site is supported by display advertising. Advertising does not influence the content of our guides or the output of our calculators — see our <a href="' . esc_url( home_url( '/editorial-policy/' ) ) . '">Editorial Policy</a> for details.', 'myautotriage' ) . '</p>';
}

function mat_seed_page_contact() {
	$email = get_theme_mod( 'mat_contact_email', 'hello@myautotriage.com' );
	return '<p>' . __( "Questions, corrections, or feedback on a guide or tool? We'd like to hear from you.", 'myautotriage' ) . '</p>'
	. '<p><strong>' . __( 'Email:', 'myautotriage' ) . '</strong> <a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></p>'
	. '<p>' . __( 'Please note: we cannot review, adjust, or intervene in your individual insurance claim, and nothing we send you by email is legal or insurance advice. For claim-specific help, contact your insurer, your state department of insurance, or a licensed attorney.', 'myautotriage' ) . '</p>';
}

function mat_seed_page_editorial() {
	return '<p>' . __( 'MyAutoTriage publishes original guides and free tools about car insurance claims. This page explains how we research, write, and maintain that content.', 'myautotriage' ) . '</p>'
	. '<h2>' . __( 'How we research', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'Our guides are written from publicly available regulatory sources, insurer-published claims information, and well-established industry formulas (such as the 17c diminished value formula and state total-loss threshold rules). Where a rule varies by state or by insurer, we say so rather than presenting a single number as universal.', 'myautotriage' ) . '</p>'
	. '<h2>' . __( 'How our tools work', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'Every calculator on this site uses a transparent, published formula or a general industry rule of thumb — never a hidden or proprietary model — and states its assumptions on the same page. Our letter generators assemble a first draft from the information you provide; they do not access, store, or transmit your claim details anywhere.', 'myautotriage' ) . '</p>'
	. '<h2>' . __( 'Corrections', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'Insurance regulations and formulas change. If you spot outdated or incorrect information, please <a href="' . esc_url( home_url( '/contact/' ) ) . '">contact us</a> and we will review and correct it.', 'myautotriage' ) . '</p>'
	. '<h2>' . __( 'Advertising', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'This site displays third-party advertising, including through Google AdSense. Advertisers have no influence over our editorial content, our tool formulas, or our recommendations.', 'myautotriage' ) . '</p>';
}

function mat_seed_page_privacy() {
	$site = get_bloginfo( 'name' );
	return '<p><em>' . sprintf( __( 'Last updated: %s', 'myautotriage' ), gmdate( 'F Y' ) ) . '</em></p>'
	. '<p>' . sprintf( __( '%s ("we", "us") respects your privacy. This policy explains what information we collect and how we use it.', 'myautotriage' ), esc_html( $site ) ) . '</p>'
	. '<h2>' . __( 'Information our tools handle', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'Our calculators and letter generators run entirely in your browser. The figures and details you type into them (vehicle values, claim numbers, names, addresses, letter text) are not transmitted to our servers, stored in a database, or shared with anyone — they exist only on your device for as long as the page is open.', 'myautotriage' ) . '</p>'
	. '<h2>' . __( 'Cookies and advertising', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'We may use cookies and similar technologies for basic site analytics and to serve advertising, including through Google AdSense. Google and its partners may use cookies to serve ads based on your prior visits to this or other websites. You can opt out of personalized advertising by visiting Google\'s Ads Settings, or generally by visiting www.aboutads.info.', 'myautotriage' ) . '</p>'
	. '<h2>' . __( 'Third-party links', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'Our guides may link to insurers, government resources, or other third-party websites. We are not responsible for the privacy practices of those sites.', 'myautotriage' ) . '</p>'
	. '<h2>' . __( 'Contact us', 'myautotriage' ) . '</h2>'
	. '<p>' . sprintf( __( 'Questions about this policy can be sent to the email address on our <a href="%s">Contact</a> page.', 'myautotriage' ), esc_url( home_url( '/contact/' ) ) ) . '</p>';
}

function mat_seed_page_terms() {
	$site = get_bloginfo( 'name' );
	return '<p><em>' . sprintf( __( 'Last updated: %s', 'myautotriage' ), gmdate( 'F Y' ) ) . '</em></p>'
	. '<p>' . sprintf( __( 'By using %s, you agree to these terms.', 'myautotriage' ), esc_html( $site ) ) . '</p>'
	. '<h2>' . __( 'Educational use only', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'All content, calculators, and letter generators on this site are provided for general educational purposes only and do not constitute legal, financial, or insurance advice. We make no guarantee about the accuracy, completeness, or applicability of any estimate, letter draft, or article to your specific situation.', 'myautotriage' ) . '</p>'
	. '<h2>' . __( 'No professional relationship', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'Using this site does not create an attorney-client, insurance-agent, or any other professional relationship between you and us.', 'myautotriage' ) . '</p>'
	. '<h2>' . __( 'Your responsibility', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'You are solely responsible for verifying any figure, deadline, or legal rule referenced on this site before relying on it, and for the content of any letter you send using our generators.', 'myautotriage' ) . '</p>'
	. '<h2>' . __( 'Limitation of liability', 'myautotriage' ) . '</h2>'
	. '<p>' . sprintf( __( 'To the fullest extent permitted by law, %s is not liable for any loss or damage arising from your use of this site or reliance on its content.', 'myautotriage' ), esc_html( $site ) ) . '</p>'
	. '<h2>' . __( 'Changes', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'We may update these terms from time to time. Continued use of the site after changes means you accept the updated terms.', 'myautotriage' ) . '</p>';
}

function mat_seed_page_disclaimer() {
	return '<h2>' . __( 'Not legal, financial, or insurance advice', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'Nothing on MyAutoTriage — including our articles, calculators, and letter generators — is legal, financial, or insurance advice. Insurance law, claims-handling rules, and industry formulas vary by state, by insurer, and by individual policy, and they change over time.', 'myautotriage' ) . '</p>'
	. '<h2>' . __( 'Independent publisher', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'We are not affiliated with, endorsed by, or acting on behalf of any insurance company, law firm, or government agency mentioned on this site.', 'myautotriage' ) . '</p>'
	. '<h2>' . __( 'Before you rely on anything here', 'myautotriage' ) . '</h2>'
	. '<p>' . __( 'Verify any figure, deadline, or rule with your insurance policy, your state department of insurance, or a licensed attorney before making a decision based on it.', 'myautotriage' ) . '</p>';
}
