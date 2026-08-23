<?php
/**
 * Вспомогательные функции темы.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Максимальная ширина контейнера в пикселях.
 *
 * Для режима «во всю ширину экрана» возвращается 100vw-эквивалент в px,
 * который используется только как значение $content_width.
 *
 * @return int
 */
function sdon_container_width() {
	$preset = sdon_opt( 'container_width_preset' );

	if ( 'full' === $preset ) {
		return 1920;
	}

	if ( 'custom' === $preset ) {
		return max( 480, min( 2560, (int) sdon_opt( 'container_width_custom' ) ) );
	}

	return max( 480, min( 2560, (int) $preset ) );
}

/**
 * CSS-значение максимальной ширины контейнера.
 *
 * @return string
 */
function sdon_container_max_width_css() {
	if ( 'full' === sdon_opt( 'container_width_preset' ) ) {
		return '100%';
	}

	return sdon_container_width() . 'px';
}

/**
 * Количество колонок макета с учётом наличия виджетов.
 *
 * @return int
 */
function sdon_layout_columns() {
	$columns = (int) sdon_opt( 'layout_columns' );
	$columns = max( 1, min( 3, $columns ) );

	if ( $columns >= 2 && ! is_active_sidebar( 'sidebar-1' ) ) {
		$columns = 1;
	}

	if ( 3 === $columns && ! is_active_sidebar( 'sidebar-2' ) ) {
		$columns = 2;
	}

	if ( is_singular() && ! is_singular( 'post' ) && is_page_template( 'templates/template-fullwidth.php' ) ) {
		$columns = 1;
	}

	/**
	 * Фильтрует итоговое количество колонок макета.
	 *
	 * @param int $columns Количество колонок.
	 */
	return (int) apply_filters( 'sdon_layout_columns', $columns );
}

/**
 * Обрезка текста превью до указанного количества символов.
 *
 * @param int         $length Количество символов, 0 — без ограничения.
 * @param WP_Post|int $post   Запись.
 * @return string
 */
function sdon_trimmed_excerpt( $length = null, $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return '';
	}

	if ( null === $length ) {
		$length = (int) sdon_opt( 'news_excerpt_length' );
	}

	$text = has_excerpt( $post ) ? $post->post_excerpt : $post->post_content;
	$text = strip_shortcodes( $text );
	$text = excerpt_remove_blocks( $text );
	$text = wp_strip_all_tags( $text, true );
	$text = trim( preg_replace( '/\s+/u', ' ', $text ) );

	if ( $length > 0 && mb_strlen( $text ) > $length ) {
		$text = mb_substr( $text, 0, $length );
		$last = mb_strrpos( $text, ' ' );

		if ( false !== $last && $last > 0 ) {
			$text = mb_substr( $text, 0, $last );
		}

		$text .= '…';
	}

	return $text;
}

/**
 * Разрешённые HTML-теги для текстовых настроек (копирайт, текст футера).
 *
 * @return array<string, array<string, bool>>
 */
function sdon_allowed_html() {
	return array(
		'a'      => array(
			'href'   => true,
			'title'  => true,
			'target' => true,
			'rel'    => true,
		),
		'br'     => array(),
		'em'     => array(),
		'strong' => array(),
		'span'   => array( 'class' => true ),
		'b'      => array(),
		'i'      => array(),
		'p'      => array( 'class' => true ),
		'small'  => array(),
		'abbr'   => array( 'title' => true ),
	);
}

/**
 * Текст копирайта футера (значение по умолчанию подставляется динамически).
 *
 * @return string
 */
function sdon_footer_copyright() {
	$copyright = (string) sdon_opt( 'footer_copyright' );

	if ( '' === trim( $copyright ) ) {
		$copyright = sprintf(
			/* translators: 1: текущий год, 2: название сайта. */
			__( '&copy; %1$s %2$s. Все права защищены.', 'sd-on-theme' ),
			gmdate( 'Y' ),
			get_bloginfo( 'name' )
		);
	}

	return $copyright;
}

/**
 * Поддерживаемые социальные сети: ключ настройки => подпись.
 *
 * @return array<string, string>
 */
function sdon_social_networks() {
	return array(
		'social_vk'       => __( 'ВКонтакте', 'sd-on-theme' ),
		'social_telegram' => __( 'Telegram', 'sd-on-theme' ),
		'social_max'      => __( 'МАКС', 'sd-on-theme' ),
		'social_youtube'  => __( 'YouTube', 'sd-on-theme' ),
		'social_rutube'   => __( 'Rutube', 'sd-on-theme' ),
		'social_x'        => __( 'X (Twitter)', 'sd-on-theme' ),
		'social_github'   => __( 'GitHub', 'sd-on-theme' ),
		'social_gitverse' => __( 'GitVerse', 'sd-on-theme' ),
		'social_rss'      => __( 'RSS', 'sd-on-theme' ),
	);
}

