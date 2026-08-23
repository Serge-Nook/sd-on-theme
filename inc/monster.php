<?php
/**
 * Анимированный монстр, выезжающий из-за края экрана.
 *
 * Монстр включается отдельно для десктопа и мобильных, появляется через
 * случайные интервалы, прячется при медленном приближении курсора и уходит
 * со звуком по клику. Разметка — inline SVG, поэтому дополнительных
 * изображений не требуется.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Включён ли монстр хотя бы для одного типа устройств.
 *
 * @return bool
 */
function sdon_monster_is_enabled() {
	return sdon_is( 'monster_desktop' ) || sdon_is( 'monster_mobile' );
}

/**
 * Варианты сторон появления монстра.
 *
 * @return array<string, string>
 */
function sdon_monster_side_choices() {
	return array(
		'left'   => __( 'Слева', 'sd-on-theme' ),
		'right'  => __( 'Справа', 'sd-on-theme' ),
		'random' => __( 'Случайно — слева или справа', 'sd-on-theme' ),
	);
}

/**
 * Варианты звука при клике по монстру.
 *
 * Встроенные звуки синтезируются в браузере через Web Audio API, поэтому
 * тема не тянет за собой аудиофайлы. При выборе «свой звук» проигрывается
 * файл, загруженный в медиабиблиотеку.
 *
 * @return array<string, string>
 */
function sdon_monster_sound_choices() {
	return array(
		'squeak' => __( 'Писк (испуганный)', 'sd-on-theme' ),
		'growl'  => __( 'Рык', 'sd-on-theme' ),
		'boing'  => __( 'Пружина', 'sd-on-theme' ),
		'glitch' => __( 'Глитч', 'sd-on-theme' ),
		'custom' => __( 'Свой звук (загрузить файл)', 'sd-on-theme' ),
		'none'   => __( 'Без звука', 'sd-on-theme' ),
	);
}

/**
 * Адрес пользовательского звука, если он выбран и загружен.
 *
 * @return string
 */
function sdon_monster_sound_file() {
	if ( 'custom' !== sdon_monster_sound() ) {
		return '';
	}

	return esc_url_raw( (string) sdon_opt( 'monster_sound_file' ) );
}

/**
 * Выбранная сторона появления с проверкой по списку вариантов.
 *
 * @return string
 */
function sdon_monster_side() {
	$side = (string) sdon_opt( 'monster_side' );

	return array_key_exists( $side, sdon_monster_side_choices() ) ? $side : 'random';
}

/**
 * Выбранный звук с проверкой по списку вариантов.
 *
 * @return string
 */
function sdon_monster_sound() {
	$sound = (string) sdon_opt( 'monster_sound' );

	return array_key_exists( $sound, sdon_monster_sound_choices() ) ? $sound : 'none';
}

/**
 * Настройки монстра для JavaScript.
 *
 * @return array<string, mixed>
 */
function sdon_monster_settings() {
	$min = max( 3, (int) sdon_opt( 'monster_min_delay' ) );
	$max = max( $min, (int) sdon_opt( 'monster_max_delay' ) );

	return array(
		'desktop'       => sdon_is( 'monster_desktop' ),
		'mobile'        => sdon_is( 'monster_mobile' ),
		'side'          => sdon_monster_side(),
		'minDelay'      => $min * 1000,
		'maxDelay'      => $max * 1000,
		'cooldown'      => max( 0, (int) sdon_opt( 'monster_cooldown' ) ) * 1000,
		'shyDistance'   => max( 40, (int) sdon_opt( 'monster_shy_distance' ) ),
		'sound'         => sdon_monster_sound(),
		'soundFile'     => sdon_monster_sound_file(),
		'volume'        => min( 100, max( 0, (int) sdon_opt( 'monster_volume' ) ) ) / 100,
		'reducedMotion' => sdon_respect_reduced_motion(),
	);
}

/**
 * Разметка монстра в подвале страницы.
 *
 * @return void
 */
function sdon_monster_markup() {
	if ( ! sdon_monster_is_enabled() ) {
		return;
	}

	$side = sdon_monster_side();
	?>
	<div class="sdon-monster" data-sdon-monster data-side="<?php echo esc_attr( $side ); ?>" hidden>
		<button class="sdon-monster__hit" type="button" data-sdon-monster-hit>
			<span class="screen-reader-text"><?php esc_html_e( 'Прогнать монстра', 'sd-on-theme' ); ?></span>
			<span class="sdon-monster__body" aria-hidden="true">
				<svg class="sdon-monster__svg" viewBox="0 0 220 260" role="presentation" focusable="false">
					<defs>
						<linearGradient id="sdon-monster-skin" x1="0" y1="0" x2="1" y2="1">
							<stop offset="0" stop-color="var(--sdon-monster-accent)" />
							<stop offset="0.55" stop-color="var(--sdon-monster-color)" />
							<stop offset="1" stop-color="var(--sdon-monster-shadow)" />
						</linearGradient>
						<radialGradient id="sdon-monster-glow" cx="0.5" cy="0.5" r="0.5">
							<stop offset="0" stop-color="var(--sdon-monster-accent)" stop-opacity="0.85" />
							<stop offset="1" stop-color="var(--sdon-monster-accent)" stop-opacity="0" />
						</radialGradient>
					</defs>

					<ellipse class="sdon-monster__aura" cx="112" cy="150" rx="104" ry="112" fill="url(#sdon-monster-glow)" />

					<g class="sdon-monster__tentacles">
						<path d="M52 214c-16 12-34 8-44 26" />
						<path d="M74 232c-10 18-30 22-44 16" />
						<path d="M104 240c-2 16-14 26-30 28" />
					</g>

					<path class="sdon-monster__horns" d="M64 74 46 18l40 34zM160 74l18-56-40 34z" />

					<path
						class="sdon-monster__skin"
						fill="url(#sdon-monster-skin)"
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
				</svg>
			</span>
		</button>
	</div>
	<?php
}
add_action( 'wp_footer', 'sdon_monster_markup', 20 );
