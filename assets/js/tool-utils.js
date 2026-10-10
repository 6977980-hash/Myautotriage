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

	function showResult( box, html ) {
		box.innerHTML = html;
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

	return { num: num, nums: nums, showError: showError, showResult: showResult, usd: usd, pct: pct, longDate: longDate, slug: slug, escape: escape };
}() );
