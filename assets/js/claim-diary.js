/**
 * Claim Diary.
 *
 * Everything is kept in localStorage under one key, so it stays in the
 * visitor's browser. From the claim dates and the state's rule text it
 * works out deadline dates; from the entries it flags promises that have
 * passed and messages that got no answer; and it turns the log into a
 * plain-text timeline for a letter or complaint.
 */
(function () {
	'use strict';

	var root = document.getElementById( 'mat-diary' );
	if ( ! root ) {
		return;
	}
	var KEY = 'mat_claim_diary_v1';
	var DAY = 24 * 60 * 60 * 1000;
	var REPLY_DAYS = 15;
	var states = {};
	try {
		states = JSON.parse( root.getAttribute( 'data-states' ) || '{}' );
	} catch ( err ) { /* no state rules */ }
	var letterUrl = root.getAttribute( 'data-letter-url' );

	var CLAIM_FIELDS = [ 'insurer', 'claimno', 'adjuster', 'state', 'party', 'loss', 'reported', 'pol' ];
	var TYPES = {
		sent: 'I contacted them',
		received: 'They contacted me',
		docs: 'I sent documents',
		inspection: 'Inspection or appraisal',
		offer: 'Offer or estimate received',
		payment: 'Payment received',
		denial: 'Denial received',
		note: 'Note',
	};

	function $( id ) {
		return document.getElementById( id );
	}

	// ---- Storage ---------------------------------------------------------

	var store = { claim: {}, entries: [] };
	var storageOk = true;

	function load() {
		try {
			var raw = window.localStorage.getItem( KEY );
			if ( raw ) {
				var parsed = JSON.parse( raw );
				if ( parsed && Array.isArray( parsed.entries ) ) {
					store = { claim: parsed.claim || {}, entries: parsed.entries };
				}
			}
		} catch ( err ) {
			storageOk = false;
		}
	}

	function save() {
		try {
			window.localStorage.setItem( KEY, JSON.stringify( store ) );
			storageOk = true;
		} catch ( err ) {
			storageOk = false;
		}
		$( 'mat-diary-saved' ).textContent = storageOk
			? 'Saved in this browser.'
			: 'This browser isn\'t letting the page save. Your diary will be lost when you close the page, so download a backup.';
	}

	// ---- Dates -----------------------------------------------------------

	function parseDate( v ) {
		var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec( v || '' );
		return m ? Date.UTC( +m[1], +m[2] - 1, +m[3] ) : null;
	}

	function today() {
		var n = new Date();
		return Date.UTC( n.getFullYear(), n.getMonth(), n.getDate() );
	}

	function fmt( t ) {
		return new Date( t ).toLocaleDateString( 'en-US', { year: 'numeric', month: 'short', day: 'numeric', timeZone: 'UTC' } );
	}

	function addDays( t, n, business ) {
		if ( ! business ) {
			return t + n * DAY;
		}
		var d = t;
		while ( n > 0 ) {
			d += DAY;
			var wd = new Date( d ).getUTCDay();
			if ( wd !== 0 && wd !== 6 ) {
				n--;
			}
		}
		return d;
	}

	function addYears( t, n ) {
		var d = new Date( t );
		d.setUTCFullYear( d.getUTCFullYear() + n );
		return d.getTime();
	}

	/**
	 * Read a rule like "15 working days after proof of loss".
	 * Returns { days, business, from } or null when it has no number.
	 * from: 'report' or 'pol'; null when the start can't be dated.
	 */
	function readRule( text, defaultFrom ) {
		var m = /^(\d+)\s+(calendar\s+|business\s+|working\s+)?days?\b(.*)$/i.exec( text || '' );
		if ( ! m ) {
			return null;
		}
		var rest = m[3].toLowerCase();
		var from = defaultFrom;
		if ( /proof of (loss|claim)|complete (claim|proof)|claim forms/.test( rest ) ) {
			from = 'pol';
		} else if ( /notice of (claim|loss)|claim is received|the claim and bills/.test( rest ) ) {
			from = 'report';
		} else if ( /after/.test( rest ) ) {
			from = null;
		}
		return { days: +m[1], business: !! m[2] && ! /calendar/i.test( m[2] ), from: from };
	}

	// ---- Alerts ----------------------------------------------------------

	function alertItem( level, title, text ) {
		return '<li class="mat-diary-alert mat-diary-alert--' + level + '"><strong>' + MAT.escape( title ) + '</strong>' + ( text ? '<p>' + MAT.escape( text ) + '</p>' : '' ) + '</li>';
	}

	function deadlineAlert( label, rule, text, dates ) {
		var start = rule.from === 'pol' ? dates.pol : dates.reported;
		if ( start === null ) {
			return alertItem( 'info', label + ': ' + text, rule.from === 'pol' ? 'Enter the date the insurer got your proof of loss to see the date.' : 'Enter the date you reported the claim to see the date.' );
		}
		var due = addDays( start, rule.days, rule.business );
		var left = Math.round( ( due - dates.now ) / DAY );
		var when = left < 0 ? Math.abs( left ) + ' days ago' : ( left === 0 ? 'today' : 'in ' + left + ' days' );
		return alertItem( left < 0 ? 'warn' : ( left <= 5 ? 'soon' : 'info' ), label + ' by ' + fmt( due ) + ' (' + when + ')', 'Rule: ' + text + '.' );
	}

	function renderAlerts() {
		var c = store.claim;
		var s = states[ c.state ];
		var dates = { reported: parseDate( c.reported ), pol: parseDate( c.pol ), loss: parseDate( c.loss ), now: today() };
		var items = [];

		if ( s && s.acknowledge ) {
			var ack = readRule( s.acknowledge, 'report' );
			var decide = readRule( s.decide, 'pol' );
			var answered = store.entries.some( function ( e ) { return e.type !== 'sent' && e.type !== 'docs' && e.type !== 'note'; } );
			if ( ack && ! answered ) {
				items.push( deadlineAlert( 'Insurer should acknowledge your claim', ack, s.acknowledge, dates ) );
			}
			var decided = store.entries.some( function ( e ) { return e.type === 'offer' || e.type === 'payment' || e.type === 'denial'; } );
			if ( decide && decide.from && ! decided ) {
				items.push( deadlineAlert( 'Insurer should accept or deny the claim', decide, s.decide, dates ) );
			} else if ( ! decided ) {
				items.push( alertItem( 'info', 'Decision deadline: ' + s.decide, '' ) );
			}
			if ( c.party === 'other' ) {
				items.push( alertItem( 'info', 'These claim deadlines are mostly written for claims on your own policy', 'Many states apply some of them to claims against the other driver\'s insurer too. Your state page lists the rule.' ) );
			}
		} else if ( ! s ) {
			items.push( alertItem( 'info', 'Choose your state to see its claim deadlines', '' ) );
		}

		if ( s && s.sol && dates.loss !== null ) {
			var inj = addYears( dates.loss, s.sol.injury_years );
			var line = 'Injury claims: ' + fmt( inj ) + '.';
			if ( s.sol.property_years ) {
				line += ' Property damage: ' + fmt( addYears( dates.loss, s.sol.property_years ) ) + '.';
			}
			items.push( alertItem( inj - dates.now < 90 * DAY ? 'soon' : 'info', 'Last day to sue, counted from the accident', line + ' Exceptions can change this, so confirm with a lawyer if it is close.' ) );
		}

		// Promises that have passed with nothing logged after them.
		store.entries.forEach( function ( e ) {
			var due = parseDate( e.due );
			if ( ! e.promise || due === null || due >= dates.now ) {
				return;
			}
			var later = store.entries.some( function ( o ) {
				return o.type !== 'sent' && o.type !== 'note' && parseDate( o.date ) >= due && o !== e;
			} );
			if ( ! later ) {
				items.push( alertItem( 'warn', 'Promise missed: ' + e.promise, 'Due ' + fmt( due ) + ' (logged ' + fmt( parseDate( e.date ) ) + '). Follow up in writing and name the date they gave you.' ) );
			}
		} );

		// Last message from you with no reply logged since.
		var sorted = sortedEntries();
		var lastOut = null;
		var replied = false;
		sorted.forEach( function ( e ) {
			if ( e.type === 'sent' || e.type === 'docs' ) {
				lastOut = e;
				replied = false;
			} else if ( e.type !== 'note' ) {
				replied = true;
			}
		} );
		if ( lastOut && ! replied ) {
			var waited = Math.round( ( dates.now - parseDate( lastOut.date ) ) / DAY );
			if ( waited > REPLY_DAYS ) {
				items.push( alertItem( 'warn', 'No reply logged for ' + waited + ' days', 'Your last message was on ' + fmt( parseDate( lastOut.date ) ) + '. The NAIC model rule many states follow expects a reply within ' + REPLY_DAYS + ' days to a communication that expects one. Send a written follow-up; if it is ignored, a complaint to your state insurance department is free.' ) );
			}
		}

		$( 'mat-diary-alerts-list' ).innerHTML = items.length ? '<ul class="mat-diary-alerts__list">' + items.join( '' ) + '</ul>' : '<p class="mat-field__hint">Add your claim dates to see deadlines here.</p>';
	}

	// ---- Entries ---------------------------------------------------------

	function sortedEntries() {
		return store.entries.slice().sort( function ( a, b ) {
			return ( parseDate( a.date ) - parseDate( b.date ) ) || ( a.id - b.id );
		} );
	}

	function renderList() {
		var list = $( 'mat-diary-list' );
		var html = '';
		sortedEntries().forEach( function ( e ) {
			html += '<li class="mat-diary-item" data-id="' + e.id + '">';
			html += '<p class="mat-diary-item__meta"><span>' + fmt( parseDate( e.date ) ) + '</span> · ' + MAT.escape( TYPES[ e.type ] || e.type ) + '</p>';
			if ( e.summary ) {
				html += '<p class="mat-diary-item__text">' + MAT.escape( e.summary ) + '</p>';
			}
			if ( e.promise ) {
				html += '<p class="mat-diary-item__promise">Promised: ' + MAT.escape( e.promise ) + ( e.due ? ' by ' + fmt( parseDate( e.due ) ) : '' ) + '</p>';
			}
			html += '<button type="button" class="mat-diary-item__delete" data-delete="' + e.id + '" aria-label="Delete this entry">Delete</button>';
			html += '</li>';
		} );
		list.innerHTML = html;
		$( 'mat-diary-empty' ).hidden = store.entries.length > 0;
	}

	function render() {
		renderList();
		renderAlerts();
	}

	function timelineText() {
		var c = store.claim;
		var lines = [];
		lines.push( 'Claim timeline' + ( c.claimno ? ', claim number ' + c.claimno : '' ) + ( c.insurer ? ', ' + c.insurer : '' ) );
		if ( c.adjuster ) {
			lines.push( 'Adjuster: ' + c.adjuster );
		}
		lines.push( '' );
		var dated = [];
		if ( c.loss ) {
			dated.push( [ parseDate( c.loss ), 'Accident.' ] );
		}
		if ( c.reported ) {
			dated.push( [ parseDate( c.reported ), 'Claim reported to the insurer.' ] );
		}
		if ( c.pol ) {
			dated.push( [ parseDate( c.pol ), 'Insurer received my proof of loss.' ] );
		}
		sortedEntries().forEach( function ( e ) {
			var text = ( TYPES[ e.type ] || e.type ) + '.';
			if ( e.summary ) {
				text += ' ' + e.summary;
			}
			if ( e.promise ) {
				text += ' Promised: ' + e.promise + ( e.due ? ' by ' + fmt( parseDate( e.due ) ) : '' ) + '.';
			}
			dated.push( [ parseDate( e.date ), text ] );
		} );
		dated.sort( function ( a, b ) { return a[0] - b[0]; } );
		dated.forEach( function ( d ) {
			lines.push( fmt( d[0] ) + ': ' + d[1].replace( /\s+/g, ' ' ).trim() );
		} );
		return lines.join( '\n' );
	}

	function status( msg ) {
		$( 'mat-diary-status' ).textContent = msg;
	}

	// ---- Wiring ----------------------------------------------------------

	load();
	CLAIM_FIELDS.forEach( function ( f ) {
		var el = $( 'mat-diary-' + f );
		if ( store.claim[ f ] !== undefined ) {
			el.value = store.claim[ f ];
		}
		el.addEventListener( 'change', function () {
			store.claim[ f ] = el.value.trim();
			save();
			renderAlerts();
		} );
	} );
	CLAIM_FIELDS.forEach( function ( f ) {
		store.claim[ f ] = $( 'mat-diary-' + f ).value.trim();
	} );
	$( 'mat-diary-claim' ).addEventListener( 'submit', function ( e ) {
		e.preventDefault();
	} );

	var dateEl = $( 'mat-diary-e-date' );
	if ( ! dateEl.value ) {
		dateEl.value = new Date( today() ).toISOString().slice( 0, 10 );
	}

	$( 'mat-diary-entry' ).addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		var err = $( 'mat-diary-e-error' );
		var summaryEl = $( 'mat-diary-e-summary' );
		var dueEl = $( 'mat-diary-e-due' );
		var promiseEl = $( 'mat-diary-e-promise' );
		[ dateEl, summaryEl, dueEl ].forEach( function ( el ) { el.removeAttribute( 'aria-invalid' ); } );
		var fail = function ( msg, el ) {
			err.textContent = msg;
			err.hidden = false;
			el.setAttribute( 'aria-invalid', 'true' );
			el.focus();
		};
		if ( parseDate( dateEl.value ) === null ) {
			return fail( 'Please enter the date.', dateEl );
		}
		if ( ! summaryEl.value.trim() && ! promiseEl.value.trim() ) {
			return fail( 'Please describe what was said or sent.', summaryEl );
		}
		if ( dueEl.value && parseDate( dueEl.value ) === null ) {
			return fail( 'Please enter a valid "promised by" date, or leave it blank.', dueEl );
		}
		err.hidden = true;
		store.entries.push( {
			id: Date.now(),
			date: dateEl.value,
			type: $( 'mat-diary-e-type' ).value,
			summary: summaryEl.value.trim(),
			promise: promiseEl.value.trim(),
			due: dueEl.value,
		} );
		save();
		render();
		summaryEl.value = '';
		promiseEl.value = '';
		dueEl.value = '';
		status( 'Entry added.' );
	} );

	$( 'mat-diary-list' ).addEventListener( 'click', function ( e ) {
		var id = e.target.getAttribute && e.target.getAttribute( 'data-delete' );
		if ( ! id ) {
			return;
		}
		store.entries = store.entries.filter( function ( x ) { return String( x.id ) !== id; } );
		save();
		render();
		status( 'Entry deleted.' );
	} );

	$( 'mat-diary-copy' ).addEventListener( 'click', function () {
		var text = timelineText();
		var area = $( 'mat-diary-timeline' );
		area.value = text;
		area.hidden = false;
		var selectIt = function () {
			area.focus();
			area.select();
			status( 'Timeline selected below: copy it with Ctrl+C or Cmd+C.' );
		};
		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( text ).then( function () { status( 'Timeline copied.' ); }, selectIt );
		} else {
			selectIt();
		}
	} );

	$( 'mat-diary-complaint' ).addEventListener( 'click', function () {
		if ( ! letterUrl ) {
			return;
		}
		try {
			sessionStorage.setItem( 'mat_diary_timeline', timelineText() );
		} catch ( err ) { /* the letter just won't be prefilled */ }
		window.location.href = letterUrl;
	} );

	$( 'mat-diary-backup' ).addEventListener( 'click', function () {
		var blob = new Blob( [ JSON.stringify( store, null, 2 ) ], { type: 'application/json' } );
		var a = document.createElement( 'a' );
		a.href = URL.createObjectURL( blob );
		a.download = 'claim-diary' + ( store.claim.claimno ? '-' + store.claim.claimno.replace( /[^a-z0-9-]+/gi, '' ) : '' ) + '.json';
		document.body.appendChild( a );
		a.click();
		setTimeout( function () {
			URL.revokeObjectURL( a.href );
			a.remove();
		}, 0 );
		status( 'Backup downloaded.' );
	} );

	$( 'mat-diary-import' ).addEventListener( 'change', function () {
		var file = this.files && this.files[0];
		if ( ! file ) {
			return;
		}
		var reader = new FileReader();
		reader.onload = function () {
			try {
				var data = JSON.parse( reader.result );
				if ( ! data || ! Array.isArray( data.entries ) ) {
					throw new Error( 'bad' );
				}
				store = { claim: data.claim || {}, entries: data.entries.filter( function ( e ) { return e && parseDate( e.date ) !== null; } ) };
				CLAIM_FIELDS.forEach( function ( f ) {
					$( 'mat-diary-' + f ).value = store.claim[ f ] || ( f === 'party' ? 'own' : '' );
				} );
				save();
				render();
				status( 'Backup restored: ' + store.entries.length + ' entries.' );
			} catch ( err ) {
				status( 'That file isn\'t a claim diary backup.' );
			}
		};
		reader.readAsText( file );
		this.value = '';
	} );

	$( 'mat-diary-clear' ).addEventListener( 'click', function () {
		$( 'mat-diary-confirm' ).hidden = false;
		$( 'mat-diary-confirm-no' ).focus();
	} );
	$( 'mat-diary-confirm-no' ).addEventListener( 'click', function () {
		$( 'mat-diary-confirm' ).hidden = true;
	} );
	$( 'mat-diary-confirm-yes' ).addEventListener( 'click', function () {
		store = { claim: { party: 'own' }, entries: [] };
		CLAIM_FIELDS.forEach( function ( f ) {
			$( 'mat-diary-' + f ).value = f === 'party' ? 'own' : '';
		} );
		save();
		render();
		$( 'mat-diary-confirm' ).hidden = true;
		status( 'Diary cleared.' );
	} );

	render();
	if ( ! storageOk ) {
		save();
	}
})();
