/**
 * Claim Payment Deadline Lookup.
 *
 * With the optional dates it also turns "15 business days after proof of
 * loss" into a calendar date, picking the start date from what the rule
 * counts from (the claim report, proof of loss, or the agreed settlement).
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

	function parseDate( value ) {
		var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec( value || '' );
		return m ? new Date( Date.UTC( +m[1], +m[2] - 1, +m[3] ) ) : null;
	}

	function addDays( date, n, businessOnly ) {
		var d = new Date( date.getTime() );
		while ( n > 0 ) {
			d.setUTCDate( d.getUTCDate() + 1 );
			var dow = d.getUTCDay();
			if ( ! businessOnly || ( dow !== 0 && dow !== 6 ) ) {
				n--;
			}
		}
		return d;
	}

	function formatDate( d ) {
		return d.toLocaleDateString( 'en-US', { weekday: 'short', year: 'numeric', month: 'long', day: 'numeric', timeZone: 'UTC' } );
	}

	/**
	 * Which of the entered dates a rule counts from, or null.
	 */
	function startFor( field, text, dates ) {
		var t = text.toLowerCase();
		if ( /proof/.test( t ) ) {
			return dates.proof ? { date: dates.proof, from: 'proof of loss' } : null;
		}
		if ( /settlement|agree|accept|approved|liability|will pay|ready for payment/.test( t ) ) {
			return dates.agreed ? { date: dates.agreed, from: 'the agreed settlement' } : null;
		}
		if ( /notice|claim is received|claim forms|complete claim|claim and bills/.test( t ) || ( field === 'acknowledge' && ! /after/.test( t ) ) ) {
			return dates.notice ? { date: dates.notice, from: 'your claim report' } : null;
		}
		return null;
	}

	function dueDate( field, text, dates ) {
		var m = /^(\d+) (calendar |business |working )?days/.exec( text );
		if ( ! m ) {
			return '';
		}
		var start = startFor( field, text, dates );
		if ( ! start ) {
			return '';
		}
		var business = m[2] === 'business ' || m[2] === 'working ';
		var due = addDays( start.date, parseInt( m[1], 10 ), business );
		var late = due < today() ? ' <strong>(passed)</strong>' : '';
		return '<br><span style="font-size:.9rem;">Due by <strong>' + formatDate( due ) + '</strong>' + late + ', counted from ' + start.from + '.</span>';
	}

	function today() {
		var n = new Date();
		return new Date( Date.UTC( n.getFullYear(), n.getMonth(), n.getDate() ) );
	}

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
		var dates = {
			notice: parseDate( document.getElementById( 'mat-cd-notice' ).value ),
			proof: parseDate( document.getElementById( 'mat-cd-proof' ).value ),
			agreed: parseDate( document.getElementById( 'mat-cd-agreed' ).value ),
		};
		var anyDate = dates.notice || dates.proof || dates.agreed;
		var anyDue = false;
		function row( label, field, text ) {
			var due = anyDate ? dueDate( field, text, dates ) : '';
			anyDue = anyDue || !! due;
			return '<tr><th scope="row">' + label + '</th><td>' + text + due + '</td></tr>';
		}

		if ( state ) {
			html += '<h3 style="margin-top:0;">' + state.name + '</h3>';
			html += '<table class="mat-table"><tbody>';
			html += row( 'Acknowledge your claim', 'acknowledge', state.acknowledge );
			html += row( 'Accept or deny it', 'decide', state.decide );
			html += row( 'Pay after agreement', 'pay', state.pay );
			html += '</tbody></table>';
			if ( anyDate && ! anyDue ) {
				html += '<p style="font-size:.9rem;">None of these rules count from the dates you entered, or the state sets no fixed number of days, so there is no exact due date to show.</p>';
			}
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
