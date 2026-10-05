/**
 * GAP Insurance Shortfall Calculator.
 *
 * Shortfall = Loan/Lease Payoff Balance - (Insurer ACV Settlement - Deductible)
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-gap-form' );
	if ( ! form ) {
		return;
	}
	var resultBox = document.getElementById( 'mat-gap-result' );

	function formatUSD( n ) {
		return n.toLocaleString( 'en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 } );
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();

		var payoff = parseFloat( document.getElementById( 'mat-gap-payoff' ).value );
		var acv = parseFloat( document.getElementById( 'mat-gap-acv' ).value );
		var deductible = parseFloat( document.getElementById( 'mat-gap-deductible' ).value ) || 0;
		var hasGap = document.getElementById( 'mat-gap-has-gap' ).value;

		if ( isNaN( payoff ) || payoff <= 0 || isNaN( acv ) || acv < 0 ) {
			resultBox.innerHTML = '<p role="alert">Please enter your loan payoff balance and the insurer\'s settlement value.</p>';
			resultBox.hidden = false;
			return;
		}

		var insurerPayout = Math.max( 0, acv - deductible );
		var shortfall = payoff - insurerPayout;

		var html = '';
		html += '<p>Insurer payout after your deductible: <strong>' + formatUSD( insurerPayout ) + '</strong> (' + formatUSD( acv ) + ' ACV &minus; ' + formatUSD( deductible ) + ' deductible).</p>';

		if ( shortfall <= 0 ) {
			html = '<p class="mat-result-box__figure">' + formatUSD( 0 ) + ' shortfall</p>' + html +
				'<p>Your insurance settlement covers your loan payoff. You likely don\'t need GAP coverage on this claim.</p>';
		} else {
			html = '<p class="mat-result-box__figure">' + formatUSD( Math.round( shortfall ) ) + ' shortfall</p>' + html +
				'<p>This is the gap between what you owe and what your insurer is paying.</p>';
			if ( hasGap === 'yes' ) {
				html += '<p style="margin-bottom:0;font-size:.9rem;">If you have GAP coverage, this is generally the amount it should cover — minus any common exclusions in your GAP contract, such as unpaid finance charges, extended warranties rolled into the loan, past-due payments, or your deductible (some GAP policies do cover the deductible up to a small cap, some don\'t). Read your GAP contract\'s exclusions section before filing.</p>';
			} else {
				html += '<p style="margin-bottom:0;font-size:.9rem;">Without GAP coverage, this shortfall is generally your responsibility to pay off with your lender directly.</p>';
			}
		}

		resultBox.innerHTML = html;
		resultBox.hidden = false;
		resultBox.setAttribute( 'tabindex', '-1' );
		resultBox.focus();
	} );
})();