/**
 * Список социальных сетей с заданными ссылками.
 *
 * @return array<int, array{key: string, label: string, url: string}>
 */
function sdon_socials() {
	$networks = sdon_social_networks();

	$items = array();

	foreach ( $networks as $key => $label ) {
		$url = (string) sdon_opt( $key );

		if ( '' === trim( $url ) ) {
			continue;
		}

		$items[] = array(
			'key'   => str_replace( 'social_', '', $key ),
			'label' => $label,
			'url'   => $url,
		);
	}

	return $items;
}

/**
 * Активен ли шаблон, показывающий сетку новостей.
 *
 * @return bool
 */
function sdon_is_news_grid() {
	if ( is_page_template( 'templates/template-news.php' ) ) {
		return true;
	}

	return is_home() || is_archive() || is_search();
}

/**
 * Доступные анимации фона: значение настройки => подпись.
 *
 * @return array<string, string>
 */
function sdon_bg_animation_choices() {
	return array(
		'none'      => __( 'Отключить анимацию', 'sd-on-theme' ),
		'stars'     => __( 'Звёзды — «Сквозь вселенную»', 'sd-on-theme' ),
		'matrix'    => __( 'Матрица', 'sd-on-theme' ),
		'maze'      => __( 'Лабиринт', 'sd-on-theme' ),
		'pipes'     => __( 'Трубопровод', 'sd-on-theme' ),
		'snow1'     => __( 'Падающие снежинки — вариант 1', 'sd-on-theme' ),
		'snow2'     => __( 'Падающие снежинки — вариант 2', 'sd-on-theme' ),
		'leaves'    => __( 'Листопад', 'sd-on-theme' ),
		'arcade'    => __( 'Аркада', 'sd-on-theme' ),
		'circuit'   => __( 'Электро плата', 'sd-on-theme' ),
		'microchip' => __( 'Микрочип', 'sd-on-theme' ),
		'cyberpunk' => __( 'Киберпанк', 'sd-on-theme' ),
		'signals'   => __( 'Сеть каналов с бегущими сигналами', 'sd-on-theme' ),
		'nebula'    => __( 'Пролёт сквозь облако частиц', 'sd-on-theme' ),
		'warp'      => __( 'Искривление пространства', 'sd-on-theme' ),
		'tunnel'    => __( 'Туннель из звёзд и колец', 'sd-on-theme' ),
		'music'     => __( 'Дух музыки', 'sd-on-theme' ),
	);
}

/**
 * Составляющие RGB из шестнадцатиричного цвета.
 *
 * @param string $hex Цвет вида #fff или #ffffff.
 * @return array<int, int> Пустой массив для некорректного значения.
 */
function sdon_hex_to_rgb( $hex ) {
	$hex = ltrim( (string) $hex, '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	if ( ! preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
		return array();
	}

	return array(
		hexdec( substr( $hex, 0, 2 ) ),
		hexdec( substr( $hex, 2, 2 ) ),
		hexdec( substr( $hex, 4, 2 ) ),
	);
}

/**
 * Затемнённый вариант цвета.
 *
 * @param string $hex     Цвет вида #fff или #ffffff.
 * @param int    $percent Насколько затемнить, в процентах.
 * @return string
 */
function sdon_darken_hex( $hex, $percent ) {
	$rgb = sdon_hex_to_rgb( $hex );

	if ( empty( $rgb ) ) {
		return (string) $hex;
	}

	$factor = 1 - min( 100, max( 0, (int) $percent ) ) / 100;

	return sprintf(
		'#%02x%02x%02x',
		(int) round( $rgb[0] * $factor ),
		(int) round( $rgb[1] * $factor ),
		(int) round( $rgb[2] * $factor )
	);
}

/**
 * Значение object-position для картинки в карточке новости.
 *
 * @return string
 */
function sdon_card_image_position() {
	$map = array(
		'center'        => 'center center',
		'center-top'    => 'center top',
		'center-bottom' => 'center bottom',
	);

	$value = (string) sdon_opt( 'news_image_position' );

	return isset( $map[ $value ] ) ? $map[ $value ] : $map['center'];
}

/**
 * Проверяет, нужно ли учитывать prefers-reduced-motion на стороне JS.
 *
 * @return bool
 */
function sdon_respect_reduced_motion() {
	return sdon_is( 'respect_reduced_motion' );
}
