<?php
/**
 * SVG-персонажи для раздела «Монстр».
 *
 * Каждый персонаж — inline SVG в системе координат 220×260, раскрашенный
 * переменными --sdon-monster-color / -accent / -shadow, поэтому цвет
 * настраивается из панели управления, а изображения не нужны.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Общие определения градиентов и свечения.
 *
 * Идентификаторы включают имя персонажа: на странице может быть несколько
 * SVG одновременно, а дублировать id нельзя.
 *
 * @param string $creature Идентификатор персонажа.
 * @return void
 */
function sdon_monster_defs( $creature ) {
	?>
	<defs>
		<linearGradient id="sdon-monster-skin-<?php echo esc_attr( $creature ); ?>" x1="0" y1="0" x2="1" y2="1">
			<stop offset="0" stop-color="var(--sdon-monster-accent)" />
			<stop offset="0.55" stop-color="var(--sdon-monster-color)" />
			<stop offset="1" stop-color="var(--sdon-monster-shadow)" />
		</linearGradient>
		<radialGradient id="sdon-monster-glow-<?php echo esc_attr( $creature ); ?>" cx="0.5" cy="0.5" r="0.5">
			<stop offset="0" stop-color="var(--sdon-monster-accent)" stop-opacity="0.85" />
			<stop offset="1" stop-color="var(--sdon-monster-accent)" stop-opacity="0" />
		</radialGradient>
	</defs>
	<ellipse class="sdon-monster__aura" cx="112" cy="150" rx="104" ry="112" fill="url(#sdon-monster-glow-<?php echo esc_attr( $creature ); ?>)" />
	<?php
}

/**
 * Классический монстр.
 *
 * @param string $creature Идентификатор персонажа.
 * @return void
 */
function sdon_monster_svg_monster( $creature ) {
	?>
	<g class="sdon-monster__tentacles">
		<path d="M52 214c-16 12-34 8-44 26" />
		<path d="M74 232c-10 18-30 22-44 16" />
		<path d="M104 240c-2 16-14 26-30 28" />
	</g>

	<path class="sdon-monster__horns" d="M64 74 46 18l40 34zM160 74l18-56-40 34z" />

	<path
		class="sdon-monster__skin"
		fill="url(#sdon-monster-skin-<?php echo esc_attr( $creature ); ?>)"
		d="M110 26c46 0 78 32 82 78 3 34-6 62-24 86-16 22-34 34-58 34s-42-12-58-34C34 166 25 138 28 104c4-46 36-78 82-78z"
	/>

	<path class="sdon-monster__spines" d="M110 12l12 26h-24zM72 30l6 24-22-12zM148 30l16 12-22 12z" />

	<g class="sdon-monster__eye" data-sdon-monster-eye>
		<ellipse cx="82" cy="118" rx="26" ry="30" fill="#f8fbff" />
		<ellipse cx="150" cy="118" rx="20" ry="24" fill="#f8fbff" />
		<circle class="sdon-monster__pupil" data-sdon-monster-pupil cx="82" cy="120" r="11" />
		<circle class="sdon-monster__pupil" data-sdon-monster-pupil cx="150" cy="120" r="9" />
		<path class="sdon-monster__brows" d="M56 84l50 12M170 88l-36 8" />
	</g>

	<path class="sdon-monster__mouth" d="M70 176c14 18 62 20 82 2-6 26-30 40-46 40s-32-16-36-42z" />
	<path class="sdon-monster__teeth" d="M84 182l10 16 10-16zM110 184l10 16 10-16zM136 180l8 14 8-14z" />

	<path class="sdon-monster__claw" d="M182 150c22 6 32 22 30 42-14-6-22-4-32 4-8-14-6-32 2-46z" />
	<?php
}

/**
 * Девушка-вампир.
 *
 * @param string $creature Идентификатор персонажа.
 * @return void
 */
