/**
 * Claim Outcome Survey: check the answers, then send them to the
 * mat/v1/outcome endpoint. The time spent on the form goes along so the
 * server can turn away instant bot posts.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-out-form' );
	if ( ! form ) {
		return;
	}
	var started = Date.now();
	var errorBox = document.getElementById( 'mat-out-error' );
	var thanks = document.getElementById( 'mat-out-thanks' );
	var button = form.querySelector( 'button[type="submit"]' );

	function money( v ) {
		var n = parseFloat( String( v ).replace( /[$,\s]/g, '' ) );
		return isFinite( n ) ? n : NaN;
	}

	function fail( msg, el ) {
		errorBox.textContent = msg;
		errorBox.hidden = false;
		if ( el ) {
			el.setAttribute( 'aria-invalid', 'true' );
			el.focus();
		}
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		errorBox.hidden = true;
		Array.prototype.forEach.call( form.querySelectorAll( '[aria-invalid]' ), function ( el ) {
			el.removeAttribute( 'aria-invalid' );
		} );

		var f = form.elements;
		var required = [ [ f.claim_type, 'Please choose the kind of claim.' ], [ f.party, 'Please choose whose insurer paid.' ] ];
		for ( var i = 0; i < required.length; i++ ) {
			if ( ! required[ i ][ 0 ].value ) {
				return fail( required[ i ][ 1 ], required[ i ][ 0 ] );
			}
		}
		var first = money( f.first_offer.value );
		var final = money( f.final_amount.value );
		if ( ! ( first >= 1 && first <= 500000 ) ) {
			return fail( 'Please enter the first offer in dollars, for example 11200.', f.first_offer );
		}
		if ( ! ( final >= 0 && final <= 500000 ) ) {
			return fail( 'Please enter the final amount in dollars, for example 13450.', f.final_amount );
		}
		if ( ! f.status.value ) {
			return fail( 'Please say whether the claim is settled.', f.status );
		}

		var steps = Array.prototype.filter.call( form.querySelectorAll( 'input[name="steps"]' ), function ( c ) {
			return c.checked;
		} ).map( function ( c ) {
			return c.value;
		} );
		var payload = {
			claim_type: f.claim_type.value,
			party: f.party.value,
			first_offer: first,
			final_amount: final,
			status: f.status.value,
			steps: steps,
			insurer: f.insurer.value,
			state: f.state.value,
			website: f.website.value,
			elapsed: Date.now() - started
		};

		button.disabled = true;
		fetch( form.getAttribute( 'data-endpoint' ), {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			credentials: 'omit',
			body: JSON.stringify( payload )
		} ).then( function ( r ) {
			return r.json().catch( function () {
				return { ok: false };
			} );
		} ).then( function ( res ) {
			button.disabled = false;
			if ( ! res || ! res.ok ) {
				return fail( ( res && res.message ) || 'Sorry, that didn\'t send. Please try again in a minute.' );
			}
			var change = final - first;
			var what = payload.status === 'open' ? 'latest offer' : 'final amount';
			var title = change > 0
				? 'Your ' + what + ' is ' + Math.round( change / first * 100 ) + '% above the first offer.'
				: ( change < 0 ? 'Your ' + what + ' is below the first offer.' : 'Your ' + what + ' matches the first offer.' );
			document.getElementById( 'mat-out-thanks-title' ).textContent = title;
			form.hidden = true;
			thanks.hidden = false;
			thanks.focus();
		} ).catch( function () {
			button.disabled = false;
			fail( 'Sorry, that didn\'t send. Check your connection and try again.' );
		} );
	} );
})();
