/**
 * SD-ON Theme — анимированный фон на canvas.
 *
 * Доступны четыре варианта: «Звёзды», «Матрица», «Лабиринт» и «Трубопровод».
 * Анимация останавливается на неактивной вкладке, отключается при
 * prefers-reduced-motion и (по настройке) на мобильных устройствах.
 */
( function () {
	'use strict';

	var settings = window.sdonBg || {};
	var canvas = document.querySelector( '[data-sdon-bg-canvas]' );

	if ( ! canvas || ! canvas.getContext ) {
		return;
	}

	var reduced = settings.reducedMotion && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var isMobile = window.matchMedia( '(max-width: 899px)' ).matches;

	if ( reduced || ( isMobile && ! settings.enableMobile ) ) {
		canvas.parentNode.removeChild( canvas );
		return;
	}

	var context = canvas.getContext( '2d' );
	var ratio = Math.min( window.devicePixelRatio || 1, 2 );
	var width = 0;
	var height = 0;
	var frame = null;
	var scene = null;

	function accent() {
		var value = window.getComputedStyle( document.documentElement ).getPropertyValue( '--sdon-primary' );

		return value ? value.trim() : '#2f6df6';
	}

	function resize() {
		width = canvas.clientWidth;
		height = canvas.clientHeight;
		canvas.width = Math.floor( width * ratio );
		canvas.height = Math.floor( height * ratio );
		context.setTransform( ratio, 0, 0, ratio, 0, 0 );

		if ( scene && scene.resize ) {
			scene.resize();
		}
	}

	/* ---------------------------------------------- Звёзды */

	function starsScene() {
		var stars = [];

		function build() {
			var count = Math.min( 220, Math.round( ( width * height ) / 9000 ) );

			stars = [];

			for ( var i = 0; i < count; i++ ) {
				stars.push( {
					x: Math.random() * width - width / 2,
					y: Math.random() * height - height / 2,
					z: Math.random() * width
				} );
			}
		}

		return {
			resize: build,
			draw: function () {
				context.clearRect( 0, 0, width, height );
				context.fillStyle = accent();

				for ( var i = 0; i < stars.length; i++ ) {
					var star = stars[ i ];

					star.z -= width / 260;

					if ( star.z <= 1 ) {
						star.z = width;
						star.x = Math.random() * width - width / 2;
						star.y = Math.random() * height - height / 2;
					}

					var k = 128 / star.z;
					var x = star.x * k + width / 2;
					var y = star.y * k + height / 2;
					var size = Math.max( 0.4, ( 1 - star.z / width ) * 2.4 );

					context.globalAlpha = Math.min( 1, 1 - star.z / width );
					context.beginPath();
					context.arc( x, y, size, 0, Math.PI * 2 );
					context.fill();
				}

				context.globalAlpha = 1;
			}
		};
	}

	/* ---------------------------------------------- Матрица */

	function matrixScene() {
		var glyphs = 'アイウエオカキクケコサシスセソ0123456789ABCDEF';
		var size = 16;
		var drops = [];

		function build() {
			var columns = Math.ceil( width / size );

			drops = [];

			for ( var i = 0; i < columns; i++ ) {
				drops.push( Math.random() * height / size );
			}
		}

		return {
			resize: build,
			draw: function () {
				context.fillStyle = 'rgba(0, 0, 0, 0.08)';
				context.fillRect( 0, 0, width, height );
				context.fillStyle = accent();
				context.font = size + 'px monospace';

				for ( var i = 0; i < drops.length; i++ ) {
					var glyph = glyphs.charAt( Math.floor( Math.random() * glyphs.length ) );

					context.fillText( glyph, i * size, drops[ i ] * size );

					if ( drops[ i ] * size > height && Math.random() > 0.975 ) {
						drops[ i ] = 0;
					}

					drops[ i ] += 0.6;
				}
			}
		};
	}

	/* ---------------------------------------------- Лабиринт */

	function mazeScene() {
		var size = 34;
		var cells = [];
		var tick = 0;

		function build() {
			cells = [];

			for ( var y = 0; y < Math.ceil( height / size ); y++ ) {
				var row = [];

				for ( var x = 0; x < Math.ceil( width / size ); x++ ) {
					row.push( Math.random() > 0.5 );
				}

				cells.push( row );
			}
		}

		return {
			resize: build,
			draw: function () {
				tick++;

				if ( tick % 30 === 0 ) {
					var y = Math.floor( Math.random() * cells.length );
					var x = cells.length ? Math.floor( Math.random() * cells[ y ].length ) : 0;

					if ( cells[ y ] ) {
						cells[ y ][ x ] = ! cells[ y ][ x ];
					}
				}

				context.clearRect( 0, 0, width, height );
				context.strokeStyle = accent();
				context.lineWidth = 1.4;
				context.globalAlpha = 0.85;
				context.beginPath();

				for ( var row = 0; row < cells.length; row++ ) {
					for ( var col = 0; col < cells[ row ].length; col++ ) {
						var px = col * size;
						var py = row * size;

						if ( cells[ row ][ col ] ) {
							context.moveTo( px, py );
							context.lineTo( px + size, py + size );
						} else {
							context.moveTo( px + size, py );
							context.lineTo( px, py + size );
						}
					}
				}

				context.stroke();
				context.globalAlpha = 1;
			}
		};
	}

	/* ---------------------------------------------- Трубопровод */

	function pipesScene() {
		var size = 26;
		var pipes = [];

		function spawn() {
			return {
				x: Math.floor( Math.random() * Math.max( 1, Math.floor( width / size ) ) ) * size,
				y: Math.floor( Math.random() * Math.max( 1, Math.floor( height / size ) ) ) * size,
				direction: Math.floor( Math.random() * 4 ),
				life: 0
			};
		}

		function build() {
			pipes = [];

			var count = Math.max( 2, Math.round( width / 500 ) );

			for ( var i = 0; i < count; i++ ) {
				pipes.push( spawn() );
			}

			context.clearRect( 0, 0, width, height );
		}

		return {
			resize: build,
			draw: function () {
				context.fillStyle = 'rgba(0, 0, 0, 0.02)';
				context.fillRect( 0, 0, width, height );
				context.strokeStyle = accent();
				context.lineWidth = 3;
				context.lineCap = 'round';

				for ( var i = 0; i < pipes.length; i++ ) {
					var pipe = pipes[ i ];
					var fromX = pipe.x;
					var fromY = pipe.y;

					if ( Math.random() > 0.8 ) {
						pipe.direction = ( pipe.direction + ( Math.random() > 0.5 ? 1 : 3 ) ) % 4;
					}

					if ( 0 === pipe.direction ) {
						pipe.x += size;
					} else if ( 1 === pipe.direction ) {
						pipe.y += size;
					} else if ( 2 === pipe.direction ) {
						pipe.x -= size;
					} else {
						pipe.y -= size;
					}

					pipe.life++;

					if ( pipe.x < 0 || pipe.y < 0 || pipe.x > width || pipe.y > height || pipe.life > 120 ) {
						pipes[ i ] = spawn();
						continue;
					}

					context.beginPath();
					context.moveTo( fromX, fromY );
					context.lineTo( pipe.x, pipe.y );
					context.stroke();
				}
			}
		};
	}

	var scenes = {
		stars: starsScene,
		matrix: matrixScene,
		maze: mazeScene,
		pipes: pipesScene
	};

	var factory = scenes[ settings.type ];

	if ( ! factory ) {
		return;
	}

	scene = factory();

	function loop() {
		scene.draw();
		frame = window.requestAnimationFrame( loop );
	}

	function start() {
		if ( ! frame ) {
			frame = window.requestAnimationFrame( loop );
		}
	}

	function stop() {
		if ( frame ) {
			window.cancelAnimationFrame( frame );
			frame = null;
		}
	}

	window.addEventListener( 'resize', function () {
		resize();
	} );

	document.addEventListener( 'visibilitychange', function () {
		if ( document.hidden ) {
			stop();
		} else {
			start();
		}
	} );

	resize();
	start();
}() );
