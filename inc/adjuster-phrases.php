<?php
/**
 * "What the adjuster said" pages: one page per thing adjusters commonly
 * tell claimants, explaining what it means, the rule behind it and what to
 * say back. Pages live under /what-the-adjuster-said/ and are created on
 * wp_loaded like the state pages; their content comes from the array below.
 *
 * The rules cited are the NAIC model claim-handling act and regulation,
 * which most states have adopted in some form. Each page says so, because
 * the state's own version is what applies.
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MAT_ADJUSTER_HUB_SLUG', 'what-the-adjuster-said' );

/** Bump when a phrase is added so the new page gets created. */
define( 'MAT_ADJUSTER_PAGES_VERSION', '1' );

/** Reviewed date shown on the pages. */
define( 'MAT_ADJUSTER_REVIEWED', '2026-10-10' );

function mat_adjuster_sources_common() {
	return array(
		'902' => array(
			'label' => __( 'NAIC Unfair Property/Casualty Claims Settlement Practices Model Regulation (Model 902)', 'myautotriage' ),
			'url'   => 'https://content.naic.org/sites/default/files/model-law-902.pdf',
		),
		'900' => array(
			'label' => __( 'NAIC Unfair Claims Settlement Practices Act (Model 900)', 'myautotriage' ),
			'url'   => 'https://content.naic.org/sites/default/files/model-law-900.pdf',
		),
		'maine' => array(
			'label' => __( 'Maine Bureau of Insurance: Auto claims FAQ', 'myautotriage' ),
			'url'   => 'https://www.maine.gov/pfr/insurance/frequently-asked-questions/auto-claims',
		),
		'ct' => array(
			'label' => __( 'Connecticut Insurance Department Bulletin CL-1-07: Loss of use in third-party auto claims', 'myautotriage' ),
			'url'   => 'https://portal.ct.gov/cid/department-resources/bulletins/claims-bulletins/bulletin-cl-1-07',
		),
		'complaint' => array(
			'label' => __( 'NAIC: How to file a complaint with your state insurance department', 'myautotriage' ),
			'url'   => 'https://content.naic.org/consumer/how-to-file-complaint',
		),
	);
}

/**
 * The phrases, in the order they are listed on the hub.
 *
 * Keys: slug, phrase (what the adjuster says), title (page H1), seo_title,
 * meta, short (two-line answer), means (paragraphs), rights (list items),
 * reply (copy-ready message), tools (slug, optional query, label),
 * faqs, sources (keys of mat_adjuster_sources_common()).
 */
