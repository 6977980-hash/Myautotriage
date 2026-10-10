/**
 * Car Insurance Refund Calculator.
 *
 * Unused premium = premium × (days left in term ÷ days in term).
 * Pro-rata refund returns all of it; short-rate keeps a penalty (a
 * percentage of the unused premium). A flat fee is taken off either.
 *
 * Texas bans short-rate on personal auto from Sept 1, 2026, and Florida
 * caps it at 10% of the unused premium unless the insurer justifies more.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-rf-form' );
	if ( ! form ) {
		return;
	}
	var resultBox = document.getElementById( 'mat-rf-result' );
	var DAY = 24 * 60 * 60 * 1000;
	var TX_PRO_RATA_FROM = Date.UTC( 2026, 8, 1 );

	function formatUSD( n ) {
		return MAT.usd( n, true );
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

		var r = MAT.nums( {
			premium: [ 'mat-rf-premium', { label: 'The premium you paid', required: true } ],
			penalty: [ 'mat-rf-penalty', { label: 'The short-rate penalty', fallback: 0 } ],
			fee: [ 'mat-rf-fee', { label: 'The cancellation fee', fallback: 0 } ],
		} );
		if ( r.error ) {
			MAT.showError( resultBox, r );
			return;
		}
		var premium = r.values.premium;
		var penalty = r.values.penalty / 100;
		var fee = r.values.fee;
		var term = parseInt( document.getElementById( 'mat-rf-term' ).value, 10 ) || 12;
		var start = parseDate( document.getElementById( 'mat-rf-start' ).value );
		var cancel = parseDate( document.getElementById( 'mat-rf-cancel' ).value );
		var method = document.getElementById( 'mat-rf-method' ).value;
		var stateEl = document.getElementById( 'mat-rf-state' );
		var state = stateEl ? stateEl.value : '';

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

		var stateNote = '';
		if ( state === 'TX' && cancel >= TX_PRO_RATA_FROM ) {
			if ( method !== 'prorata' ) {
				method = 'prorata';
			}
			stateNote = 'Texas: for personal auto policies cancelled on or after September 1, 2026 the refund must be the full unearned premium, calculated pro rata, so a short-rate penalty is not allowed (28 TAC § 5.7015). The insurer may still keep a minimum retained premium that is in its filed rates; enter that as the flat fee.';
		} else if ( state === 'TX' ) {
			stateNote = 'Texas: the pro-rata requirement (28 TAC § 5.7015) applies to cancellations on or after September 1, 2026. Before that date your policy\'s cancellation clause decides.';
		} else if ( state === 'FL' && method !== 'prorata' && penalty > 0.1 ) {
			stateNote = 'Florida: a short-rate refund below 90% of the pro-rata amount (a penalty above 10%) is prohibited unless the insurer has filed justification (Fla. Admin. Code R. 69O-170.010). If your insurer keeps more than 10%, ask it to show the filed justification.';
		} else if ( state === 'FL' ) {
			stateNote = 'Florida: a short-rate penalty above 10% of the unused premium is prohibited unless the insurer has filed justification (Fla. Admin. Code R. 69O-170.010).';
		}

		var proRata = Math.max( unused - fee, 0 );
		var shortRate = Math.max( unused * ( 1 - penalty ) - fee, 0 );

		var html = '';
		if ( method === 'shortrate' ) {
			html += '<p class="mat-result-box__figure">' + formatUSD( shortRate ) + '</p>';
			html += '<p>Estimated short-rate refund: ' + formatUSD( unused ) + ' unused premium, minus a ' + MAT.pct( penalty ) + ' penalty' + ( fee ? ' and a ' + formatUSD( fee ) + ' fee' : '' ) + '.</p>';
		} else if ( method === 'both' ) {
			html += '<p class="mat-result-box__figure">' + formatUSD( shortRate ) + ' – ' + formatUSD( proRata ) + '</p>';
			html += '<p><strong>Pro-rata:</strong> ' + formatUSD( proRata ) + '<br><strong>Short-rate (' + MAT.pct( penalty ) + ' penalty):</strong> ' + formatUSD( shortRate ) + '</p>';
		} else {
			html += '<p class="mat-result-box__figure">' + formatUSD( proRata ) + '</p>';
			html += '<p>Estimated pro-rata refund: the full unused premium' + ( fee ? ', minus a ' + formatUSD( fee ) + ' fee' : '' ) + '.</p>';
		}
		html += '<p style="margin-bottom:0;font-size:.9rem;">You used ' + usedDays + ' of ' + totalDays + ' days (' + leftDays + ' days left), so ' + formatUSD( unused ) + ' of your ' + formatUSD( premium ) + ' premium is unused.</p>';

		if ( stateNote ) {
			html += '<p style="font-size:.9rem;">' + MAT.escape( stateNote ) + '</p>';
		}
		MAT.showResult( resultBox, html );
	} );
})();
