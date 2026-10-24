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

	/**
	 * US federal holidays for a year as "YYYY-MM-DD", on the day they are
	 * observed: a Saturday holiday moves to Friday, a Sunday one to Monday.
	 */
	var holidayCache = {};
	function federalHolidays( year ) {
		if ( holidayCache[ year ] ) {
			return holidayCache[ year ];
		}
		var key = function ( d ) {
			return d.toISOString().slice( 0, 10 );
		};
		var fixed = function ( month, day ) {
			var d = new Date( Date.UTC( year, month, day ) );
			var dow = d.getUTCDay();
			if ( dow === 6 ) {
				d.setUTCDate( day - 1 );
			} else if ( dow === 0 ) {
				d.setUTCDate( day + 1 );
			}
			return key( d );
		};
		// nth weekday (0 = Sunday) of a month; n = -1 for the last one.
		var nth = function ( month, weekday, n ) {
			var d;
			if ( n > 0 ) {
				d = new Date( Date.UTC( year, month, 1 ) );
				d.setUTCDate( 1 + ( weekday - d.getUTCDay() + 7 ) % 7 + ( n - 1 ) * 7 );
			} else {
				d = new Date( Date.UTC( year, month + 1, 0 ) );
				d.setUTCDate( d.getUTCDate() - ( d.getUTCDay() - weekday + 7 ) % 7 );
			}
			return key( d );
		};
		var list = [
			fixed( 0, 1 ), // New Year's Day
			nth( 0, 1, 3 ), // Martin Luther King Jr. Day
			nth( 1, 1, 3 ), // Presidents' Day
			nth( 4, 1, -1 ), // Memorial Day
			fixed( 5, 19 ), // Juneteenth
			fixed( 6, 4 ), // Independence Day
			nth( 8, 1, 1 ), // Labor Day
			nth( 9, 1, 2 ), // Columbus Day
			fixed( 10, 11 ), // Veterans Day
			nth( 10, 4, 4 ), // Thanksgiving
			fixed( 11, 25 ), // Christmas
		];
		// New Year's Day on a Saturday is observed on December 31 before.
		var nextNewYear = new Date( Date.UTC( year + 1, 0, 1 ) );
		if ( nextNewYear.getUTCDay() === 6 ) {
			list.push( year + '-12-31' );
		}
		holidayCache[ year ] = list;
		return list;
	}

	function addDays( date, n, businessOnly ) {
		var d = new Date( date.getTime() );
		while ( n > 0 ) {
			d.setUTCDate( d.getUTCDate() + 1 );
			var dow = d.getUTCDay();
			if ( ! businessOnly || ( dow !== 0 && dow !== 6 && federalHolidays( d.getUTCFullYear() ).indexOf( d.toISOString().slice( 0, 10 ) ) === -1 ) ) {
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
		return '<br><span style="font-size:.9rem;">Due by <strong>' + formatDate( due ) + '</strong>' + late + ', counted from ' + start.from + ( business ? ', not counting weekends or federal holidays' : '' ) + '.</span>';
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