function mat_adjuster_phrases() {
	return array(
		array(
			'slug'      => 'we-need-a-recorded-statement',
			'phrase'    => __( 'We need to take a recorded statement.', 'myautotriage' ),
			'title'     => __( 'The Adjuster Wants a Recorded Statement: Do You Have to Give One?', 'myautotriage' ),
			'seo_title' => __( 'Do I Have to Give a Recorded Statement to Insurance?', 'myautotriage' ),
			'meta'      => __( 'The other driver\'s insurer asked for a recorded statement? You usually don\'t have to give one. Your own insurer is different. What it means and what to say.', 'myautotriage' ),
			'short'     => __( 'To your own insurer, usually yes: your policy\'s cooperation clause can require it. To the other driver\'s insurer, you generally have no contract with them and no duty to record a statement, though refusing everything can slow your claim. A short written statement of the facts is a common middle ground.', 'myautotriage' ),
			'means'     => array(
				__( 'An adjuster records a statement to lock in your account of the accident: speed, where you were looking, injuries, what you said at the scene. It is used to decide fault and to value the claim, and anything vague or guessed can be used to reduce what you are paid.', 'myautotriage' ),
				__( 'Who is asking matters. Your own insurer can rely on the duty to cooperate in your policy. The at-fault driver\'s insurer is not your insurer, so that duty does not apply to you in the same way.', 'myautotriage' ),
			),
			'rights'    => array(
				__( 'Maine\'s insurance regulator says your own policy requires you to cooperate in the investigation, while in a third-party claim there is no such contractual duty, although refusing a statement could in some cases be treated as failing to cooperate.', 'myautotriage' ),
				__( 'You can ask for the questions in writing, answer in writing, and keep to facts you know. Don\'t guess at speeds, distances or injuries.', 'myautotriage' ),
				__( 'If you do record one, you can ask for a copy or transcript.', 'myautotriage' ),
			),
			'reply'     => __( "Thank you for reaching out about claim [claim number]. I am not going to give a recorded statement at this time. I am happy to provide a written statement of the facts of the accident, along with the police report number and photos. Please send any specific questions in writing to this email address and I will answer them.", 'myautotriage' ),
			'tools'     => array(
				array( 'slug' => 'what-to-do-after-a-car-accident-checklist', 'label' => __( 'What to do after a car accident: the step-by-step checklist', 'myautotriage' ) ),
				array( 'slug' => 'comparative-fault-calculator', 'label' => __( 'See how a share of fault changes your payout', 'myautotriage' ) ),
			),
			'faqs'      => array(
				array(
					'question' => __( 'Can the other driver\'s insurance deny my claim if I refuse a recorded statement?', 'myautotriage' ),
					'answer'   => __( 'They can say they lack information to accept liability, which delays the claim. Offering a written statement, photos and the police report usually answers that. If they still won\'t decide, ask in writing what specific information they need.', 'myautotriage' ),
				),
				array(
					'question' => __( 'Do I have to give a recorded statement to my own insurance company?', 'myautotriage' ),
					'answer'   => __( 'Usually your policy requires you to cooperate with your own insurer\'s investigation, which can include a statement. You can still prepare, stick to facts, and ask for a copy.', 'myautotriage' ),
				),
			),
			'sources'   => array( 'maine', '902' ),
		),
		array(
			'slug'      => 'this-is-our-final-offer',
			'phrase'    => __( 'This is our final offer.', 'myautotriage' ),
			'title'     => __( '"This Is Our Final Offer": What It Means and How to Respond', 'myautotriage' ),
			'seo_title' => __( 'Insurance Says "This Is Our Final Offer": What to Do Next', 'myautotriage' ),
			'meta'      => __( 'An adjuster calling an offer "final" doesn\'t end your claim. What the rules say, how to ask for the explanation, and your options: counter-offer, appraisal and a complaint.', 'myautotriage' ),
			'short'     => __( 'It is a negotiating position, not a legal end to your claim. Ask for a written explanation of how the amount was calculated, send a written counter-offer with evidence, and if that fails use your policy\'s appraisal clause, a complaint to your state insurance department, or small claims court.', 'myautotriage' ),
			'means'     => array(
				__( 'Adjusters have settlement authority up to a limit. "Final" often means final at their level, for the evidence they have. New evidence, a supervisor, or a formal step like appraisal can change the number.', 'myautotriage' ),
			),
			'rights'    => array(
				__( 'The NAIC model act lists as an unfair claims practice failing to promptly give a reasonable and accurate explanation of the basis for an offer of compromise settlement (Model 900, Section 4L).', 'myautotriage' ),
				__( 'It also lists compelling insureds to sue by offering substantially less than they later recover in court (Section 4E).', 'myautotriage' ),
				__( 'For your own insurer, the model regulation says a payment or letter shouldn\'t be called "final" or "a release" unless the policy limit was paid or you agreed to a compromise settlement (Model 902, Section 5E).', 'myautotriage' ),
			),
			'reply'     => __( "I received your offer of $[amount] on claim [claim number] and do not accept it. Please send me a written explanation of how this amount was calculated, including any estimate or valuation report and every deduction. Enclosed is my evidence supporting $[your amount]. Please respond in writing within 14 days. If we cannot resolve this, I will consider appraisal under my policy and a complaint to my state department of insurance.", 'myautotriage' ),
			'tools'     => array(
				array( 'slug' => 'insurance-underpayment-demand-letter-generator', 'label' => __( 'Write a counter-offer to a low settlement', 'myautotriage' ) ),
				array( 'slug' => 'total-loss-valuation-checker', 'label' => __( 'Totaled? Check the valuation report behind the offer', 'myautotriage' ) ),
				array( 'slug' => 'demand-letter-generator', 'query' => array( 'type' => 'appraisal' ), 'label' => __( 'Invoke your policy\'s appraisal clause', 'myautotriage' ) ),
				array( 'slug' => 'how-to-negotiate-insurance-adjuster', 'label' => __( 'Read: how to negotiate with an insurance adjuster', 'myautotriage' ) ),
			),
			'faqs'      => array(
				array(
					'question' => __( 'Can an insurance company withdraw an offer if I counter?', 'myautotriage' ),
					'answer'   => __( 'An offer that hasn\'t been accepted can generally be changed, but insurers rarely pull an offer below the amount they already agree is owed. Ask them to pay the undisputed amount while you dispute the rest.', 'myautotriage' ),
				),
				array(
					'question' => __( 'Should I cash a check marked "final"?', 'myautotriage' ),
					'answer'   => __( 'Read the check and any letter with it first. If it says it settles or releases the whole claim and you don\'t agree, ask in writing for a payment of the undisputed amount without release language before you cash it.', 'myautotriage' ),
				),
			),
			'sources'   => array( '900', '902', 'complaint' ),
		),
		array(
			'slug'      => 'your-claim-is-under-investigation',
			'phrase'    => __( 'Your claim is still under investigation.', 'myautotriage' ),
			'title'     => __( '"Your Claim Is Still Under Investigation": How Long Can They Take?', 'myautotriage' ),
			'seo_title' => __( 'Claim "Still Under Investigation"? How Long Insurers Can Take', 'myautotriage' ),
			'meta'      => __( 'Weeks of "still under investigation"? Insurers have deadlines to acknowledge, decide and explain delays in writing. What the rules say and what to send.', 'myautotriage' ),
			'short'     => __( 'Insurers don\'t get unlimited time. Under the NAIC model rules most states follow in some form, they must acknowledge a claim within 15 days, accept or deny within 21 days of your proof of loss, and if they need longer, tell you why in writing and update you every 45 days. Your state\'s exact deadlines may differ.', 'myautotriage' ),
			'means'     => array(
				__( 'Sometimes the investigation is real: fault is disputed, a witness hasn\'t called back, or the insurer is waiting on its own driver. Sometimes the file is just sitting. A written request that names the deadline usually shows which.', 'myautotriage' ),
			),
			'rights'    => array(
				__( 'Acknowledge the claim within 15 days, and reply within 15 days to other communications that expect a response (Model 902, Sections 6A and 6C).', 'myautotriage' ),
				__( 'Accept or deny a first-party claim within 21 days after receiving proof of loss; if more time is needed, say why within those 21 days, then send a letter with the reasons every 45 days (Section 7A-B).', 'myautotriage' ),
				__( 'Pay within 30 days once liability is affirmed and the amount isn\'t in dispute (Section 7F).', 'myautotriage' ),
			),
			'reply'     => __( "I am writing about claim [claim number], reported on [date]. I sent my proof of loss on [date] and have not received a decision. Please tell me in writing what specific information you still need, the reasons more time is needed, and the date you expect to decide the claim. Under my state's claim-handling rules, I expect a written response within 15 days.", 'myautotriage' ),
			'tools'     => array(
				array( 'slug' => 'claim-payment-deadline-by-state', 'label' => __( 'Look up your state\'s claim deadlines', 'myautotriage' ) ),
				array( 'slug' => 'insurance-late-payment-interest-calculator', 'label' => __( 'Paid late? Calculate the interest owed', 'myautotriage' ) ),
				array( 'slug' => 'demand-letter-generator', 'query' => array( 'type' => 'doi-complaint' ), 'label' => __( 'Draft a complaint to your state department of insurance', 'myautotriage' ) ),
			),
			'faqs'      => array(
				array(
					'question' => __( 'Do these deadlines apply when I claim against the other driver\'s insurer?', 'myautotriage' ),
					'answer'   => __( 'The acceptance and payment deadlines in the model regulation are written for first-party claims, but many states apply acknowledgment and good-faith rules to third-party claims too. Your state\'s rules decide.', 'myautotriage' ),
				),
				array(
					'question' => __( 'What counts as proof of loss?', 'myautotriage' ),
					'answer'   => __( 'The documents your insurer reasonably needs to decide the claim, often a signed form plus estimates, photos and the police report. Send them in writing and keep the date: deadlines usually run from when the insurer receives them.', 'myautotriage' ),
				),
			),
			'sources'   => array( '902', 'complaint' ),
		),
		array(
			'slug'      => 'file-with-your-own-insurance',
			'phrase'    => __( 'You should file this with your own insurance.', 'myautotriage' ),
			'title'     => __( 'The Other Driver\'s Insurer Says "File With Your Own Insurance"', 'myautotriage' ),
			'seo_title' => __( 'Other Driver\'s Insurance Says File With Your Own? Read This', 'myautotriage' ),
			'meta'      => __( 'The at-fault driver\'s insurer told you to use your own coverage? When that is fair, when it isn\'t, and how using your own insurer affects your deductible.', 'myautotriage' ),
			'short'     => __( 'You can choose either route. Where liability and damages are reasonably clear, the NAIC model rule says insurers shouldn\'t push you to your own policy just to avoid paying. Using your own collision coverage can be faster, but you pay your deductible up front and wait for it to be recovered.', 'myautotriage' ),
			'means'     => array(
				__( 'Often it means the other insurer hasn\'t accepted fault yet, or its own customer hasn\'t reported the accident. Sometimes it is a way to move cost onto your insurer.', 'myautotriage' ),
			),
			'rights'    => array(
				__( 'Where liability and damages are reasonably clear, insurers shall not recommend that third-party claimants make claim under their own policies solely to avoid paying claims under the insurer\'s policy (Model 902, Section 8B).', 'myautotriage' ),
				__( 'If you use your own insurer, on request it must include your deductible in its subrogation demand and share recoveries with you proportionately (Section 8D).', 'myautotriage' ),
			),
			'reply'     => __( "Thank you. I am making a claim against your insured's policy for the damage caused in the accident on [date]. If you are not accepting liability, please tell me in writing the specific reasons and the information you still need. If liability is not in dispute, please confirm when you will inspect my vehicle and arrange a rental.", 'myautotriage' ),
			'tools'     => array(
				array( 'slug' => 'property-damage-demand-letter-generator', 'label' => __( 'Send a property damage demand to the at-fault insurer', 'myautotriage' ) ),
				array( 'slug' => 'comparative-fault-calculator', 'label' => __( 'See how a share of fault changes your payout', 'myautotriage' ) ),
				array( 'slug' => 'uninsured-motorist-claim-guide', 'label' => __( 'Other driver uninsured? Read the uninsured motorist guide', 'myautotriage' ) ),
			),
			'faqs'      => array(
				array(
					'question' => __( 'Will claiming on my own policy raise my rates if I wasn\'t at fault?', 'myautotriage' ),
					'answer'   => __( 'Many insurers don\'t surcharge not-at-fault claims and some states restrict it, but it depends on your insurer and state. Ask your agent before you file.', 'myautotriage' ),
				),
				array(
					'question' => __( 'Do I get my deductible back if I use my own insurance?', 'myautotriage' ),
					'answer'   => __( 'Usually, if your insurer recovers from the at-fault driver\'s insurer. Ask it to include your deductible in its subrogation demand. If fault is shared, you may get back only part of it.', 'myautotriage' ),
				),
			),
			'sources'   => array( '902' ),
		),
		array(
			'slug'      => 'rental-coverage-is-ending',
			'phrase'    => __( 'We\'re ending your rental on Friday.', 'myautotriage' ),
			'title'     => __( '"We\'re Ending Your Rental": Can They Stop It Before Repairs Are Done?', 'myautotriage' ),
			'seo_title' => __( 'Insurance Ending Rental Car Before Repairs Are Done? What to Do', 'myautotriage' ),
			'meta'      => __( 'Rental cut off while your car is still in the shop? Whose claim it is decides the rules. What a reasonable rental period means and what to send the adjuster.', 'myautotriage' ),
			'short'     => __( 'It depends whose insurance pays. On your own policy, rental reimbursement stops at the daily and total limits you bought. From the at-fault driver\'s insurer, most states let you recover loss of use for the period reasonably needed to repair or replace your car, which can run past an arbitrary cutoff when the delay isn\'t your fault.', 'myautotriage' ),
			'means'     => array(
				__( 'Adjusters often set a rental end date from the shop\'s first estimate of repair time. Parts delays, supplements and the insurer\'s own slow approvals stretch that time, and the end date doesn\'t always move with it.', 'myautotriage' ),
			),
			'rights'    => array(
				__( 'Connecticut\'s insurance department says loss of use in third-party claims covers the period reasonably required to repair or replace the vehicle, and that merely offering or discussing a rental isn\'t enough to meet that obligation.', 'myautotriage' ),
				__( 'Maine\'s regulator says its law requires reasonable rental expenses to be reimbursed in third-party claims.', 'myautotriage' ),
				__( 'If you didn\'t rent, you may still claim loss of use for the days without your car, valued at a comparable rental rate.', 'myautotriage' ),
			),
			'reply'     => __( "My vehicle is still at [shop] on claim [claim number]. The shop expects repairs to be finished on [date]; the delay is caused by [parts on back order / waiting for your supplement approval], not by me. Please extend the rental until the repairs are complete. If you will not, please tell me in writing the reason and the date you consider reasonable for repairs.", 'myautotriage' ),
			'tools'     => array(
				array( 'slug' => 'loss-of-use-calculator', 'label' => __( 'Calculate the loss of use to claim', 'myautotriage' ) ),
				array( 'slug' => 'property-damage-demand-letter-generator', 'label' => __( 'Add rental or loss of use to a property damage demand', 'myautotriage' ) ),
			),
			'faqs'      => array(
				array(
					'question' => __( 'How long will insurance pay for a rental when my car is totaled?', 'myautotriage' ),
					'answer'   => __( 'Usually only until a reasonable time after the total loss offer or payment, since the claim then moves to replacing the car. Ask in writing for the end date and for a few days\' notice.', 'myautotriage' ),
				),
				array(
					'question' => __( 'Do I have to take the cheapest rental?', 'myautotriage' ),
					'answer'   => __( 'In a third-party claim, a rental reasonably comparable to your car is the usual standard, not the cheapest one. On your own policy, the daily limit you bought applies.', 'myautotriage' ),
				),
			),
			'sources'   => array( 'ct', 'maine' ),
		),
		array(
			'slug'      => 'we-use-aftermarket-parts',
			'phrase'    => __( 'The estimate uses aftermarket parts.', 'myautotriage' ),
			'title'     => __( '"We Use Aftermarket Parts": Can You Insist on OEM Parts?', 'myautotriage' ),
			'seo_title' => __( 'Insurance Using Aftermarket Parts? Your Rights on OEM Parts', 'myautotriage' ),
			'meta'      => __( 'Insurance estimate lists aftermarket or non-OEM parts? When that\'s allowed, the quality standard they must meet, and how to ask for OEM parts.', 'myautotriage' ),
			'short'     => __( 'Usually yes, unless your policy promises OEM parts. But under the NAIC model rule, an insurer can\'t require aftermarket crash parts unless they are at least equal to the original in fit, quality and performance, and some states require the estimate to identify them. You can pay the difference for OEM parts.', 'myautotriage' ),
			'means'     => array(
				__( 'Aftermarket (non-OEM) parts are made by companies other than the car\'s manufacturer and cost less. Many policies allow them. Fit problems show up as uneven panel gaps, paint mismatch or sensors that don\'t line up.', 'myautotriage' ),
			),
			'rights'    => array(
				__( 'No insurer shall require replacement crash parts unless they are at least equal in kind and quality to the original in fit, quality and performance, and the insurer must consider the cost of any modifications needed (Model 902, Section 8J(4)).', 'myautotriage' ),
				__( 'Maine\'s regulator says you choose which parts are used, but the insurer may not have to pay the higher cost of OEM parts.', 'myautotriage' ),
				__( 'Check your policy for an OEM parts endorsement; some insurers sell one.', 'myautotriage' ),
			),
			'reply'     => __( "The estimate on claim [claim number] lists aftermarket parts for [parts]. My shop reports that [part] does not fit or perform like the original. Please approve OEM parts for these items or confirm in writing that the aftermarket parts are equal to the original in fit, quality and performance, and that you will pay for any modifications needed to install them.", 'myautotriage' ),
			'tools'     => array(
				array( 'slug' => 'insurance-underpayment-demand-letter-generator', 'label' => __( 'Dispute an underpaid repair estimate', 'myautotriage' ) ),
				array( 'slug' => 'diminished-value-calculator', 'label' => __( 'Estimate your car\'s diminished value after repairs', 'myautotriage' ) ),
			),
			'faqs'      => array(
				array(
					'question' => __( 'Does using aftermarket parts void my car\'s warranty?', 'myautotriage' ),
					'answer'   => __( 'Federal law generally stops a manufacturer from voiding a whole warranty just because non-OEM parts were used, but it can refuse to cover damage those parts cause. Ask the shop and the insurer who stands behind the part.', 'myautotriage' ),
				),
				array(
					'question' => __( 'Are used (recycled) OEM parts the same as aftermarket?', 'myautotriage' ),
					'answer'   => __( 'No. Recycled or "LKQ" parts are original-manufacturer parts taken from another car. Estimates often mix new OEM, recycled and aftermarket parts, so read each line.', 'myautotriage' ),
				),
			),
			'sources'   => array( '902', 'maine' ),
		),
		array(
			'slug'      => 'betterment-deduction',
			'phrase'    => __( 'We\'re taking a betterment deduction.', 'myautotriage' ),
			'title'     => __( '"We\'re Taking a Betterment Deduction": Is That Allowed?', 'myautotriage' ),
			'seo_title' => __( 'Betterment Deduction on a Car Insurance Claim: Is It Legal?', 'myautotriage' ),
			'meta'      => __( 'Insurer deducted "betterment" or depreciation from your repair? When it\'s allowed, the itemization it needs, and how to challenge it.', 'myautotriage' ),
			'short'     => __( 'Often allowed, within limits. The idea is that new tires, a battery or other wear parts leave your car better than before the accident. Under the NAIC model rule, betterment must reflect a measurable change in value and be itemized with a dollar amount, and wear-and-tear deductions are capped.', 'myautotriage' ),
			'means'     => array(
				__( 'You will see it on parts that wear out: tires, batteries, brakes, sometimes paint. You pay part of the cost because the insurer says the new part is worth more than the worn one it replaced.', 'myautotriage' ),
			),
			'rights'    => array(
				__( 'When the amount claimed is reduced because of betterment or depreciation, the deductions must be itemized and specified as to dollar amount (Model 902, Section 8F).', 'myautotriage' ),
				__( 'Betterment deductions are allowed only if they reflect a measurable decrease in market value from poorer condition or prior damage, or the car\'s general condition, with wear and tear or rust limited to $1,000 (Section 8I).', 'myautotriage' ),
				__( 'Maine\'s regulator also says insurers may deduct for betterment when old parts are replaced with new.', 'myautotriage' ),
			),
			'reply'     => __( "Your estimate on claim [claim number] deducts $[amount] for betterment. Please itemize each betterment deduction with its dollar amount and explain how each reflects a measurable increase in my vehicle's value. Enclosed are records showing [the tires were replaced on (date) / the part's condition before the accident].", 'myautotriage' ),
			'tools'     => array(
				array( 'slug' => 'insurance-underpayment-demand-letter-generator', 'label' => __( 'Dispute an underpaid repair estimate', 'myautotriage' ) ),
				array( 'slug' => 'how-to-negotiate-insurance-adjuster', 'label' => __( 'Read: how to negotiate with an insurance adjuster', 'myautotriage' ) ),
			),
			'faqs'      => array(
				array(
					'question' => __( 'Can an insurer take betterment on a third-party claim?', 'myautotriage' ),
					'answer'   => __( 'Rules differ. Some states limit betterment against third-party claimants, who never agreed to the at-fault driver\'s policy terms. Check with your state insurance department.', 'myautotriage' ),
				),
				array(
					'question' => __( 'How do I prove a part wasn\'t worn?', 'myautotriage' ),
					'answer'   => __( 'Receipts showing when it was installed, a tread-depth reading or service record, and photos from before the accident.', 'myautotriage' ),
				),
			),
			'sources'   => array( '902', 'maine' ),
		),
		array(
			'slug'      => 'you-were-partly-at-fault',
			'phrase'    => __( 'We find you 50% at fault.', 'myautotriage' ),
			'title'     => __( '"We Find You 50% at Fault": How to Dispute a Fault Split', 'myautotriage' ),
			'seo_title' => __( 'Insurance Says You Were 50% at Fault? How to Dispute It', 'myautotriage' ),
			'meta'      => __( 'An adjuster split fault 50/50 or 70/30? What that does to your payout under your state\'s fault rule, and how to challenge the decision.', 'myautotriage' ),
			'short'     => __( 'A fault percentage is the insurer\'s opinion, not a final ruling. It matters a lot: depending on your state, your share of fault cuts your payout or, at 50% or 51% (or any fault at all in a few states), blocks it. Ask how the percentage was set and send evidence that changes it.', 'myautotriage' ),
			'means'     => array(
				__( 'Split decisions are common when there is no independent witness or video, or the drivers\' stories conflict. Insurers on both sides may each settle at their own split.', 'myautotriage' ),
			),
			'rights'    => array(
				__( 'The NAIC model act lists refusing to pay claims without conducting a reasonable investigation as an unfair claims practice (Model 900, Section 4F), and denials or compromise offers should come with a reasonable explanation (Section 4L).', 'myautotriage' ),
				__( 'Your state\'s fault rule decides what a split does to your payout. Our state pages list each state\'s rule with its citation.', 'myautotriage' ),
				__( 'Fault decided by an insurer doesn\'t bind a court. If the amount is within your state\'s small claims limit, a judge can decide fault.', 'myautotriage' ),
			),
			'reply'     => __( "I dispute your decision that I was [percent]% at fault for the accident on [date], claim [claim number]. Please send me the basis for this percentage, including the statements, photos and any police report you relied on. Enclosed is additional evidence: [photos of the damage pattern / witness contact / dashcam / police report]. Please review the liability decision and respond in writing within 14 days.", 'myautotriage' ),
			'tools'     => array(
				array( 'slug' => 'comparative-fault-calculator', 'label' => __( 'Calculate what a fault split does to your payout', 'myautotriage' ) ),
				array( 'slug' => 'appeal-letter-generator', 'label' => __( 'Write an appeal of the decision', 'myautotriage' ) ),
				array( 'slug' => 'small-claims-court-vs-insurance-claim', 'label' => __( 'Read: small claims court vs. your insurance claim', 'myautotriage' ) ),
			),
			'faqs'      => array(
				array(
					'question' => __( 'Does 50/50 fault mean I get half?', 'myautotriage' ),
					'answer'   => __( 'In pure comparative and modified (51% bar) states, yes, you recover 50%. In states with a 50% bar, being exactly 50% at fault can block recovery. In the few contributory negligence states, any fault can block it.', 'myautotriage' ),
				),
				array(
					'question' => __( 'Will a fault split raise my insurance rates?', 'myautotriage' ),
					'answer'   => __( 'It can, because your insurer may treat a shared-fault accident as partly at fault. How much depends on your insurer and state.', 'myautotriage' ),
				),
			),
			'sources'   => array( '900' ),
		),
		array(
			'slug'      => 'sign-this-release',
			'phrase'    => __( 'Sign the release and we\'ll send the check.', 'myautotriage' ),
			'title'     => __( '"Sign the Release and We\'ll Send the Check": Read This First', 'myautotriage' ),
			'seo_title' => __( 'Insurance Release Form: What You Give Up When You Sign', 'myautotriage' ),
			'meta'      => __( 'Asked to sign a release to get paid? What a release does, why a property damage release shouldn\'t cover injuries, and the rules on deadlines and "final" checks.', 'myautotriage' ),
			'short'     => __( 'A release usually ends that part of your claim for good. Read what it covers: a property damage release should be limited to property damage and not release injury claims. Don\'t sign under a deadline you don\'t understand, and don\'t sign until the amount covers everything listed.', 'myautotriage' ),
			'means'     => array(
				__( 'Insurers want a release so the claim can\'t be reopened. That is normal at the end of a claim. The problems come when the release is broader than the payment, such as releasing "all claims" when you are only being paid for your car.', 'myautotriage' ),
			),
			'rights'    => array(
				__( 'Insurers shall not suggest a third-party claimant\'s rights may be hurt if a form or release isn\'t completed in a set time, unless they are telling you about a statute of limitations (Model 902, Section 7E).', 'myautotriage' ),
				__( 'Insurers shall not issue checks in partial settlement under a specific coverage that contain language releasing the insurer or its insured from total liability (Section 5F).', 'myautotriage' ),
				__( 'An insurer negotiating directly with you while you have no lawyer must give you written notice of a lawsuit deadline before it expires: at least 60 days ahead for third-party claimants (Section 7D).', 'myautotriage' ),
			),
			'reply'     => __( "Thank you for the release on claim [claim number]. Before I sign, please revise it so that it releases only the property damage to my vehicle for the amount of $[amount], and does not release any bodily injury claim or any other claim. Please also confirm in writing what the payment covers (repairs, rental or loss of use, diminished value, towing and storage).", 'myautotriage' ),
			'tools'     => array(
				array( 'slug' => 'car-accident-lawsuit-deadline-calculator', 'label' => __( 'Work out the last day to sue after your accident', 'myautotriage' ) ),
				array( 'slug' => 'diminished-value-calculator', 'label' => __( 'Check you\'re not leaving diminished value behind', 'myautotriage' ) ),
				array( 'slug' => 'loss-of-use-calculator', 'label' => __( 'Check you\'ve claimed loss of use', 'myautotriage' ) ),
			),
			'faqs'      => array(
				array(
					'question' => __( 'Can I undo a release after signing it?', 'myautotriage' ),
					'answer'   => __( 'Rarely. Courts usually enforce signed releases unless there was fraud or a serious mistake. Treat signing as final.', 'myautotriage' ),
				),
				array(
					'question' => __( 'Do I need a release for my own insurance company to pay me?', 'myautotriage' ),
					'answer'   => __( 'Usually not for a first-party property damage payment. Releases are mainly used when the at-fault driver\'s insurer settles with you.', 'myautotriage' ),
				),
			),
			'sources'   => array( '902' ),
		),
		array(
			'slug'      => 'use-our-preferred-shop',
			'phrase'    => __( 'You\'ll need to use one of our preferred shops.', 'myautotriage' ),
			'title'     => __( '"Use One of Our Preferred Shops": Can You Choose Your Own?', 'myautotriage' ),
			'seo_title' => __( 'Do I Have to Use the Insurance Company\'s Preferred Body Shop?', 'myautotriage' ),
			'meta'      => __( 'Adjuster says you must use their preferred body shop? Usually you can choose your own. What happens if your shop\'s estimate is higher, and who guarantees the repair.', 'myautotriage' ),
			'short'     => __( 'Generally you can choose your own repair shop, and many states have laws against insurers steering you. If your shop\'s estimate is higher, under the NAIC model rule the insurer must either pay the difference or name a shop that will do the repair for its estimate.', 'myautotriage' ),
			'means'     => array(
				__( 'Preferred or direct-repair shops have agreements with the insurer on rates and processes. That can make things faster, but the shop also works with the insurer every day. Your own shop works for you.', 'myautotriage' ),
			),
			'rights'    => array(
				__( 'Maine\'s regulator says you are not required to use a repair shop recommended by the insurer, though you may pay the difference if your shop charges more.', 'myautotriage' ),
				__( 'If your written estimate is higher, the insurer must pay the difference or promptly name at least one shop that will repair for its estimate (Model 902, Section 8E).', 'myautotriage' ),
				__( 'When the insurer designates a shop, it must have the car restored to its pre-loss condition at no extra cost to you beyond the policy terms, in a reasonable time (Section 8G), and it can\'t make you travel an unreasonable distance (Section 8C).', 'myautotriage' ),
			),
			'reply'     => __( "I have chosen [shop name and address] to repair my vehicle on claim [claim number]. Please send your appraiser or arrange an inspection there. If your estimate is lower than my shop's, please either pay the difference or give me the name of a shop that will complete the repairs for your estimate, as I understand the claim-handling rules require.", 'myautotriage' ),
			'tools'     => array(
				array( 'slug' => 'insurance-underpayment-demand-letter-generator', 'label' => __( 'Dispute an underpaid repair estimate', 'myautotriage' ) ),
				array( 'slug' => 'total-loss-threshold-calculator', 'label' => __( 'Check whether your car should be totaled instead', 'myautotriage' ) ),
			),
			'faqs'      => array(
				array(
					'question' => __( 'Who guarantees the repair if I use the insurer\'s shop?', 'myautotriage' ),
					'answer'   => __( 'Ask in writing. Many insurers offer a lifetime guarantee on repairs at their direct-repair shops. With your own shop, the shop\'s warranty applies.', 'myautotriage' ),
				),
				array(
					'question' => __( 'Will my claim be slower if I use my own shop?', 'myautotriage' ),
					'answer'   => __( 'Sometimes a little, because the insurer has to send an appraiser and approve supplements. Ask the shop how it works with your insurer.', 'myautotriage' ),
				),
			),
			'sources'   => array( 'maine', '902' ),
		),
	);
}

