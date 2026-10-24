/**
 * File-a-Claim Break-Even Calculator.
 *
 * Compares paying out of pocket vs. filing a claim once the deductible and
 * the likely multi-year premium surcharge are both counted.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-ded-form' );
	if ( ! form ) {
		return;
	}
	var resultBox = document.getElementById( 'mat-ded-result' );
	var usd = MAT.usd;

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();

		var r = MAT.nums( {
			repairCost: [ 'mat-ded-repair', { label: 'The repair cost', required: true } ],
			deductible: [ 'mat-ded-deductible', { label: 'Your deductible', required: true } ],
			surcharge: [ 'mat-ded-surcharge', { label: 'The annual premium increase', fallback: 0 } ],
			years: [ 'mat-ded-years', { label: 'The number of years', fallback: 3, integer: true } ],
		} );
		if ( r.error ) {
			MAT.showError( resultBox, r );
			return;
		}
		var v = r.values;
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

		MAT.showResult( resultBox, html );
	} );
})();
