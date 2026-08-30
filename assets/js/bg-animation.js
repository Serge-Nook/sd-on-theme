/**
 * SD-ON — анимированный фон на canvas.
 *
 * Набор сцен выбирается в настройках темы, скорость и автоматическое
 * переключение тоже настраиваются. Анимация останавливается на неактивной
 * вкладке, отключается при prefers-reduced-motion и (по настройке) на
 * мобильных устройствах.
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
	var speed = 'number' === typeof settings.speed && settings.speed > 0 ? settings.speed : 1;
	var width = 0;
	var height = 0;
	var frame = null;
	var scene = null;
	var pointer = { x: 0.5, y: 0.5 };
	var shuffleTimer = null;

	function accent() {
		var value = window.getComputedStyle( document.documentElement ).getPropertyValue( '--sdon-primary' );

		return value ? value.trim() : '#2f6df6';
	}

	function rand( min, max ) {
		return min + Math.random() * ( max - min );
	}

	function clear() {
		context.clearRect( 0, 0, width, height );
	}

	function fade( alpha ) {
		context.fillStyle = 'rgba(0, 0, 0, ' + alpha + ')';
		context.fillRect( 0, 0, width, height );
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
				clear();
				context.fillStyle = accent();

				for ( var i = 0; i < stars.length; i++ ) {
					var star = stars[ i ];

					star.z -= ( width / 260 ) * speed;

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
				fade( 0.08 );
				context.fillStyle = accent();
				context.font = size + 'px monospace';

				for ( var i = 0; i < drops.length; i++ ) {
					var glyph = glyphs.charAt( Math.floor( Math.random() * glyphs.length ) );

					context.fillText( glyph, i * size, drops[ i ] * size );

					if ( drops[ i ] * size > height && Math.random() > 0.975 ) {
						drops[ i ] = 0;
					}

					drops[ i ] += 0.6 * speed;
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
				tick += speed;

				if ( tick > 30 ) {
					tick = 0;

					var y = Math.floor( Math.random() * cells.length );
					var x = cells.length ? Math.floor( Math.random() * cells[ y ].length ) : 0;

					if ( cells[ y ] ) {
						cells[ y ][ x ] = ! cells[ y ][ x ];
					}
				}

				clear();
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

			clear();
		}

		return {
			resize: build,
			draw: function () {
				fade( 0.02 );
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

	/* ---------------------------------------------- Снежинки, вариант 1 */

	function snowRoundScene() {
		var flakes = [];

		function build() {
			var count = Math.min( 260, Math.round( ( width * height ) / 12000 ) );

			flakes = [];

			for ( var i = 0; i < count; i++ ) {
				flakes.push( {
					x: Math.random() * width,
					y: Math.random() * height,
					r: rand( 1, 3.4 ),
					vy: rand( 0.4, 1.5 ),
					sway: rand( 0.4, 1.4 ),
					phase: Math.random() * Math.PI * 2
				} );
			}
		}

		return {
			resize: build,
			draw: function () {
				clear();
				context.fillStyle = '#fff';

				for ( var i = 0; i < flakes.length; i++ ) {
					var flake = flakes[ i ];

					flake.phase += 0.02 * speed;
					flake.y += flake.vy * speed;
					flake.x += Math.sin( flake.phase ) * flake.sway * speed;

					if ( flake.y - flake.r > height ) {
						flake.y = -flake.r;
						flake.x = Math.random() * width;
					}

					context.globalAlpha = 0.35 + flake.r / 6;
					context.beginPath();
					context.arc( flake.x, flake.y, flake.r, 0, Math.PI * 2 );
					context.fill();
				}

				context.globalAlpha = 1;
			}
		};
	}

	/* ---------------------------------------------- Снежинки, вариант 2 */

	function snowCrystalScene() {
		var flakes = [];

		function build() {
			var count = Math.min( 90, Math.round( ( width * height ) / 26000 ) );

			flakes = [];

			for ( var i = 0; i < count; i++ ) {
				flakes.push( {
					x: Math.random() * width,
					y: Math.random() * height,
					size: rand( 5, 14 ),
					vy: rand( 0.3, 1 ),
					angle: Math.random() * Math.PI,
					spin: rand( -0.02, 0.02 ),
					drift: rand( -0.5, 0.5 )
				} );
			}
		}

		function crystal( flake ) {
			context.save();
			context.translate( flake.x, flake.y );
			context.rotate( flake.angle );
			context.beginPath();

			for ( var i = 0; i < 6; i++ ) {
				var angle = ( Math.PI / 3 ) * i;
				var x = Math.cos( angle ) * flake.size;
				var y = Math.sin( angle ) * flake.size;

				context.moveTo( 0, 0 );
				context.lineTo( x, y );
				context.moveTo( x * 0.6, y * 0.6 );
				context.lineTo( x * 0.6 + Math.cos( angle + 1 ) * flake.size * 0.3, y * 0.6 + Math.sin( angle + 1 ) * flake.size * 0.3 );
				context.moveTo( x * 0.6, y * 0.6 );
				context.lineTo( x * 0.6 + Math.cos( angle - 1 ) * flake.size * 0.3, y * 0.6 + Math.sin( angle - 1 ) * flake.size * 0.3 );
			}

			context.stroke();
			context.restore();
		}

		return {
			resize: build,
			draw: function () {
				clear();
				context.strokeStyle = '#e8f3ff';
				context.lineWidth = 1.1;

				for ( var i = 0; i < flakes.length; i++ ) {
					var flake = flakes[ i ];

					flake.y += flake.vy * speed;
					flake.x += flake.drift * speed;
					flake.angle += flake.spin * speed;

					if ( flake.y - flake.size > height ) {
						flake.y = -flake.size;
						flake.x = Math.random() * width;
					}

					if ( flake.x < -flake.size ) {
						flake.x = width + flake.size;
					} else if ( flake.x > width + flake.size ) {
						flake.x = -flake.size;
					}

					context.globalAlpha = 0.5 + flake.size / 40;
					crystal( flake );
				}

				context.globalAlpha = 1;
			}
		};
	}

	/* ---------------------------------------------- Листопад */

	function leavesScene() {
		var colors = [ '#d97706', '#b45309', '#ca8a04', '#9a3412', '#65a30d' ];
		var leaves = [];

		function build() {
			var count = Math.min( 70, Math.round( ( width * height ) / 26000 ) );

			leaves = [];

			for ( var i = 0; i < count; i++ ) {
				leaves.push( {
					x: Math.random() * width,
					y: Math.random() * height,
					size: rand( 7, 16 ),
					vy: rand( 0.4, 1.1 ),
					angle: Math.random() * Math.PI * 2,
					spin: rand( -0.03, 0.03 ),
					phase: Math.random() * Math.PI * 2,
					sway: rand( 0.6, 1.8 ),
					color: colors[ Math.floor( Math.random() * colors.length ) ]
				} );
			}
		}

		return {
			resize: build,
			draw: function () {
				clear();

				for ( var i = 0; i < leaves.length; i++ ) {
					var leaf = leaves[ i ];

					leaf.phase += 0.03 * speed;
					leaf.y += leaf.vy * speed;
					leaf.x += Math.sin( leaf.phase ) * leaf.sway * speed;
					leaf.angle += leaf.spin * speed;

					if ( leaf.y - leaf.size > height ) {
						leaf.y = -leaf.size;
						leaf.x = Math.random() * width;
					}

					context.save();
					context.translate( leaf.x, leaf.y );
					context.rotate( leaf.angle );
					context.globalAlpha = 0.8;
					context.fillStyle = leaf.color;
					context.beginPath();
					context.ellipse( 0, 0, leaf.size, leaf.size * 0.45, 0, 0, Math.PI * 2 );
					context.fill();
					context.strokeStyle = 'rgba(0, 0, 0, 0.25)';
					context.lineWidth = 1;
					context.beginPath();
					context.moveTo( -leaf.size, 0 );
					context.lineTo( leaf.size, 0 );
					context.stroke();
					context.restore();
				}

				context.globalAlpha = 1;
			}
		};
	}

	/* ---------------------------------------------- Аркада */

	function arcadeScene() {
		var sprite = [
			'001000100',
			'000111000',
			'001111100',
			'011101110',
			'111111111',
			'101111101',
			'101000101',
			'000110110'
		];
		var pixel = 4;
		var invaders = [];
		var shots = [];
		var direction = 1;
		var tick = 0;

		function build() {
			invaders = [];

			var stepX = sprite[ 0 ].length * pixel + 22;
			var stepY = sprite.length * pixel + 20;
			var cols = Math.max( 3, Math.floor( width / stepX ) - 1 );
			var rows = Math.max( 2, Math.min( 5, Math.floor( height / stepY / 3 ) ) );

			for ( var row = 0; row < rows; row++ ) {
				for ( var col = 0; col < cols; col++ ) {
					invaders.push( {
						x: 30 + col * stepX,
						y: 40 + row * stepY
					} );
				}
			}

			shots = [];
			clear();
		}

		function drawSprite( x, y ) {
			for ( var row = 0; row < sprite.length; row++ ) {
				for ( var col = 0; col < sprite[ row ].length; col++ ) {
					if ( '1' === sprite[ row ].charAt( col ) ) {
						context.fillRect( x + col * pixel, y + row * pixel, pixel, pixel );
					}
				}
			}
		}

		return {
			resize: build,
			draw: function () {
				clear();
				tick += speed;

				var shift = direction * 0.45 * speed;
				var i;

				for ( i = 0; i < invaders.length; i++ ) {
					invaders[ i ].x += shift;
				}

				if ( invaders.length ) {
					var minX = invaders[ 0 ].x;
					var maxX = invaders[ 0 ].x;

					for ( i = 1; i < invaders.length; i++ ) {
						minX = Math.min( minX, invaders[ i ].x );
						maxX = Math.max( maxX, invaders[ i ].x );
					}

					if ( maxX + sprite[ 0 ].length * pixel > width - 20 || minX < 20 ) {
						direction *= -1;

						for ( i = 0; i < invaders.length; i++ ) {
							invaders[ i ].y += 10;

							if ( invaders[ i ].y > height ) {
								invaders[ i ].y = 40;
							}
						}
					}
				}

				if ( tick > 40 && invaders.length ) {
					tick = 0;

					var shooter = invaders[ Math.floor( Math.random() * invaders.length ) ];

					shots.push( {
						x: shooter.x + sprite[ 0 ].length * pixel / 2,
						y: shooter.y + sprite.length * pixel
					} );
				}

				context.fillStyle = accent();
				context.globalAlpha = 0.85;

				for ( i = 0; i < invaders.length; i++ ) {
					drawSprite( invaders[ i ].x, invaders[ i ].y );
				}

				for ( i = shots.length - 1; i >= 0; i-- ) {
					shots[ i ].y += 3 * speed;

					if ( shots[ i ].y > height ) {
						shots.splice( i, 1 );
						continue;
					}

					context.fillRect( shots[ i ].x, shots[ i ].y, pixel / 2, pixel * 3 );
				}

				context.globalAlpha = 1;
			}
		};
	}

	/**
	 * Общая механика «дорожек с импульсами»: набор отрезков и бегущие по ним точки.
	 *
	 * @param {Object} options Параметры сцены.
	 * @return {Object} Сцена.
	 */
	function traceScene( options ) {
		var segments = [];
		var pulses = [];

		function build() {
			segments = options.build( width, height );
			pulses = [];

			for ( var i = 0; i < Math.min( segments.length, options.pulses ); i++ ) {
				pulses.push( {
					segment: Math.floor( Math.random() * segments.length ),
					progress: Math.random()
				} );
			}
		}

		return {
			resize: build,
			draw: function () {
				clear();
				context.strokeStyle = options.color || accent();
				context.lineWidth = options.lineWidth || 1.4;
				context.globalAlpha = options.trackAlpha || 0.35;
				context.beginPath();

				var i;
				var segment;

				for ( i = 0; i < segments.length; i++ ) {
					segment = segments[ i ];
					context.moveTo( segment.x1, segment.y1 );
					context.lineTo( segment.x2, segment.y2 );
				}

				context.stroke();

				if ( options.nodes ) {
					context.globalAlpha = 0.5;
					context.fillStyle = options.color || accent();

					for ( i = 0; i < segments.length; i++ ) {
						segment = segments[ i ];
						context.beginPath();
						context.arc( segment.x2, segment.y2, options.nodeSize || 2.2, 0, Math.PI * 2 );
						context.fill();
					}
				}

				context.globalAlpha = 1;
				context.fillStyle = options.pulseColor || '#fff';

				for ( i = 0; i < pulses.length; i++ ) {
					var pulse = pulses[ i ];

					segment = segments[ pulse.segment ];

					if ( ! segment ) {
						pulse.segment = Math.floor( Math.random() * segments.length );
						continue;
					}

					pulse.progress += ( options.pulseSpeed || 0.01 ) * speed;

					if ( pulse.progress > 1 ) {
						pulse.progress = 0;
						pulse.segment = Math.floor( Math.random() * segments.length );
						continue;
					}

					var x = segment.x1 + ( segment.x2 - segment.x1 ) * pulse.progress;
					var y = segment.y1 + ( segment.y2 - segment.y1 ) * pulse.progress;

					context.beginPath();
					context.arc( x, y, options.pulseSize || 2.6, 0, Math.PI * 2 );
					context.fill();
				}
			}
		};
	}

	/* ---------------------------------------------- Электро плата */

	function circuitScene() {
		return traceScene( {
			pulses: 26,
			nodes: true,
			pulseSpeed: 0.008,
			build: function ( w, h ) {
				var step = 60;
				var segments = [];

				for ( var y = step; y < h; y += step ) {
					var x = 20;

					while ( x < w - step ) {
						var length = step * Math.round( rand( 1, 3 ) );
						var next = Math.min( w - 20, x + length );

						segments.push( { x1: x, y1: y, x2: next, y2: y } );

						if ( Math.random() > 0.45 ) {
							var down = y + step * ( Math.random() > 0.5 ? 1 : -1 );

							if ( down > 10 && down < h - 10 ) {
								segments.push( { x1: next, y1: y, x2: next, y2: down } );
							}
						}

						x = next + step / 2;
					}
				}

				return segments;
			}
		} );
	}

	/* ---------------------------------------------- Микрочип */

	function microchipScene() {
		return traceScene( {
			pulses: 30,
			nodes: true,
			nodeSize: 3,
			pulseSpeed: 0.012,
			build: function ( w, h ) {
				var cx = w / 2;
				var cy = h / 2;
				var chip = Math.min( w, h ) * 0.18;
				var segments = [];
				var pins = 9;
				var i;
				var offset;

				segments.push( { x1: cx - chip, y1: cy - chip, x2: cx + chip, y2: cy - chip } );
				segments.push( { x1: cx + chip, y1: cy - chip, x2: cx + chip, y2: cy + chip } );
				segments.push( { x1: cx + chip, y1: cy + chip, x2: cx - chip, y2: cy + chip } );
				segments.push( { x1: cx - chip, y1: cy + chip, x2: cx - chip, y2: cy - chip } );

				for ( i = 1; i < pins; i++ ) {
					offset = -chip + ( chip * 2 / pins ) * i;

					segments.push( { x1: cx + offset, y1: cy - chip, x2: cx + offset, y2: 10 } );
					segments.push( { x1: cx + offset, y1: cy + chip, x2: cx + offset, y2: h - 10 } );
					segments.push( { x1: cx - chip, y1: cy + offset, x2: 10, y2: cy + offset } );
					segments.push( { x1: cx + chip, y1: cy + offset, x2: w - 10, y2: cy + offset } );
				}

				return segments;
			}
		} );
	}

	/* ---------------------------------------------- Сеть каналов с сигналами */

	function signalsScene() {
		return traceScene( {
			pulses: 24,
			nodes: true,
			lineWidth: 1,
			trackAlpha: 0.28,
			pulseSize: 3.2,
			pulseSpeed: 0.006,
			build: function ( w, h ) {
				var nodes = [];
				var segments = [];
				var count = Math.max( 8, Math.round( ( w * h ) / 90000 ) );
				var i;
				var j;

				for ( i = 0; i < count; i++ ) {
					nodes.push( { x: rand( 30, w - 30 ), y: rand( 30, h - 30 ) } );
				}

				for ( i = 0; i < nodes.length; i++ ) {
					for ( j = i + 1; j < nodes.length; j++ ) {
						var dx = nodes[ i ].x - nodes[ j ].x;
						var dy = nodes[ i ].y - nodes[ j ].y;

						if ( Math.sqrt( dx * dx + dy * dy ) < Math.min( w, h ) / 3 ) {
							segments.push( { x1: nodes[ i ].x, y1: nodes[ i ].y, x2: nodes[ j ].x, y2: nodes[ j ].y } );
						}
					}
				}

				return segments;
			}
		} );
	}

	/* ---------------------------------------------- Киберпанк */

	function cyberpunkScene() {
		var buildings = [];
		var rain = [];
		var glow = 0;

		function build() {
			buildings = [];
			rain = [];

			var x = 0;

			while ( x < width ) {
				var w = rand( 30, 90 );

				buildings.push( {
					x: x,
					w: w,
					h: rand( height * 0.2, height * 0.65 ),
					neon: Math.random() > 0.5 ? '#ff2fb3' : '#22d3ee'
				} );

				x += w + rand( 6, 20 );
			}

			for ( var i = 0; i < Math.min( 220, Math.round( width / 3 ) ); i++ ) {
				rain.push( {
					x: Math.random() * width,
					y: Math.random() * height,
					len: rand( 8, 22 ),
					vy: rand( 6, 12 )
				} );
			}
		}

		return {
			resize: build,
			draw: function () {
				clear();
				glow += 0.02 * speed;

				var i;
				var pulse = 0.55 + Math.sin( glow ) * 0.2;

				for ( i = 0; i < buildings.length; i++ ) {
					var building = buildings[ i ];
					var top = height - building.h;

					context.globalAlpha = 0.55;
					context.fillStyle = '#0b1020';
					context.fillRect( building.x, top, building.w, building.h );
					context.globalAlpha = pulse;
					context.strokeStyle = building.neon;
					context.lineWidth = 1.6;
					context.strokeRect( building.x, top, building.w, building.h );

					context.globalAlpha = 0.35;

					for ( var y = top + 12; y < height - 8; y += 16 ) {
						context.beginPath();
						context.moveTo( building.x + 4, y );
						context.lineTo( building.x + building.w - 4, y );
						context.stroke();
					}
				}

				context.globalAlpha = 0.45;
				context.strokeStyle = '#7dd3fc';
				context.lineWidth = 1;

				for ( i = 0; i < rain.length; i++ ) {
					var drop = rain[ i ];

					drop.y += drop.vy * speed;
					drop.x += 0.6 * speed;

					if ( drop.y > height ) {
						drop.y = -drop.len;
						drop.x = Math.random() * width;
					}

					context.beginPath();
					context.moveTo( drop.x, drop.y );
					context.lineTo( drop.x - 1.5, drop.y + drop.len );
					context.stroke();
				}

				context.globalAlpha = 1;
			}
		};
	}

	/* ---------------------------------------------- Облако частиц */

	function nebulaScene() {
		var particles = [];

		function build() {
			var count = Math.min( 320, Math.round( ( width * height ) / 7000 ) );

			particles = [];

			for ( var i = 0; i < count; i++ ) {
				particles.push( {
					x: rand( -1, 1 ),
					y: rand( -1, 1 ),
					z: rand( 0.1, 1 ),
					hue: Math.random() > 0.5 ? '#8b5cf6' : '#22d3ee',
					size: rand( 0.8, 2.6 )
				} );
			}
		}

		return {
			resize: build,
			draw: function () {
				fade( 0.12 );

				var shiftX = ( pointer.x - 0.5 ) * width * 0.35;
				var shiftY = ( pointer.y - 0.5 ) * height * 0.35;

				for ( var i = 0; i < particles.length; i++ ) {
					var particle = particles[ i ];

					particle.z -= 0.0035 * speed;

					if ( particle.z <= 0.05 ) {
						particle.z = 1;
						particle.x = rand( -1, 1 );
						particle.y = rand( -1, 1 );
					}

					var k = 0.6 / particle.z;
					var x = width / 2 + ( particle.x * width * 0.5 + shiftX ) * k;
					var y = height / 2 + ( particle.y * height * 0.5 + shiftY ) * k;

					context.globalAlpha = Math.min( 0.9, ( 1 - particle.z ) * 1.2 );
					context.fillStyle = particle.hue;
					context.beginPath();
					context.arc( x, y, particle.size * k, 0, Math.PI * 2 );
					context.fill();
				}

				context.globalAlpha = 1;
			}
		};
	}

	/* ---------------------------------------------- Искривление пространства */

	function warpScene() {
		var phase = 0;

		return {
			resize: function () {
				clear();
			},
			draw: function () {
				clear();
				phase += 0.01 * speed;

				var cx = width / 2 + ( pointer.x - 0.5 ) * width * 0.15;
				var cy = height / 2 + ( pointer.y - 0.5 ) * height * 0.15;
				var strength = ( Math.min( width, height ) * 0.35 ) * ( 0.7 + Math.sin( phase ) * 0.3 );
				var step = 46;
				var x;
				var y;

				context.strokeStyle = accent();
				context.lineWidth = 1;
				context.globalAlpha = 0.5;

				function bend( px, py ) {
					var dx = px - cx;
					var dy = py - cy;
					var dist = Math.max( 24, Math.sqrt( dx * dx + dy * dy ) );
					var pull = ( strength * strength ) / ( dist * dist );

					return {
						x: px - dx * Math.min( 0.85, pull ),
						y: py - dy * Math.min( 0.85, pull )
					};
				}

				for ( y = 0; y <= height + step; y += step ) {
					context.beginPath();

					for ( x = 0; x <= width + step; x += step / 2 ) {
						var point = bend( x, y );

						if ( 0 === x ) {
							context.moveTo( point.x, point.y );
						} else {
							context.lineTo( point.x, point.y );
						}
					}

					context.stroke();
				}

				for ( x = 0; x <= width + step; x += step ) {
					context.beginPath();

					for ( y = 0; y <= height + step; y += step / 2 ) {
						var vertical = bend( x, y );

						if ( 0 === y ) {
							context.moveTo( vertical.x, vertical.y );
						} else {
							context.lineTo( vertical.x, vertical.y );
						}
					}

					context.stroke();
				}

				context.globalAlpha = 1;
			}
		};
	}

	/* ---------------------------------------------- Туннель из звёзд и колец */

	function tunnelScene() {
		var rings = [];
		var stars = [];

		function build() {
			rings = [];
			stars = [];

			for ( var i = 0; i < 18; i++ ) {
				rings.push( { z: 0.06 + i / 18 } );
			}

			for ( var j = 0; j < Math.min( 200, Math.round( ( width * height ) / 11000 ) ); j++ ) {
				stars.push( {
					angle: Math.random() * Math.PI * 2,
					radius: rand( 0.15, 1 ),
					z: Math.random()
				} );
			}
		}

		return {
			resize: build,
			draw: function () {
				fade( 0.18 );

				var cx = width / 2;
				var cy = height / 2;
				var base = Math.min( width, height ) * 0.75;
				var i;

				context.strokeStyle = accent();
				context.lineWidth = 1.4;

				for ( i = 0; i < rings.length; i++ ) {
					var ring = rings[ i ];

					ring.z -= 0.004 * speed;

					if ( ring.z <= 0.05 ) {
						ring.z = 1;
					}

					var radius = base / ring.z * 0.12;

					context.globalAlpha = Math.min( 0.8, 1 - ring.z );
					context.beginPath();
					context.arc( cx, cy, radius, 0, Math.PI * 2 );
					context.stroke();
				}

				context.fillStyle = '#fff';

				for ( i = 0; i < stars.length; i++ ) {
					var star = stars[ i ];

					star.z -= 0.006 * speed;

					if ( star.z <= 0.04 ) {
						star.z = 1;
						star.angle = Math.random() * Math.PI * 2;
						star.radius = rand( 0.15, 1 );
					}

					var distance = ( star.radius * base * 0.12 ) / star.z;
					var x = cx + Math.cos( star.angle ) * distance;
					var y = cy + Math.sin( star.angle ) * distance;

					context.globalAlpha = Math.min( 1, 1 - star.z );
					context.beginPath();
					context.arc( x, y, Math.max( 0.5, ( 1 - star.z ) * 2.2 ), 0, Math.PI * 2 );
					context.fill();
				}

				context.globalAlpha = 1;
			}
		};
	}

	/* ---------------------------------------------- Дух музыки */

	function musicScene() {
		var phase = 0;
		var bars = 72;
		var levels = [];

		function build() {
			levels = [];

			for ( var i = 0; i < bars; i++ ) {
				levels.push( Math.random() );
			}
		}

		return {
			resize: build,
			draw: function () {
				clear();
				phase += 0.03 * speed;

				var cx = width / 2;
				var cy = height / 2;
				var base = Math.min( width, height ) * 0.22;
				var i;

				context.strokeStyle = accent();
				context.lineWidth = 2;

				for ( i = 0; i < bars; i++ ) {
					var angle = ( Math.PI * 2 / bars ) * i;
					var wave = Math.sin( phase + i * 0.35 ) * 0.5 + Math.sin( phase * 1.7 + i * 0.12 ) * 0.5;

					levels[ i ] += ( Math.abs( wave ) - levels[ i ] ) * 0.12;

					var length = base * ( 0.35 + levels[ i ] * 0.9 );

					context.globalAlpha = 0.35 + levels[ i ] * 0.5;
					context.beginPath();
					context.moveTo( cx + Math.cos( angle ) * base, cy + Math.sin( angle ) * base );
					context.lineTo( cx + Math.cos( angle ) * ( base + length ), cy + Math.sin( angle ) * ( base + length ) );
					context.stroke();
				}

				context.globalAlpha = 0.6;
				context.beginPath();

				for ( i = 0; i <= bars; i++ ) {
					var ringAngle = ( Math.PI * 2 / bars ) * i;
					var radius = base * ( 0.9 + Math.sin( phase * 2 + i * 0.5 ) * 0.06 );
					var x = cx + Math.cos( ringAngle ) * radius;
					var y = cy + Math.sin( ringAngle ) * radius;

					if ( 0 === i ) {
						context.moveTo( x, y );
					} else {
						context.lineTo( x, y );
					}
				}

				context.closePath();
				context.stroke();
				context.globalAlpha = 1;
			}
		};
	}

	var scenes = {
		stars: starsScene,
		matrix: matrixScene,
		maze: mazeScene,
		pipes: pipesScene,
		snow1: snowRoundScene,
		snow2: snowCrystalScene,
		leaves: leavesScene,
		arcade: arcadeScene,
		circuit: circuitScene,
		microchip: microchipScene,
		cyberpunk: cyberpunkScene,
		signals: signalsScene,
		nebula: nebulaScene,
		warp: warpScene,
		tunnel: tunnelScene,
		music: musicScene
	};

	var names = Object.keys( scenes );
	var current = settings.type;

	if ( ! scenes[ current ] ) {
		return;
	}

	function build( name ) {
		current = name;
		scene = scenes[ name ]();
		clear();

		if ( scene.resize ) {
			scene.resize();
		}
	}

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

	function shuffle() {
		if ( names.length < 2 ) {
			return;
		}

		var next = current;

		while ( next === current ) {
			next = names[ Math.floor( Math.random() * names.length ) ];
		}

		build( next );
	}

	function startShuffle() {
		if ( ! settings.shuffle || shuffleTimer ) {
			return;
		}

		var interval = 'number' === typeof settings.shuffleInterval ? settings.shuffleInterval : 60000;

		shuffleTimer = window.setInterval( shuffle, Math.max( 10000, interval ) );
	}

	function stopShuffle() {
		if ( shuffleTimer ) {
			window.clearInterval( shuffleTimer );
			shuffleTimer = null;
		}
	}

	window.addEventListener( 'resize', function () {
		resize();
	} );

	window.addEventListener( 'pointermove', function ( event ) {
		pointer.x = event.clientX / Math.max( 1, window.innerWidth );
		pointer.y = event.clientY / Math.max( 1, window.innerHeight );
	}, { passive: true } );

	window.addEventListener( 'scroll', function () {
		var max = Math.max( 1, document.body.scrollHeight - window.innerHeight );

		pointer.y = Math.min( 1, window.scrollY / max );
	}, { passive: true } );

	document.addEventListener( 'visibilitychange', function () {
		if ( document.hidden ) {
			stop();
			stopShuffle();
		} else {
			start();
			startShuffle();
		}
	} );

	build( current );
	resize();
	start();
	startShuffle();
}() );
