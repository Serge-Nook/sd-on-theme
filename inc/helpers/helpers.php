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
 * Список социальных сетей с заданными ссылками.
 *
 * @return array<int, array{key: string, label: string, url: string}>
 */
function sdon_socials() {
	$networks = array(
		'social_vk'       => __( 'ВКонтакте', 'sd-on-theme' ),
		'social_telegram' => __( 'Telegram', 'sd-on-theme' ),
		'social_youtube'  => __( 'YouTube', 'sd-on-theme' ),
		'social_x'        => __( 'X (Twitter)', 'sd-on-theme' ),
		'social_github'   => __( 'GitHub', 'sd-on-theme' ),
		'social_rss'      => __( 'RSS', 'sd-on-theme' ),
	);

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
 * Проверяет, нужно ли учитывать prefers-reduced-motion на стороне JS.
 *
 * @return bool
 */
function sdon_respect_reduced_motion() {
	return sdon_is( 'respect_reduced_motion' );
}