function mat_adjuster_hub_url() {
	return home_url( user_trailingslashit( MAT_ADJUSTER_HUB_SLUG ) );
}

function mat_adjuster_url( $phrase ) {
	return home_url( user_trailingslashit( MAT_ADJUSTER_HUB_SLUG . '/' . $phrase['slug'] ) );
}

/**
 * The phrase shown on the current page, or null.
 */
function mat_current_adjuster_phrase( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || 'page' !== $post->post_type || ! $post->post_parent ) {
		return null;
	}
	$parent = get_post( $post->post_parent );
	if ( ! $parent || MAT_ADJUSTER_HUB_SLUG !== $parent->post_name ) {
		return null;
	}
	foreach ( mat_adjuster_phrases() as $phrase ) {
		if ( $phrase['slug'] === $post->post_name ) {
			return $phrase;
		}
	}
	return null;
}

function mat_adjuster_sources( $phrase ) {
	$common  = mat_adjuster_sources_common();
	$sources = array();
	foreach ( $phrase['sources'] as $key ) {
		if ( isset( $common[ $key ] ) ) {
			$sources[] = $common[ $key ];
		}
	}
	return $sources;
}

/**
 * Create the hub and phrase pages. Runs on wp_loaded, so deploying the
 * theme is enough.
 */
