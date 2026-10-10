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
	var letterUrl = form.getAttribute( 'data-letter-url' );

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

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();

		var r = MAT.nums( {
			value: [ 'mat-dv-value', { label: 'The pre-accident value', required: true } ],
			miles: [ 'mat-dv-mileage', { label: 'The mileage', required: true } ],
		} );
		if ( r.error ) {
			MAT.showError( resultBox, r );
			return;
		}
		var value = r.values.value;
		var miles = r.values.miles;
		var damage = DAMAGE_MULTIPLIERS[ document.getElementById( 'mat-dv-damage' ).value ] || DAMAGE_MULTIPLIERS.moderate;
		var mileageMult = mileageMultiplier( miles );
		var baseCapped = value * 0.10;
		var diminishedValue = Math.round( baseCapped * damage.value * mileageMult );

		var html = '';
		html += '<p class="mat-result-box__figure">' + MAT.usd( diminishedValue ) + '</p>';
		html += '<p>Estimated diminished value using the 17c formula: ' + MAT.usd( baseCapped ) + ' (10% of the car\'s value) &times; ' + MAT.pct( damage.value ) + ' damage factor &times; ' + MAT.pct( mileageMult ) + ' mileage factor.</p>';
		if ( mileageMult === 0 ) {
			html += '<p>The 17c formula gives nothing at 100,000 miles or more. An independent appraisal based on comparable sales is the only way to show a loss on a high-mileage car.</p>';
		}
		html += '<p style="font-size:.9rem;">Many public adjusters and independent appraisers argue the true diminished value on a well-documented claim is higher than this baseline, so use it as your starting point, not your ceiling. If the car already had accident history, expect the insurer to argue for less; there is no standard deduction for that.</p>';
		if ( letterUrl && diminishedValue > 0 ) {
			html += '<p style="margin-bottom:0;"><a class="mat-btn mat-btn--accent" href="' + MAT.escape( letterUrl ) + '">Put ' + MAT.usd( diminishedValue ) + ' in a diminished value demand letter</a></p>';
		}

		MAT.showResult( resultBox, html );

		// Hand the figure to the demand-letter generator via sessionStorage so
		// a visitor who clicks through doesn't have to re-type it.
		try {
			sessionStorage.setItem( 'mat_dv_estimate', String( diminishedValue ) );
		} catch ( err ) { /* storage unavailable — non-fatal */ }
	} );
})();
