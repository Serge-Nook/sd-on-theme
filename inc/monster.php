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
 * Персонажи раздела «Монстр».
 *
 * @return array<string, string>
 */
function sdon_monster_creature_choices() {
	return array(
		'monster' => __( 'Монстр', 'sd-on-theme' ),
		'vampire' => __( 'Девушка-вампир', 'sd-on-theme' ),
		'cthulhu' => __( 'Ктулху', 'sd-on-theme' ),
		'hellcat' => __( 'Адский кот мейн-кун', 'sd-on-theme' ),
		'robot'   => __( 'Робот-убийца', 'sd-on-theme' ),
		'random'  => __( 'Хаотично — случайный персонаж', 'sd-on-theme' ),
	);
}

/**
 * Выбранный персонаж с проверкой по списку вариантов.
 *
 * @return string
 */
function sdon_monster_creature() {
	$creature = (string) sdon_opt( 'monster_creature' );

	return array_key_exists( $creature, sdon_monster_creature_choices() ) ? $creature : 'random';
}

/**
 * Персонажи, которые выводятся в разметке.
 *
 * @return array<int, string>
 */
function sdon_monster_active_creatures() {
	$creature = sdon_monster_creature();

	if ( 'random' !== $creature ) {
		return array( $creature );
	}

	$choices = sdon_monster_creature_choices();

	unset( $choices['random'] );

	return array_keys( $choices );
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
		'creature'      => sdon_monster_creature(),
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
 * При хаотичном режиме в разметку попадают все персонажи, а JavaScript
 * перед каждым появлением показывает одного из них.
 *
 * @return void
 */
function sdon_monster_markup() {
	if ( ! sdon_monster_is_enabled() ) {
		return;
	}

	$side      = sdon_monster_side();
	$creatures = sdon_monster_active_creatures();
	?>
	<div
		class="sdon-monster"
		data-sdon-monster
		data-side="<?php echo esc_attr( $side ); ?>"
		data-creature="<?php echo esc_attr( sdon_monster_creature() ); ?>"
		hidden
	>
		<button class="sdon-monster__hit" type="button" data-sdon-monster-hit>
			<span class="screen-reader-text"><?php esc_html_e( 'Прогнать монстра', 'sd-on-theme' ); ?></span>
			<?php foreach ( $creatures as $index => $creature ) : ?>
				<span
					class="sdon-monster__body"
					data-sdon-monster-creature="<?php echo esc_attr( $creature ); ?>"
					aria-hidden="true"
					<?php echo 0 === $index ? '' : 'hidden'; ?>
				>
					<?php sdon_monster_creature_svg( $creature ); ?>
				</span>
			<?php endforeach; ?>
		</button>
	</div>
	<?php
}
add_action( 'wp_footer', 'sdon_monster_markup', 20 );