function sdon_monster_svg_vampire( $creature ) {
	?>
	<g class="sdon-monster__bats">
		<path d="M24 46c8-8 14-2 18 2 4-4 10-10 18-2-8 2-12 8-18 6-6 2-10-4-18-6z" />
		<path d="M176 30c6-6 11-2 14 1 3-3 8-7 14-1-6 1-9 6-14 5-5 1-8-3-14-5z" />
	</g>

	<path class="sdon-monster__cloak" d="M110 96c40 0 62 26 74 74 8 32 6 60-6 82H42c-12-22-14-50-6-82 12-48 34-74 74-74z" />
	<path class="sdon-monster__collar" fill="url(#sdon-monster-skin-<?php echo esc_attr( $creature ); ?>)" d="M110 96c22 0 34 10 42 30-14 26-28 40-42 40s-28-14-42-40c8-20 20-30 42-30z" />

	<path class="sdon-monster__hair" d="M110 12c40 0 62 26 62 66 0 34-6 62-16 84-6-30-4-60-10-84-10 12-24 18-36 18s-26-6-36-18c-6 24-4 54-10 84-10-22-16-50-16-84 0-40 22-66 62-66z" />

	<path class="sdon-monster__pale" d="M110 26c26 0 42 18 42 46 0 30-18 54-42 54s-42-24-42-54c0-28 16-46 42-46z" />

	<g class="sdon-monster__eye" data-sdon-monster-eye>
		<ellipse cx="94" cy="66" rx="13" ry="9" fill="#fdf7fb" />
		<ellipse cx="128" cy="66" rx="13" ry="9" fill="#fdf7fb" />
		<circle class="sdon-monster__pupil sdon-monster__pupil--blood" data-sdon-monster-pupil cx="94" cy="66" r="6" />
		<circle class="sdon-monster__pupil sdon-monster__pupil--blood" data-sdon-monster-pupil cx="128" cy="66" r="6" />
		<path class="sdon-monster__brows" d="M80 50l26 6M142 50l-26 6" />
	</g>

	<path class="sdon-monster__lips" d="M96 92c8 6 20 6 28 0-4 12-10 18-14 18s-10-6-14-18z" />
	<path class="sdon-monster__fangs" d="M100 96l4 12 4-12zM114 96l4 12 4-12z" />
	<path class="sdon-monster__blood" d="M118 108c2 8 0 14-2 18-4-6-4-12 2-18z" />

	<g class="sdon-monster__nails">
		<path d="M60 202c-14 6-22 18-22 34 10-6 18-6 26 0-6-10-8-22-4-34z" />
		<path d="M160 202c14 6 22 18 22 34-10-6-18-6-26 0 6-10 8-22 4-34z" />
	</g>
	<?php
}

/**
 * Ктулху.
 *
 * @param string $creature Идентификатор персонажа.
 * @return void
 */
function sdon_monster_svg_cthulhu( $creature ) {
	?>
	<g class="sdon-monster__wings">
		<path d="M56 96C30 78 12 82 2 104c18 2 26 14 30 34 10-16 18-30 24-42z" />
		<path d="M164 96c26-18 44-14 54 8-18 2-26 14-30 34-10-16-18-30-24-42z" />
	</g>

	<path
		class="sdon-monster__skin"
		fill="url(#sdon-monster-skin-<?php echo esc_attr( $creature ); ?>)"
		d="M110 18c44 0 72 30 72 74 0 30-10 52-28 70-14 14-28 22-44 22s-30-8-44-22c-18-18-28-40-28-70 0-44 28-74 72-74z"
	/>

	<path class="sdon-monster__ridge" d="M110 6l10 22H100zM74 20l4 20-18-10zM146 20l18 10-22 10z" />

	<g class="sdon-monster__eye" data-sdon-monster-eye>
		<ellipse cx="86" cy="76" rx="20" ry="16" fill="#f2fff6" />
		<ellipse cx="140" cy="76" rx="20" ry="16" fill="#f2fff6" />
		<ellipse class="sdon-monster__pupil sdon-monster__pupil--slit" data-sdon-monster-pupil cx="86" cy="76" rx="5" ry="13" />
		<ellipse class="sdon-monster__pupil sdon-monster__pupil--slit" data-sdon-monster-pupil cx="140" cy="76" rx="5" ry="13" />
		<path class="sdon-monster__brows" d="M62 54l44 8M164 54l-44 8" />
	</g>

	<g class="sdon-monster__beard">
		<path d="M78 118c-10 24-8 48 4 72" />
		<path d="M96 122c-6 28-4 54 6 78" />
		<path d="M114 122c6 28 8 54 2 78" />
		<path d="M132 118c12 24 12 48 2 72" />
		<path d="M62 112c-16 18-20 40-12 62" />
		<path d="M158 112c16 18 20 40 12 62" />
	</g>

	<path class="sdon-monster__suckers" d="M92 150a5 5 0 1 0 0.1 0zM112 168a5 5 0 1 0 0.1 0zM128 146a5 5 0 1 0 0.1 0z" />
	<?php
}

/**
 * Адский кот мейн-кун.
 *
 * @param string $creature Идентификатор персонажа.
 * @return void
 */
