/**
 * Loss of Use Calculator.
 *
 * Claim = (days without the car - rental days already paid) x daily rate
 *         + other transport costs.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-lu-form' );
	if ( ! form ) {
		return;
	}
	var resultBox = document.getElementById( 'mat-lu-result' );
	var errorBox = document.getElementById( 'mat-lu-error' );
	var letterUrl = form.getAttribute( 'data-letter-url' );
	var DAY = 24 * 60 * 60 * 1000;

	function parseDate( value ) {
		var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec( value || '' );
		return m ? Date.UTC( +m[1], +m[2] - 1, +m[3] ) : null;
	}

	function fail( message, el ) {
		errorBox.textContent = message;
		errorBox.hidden = false;
		if ( el ) {
			el.setAttribute( 'aria-invalid', 'true' );
			el.focus();
		}
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		var fromEl = document.getElementById( 'mat-lu-from' );
		var toEl = document.getElementById( 'mat-lu-to' );
		fromEl.removeAttribute( 'aria-invalid' );
		toEl.removeAttribute( 'aria-invalid' );
		var from = parseDate( fromEl.value );
		var to = parseDate( toEl.value );
		if ( from === null ) {
			fail( 'Please enter the first day without your car.', fromEl );
			return;
		}
		if ( to === null ) {
			fail( 'Please enter the day you got the car back.', toEl );
			return;
		}
		if ( to <= from ) {
			fail( 'The day you got the car back must be after the first day without it.', toEl );
			return;
		}
		var r = MAT.nums( {
			rate: [ 'mat-lu-rate', { label: 'The daily rental rate', required: true } ],
			paid: [ 'mat-lu-paid', { label: 'Days already paid', fallback: 0, integer: true } ],
			extra: [ 'mat-lu-extra', { label: 'Other costs', fallback: 0 } ],
		} );
		if ( r.error ) {
			fail( r.error, null );
			r.field.focus();
			return;
		}
		errorBox.hidden = true;
		var v = r.values;
		var days = Math.round( ( to - from ) / DAY );
		var unpaid = Math.max( 0, days - v.paid );
		var claim = unpaid * v.rate + v.extra;

		var html = '<p class="mat-result-box__figure">' + MAT.usd( claim, true ) + '</p>';
		html += '<table class="mat-result-table"><tbody>';
		html += '<tr><td>Days without your car</td><td>' + days + '</td></tr>';
		if ( v.paid ) {
			html += '<tr><td>Rental days already paid by the insurer</td><td>&minus;' + Math.min( v.paid, days ) + '</td></tr>';
		}
		html += '<tr><td>' + unpaid + ' days × ' + MAT.usd( v.rate, true ) + '</td><td>' + MAT.usd( unpaid * v.rate, true ) + '</td></tr>';
		if ( v.extra ) {
			html += '<tr><td>Other transport costs</td><td>' + MAT.usd( v.extra, true ) + '</td></tr>';
		}
		html += '<tr class="mat-result-table__total"><td>Loss of use to claim</td><td>' + MAT.usd( claim, true ) + '</td></tr>';
		html += '</tbody></table>';
		if ( v.paid > days ) {
			html += '<p style="font-size:.9rem;">The insurer paid for more rental days than you were without the car, so there is nothing more to claim for the rental itself.</p>';
		}
		if ( days > 30 ) {
			html += '<p style="font-size:.9rem;">A long period like this is often questioned. Keep a dated record of why the repair or settlement took so long; delays caused by the insurer or by parts shortages still count.</p>';
		}
		if ( letterUrl ) {
			html += '<p style="margin-bottom:0;"><a href="' + MAT.escape( letterUrl ) + '">Add this to a property damage demand letter</a></p>';
		}
		MAT.showResult( resultBox, html );
	} );
})();
