<?php
/**
 * Rewritten guide bodies (October 2026 rewrite, from the 2026-10-24 audit).
 *
 * Each body replaces a launch article in inc/seed-articles.php and, on
 * live sites, the stored post via mat_apply_content_fixes(). Bodies open
 * with a <p class="mat-lede"> paragraph; that class marks a post as
 * already rewritten. Legal facts come from the state data files the
 * tables on the site use, so the guides and tables agree.
 *
 * @package MyAutoTriage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rewritten guides: slug => meta description and excerpt.
 */
function mat_guide_rewrites() {
	return array(
		'how-to-file-diminished-value-claim' => array(
			'meta'    => 'Who pays diminished value in your state, the 17c formula and its factor tables, and how to back a higher number with an appraisal or dealer quotes.',
			'excerpt' => 'Who pays for the value your car loses after an accident, how insurers calculate it, and how to prove a higher number.',
		),
		'totaled-car-payout-too-low-negotiate' => array(
			'meta'    => 'Total loss offer too low? What the payout must cover, sales tax rules by state, how to check the valuation report, and how to counter, step by step.',
			'excerpt' => 'What a total loss payout has to cover, how to find the money taken off in the valuation report, and how to counter it in writing.',
		),
	);
}

/**
 * HTML for a small two-or-more column table inside a guide.
 */
function mat_guide_table( $caption, $head, $rows ) {
	$html = '<div class="mat-table-wrap"><table class="mat-table"><caption>' . esc_html( $caption ) . '</caption><thead><tr>';
	foreach ( $head as $cell ) {
		$html .= '<th>' . esc_html( $cell ) . '</th>';
	}
	$html .= '</tr></thead><tbody>';
	foreach ( $rows as $row ) {
		$html .= '<tr>';
		foreach ( $row as $cell ) {
			$html .= '<td>' . esc_html( $cell ) . '</td>';
		}
		$html .= '</tr>';
	}
	return $html . '</tbody></table></div>';
}

/**
 * The rewritten body for one guide, or '' if it hasn't been rewritten.
 */
