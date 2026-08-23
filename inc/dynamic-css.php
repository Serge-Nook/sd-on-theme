<?php
/**
 * Генерация CSS из настроек темы.
 *
 * Все значения выводятся через CSS-переменные, поэтому базовая таблица стилей
 * остаётся статической и кешируемой, а настройки применяются одним inline-блоком.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Список CSS-переменных светлой схемы.
 *
 * @return array<string, string>
 */
function sdon_light_scheme_vars() {
	return array(
		'--sdon-primary'      => sdon_opt( 'color_primary' ),
		'--sdon-secondary'    => sdon_opt( 'color_secondary' ),
		'--sdon-text'         => sdon_opt( 'color_text' ),
		'--sdon-headings'     => sdon_opt( 'color_headings' ),
		'--sdon-link'         => sdon_opt( 'color_link' ),
		'--sdon-link-hover'   => sdon_opt( 'color_link_hover' ),
		'--sdon-surface'      => sdon_opt( 'color_surface' ),
		'--sdon-menu-bg'      => sdon_opt( 'color_menu_bg' ),
		'--sdon-menu-text'    => sdon_opt( 'color_menu_text' ),
		'--sdon-button-bg'    => sdon_opt( 'color_button_bg' ),
		'--sdon-button-text'  => sdon_opt( 'color_button_text' ),
		'--sdon-button-hover' => sdon_opt( 'color_button_hover' ),
		'--sdon-footer-bg'    => sdon_opt( 'color_footer_bg' ),
		'--sdon-footer-text'  => sdon_opt( 'color_footer_text' ),
	);
}

/**
 * Список CSS-переменных тёмной схемы.
 *
 * @return array<string, string>
 */
function sdon_dark_scheme_vars() {
	return array(
		'--sdon-primary'     => sdon_opt( 'dark_color_primary' ),
		'--sdon-text'        => sdon_opt( 'dark_color_text' ),
		'--sdon-headings'    => sdon_opt( 'dark_color_headings' ),
		'--sdon-link'        => sdon_opt( 'dark_color_link' ),
		'--sdon-link-hover'  => sdon_opt( 'dark_color_link_hover' ),
		'--sdon-body-bg'     => sdon_opt( 'dark_color_bg' ),
		'--sdon-surface'     => sdon_opt( 'dark_color_surface' ),
		'--sdon-menu-bg'     => sdon_opt( 'dark_color_menu_bg' ),
		'--sdon-menu-text'   => sdon_opt( 'dark_color_menu_text' ),
		'--sdon-button-bg'   => sdon_opt( 'dark_color_button_bg' ),
		'--sdon-button-text' => sdon_opt( 'dark_color_button_text' ),
		'--sdon-footer-bg'   => sdon_opt( 'dark_color_footer_bg' ),
		'--sdon-footer-text' => sdon_opt( 'dark_color_footer_text' ),
	);
}

/**
 * Собирает набор переменных в строку объявлений.
 *
 * @param array<string, string> $vars Переменные.
 * @return string
 */
function sdon_vars_to_css( $vars ) {
	$css = '';

	foreach ( $vars as $name => $value ) {
		if ( '' === (string) $value ) {
			continue;
		}

		$css .= $name . ':' . $value . ';';
	}

	return $css;
}

/**
 * CSS фона сайта в соответствии с настройками раздела «Фон».
 *
 * @return string
 */
function sdon_background_css() {
	$type = sdon_opt( 'bg_type' );
	$css  = '';

	if ( 'gradient' === $type ) {
		$css .= sprintf(
			'--sdon-body-bg-image:linear-gradient(%s,%s,%s);',
			sdon_opt( 'bg_gradient_direction' ),
			sdon_opt( 'bg_gradient_from' ),
			sdon_opt( 'bg_gradient_to' )
		);
	} elseif ( 'image' === $type && sdon_opt( 'bg_image' ) ) {
		$css .= sprintf( '--sdon-body-bg-image:url("%s");', esc_url( sdon_opt( 'bg_image' ) ) );
		$css .= sprintf( '--sdon-body-bg-size:%s;', sdon_opt( 'bg_image_size' ) );
		$css .= sprintf( '--sdon-body-bg-repeat:%s;', sdon_opt( 'bg_image_repeat' ) );
		$css .= sprintf( '--sdon-body-bg-position:%s;', sdon_opt( 'bg_image_position' ) );
	}

	$css .= sprintf( '--sdon-body-bg:%s;', sdon_opt( 'bg_color' ) );
	$css .= sprintf( '--sdon-body-bg-attachment:%s;', 'fixed' === sdon_opt( 'bg_attachment' ) ? 'fixed' : 'scroll' );

	return $css;
}

/**
 * Цвет блоков с учётом настроенной прозрачности.
 *
 * @param string $hex Цвет блоков в шестнадцатиричном виде.
 * @return string Значение для CSS-свойства background.
 */
function sdon_surface_background( $hex ) {
	$opacity = min( 100, max( 5, (int) sdon_opt( 'content_opacity' ) ) );
	$rgb     = sdon_hex_to_rgb( $hex );

	if ( 100 === $opacity || empty( $rgb ) ) {
		return (string) $hex;
	}

	return sprintf( 'rgba(%1$d,%2$d,%3$d,%4$s)', $rgb[0], $rgb[1], $rgb[2], round( $opacity / 100, 2 ) );
}

