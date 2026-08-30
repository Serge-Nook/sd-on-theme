/**
 * SD-ON — мобильное меню, подменю, кнопка «Наверх», прелоадер.
 *
 * Скрипт не использует внешних библиотек и работает при отключённом jQuery.
 */
( function () {
	'use strict';

	var settings = window.sdonNav || {};
	var i18n = settings.i18n || {};
	var DESKTOP = 900;

	function isDesktop() {
		return window.innerWidth >= DESKTOP;
	}

	function isOffcanvas() {
		return 'offcanvas' === settings.menuType || ! isDesktop();
	}

	/* ---------------------------------------------- Раздвижное меню */

	function initMenuToggle() {
		var toggle = document.querySelector( '[data-sdon-menu-toggle]' );
		var nav = document.getElementById( 'sdon-primary-navigation' );

		if ( ! toggle || ! nav ) {
			return;
		}

		var label = toggle.querySelector( '.screen-reader-text' );

		function setState( open ) {
			nav.classList.toggle( 'is-open', open );
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			document.documentElement.style.overflow = open && isOffcanvas() ? 'hidden' : '';

			if ( label ) {
				label.textContent = open ? ( i18n.closeMenu || 'Close menu' ) : ( i18n.openMenu || 'Open menu' );
			}
		}

		function isOpen() {
			return nav.classList.contains( 'is-open' );
		}

		toggle.addEventListener( 'click', function () {
			var open = ! isOpen();
			setState( open );

			if ( open ) {
				var first = nav.querySelector( 'a, button, input' );

				if ( first ) {
					first.focus();
				}
			}
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && isOpen() ) {
				setState( false );
				toggle.focus();
			}
		} );

		document.addEventListener( 'click', function ( event ) {
			if ( ! isOpen() || ! isOffcanvas() ) {
				return;
			}

			if ( ! nav.contains( event.target ) && ! toggle.contains( event.target ) ) {
				setState( false );
			}
		} );

		window.addEventListener( 'resize', function () {
			if ( isOpen() && ! isOffcanvas() ) {
				setState( false );
			}
		} );
	}

	/* ---------------------------------------------- Подменю */

	function initSubmenus() {
		var toggles = document.querySelectorAll( '.sdon-submenu-toggle' );

		Array.prototype.forEach.call( toggles, function ( button ) {
			var item = button.parentNode;

			button.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				event.stopPropagation();

				var open = ! item.classList.contains( 'is-open' );

				closeSiblings( item );
				item.classList.toggle( 'is-open', open );
				button.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			} );
		} );

		if ( 'hover' === settings.submenuTrigger ) {
			var parents = document.querySelectorAll( '.sdon-menu .menu-item-has-children' );

			Array.prototype.forEach.call( parents, function ( item ) {
				item.addEventListener( 'mouseenter', function () {
					if ( isDesktop() && ! isOffcanvas() ) {
						item.classList.add( 'is-open' );
					}
				} );

				item.addEventListener( 'mouseleave', function () {
					if ( isDesktop() && ! isOffcanvas() ) {
						item.classList.remove( 'is-open' );
					}
				} );
			} );
		}

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' !== event.key ) {
				return;
			}

			closeAllSubmenus();
		} );

		document.addEventListener( 'click', function ( event ) {
			if ( ! event.target.closest || ! event.target.closest( '.sdon-menu' ) ) {
				closeAllSubmenus();
			}
		} );
	}

	function closeSiblings( item ) {
		var siblings = item.parentNode ? item.parentNode.children : [];

		Array.prototype.forEach.call( siblings, function ( sibling ) {
			if ( sibling !== item ) {
				closeItem( sibling );
			}
		} );
	}

	function closeAllSubmenus() {
		Array.prototype.forEach.call( document.querySelectorAll( '.sdon-menu .is-open' ), closeItem );
	}

	function closeItem( item ) {
		if ( ! item.classList || ! item.classList.contains( 'is-open' ) ) {
			return;
		}

		item.classList.remove( 'is-open' );

		var button = item.querySelector( ':scope > .sdon-submenu-toggle' );

		if ( button ) {
			button.setAttribute( 'aria-expanded', 'false' );
		}
	}

	/* ---------------------------------------------- Кнопка «Наверх» */

	function initBackToTop() {
		var button = document.querySelector( '[data-sdon-to-top]' );

		if ( ! button ) {
			return;
		}

		var hideTimer = null;

		function show() {
			window.clearTimeout( hideTimer );
			button.hidden = false;

			window.requestAnimationFrame( function () {
				button.classList.add( 'is-visible' );
			} );
		}

		function hide() {
			button.classList.remove( 'is-visible' );

			window.clearTimeout( hideTimer );

			hideTimer = window.setTimeout( function () {
				if ( ! button.classList.contains( 'is-visible' ) ) {
					button.hidden = true;
				}
			}, 300 );
		}

		function update() {
			var visible = window.pageYOffset >= 400;

			if ( visible === button.classList.contains( 'is-visible' ) ) {
				return;
			}

			if ( visible ) {
				show();
			} else {
				hide();
			}
		}

		window.addEventListener( 'scroll', update, { passive: true } );
		update();

		button.addEventListener( 'click', function () {
			var reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

			window.scrollTo( {
				top: 0,
				behavior: reduced ? 'auto' : 'smooth'
			} );
		} );
	}

	/* ---------------------------------------------- Прелоадер */

	function initPreloader() {
		var preloader = document.querySelector( '[data-sdon-preloader]' );

		if ( ! preloader ) {
			return;
		}

		function hide() {
			preloader.classList.add( 'is-hidden' );
		}

		window.addEventListener( 'load', hide );
		window.setTimeout( hide, 4000 );
	}

	function init() {
		initMenuToggle();
		initSubmenus();
		initBackToTop();
		initPreloader();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
