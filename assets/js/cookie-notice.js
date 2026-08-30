/**
 * Окно соглашения об использовании временных файлов.
 *
 * Показывается один раз, после ответа посетителя — не чаще, чем раз в
 * указанное в настройках число дней.
 */
( function () {
	'use strict';

	var settings = window.sdonCookieNotice || {};
	var root = document.querySelector( '[data-sdon-cookie]' );

	if ( ! root ) {
		return;
	}

	var key = settings.storage || 'sdonCookieConsent';
	var days = Math.min( 365, Math.max( 1, settings.days || 7 ) );
	var period = days * 24 * 60 * 60 * 1000;

	function store() {
		try {
			return window.localStorage;
		} catch ( error ) {
			return null;
		}
	}

	function answeredAt() {
		var storage = store();

		if ( ! storage ) {
			return 0;
		}

		var raw = storage.getItem( key );
		var value = raw ? parseInt( raw, 10 ) : 0;

		return isNaN( value ) ? 0 : value;
	}

	function remember() {
		var storage = store();

		if ( storage ) {
			try {
				storage.setItem( key, String( Date.now() ) );
			} catch ( error ) {
				// Приватный режим браузера — окно просто покажется снова.
			}
		}
	}

	function close() {
		remember();
		root.classList.remove( 'is-visible' );

		window.setTimeout( function () {
			root.hidden = true;
		}, 420 );
	}

	var last = answeredAt();

	if ( last && Date.now() - last < period ) {
		root.parentNode.removeChild( root );
		return;
	}

	var buttons = root.querySelectorAll( '[data-sdon-cookie-accept], [data-sdon-cookie-decline]' );
	var i;

	for ( i = 0; i < buttons.length; i++ ) {
		buttons[ i ].addEventListener( 'click', close );
	}

	root.hidden = false;

	window.setTimeout( function () {
		root.classList.add( 'is-visible' );
	}, 900 );
}() );
