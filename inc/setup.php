<?php
/**
 * Регистрация возможностей темы, меню и областей виджетов.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Базовая настройка темы.
 *
 * @return void
 */
function sdon_setup() {
	load_theme_textdomain( 'sd-on-theme', SDON_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 120,
			'width'       => 480,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_image_size( 'sdon-card', 800, 500, true );
	add_image_size( 'sdon-slide', 1920, 900, true );

	register_nav_menus(
		array(
			'primary' => __( 'Главное меню', 'sd-on-theme' ),
			'footer'  => __( 'Меню в футере', 'sd-on-theme' ),
			'social'  => __( 'Меню социальных сетей', 'sd-on-theme' ),
		)
	);
}
add_action( 'after_setup_theme', 'sdon_setup' );

/**
 * Ширина контента для встраиваемых объектов.
 *
 * @return void
 */
function sdon_content_width() {
	$GLOBALS['content_width'] = sdon_container_width();
}
add_action( 'after_setup_theme', 'sdon_content_width', 20 );

/**
 * Области виджетов: боковые колонки и футер.
 *
 * @return void
 */
function sdon_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Боковая колонка', 'sd-on-theme' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Основная боковая колонка (макет из 2 или 3 колонок).', 'sd-on-theme' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Вторая боковая колонка', 'sd-on-theme' ),
			'id'            => 'sidebar-2',
			'description'   => __( 'Используется только в макете из 3 колонок.', 'sd-on-theme' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	$columns = max( 1, (int) sdon_opt( 'footer_widget_columns' ) );

	for ( $i = 1; $i <= $columns; $i++ ) {
		register_sidebar(
			array(
				'name'          => sprintf( /* translators: %d: номер колонки футера. */ __( 'Футер — колонка %d', 'sd-on-theme' ), $i ),
				'id'            => 'footer-' . $i,
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);
	}
}
add_action( 'widgets_init', 'sdon_widgets_init' );

/**
 * Количество материалов на странице архивов и шаблона новостей.
 *
 * @param WP_Query $query Основной запрос.
 * @return void
 */
function sdon_posts_per_page( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_home() || $query->is_archive() || $query->is_search() ) {
		$query->set( 'posts_per_page', (int) sdon_opt( 'news_per_page' ) );
	}
}
add_action( 'pre_get_posts', 'sdon_posts_per_page' );

/**
 * Классы body, описывающие выбранный макет.
 *
 * @param string[] $classes Классы.
 * @return string[]
 */
function sdon_body_classes( $classes ) {
	$classes[] = 'sdon-layout-' . sdon_layout_columns();
	$classes[] = 'sdon-sidebar-' . sdon_opt( 'sidebar_position' );
	$classes[] = 'sdon-mobile-' . sdon_opt( 'mobile_order' );
	$classes[] = 'sdon-menu-' . sdon_opt( 'menu_type' );

	if ( 'full' === sdon_opt( 'container_width_preset' ) ) {
		$classes[] = 'sdon-container-full';
	}

	if ( sdon_is( 'menu_sticky' ) ) {
		$classes[] = 'sdon-has-sticky-header';
	}

	if ( sdon_is( 'sticky_sidebar' ) ) {
		$classes[] = 'sdon-sticky-sidebar';
	}

	if ( 'none' !== sdon_opt( 'bg_animation' ) ) {
		$classes[] = 'sdon-has-bg-animation';
	}

	if ( sdon_is( 'menu_uppercase' ) ) {
		$classes[] = 'sdon-menu-uppercase';
	}

	return $classes;
}
add_filter( 'body_class', 'sdon_body_classes' );

/**
 * Лёгкая ссылка «Читать далее» вместо стандартной.
 *
 * @return string
 */
function sdon_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'sdon_excerpt_more' );

/**
 * Отключение emoji-скриптов WordPress ради производительности.
 *
 * @return void
 */
function sdon_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'sdon_disable_emojis' );