function mat_guide_rewrite_content( $slug ) {
	$url = function ( $path ) {
		return esc_url( home_url( '/' . $path . '/' ) );
	};
	$glance = mat_guide_glance_tables();
	$glance = isset( $glance[ $slug ] ) ? mat_glance_table_html( $glance[ $slug ] ) : '';

	switch ( $slug ) {
		case 'how-to-file-diminished-value-claim':
			return '<p class="mat-lede">A repaired car with an accident on its history report sells for less than the same car with a clean report. That loss is diminished value. You can usually claim it from the at-fault driver\'s insurer. Claiming it from your own insurer works in only four states. This guide covers who pays, how insurers work out the number, how to back a higher one, and the deadlines.</p>'
				. $glance

				. '<h2>Who pays: the other driver\'s insurer or yours</h2>'
				. '<p>A <strong>third-party claim</strong> goes to the at-fault driver\'s liability insurer. Courts in most states treat the lost value as part of the property damage the other driver caused. A <strong>first-party claim</strong> goes to your own insurer under your collision or comprehensive coverage, and most courts have said the standard "repair or replace" policy wording doesn\'t cover it.</p>'
				. mat_guide_table(
					'Where each kind of claim stands',
					array( 'Claim', 'Where it works', 'Where it doesn\'t' ),
					array(
						array( 'Third party (the at-fault driver\'s insurer)', 'Recognised in 32 states and DC by statute, case law or regulator guidance. Unsettled in the rest.', 'Nebraska and North Dakota: if the car is repaired, the measure is the repair cost (plus loss of use). Michigan: car damage from the other driver is capped at the $3,000 mini-tort.' ),
						array( 'First party (your own policy)', 'Georgia (State Farm v. Mabry, 2001), Oregon (Gonzales v. Farmers, 2008), Vermont (Insurance Bulletin 164, 2011) and Washington (Moeller v. Farmers, 2011), unless your policy clearly excludes it.', 'Courts or regulators in 24 states, including California, Florida and Texas, have said standard policies don\'t pay it. Elsewhere it\'s unsettled, and many policies now exclude it in writing.' ),
					)
				)
				. '<p>Our <a href="' . $url( 'diminished-value-by-state' ) . '">diminished value rules by state</a> table lists the case or statute for every state. Two state rules are worth knowing even if you never go to court:</p>'
				. '<ul><li><strong>North Carolina:</strong> liability policies must offer appraisal when you and the insurer are more than $2,000 or 25% apart on diminished value (N.C. Gen. Stat. § 20-279.21(d1)). A release you sign for the repairs doesn\'t bar a diminished value claim made within 30 days after the repair (11 NCAC 04.0421).</li>'
				. '<li><strong>Texas:</strong> the Department of Insurance (Bulletin B-0027-00) says a liability insurer may owe a third-party claimant the loss of market value even after a complete repair. The Texas Supreme Court said in 2003 that your own policy doesn\'t (American Manufacturers v. Schaefer).</li></ul>'
				. '<p>If you caused the accident, there is no third-party claim. Outside the four first-party states, the loss is usually yours.</p>'

				. '<h2>How insurers work out the number: the 17c formula</h2>'
				. '<p>Most insurers start from "17c", named after paragraph 17(c) of the settlement order in the Georgia <em>Mabry</em> case. Take 10% of the car\'s pre-accident value, then multiply by a damage factor and a mileage factor.</p>'
				. mat_guide_table(
					'17c damage factor',
					array( 'Damage', 'Factor' ),
					array(
						array( 'Severe structural or frame damage', '1.00' ),
						array( 'Major damage to structure and panels', '0.75' ),
						array( 'Moderate damage to structure and panels', '0.50' ),
						array( 'Minor damage to structure and panels', '0.25' ),
						array( 'No structural damage or replaced panels', '0.00' ),
					)
				)
				. mat_guide_table(
					'17c mileage factor',
					array( 'Odometer', 'Factor' ),
					array(
						array( 'Under 20,000 miles', '1.00' ),
						array( '20,000 to 39,999', '0.80' ),
						array( '40,000 to 59,999', '0.60' ),
						array( '60,000 to 79,999', '0.40' ),
						array( '80,000 to 99,999', '0.20' ),
						array( '100,000 or more', '0.00' ),
					)
				)
				. '<p>Example: a $24,000 car with moderate damage and 35,000 miles comes to $24,000 × 10% × 0.50 × 0.80 = $960. The <a href="' . $url( 'diminished-value-calculator' ) . '">diminished value calculator</a> does the same sum and shows each factor.</p>'

				. '<h2>Why 17c is a floor, and how to show a higher number</h2>'
				. '<p>17c caps the loss at 10% of the car\'s value before any cut, ignores how buyers of your model react to an accident record, and gives nothing at 100,000 miles or more. The market often says otherwise, mostly on newer, low-mileage, luxury and in-demand cars. Three kinds of evidence beat the formula:</p>'
				. '<ul><li><strong>An independent appraisal</strong> based on comparable sales of your make, model, year and mileage, with and without accident history. Ask the appraiser for the fee up front and for a report written to support a demand.</li>'
				. '<li><strong>Written trade-in quotes</strong> from dealers who have seen the history report. A quote that names the accident as the reason for the lower figure is strong evidence.</li>'
				. '<li><strong>Your own history report.</strong> Pull the CARFAX or AutoCheck report and check how the accident is described. If it overstates the damage, dispute it with the report provider first; a wrong "structural damage" line costs you money whether you claim or not.</li></ul>'

				. '<h2>Step by step</h2>'
				. '<ol><li><strong>Check you have a claim.</strong> Third party if the other driver was at fault; first party only in Georgia, Oregon, Vermont and Washington, and only if your policy doesn\'t exclude it.</li>'
				. '<li><strong>Wait for the repair to finish.</strong> Keep the final invoice with every part and labor line, plus any supplements. Photos from before the repair matter if the insurer later plays down the damage.</li>'
				. '<li><strong>Get your number.</strong> Run 17c, then decide whether an appraisal is worth its fee. It is most likely to pay off on newer, low-mileage or higher-value cars, where 17c falls furthest behind the market.</li>'
				. '<li><strong>Send a written demand</strong> to the adjuster with the invoice, history report, appraisal or dealer quotes, and a date to respond by. The <a href="' . $url( 'diminished-value-demand-letter-generator' ) . '">diminished value demand letter generator</a> fills in your number from the calculator.</li>'
				. '<li><strong>Log every reply.</strong> A <a href="' . $url( 'claim-diary' ) . '">claim diary</a> keeps dates and promises in one place, which matters if you escalate.</li>'
				. '<li><strong>If the insurer refuses or lowballs,</strong> ask in writing for the basis of its figure, then answer it point by point. After that, your options are a complaint to your state insurance department, and on a third-party claim, a small claims case against the at-fault driver (see <a href="' . $url( 'small-claims-court-vs-insurance-claim' ) . '">small claims vs. an insurance claim</a>). On a first-party claim, check whether your policy\'s <a href="' . $url( 'insurance-appraisal-clause' ) . '">appraisal clause</a> covers the dispute.</li></ol>'

				. '<h2>Deadlines</h2>'
				. '<p>A third-party diminished value claim runs on your state\'s deadline for property damage lawsuits, which is two to six years in most states. Negotiating doesn\'t stop the clock. Each <a href="' . $url( 'car-insurance-claim-laws' ) . '">state claim law page</a> lists the deadline. First-party claims also run on your policy\'s own notice and suit limits, which can be shorter. Claim soon after the repair while the records are fresh, and within 30 days in North Carolina if you signed a repair release.</p>'

				. '<h2>Special cases</h2>'
				. '<ul><li><strong>Michigan:</strong> what you can recover from the at-fault driver for car damage is capped at $3,000 (MCL 500.3135(3)(e)). The <a href="' . $url( 'michigan-mini-tort-calculator' ) . '">mini-tort calculator</a> shows how much of that applies to you.</li>'
				. '<li><strong>Leased cars:</strong> the leasing company owns the car, so it usually has the claim. It matters to you if you plan to buy the car at lease end, or if your lease charges you for lost value. Read the lease and talk to the leasing company before you claim.</li>'
				. '<li><strong>Totaled cars:</strong> there is no diminished value claim on a total loss, because the insurer pays the whole pre-accident value. Check the valuation instead with the <a href="' . $url( 'total-loss-valuation-checker' ) . '">total loss valuation checker</a>.</li>'
				. '<li><strong>Keeping the car for years:</strong> the loss is still real, but it shows up when you sell or trade in. Whether an appraisal is worth its fee depends on how soon that is.</li></ul>'

				. '<p>Insurers rarely bring up diminished value themselves. Knowing whether your state and your claim type support it, and backing your number with market evidence rather than the formula alone, is most of the work.</p>';

		case 'totaled-car-payout-too-low-negotiate':
			return '<p class="mat-lede">A total loss offer is the insurer\'s opening number, built by valuation software from a handful of comparable cars and a list of deductions. You can ask for the report behind it, check each line, and counter in writing with your own evidence. This guide covers what the payout should include, where money usually gets taken off, and what to do if the insurer won\'t move.</p>'
				. $glance

				. '<h2>What a total loss payout should cover</h2>'
				. '<p>Most state total loss rules follow the NAIC model claims settlement regulation. Under it, a cash settlement should be based on the actual cost to buy a comparable car in your local market, less your deductible, plus the taxes and fees to transfer ownership (Section 8A(2)). Any deduction, including for condition, must be "measurable, discernible, itemized and specified as to dollar amount" (Section 8A(3)). If you tell the insurer within 35 days that you can\'t buy a comparable car for the amount paid, it must reopen the claim, unless it already showed you a specific car, by VIN, that you could have bought for that amount (Section 8A(2)(e)). Your state\'s own regulation may differ in the details.</p>'

				. '<h2>Sales tax and fees: the line most often missing</h2>'
				. '<p>Whether the insurer has to add sales tax depends on your state, and whether you claim from your own insurer or from the at-fault driver\'s.</p>'
				. mat_guide_table(
					'Sales tax on a total loss settlement, by state rule',
					array( 'Rule', 'States' ),
					array(
						array( 'Included in the settlement', '30 states, including California, Florida, Georgia, New York, Pennsylvania and Washington' ),
						array( 'Repaid after you buy a replacement', 'Hawaii, Illinois and Ohio (Ohio: buy within 30 days and the insurer repays the tax, up to the tax on a car worth the settlement)' ),
						array( 'Regulator guidance only, no rule', 'Idaho, New Mexico, Wisconsin and Wyoming' ),
						array( 'Depends on your policy', '10 jurisdictions, including Texas, Michigan, Massachusetts, North Carolina and DC' ),
						array( 'No state sales tax', 'Delaware, Montana, New Hampshire and Oregon' ),
					)
				)
				. '<p>About half of states also say title and registration fees must be paid. The <a href="' . $url( 'total-loss-sales-tax-by-state' ) . '">total loss sales tax and fees by state</a> table gives the rule and citation for each state, including what applies to third-party claims.</p>'

				. '<h2>First check: should it be a total loss at all?</h2>'
				. '<p>25 states set a percentage: the car is totaled once repairs reach a set share of its value, from 65% (Nevada) to 100% (Texas). Oklahoma moves from 60% to 70% on November 1, 2026. Five states (Connecticut, Massachusetts, Pennsylvania, Virginia and Washington) use the Total Loss Formula: repair cost plus salvage value against the car\'s value. In the other 21 the insurer decides, and most use the same formula. The <a href="' . $url( 'total-loss-threshold-calculator' ) . '">total loss threshold calculator</a> applies your state\'s rule to your numbers. If you think the car could be repaired, an estimate from an independent body shop is the evidence that matters.</p>'

				. '<h2>Read the valuation report line by line</h2>'
				. '<p>Ask the adjuster in writing for the complete valuation report (usually CCC, Mitchell or Audatex), with every comparable car and every adjustment. Then check these lines:</p>'
				. mat_guide_table(
					'Where money usually comes off',
					array( 'Line in the report', 'What to check' ),
					array(
						array( 'Comparable cars', 'Same make, model, year and trim, similar options and mileage, and close to you. Cross out any that aren\'t.' ),
						array( 'Projected sold or "typical negotiation" adjustment', 'Each comparable\'s advertised price cut to a predicted sale price. Ask for the data behind it; you\'d pay the advertised price to replace your car.' ),
						array( 'Condition adjustment', 'Your car rated below average. Ask who inspected it and which photos support each rating; send maintenance records and photos from before the loss.' ),
						array( 'Options and packages', 'Missing packages, upgraded trim or recent major work (new tires, a replaced transmission) that should add value.' ),
						array( 'Prior damage deduction', 'Must be itemised with a dollar amount. Unrepaired damage you never had shouldn\'t be there.' ),
						array( 'Tax, title and registration', 'Included if your state requires it (table above).' ),
					)
				)
				. '<p>The <a href="' . $url( 'total-loss-valuation-checker' ) . '">total loss valuation checker</a> takes the report\'s numbers and comparables, flags each deduction against the model rule, and turns the result into a counter-offer letter.</p>'

				. '<h2>Find your own comparable cars</h2>'
				. '<p>Search Autotrader, Cars.com, CarGurus and local dealer sites for the same year, make, model and trim, in a similar mileage band and within a reasonable distance. Save at least three listings as PDFs or screenshots with the price, mileage and location visible. Listings close to your car beat a long list of loose matches.</p>'

				. '<h2>Counter in writing</h2>'
				. '<p>Send the adjuster a short letter: the figure you want, how you got it (line by line against their report), your listings, and a date to reply by. A written counter gets a written answer; a phone call gets "that\'s our valuation." The <a href="' . esc_url( add_query_arg( 'type', 'total-loss', home_url( '/demand-letter-generator/' ) ) ) . '">total loss counter-offer letter</a> builds this for you, and a <a href="' . $url( 'claim-diary' ) . '">claim diary</a> keeps the dates.</p>'
				. '<p><strong>A worked example (hypothetical, round numbers):</strong> the report averages three comparables advertised at $15,200, takes $700 off as a projected sold adjustment and $500 for condition, for a value of $14,000. Less a $500 deductible, the offer is $13,500 with no tax. You challenge the $700 adjustment, and the $500 condition cut because it isn\'t itemised, and send three listings averaging $15,400. If your state includes sales tax at, say, 7%, your counter is $15,200 + $1,064 tax − $500 deductible = $15,764. Whether and by how much an insurer moves varies.</p>'

				. '<h2>If the insurer won\'t move</h2>'
				. '<ul><li><strong>Appraisal clause (your own policy).</strong> You and the insurer each hire an appraiser; if they disagree, an umpire decides, and two of the three set a binding figure. Each side usually pays its own appraiser and splits the umpire. See <a href="' . $url( 'insurance-appraisal-clause' ) . '">how the appraisal clause works</a>, including states with their own appraisal rules.</li>'
				. '<li><strong>A complaint to your state insurance department.</strong> It\'s free and the insurer has to answer in writing. Our <a href="' . $url( 'insurer-complaints' ) . '">insurer complaint scorecard</a> shows how often complaints to Texas regulators ended with more money paid.</li>'
				. '<li><strong>Small claims court (claims against the at-fault driver).</strong> There is no appraisal clause on someone else\'s policy; see <a href="' . $url( 'small-claims-court-vs-insurance-claim' ) . '">small claims vs. an insurance claim</a>.</li></ul>'
				. '<p>Weigh the gap against the cost. An appraiser\'s fee and a few weeks may not be worth it for a few hundred dollars; they usually are for a few thousand.</p>'

				. '<h2>Before you sign: loan, rental and salvage</h2>'
				. '<ul><li><strong>Loan or lease:</strong> the check usually goes to your lender first, and you get anything left over once it releases its lien. If the settlement is less than you owe, run the <a href="' . $url( 'gap-insurance-shortfall-calculator' ) . '">GAP shortfall calculator</a>; GAP contracts often exclude the deductible and past-due payments.</li>'
				. '<li><strong>Rental:</strong> rental coverage has a day or dollar limit that keeps running while you negotiate. You can accept the undisputed amount, or ask for more rental time in writing with the <a href="' . $url( 'rental-car-extension-letter' ) . '">rental extension letter</a>.</li>'
				. '<li><strong>Keeping the car:</strong> most insurers let you keep it if you accept the salvage value being taken off the payout. The car then usually gets a salvage title, which affects registration, insurance and resale.</li></ul>'

				. '<p>The first offer is the software\'s number, not a final one. A complete report, three solid listings and a written counter are what move it.</p>';

	}
	return '';
}
