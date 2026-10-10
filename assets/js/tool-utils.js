/**
 * Small helpers shared by the calculators and letter generators: number
 * fields that respect their min/max, readable money/percent/date output,
 * and one way of showing a form error. The forms use novalidate so the
 * error text is ours (and consistent), which means the limits in the HTML
 * have to be checked here.
 */
window.MAT = ( function () {
	'use strict';

	/**
	 * Read a number input. Returns { value } or { error }.
	 * opts: label, required, fallback (used when blank), integer.
	 * min/max come from the input's own attributes.
	 */
	function num( id, opts ) {
		opts = opts || {};
		var el = document.getElementById( id );
		var raw = el ? String( el.value ).trim() : '';
		var label = opts.label || 'This field';
		if ( el ) {
			el.removeAttribute( 'aria-invalid' );
		}
		if ( raw === '' ) {
			if ( opts.required ) {
				return fail( el, 'Please enter ' + label.toLowerCase() + '.' );
			}
			return { value: opts.fallback === undefined ? null : opts.fallback };
		}
		var value = Number( raw );
		if ( ! isFinite( value ) ) {
			return fail( el, label + ' must be a number.' );
		}
		if ( opts.integer && Math.floor( value ) !== value ) {
			return fail( el, label + ' must be a whole number.' );
		}
		var min = el && el.hasAttribute( 'min' ) ? Number( el.getAttribute( 'min' ) ) : null;
		var max = el && el.hasAttribute( 'max' ) ? Number( el.getAttribute( 'max' ) ) : null;
		if ( min !== null && value < min ) {
			return fail( el, label + ' can\'t be less than ' + min + '.' );
		}
		if ( max !== null && value > max ) {
			return fail( el, label + ' can\'t be more than ' + max + '.' );
		}
		return { value: value };
	}

	function fail( el, message ) {
		if ( el ) {
			el.setAttribute( 'aria-invalid', 'true' );
		}
		return { error: message, field: el };
	}

	/**
	 * Read several fields at once. specs: { key: [ id, opts ] }.
	 * Returns { values } or { error, field } for the first bad field.
	 */
	function nums( specs ) {
		var values = {};
		var first = null;
		Object.keys( specs ).forEach( function ( key ) {
			var r = num( specs[ key ][0], specs[ key ][1] );
			if ( r.error ) {
				first = first || r;
			} else {
				values[ key ] = r.value;
			}
		} );
		return first || { values: values };
	}

	function showError( box, result ) {
		box.innerHTML = '<p role="alert">' + escape( result.error || result ) + '</p>';
		box.hidden = false;
		if ( result.field ) {
			result.field.focus();
		}
	}

	// Calculator forms whose inputs can go in a shareable link. Letter
	// generators are left out: their fields hold names and addresses.
	var SHAREABLE = [ 'mat-cd-form', 'mat-sol-form', 'mat-cf-form', 'mat-lu-form', 'mat-li-form', 'mat-ded-form', 'mat-dv-form', 'mat-gap-form', 'mat-rf-form', 'mat-tlt-form', 'mat-tlv-form' ];

	function shareableForm() {
		for ( var i = 0; i < SHAREABLE.length; i++ ) {
			var f = document.getElementById( SHAREABLE[ i ] );
			if ( f ) {
				return f;
			}
		}
		return null;
	}

	function formFields( form ) {
		return Array.prototype.filter.call( form.querySelectorAll( 'input[id], select[id]' ), function ( el ) {
			return el.type !== 'submit' && el.type !== 'button';
		} );
	}

	/** This page's URL with the form's filled-in fields as query args. */
	function shareUrl( form ) {
		var params = new URLSearchParams();
		formFields( form ).forEach( function ( el ) {
			var value = el.type === 'checkbox' ? ( el.checked ? '1' : '' ) : String( el.value ).trim();
			if ( value !== '' ) {
				params.set( el.id.replace( /^mat-/, '' ), value );
			}
		} );
		params.set( 'calc', '1' );
		return window.location.origin + window.location.pathname + '?' + params.toString();
	}

	function resultActions( box, form ) {
		var wrap = document.createElement( 'p' );
		wrap.className = 'mat-result-actions';
		var print = document.createElement( 'button' );
		print.type = 'button';
		print.className = 'mat-btn mat-btn--ghost mat-btn--sm';
		print.textContent = 'Print result';
		print.addEventListener( 'click', function () {
			document.body.classList.add( 'mat-print-result' );
			window.print();
		} );
		var copy = document.createElement( 'button' );
		copy.type = 'button';
		copy.className = 'mat-btn mat-btn--ghost mat-btn--sm';
		copy.textContent = 'Copy link to this result';
		copy.addEventListener( 'click', function () {
			var url = shareUrl( form );
			var done = function () {
				copy.textContent = 'Link copied';
				setTimeout( function () { copy.textContent = 'Copy link to this result'; }, 1800 );
			};
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( url ).then( done, function () { window.prompt( 'Copy this link:', url ); } );
			} else {
				window.prompt( 'Copy this link:', url );
			}
		} );
		wrap.appendChild( print );
		wrap.appendChild( copy );
		box.appendChild( wrap );
	}

	window.addEventListener( 'afterprint', function () {
		document.body.classList.remove( 'mat-print-result' );
	} );

	/**
	 * Fill a calculator from its link (?gap-payoff=21000&calc=1) and run it.
	 * Also how one tool hands numbers to the next. Selects filled from JSON
	 * (the state list) may not have their options yet, so wait for them.
	 */
	function prefillFromUrl() {
		var form = shareableForm();
		if ( ! form || ! window.location.search ) {
			return;
		}
		var params = new URLSearchParams( window.location.search );
		var pending = [];
		formFields( form ).forEach( function ( el ) {
			var key = el.id.replace( /^mat-/, '' );
			if ( ! params.has( key ) ) {
				return;
			}
			var value = params.get( key );
			if ( el.type === 'checkbox' ) {
				el.checked = value === '1';
			} else if ( el.tagName === 'SELECT' && ! el.querySelector( 'option[value="' + value.replace( /"/g, '' ) + '"]' ) ) {
				pending.push( [ el, value ] );
			} else {
				el.value = value;
			}
			var details = el.closest( 'details' );
			if ( details ) {
				details.open = true;
			}
		} );
		if ( params.get( 'calc' ) !== '1' ) {
			return;
		}
		var tries = 0;
		( function run() {
			pending = pending.filter( function ( p ) {
				if ( p[0].querySelector( 'option[value="' + p[1].replace( /"/g, '' ) + '"]' ) ) {
					p[0].value = p[1];
					return false;
				}
				return true;
			} );
			if ( pending.length && tries++ < 30 ) {
				setTimeout( run, 100 );
				return;
			}
			if ( form.requestSubmit ) {
				form.requestSubmit();
			} else {
				form.dispatchEvent( new Event( 'submit', { cancelable: true } ) );
			}
		}() );
	}

	if ( document.readyState === 'complete' ) {
		setTimeout( prefillFromUrl, 0 );
	} else {
		window.addEventListener( 'load', prefillFromUrl );
	}

	/**
	 * Link to another calculator with some of its fields filled in. With
	 * run, the calculator also runs on arrival (only when every required
	 * field is in the link).
	 */
	function toolLink( url, fields, run ) {
		var params = new URLSearchParams();
		Object.keys( fields ).forEach( function ( k ) {
			if ( fields[ k ] !== null && fields[ k ] !== undefined && fields[ k ] !== '' ) {
				params.set( k, fields[ k ] );
			}
		} );
		if ( run ) {
			params.set( 'calc', '1' );
		}
		return url + ( url.indexOf( '?' ) === -1 ? '?' : '&' ) + params.toString();
	}

	function showResult( box, html ) {
		box.innerHTML = html;
		var form = shareableForm();
		if ( form && box.id.indexOf( form.id.replace( /-form$/, '' ) + '-' ) === 0 ) {
			resultActions( box, form );
		}
		box.hidden = false;
		box.setAttribute( 'tabindex', '-1' );
		box.focus();
	}

	function usd( n, cents ) {
		return Number( n ).toLocaleString( 'en-US', {
			style: 'currency',
			currency: 'USD',
			minimumFractionDigits: cents ? 2 : 0,
			maximumFractionDigits: cents ? 2 : 0,
		} );
	}

	/**
	 * 0.125 -> "12.5%", 0.75 -> "75%". Never rounds a value up across a
	 * threshold: 0.7496 shows as "74.9%", not "75%".
	 */
	function pct( ratio ) {
		var tenths = Math.floor( ratio * 1000 + 1e-9 ) / 10;
		return ( tenths % 1 === 0 ? tenths.toFixed( 0 ) : tenths.toFixed( 1 ) ) + '%';
	}

	/** "2026-03-05" -> "March 5, 2026"; anything else is returned as-is. */
	function longDate( value ) {
		var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec( value || '' );
		if ( ! m ) {
			return value;
		}
		var d = new Date( Date.UTC( +m[1], +m[2] - 1, +m[3] ) );
		return d.toLocaleDateString( 'en-US', { year: 'numeric', month: 'long', day: 'numeric', timeZone: 'UTC' } );
	}

	/** Same slug WordPress builds for a state page (sanitize_title). */
	function slug( name ) {
		return String( name ).toLowerCase().replace( /[^a-z0-9]+/g, '-' ).replace( /^-+|-+$/g, '' );
	}

	function escape( s ) {
		return String( s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	return { num: num, nums: nums, showError: showError, showResult: showResult, usd: usd, pct: pct, longDate: longDate, slug: slug, escape: escape, toolLink: toolLink };
}() );
