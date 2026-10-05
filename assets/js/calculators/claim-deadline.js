/**
 * Claim Payment Deadline Lookup.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-cd-form' );
	if ( ! form ) {
		return;
	}
	var resultBox = document.getElementById( 'mat-cd-result' );
	var dataUrl = form.getAttribute( 'data-json' );
	var claimData = null;

	fetch( dataUrl ).then( function ( r ) { return r.json(); } ).then( function ( json ) {
		claimData = json;
		var select = document.getElementById( 'mat-cd-state' );
		var codes = Object.keys( json.all_states ).sort( function ( a, b ) {
			return json.all_states[ a ].localeCompare( json.all_states[ b ] );
		} );
		codes.forEach( function ( code ) {
			var opt = document.createElement( 'option' );
			opt.value = code;
			opt.textContent = json.all_states[ code ] + ( json.states[ code ] ? '' : '' );
			select.appendChild( opt );
		} );
	} ).catch( function () {
		resultBox.innerHTML = '<p role="alert">Could not load state data. Please refresh the page.</p>';
		resultBox.hidden = false;
	} );

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		if ( ! claimData ) {
			return;
		}
		var code = document.getElementById( 'mat-cd-state' ).value;
		if ( ! code ) {
			return;
		}
		var state = claimData.states[ code ];
		var html = '';

		if ( state ) {
			html += '<h3 style="margin-top:0;">' + state.name + '</h3>';
			html += '<table class="mat-table"><tbody>';
			html += '<tr><th scope="row">Acknowledge your claim</th><td>' + state.acknowledge + '</td></tr>';
			html += '<tr><th scope="row">Accept or deny it</th><td>' + state.decide + '</td></tr>';
			html += '<tr><th scope="row">Pay after agreement</th><td>' + state.pay + '</td></tr>';
			html += '</tbody></table>';
		} else {
			var name = ( document.getElementById( 'mat-cd-state' ).selectedOptions[0] || {} ).textContent || 'Your state';
			html += '<h3 style="margin-top:0;">' + name + ' — general model</h3>';
			html += '<p>We don\'t have a state-specific breakdown verified for this state yet. Most states follow a similar model:</p>';
			html += '<table class="mat-table"><tbody>';
			html += '<tr><th scope="row">Acknowledge your claim</th><td>' + claimData.default.acknowledge + '</td></tr>';
			html += '<tr><th scope="row">Accept or deny it</th><td>' + claimData.default.decide + '</td></tr>';
			html += '<tr><th scope="row">Pay after agreement</th><td>' + claimData.default.pay + '</td></tr>';
			html += '</tbody></table>';
		}
		html += '<p style="margin-bottom:0;font-size:.9rem;">Confirm the exact number of days with your state department of insurance — these rules are updated periodically.</p>';

		resultBox.innerHTML = html;
		resultBox.hidden = false;
		resultBox.setAttribute( 'tabindex', '-1' );
		resultBox.focus();
	} );
})();
