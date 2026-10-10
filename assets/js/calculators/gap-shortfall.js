/**
 * GAP Insurance Shortfall Calculator.
 *
 * Shortfall = Loan/Lease Payoff Balance - (Insurer ACV Settlement - Deductible)
 *
 * With the optional contract details it also splits the shortfall into
 * what GAP should pay and what usually stays with the borrower: past-due
 * amounts, refunds on cancelled add-ons, loan above the GAP contract's
 * loan-to-value cap, and the part of the deductible GAP doesn't cover.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-gap-form' );
	if ( ! form ) {
		return;
	}
	var resultBox = document.getElementById( 'mat-gap-result' );
	var usd = MAT.usd;

	/**
	 * Split the shortfall into what GAP should pay and what is left for the
	 * borrower, using the contract details entered.
	 */
	function itemise( v, insurerPayout ) {
		var excluded = Math.min( v.payoff, v.pastDue + v.addOns );
		var eligible = v.payoff - excluded;
		var overCap = 0;
		if ( v.ltv !== null ) {
			var capLimit = v.acv * v.ltv / 100;
			overCap = Math.max( 0, eligible - capLimit );
		}
		var covered = eligible - overCap;
		var dedCovered = v.dedCap === null ? 0 : Math.min( v.deductible, v.dedCap );
		var gapPays = Math.max( 0, covered - v.acv ) + ( covered > v.acv ? dedCovered : 0 );
		var youOwe = Math.max( 0, v.payoff - insurerPayout - gapPays );
		var dedLeft = covered > v.acv ? v.deductible - dedCovered : 0;

		var rows = [];
		rows.push( [ 'Loan payoff', usd( v.payoff ) ] );
		rows.push( [ 'Insurer pays the lender', '&minus;' + usd( insurerPayout ) ] );
		rows.push( [ 'GAP should pay', '&minus;' + usd( Math.round( gapPays ) ) ] );
		var html = '<table class="mat-result-table"><tbody>';
		rows.forEach( function ( r ) {
			html += '<tr><td>' + r[0] + '</td><td>' + r[1] + '</td></tr>';
		} );
		html += '<tr class="mat-result-table__total"><td>Left for you to pay</td><td>' + usd( Math.round( youOwe ) ) + '</td></tr>';
		html += '</tbody></table>';

		var why = [];
		if ( v.pastDue > 0 ) {
			why.push( 'Past-due payments and late fees (' + usd( v.pastDue ) + '): GAP contracts exclude them, so bring the loan current if you can.' );
		}
		if ( v.addOns > 0 ) {
			why.push( 'Add-on refunds (' + usd( v.addOns ) + '): GAP subtracts what you get back for cancelling the warranty or service contract. Cancel them right away; the refund normally goes to the lender and lowers the balance.' );
		}
		if ( overCap > 0 ) {
			why.push( 'Loan above the GAP limit (' + usd( Math.round( overCap ) ) + '): your contract covers the loan only up to ' + v.ltv + '% of the car\'s value (' + usd( Math.round( v.acv * v.ltv / 100 ) ) + '). This often comes from negative equity rolled in from a previous car.' );
		}
		if ( dedLeft > 0 ) {
			why.push( 'Deductible not covered by GAP (' + usd( dedLeft ) + ').' );
		}
		if ( why.length ) {
			html += '<p style="margin-bottom:.25rem;"><strong>Why GAP may not pay it all:</strong></p><ul style="font-size:.9rem;">';
			why.forEach( function ( w ) {
				html += '<li>' + w + '</li>';
			} );
			html += '</ul>';
		}
		html += '<p style="margin-bottom:0;font-size:.9rem;">If the GAP payment is lower than this, ask the GAP administrator for a written breakdown of every deduction. A higher actual cash value from your insurer also lowers what is left for you, so check the valuation first.</p>';
		return html;
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();

		var r = MAT.nums( {
			payoff: [ 'mat-gap-payoff', { label: 'Your loan payoff balance', required: true } ],
			acv: [ 'mat-gap-acv', { label: 'The insurer\'s settlement value', required: true } ],
			deductible: [ 'mat-gap-deductible', { label: 'Your deductible', fallback: 0 } ],
			pastDue: [ 'mat-gap-pastdue', { label: 'Past-due payments and fees', fallback: 0 } ],
			addOns: [ 'mat-gap-addons', { label: 'Add-on refunds', fallback: 0 } ],
			ltv: [ 'mat-gap-ltv', { label: 'The GAP limit', fallback: null } ],
			dedCap: [ 'mat-gap-dedcap', { label: 'The deductible GAP covers', fallback: null } ],
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
			var detailed = v.pastDue > 0 || v.addOns > 0 || v.ltv !== null || v.dedCap !== null;
			if ( hasGap === 'yes' && detailed ) {
				html += itemise( v, insurerPayout );
			} else if ( hasGap === 'yes' ) {
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
