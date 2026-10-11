/**
 * Deductible Recovery Calculator.
 *
 * Expected refund = deductible x (1 - your fault share) x share collected.
 * The stage advice depends on how long ago the insurer paid the claim.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-dr-form' );
	if ( ! form ) {
		return;
	}
	var resultBox = document.getElementById( 'mat-dr-result' );
	var errorBox = document.getElementById( 'mat-dr-error' );
	var wrap = document.getElementById( 'mat-dr-preview-wrap' );
	var preview = document.getElementById( 'mat-dr-preview' );
	var DAY = 24 * 60 * 60 * 1000;

	function parseDate( value ) {
		var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec( value || '' );
		return m ? Date.UTC( +m[1], +m[2] - 1, +m[3] ) : null;
	}

	function val( id ) {
		return document.getElementById( id ).value.trim();
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
		var paidEl = document.getElementById( 'mat-dr-paid' );
		paidEl.removeAttribute( 'aria-invalid' );
		var r = MAT.nums( {
			deductible: [ 'mat-dr-deductible', { label: 'The deductible', required: true } ],
			fault: [ 'mat-dr-fault', { label: 'Your share of the fault', fallback: 0 } ],
			collected: [ 'mat-dr-collected', { label: 'The share collected', fallback: 100 } ]
		} );
		if ( r.error ) {
			fail( r.error, null );
			r.field.focus();
			return;
		}
		var paid = parseDate( paidEl.value );
		var now = new Date();
		var today = Date.UTC( now.getFullYear(), now.getMonth(), now.getDate() );
		if ( paid === null ) {
			return fail( 'Please enter the date your insurer paid the claim.', paidEl );
		}
		if ( paid > today ) {
			return fail( 'The payment date can\'t be in the future.', paidEl );
		}
		errorBox.hidden = true;
		var v = r.values;
		if ( v.fault >= 100 ) {
			MAT.showResult( resultBox, '<p class="mat-result-box__figure">' + MAT.usd( 0 ) + '</p><p>If you were fully at fault, there is no one to recover your deductible from.</p>' );
			wrap.hidden = true;
			return;
		}
		var refund = v.deductible * ( 1 - v.fault / 100 ) * ( v.collected / 100 );
		var elapsed = Math.round( ( today - paid ) / DAY );

		var stage;
		if ( elapsed < 45 ) {
			stage = '<strong>Early stage (' + elapsed + ' days).</strong> Your insurer is usually still sending its demand to the other insurer. Now is the time to ask in writing that your deductible be included.';
		} else if ( elapsed < 120 ) {
			stage = '<strong>Ask for a status update (' + elapsed + ' days).</strong> Many clear-liability recoveries are paid by now. Ask whether the demand included your deductible, whether the other insurer accepted fault, and when they expect payment.';
		} else {
			stage = '<strong>Push for an answer (' + elapsed + ' days).</strong> Ask whether the recovery is in arbitration, has been settled for less, or has been dropped. If they aren\'t pursuing it, get that in writing and claim the deductible from the other driver\'s insurer yourself, before your state\'s lawsuit deadline for property damage.';
		}

		var html = '<p class="mat-result-box__figure">' + MAT.usd( refund ) + '</p>';
		html += '<p>Expected back from your ' + MAT.usd( v.deductible ) + ' deductible.</p>';
		html += '<table class="mat-result-table"><tbody>';
		html += '<tr><td>Deductible paid</td><td>' + MAT.usd( v.deductible ) + '</td></tr>';
		if ( v.fault ) {
			html += '<tr><td>Less your ' + v.fault + '% share of fault</td><td>&minus;' + MAT.usd( v.deductible * v.fault / 100 ) + '</td></tr>';
		}
		if ( v.collected < 100 ) {
			html += '<tr><td>Times the ' + v.collected + '% your insurer collected</td><td>&minus;' + MAT.usd( v.deductible * ( 1 - v.fault / 100 ) * ( 1 - v.collected / 100 ) ) + '</td></tr>';
		}
		html += '<tr class="mat-result-table__total"><td>Expected refund</td><td>' + MAT.usd( refund ) + '</td></tr>';
		html += '</tbody></table>';
		if ( v.fault >= 50 ) {
			html += '<p>At ' + v.fault + '% fault, many states bar recovery from the other driver entirely, and in a few states any fault at all does. Check your state\'s fault rule before counting on this.</p>';
		} else if ( v.fault > 0 ) {
			html += '<p>A handful of states bar any recovery when you share even a little of the fault. Check your state\'s fault rule.</p>';
		}
		html += '<p>' + stage + '</p>';
		MAT.showResult( resultBox, html );

		var claim = val( 'mat-dr-claim' ) || '[claim number]';
		var lines = [
			new Date().toLocaleDateString( 'en-US', { year: 'numeric', month: 'long', day: 'numeric' } ),
			'',
			'Re: Deductible recovery, claim ' + claim,
			'',
			'Dear Claims Department,',
			'',
			'I paid a ' + MAT.usd( v.deductible ) + ' deductible on this claim, and you paid the claim on ' + MAT.longDate( paidEl.value ) + '. ' + ( v.fault ? 'I understand fault may be shared, but the other driver was primarily responsible.' : 'The other driver was at fault for this accident.' ),
			'',
			'Please include my deductible in your subrogation demand against the at-fault party\'s insurer, and share any recovery with me on a proportionate basis, without deducting your expenses from my share unless an outside attorney is retained.',
			'',
			'Please also tell me in writing:',
			'1. Whether a subrogation demand has been sent, and whether it includes my deductible.',
			'2. Whether the other insurer has accepted liability, and at what percentage.',
			'3. Whether the claim is in inter-company arbitration, and if so its expected timeline.',
			'4. If you decide not to pursue recovery, or settle for less than the full amount, the date and reason, so that I can pursue my deductible directly before any deadline passes.',
			'',
			'Thank you,',
			val( 'mat-dr-name' ) || '[Your name]'
		];
		preview.textContent = lines.join( '\n' );
		wrap.hidden = false;
	} );

	document.getElementById( 'mat-dr-print' ).addEventListener( 'click', function () {
		window.print();
	} );
	var copyBtn = document.getElementById( 'mat-dr-copy' );
	copyBtn.addEventListener( 'click', function () {
		var done = function () {
			copyBtn.textContent = 'Copied!';
			setTimeout( function () { copyBtn.textContent = 'Copy text'; }, 1800 );
		};
		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( preview.textContent ).then( done, function () {
				window.prompt( 'Copy this message:', preview.textContent );
			} );
		} else {
			window.prompt( 'Copy this message:', preview.textContent );
		}
	} );
})();
