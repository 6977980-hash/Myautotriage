/**
 * Diminished Value (17c) Calculator.
 *
 * Implements the "17c formula" that originated in Mabry v. State Farm and is
 * still the baseline most insurers and appraisers start from:
 *
 *   Diminished Value = Vehicle Value x 10% cap x Damage Multiplier x Mileage Multiplier
 *
 * This is a floor, not a ceiling — many public adjusters argue actual
 * diminished value is higher, especially on newer / low-mileage vehicles.
 * The UI says so; this file just does the arithmetic.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-dv-form' );
	if ( ! form ) {
		return;
	}

	var resultBox = document.getElementById( 'mat-dv-result' );

	var DAMAGE_MULTIPLIERS = {
		severe: { label: 'Severe structural / frame damage', value: 1.0 },
		major: { label: 'Major damage to structure and panels', value: 0.75 },
		moderate: { label: 'Moderate damage to structure and panels', value: 0.5 },
		minor: { label: 'Minor damage, panels only (no structural damage)', value: 0.25 },
		cosmetic: { label: 'Cosmetic only (bumper, paint, minor panel)', value: 0.125 },
	};

	function mileageMultiplier( miles ) {
		if ( miles < 20000 ) return 1.0;
		if ( miles < 40000 ) return 0.8;
		if ( miles < 60000 ) return 0.6;
		if ( miles < 80000 ) return 0.4;
		if ( miles < 100000 ) return 0.2;
		return 0;
	}

	function formatUSD( n ) {
		return n.toLocaleString( 'en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 } );
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();

		var value = parseFloat( document.getElementById( 'mat-dv-value' ).value );
		var damageKey = document.getElementById( 'mat-dv-damage' ).value;
		var miles = parseFloat( document.getElementById( 'mat-dv-mileage' ).value );
		var priorDamage = document.getElementById( 'mat-dv-prior' ).checked;

		if ( isNaN( value ) || value <= 0 || isNaN( miles ) || miles < 0 ) {
			resultBox.innerHTML = '<p role="alert">Please enter a valid pre-accident value and mileage.</p>';
			resultBox.hidden = false;
			return;
		}

		var damage = DAMAGE_MULTIPLIERS[ damageKey ] || DAMAGE_MULTIPLIERS.moderate;
		var mileageMult = mileageMultiplier( miles );
		var baseCapped = value * 0.10;
		var diminishedValue = baseCapped * damage.value * mileageMult;

		if ( priorDamage ) {
			diminishedValue *= 0.5; // Insurers commonly halve DV when there is unrelated prior damage/repair history.
		}

		diminishedValue = Math.round( diminishedValue );

		var html = '';
		html += '<p class="mat-result-box__figure">' + formatUSD( diminishedValue ) + '</p>';
		html += '<p>Estimated diminished value using the 17c formula (10% value cap &times; ' + Math.round( damage.value * 100 ) + '% damage factor &times; ' + Math.round( mileageMult * 100 ) + '% mileage factor' + ( priorDamage ? ' &times; 50% prior-damage adjustment' : '' ) + ').</p>';
		html += '<p style="margin-bottom:0;font-size:.9rem;">10% cap of vehicle value: ' + formatUSD( baseCapped ) + '. Many public adjusters and independent appraisers argue the true diminished value on a well-documented claim is higher than this baseline — use this number as your starting point, not your ceiling.</p>';

		resultBox.innerHTML = html;
		resultBox.hidden = false;
		resultBox.setAttribute( 'tabindex', '-1' );
		resultBox.focus();

		// Hand the figure to the demand-letter generator via sessionStorage so
		// a visitor who clicks through doesn't have to re-type it.
		try {
			sessionStorage.setItem( 'mat_dv_estimate', String( diminishedValue ) );
		} catch ( err ) { /* storage unavailable — non-fatal */ }
	} );
})();
