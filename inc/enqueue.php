<?php
/**
 * Подключение стилей и скриптов.
 *
 * Скрипты отключённых функций не подключаются вовсе, остальные загружаются
 * с атрибутом defer — это требование к производительности из ТЗ.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Регистрирует скрипт темы с отложенной загрузкой.
 *
 * @param string   $handle Идентификатор.
 * @param string   $file   Путь относительно assets/js/.
 * @param string[] $deps   Зависимости.
 * @return void
 */
function sdon_enqueue_deferred_script( $handle, $file, $deps = array() ) {
	wp_enqueue_script( $handle, SDON_URI . '/assets/js/' . $file, $deps, SDON_VERSION, true );
	wp_script_add_data( $handle, 'strategy', 'defer' );
}

/**
 * Стили и скрипты фронтенда.
 *
 * @return void
 */
function sdon_enqueue_assets() {
	$google = sdon_google_fonts_url();

	if ( '' !== $google ) {
		wp_enqueue_style( 'sdon-google-fonts', $google, array(), SDON_VERSION );
	}

	wp_enqueue_style( 'sdon-main', SDON_URI . '/assets/css/main.css', array(), SDON_VERSION );
	wp_add_inline_style( 'sdon-main', sdon_dynamic_css() );

	$font_face = sdon_font_face_css();

	if ( '' !== $font_face ) {
		wp_add_inline_style( 'sdon-main', $font_face );
	}

	sdon_enqueue_deferred_script( 'sdon-navigation', 'navigation.js' );
	wp_localize_script(
		'sdon-navigation',
		'sdonNav',
		array(
			'menuType'       => sdon_opt( 'menu_type' ),
			'submenuTrigger' => sdon_opt( 'menu_submenu_trigger' ),
			'backToTop'      => sdon_is( 'back_to_top' ),
			'i18n'           => array(
				'openMenu'  => __( 'Открыть меню', 'sd-on-theme' ),
				'closeMenu' => __( 'Закрыть меню', 'sd-on-theme' ),
				'expand'    => __( 'Раскрыть подменю', 'sd-on-theme' ),
			),
		)
	);

	if ( sdon_slider_is_visible() ) {
		sdon_enqueue_deferred_script( 'sdon-slider', 'slider.js' );
		wp_localize_script(
			'sdon-slider',
			'sdonSlider',
			array(
				'autoplay'      => sdon_is( 'slider_autoplay' ),
				'interval'      => (int) sdon_opt( 'slider_interval' ),
				'speed'         => (int) sdon_opt( 'slider_speed' ),
				'swipe'         => sdon_is( 'slider_swipe' ),
				'keyboard'      => sdon_is( 'slider_keyboard' ),
				'reducedMotion' => sdon_respect_reduced_motion(),
			)
		);
	}

	if ( 'none' !== sdon_opt( 'bg_animation' ) ) {
		sdon_enqueue_deferred_script( 'sdon-bg-animation', 'bg-animation.js' );
		wp_localize_script(
			'sdon-bg-animation',
			'sdonBg',
			array(
				'type'            => sdon_opt( 'bg_animation' ),
				'opacity'         => (int) sdon_opt( 'bg_animation_opacity' ) / 100,
				'speed'           => max( 10, (int) sdon_opt( 'bg_animation_speed' ) ) / 100,
				'shuffle'         => sdon_is( 'bg_animation_shuffle' ),
				'shuffleInterval' => max( 10, (int) sdon_opt( 'bg_animation_shuffle_interval' ) ) * 1000,
				'enableMobile'    => sdon_is( 'bg_animation_mobile' ),
				'reducedMotion'   => sdon_respect_reduced_motion(),
			)
		);
	}

	if ( sdon_dark_mode_is_available() ) {
		sdon_enqueue_deferred_script( 'sdon-dark-mode', 'dark-mode.js' );
		wp_localize_script(
			'sdon-dark-mode',
			'sdonDark',
			array(
				'mode'   => sdon_opt( 'color_scheme_mode' ),
				'toggle' => sdon_is( 'dark_mode_toggle' ),
				'i18n'   => array(
					'switchToDark'  => __( 'Включить тёмную тему', 'sd-on-theme' ),
					'switchToLight' => __( 'Включить светлую тему', 'sd-on-theme' ),
				),
			)
		);
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'sdon_enqueue_assets' );

/**
 * Стили редактора блоков, чтобы контент выглядел как на сайте.
 *
 * @return void
 */
function sdon_enqueue_editor_assets() {
	wp_enqueue_style( 'sdon-editor', SDON_URI . '/assets/css/editor.css', array(), SDON_VERSION );
	wp_add_inline_style( 'sdon-editor', sdon_dynamic_css() );
}
add_action( 'enqueue_block_editor_assets', 'sdon_enqueue_editor_assets' );

/**
 * Доступна ли тёмная схема (переключатель или автоматический режим).
 *
 * @return bool
 */
function sdon_dark_mode_is_available() {
	$mode = sdon_opt( 'color_scheme_mode' );

	return 'auto' === $mode || sdon_is( 'dark_mode_toggle' );
}

/**
 * Ранний inline-скрипт: выставляет схему до отрисовки, чтобы не было «мигания».
 *
 * @return void
 */
function sdon_print_color_scheme_bootstrap() {
	$mode = sdon_opt( 'color_scheme_mode' );

	if ( 'dark' === $mode ) {
		wp_print_inline_script_tag( 'document.documentElement.dataset.sdonScheme="dark";' );
		return;
	}

	if ( ! sdon_dark_mode_is_available() ) {
		return;
	}

	$auto = 'auto' === $mode ? 'true' : 'false';

	wp_print_inline_script_tag(
		'(function(){try{var s=localStorage.getItem("sdon-scheme");if(!s&&' . $auto . '){s=window.matchMedia("(prefers-color-scheme: dark)").matches?"dark":"light";}if(s){document.documentElement.dataset.sdonScheme=s;}}catch(e){}})();'
	);
}
add_action( 'wp_head', 'sdon_print_color_scheme_bootstrap', 1 );

/**
 * Полностью отключает ленивую загрузку, если пользователь снял галочку.
 *
 * @param bool $enabled Значение по умолчанию.
 * @return bool
 */
function sdon_filter_lazy_loading( $enabled ) {
	return sdon_is( 'lazy_loading' ) ? $enabled : false;
}
add_filter( 'wp_lazy_loading_enabled', 'sdon_filter_lazy_loading' );
