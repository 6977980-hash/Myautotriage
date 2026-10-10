/**
 * Auto Insurance Demand Letter Generator.
 *
 * Builds a complete, ready-to-send demand letter from form inputs. The
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
	var errorBox = document.getElementById( 'mat-dl-error' );

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
			return 'I am writing to dispute the settlement amount offered on claim number ' + ( f.claimNumber || '[claim number]' ) + '. The amount offered does not reasonably reflect the actual cost to repair or replace my vehicle following the accident on ' + f.accidentDate + '. ' + f.narrative;
		},
		'diminished-value': function ( f ) {
			return 'I am writing to demand compensation for the diminished value of my vehicle following the accident on ' + f.accidentDate + ', which was caused by your insured, ' + f.atFaultName + '. Even after full repair, my vehicle is now worth less on the resale market than it was before the accident, and this diminished value is a legitimate component of my property damage claim. ' + f.narrative;
		},
		'total-loss': function ( f ) {
			return 'I am writing in response to your total loss valuation of my vehicle on claim number ' + ( f.claimNumber || '[claim number]' ) + ', following the loss on ' + f.accidentDate + '. I do not accept the actual cash value you offered, because it does not reflect what it would cost to buy a comparable vehicle in my area. ' + f.narrative;
		},
		'appraisal': function ( f ) {
			return 'We have been unable to agree on the amount of loss on claim number ' + ( f.claimNumber || '[claim number]' ) + ', arising from the loss on ' + f.accidentDate + '. Under the Appraisal provision of my policy, this letter is my written demand for appraisal of the amount of loss. ' + f.narrative;
		},
		'doi-complaint': function ( f ) {
			return 'I am filing a complaint against ' + f.companyName + ' regarding claim number ' + ( f.claimNumber || '[claim number]' ) + ', arising from the accident on ' + f.accidentDate + ' at ' + f.accidentLocation + '. ' + f.narrative;
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
		'total-loss': function ( f ) {
			return 'Based on the enclosed listings of comparable vehicles for sale near me and the condition, mileage and options of my vehicle, I am requesting a revised actual cash value of ' + f.amountFormatted + ', plus the sales tax, title and registration fees my policy and state law provide for. Please also send me a complete copy of the valuation report you relied on, including the comparable vehicles used and every condition adjustment, and respond in writing within ' + f.deadlineDays + ' days of the date of this letter.';
		},
		'appraisal': function ( f ) {
			return 'My position on the amount of loss is ' + f.amountFormatted + '. I have selected [name and contact details of my appraiser] as my appraiser. Please name your appraiser in writing within ' + f.deadlineDays + ' days of the date of this letter, or within any shorter time my policy sets, so the appraisers can proceed. This demand concerns only the amount of the loss; it does not waive any of my other rights under the policy or the law.';
		},
		'doi-complaint': function ( f ) {
			return 'The amount in dispute is ' + f.amountFormatted + '. I have tried to resolve this directly with the company, including in writing, without success. I ask the Department to review the company\'s handling of my claim and to require a written response explaining its position and the reasons for any delay or reduced payment.';
		},
		'small-claims': function ( f ) {
			return 'I am requesting payment of ' + f.amountFormatted + ' within ' + f.deadlineDays + ' days of the date of this letter. If I do not receive payment or a written response by that date, I intend to file a claim in small claims court without further notice.';
		},
	};

	var ENCLOSURES = {
		'general': 'Enclosed with this letter you will find supporting documentation, including repair estimates, photographs, and/or other records relevant to this claim. Please contact me at the phone number or email above if you require any additional information.',
		'total-loss': 'Enclosed are listings of comparable vehicles, records of recent maintenance and upgrades, and photographs of my vehicle before the loss. Please contact me at the phone number or email above if you need anything else to reconsider the valuation.',
		'appraisal': 'Enclosed are my repair estimate or valuation, photographs, and our correspondence on the amount of loss. Please contact me at the phone number or email above with your appraiser\'s name and contact details.',
		'doi-complaint': 'Enclosed are copies of my policy declarations page, the claim correspondence, the company\'s offer or denial, and my own estimates. Please contact me at the phone number or email above if the Department needs anything else.',
	};

	function formatUSD( n ) {
		return n ? MAT.usd( n ) : '[amount]';
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
		if ( f.type === 'doi-complaint' ) {
			lines.push( '[Your state] Department of Insurance, Consumer Services' );
			lines.push( 'Re: Complaint against ' + f.companyName + ', Claim No. ' + ( f.claimNumber || '[Claim number]' ) );
			lines.push( '' );
			lines.push( 'Dear Consumer Services Representative:' );
		} else {
			lines.push( f.insurerName );
			lines.push( 'Re: Claim No. ' + ( f.claimNumber || '[Claim number]' ) + ( f.adjusterName ? ' — Attn: ' + f.adjusterName : '' ) );
			lines.push( '' );
			lines.push( 'Dear Claims Representative' + ( f.adjusterName ? ', ' + f.adjusterName : '' ) + ':' );
		}
		lines.push( '' );
		lines.push( opener.trim() );
		lines.push( '' );
		lines.push( closer );
		lines.push( '' );
		lines.push( ENCLOSURES[ f.type ] || ENCLOSURES.general );
		lines.push( '' );
		lines.push( 'Sincerely,' );
		lines.push( '' );
		lines.push( f.yourName );

		return lines.join( '\n' );
	}

	function collectFields( amount, deadline ) {
		return {
			type: typeSelect.value,
			yourName: document.getElementById( 'mat-dl-name' ).value.trim() || '[Your Name]',
			yourAddress: document.getElementById( 'mat-dl-address' ).value.trim() || '[Your Address]',
			yourContact: document.getElementById( 'mat-dl-contact' ).value.trim() || '[Your Phone / Email]',
			insurerName: document.getElementById( 'mat-dl-insurer' ).value.trim() || '[Insurance Company Name and Address]',
			companyName: document.getElementById( 'mat-dl-insurer' ).value.trim().split( ',' )[0] || '[Insurance Company Name]',
			claimNumber: document.getElementById( 'mat-dl-claim' ).value.trim(),
			adjusterName: document.getElementById( 'mat-dl-adjuster' ).value.trim(),
			atFaultName: document.getElementById( 'mat-dl-atfault' ).value.trim() || 'the other driver',
			accidentDate: MAT.longDate( document.getElementById( 'mat-dl-date' ).value ) || '[date of accident]',
			accidentLocation: document.getElementById( 'mat-dl-location' ).value.trim() || '[location of accident]',
			narrative: document.getElementById( 'mat-dl-narrative' ).value.trim(),
			amountFormatted: formatUSD( amount ),
			deadlineDays: String( deadline ),
		};
	}

	function prefillFromQuery() {
		var params = new URLSearchParams( window.location.search );
		var type = params.get( 'type' );
		if ( type && typeSelect.querySelector( 'option[value="' + type + '"]' ) ) {
			typeSelect.value = type;
		}
		// The diminished value calculator stores its last estimate; use it on
		// any diminished value letter (the dedicated page or ?type=), unless
		// the visitor already typed an amount.
		var amountField = document.getElementById( 'mat-dl-amount' );
		if ( typeSelect.value === 'diminished-value' && ! amountField.value ) {
			try {
				var dv = sessionStorage.getItem( 'mat_dv_estimate' );
				if ( dv && Number( dv ) > 0 ) {
					amountField.value = dv;
				}
			} catch ( err ) { /* ignore */ }
		}
		// Same for the total loss valuation checker: its fair value and the
		// points it found go into a total loss letter the visitor hasn't
		// started filling in.
		// The claim diary hands over its dated timeline for a complaint.
		if ( typeSelect.value === 'doi-complaint' ) {
			try {
				var timeline = sessionStorage.getItem( 'mat_diary_timeline' );
				var narrativeEl = document.getElementById( 'mat-dl-narrative' );
				if ( timeline && ! narrativeEl.value ) {
					narrativeEl.value = 'Here is the timeline of my claim:\n\n' + timeline;
				}
			} catch ( err ) { /* ignore */ }
		}
		if ( typeSelect.value === 'total-loss' ) {
			try {
				var tlAmount = sessionStorage.getItem( 'mat_tlv_amount' );
				var tlPoints = sessionStorage.getItem( 'mat_tlv_narrative' );
				var narrativeField = document.getElementById( 'mat-dl-narrative' );
				if ( tlAmount && Number( tlAmount ) > 0 && ! amountField.value ) {
					amountField.value = tlAmount;
				}
				if ( tlPoints && ! narrativeField.value ) {
					narrativeField.value = tlPoints;
				}
			} catch ( err ) { /* ignore */ }
		}
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		var r = MAT.nums( {
			amount: [ 'mat-dl-amount', { label: 'The amount' } ],
			deadline: [ 'mat-dl-deadline', { label: 'The response deadline', fallback: 14, integer: true } ],
		} );
		if ( r.error ) {
			errorBox.hidden = false;
			errorBox.textContent = r.error;
			r.field.focus();
			return;
		}
		errorBox.hidden = true;
		var fields = collectFields( r.values.amount, r.values.deadline );
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
