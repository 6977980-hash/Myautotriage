/**
 * Recorded Statement Checklist: whose insurer is asking, injuries and
 * disputed fault decide the advice and the message to send.
 */
(function () {
	'use strict';

	var form = document.getElementById( 'mat-rs-form' );
	if ( ! form ) {
		return;
	}
	var box = document.getElementById( 'mat-rs-result' );

	var SCRIPTS = {
		decline: 'Thank you for contacting me about claim [claim number]. I am not going to give a recorded statement at this time. I am happy to give a written statement of the facts, along with the police report number and my photos. Please send any specific questions to this email address and I will answer them in writing.',
		prepare: 'Thank you for contacting me about claim [claim number]. Before a recorded statement, please email me the topics you plan to cover and confirm that I will receive a copy of the recording or transcript. I am available on [date and time].',
		lawyer: 'Thank you for contacting me about claim [claim number]. Because this accident involved injuries, I am not giving a recorded statement until I have had advice. Please send any questions in writing to this email address.'
	};

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		var who = document.getElementById( 'mat-rs-who' ).value;
		var injury = document.getElementById( 'mat-rs-injury' ).value === 'yes';
		var disputed = document.getElementById( 'mat-rs-fault' ).value === 'yes';

		var title;
		var why;
		var script;
		var tips = [];
		if ( who === 'own' ) {
			title = 'You probably need to cooperate, but you can prepare first';
			why = 'Your own policy almost certainly has a duty-to-cooperate clause, and refusing a statement can put your coverage at risk. You can still set the time, ask for the topics in advance, and ask for a copy.';
			script = SCRIPTS.prepare;
			tips.push( 'Read the "Duties after an accident or loss" section of your policy so you know exactly what it requires.' );
		} else if ( who === 'other' ) {
			title = 'You generally don\'t have to give one';
			why = 'You have no contract with the other driver\'s insurer, so a policy\'s cooperation clause doesn\'t bind you. What they need to decide your claim are the facts, and a written statement with photos and the police report gives them those without a recording.';
			script = injury ? SCRIPTS.lawyer : SCRIPTS.decline;
			tips.push( 'If they say they can\'t decide your claim without it, ask in writing exactly what information is missing.' );
		} else {
			title = 'First, find out who the adjuster works for';
			why = 'Ask the adjuster which company they represent and whose policy the claim is under. If it\'s your own insurer, you likely have to cooperate. If it\'s the other driver\'s, you generally don\'t have to give a recording.';
			script = SCRIPTS.prepare;
		}
		if ( injury ) {
			tips.push( 'With injuries, what you say about how you feel now can be used to value the injury claim later. Don\'t say you\'re fine; consider talking to a lawyer before any statement.' );
		}
		if ( disputed ) {
			tips.push( 'With fault in dispute, every detail about speed, distance and where you were looking matters. Answer only what you know and don\'t estimate.' );
		}
		if ( ! injury && ! disputed && who !== 'own' ) {
			tips.push( 'With clear fault and no injuries, a short written statement usually moves the claim along just as fast.' );
		}

		var html = '<p class="mat-triage-result__label">Your answer</p>';
		html += '<h2 class="mat-triage-result__title">' + MAT.escape( title ) + '</h2>';
		html += '<p>' + MAT.escape( why ) + '</p>';
		if ( tips.length ) {
			html += '<ul>' + tips.map( function ( t ) {
				return '<li>' + MAT.escape( t ) + '</li>';
			} ).join( '' ) + '</ul>';
		}
		html += '<h3>Message to send the adjuster</h3>';
		html += '<div class="mat-adj-reply"><p id="mat-rs-script" class="mat-adj-reply__text">' + MAT.escape( script ) + '</p>';
		html += '<button type="button" class="mat-btn mat-btn--ghost mat-btn--sm" id="mat-rs-copy">Copy message</button></div>';
		html += '<p class="mat-triage-result__note">Then go through the checklist below before any call.</p>';
		box.innerHTML = html;
		box.hidden = false;
		box.setAttribute( 'tabindex', '-1' );
		box.focus();

		var copyBtn = document.getElementById( 'mat-rs-copy' );
		copyBtn.addEventListener( 'click', function () {
			var text = document.getElementById( 'mat-rs-script' ).textContent;
			var done = function () {
				copyBtn.textContent = 'Copied!';
				setTimeout( function () { copyBtn.textContent = 'Copy message'; }, 1800 );
			};
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( text ).then( done, function () {
					window.prompt( 'Copy this message:', text );
				} );
			} else {
				window.prompt( 'Copy this message:', text );
			}
		} );
	} );
})();
