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

	function formatUSD( n ) {
		return n.toLocaleString( 'en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 } );
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();

		var repairCost = parseFloat( document.getElementById( 'mat-ded-repair' ).value );
		var deductible = parseFloat( document.getElementById( 'mat-ded-deductible' ).value );
		var surcharge = parseFloat( document.getElementById( 'mat-ded-surcharge' ).value ) || 0;
		var years = parseFloat( document.getElementById( 'mat-ded-years' ).value ) || 3;

		if ( isNaN( repairCost ) || repairCost <= 0 || isNaN( deductible ) || deductible < 0 ) {
			resultBox.innerHTML = '<p role="alert">Please enter the repair cost and your deductible.</p>';
			resultBox.hidden = false;
			return;
		}

		var claimCost = Math.min( repairCost, deductible ) + ( surcharge * years );
		// If repair cost is below the deductible, filing a claim gets you $0 from
		// the insurer anyway, so "claim cost" is really just the surcharge on top
		// of paying the full repair yourself.
		if ( repairCost <= deductible ) {
			claimCost = repairCost + ( surcharge * years );
		}

		var outOfPocketCost = repairCost;
		var betterToFile = claimCost < outOfPocketCost;
		var difference = Math.abs( claimCost - outOfPocketCost );

		var html = '';
		html += '<p class="mat-result-box__figure">' + ( betterToFile ? 'Filing a claim looks cheaper' : 'Paying out of pocket looks cheaper' ) + '</p>';
		html += '<p><strong>Cost if you file a claim:</strong> ' + formatUSD( Math.round( claimCost ) ) + ' (deductible/repair cost' + ( surcharge ? ' + estimated ' + years + '-year premium surcharge of ' + formatUSD( Math.round( surcharge * years ) ) : '' ) + ')</p>';
		html += '<p><strong>Cost if you pay yourself:</strong> ' + formatUSD( Math.round( outOfPocketCost ) ) + '</p>';
		html += '<p style="margin-bottom:0;font-size:.9rem;">Estimated difference: ' + formatUSD( Math.round( difference ) ) + '. This ignores non-financial factors — a claim on your record can also affect future shopping for insurance, and some damage (like a cracked windshield in some states) doesn\'t raise your rate at all.</p>';

		resultBox.innerHTML = html;
		resultBox.hidden = false;
		resultBox.setAttribute( 'tabindex', '-1' );
		resultBox.focus();
	} );
})();
