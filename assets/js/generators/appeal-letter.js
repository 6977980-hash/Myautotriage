/**
 * Claim Denial Appeal Letter Generator.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-ap-form' );
	if ( ! form ) {
		return;
	}
	var preview = document.getElementById( 'mat-ap-preview' );
	var previewWrap = document.getElementById( 'mat-ap-preview-wrap' );
	var printBtn = document.getElementById( 'mat-ap-print' );
	var copyBtn = document.getElementById( 'mat-ap-copy' );
	var errorBox = document.getElementById( 'mat-ap-error' );

	function todayFormatted() {
		return new Date().toLocaleDateString( 'en-US', { year: 'numeric', month: 'long', day: 'numeric' } );
	}

	function buildLetter( f ) {
		var lines = [];
		lines.push( f.yourName );
		lines.push( f.yourAddress );
		lines.push( f.yourContact );
		lines.push( '' );
		lines.push( todayFormatted() );
		lines.push( '' );
		lines.push( f.insurerName );
		lines.push( 'Re: Formal Appeal of Claim Denial — Claim No. ' + ( f.claimNumber || '[Claim number]' ) + ( f.policyNumber ? ', Policy No. ' + f.policyNumber : '' ) );
		lines.push( '' );
		lines.push( 'Dear Claims Review Department:' );
		lines.push( '' );
		lines.push( 'I am writing to formally appeal your decision, dated ' + ( f.denialDate || '[date of denial letter]' ) + ', to deny my claim. Your denial letter cited the following reason: "' + ( f.denialReason || '[reason stated in your denial letter]' ) + '."' );
		lines.push( '' );
		lines.push( 'I respectfully disagree with this decision for the following reason(s): ' + ( f.rebuttal || '[explain, with specifics, why the stated reason does not apply to your claim]' ) );
		lines.push( '' );
		lines.push( 'In support of this appeal, I am enclosing the following documentation: ' + ( f.evidence || '[list your supporting documents — photos, estimates, medical records, witness statements, etc.]' ) + '.' );
		lines.push( '' );
		lines.push( 'Based on the information above, I am requesting that you reverse this denial and approve payment of ' + ( f.amountFormatted || 'the full claim amount' ) + '. Please respond in writing within ' + ( f.deadlineDays || '30' ) + ' days of the date of this letter. If this appeal is not resolved to my satisfaction, I intend to pursue this further, including filing a complaint with my state department of insurance and/or seeking independent legal advice.' );
		lines.push( '' );
		lines.push( 'Thank you for your prompt attention to this matter.' );
		lines.push( '' );
		lines.push( 'Sincerely,' );
		lines.push( '' );
		lines.push( f.yourName );
		return lines.join( '\n' );
	}


	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		var r = MAT.nums( {
			amount: [ 'mat-ap-amount', { label: 'The amount' } ],
			deadline: [ 'mat-ap-deadline', { label: 'The response deadline', fallback: 30, integer: true } ],
		} );
		if ( r.error ) {
			errorBox.hidden = false;
			errorBox.textContent = r.error;
			r.field.focus();
			return;
		}
		errorBox.hidden = true;
		var f = {
			yourName: document.getElementById( 'mat-ap-name' ).value.trim() || '[Your Name]',
			yourAddress: document.getElementById( 'mat-ap-address' ).value.trim() || '[Your Address]',
			yourContact: document.getElementById( 'mat-ap-contact' ).value.trim() || '[Your Phone / Email]',
			insurerName: document.getElementById( 'mat-ap-insurer' ).value.trim() || '[Insurance Company Name and Address]',
			claimNumber: document.getElementById( 'mat-ap-claim' ).value.trim(),
			policyNumber: document.getElementById( 'mat-ap-policy' ).value.trim(),
			denialDate: MAT.longDate( document.getElementById( 'mat-ap-denialdate' ).value ),
			denialReason: document.getElementById( 'mat-ap-reason' ).value.trim(),
			rebuttal: document.getElementById( 'mat-ap-rebuttal' ).value.trim(),
			evidence: document.getElementById( 'mat-ap-evidence' ).value.trim(),
			amountFormatted: r.values.amount ? MAT.usd( r.values.amount ) : '',
			deadlineDays: String( r.values.deadline ),
		};

		preview.textContent = buildLetter( f );
		previewWrap.hidden = false;
		previewWrap.scrollIntoView( { behavior: 'smooth', block: 'start' } );
	} );

	if ( printBtn ) {
		printBtn.addEventListener( 'click', function () { window.print(); } );
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
})();
