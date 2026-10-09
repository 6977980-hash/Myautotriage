/**
 * Total Loss Threshold Calculator.
 *
 * Percentage-threshold states: total loss when repair cost >= threshold% x ACV.
 * Total Loss Formula (TLF) states: total loss when repair cost + salvage value >= ACV.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-tlt-form' );
	if ( ! form ) {
		return;
	}
	var resultBox = document.getElementById( 'mat-tlt-result' );
	var dataUrl = form.getAttribute( 'data-json' );
	var stateData = null;

	fetch( dataUrl ).then( function ( r ) { return r.json(); } ).then( function ( json ) {
		stateData = json;
		var select = document.getElementById( 'mat-tlt-state' );
		var codes = Object.keys( json.states ).sort( function ( a, b ) {
			return json.states[ a ].name.localeCompare( json.states[ b ].name );
		} );
		codes.forEach( function ( code ) {
			var opt = document.createElement( 'option' );
			opt.value = code;
			opt.textContent = json.states[ code ].name;
			select.appendChild( opt );
		} );
	} ).catch( function () {
		resultBox.innerHTML = '<p role="alert">Could not load state data. Please refresh the page.</p>';
		resultBox.hidden = false;
	} );

	function formatUSD( n ) {
		return n.toLocaleString( 'en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 } );
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		if ( ! stateData ) {
			return;
		}

		var stateCode = document.getElementById( 'mat-tlt-state' ).value;
		var acv = parseFloat( document.getElementById( 'mat-tlt-acv' ).value );
		var repair = parseFloat( document.getElementById( 'mat-tlt-repair' ).value );
		var salvageInput = document.getElementById( 'mat-tlt-salvage' ).value;
		var salvage = salvageInput ? parseFloat( salvageInput ) : acv * 0.18; // default estimate ~18% of ACV

		if ( ! stateCode || isNaN( acv ) || acv <= 0 || isNaN( repair ) || repair < 0 ) {
			resultBox.innerHTML = '<p role="alert">Please choose a state and enter your vehicle value and repair estimate.</p>';
			resultBox.hidden = false;
			return;
		}

		var state = stateData.states[ stateCode ];
		var html = '';
		var isTotal = false;
		var ratio = repair / acv;

		if ( state.type === 'percentage' ) {
			isTotal = ratio >= state.threshold;
			html += '<p>' + state.name + ' uses a <strong>' + Math.round( state.threshold * 100 ) + '% threshold</strong>: your car is a total loss if repair costs reach ' + Math.round( state.threshold * 100 ) + '% of its value.</p>';
			html += '<p>Your repair estimate is <strong>' + Math.round( ratio * 100 ) + '%</strong> of the vehicle value (' + formatUSD( repair ) + ' of ' + formatUSD( acv ) + ').</p>';
		} else {
			var combined = repair + salvage;
			isTotal = combined >= acv;
			if ( state.type === 'formula' ) {
				html += '<p>' + state.name + ' uses the <strong>Total Loss Formula</strong>: your car is a total loss if repair cost + estimated salvage value reaches or exceeds its full value.</p>';
			} else {
				html += '<p>' + state.name + ' law sets <strong>no fixed percentage</strong>: the insurer totals a car when it decides repairs are uneconomical. Most insurers use the Total Loss Formula (repair cost + salvage value vs. the car\'s value), so that test is shown here.</p>';
			}
			html += '<p>Repair estimate (' + formatUSD( repair ) + ') + estimated salvage value (' + formatUSD( Math.round( salvage ) ) + ( salvageInput ? '' : ', a default 18% estimate' ) + ') = <strong>' + formatUSD( Math.round( combined ) ) + '</strong> vs. a vehicle value of ' + formatUSD( acv ) + '.</p>';
		}

		if ( state.note ) {
			html += '<p>' + state.note + '</p>';
		}

		resultBox.className = 'mat-result-box' + ( isTotal ? '' : '' );
		resultBox.innerHTML =
			'<p class="mat-result-box__figure">' + ( isTotal ? 'Likely a total loss' : 'Likely repairable' ) + '</p>' +
			html +
			'<p style="margin-bottom:0;font-size:.9rem;">This is an estimate based on published state rules — your insurer makes the final call using its own valuation and repair estimate, which can differ from yours.</p>';
		resultBox.hidden = false;
		resultBox.setAttribute( 'tabindex', '-1' );
		resultBox.focus();
	} );
})();
