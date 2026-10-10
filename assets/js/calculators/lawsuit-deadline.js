/**
 * Car Accident Lawsuit Deadline Calculator.
 *
 * Deadline = accident date + the state's statute of limitations (years),
 * from assets/js/data/state-claim-facts.json.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-sol-form' );
	if ( ! form ) {
		return;
	}
	var resultBox = document.getElementById( 'mat-sol-result' );
	var errorBox = document.getElementById( 'mat-sol-error' );
	var hubUrl = form.getAttribute( 'data-hub-url' );
	var facts = null;
	var esc = MAT.escape;
	var DAY = 24 * 60 * 60 * 1000;

	fetch( form.getAttribute( 'data-json' ) ).then( function ( r ) { return r.json(); } ).then( function ( json ) {
		facts = json;
	} ).catch( function () {
		MAT.showError( resultBox, 'Could not load state data. Please refresh the page.' );
	} );

	function parseDate( value ) {
		var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec( value || '' );
		return m ? new Date( Date.UTC( +m[1], +m[2] - 1, +m[3] ) ) : null;
	}

	// Same month and day, N years later (Feb 29 becomes Feb 28).
	function addYears( d, years ) {
		var out = new Date( Date.UTC( d.getUTCFullYear() + years, d.getUTCMonth(), 1 ) );
		var last = new Date( Date.UTC( out.getUTCFullYear(), out.getUTCMonth() + 1, 0 ) ).getUTCDate();
		out.setUTCDate( Math.min( d.getUTCDate(), last ) );
		return out;
	}

	function today() {
		var n = new Date();
		return new Date( Date.UTC( n.getFullYear(), n.getMonth(), n.getDate() ) );
	}

	function formatDate( d ) {
		return d.toLocaleDateString( 'en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', timeZone: 'UTC' } );
	}

	function deadlineHtml( label, years, accident ) {
		var due = addYears( accident, years );
		var left = Math.round( ( due - today() ) / DAY );
		var status;
		if ( left < 0 ) {
			status = '<strong>This date has passed.</strong> Some exceptions can extend it, so if you still have a claim, speak to a lawyer right away.';
		} else if ( left <= 180 ) {
			status = '<strong>' + left + ' days left.</strong> That is close: if the claim isn\'t settled in writing, talk to a lawyer now.';
		} else {
			status = left + ' days left.';
		}
		return '<tr><th scope="row">' + label + ' (' + years + ( years === 1 ? ' year' : ' years' ) + ')</th><td><strong>' + formatDate( due ) + '</strong><br><span style="font-size:.9rem;">' + status + '</span></td></tr>';
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		if ( ! facts ) {
			return;
		}
		var stateEl = document.getElementById( 'mat-sol-state' );
		var dateEl = document.getElementById( 'mat-sol-date' );
		var type = document.getElementById( 'mat-sol-type' ).value;
		var accident = parseDate( dateEl.value );
		var fail = function ( msg, el ) {
			errorBox.textContent = msg;
			errorBox.hidden = false;
			el.setAttribute( 'aria-invalid', 'true' );
			el.focus();
		};
		stateEl.removeAttribute( 'aria-invalid' );
		dateEl.removeAttribute( 'aria-invalid' );
		if ( ! stateEl.value ) {
			fail( 'Please choose your state.', stateEl );
			return;
		}
		if ( ! accident ) {
			fail( 'Please enter the date of the accident.', dateEl );
			return;
		}
		if ( accident > today() ) {
			fail( 'The accident date can\'t be in the future.', dateEl );
			return;
		}
		errorBox.hidden = true;

		var state = facts.states[ stateEl.value ] || {};
		var sol = state.lawsuit_deadline;
		var name = stateEl.options[ stateEl.selectedIndex ].text;
		if ( ! sol ) {
			MAT.showError( resultBox, 'We have not confirmed the deadline for ' + name + ' yet. Check with the state court or a lawyer.' );
			return;
		}

		var rows = '';
		if ( type !== 'property' ) {
			rows += deadlineHtml( 'Injury lawsuit', sol.injury_years, accident );
		}
		if ( type !== 'injury' ) {
			rows += sol.property_years
				? deadlineHtml( 'Car damage lawsuit', sol.property_years, accident )
				: '<tr><th scope="row">Car damage lawsuit</th><td>We have not confirmed this deadline for ' + esc( name ) + '; check with the court or a lawyer.</td></tr>';
		}

		var html = '<h3 style="margin-top:0;">' + esc( name ) + '</h3>';
		html += '<table class="mat-table"><tbody>' + rows + '</tbody></table>';
		if ( sol.note ) {
			html += '<p style="font-size:.9rem;">' + esc( sol.note ) + '</p>';
		}
		html += '<p style="font-size:.9rem;">Law: ' + esc( sol.citation ) + '. Counted from the accident date; exceptions (government defendants, minors, injuries discovered later) can change it.</p>';
		if ( hubUrl ) {
			html += '<p style="margin-bottom:0;"><a href="' + esc( hubUrl + MAT.slug( name ) + '/' ) + '">' + esc( name ) + ' fault rule, claim deadlines and small claims limit</a></p>';
		}
		MAT.showResult( resultBox, html );
	} );
})();