/**
 * Итоговый динамический CSS темы.
 *
 * @return string
 */
function sdon_dynamic_css() {
	$slider_height = array(
		'wide'       => '58vh',
		'tall'       => '75vh',
		'fullscreen' => '100vh',
	);

	$height = isset( $slider_height[ sdon_opt( 'slider_height' ) ] ) ? $slider_height[ sdon_opt( 'slider_height' ) ] : $slider_height['wide'];

	$root = sdon_vars_to_css( sdon_light_scheme_vars() );

	$root .= sdon_background_css();
	$root .= sprintf( '--sdon-container:%s;', sdon_container_max_width_css() );
	$root .= sprintf( '--sdon-logo-max-width:%dpx;', (int) sdon_opt( 'logo_max_width' ) );
	$root .= sprintf( '--sdon-font-body:%s;', sdon_font_stack( sdon_opt( 'font_body' ) ) );
	$root .= sprintf( '--sdon-font-headings:%s;', sdon_font_stack( sdon_opt( 'font_headings' ) ) );
	$root .= sprintf( '--sdon-font-menu:%s;', sdon_font_stack( sdon_opt( 'font_menu' ) ) );
	$root .= sprintf( '--sdon-font-size:%dpx;', (int) sdon_opt( 'font_size_base' ) );
	$root .= sprintf( '--sdon-font-weight:%d;', (int) sdon_opt( 'font_weight_body' ) );
	$root .= sprintf( '--sdon-font-weight-headings:%d;', (int) sdon_opt( 'font_weight_headings' ) );
	$root .= sprintf( '--sdon-line-height:%s;', (float) sdon_opt( 'line_height_body' ) );
	$root .= sprintf( '--sdon-news-columns:%d;', (int) sdon_opt( 'news_columns' ) );
	$root .= sprintf( '--sdon-footer-columns:%d;', (int) sdon_opt( 'footer_widget_columns' ) );
	$root .= sprintf( '--sdon-slider-height:%s;', $height );
	$root .= sprintf( '--sdon-slider-speed:%dms;', (int) sdon_opt( 'slider_speed' ) );
	$root .= sprintf( '--sdon-bg-animation-opacity:%s;', (int) sdon_opt( 'bg_animation_opacity' ) / 100 );
	$root .= sprintf( '--sdon-surface-bg:%s;', sdon_surface_background( sdon_opt( 'color_surface' ) ) );
	$root .= sprintf( '--sdon-card-border-width:%dpx;', sdon_is( 'news_card_border' ) ? min( 8, max( 0, (int) sdon_opt( 'news_card_border_width' ) ) ) : 0 );
	$root .= sprintf( '--sdon-card-border-color:%s;', sdon_opt( 'news_card_border_color' ) );
	$root .= sprintf( '--sdon-card-image-position:%s;', sdon_card_image_position() );

	if ( sdon_monster_is_enabled() ) {
		$monster = sanitize_hex_color( (string) sdon_opt( 'monster_color' ) );
		$monster = $monster ? $monster : (string) sdon_default( 'monster_color' );
		$accent  = sanitize_hex_color( (string) sdon_opt( 'monster_accent' ) );
		$accent  = $accent ? $accent : (string) sdon_default( 'monster_accent' );

		$root .= sprintf( '--sdon-monster-size:%dpx;', min( 480, max( 100, (int) sdon_opt( 'monster_size' ) ) ) );
		$root .= sprintf( '--sdon-monster-color:%s;', $monster );
		$root .= sprintf( '--sdon-monster-accent:%s;', $accent );
		$root .= sprintf( '--sdon-monster-shadow:%s;', sdon_darken_hex( $monster, 35 ) );
	}

	$css = ':root{' . $root . '}';

	$dark = sdon_vars_to_css( sdon_dark_scheme_vars() );

	if ( '' !== $dark ) {
		$mode = sdon_opt( 'color_scheme_mode' );

		$dark .= sprintf( '--sdon-surface-bg:%s;', sdon_surface_background( sdon_opt( 'dark_color_surface' ) ) );

		if ( 'dark' === $mode ) {
			$css .= ':root{' . $dark . '}';
		} else {
			$css .= '[data-sdon-scheme="dark"]{' . $dark . '}';

			if ( 'auto' === $mode ) {
				// Резервный вариант, если JavaScript отключён.
				$css .= '@media (prefers-color-scheme: dark){:root:not([data-sdon-scheme="light"]){' . $dark . '}}';
			}
		}
	}

	for ( $i = 1; $i <= SDON_MAX_SLIDES; $i++ ) {
		$overlay = min( 100, max( 0, (int) sdon_opt( "slide_{$i}_overlay" ) ) );

		$css .= sprintf( '.sdon-slide--%1$d{--sdon-slide-overlay:%2$s;}', $i, $overlay / 100 );
	}

	/**
	 * Фильтрует динамический CSS темы.
	 *
	 * @param string $css Готовый CSS.
	 */
	$css = apply_filters( 'sdon_dynamic_css', $css );

	return wp_strip_all_tags( $css );
}
