/**
 * Late Claim Payment Interest Calculator.
 *
 * Simple interest = amount x annual rate x days late / 365.
 * Texas (18%) and Michigan (12%) rates are built in; anything else uses
 * the rate entered.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-li-form' );
	if ( ! form ) {
		return;
	}
	var resultBox = document.getElementById( 'mat-li-result' );
	var errorBox = document.getElementById( 'mat-li-error' );
	var ruleEl = document.getElementById( 'mat-li-rule' );
	var rateEl = document.getElementById( 'mat-li-rate' );
	var deadlineUrl = form.getAttribute( 'data-deadline-url' );
	var DAY = 24 * 60 * 60 * 1000;

	var RULES = {
		TX: {
			rate: 18,
			note: 'Texas: 18% a year as damages, plus reasonable attorney\'s fees, when the insurer misses a prompt-payment deadline on a claim by the policyholder or a beneficiary (Tex. Ins. Code § 542.060). Weather-related property claims under Chapter 542A use a lower rate.',
		},
		MI: {
			rate: 12,
			note: 'Michigan: 12% simple interest a year from 60 days after the insurer received satisfactory proof of loss (MCL § 500.2006). Enter that date as the due date. Third-party claimants get it only if a court finds the insurer refused payment in bad faith.',
		},
	};

	function syncRate() {
		var rule = RULES[ ruleEl.value ];
		if ( rule ) {
			rateEl.value = rule.rate;
			rateEl.readOnly = true;
		} else {
			rateEl.readOnly = false;
		}
	}
	ruleEl.addEventListener( 'change', syncRate );
	syncRate();

	function parseDate( value ) {
		var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec( value || '' );
		return m ? Date.UTC( +m[1], +m[2] - 1, +m[3] ) : null;
	}

	function today() {
		var n = new Date();
		return Date.UTC( n.getFullYear(), n.getMonth(), n.getDate() );
	}

	function fail( message, el ) {
		errorBox.textContent = message;
		errorBox.hidden = false;
		el.setAttribute( 'aria-invalid', 'true' );
		el.focus();
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		syncRate();
		var r = MAT.nums( {
			amount: [ 'mat-li-amount', { label: 'The claim amount', required: true } ],
			rate: [ 'mat-li-rate', { label: 'The interest rate', required: true } ],
		} );
		if ( r.error ) {
			fail( r.error, r.field );
			return;
		}
		var dueEl = document.getElementById( 'mat-li-due' );
		var paidEl = document.getElementById( 'mat-li-paid' );
		dueEl.removeAttribute( 'aria-invalid' );
		paidEl.removeAttribute( 'aria-invalid' );
		var due = parseDate( dueEl.value );
		var paid = paidEl.value ? parseDate( paidEl.value ) : today();
		if ( due === null ) {
			fail( 'Please enter the date payment was due.', dueEl );
			return;
		}
		if ( paid === null ) {
			fail( 'Please enter a valid payment date, or leave it blank.', paidEl );
			return;
		}
		errorBox.hidden = true;

		var days = Math.round( ( paid - due ) / DAY );
		var amount = r.values.amount;
		var rate = r.values.rate;
		var html;
		if ( days <= 0 ) {
			html = '<p class="mat-result-box__figure">' + MAT.usd( 0 ) + '</p><p>' + ( paidEl.value ? 'The claim was paid on or before the due date, so no late-payment interest applies.' : 'The due date hasn\'t passed yet.' ) + '</p>';
		} else {
			var interest = amount * ( rate / 100 ) * days / 365;
			html = '<p class="mat-result-box__figure">' + MAT.usd( interest, true ) + ' interest</p>';
			html += '<table class="mat-result-table"><tbody>';
			html += '<tr><td>Days late' + ( paidEl.value ? '' : ' (to today, still unpaid)' ) + '</td><td>' + days + '</td></tr>';
			html += '<tr><td>' + MAT.usd( amount, true ) + ' × ' + rate + '% × ' + days + '/365</td><td>' + MAT.usd( interest, true ) + '</td></tr>';
			if ( ! paidEl.value ) {
				html += '<tr><td>Adds per day until paid</td><td>' + MAT.usd( amount * ( rate / 100 ) / 365, true ) + '</td></tr>';
			}
			html += '<tr class="mat-result-table__total"><td>Claim plus interest</td><td>' + MAT.usd( amount + interest, true ) + '</td></tr>';
			html += '</tbody></table>';
		}
		var rule = RULES[ ruleEl.value ];
		if ( rule ) {
			html += '<p style="font-size:.9rem;">' + MAT.escape( rule.note ) + '</p>';
		} else {
			html += '<p style="font-size:.9rem;">Uses the rate you entered. Check your state\'s statute for the correct rate and when interest starts.</p>';
		}
		if ( deadlineUrl ) {
			html += '<p style="margin-bottom:0;"><a href="' + MAT.escape( deadlineUrl ) + '">Not sure when payment was due? Look up your state\'s claim deadlines</a></p>';
		}
		MAT.showResult( resultBox, html );
	} );
})();
