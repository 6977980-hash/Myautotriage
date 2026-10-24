/**
 * File-a-Claim Break-Even Calculator.
 *
 * Compares paying out of pocket vs. filing a claim once the deductible and
 * the likely multi-year premium surcharge are both counted. Where a state
 * sets the surcharge rule publicly (data-states on the form, from
 * claim-surcharge-rules.json) the result applies it: North Carolina's
 * point schedule estimates the increase, California and Massachusetts
 * thresholds say when a claim can't be surcharged.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-ded-form' );
	if ( ! form ) {
		return;
	}
	var resultBox = document.getElementById( 'mat-ded-result' );
	var usd = MAT.usd;
	var states = {};
	try {
		states = JSON.parse( form.getAttribute( 'data-states' ) || '{}' );
	} catch ( err ) {}

	function stateNotes( st, v ) {
		if ( ! st ) {
			return '';
		}
		if ( ! st.text ) {
			return '<p style="font-size:.9rem;"><strong>' + MAT.escape( st.name ) + ':</strong> we have no state-set surcharge rule on file, so the increase comes from your insurer\'s own rating plan. Ask your agent what an at-fault claim would add.</p>';
		}
		var html = '<p style="font-size:.9rem;"><strong>' + MAT.escape( st.name ) + ':</strong> ' + MAT.escape( st.text ) + ' <a href="' + MAT.escape( st.url ) + '" rel="noopener" target="_blank">' + MAT.escape( st.cite ) + '</a></p>';
		if ( 'threshold' === st.kind && 'payment' === st.basis && v.repairCost - v.deductible <= st.threshold ) {
			html += '<p><strong>For this repair:</strong> the insurer would pay ' + usd( v.repairCost - v.deductible ) + ', not more than ' + usd( st.threshold ) + ' above your deductible, so under the state plan the claim shouldn\'t add a surcharge. Filing would cost you ' + usd( v.deductible ) + '.</p>';
		} else if ( 'threshold' === st.kind && 'total' === st.basis && v.repairCost <= st.threshold ) {
			html += '<p><strong>For this repair:</strong> if nobody was hurt and the total damage in the accident, all cars included, was ' + usd( st.threshold ) + ' or less, it can\'t count as an at-fault accident, so filing would cost you ' + usd( v.deductible ) + '.</p>';
		}
		return html;
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();

		var r = MAT.nums( {
			repairCost: [ 'mat-ded-repair', { label: 'The repair cost', required: true } ],
			deductible: [ 'mat-ded-deductible', { label: 'Your deductible', required: true } ],
			surcharge: [ 'mat-ded-surcharge', { label: 'The annual premium increase', fallback: 0 } ],
			years: [ 'mat-ded-years', { label: 'The number of years', fallback: 3, integer: true } ],
			premium: [ 'mat-ded-premium', { label: 'Your premium', fallback: 0 } ],
		} );
		if ( r.error ) {
			MAT.showError( resultBox, r );
			return;
		}
		var v = r.values;
		var st = states[ document.getElementById( 'mat-ded-state' ).value ] || null;
		var estimate = '';
		if ( st && 'points' === st.kind && '' === document.getElementById( 'mat-ded-surcharge' ).value.trim() && v.premium > 0 ) {
			var tier = st.tiers.filter( function ( t ) {
				return null === t.max || v.repairCost <= t.max;
			} )[ 0 ];
			v.surcharge = v.premium * tier.pct / 100;
			v.years = st.years;
			estimate = '<p style="font-size:.9rem;">Premium increase estimated from ' + MAT.escape( st.name ) + '\'s plan: ' + tier.points + ' insurance point' + ( 1 === tier.points ? '' : 's' ) + ', +' + tier.pct + '% of your ' + usd( v.premium ) + ' premium for ' + st.years + ' years, taking your repair cost as the total damage. If other property was damaged too, add it: more damage can mean more points.</p>';
		}
		var surchargeTotal = v.surcharge * v.years;

		if ( v.repairCost <= v.deductible ) {
			MAT.showResult( resultBox,
				'<p class="mat-result-box__figure">Don\'t file</p>' +
				'<p>The repair (' + usd( v.repairCost ) + ') is no more than your ' + usd( v.deductible ) + ' deductible, so the insurer would pay $0, and a claim could still raise your premium.</p>' +
				'<p style="margin-bottom:0;font-size:.9rem;">If another driver caused the damage, you can claim the repair from their insurer instead; your deductible doesn\'t apply there.</p>' );
			return;
		}

		// The insurer pays repair cost minus the deductible, so filing costs
		// you the deductible plus the surcharge.
		var claimCost = v.deductible + surchargeTotal;
		var outOfPocketCost = v.repairCost;
		var betterToFile = claimCost < outOfPocketCost;
		var difference = Math.abs( claimCost - outOfPocketCost );

		var html = '';
		html += '<p class="mat-result-box__figure">' + ( difference < 1 ? 'It costs about the same either way' : ( betterToFile ? 'Filing a claim looks cheaper' : 'Paying out of pocket looks cheaper' ) ) + '</p>';
		html += '<p><strong>Cost if you file a claim:</strong> ' + usd( Math.round( claimCost ) ) + ' (your deductible' + ( surchargeTotal ? ' + an estimated ' + v.years + '-year premium increase of ' + usd( Math.round( surchargeTotal ) ) : '' ) + ')</p>';
		html += '<p><strong>Cost if you pay yourself:</strong> ' + usd( Math.round( outOfPocketCost ) ) + '</p>';
		html += '<p style="margin-bottom:0;font-size:.9rem;">Estimated difference: ' + usd( Math.round( difference ) ) + '. This ignores non-financial factors: a claim on your record can also affect future shopping for insurance, and some damage (like a cracked windshield in some states) doesn\'t raise your rate at all.</p>';
		html += estimate + stateNotes( st, v );

		MAT.showResult( resultBox, html );
	} );
})();
