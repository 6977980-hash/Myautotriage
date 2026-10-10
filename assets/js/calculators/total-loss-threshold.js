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
	var hubUrl = form.getAttribute( 'data-hub-url' );
	var gapUrl = form.getAttribute( 'data-gap-url' );
	var stateData = null;
	var usd = MAT.usd;
	var esc = MAT.escape;

	fetch( dataUrl ).then( function ( r ) { return r.json(); } ).then( function ( json ) {
		stateData = json;
		var select = document.getElementById( 'mat-tlt-state' );
		select.options[0].textContent = 'Choose your state';
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
		MAT.showError( resultBox, 'Could not load state data. Please refresh the page.' );
	} );

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		if ( ! stateData ) {
			return;
		}

		var select = document.getElementById( 'mat-tlt-state' );
		var stateCode = select.value;
		if ( ! stateCode ) {
			select.setAttribute( 'aria-invalid', 'true' );
			MAT.showError( resultBox, { error: 'Please choose your state.', field: select } );
			return;
		}
		select.removeAttribute( 'aria-invalid' );

		var r = MAT.nums( {
			acv: [ 'mat-tlt-acv', { label: 'The vehicle value', required: true } ],
			repair: [ 'mat-tlt-repair', { label: 'The repair estimate', required: true } ],
			salvage: [ 'mat-tlt-salvage', { label: 'The salvage value' } ],
		} );
		if ( r.error ) {
			MAT.showError( resultBox, r );
			return;
		}
		var acv = r.values.acv;
		var repair = r.values.repair;
		var salvageGiven = r.values.salvage !== null;
		if ( salvageGiven && r.values.salvage >= acv ) {
			MAT.showError( resultBox, { error: 'The salvage value should be less than the vehicle\'s value.', field: document.getElementById( 'mat-tlt-salvage' ) } );
			return;
		}
		var salvage = salvageGiven ? r.values.salvage : acv * 0.18; // default estimate ~18% of ACV

		var state = stateData.states[ stateCode ];
		var html = '';
		var isTotal = false;
		var ratio = repair / acv;

		if ( state.type === 'percentage' ) {
			isTotal = ratio >= state.threshold;
			html += '<p>' + esc( state.name ) + ' uses a <strong>' + MAT.pct( state.threshold ) + ' threshold</strong>: your car is a total loss if repair costs reach ' + MAT.pct( state.threshold ) + ' of its value.</p>';
			html += '<p>Your repair estimate is <strong>' + MAT.pct( ratio ) + '</strong> of the vehicle value (' + usd( repair ) + ' of ' + usd( acv ) + ').</p>';
			if ( ! isTotal ) {
				html += '<p>Repairs would need to reach ' + usd( Math.ceil( acv * state.threshold ) ) + ' for the threshold to apply.</p>';
			}
		} else {
			var combined = repair + salvage;
			isTotal = combined >= acv;
			if ( state.type === 'formula' ) {
				html += '<p>' + esc( state.name ) + ' uses the <strong>Total Loss Formula</strong>: your car is a total loss if repair cost + estimated salvage value reaches or exceeds its full value.</p>';
			} else {
				html += '<p>' + esc( state.name ) + ' law sets <strong>no fixed percentage</strong>: the insurer totals a car when it decides repairs are uneconomical. Most insurers use the Total Loss Formula (repair cost + salvage value vs. the car\'s value), so that test is shown here.</p>';
			}
			html += '<p>Repair estimate (' + usd( repair ) + ') + estimated salvage value (' + usd( Math.round( salvage ) ) + ( salvageGiven ? '' : ', a default 18% estimate' ) + ') = <strong>' + usd( Math.round( combined ) ) + '</strong> vs. a vehicle value of ' + usd( acv ) + '.</p>';
		}

		if ( state.note ) {
			html += '<p>' + esc( state.note ) + '</p>';
		}
		if ( state.citation ) {
			var cite = state.source_url
				? '<a href="' + esc( state.source_url ) + '" rel="noopener" target="_blank">' + esc( state.citation ) + '</a>'
				: esc( state.citation );
			html += '<p style="font-size:.9rem;">' + ( state.type === 'insurer' ? 'Law: ' : 'Rule: ' ) + cite + '.</p>';
		}

		var links = [];
		if ( hubUrl ) {
			links.push( '<a href="' + esc( hubUrl + MAT.slug( state.name ) + '/' ) + '">' + esc( state.name ) + ' claim deadlines and total loss rules</a>' );
		}
		if ( isTotal && gapUrl ) {
			links.push( '<a href="' + esc( gapUrl ) + '">Still owe on a loan? Check your GAP shortfall</a>' );
		}

		resultBox.className = 'mat-result-box';
		MAT.showResult( resultBox,
			'<p class="mat-result-box__figure">' + ( isTotal ? 'Likely a total loss' : 'Likely repairable' ) + '</p>' +
			html +
			'<p style="font-size:.9rem;">This is an estimate based on published state rules. Your insurer makes the final call using its own valuation and repair estimate, which can differ from yours.</p>' +
			( links.length ? '<p style="margin-bottom:0;">' + links.join( '<br>' ) + '</p>' : '' ) );
	} );
})();
