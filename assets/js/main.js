/**
 * Site-wide JS — navigation toggle only. Deliberately tiny: calculators and
 * generators load their own small scripts per page (see functions.php),
 * so a visitor reading a blog post never pays for code they don't use.
 */
(function () {
	'use strict';

	var toggle = document.querySelector( '.mat-nav-toggle' );
	var nav = document.getElementById( 'mat-primary-menu' );

	if ( toggle && nav ) {
		toggle.addEventListener( 'click', function () {
			var expanded = toggle.getAttribute( 'aria-expanded' ) === 'true';
			toggle.setAttribute( 'aria-expanded', String( ! expanded ) );
			nav.classList.toggle( 'is-open' );
			document.body.classList.toggle( 'mat-nav-open' );
		} );

		// Close the mobile menu when a link inside it is clicked.
		nav.addEventListener( 'click', function ( e ) {
			if ( e.target.tagName === 'A' ) {
				nav.classList.remove( 'is-open' );
				toggle.setAttribute( 'aria-expanded', 'false' );
				document.body.classList.remove( 'mat-nav-open' );
			}
		} );
	}

	// Mark the current nav link for styling + accessibility.
	var here = window.location.pathname.replace( /\/$/, '' );
	document.querySelectorAll( '.mat-menu a' ).forEach( function ( link ) {
		try {
			var linkPath = new URL( link.href ).pathname.replace( /\/$/, '' );
			if ( linkPath === here ) {
				link.setAttribute( 'aria-current', 'page' );
			}
		} catch ( err ) { /* ignore malformed hrefs */ }
	} );

	// "Copy" buttons: data-mat-copy names the element whose text to copy.
	document.querySelectorAll( '[data-mat-copy]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var el = document.getElementById( btn.getAttribute( 'data-mat-copy' ) );
			if ( ! el ) {
				return;
			}
			var label = btn.textContent;
			var done = function () {
				btn.textContent = 'Copied';
				setTimeout( function () { btn.textContent = label; }, 1800 );
			};
			var select = function () {
				var range = document.createRange();
				range.selectNodeContents( el );
				var sel = window.getSelection();
				sel.removeAllRanges();
				sel.addRange( range );
			};
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( el.textContent.trim() ).then( done, select );
			} else {
				select();
			}
		} );
	} );
})();
