/**
 * SD-ON — слайдер главной страницы.
 *
 * Поддерживает кнопки, индикаторы, автопереключение, свайпы и управление
 * с клавиатуры. Автопереключение останавливается при наведении, фокусе
 * и когда вкладка неактивна.
 */
( function () {
	'use strict';

	var settings = window.sdonSlider || {};

	function reducedMotion() {
		return settings.reducedMotion && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	}

	function initSlider( root ) {
		var slides = root.querySelectorAll( '.sdon-slide' );
		var dots = root.querySelectorAll( '[data-sdon-slider-dot]' );
		var prev = root.querySelector( '[data-sdon-slider-prev]' );
		var next = root.querySelector( '[data-sdon-slider-next]' );
		var total = slides.length;
		var current = 0;
		var timer = null;

		if ( total < 2 ) {
			return;
		}

		if ( settings.speed ) {
			root.style.setProperty( '--sdon-slider-speed', parseInt( settings.speed, 10 ) + 'ms' );
		}

		function show( index ) {
			current = ( index + total ) % total;

			Array.prototype.forEach.call( slides, function ( slide, i ) {
				var active = i === current;

				slide.classList.toggle( 'is-active', active );

				if ( active ) {
					slide.removeAttribute( 'aria-hidden' );
				} else {
					slide.setAttribute( 'aria-hidden', 'true' );
				}
			} );

			Array.prototype.forEach.call( dots, function ( dot, i ) {
				dot.classList.toggle( 'is-active', i === current );
				dot.setAttribute( 'aria-selected', i === current ? 'true' : 'false' );
			} );
		}

		function goTo( index ) {
			show( index );
			restart();
		}

		function stop() {
			if ( timer ) {
				window.clearInterval( timer );
				timer = null;
			}
		}

		function start() {
			if ( ! settings.autoplay || reducedMotion() || timer ) {
				return;
			}

			var interval = Math.max( 1500, parseInt( settings.interval, 10 ) || 6000 );

			timer = window.setInterval( function () {
				show( current + 1 );
			}, interval );
		}

		function restart() {
			stop();
			start();
		}

		if ( prev ) {
			prev.addEventListener( 'click', function () {
				goTo( current - 1 );
			} );
		}

		if ( next ) {
			next.addEventListener( 'click', function () {
				goTo( current + 1 );
			} );
		}

		Array.prototype.forEach.call( dots, function ( dot, i ) {
			dot.addEventListener( 'click', function () {
				goTo( i );
			} );
		} );

		if ( settings.keyboard ) {
			root.setAttribute( 'tabindex', '0' );
			root.addEventListener( 'keydown', function ( event ) {
				if ( 'ArrowLeft' === event.key ) {
					event.preventDefault();
					goTo( current - 1 );
				} else if ( 'ArrowRight' === event.key ) {
					event.preventDefault();
					goTo( current + 1 );
				}
			} );
		}

		if ( settings.swipe ) {
			var startX = 0;
			var startY = 0;

			root.addEventListener( 'touchstart', function ( event ) {
				startX = event.touches[ 0 ].clientX;
				startY = event.touches[ 0 ].clientY;
				stop();
			}, { passive: true } );

			root.addEventListener( 'touchend', function ( event ) {
				var deltaX = event.changedTouches[ 0 ].clientX - startX;
				var deltaY = event.changedTouches[ 0 ].clientY - startY;

				if ( Math.abs( deltaX ) > 40 && Math.abs( deltaX ) > Math.abs( deltaY ) ) {
					goTo( deltaX < 0 ? current + 1 : current - 1 );
				} else {
					start();
				}
			}, { passive: true } );
		}

		root.addEventListener( 'mouseenter', stop );
		root.addEventListener( 'mouseleave', start );
		root.addEventListener( 'focusin', stop );
		root.addEventListener( 'focusout', start );

		document.addEventListener( 'visibilitychange', function () {
			if ( document.hidden ) {
				stop();
			} else {
				start();
			}
		} );

		show( 0 );
		start();
	}

	function init() {
		Array.prototype.forEach.call( document.querySelectorAll( '[data-sdon-slider]' ), initSlider );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
