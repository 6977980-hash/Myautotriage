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
	var usd = MAT.usd;

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();

		var r = MAT.nums( {
			payoff: [ 'mat-gap-payoff', { label: 'Your loan payoff balance', required: true } ],
			acv: [ 'mat-gap-acv', { label: 'The insurer\'s settlement value', required: true } ],
			deductible: [ 'mat-gap-deductible', { label: 'Your deductible', fallback: 0 } ],
		} );
		if ( r.error ) {
			MAT.showError( resultBox, r );
			return;
		}
		var v = r.values;
		var hasGap = document.getElementById( 'mat-gap-has-gap' ).value;

		var insurerPayout = Math.max( 0, v.acv - v.deductible );
		var shortfall = v.payoff - insurerPayout;

		var html = '';
		var detail = '<p>Insurer payout after your deductible: <strong>' + usd( insurerPayout ) + '</strong> (' + usd( v.acv ) + ' actual cash value &minus; ' + usd( v.deductible ) + ' deductible).</p>';

		if ( shortfall <= 0 ) {
			html += '<p class="mat-result-box__figure">' + usd( 0 ) + ' shortfall</p>' + detail;
			html += '<p>Your insurance settlement covers your loan payoff' + ( shortfall < 0 ? ', with about ' + usd( -shortfall ) + ' left over for you' : '' ) + '. GAP coverage isn\'t needed on this claim.</p>';
		} else {
			html += '<p class="mat-result-box__figure">' + usd( Math.round( shortfall ) ) + ' shortfall</p>' + detail;
			html += '<p>This is the gap between what you owe and what your insurer is paying.</p>';
			if ( hasGap === 'yes' ) {
				html += '<p style="margin-bottom:0;font-size:.9rem;">GAP coverage should generally pay this, minus common exclusions in your GAP contract: unpaid finance charges, extended warranties rolled into the loan, past-due payments, and often your deductible (some GAP policies cover the deductible up to a small cap, many don\'t). Read the exclusions section before filing.</p>';
			} else if ( hasGap === 'unsure' ) {
				html += '<p style="margin-bottom:0;font-size:.9rem;">Check before you assume you owe this. GAP is often sold inside the loan or lease contract under another name, such as "GAP waiver", "debt cancellation" or "guaranteed asset protection", and many leases include it automatically. Look at your finance contract or call the lender and ask whether the loan has GAP.</p>';
			} else {
				html += '<p style="margin-bottom:0;font-size:.9rem;">Without GAP coverage, this shortfall is generally yours to pay to the lender. Ask the lender for a payment plan as soon as you know the settlement amount.</p>';
			}
		}

		MAT.showResult( resultBox, html );
	} );
})();