function mat_ensure_adjuster_pages() {
	if ( MAT_ADJUSTER_PAGES_VERSION === get_option( 'mat_adjuster_pages_version' ) ) {
		return;
	}
	update_option( 'mat_adjuster_pages_version', MAT_ADJUSTER_PAGES_VERSION );

	$hub = get_page_by_path( MAT_ADJUSTER_HUB_SLUG );
	if ( ! $hub ) {
		$hub_id = wp_insert_post( array(
			'post_title'   => __( 'What the Adjuster Said: Common Phrases Decoded', 'myautotriage' ),
			'post_name'    => MAT_ADJUSTER_HUB_SLUG,
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
	update_post_meta( $hub_id, '_wp_page_template', 'page-templates/template-adjuster-hub.php' );

	foreach ( mat_adjuster_phrases() as $i => $phrase ) {
		if ( get_page_by_path( MAT_ADJUSTER_HUB_SLUG . '/' . $phrase['slug'] ) ) {
			continue;
		}
		$post_id = wp_insert_post( array(
			'post_title'   => $phrase['title'],
			'post_name'    => $phrase['slug'],
			'post_parent'  => $hub_id,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'menu_order'   => $i,
		) );
		if ( $post_id && ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, '_wp_page_template', 'page-templates/template-adjuster-phrase.php' );
		}
	}
}
add_action( 'wp_loaded', 'mat_ensure_adjuster_pages' );
