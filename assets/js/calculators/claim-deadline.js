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
	var hubUrl = form.getAttribute( 'data-hub-url' );
	var appealUrl = form.getAttribute( 'data-appeal-url' );

	fetch( dataUrl ).then( function ( r ) { return r.json(); } ).then( function ( json ) {
		claimData = json;
		var select = document.getElementById( 'mat-cd-state' );
		select.options[0].textContent = 'Choose your state';
		var codes = Object.keys( json.all_states ).sort( function ( a, b ) {
			return json.all_states[ a ].localeCompare( json.all_states[ b ] );
		} );
		codes.forEach( function ( code ) {
			var opt = document.createElement( 'option' );
			opt.value = code;
			opt.textContent = json.all_states[ code ];
			select.appendChild( opt );
		} );
	} ).catch( function () {
		MAT.showError( resultBox, 'Could not load state data. Please refresh the page.' );
	} );

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		if ( ! claimData ) {
			return;
		}
		var select = document.getElementById( 'mat-cd-state' );
		var code = select.value;
		if ( ! code ) {
			MAT.showError( resultBox, { error: 'Please choose your state.', field: select } );
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
			if ( state.note ) {
				html += '<p>' + state.note + '</p>';
			}
			if ( state.citation ) {
				html += '<p style="font-size:.9rem;">' + ( state.citation.indexOf( 'No ' ) === 0 ? '' : 'Rule: ' ) + state.citation + '.</p>';
			}
		} else {
			var name = claimData.all_states[ code ] || 'Your state';
			html += '<h3 style="margin-top:0;">' + name + ' — general model</h3>';
			html += '<p>We don\'t have a state-specific breakdown verified for this state yet. Most states follow a similar model:</p>';
			html += '<table class="mat-table"><tbody>';
			html += '<tr><th scope="row">Acknowledge your claim</th><td>' + claimData.default.acknowledge + '</td></tr>';
			html += '<tr><th scope="row">Accept or deny it</th><td>' + claimData.default.decide + '</td></tr>';
			html += '<tr><th scope="row">Pay after agreement</th><td>' + claimData.default.pay + '</td></tr>';
			html += '</tbody></table>';
		}
		html += '<p style="font-size:.9rem;">Confirm the exact number of days with your state department of insurance; these rules are updated periodically.</p>';

		var links = [];
		var stateName = state ? state.name : claimData.all_states[ code ];
		if ( hubUrl && stateName ) {
			links.push( '<a href="' + MAT.escape( hubUrl + MAT.slug( stateName ) + '/' ) + '">' + MAT.escape( stateName ) + ' claim laws, total loss rule and sources</a>' );
		}
		if ( appealUrl ) {
			links.push( '<a href="' + MAT.escape( appealUrl ) + '">Claim denied? Write an appeal letter</a>' );
		}
		if ( links.length ) {
			html += '<p style="margin-bottom:0;">' + links.join( '<br>' ) + '</p>';
		}

		MAT.showResult( resultBox, html );
	} );
})();
