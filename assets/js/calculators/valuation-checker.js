/**
 * Total Loss Valuation Checker.
 *
 * Checks the numbers from an insurer's total loss valuation report against
 * the NAIC model rule (Unfair Property/Casualty Claims Settlement Practices
 * Model Regulation, Section 8A): two or more comparable cars from the local
 * market, taxes and transfer fees included, and every deduction itemized.
 *
 * Money "in question" is only counted where the report itself shows an
 * amount: the comparables' negotiation adjustments (or the gap to the
 * visitor's own listings, never both), condition and other deductions, and
 * missing sales tax when a tax rate is given.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-tlv-form' );
	if ( ! form ) {
		return;
	}
	var resultBox = document.getElementById( 'mat-tlv-result' );
	var errorBox = document.getElementById( 'mat-tlv-error' );
	var letterUrl = form.getAttribute( 'data-letter-url' );
	var states = {};
	try {
		states = JSON.parse( form.getAttribute( 'data-states' ) || '{}' );
	} catch ( err ) { /* no state links */ }

	var FAR_MILES = 100;
	var MILEAGE_GAP = 0.2;
	var COMPS = 5;

	function fail( message, el ) {
		errorBox.textContent = message;
		errorBox.hidden = false;
		if ( el ) {
			el.setAttribute( 'aria-invalid', 'true' );
			el.focus();
		}
	}

	function readComps() {
		var comps = [];
		for ( var i = 1; i <= COMPS; i++ ) {
			var label = 'Comparable ' + i + '\'s ';
			var r = MAT.nums( {
				price: [ 'mat-tlv-c' + i + '-price', { label: label + 'advertised price' } ],
				adj: [ 'mat-tlv-c' + i + '-adj', { label: label + 'adjustment', fallback: 0 } ],
				miles: [ 'mat-tlv-c' + i + '-miles', { label: label + 'distance', integer: true } ],
				odo: [ 'mat-tlv-c' + i + '-odo', { label: label + 'mileage', integer: true } ],
			} );
			if ( r.error ) {
				return r;
			}
			var diff = document.getElementById( 'mat-tlv-c' + i + '-diff' ).checked;
			var v = r.values;
			if ( v.price === null ) {
				if ( v.adj || v.miles !== null || v.odo !== null || diff ) {
					return { error: 'Please enter comparable ' + i + '\'s advertised price, or clear its other boxes.', field: document.getElementById( 'mat-tlv-c' + i + '-price' ) };
				}
				continue;
			}
			if ( v.adj >= v.price ) {
				return { error: 'Comparable ' + i + '\'s adjustment must be less than its advertised price.', field: document.getElementById( 'mat-tlv-c' + i + '-adj' ) };
			}
			comps.push( { n: i, price: v.price, adj: v.adj, miles: v.miles, odo: v.odo, diff: diff } );
		}
		return { comps: comps };
	}

	function avg( list ) {
		return list.reduce( function ( a, b ) { return a + b; }, 0 ) / list.length;
	}

	function plural( n, one, many ) {
		return n + ' ' + ( n === 1 ? one : many );
	}

	/** Same as plural(), but a sentence in the letter reads "One car", not "1 car". */
	function sentencePlural( n, one, many ) {
		return n === 1 ? 'One ' + one : plural( n, one, many );
	}

	function numberList( comps ) {
		return comps.map( function ( c ) { return '#' + c.n; } ).join( ', ' );
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		errorBox.hidden = true;
		var r = MAT.nums( {
			base: [ 'mat-tlv-base', { label: 'The base or market value', required: true } ],
			condition: [ 'mat-tlv-condition', { label: 'The condition adjustment', fallback: 0 } ],
			other: [ 'mat-tlv-other', { label: 'Other deductions', fallback: 0 } ],
			deductible: [ 'mat-tlv-deductible', { label: 'Your deductible', fallback: 0 } ],
			tax: [ 'mat-tlv-tax', { label: 'The sales tax included', fallback: 0 } ],
			fees: [ 'mat-tlv-fees', { label: 'The title and registration fees', fallback: 0 } ],
			offer: [ 'mat-tlv-offer', { label: 'The final amount offered' } ],
			taxRate: [ 'mat-tlv-taxrate', { label: 'The sales tax rate' } ],
			mileage: [ 'mat-tlv-mileage', { label: 'Your car\'s mileage', integer: true } ],
			y1: [ 'mat-tlv-y1', { label: 'Listing 1 price' } ],
			y2: [ 'mat-tlv-y2', { label: 'Listing 2 price' } ],
			y3: [ 'mat-tlv-y3', { label: 'Listing 3 price' } ],
		} );
		if ( r.error ) {
			fail( r.error, r.field );
			return;
		}
		var c = readComps();
		if ( c.error ) {
			fail( c.error, c.field );
			return;
		}
		var v = r.values;
		var comps = c.comps;
		if ( v.condition + v.other >= v.base ) {
			fail( 'The deductions can\'t add up to more than the base value.', document.getElementById( 'mat-tlv-condition' ) );
			return;
		}

		var acv = v.base - v.condition - v.other;
		var computedNet = acv + v.tax + v.fees - v.deductible;
		var net = v.offer !== null ? v.offer : computedNet;
		var issues = [];
		var letterPoints = [];
		var valueGap = 0;
		var taxGap = 0;

		// Arithmetic: do the report's own numbers reach the amount offered?
		if ( v.offer !== null && Math.abs( v.offer - computedNet ) >= 1 ) {
			var diffAmt = computedNet - v.offer;
			issues.push( {
				title: 'The numbers don\'t add up to the offer',
				text: 'Base value minus deductions, plus tax and fees, minus your deductible comes to ' + MAT.usd( computedNet, true ) + ', but you were offered ' + MAT.usd( v.offer, true ) + ( diffAmt > 0 ? ': ' + MAT.usd( diffAmt, true ) + ' less than the report supports' : '' ) + '. Ask the adjuster to itemize every line between the base value and the payment.',
			} );
			letterPoints.push( 'The amount offered does not match the figures in your own valuation report, and I ask you to itemize every deduction between the base value and the payment.' );
		}

		// Comparables.
		var withYours = [ v.y1, v.y2, v.y3 ].filter( function ( x ) { return x !== null; } );
		var avgAdvertised = comps.length ? avg( comps.map( function ( x ) { return x.price; } ) ) : null;
		var avgAdj = comps.length ? avg( comps.map( function ( x ) { return x.adj; } ) ) : 0;
		if ( comps.length === 0 ) {
			issues.push( {
				ok: true,
				title: 'No comparables entered',
				text: 'Add the comparable cars from the report to check their prices, distance and adjustments.',
			} );
		} else if ( comps.length < 2 ) {
			issues.push( {
				title: 'Only one comparable car',
				text: 'The NAIC model rule bases a cash settlement on two or more comparable cars (Section 8A(2)). Ask how the value was set from a single car.',
			} );
			letterPoints.push( 'The valuation relies on only one comparable vehicle; I ask that it be based on at least two comparable vehicles available in my local market.' );
		}

		var adjusted = comps.filter( function ( x ) { return x.adj > 0; } );
		if ( adjusted.length ) {
			issues.push( {
				title: 'Negotiation adjustments cut ' + MAT.usd( avgAdj ) + ' a car on average',
				text: plural( adjusted.length, 'comparable was', 'comparables were' ) + ' valued below the advertised price (' + numberList( adjusted ) + '). You would have to pay the advertised price to replace your car. Ask for the data behind each "projected sold" or "typical negotiation" adjustment, or for the comparables to be valued at their advertised prices.',
				amount: withYours.length ? null : avgAdj,
			} );
			letterPoints.push( 'Your report reduced the advertised prices of the comparable vehicles by an average of ' + MAT.usd( avgAdj ) + ' through "projected sold" or negotiation adjustments. I would have to pay the advertised price to replace my vehicle, so please value the comparables at their advertised prices or provide the data supporting each adjustment.' );
			if ( ! withYours.length ) {
				valueGap = avgAdj;
			}
		}

		var far = comps.filter( function ( x ) { return x.miles !== null && x.miles > FAR_MILES; } );
		if ( far.length ) {
			issues.push( {
				title: plural( far.length, 'comparable is', 'comparables are' ) + ' more than ' + FAR_MILES + ' miles away',
				text: 'The model rule looks first to comparable cars in your local market area, and to nearby areas only when none are available locally (Section 8A(2)(a)-(b)). Ask why closer cars (' + numberList( far ) + ') weren\'t used, and send local listings if you find them.',
			} );
			letterPoints.push( sentencePlural( far.length, 'comparable vehicle is', 'comparable vehicles are' ) + ' more than ' + FAR_MILES + ' miles from my home, outside my local market area.' );
		}

		if ( v.mileage !== null && v.mileage > 0 ) {
			var higher = comps.filter( function ( x ) { return x.odo !== null && x.odo > v.mileage * ( 1 + MILEAGE_GAP ); } );
			if ( higher.length ) {
				issues.push( {
					title: plural( higher.length, 'comparable has', 'comparables have' ) + ' much higher mileage than your car',
					text: numberList( higher ) + ' ' + ( higher.length === 1 ? 'has' : 'have' ) + ' more than ' + Math.round( MILEAGE_GAP * 100 ) + '% more miles than yours. Check that the report added a mileage adjustment in your favor for ' + ( higher.length === 1 ? 'it' : 'each' ) + ', and how much.',
				} );
				letterPoints.push( 'Some comparable vehicles have substantially higher mileage than mine; please show the mileage adjustment applied to each.' );
			}
		}

		var diffs = comps.filter( function ( x ) { return x.diff; } );
		if ( diffs.length ) {
			issues.push( {
				title: plural( diffs.length, 'comparable doesn\'t match', 'comparables don\'t match' ) + ' your car',
				text: 'A comparable should be the same make with a similar body style, options and mileage (Section 8A(1)). Ask for ' + numberList( diffs ) + ' to be replaced with cars that match your trim and options, or for an adjustment for every difference.',
			} );
			letterPoints.push( sentencePlural( diffs.length, 'comparable vehicle differs', 'comparable vehicles differ' ) + ' from mine in trim, options, body style or model year, and should be replaced or adjusted.' );
		}

		if ( withYours.length ) {
			var avgYours = avg( withYours );
			valueGap = Math.max( 0, avgYours - v.base );
			issues.push( {
				ok: valueGap === 0,
				title: valueGap > 0 ? 'Your listings average ' + MAT.usd( valueGap ) + ' more than the report\'s base value' : 'Your listings don\'t beat the report\'s base value',
				text: valueGap > 0
					? 'Your ' + plural( withYours.length, 'listing averages', 'listings average' ) + ' ' + MAT.usd( avgYours ) + ' against a base value of ' + MAT.usd( v.base ) + '. Enclose the listings with your letter. This replaces the negotiation adjustments above in the total, so nothing is counted twice.'
					: 'Your listings average ' + MAT.usd( avgYours ) + '. Look for listings closer to your car\'s trim and mileage, or lean on the other points here.',
				amount: valueGap > 0 ? valueGap : null,
			} );
			if ( valueGap > 0 ) {
				letterPoints.push( 'Comparable vehicles for sale near me, listings enclosed, average ' + MAT.usd( avgYours ) + ', which is ' + MAT.usd( valueGap ) + ' more than your base value.' );
			}
		}

		// Deductions.
		if ( v.condition > 0 ) {
			issues.push( {
				title: 'Condition adjustment of ' + MAT.usd( v.condition ),
				text: 'Deductions must be "measurable, discernible, itemized and specified as to dollar amount" (Section 8A(3)). Ask who rated the condition, how, and which photos support each rating, and send maintenance records and photos from before the loss.',
				amount: v.condition,
			} );
			letterPoints.push( 'You deducted ' + MAT.usd( v.condition ) + ' for condition. Please itemize this deduction and provide the inspection notes and photographs supporting each condition rating; my maintenance records and photographs from before the loss are enclosed.' );
		}
		if ( v.other > 0 ) {
			issues.push( {
				title: 'Other deductions of ' + MAT.usd( v.other ),
				text: 'Ask for each deduction to be itemized with its dollar amount and the evidence for it, such as the record of the prior damage it relates to.',
				amount: v.other,
			} );
			letterPoints.push( 'Please itemize the other deductions of ' + MAT.usd( v.other ) + ' and provide the evidence for each.' );
		}

		// Taxes and fees.
		var expectedTax = v.taxRate !== null ? acv * v.taxRate / 100 : null;
		if ( expectedTax !== null && v.tax < expectedTax - 1 ) {
			taxGap = expectedTax - v.tax;
			issues.push( {
				title: v.tax > 0 ? 'Sales tax looks short by ' + MAT.usd( taxGap ) : 'No sales tax included',
				text: 'At ' + v.taxRate + '%, tax on ' + MAT.usd( acv ) + ' is ' + MAT.usd( expectedTax ) + '. The model rule includes all applicable taxes in a cash settlement (Section 8A(2)). Some states pay tax only once you buy a replacement, so check your state\'s rule.',
				amount: taxGap,
			} );
			letterPoints.push( 'The settlement ' + ( v.tax > 0 ? 'under-states' : 'does not include' ) + ' the sales tax I will pay on a comparable vehicle (' + MAT.usd( expectedTax ) + ' at ' + v.taxRate + '%).' );
		} else if ( v.tax === 0 && expectedTax === null ) {
			issues.push( {
				title: 'No sales tax included',
				text: 'The model rule includes all applicable taxes in a cash settlement (Section 8A(2)). Enter your local tax rate above to see how much, and check whether your state pays it up front or after you buy a replacement.',
			} );
			letterPoints.push( 'The settlement does not include the sales tax I will pay on a comparable vehicle.' );
		}
		if ( v.fees === 0 ) {
			issues.push( {
				title: 'No title or registration fees included',
				text: 'The model rule also includes license fees and other fees to transfer ownership of a comparable car (Section 8A(2)). Ask for your state\'s title and registration fees to be added.',
			} );
			letterPoints.push( 'The settlement does not include the title, registration and other fees to transfer ownership of a comparable vehicle.' );
		}

		var inQuestion = valueGap + v.condition + v.other + taxGap;
		var fairAcv = v.base + valueGap;
		var counter = net + inQuestion;
		var problems = issues.filter( function ( i ) { return ! i.ok; } ).length;

		var html = '';
		if ( inQuestion > 0 ) {
			html += '<p class="mat-result-box__figure">Up to ' + MAT.usd( inQuestion ) + ' in question</p>';
		} else {
			html += '<p class="mat-result-box__figure">' + ( problems ? plural( problems, 'issue', 'issues' ) + ' to raise' : 'No problems found' ) + '</p>';
		}
		html += '<table class="mat-result-table"><tbody>';
		html += '<tr><td>Base value in the report</td><td>' + MAT.usd( v.base, true ) + '</td></tr>';
		if ( v.condition + v.other > 0 ) {
			html += '<tr><td>Deductions (condition and other)</td><td>−' + MAT.usd( v.condition + v.other, true ) + '</td></tr>';
		}
		html += '<tr><td>Tax and fees included</td><td>' + MAT.usd( v.tax + v.fees, true ) + '</td></tr>';
		if ( v.deductible > 0 ) {
			html += '<tr><td>Your deductible</td><td>−' + MAT.usd( v.deductible, true ) + '</td></tr>';
		}
		html += '<tr><td>' + ( v.offer !== null ? 'Amount offered' : 'Payment these figures produce' ) + '</td><td>' + MAT.usd( net, true ) + '</td></tr>';
		if ( avgAdvertised !== null ) {
			html += '<tr><td>Comparables\' average advertised price</td><td>' + MAT.usd( avgAdvertised ) + '</td></tr>';
		}
		if ( inQuestion > 0 ) {
			html += '<tr class="mat-result-table__total"><td>Counter-offer if every point is accepted</td><td>' + MAT.usd( counter ) + '</td></tr>';
		}
		html += '</tbody></table>';
		if ( inQuestion > 0 ) {
			html += '<p style="font-size:.9rem;">That is the most these points support. The insurer may justify some deductions, so treat it as your opening position' + ( v.fees === 0 ? ', and it does not yet include title and registration fees' : '' ) + '.</p>';
		}

		html += '<ul class="mat-tlv-issues">';
		issues.forEach( function ( i ) {
			html += '<li' + ( i.ok ? ' class="is-ok"' : '' ) + '><strong>' + MAT.escape( i.title ) + ( i.amount ? ' · ' + MAT.usd( i.amount ) : '' ) + '</strong><p>' + MAT.escape( i.text ) + '</p></li>';
		} );
		html += '<li class="is-ok"><strong>Can\'t buy a comparable car for the amount paid?</strong><p>Under the model rule, telling the insurer within 35 days of receiving the payment that you can\'t buy a comparable car for that amount should reopen the claim (Section 8A(2)(e)).</p></li>';
		html += '</ul>';

		var state = states[ document.getElementById( 'mat-tlv-state' ).value ];
		if ( state && state.url ) {
			html += '<p><a href="' + MAT.escape( state.url ) + '">' + MAT.escape( state.name ) + ' claim laws: total loss rule, deadlines and where to complain</a></p>';
		}

		if ( letterUrl && problems ) {
			try {
				sessionStorage.setItem( 'mat_tlv_amount', String( Math.round( fairAcv ) ) );
				sessionStorage.setItem( 'mat_tlv_narrative', letterPoints.join( ' ' ) );
			} catch ( err ) { /* the letter just won't be prefilled */ }
			html += '<p style="margin-bottom:0;"><a class="mat-btn mat-btn--primary" href="' + MAT.escape( letterUrl ) + '">Turn this into a counter-offer letter</a></p>';
			html += '<p style="font-size:.85rem;">The letter asks for a value of ' + MAT.usd( fairAcv ) + ' plus tax and fees, and lists the points above.</p>';
		}
		MAT.showResult( resultBox, html );
	} );
})();
