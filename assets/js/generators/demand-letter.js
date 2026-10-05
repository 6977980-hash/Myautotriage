/**
 * Auto Insurance Demand Letter Generator.
 *
 * Builds a complete, ready-to-send demand letter from form inputs. Six
 * "type" variants share this one engine (see the opening-paragraph and
 * closing-paragraph templates below) so /demand-letter-generator/?type=...
 * can serve several distinct search intents without duplicating code.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-dl-form' );
	if ( ! form ) {
		return;
	}
	var preview = document.getElementById( 'mat-dl-preview' );
	var previewWrap = document.getElementById( 'mat-dl-preview-wrap' );
	var printBtn = document.getElementById( 'mat-dl-print' );
	var copyBtn = document.getElementById( 'mat-dl-copy' );
	var typeSelect = document.getElementById( 'mat-dl-type' );

	var OPENERS = {
		'general': function ( f ) {
			return 'I am writing regarding the automobile accident that occurred on ' + f.accidentDate + ' at ' + f.accidentLocation + ', involving your insured, ' + f.atFaultName + ', and my vehicle. ' + f.narrative;
		},
		'no-injury': function ( f ) {
			return 'I am writing to demand payment for the property damage I sustained in the automobile accident that occurred on ' + f.accidentDate + ' at ' + f.accidentLocation + '. This claim involves property damage only; I am not asserting any bodily injury as part of this demand. ' + f.narrative;
		},
		'property-damage': function ( f ) {
			return 'This letter is a formal demand for payment of property damage to my vehicle arising from the accident on ' + f.accidentDate + ' at ' + f.accidentLocation + ', caused by your insured, ' + f.atFaultName + '. ' + f.narrative;
		},
		'underpayment': function ( f ) {
			return 'I am writing to dispute the settlement amount offered on claim number ' + f.claimNumber + '. The amount offered does not reasonably reflect the actual cost to repair or replace my vehicle following the accident on ' + f.accidentDate + '. ' + f.narrative;
		},
		'diminished-value': function ( f ) {
			return 'I am writing to demand compensation for the diminished value of my vehicle following the accident on ' + f.accidentDate + ', which was caused by your insured, ' + f.atFaultName + '. Even after full repair, my vehicle is now worth less on the resale market than it was before the accident, and this diminished value is a legitimate component of my property damage claim. ' + f.narrative;
		},
		'small-claims': function ( f ) {
			return 'This letter is a final demand for payment before I file a small claims action. On ' + f.accidentDate + ', at ' + f.accidentLocation + ', your insured, ' + f.atFaultName + ', caused damage to my vehicle. ' + f.narrative;
		},
	};

	var CLOSERS = {
		'general': function ( f ) {
			return 'I am requesting payment in the amount of ' + f.amountFormatted + ' to resolve this claim in full. Please respond in writing within ' + f.deadlineDays + ' days of the date of this letter.';
		},
		'no-injury': function ( f ) {
			return 'I am requesting payment in the amount of ' + f.amountFormatted + ', which reflects the documented cost of repairs (and any related out-of-pocket expenses, such as a rental vehicle). Please respond in writing within ' + f.deadlineDays + ' days of the date of this letter.';
		},
		'property-damage': function ( f ) {
			return 'I am requesting payment of ' + f.amountFormatted + ' for the property damage described above. Supporting documentation is enclosed. Please respond in writing within ' + f.deadlineDays + ' days.';
		},
		'underpayment': function ( f ) {
			return 'Based on the enclosed documentation, I am requesting a revised payment of ' + f.amountFormatted + '. If this claim is not resolved fairly, I am prepared to escalate this dispute, including filing a complaint with my state department of insurance. Please respond within ' + f.deadlineDays + ' days.';
		},
		'diminished-value': function ( f ) {
			return 'I am requesting payment of ' + f.amountFormatted + ' to compensate for this diminished value, in addition to any repair costs already covered. Please respond in writing within ' + f.deadlineDays + ' days of the date of this letter.';
		},
		'small-claims': function ( f ) {
			return 'I am requesting payment of ' + f.amountFormatted + ' within ' + f.deadlineDays + ' days of the date of this letter. If I do not receive payment or a written response by that date, I intend to file a claim in small claims court without further notice.';
		},
	};

	function formatUSD( n ) {
		var num = parseFloat( n );
		if ( isNaN( num ) ) {
			return '$0';
		}
		return num.toLocaleString( 'en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 } );
	}

	function todayFormatted() {
		var d = new Date();
		return d.toLocaleDateString( 'en-US', { year: 'numeric', month: 'long', day: 'numeric' } );
	}

	function buildLetter( f ) {
		var opener = ( OPENERS[ f.type ] || OPENERS.general )( f );
		var closer = ( CLOSERS[ f.type ] || CLOSERS.general )( f );

		var lines = [];
		lines.push( f.yourName );
		lines.push( f.yourAddress );
		lines.push( f.yourContact );
		lines.push( '' );
		lines.push( todayFormatted() );
		lines.push( '' );
		lines.push( f.insurerName );
		lines.push( 'Re: Claim No. ' + ( f.claimNumber || '[Claim number]' ) + ( f.adjusterName ? ' — Attn: ' + f.adjusterName : '' ) );
		lines.push( '' );
		lines.push( 'Dear Claims Representative' + ( f.adjusterName ? ', ' + f.adjusterName : '' ) + ':' );
		lines.push( '' );
		lines.push( opener );
		lines.push( '' );
		lines.push( closer );
		lines.push( '' );
		lines.push( 'Enclosed with this letter you will find supporting documentation, including repair estimates, photographs, and/or other records relevant to this claim. Please contact me at the phone number or email above if you require any additional information.' );
		lines.push( '' );
		lines.push( 'Sincerely,' );
		lines.push( '' );
		lines.push( f.yourName );

		return lines.join( '\n' );
	}

	function collectFields() {
		var amount = document.getElementById( 'mat-dl-amount' ).value;
		return {
			type: typeSelect.value,
			yourName: document.getElementById( 'mat-dl-name' ).value.trim() || '[Your Name]',
			yourAddress: document.getElementById( 'mat-dl-address' ).value.trim() || '[Your Address]',
			yourContact: document.getElementById( 'mat-dl-contact' ).value.trim() || '[Your Phone / Email]',
			insurerName: document.getElementById( 'mat-dl-insurer' ).value.trim() || '[Insurance Company Name and Address]',
			claimNumber: document.getElementById( 'mat-dl-claim' ).value.trim(),
			adjusterName: document.getElementById( 'mat-dl-adjuster' ).value.trim(),
			atFaultName: document.getElementById( 'mat-dl-atfault' ).value.trim() || 'the other driver',
			accidentDate: document.getElementById( 'mat-dl-date' ).value || '[date of accident]',
			accidentLocation: document.getElementById( 'mat-dl-location' ).value.trim() || '[location of accident]',
			narrative: document.getElementById( 'mat-dl-narrative' ).value.trim(),
			amountFormatted: formatUSD( amount ),
			deadlineDays: document.getElementById( 'mat-dl-deadline' ).value || '14',
		};
	}

	function prefillFromQuery() {
		var params = new URLSearchParams( window.location.search );
		var type = params.get( 'type' );
		if ( type && typeSelect.querySelector( 'option[value="' + type + '"]' ) ) {
			typeSelect.value = type;
		}
		if ( type === 'diminished-value' ) {
			try {
				var dv = sessionStorage.getItem( 'mat_dv_estimate' );
				if ( dv ) {
					document.getElementById( 'mat-dl-amount' ).value = dv;
				}
			} catch ( err ) { /* ignore */ }
		}
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		var fields = collectFields();
		var letter = buildLetter( fields );
		preview.textContent = letter;
		previewWrap.hidden = false;
		previewWrap.scrollIntoView( { behavior: 'smooth', block: 'start' } );
	} );

	if ( printBtn ) {
		printBtn.addEventListener( 'click', function () {
			window.print();
		} );
	}

	if ( copyBtn ) {
		copyBtn.addEventListener( 'click', function () {
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( preview.textContent ).then( function () {
					copyBtn.textContent = 'Copied!';
					setTimeout( function () { copyBtn.textContent = 'Copy text'; }, 1800 );
				} );
			}
		} );
	}

	prefillFromQuery();
})();
