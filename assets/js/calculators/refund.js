/**
 * Car Insurance Refund Calculator.
 *
 * Unused premium = premium × (days left in term ÷ days in term).
 * Pro-rata refund returns all of it; short-rate keeps a penalty (a
 * percentage of the unused premium). A flat fee is taken off either.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-rf-form' );
	if ( ! form ) {
		return;
	}
	var resultBox = document.getElementById( 'mat-rf-result' );
	var DAY = 24 * 60 * 60 * 1000;

	function formatUSD( n ) {
		return n.toLocaleString( 'en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2, maximumFractionDigits: 2 } );
	}

	// Parse yyyy-mm-dd as a UTC date so day counts ignore DST changes.
	function parseDate( value ) {
		var parts = ( value || '' ).split( '-' );
		if ( parts.length !== 3 ) {
			return null;
		}
		var d = Date.UTC( +parts[0], +parts[1] - 1, +parts[2] );
		return isNaN( d ) ? null : d;
	}

	function addMonths( utc, months ) {
		var d = new Date( utc );
		var day = d.getUTCDate();
		d.setUTCDate( 1 );
		d.setUTCMonth( d.getUTCMonth() + months );
		var lastDay = new Date( Date.UTC( d.getUTCFullYear(), d.getUTCMonth() + 1, 0 ) ).getUTCDate();
		d.setUTCDate( Math.min( day, lastDay ) );
		return d.getTime();
	}

	function showError( msg ) {
		resultBox.innerHTML = '<p role="alert">' + msg + '</p>';
		resultBox.hidden = false;
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();

		var premium = parseFloat( document.getElementById( 'mat-rf-premium' ).value );
		var term = parseInt( document.getElementById( 'mat-rf-term' ).value, 10 ) || 12;
		var start = parseDate( document.getElementById( 'mat-rf-start' ).value );
		var cancel = parseDate( document.getElementById( 'mat-rf-cancel' ).value );
		var method = document.getElementById( 'mat-rf-method' ).value;
		var penalty = Math.min( Math.max( parseFloat( document.getElementById( 'mat-rf-penalty' ).value ) || 0, 0 ), 50 ) / 100;
		var fee = Math.max( parseFloat( document.getElementById( 'mat-rf-fee' ).value ) || 0, 0 );

		if ( isNaN( premium ) || premium <= 0 ) {
			showError( 'Please enter the premium you paid for the policy term.' );
			return;
		}
		if ( start === null || cancel === null ) {
			showError( 'Please enter both the policy start date and the cancellation date.' );
			return;
		}

		var end = addMonths( start, term );
		if ( cancel < start ) {
			showError( 'The cancellation date is before the policy started. If the policy never took effect, ask your insurer for a full refund.' );
			return;
		}
		if ( cancel >= end ) {
			showError( 'That cancellation date is on or after the end of the term, so there is no unused premium to refund.' );
			return;
		}

		var totalDays = Math.round( ( end - start ) / DAY );
		var usedDays = Math.round( ( cancel - start ) / DAY );
		var leftDays = totalDays - usedDays;
		var unused = premium * leftDays / totalDays;

		var proRata = Math.max( unused - fee, 0 );
		var shortRate = Math.max( unused * ( 1 - penalty ) - fee, 0 );

		var html = '';
		if ( method === 'shortrate' ) {
			html += '<p class="mat-result-box__figure">' + formatUSD( shortRate ) + '</p>';
			html += '<p>Estimated short-rate refund: ' + formatUSD( unused ) + ' unused premium, minus a ' + Math.round( penalty * 100 ) + '% penalty' + ( fee ? ' and a ' + formatUSD( fee ) + ' fee' : '' ) + '.</p>';
		} else if ( method === 'both' ) {
			html += '<p class="mat-result-box__figure">' + formatUSD( shortRate ) + ' – ' + formatUSD( proRata ) + '</p>';
			html += '<p><strong>Pro-rata:</strong> ' + formatUSD( proRata ) + '<br><strong>Short-rate (' + Math.round( penalty * 100 ) + '% penalty):</strong> ' + formatUSD( shortRate ) + '</p>';
		} else {
			html += '<p class="mat-result-box__figure">' + formatUSD( proRata ) + '</p>';
			html += '<p>Estimated pro-rata refund: the full unused premium' + ( fee ? ', minus a ' + formatUSD( fee ) + ' fee' : '' ) + '.</p>';
		}
		html += '<p style="margin-bottom:0;font-size:.9rem;">You used ' + usedDays + ' of ' + totalDays + ' days (' + leftDays + ' days left), so ' + formatUSD( unused ) + ' of your ' + formatUSD( premium ) + ' premium is unused.</p>';

		resultBox.innerHTML = html;
		resultBox.hidden = false;
		resultBox.setAttribute( 'tabindex', '-1' );
		resultBox.focus();
	} );
})();
