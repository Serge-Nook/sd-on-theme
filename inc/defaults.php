<?php
/**
 * Значения по умолчанию и доступ к настройкам темы.
 *
 * Настройки хранятся в theme_mods текущей темы, поэтому они доступны
 * в WordPress Customizer (живой предпросмотр) без дополнительного кода.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Полный список настроек темы со значениями по умолчанию.
 *
 * @return array<string, mixed>
 */
function sdon_defaults() {
	static $defaults = null;

	if ( null !== $defaults ) {
		return $defaults;
	}

	$defaults = array(
		// 1. Основные настройки.
		'color_scheme_mode'      => 'light',
		'dark_mode_toggle'       => true,
		'back_to_top'            => true,
		'breadcrumbs'            => true,
		'preloader'              => false,

		// 2. Макет сайта.
		'layout_columns'         => 2,
		'sidebar_position'       => 'right',
		'mobile_order'           => 'content-first',
		'container_width_preset' => '1200',
		'container_width_custom' => 1200,
		'sticky_sidebar'         => true,

		// 3. Шапка сайта.
		'header_layout'          => 'left',
		'header_show_logo'       => true,
		'logo_max_width'         => 220,
		'header_show_title'      => true,
		'header_title_custom'    => '',
		'header_tagline'         => '',
		'header_show_search'     => true,

		// 4. Меню.
		'menu_sticky'            => true,
		'menu_type'              => 'multilevel',
		'menu_submenu_trigger'   => 'hover',
		'menu_uppercase'         => false,

		// 5. Слайдер.
		'slider_enable'          => true,
		'slider_count'           => 3,
		'slider_height'          => 'wide',
		'slider_arrows'          => true,
		'slider_dots'            => true,
		'slider_autoplay'        => true,
		'slider_interval'        => 6000,
		'slider_speed'           => 600,
		'slider_swipe'           => true,
		'slider_keyboard'        => true,

		// 6. Фон.
		'bg_type'                => 'color',
		'bg_color'               => '#f4f6fb',
		'bg_gradient_from'       => '#1f2a5b',
		'bg_gradient_to'         => '#12b1c8',
		'bg_gradient_direction'  => '180deg',
		'bg_image'               => '',
		'bg_image_size'          => 'cover',
		'bg_image_repeat'        => 'no-repeat',
		'bg_image_position'      => 'center center',
		'bg_attachment'          => 'scroll',
		'bg_animation'           => 'none',
		'bg_animation_opacity'   => 40,
		'bg_animation_mobile'    => false,

		// 7. Шрифты.
		'font_body'              => 'system',
		'font_headings'          => 'system',
		'font_menu'              => 'system',
		'font_size_base'         => 17,
		'font_weight_body'       => 400,
		'font_weight_headings'   => 700,
		'line_height_body'       => 1.65,

		// Цветовая схема (светлая).
		'color_primary'          => '#2f6df6',
		'color_secondary'        => '#12b1c8',
		'color_text'             => '#1f2430',
		'color_headings'         => '#101322',
		'color_link'             => '#2f6df6',
		'color_link_hover'       => '#0b46c2',
		'color_surface'          => '#ffffff',
		'color_menu_bg'          => '#ffffff',
		'color_menu_text'        => '#101322',
		'color_button_bg'        => '#2f6df6',
		'color_button_text'      => '#ffffff',
		'color_button_hover'     => '#0b46c2',
		'color_footer_bg'        => '#101322',
		'color_footer_text'      => '#c9cede',

		// Цветовая схема (тёмная).
		'dark_color_primary'     => '#5b8dff',
		'dark_color_text'        => '#dfe3ef',
		'dark_color_headings'    => '#ffffff',
		'dark_color_link'        => '#7ea6ff',
		'dark_color_link_hover'  => '#a9c4ff',
		'dark_color_bg'          => '#0d1017',
		'dark_color_surface'     => '#151a24',
		'dark_color_menu_bg'     => '#151a24',
		'dark_color_menu_text'   => '#e7ebf6',
		'dark_color_button_bg'   => '#5b8dff',
		'dark_color_button_text' => '#0d1017',
		'dark_color_footer_bg'   => '#080a10',
		'dark_color_footer_text' => '#aab2c6',

		// 8. Новости.
		'news_columns'           => 3,
		'news_per_page'          => 9,
		'news_excerpt_length'    => 150,
		'news_show_image'        => true,
		'news_show_category'     => true,
		'news_show_date'         => true,
		'news_show_author'       => true,
		'news_show_comments'     => true,
		'news_show_button'       => true,
		'news_button_text'       => '',

		// 9. Футер.
		'footer_widget_columns'  => 3,
		'footer_show_menu'       => true,
		'footer_text'            => '',
		'footer_copyright'       => '',
		'footer_show_socials'    => true,
		'social_vk'              => '',
		'social_telegram'        => '',
		'social_youtube'         => '',
		'social_x'               => '',
		'social_github'          => '',
		'social_rss'             => '',

		// 10. Дополнительные настройки.
		'lazy_loading'           => true,
		'seo_open_graph'         => true,
		'seo_schema'             => true,
		'respect_reduced_motion' => true,
	);

	// Значения по умолчанию для 10 слайдов.
	for ( $i = 1; $i <= SDON_MAX_SLIDES; $i++ ) {
		$defaults[ "slide_{$i}_image" ]       = '';
		$defaults[ "slide_{$i}_title" ]       = '';
		$defaults[ "slide_{$i}_text" ]        = '';
		$defaults[ "slide_{$i}_button" ]      = true;
		$defaults[ "slide_{$i}_button_text" ] = '';
		$defaults[ "slide_{$i}_url" ]         = '';
		$defaults[ "slide_{$i}_overlay" ]     = 40;
	}

	/**
	 * Позволяет дочерней теме изменить значения по умолчанию.
	 *
	 * @param array<string, mixed> $defaults Значения по умолчанию.
	 */
	return apply_filters( 'sdon_defaults', $defaults );
}

/**
 * Значение по умолчанию для одной настройки.
 *
 * @param string $key Ключ настройки.
 * @return mixed
 */
function sdon_default( $key ) {
	$defaults = sdon_defaults();

	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : null;
}

/**
 * Текущее значение настройки темы.
 *
 * @param string $key Ключ настройки.
 * @return mixed
 */
function sdon_opt( $key ) {
	$value = get_theme_mod( $key, sdon_default( $key ) );

	/**
	 * Фильтрует значение настройки темы.
	 *
	 * @param mixed  $value Значение.
	 * @param string $key   Ключ настройки.
	 */
	return apply_filters( 'sdon_option', $value, $key );
}

/**
 * Логические настройки (чекбоксы) как bool.
 *
 * @param string $key Ключ настройки.
 * @return bool
 */
function sdon_is( $key ) {
	return (bool) sdon_opt( $key );
}
