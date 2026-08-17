<?php
/**
 * Шрифты темы: встроенные наборы и пользовательские загрузки.
 *
 * Пользовательские шрифты хранятся в опции sdon_custom_fonts и подключаются
 * через @font-face, без внешних запросов к сторонним сервисам.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

/** Опция со списком пользовательских шрифтов. */
define( 'SDON_FONTS_OPTION', 'sdon_custom_fonts' );

/**
 * Встроенные (не требующие загрузки извне) наборы шрифтов.
 *
 * @return array<string, array{label: string, stack: string}>
 */
function sdon_builtin_fonts() {
	return array(
		'system'    => array(
			'label' => __( 'Системный (быстрый, без загрузки)', 'sd-on-theme' ),
			'stack' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
		),
		'georgia'   => array(
			'label' => __( 'Georgia (с засечками)', 'sd-on-theme' ),
			'stack' => 'Georgia, "Times New Roman", "PT Serif", serif',
		),
		'verdana'   => array(
			'label' => __( 'Verdana (без засечек)', 'sd-on-theme' ),
			'stack' => 'Verdana, Geneva, Tahoma, sans-serif',
		),
		'tahoma'    => array(
			'label' => __( 'Tahoma', 'sd-on-theme' ),
			'stack' => 'Tahoma, Verdana, Segoe, sans-serif',
		),
		'trebuchet' => array(
			'label' => __( 'Trebuchet MS', 'sd-on-theme' ),
			'stack' => '"Trebuchet MS", "Lucida Grande", Tahoma, sans-serif',
		),
		'monospace' => array(
			'label' => __( 'Моноширинный', 'sd-on-theme' ),
			'stack' => 'ui-monospace, "Cascadia Mono", Consolas, "Liberation Mono", monospace',
		),
	);
}

/**
 * Пользовательские шрифты, загруженные через админ-панель.
 *
 * @return array<string, array{label: string, files: array<string, string>}>
 */
function sdon_custom_fonts() {
	$fonts = get_option( SDON_FONTS_OPTION, array() );

	return is_array( $fonts ) ? $fonts : array();
}

/**
 * Список всех доступных шрифтов для селектов настроек.
 *
 * @return array<string, string> Ключ => подпись.
 */
function sdon_font_choices() {
	$choices = array();

	foreach ( sdon_builtin_fonts() as $key => $font ) {
		$choices[ $key ] = $font['label'];
	}

	foreach ( sdon_custom_fonts() as $key => $font ) {
		$choices[ 'custom:' . $key ] = sprintf(
			/* translators: %s: название загруженного шрифта. */
			__( '%s (загруженный)', 'sd-on-theme' ),
			$font['label']
		);
	}

	return $choices;
}

/**
 * CSS-значение font-family для выбранного шрифта.
 *
 * @param string $value Значение настройки шрифта.
 * @return string
 */
function sdon_font_stack( $value ) {
	$builtin = sdon_builtin_fonts();

	if ( isset( $builtin[ $value ] ) ) {
		return $builtin[ $value ]['stack'];
	}

	if ( 0 === strpos( (string) $value, 'custom:' ) ) {
		$key   = substr( $value, 7 );
		$fonts = sdon_custom_fonts();

		if ( isset( $fonts[ $key ] ) ) {
			return sprintf( '"%s", %s', $fonts[ $key ]['label'], $builtin['system']['stack'] );
		}
	}

	return $builtin['system']['stack'];
}

/**
 * Правила @font-face для загруженных шрифтов, которые реально используются.
 *
 * @return string
 */
function sdon_font_face_css() {
	$fonts = sdon_custom_fonts();

	if ( empty( $fonts ) ) {
		return '';
	}

	$used = array();

	foreach ( array( 'font_body', 'font_headings', 'font_menu' ) as $setting ) {
		$value = (string) sdon_opt( $setting );

		if ( 0 === strpos( $value, 'custom:' ) ) {
			$used[] = substr( $value, 7 );
		}
	}

	$used = array_unique( $used );
	$css  = '';

	// WOFF2 — приоритетный формат для веба, далее по убыванию поддержки.
	$formats = array(
		'woff2' => 'woff2',
		'woff'  => 'woff',
		'ttf'   => 'truetype',
		'otf'   => 'opentype',
	);

	foreach ( $used as $key ) {
		if ( ! isset( $fonts[ $key ] ) ) {
			continue;
		}

		$sources = array();

		foreach ( $formats as $ext => $format ) {
			if ( ! empty( $fonts[ $key ]['files'][ $ext ] ) ) {
				$sources[] = sprintf( 'url("%s") format("%s")', esc_url( $fonts[ $key ]['files'][ $ext ] ), $format );
			}
		}

		if ( empty( $sources ) ) {
			continue;
		}

		$css .= sprintf(
			'@font-face{font-family:"%s";src:%s;font-display:swap;font-style:normal;font-weight:100 900;}',
			esc_attr( $fonts[ $key ]['label'] ),
			implode( ',', $sources )
		);
	}

	return $css;
}
