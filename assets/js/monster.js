/**
 * SD-ON — анимированный монстр у края экрана.
 *
 * Монстр выезжает слева или справа через случайные интервалы, следит зрачками
 * за курсором, прячется, если мышь медленно подбирается к нему, и резко
 * убегает со звуком по клику. После клика он не показывается заданное время
 * (по умолчанию 300 секунд).
 */
( function () {
	'use strict';

	var settings = window.sdonMonster || {};
	var root = document.querySelector( '[data-sdon-monster]' );

	if ( ! root ) {
		return;
	}

	var reduced = settings.reducedMotion && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var isMobile = window.matchMedia( '(max-width: 899px)' ).matches;
	var allowed = isMobile ? settings.mobile : settings.desktop;

	if ( reduced || ! allowed ) {
		root.parentNode.removeChild( root );
		return;
	}

	var hit = root.querySelector( '[data-sdon-monster-hit]' );
	var bodies = root.querySelectorAll( '[data-sdon-monster-creature]' );
	var pupils = bodies.length ? bodies[ 0 ].querySelectorAll( '[data-sdon-monster-pupil]' ) : [];
	var minDelay = Math.max( 1000, settings.minDelay || 25000 );
	var maxDelay = Math.max( minDelay, settings.maxDelay || 120000 );
	var cooldown = Math.max( 0, 'number' === typeof settings.cooldown ? settings.cooldown : 300000 );
	var shyDistance = Math.max( 40, settings.shyDistance || 220 );
	var volume = 'number' === typeof settings.volume ? Math.min( 1, Math.max( 0, settings.volume ) ) : 0.6;

	var appearTimer = null;
	var retreatTimer = null;
	var visible = false;
	var shyness = 0;
	var pointerLast = null;
	var audioContext = null;
	var customAudio = null;

	function side() {
		if ( 'left' === root.dataset.side || 'right' === root.dataset.side ) {
			return root.dataset.side;
		}

		return Math.random() < 0.5 ? 'left' : 'right';
	}

	// Хаотичный режим: перед каждым выходом на сцену выбирается случайный персонаж.
	function pickCreature() {
		var chosen = bodies.length < 2 ? 0 : Math.floor( Math.random() * bodies.length );
		var i;

		for ( i = 0; i < bodies.length; i++ ) {
			bodies[ i ].hidden = i !== chosen;
		}

		if ( bodies.length ) {
			pupils = bodies[ chosen ].querySelectorAll( '[data-sdon-monster-pupil]' );
			root.dataset.shown = bodies[ chosen ].dataset.sdonMonsterCreature;
		}
	}

	function offset( fraction ) {
		var sign = 'right' === root.dataset.current ? 1 : -1;

		root.style.setProperty( '--sdon-monster-shift', ( sign * fraction * 100 ) + '%' );
	}

	function clearTimers() {
		if ( appearTimer ) {
			window.clearTimeout( appearTimer );
			appearTimer = null;
		}

		if ( retreatTimer ) {
			window.clearTimeout( retreatTimer );
			retreatTimer = null;
		}
	}

	function hide( scared ) {
		visible = false;
		shyness = 0;
		root.classList.toggle( 'is-scared', !! scared );
		root.classList.remove( 'is-visible' );
		offset( 1 );

		window.setTimeout( function () {
			if ( ! visible ) {
				root.hidden = true;
				root.classList.remove( 'is-scared' );
			}
		}, scared ? 320 : 900 );
	}

	function schedule( delay ) {
		clearTimers();

		var wait = 'number' === typeof delay ? delay : minDelay + Math.random() * ( maxDelay - minDelay );

		appearTimer = window.setTimeout( show, wait );
	}

	function show() {
		if ( document.hidden ) {
			schedule( 5000 );
			return;
		}

		pickCreature();
		root.dataset.current = side();
		root.hidden = false;
		shyness = 0;
		offset( 1 );

		// Кадр задержки нужен, чтобы браузер применил стартовое смещение.
		window.requestAnimationFrame( function () {
			visible = true;
			root.classList.add( 'is-visible' );
			offset( 0 );
		} );

		// Сам уходит, если посетитель его не заметил.
		retreatTimer = window.setTimeout( function () {
			if ( visible ) {
				hide( false );
				schedule();
			}
		}, 14000 );
	}

	function context() {
		var Ctor = window.AudioContext || window.webkitAudioContext;

		if ( ! Ctor ) {
			return null;
		}

		if ( ! audioContext ) {
			audioContext = new Ctor();
		}

		return audioContext;
	}

	function tone( type, from, to, duration, wave ) {
		var ctx = context();

		if ( ! ctx ) {
			return;
		}

		var osc = ctx.createOscillator();
		var gain = ctx.createGain();
		var now = ctx.currentTime;

		osc.type = wave || type;
		osc.frequency.setValueAtTime( from, now );
		osc.frequency.exponentialRampToValueAtTime( Math.max( 40, to ), now + duration );
		gain.gain.setValueAtTime( volume * 0.35, now );
		gain.gain.exponentialRampToValueAtTime( 0.0001, now + duration );
		osc.connect( gain );
		gain.connect( ctx.destination );
		osc.start( now );
		osc.stop( now + duration + 0.05 );
	}

	function playCustom() {
		if ( ! settings.soundFile ) {
			return;
		}

		if ( ! customAudio ) {
			customAudio = new Audio( settings.soundFile );
		}

		customAudio.volume = volume;
		customAudio.currentTime = 0;

		var played = customAudio.play();

		if ( played && played.catch ) {
			played.catch( function () {
				// Браузер запретил автозвук — молча игнорируем.
			} );
		}
	}

	function playSound() {
		switch ( settings.sound ) {
			case 'none':
				break;

			case 'custom':
				playCustom();
				break;

			case 'growl':
				tone( 'sawtooth', 180, 48, 0.55 );
				break;

			case 'boing':
				tone( 'sine', 140, 620, 0.28 );
				window.setTimeout( function () {
					tone( 'sine', 520, 120, 0.34 );
				}, 120 );
				break;

			case 'glitch':
				tone( 'square', 900, 220, 0.12 );
				window.setTimeout( function () {
					tone( 'square', 320, 1400, 0.1 );
				}, 90 );
				window.setTimeout( function () {
					tone( 'square', 1200, 180, 0.14 );
				}, 190 );
				break;

			default:
				tone( 'triangle', 760, 1800, 0.16 );
				window.setTimeout( function () {
					tone( 'triangle', 1500, 300, 0.22 );
				}, 130 );
		}
	}

	function lookAt( x, y ) {
		var box = root.getBoundingClientRect();
		var dx = ( x - ( box.left + box.width / 2 ) ) / Math.max( 1, box.width );
		var dy = ( y - ( box.top + box.height / 2 ) ) / Math.max( 1, box.height );
		var shift = 7;
		var i;

		for ( i = 0; i < pupils.length; i++ ) {
			pupils[ i ].style.transform = 'translate(' +
				Math.max( -shift, Math.min( shift, dx * shift * 2 ) ) + 'px,' +
				Math.max( -shift, Math.min( shift, dy * shift * 2 ) ) + 'px)';
		}
	}

	function creepAway( distance ) {
		// Чем ближе курсор, тем сильнее монстр вжимается за край экрана.
		var target = Math.min( 1, Math.max( shyness, 1 - distance / shyDistance ) );

		shyness = target;
		root.classList.add( 'is-shy' );
		offset( target * 0.9 );

		if ( target > 0.85 ) {
			root.classList.remove( 'is-shy' );
			hide( false );
			schedule();
		}
	}

	function onPointerMove( event ) {
		var now = event.timeStamp || Date.now();
		var speed = 0;

		if ( pointerLast ) {
			var dt = Math.max( 8, now - pointerLast.t );

			speed = Math.sqrt(
				Math.pow( event.clientX - pointerLast.x, 2 ) +
				Math.pow( event.clientY - pointerLast.y, 2 )
			) / dt;
		}

		pointerLast = { x: event.clientX, y: event.clientY, t: now };

		if ( ! visible ) {
			return;
		}

		lookAt( event.clientX, event.clientY );

		var box = root.getBoundingClientRect();
		var cx = Math.min( box.right, Math.max( box.left, event.clientX ) );
		var cy = Math.min( box.bottom, Math.max( box.top, event.clientY ) );
		var distance = Math.sqrt(
			Math.pow( event.clientX - cx, 2 ) + Math.pow( event.clientY - cy, 2 )
		);

		// Прячется только от медленного, «подкрадывающегося» курсора.
		if ( distance < shyDistance && speed < 0.45 ) {
			creepAway( distance );
			return;
		}

		if ( distance > shyDistance && shyness > 0 ) {
			shyness = 0;
			root.classList.remove( 'is-shy' );
			offset( 0 );
		}
	}

	function onHit( event ) {
		event.preventDefault();

		if ( ! visible ) {
			return;
		}

		playSound();
		hide( true );
		schedule( cooldown );
	}

	if ( hit ) {
		hit.addEventListener( 'click', onHit );
	}

	window.addEventListener( 'pointermove', onPointerMove, { passive: true } );

	document.addEventListener( 'visibilitychange', function () {
		if ( document.hidden ) {
			clearTimers();
			return;
		}

		if ( ! visible ) {
			schedule();
		}
	} );

	schedule();
}() );
