/**
 * Claim Triage: turn three answers into a next step. All the content and
 * the state facts come from the form's data-triage attribute, built in
 * inc/claim-triage.php, so this script only arranges them.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-triage-form' );
	if ( ! form ) {
		return;
	}
	var box = document.getElementById( 'mat-triage-result' );
	var errorBox = document.getElementById( 'mat-triage-error' );
	var situationEl = document.getElementById( 'mat-triage-situation' );
	var stateEl = document.getElementById( 'mat-triage-state' );
	var partyEl = document.getElementById( 'mat-triage-party' );
	var data;
	try {
		data = JSON.parse( form.getAttribute( 'data-triage' ) );
	} catch ( err ) {
		return;
	}

	function esc( s ) {
		return String( s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	function linkList( items ) {
		return '<ul>' + items.map( function ( i ) {
			return '<li><a href="' + esc( i.url ) + '">' + esc( i.label ) + '</a></li>';
		} ).join( '' ) + '</ul>';
	}

	function stateFacts( s, show, party ) {
		var rows = [];
		show.forEach( function ( key ) {
			if ( key === 'claim' && s.claim ) {
				rows.push( '<li>Insurer must acknowledge your claim ' + esc( s.claim.acknowledge ) + ', accept or deny it ' + esc( s.claim.decide ) + ', and pay ' + esc( s.claim.pay ) + '.' + ( party === 'other' ? ' These rules are mostly written for claims on your own policy; many states apply some of them to claims against the other driver\'s insurer too.' : '' ) + '</li>' );
			}
			if ( key === 'totalloss' && s.totalloss ) {
				rows.push( '<li>Your car is a total loss when: ' + esc( s.totalloss.charAt( 0 ).toLowerCase() + s.totalloss.slice( 1 ) ) + '.</li>' );
			}
			if ( key === 'fault' && s.fault ) {
				rows.push( '<li>' + esc( s.fault ) + '</li>' );
			}
			if ( key === 'lawsuit' && s.lawsuit ) {
				rows.push( '<li>Lawsuit deadline: ' + esc( s.lawsuit.injury ) + ' for injuries' + ( s.lawsuit.property ? ', ' + esc( s.lawsuit.property ) + ' for property damage' : '' ) + ', usually from the accident date.</li>' );
			}
		} );
		if ( ! rows.length ) {
			return '';
		}
		var html = '<h3>' + esc( s.name ) + ' rules that matter here</h3><ul>' + rows.join( '' ) + '</ul>';
		if ( s.url ) {
			html += '<p><a href="' + esc( s.url ) + '">All ' + esc( s.name ) + ' claim laws, with citations</a></p>';
		}
		return html;
	}

	function show() {
		var sit = data.situations[ situationEl.value ];
		if ( ! sit ) {
			errorBox.textContent = 'Please choose what\'s happening with your claim.';
			errorBox.hidden = false;
			situationEl.setAttribute( 'aria-invalid', 'true' );
			situationEl.focus();
			return false;
		}
		errorBox.hidden = true;
		situationEl.removeAttribute( 'aria-invalid' );
		var party = partyEl.value;
		var state = data.states[ stateEl.value ];

		var html = '<p class="mat-triage-result__label">Your next step</p>';
		html += '<h2 class="mat-triage-result__title">' + esc( sit.title ) + '</h2>';
		html += '<div class="mat-triage-result__first"><p>' + esc( sit.first.why ) + '</p>';
		html += '<a class="mat-btn mat-btn--primary" href="' + esc( sit.first.url ) + '">' + esc( sit.first.label ) + '</a></div>';
		if ( party === 'own' && sit.own ) {
			html += '<p class="mat-triage-result__note">' + esc( sit.own ) + '</p>';
		}
		if ( sit.then.length ) {
			html += '<h3>Then</h3>' + linkList( sit.then );
		}
		if ( state ) {
			html += stateFacts( state, sit.show, party );
		} else {
			html += '<p class="mat-triage-result__note">Pick your state above to see its deadlines and rules for this.</p>';
		}
		if ( sit.phrases.length ) {
			html += '<h3>If the adjuster said…</h3>' + linkList( sit.phrases );
		}
		box.innerHTML = html;
		box.hidden = false;
		return true;
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		if ( show() ) {
			box.setAttribute( 'tabindex', '-1' );
			box.focus();
		}
	} );

	// Once an answer is showing, keep it in step with the selects.
	[ stateEl, partyEl, situationEl ].forEach( function ( el ) {
		el.addEventListener( 'change', function () {
			if ( ! box.hidden && situationEl.value ) {
				show();
			}
		} );
	} );
})();
