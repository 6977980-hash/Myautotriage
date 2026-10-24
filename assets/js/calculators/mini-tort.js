/**
 * Michigan Mini-Tort Calculator.
 *
 * Claim = min(damage not covered by insurance, $3,000) x (1 - your fault),
 * and $0 above 50% fault or if your car was uninsured
 * (MCL 500.3135(3)(e) and (4)). The statute doesn't say whether the cap
 * applies before or after the fault cut; capping first is the lower,
 * safer reading, and the result says so.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-mt-form' );
	if ( ! form ) {
		return;
	}
	var resultBox = document.getElementById( 'mat-mt-result' );
	var errorBox = document.getElementById( 'mat-mt-error' );
	var letterUrl = form.getAttribute( 'data-letter-url' );
	var collisionEl = document.getElementById( 'mat-mt-collision' );
	var dedField = document.getElementById( 'mat-mt-deductible-field' );
	var CAP = 3000;

	function syncDeductible() {
		dedField.hidden = collisionEl.value !== 'yes';
	}
	collisionEl.addEventListener( 'change', syncDeductible );
	syncDeductible();

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		var hasCollision = collisionEl.value === 'yes';
		var r = MAT.nums( {
			repair: [ 'mat-mt-repair', { label: 'The repair cost', required: true } ],
			deductible: [ 'mat-mt-deductible', { label: 'Your deductible', required: hasCollision } ],
			fault: [ 'mat-mt-fault', { label: 'Your share of the fault', fallback: 0 } ],
		} );
		if ( r.error ) {
			errorBox.textContent = r.error;
			errorBox.hidden = false;
			r.field.focus();
			return;
		}
		errorBox.hidden = true;
		var v = r.values;
		var uncovered = hasCollision ? Math.min( v.deductible, v.repair ) : v.repair;
		var capped = Math.min( uncovered, CAP );
		var insured = document.getElementById( 'mat-mt-insured' ).value === 'yes';
		var barred = v.fault > 50 || ! insured;
		var claim = barred ? 0 : Math.round( capped * ( 100 - v.fault ) ) / 100;

		var html = '<p class="mat-result-box__figure">' + MAT.usd( claim, true ) + '</p>';
		if ( ! insured ) {
			html += '<p>You can\'t recover a mini-tort if your car was being driven without the no-fault insurance Michigan requires (MCL 500.3135(4)(e)).</p>';
		} else if ( v.fault > 50 ) {
			html += '<p>At more than 50% fault, Michigan doesn\'t allow a mini-tort award in your favour (MCL 500.3135(4)(a)).</p>';
		}
		html += '<table class="mat-result-table"><tbody>';
		html += '<tr><td>' + ( hasCollision ? 'Not paid by your insurance (your deductible)' : 'Not paid by your insurance (no collision coverage)' ) + '</td><td>' + MAT.usd( uncovered, true ) + '</td></tr>';
		if ( uncovered > CAP ) {
			html += '<tr><td>Mini-tort cap</td><td>' + MAT.usd( CAP ) + '</td></tr>';
		}
		if ( v.fault > 0 && ! barred ) {
			html += '<tr><td>Less your ' + v.fault + '% share of the fault</td><td>&minus;' + MAT.usd( capped - claim, true ) + '</td></tr>';
		}
		html += '<tr class="mat-result-table__total"><td>Mini-tort to claim from the at-fault driver</td><td>' + MAT.usd( claim, true ) + '</td></tr>';
		html += '</tbody></table>';
		if ( ! hasCollision && v.repair > CAP && ! barred ) {
			html += '<p style="font-size:.9rem;">The other ' + MAT.usd( v.repair - CAP, true ) + ' of the damage can\'t be recovered from the at-fault driver in Michigan; only collision coverage on your own policy would have paid it.</p>';
		}
		if ( v.fault > 0 && ! barred && uncovered > CAP ) {
			html += '<p style="font-size:.9rem;">This applies the $3,000 cap before cutting for your fault, the lower reading of the statute. A court could apply the fault cut first.</p>';
		}
		if ( claim > 0 && letterUrl ) {
			html += '<p style="margin-bottom:0;"><a href="' + MAT.escape( letterUrl ) + '">Write a final demand letter before small claims court</a></p>';
		}
		MAT.showResult( resultBox, html );
	} );
})();
