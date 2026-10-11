/**
 * Rental Extension Letter: count the extra days between the insurer's
 * cutoff and the date the car should be ready, check them against the
 * policy's day limit on the visitor's own coverage, and write the letter.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-re-form' );
	if ( ! form ) {
		return;
	}
	var errorBox = document.getElementById( 'mat-re-error' );
	var wrap = document.getElementById( 'mat-re-preview-wrap' );
	var summary = document.getElementById( 'mat-re-summary' );
	var preview = document.getElementById( 'mat-re-preview' );
	var ownRow = form.querySelector( '.mat-re-own' );
	var DAY = 24 * 60 * 60 * 1000;

	var REASONS = {
		parts: 'The repairs are waiting on parts that are on back order. The delay is caused by parts availability, not by me.',
		supplement: 'The shop found additional damage and is waiting for your approval of the supplement before it can continue. The car cannot be finished until that approval is given.',
		inspection: 'The vehicle has not yet been inspected by your company, so repairs could not be authorized or started.',
		totalloss: 'Your company has not yet decided whether the vehicle is a total loss, so I can neither have it repaired nor replace it.',
		payment: 'The vehicle has been declared a total loss, but I have not yet received a reasonable settlement offer or payment that would allow me to replace it.',
		repairs: 'The repairs are taking longer than the shop first estimated. The additional time is needed to complete the repairs properly, and none of the delay was caused by me.'
	};

	function party() {
		var checked = form.querySelector( 'input[name="mat-re-party"]:checked' );
		return checked ? checked.value : 'other';
	}

	function parseDate( value ) {
		var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec( value || '' );
		return m ? Date.UTC( +m[1], +m[2] - 1, +m[3] ) : null;
	}

	function val( id ) {
		var el = document.getElementById( id );
		return el ? el.value.trim() : '';
	}

	function fail( message, el ) {
		errorBox.textContent = message;
		errorBox.hidden = false;
		if ( el ) {
			el.setAttribute( 'aria-invalid', 'true' );
			el.focus();
		}
	}

	function days( n ) {
		return n + ( n === 1 ? ' day' : ' days' );
	}

	Array.prototype.forEach.call( form.querySelectorAll( 'input[name="mat-re-party"]' ), function ( r ) {
		r.addEventListener( 'change', function () {
			ownRow.hidden = party() !== 'own';
		} );
	} );

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		Array.prototype.forEach.call( form.querySelectorAll( '[aria-invalid]' ), function ( el ) {
			el.removeAttribute( 'aria-invalid' );
		} );
		var endEl = document.getElementById( 'mat-re-end' );
		var readyEl = document.getElementById( 'mat-re-ready' );
		var end = parseDate( endEl.value );
		var ready = parseDate( readyEl.value );
		if ( end === null ) {
			return fail( 'Please enter the date the insurer says the rental ends.', endEl );
		}
		if ( ready === null ) {
			return fail( 'Please enter the date the car should be ready.', readyEl );
		}
		if ( ready <= end ) {
			return fail( 'The car is due back on or before the rental end date, so there is nothing to extend. Check both dates.', readyEl );
		}
		errorBox.hidden = true;

		var who = party();
		var extra = Math.round( ( ready - end ) / DAY );
		var name = val( 'mat-re-name' ) || '[Your name]';
		var claim = val( 'mat-re-claim' ) || '[claim number]';
		var adjuster = val( 'mat-re-adjuster' );
		var shop = val( 'mat-re-shop' ) || 'the repair shop';
		var reason = document.getElementById( 'mat-re-reason' ).value;

		var note = '';
		if ( who === 'own' ) {
			var cap = parseInt( val( 'mat-re-cap-days' ), 10 );
			var start = parseDate( val( 'mat-re-start' ) );
			if ( cap > 0 && start !== null && start < ready ) {
				var needed = Math.round( ( ready - start ) / DAY );
				if ( needed > cap ) {
					note = '<p>Your policy covers up to ' + days( cap ) + ' and you need ' + days( needed ) + ' in total, so about ' + days( needed - cap ) + ' won\'t be covered by your own policy. If another driver caused the accident, claim those days from their insurer as loss of use.</p>';
				} else {
					note = '<p>You need ' + days( needed ) + ' in total, within your policy\'s limit of ' + days( cap ) + ', so the extension should fit your coverage.</p>';
				}
			} else {
				note = '<p>Check the daily and total limits on your declarations page; your own rental coverage stops there.</p>';
			}
		}
		summary.innerHTML = '<p class="mat-result-box__figure">' + days( extra ) + '</p>'
			+ '<p>That is the extension to ask for: from ' + MAT.longDate( endEl.value ) + ' to ' + MAT.longDate( readyEl.value ) + '.</p>' + note;

		var lines = [];
		lines.push( new Date().toLocaleDateString( 'en-US', { year: 'numeric', month: 'long', day: 'numeric' } ) );
		lines.push( '' );
		lines.push( 'Re: Request to extend rental, claim ' + claim );
		lines.push( '' );
		lines.push( 'Dear ' + ( adjuster || 'Claims Adjuster' ) + ',' );
		lines.push( '' );
		lines.push( 'I was told that the rental vehicle on this claim will end on ' + MAT.longDate( endEl.value ) + '. My car is still at ' + shop + ' and is not expected to be ready until ' + MAT.longDate( readyEl.value ) + '.' );
		lines.push( '' );
		lines.push( REASONS[ reason ] );
		lines.push( '' );
		if ( who === 'other' ) {
			lines.push( 'As your insured caused this accident, your company is responsible for my loss of use for the time reasonably needed to repair or replace my vehicle. That period has not ended. Please extend the rental by ' + days( extra ) + ', through ' + MAT.longDate( readyEl.value ) + ', or until the repairs are complete if that is sooner.' );
		} else {
			lines.push( 'Please extend the rental under my policy\'s rental reimbursement coverage by ' + days( extra ) + ', through ' + MAT.longDate( readyEl.value ) + ', or until the repairs are complete if that is sooner, up to the limits of my coverage. If you believe my coverage runs out before then, please tell me the date and the limit you are applying.' );
		}
		lines.push( '' );
		lines.push( 'If you will not extend the rental, please tell me in writing before ' + MAT.longDate( endEl.value ) + ' the reason, and the date you consider reasonable for the repairs and why. I am keeping a record of all costs I incur from being without my car.' );
		lines.push( '' );
		lines.push( 'Thank you,' );
		lines.push( name );
		preview.textContent = lines.join( '\n' );
		wrap.hidden = false;
		summary.setAttribute( 'tabindex', '-1' );
		summary.focus();
	} );

	document.getElementById( 'mat-re-print' ).addEventListener( 'click', function () {
		window.print();
	} );
	var copyBtn = document.getElementById( 'mat-re-copy' );
	copyBtn.addEventListener( 'click', function () {
		var done = function () {
			copyBtn.textContent = 'Copied!';
			setTimeout( function () { copyBtn.textContent = 'Copy text'; }, 1800 );
		};
		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( preview.textContent ).then( done, function () {
				window.prompt( 'Copy this letter:', preview.textContent );
			} );
		} else {
			window.prompt( 'Copy this letter:', preview.textContent );
		}
	} );
})();
