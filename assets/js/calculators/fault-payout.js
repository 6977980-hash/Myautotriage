/**
 * Comparative Fault Payout Calculator.
 *
 * Recovery = damages x (1 - your fault), unless the state's rule bars it:
 * pure contributory (any fault), 50% bar (fault >= 50), 51% bar
 * (fault > 50). Rules come from assets/js/data/state-claim-facts.json.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-cf-form' );
	if ( ! form ) {
		return;
	}
	var resultBox = document.getElementById( 'mat-cf-result' );
	var errorBox = document.getElementById( 'mat-cf-error' );
	var hubUrl = form.getAttribute( 'data-hub-url' );
	var facts = null;
	var esc = MAT.escape;

	var NAMES = {
		'pure_contributory': 'pure contributory negligence',
		'pure_comparative': 'pure comparative fault',
		'bar_50': 'modified comparative fault with a 50% bar',
		'bar_51': 'modified comparative fault with a 51% bar',
		'slight_gross': 'a slight/gross negligence comparison',
	};

	fetch( form.getAttribute( 'data-json' ) ).then( function ( r ) { return r.json(); } ).then( function ( json ) {
		facts = json;
	} ).catch( function () {
		MAT.showError( resultBox, 'Could not load state data. Please refresh the page.' );
	} );

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		if ( ! facts ) {
			return;
		}
		var stateEl = document.getElementById( 'mat-cf-state' );
		stateEl.removeAttribute( 'aria-invalid' );
		var r = MAT.nums( {
			damages: [ 'mat-cf-damages', { label: 'Your total damages', required: true } ],
			fault: [ 'mat-cf-fault', { label: 'Your share of the fault', required: true } ],
		} );
		if ( ! stateEl.value ) {
			r = { error: 'Please choose your state.', field: stateEl };
			stateEl.setAttribute( 'aria-invalid', 'true' );
		}
		if ( r.error ) {
			errorBox.textContent = r.error;
			errorBox.hidden = false;
			r.field.focus();
			return;
		}
		errorBox.hidden = true;

		var damages = r.values.damages;
		var f = r.values.fault;
		var name = stateEl.options[ stateEl.selectedIndex ].text;
		var fault = ( facts.states[ stateEl.value ] || {} ).fault;
		if ( ! fault ) {
			MAT.showError( resultBox, 'We have not confirmed the fault rule for ' + name + ' yet.' );
			return;
		}

		var reduced = damages * ( 100 - f ) / 100;
		var barred = false;
		var why = '';
		switch ( fault.rule ) {
			case 'pure_contributory':
				barred = f > 0;
				why = barred ? 'Under pure contributory negligence, any fault of your own can bar recovery from the other driver.' : 'With no fault on your side, you can recover your full damages.';
				break;
			case 'bar_50':
				barred = f >= 50;
				why = barred ? 'At 50% or more of the fault, ' + name + ' bars recovery.' : 'Below 50% fault, your recovery is reduced by your share.';
				break;
			case 'bar_51':
				barred = f > 50;
				why = barred ? 'More than 50% at fault means you were more at fault than the other side, which bars recovery in ' + name + '.' : 'At 50% fault or less, your recovery is reduced by your share.';
				break;
			case 'slight_gross':
				why = 'South Dakota compares negligence differently: you recover only if your negligence was slight compared with the other driver\'s, and then reduced by it. Treat this figure as the most you could expect.';
				break;
			default:
				why = 'Your recovery is reduced by your share of the fault, however large it is.';
		}
		// Michigan: past 50% the economic damages are still recoverable
		// (reduced); only pain and suffering is barred.
		var michigan = stateEl.value === 'MI' && f > 50;

		var html = '<h3 style="margin-top:0;">' + esc( name ) + ': ' + NAMES[ fault.rule ] + '</h3>';
		if ( barred && ! michigan ) {
			html += '<p class="mat-result-box__figure">' + MAT.usd( 0 ) + '</p>';
		} else {
			html += '<p class="mat-result-box__figure">' + MAT.usd( Math.round( reduced ) ) + '</p>';
		}
		html += '<p>' + esc( michigan ? 'In Michigan, at more than 50% fault you can still recover your economic damages (such as medical bills and lost wages) reduced by your share, but not pain-and-suffering damages.' : why ) + '</p>';
		html += '<p style="font-size:.9rem;">' + MAT.usd( damages ) + ' in damages, ' + f + '% your fault. ';
		if ( fault.rule === 'bar_50' || fault.rule === 'bar_51' ) {
			var line = fault.rule === 'bar_50' ? 49 : 50;
			html += 'At ' + line + '% fault you would still recover ' + MAT.usd( Math.round( damages * ( 100 - line ) / 100 ) ) + '; one more point and it drops to $0.';
		} else if ( fault.rule === 'pure_contributory' && f > 0 ) {
			html += 'Some exceptions exist, such as the other driver having the "last clear chance" to avoid the crash; a lawyer can tell you if one fits.';
		}
		html += '</p>';
		if ( fault.note ) {
			html += '<p style="font-size:.9rem;">' + esc( fault.note ) + '</p>';
		}
		html += '<p style="font-size:.9rem;">Rule: ' + esc( fault.citation ) + '.</p>';
		if ( hubUrl ) {
			html += '<p style="margin-bottom:0;"><a href="' + esc( hubUrl + MAT.slug( name ) + '/' ) + '">' + esc( name ) + ' claim laws, deadlines and small claims limit</a></p>';
		}
		MAT.showResult( resultBox, html );
	} );
})();
