/**
 * SD-ON Theme — переключение светлой и тёмной схемы.
 *
 * Выбор пользователя сохраняется в localStorage и имеет приоритет
 * над системной настройкой при автоматическом режиме.
 */
( function () {
	'use strict';

	var settings = window.sdonDark || {};
	var i18n = settings.i18n || {};
	var STORAGE_KEY = 'sdon-scheme';
	var root = document.documentElement;

	function stored() {
		try {
			return window.localStorage.getItem( STORAGE_KEY );
		} catch ( error ) {
			return null;
		}
	}

	function save( scheme ) {
		try {
			window.localStorage.setItem( STORAGE_KEY, scheme );
		} catch ( error ) {
			// Приватный режим браузера — просто не сохраняем выбор.
		}
	}

	function systemScheme() {
		return window.matchMedia( '(prefers-color-scheme: dark)' ).matches ? 'dark' : 'light';
	}

	function currentScheme() {
		return 'dark' === root.dataset.sdonScheme ? 'dark' : 'light';
	}

	function apply( scheme, toggle ) {
		root.dataset.sdonScheme = scheme;

		if ( toggle ) {
			var dark = 'dark' === scheme;
			var label = toggle.querySelector( '.screen-reader-text' );

			toggle.setAttribute( 'aria-pressed', dark ? 'true' : 'false' );

			if ( label ) {
				label.textContent = dark ? ( i18n.switchToLight || 'Light theme' ) : ( i18n.switchToDark || 'Dark theme' );
			}
		}
	}

	function init() {
		var toggle = document.querySelector( '[data-sdon-scheme-toggle]' );
		var initial = stored();

		if ( ! initial ) {
			initial = 'auto' === settings.mode ? systemScheme() : ( 'dark' === settings.mode ? 'dark' : 'light' );
		}

		apply( initial, toggle );

		if ( toggle ) {
			toggle.addEventListener( 'click', function () {
				var scheme = 'dark' === currentScheme() ? 'light' : 'dark';

				apply( scheme, toggle );
				save( scheme );
			} );
		}

		if ( 'auto' === settings.mode ) {
			var query = window.matchMedia( '(prefers-color-scheme: dark)' );
			var handler = function ( event ) {
				if ( ! stored() ) {
					apply( event.matches ? 'dark' : 'light', toggle );
				}
			};

			if ( query.addEventListener ) {
				query.addEventListener( 'change', handler );
			} else if ( query.addListener ) {
				query.addListener( handler );
			}
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