function sdon_monster_svg_hellcat( $creature ) {
	?>
	<g class="sdon-monster__flames">
		<path d="M40 214c-12-18-6-36 8-48-2 18 8 26 14 38-6 8-14 12-22 10z" />
		<path d="M180 214c12-18 6-36-8-48 2 18-8 26-14 38 6 8 14 12 22 10z" />
	</g>

	<path class="sdon-monster__tail" d="M176 176c26 10 38 34 30 62-16-10-28-6-38 8-10-22-6-50 8-70z" />

	<path class="sdon-monster__mane" d="M110 30c48 0 76 30 76 76 0 30-12 54-30 70-16 14-30 22-46 22s-30-8-46-22c-18-16-30-40-30-70 0-46 28-76 76-76z" />

	<path class="sdon-monster__ears" d="M52 66 40 14l42 30zM168 66l12-52-42 30z" />
	<path class="sdon-monster__horns" d="M76 30 62 0l30 20zM144 30 158 0l-30 20z" />

	<path
		class="sdon-monster__skin"
		fill="url(#sdon-monster-skin-<?php echo esc_attr( $creature ); ?>)"
		d="M110 44c38 0 62 26 62 62 0 40-26 76-62 76s-62-36-62-76c0-36 24-62 62-62z"
	/>

	<g class="sdon-monster__eye" data-sdon-monster-eye>
		<ellipse cx="84" cy="98" rx="19" ry="17" fill="#fff6d8" />
		<ellipse cx="136" cy="98" rx="19" ry="17" fill="#fff6d8" />
		<ellipse class="sdon-monster__pupil sdon-monster__pupil--slit" data-sdon-monster-pupil cx="84" cy="98" rx="5" ry="14" />
		<ellipse class="sdon-monster__pupil sdon-monster__pupil--slit" data-sdon-monster-pupil cx="136" cy="98" rx="5" ry="14" />
	</g>

	<path class="sdon-monster__nose" d="M110 124l10 8-10 8-10-8z" />
	<path class="sdon-monster__mouth" d="M92 142c8 12 28 12 36 0-2 18-12 26-18 26s-16-8-18-26z" />
	<path class="sdon-monster__teeth" d="M98 144l4 12 5-12zM114 144l4 12 5-12z" />

	<g class="sdon-monster__whiskers">
		<path d="M74 128 30 118M74 136 32 138M146 128l44-10M146 136l42 2" />
	</g>
	<?php
}

/**
 * Робот-убийца.
 *
 * @param string $creature Идентификатор персонажа.
 * @return void
 */
function sdon_monster_svg_robot( $creature ) {
	?>
	<g class="sdon-monster__cables">
		<path d="M56 210c-18 8-28 22-30 44" />
		<path d="M164 210c18 8 28 22 30 44" />
	</g>

	<path class="sdon-monster__antenna" d="M108 8h6v30h-6z" />
	<circle class="sdon-monster__lamp" cx="111" cy="8" r="8" />

	<path
		class="sdon-monster__skin"
		fill="url(#sdon-monster-skin-<?php echo esc_attr( $creature ); ?>)"
		d="M64 40h94a18 18 0 0 1 18 18v96a18 18 0 0 1-18 18H64a18 18 0 0 1-18-18V58a18 18 0 0 1 18-18z"
	/>

	<path class="sdon-monster__plate" d="M46 96h-16v40h16zM192 96h-16v40h16z" />
	<path class="sdon-monster__visor" d="M64 72h94v46H64z" />

	<g class="sdon-monster__eye" data-sdon-monster-eye>
		<rect class="sdon-monster__scan" x="64" y="88" width="94" height="12" />
		<rect class="sdon-monster__pupil sdon-monster__pupil--laser" data-sdon-monster-pupil x="98" y="80" width="26" height="28" rx="6" />
	</g>

	<path class="sdon-monster__grill" d="M74 132h74v6H74zM82 146h58v6H82zM90 160h42v6H90z" />

	<path class="sdon-monster__shoulders" d="M40 176h140l-14 34H54z" />
	<path class="sdon-monster__cannon" d="M170 150h44v22h-44z" />
	<circle class="sdon-monster__lamp sdon-monster__lamp--core" cx="111" cy="192" r="10" />
	<?php
}

/**
 * Разметка выбранного персонажа.
 *
 * @param string $creature Идентификатор персонажа.
 * @return void
 */
function sdon_monster_creature_svg( $creature ) {
	$renderers = array(
		'monster' => 'sdon_monster_svg_monster',
		'vampire' => 'sdon_monster_svg_vampire',
		'cthulhu' => 'sdon_monster_svg_cthulhu',
		'hellcat' => 'sdon_monster_svg_hellcat',
		'robot'   => 'sdon_monster_svg_robot',
	);

	if ( ! isset( $renderers[ $creature ] ) ) {
		return;
	}
	?>
	<svg class="sdon-monster__svg" viewBox="0 0 220 260" role="presentation" focusable="false">
		<?php
		sdon_monster_defs( $creature );
		call_user_func( $renderers[ $creature ], $creature );
		?>
	</svg>
	<?php
}
