<?php
/**
 * Настройки темы в WordPress Customizer (Внешний вид → Настроить → Настройки темы).
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

require_once SDON_DIR . '/inc/customizer/class-sdon-range-control.php';

/**
 * Настройки, которые обновляются в предпросмотре без перезагрузки страницы.
 *
 * @return array<string, array{var: string, unit: string}>
 */
function sdon_postmessage_map() {
	$map = array(
		'color_primary'      => '--sdon-primary',
		'color_secondary'    => '--sdon-secondary',
		'color_text'         => '--sdon-text',
		'color_headings'     => '--sdon-headings',
		'color_link'         => '--sdon-link',
		'color_link_hover'   => '--sdon-link-hover',
		'color_menu_bg'      => '--sdon-menu-bg',
		'color_menu_text'    => '--sdon-menu-text',
		'color_button_bg'    => '--sdon-button-bg',
		'color_button_text'  => '--sdon-button-text',
		'color_button_hover' => '--sdon-button-hover',
		'color_footer_bg'    => '--sdon-footer-bg',
		'color_footer_text'  => '--sdon-footer-text',
		'bg_color'           => '--sdon-body-bg',
	);

	$result = array();

	foreach ( $map as $id => $var ) {
		$result[ $id ] = array(
			'var'  => $var,
			'unit' => '',
		);
	}

	$result['font_size_base']       = array(
		'var'  => '--sdon-font-size',
		'unit' => 'px',
	);
	$result['logo_max_width']       = array(
		'var'  => '--sdon-logo-max-width',
		'unit' => 'px',
	);
	$result['line_height_body']     = array(
		'var'  => '--sdon-line-height',
		'unit' => '',
	);
	$result['font_weight_body']     = array(
		'var'  => '--sdon-font-weight',
		'unit' => '',
	);
	$result['font_weight_headings'] = array(
		'var'  => '--sdon-font-weight-headings',
		'unit' => '',
	);
	$result['news_columns']         = array(
		'var'  => '--sdon-news-columns',
		'unit' => '',
	);

	return $result;
}

/**
 * Добавляет настройку и контрол одной вызовом.
 *
 * @param WP_Customize_Manager $wp_customize Менеджер настроек.
 * @param string               $id           Идентификатор настройки.
 * @param array<string, mixed> $args         Параметры контрола.
 * @return void
 */
function sdon_customize_add( $wp_customize, $id, $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'section'         => 'sdon_general',
			'label'           => '',
			'description'     => '',
			'type'            => 'text',
			'choices'         => array(),
			'input_attrs'     => array(),
			'sanitize'        => null,
			'unit'            => '',
			'active_callback' => null,
			'priority'        => 10,
		)
	);

	$sanitize_defaults = array(
		'checkbox' => 'sdon_sanitize_checkbox',
		'select'   => 'sdon_sanitize_select',
		'radio'    => 'sdon_sanitize_select',
		'number'   => 'sdon_sanitize_number',
		'range'    => 'sdon_sanitize_number',
		'text'     => 'sanitize_text_field',
		'textarea' => 'sdon_sanitize_textarea',
		'url'      => 'esc_url_raw',
		'color'    => 'sanitize_hex_color',
		'image'    => 'esc_url_raw',
		'audio'    => 'esc_url_raw',
		'html'     => 'sdon_sanitize_html',
		'font'     => 'sdon_sanitize_font',
	);

	$sanitize = $args['sanitize'];

	if ( ! $sanitize ) {
		$sanitize = isset( $sanitize_defaults[ $args['type'] ] ) ? $sanitize_defaults[ $args['type'] ] : 'sanitize_text_field';
	}

	$postmessage = sdon_postmessage_map();

	$wp_customize->add_setting(
		$id,
		array(
			'default'           => sdon_default( $id ),
			'sanitize_callback' => $sanitize,
			'transport'         => isset( $postmessage[ $id ] ) ? 'postMessage' : 'refresh',
		)
	);

	$control_args = array(
		'label'       => $args['label'],
		'description' => $args['description'],
		'section'     => $args['section'],
		'settings'    => $id,
		'priority'    => $args['priority'],
	);

	if ( $args['active_callback'] ) {
		$control_args['active_callback'] = $args['active_callback'];
	}

	if ( ! empty( $args['input_attrs'] ) ) {
		$control_args['input_attrs'] = $args['input_attrs'];
	}

	switch ( $args['type'] ) {
		case 'color':
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, $control_args ) );
			break;

		case 'image':
			$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $id, $control_args ) );
			break;

		case 'audio':
			$control_args['mime_type'] = 'audio';
			$wp_customize->add_control( new WP_Customize_Upload_Control( $wp_customize, $id, $control_args ) );
			break;

		case 'range':
			$control_args['unit'] = $args['unit'];
			$wp_customize->add_control( new SDON_Range_Control( $wp_customize, $id, $control_args ) );
			break;

		case 'select':
		case 'radio':
			$control_args['type']    = $args['type'];
			$control_args['choices'] = $args['choices'];
			$wp_customize->add_control( $id, $control_args );
			break;

		case 'font':
			$control_args['type']    = 'select';
			$control_args['choices'] = sdon_font_choices();
			$wp_customize->add_control( $id, $control_args );
			break;

		case 'html':
			$control_args['type'] = 'textarea';
			$wp_customize->add_control( $id, $control_args );
			break;

		default:
			$control_args['type'] = $args['type'];
			$wp_customize->add_control( $id, $control_args );
	}
}

/**
 * Регистрация панели, разделов и настроек темы.
 *
 * @param WP_Customize_Manager $wp_customize Менеджер настроек.
 * @return void
 */
function sdon_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	$wp_customize->add_panel(
		'sdon_panel',
		array(
			'title'       => __( 'Настройки темы', 'sd-on-theme' ),
			'description' => __( 'Внешний вид сайта настраивается полностью здесь — без правки PHP, CSS или JavaScript.', 'sd-on-theme' ),
			'priority'    => 10,
		)
	);

	$sections = array(
		'sdon_general'    => array( __( '1. Основные настройки', 'sd-on-theme' ), __( 'Цветовая схема сайта и общие элементы интерфейса.', 'sd-on-theme' ) ),
		'sdon_layout'     => array( __( '2. Макет сайта', 'sd-on-theme' ), __( 'Количество колонок и максимальная ширина контейнера.', 'sd-on-theme' ) ),
		'sdon_header'     => array( __( '3. Шапка сайта', 'sd-on-theme' ), __( 'Логотип, название, подпись и поиск.', 'sd-on-theme' ) ),
		'sdon_menu'       => array( __( '4. Меню', 'sd-on-theme' ), __( 'Тип главного меню и его поведение при прокрутке.', 'sd-on-theme' ) ),
		'sdon_slider'     => array( __( '5. Слайдер', 'sd-on-theme' ), __( 'Слайдер главной страницы: до 10 слайдов с отдельными настройками.', 'sd-on-theme' ) ),
		'sdon_background' => array( __( '6. Фон', 'sd-on-theme' ), __( 'Цвет, градиент, изображение и анимация фона.', 'sd-on-theme' ) ),
		'sdon_fonts'      => array( __( '7. Шрифты', 'sd-on-theme' ), __( 'Шрифты текста, заголовков и меню. Свои шрифты загружаются в разделе «Внешний вид → Настройки темы».', 'sd-on-theme' ) ),
		'sdon_colors'     => array( __( '8. Цвета', 'sd-on-theme' ), __( 'Светлая и тёмная цветовые схемы.', 'sd-on-theme' ) ),
		'sdon_news'       => array( __( '9. Новости', 'sd-on-theme' ), __( 'Сетка новостей и содержимое карточки материала.', 'sd-on-theme' ) ),
		'sdon_footer'     => array( __( '10. Футер', 'sd-on-theme' ), __( 'Колонки виджетов, меню, соцсети и копирайт.', 'sd-on-theme' ) ),
		'sdon_monster'    => array( __( '11. Монстр', 'sd-on-theme' ), __( 'Анимированный монстр, выезжающий из-за края экрана.', 'sd-on-theme' ) ),
		'sdon_extra'      => array( __( '12. Дополнительные настройки', 'sd-on-theme' ), __( 'SEO, производительность и доступность.', 'sd-on-theme' ) ),
	);

	$priority = 10;

	foreach ( $sections as $id => $data ) {
		$wp_customize->add_section(
			$id,
			array(
				'title'       => $data[0],
				'description' => $data[1],
				'panel'       => 'sdon_panel',
				'priority'    => $priority,
			)
		);

		$priority += 10;
	}

	sdon_customize_general( $wp_customize );
	sdon_customize_layout( $wp_customize );
	sdon_customize_header( $wp_customize );
	sdon_customize_menu( $wp_customize );
	sdon_customize_slider( $wp_customize );
	sdon_customize_background( $wp_customize );
	sdon_customize_fonts( $wp_customize );
	sdon_customize_colors( $wp_customize );
	sdon_customize_news( $wp_customize );
	sdon_customize_footer( $wp_customize );
	sdon_customize_monster( $wp_customize );
	sdon_customize_extra( $wp_customize );

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'footer_copyright',
			array(
				'selector'        => '.sdon-footer__copyright',
				'render_callback' => static function () {
					return wp_kses( sdon_footer_copyright(), sdon_allowed_html() );
				},
			)
		);
	}
}
add_action( 'customize_register', 'sdon_customize_register' );

/**
 * Раздел «Основные настройки».
 *
 * @param WP_Customize_Manager $wp_customize Менеджер настроек.
 * @return void
 */
function sdon_customize_general( $wp_customize ) {
	sdon_customize_add(
		$wp_customize,
		'color_scheme_mode',
		array(
			'section'     => 'sdon_general',
			'label'       => __( 'Цветовая схема', 'sd-on-theme' ),
			'description' => __( 'Светлая, тёмная или автоматически по системной настройке устройства.', 'sd-on-theme' ),
			'type'        => 'radio',
			'choices'     => array(
				'light' => __( 'Светлая', 'sd-on-theme' ),
				'dark'  => __( 'Тёмная', 'sd-on-theme' ),
				'auto'  => __( 'Автоматически (по настройке устройства)', 'sd-on-theme' ),
			),
		)
	);

	sdon_customize_add(
		$wp_customize,
		'dark_mode_toggle',
		array(
			'section'     => 'sdon_general',
			'label'       => __( 'Показывать переключатель тёмной темы', 'sd-on-theme' ),
			'description' => __( 'Кнопка в шапке сайта, позволяющая посетителю сменить схему.', 'sd-on-theme' ),
			'type'        => 'checkbox',
		)
	);

	sdon_customize_add(
		$wp_customize,
		'back_to_top',
		array(
			'section' => 'sdon_general',
			'label'   => __( 'Кнопка «Наверх»', 'sd-on-theme' ),
			'type'    => 'checkbox',
		)
	);

	sdon_customize_add(
		$wp_customize,
		'breadcrumbs',
		array(
			'section'     => 'sdon_general',
			'label'       => __( 'Хлебные крошки', 'sd-on-theme' ),
			'description' => __( 'Навигационная цепочка с микроразметкой Schema.org.', 'sd-on-theme' ),
			'type'        => 'checkbox',
		)
	);
}

/**
 * Раздел «Макет сайта».
 *
 * @param WP_Customize_Manager $wp_customize Менеджер настроек.
 * @return void
 */
function sdon_customize_layout( $wp_customize ) {
	sdon_customize_add(
		$wp_customize,
		'layout_columns',
		array(
			'section'     => 'sdon_layout',
			'label'       => __( 'Количество колонок', 'sd-on-theme' ),
			'description' => __( 'На мобильных устройствах колонки автоматически выстраиваются вертикально.', 'sd-on-theme' ),
			'type'        => 'radio',
			'choices'     => array(
				'1' => __( '1 колонка — контент во всю ширину', 'sd-on-theme' ),
				'2' => __( '2 колонки — контент и боковая колонка', 'sd-on-theme' ),
				'3' => __( '3 колонки — контент и две боковые колонки', 'sd-on-theme' ),
			),
		)
	);

	sdon_customize_add(
		$wp_customize,
		'sidebar_position',
		array(
			'section' => 'sdon_layout',
			'label'   => __( 'Положение боковой колонки', 'sd-on-theme' ),
			'type'    => 'radio',
			'choices' => array(
				'right' => __( 'Справа', 'sd-on-theme' ),
				'left'  => __( 'Слева', 'sd-on-theme' ),
			),
		)
	);

	sdon_customize_add(
		$wp_customize,
		'mobile_order',
		array(
			'section'     => 'sdon_layout',
			'label'       => __( 'Порядок блоков на мобильных', 'sd-on-theme' ),
			'description' => __( 'Что показывать первым при вертикальном расположении блоков.', 'sd-on-theme' ),
			'type'        => 'radio',
			'choices'     => array(
				'content-first' => __( 'Сначала контент', 'sd-on-theme' ),
				'sidebar-first' => __( 'Сначала боковые колонки', 'sd-on-theme' ),
			),
		)
	);

	sdon_customize_add(
		$wp_customize,
		'container_width_preset',
		array(
			'section' => 'sdon_layout',
			'label'   => __( 'Максимальная ширина сайта', 'sd-on-theme' ),
			'type'    => 'select',
			'choices' => array(
				'1000'   => '1000 px',
				'1100'   => '1100 px',
				'1200'   => '1200 px',
				'1300'   => '1300 px',
				'1400'   => '1400 px',
				'custom' => __( 'Собственное значение', 'sd-on-theme' ),
				'full'   => __( 'Во всю ширину экрана', 'sd-on-theme' ),
			),
		)
	);

	sdon_customize_add(
		$wp_customize,
		'container_width_custom',
		array(
			'section'     => 'sdon_layout',
			'label'       => __( 'Своя ширина, px', 'sd-on-theme' ),
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 480,
				'max'  => 2560,
				'step' => 10,
			),
		)
	);

	sdon_customize_add(
		$wp_customize,
		'sticky_sidebar',
		array(
			'section' => 'sdon_layout',
			'label'   => __( '«Прилипающая» боковая колонка', 'sd-on-theme' ),
			'type'    => 'checkbox',
		)
	);
}

/**
 * Раздел «Шапка сайта».
 *
 * @param WP_Customize_Manager $wp_customize Менеджер настроек.
 * @return void
 */
function sdon_customize_header( $wp_customize ) {
	sdon_customize_add(
		$wp_customize,
		'header_layout',
		array(
			'section' => 'sdon_header',
			'label'   => __( 'Расположение шапки', 'sd-on-theme' ),
			'type'    => 'radio',
			'choices' => array(
				'left'   => __( 'Логотип слева, меню справа', 'sd-on-theme' ),
				'center' => __( 'Логотип по центру, меню под ним', 'sd-on-theme' ),
			),
		)
	);

	sdon_customize_add(
		$wp_customize,
		'header_show_logo',
		array(
			'section'     => 'sdon_header',
			'label'       => __( 'Показывать логотип', 'sd-on-theme' ),
			'description' => __( 'Загрузить, заменить или удалить логотип можно в разделе «Свойства сайта → Логотип».', 'sd-on-theme' ),
			'type'        => 'checkbox',
		)
	);

	sdon_customize_add(
		$wp_customize,
		'logo_max_width',
		array(
			'section'     => 'sdon_header',
			'label'       => __( 'Максимальная ширина логотипа', 'sd-on-theme' ),
			'type'        => 'range',
			'unit'        => 'px',
			'input_attrs' => array(
				'min'  => 60,
				'max'  => 480,
				'step' => 10,
			),
		)
	);

	sdon_customize_add(
		$wp_customize,
		'header_show_title',
		array(
			'section' => 'sdon_header',
			'label'   => __( 'Показывать название сайта', 'sd-on-theme' ),
			'type'    => 'checkbox',
		)
	);

	sdon_customize_add(
		$wp_customize,
		'header_title_custom',
		array(
			'section'     => 'sdon_header',
			'label'       => __( 'Своё название в шапке', 'sd-on-theme' ),
			'description' => __( 'Если пусто — используется название сайта из общих настроек WordPress.', 'sd-on-theme' ),
			'type'        => 'text',
		)
	);

	sdon_customize_add(
		$wp_customize,
		'header_tagline',
		array(
			'section'     => 'sdon_header',
			'label'       => __( 'Подпись под названием', 'sd-on-theme' ),
			'description' => __( 'Например: Игры • Новости • Обзоры', 'sd-on-theme' ),
			'type'        => 'text',
		)
	);

	sdon_customize_add(
		$wp_customize,
		'header_show_search',
		array(
			'section' => 'sdon_header',
			'label'   => __( 'Поиск в шапке', 'sd-on-theme' ),
			'type'    => 'checkbox',
		)
	);
}

/**
 * Раздел «Меню».
 *
 * @param WP_Customize_Manager $wp_customize Менеджер настроек.
 * @return void
 */
function sdon_customize_menu( $wp_customize ) {
	sdon_customize_add(
		$wp_customize,
		'menu_sticky',
		array(
			'section'     => 'sdon_menu',
			'label'       => __( 'Закреплённое меню', 'sd-on-theme' ),
			'description' => __( 'Меню остаётся в верхней части экрана при прокрутке страницы.', 'sd-on-theme' ),
			'type'        => 'checkbox',
		)
	);

	sdon_customize_add(
		$wp_customize,
		'menu_type',
		array(
			'section'     => 'sdon_menu',
			'label'       => __( 'Тип главного меню', 'sd-on-theme' ),
			'description' => __( 'Многоуровневое поддерживает до трёх уровней вложенности. Раздвижное открывает боковую панель.', 'sd-on-theme' ),
			'type'        => 'radio',
			'choices'     => array(
				'multilevel' => __( 'Многоуровневое', 'sd-on-theme' ),
				'offcanvas'  => __( 'Раздвижное', 'sd-on-theme' ),
			),
		)
	);

	sdon_customize_add(
		$wp_customize,
		'menu_submenu_trigger',
		array(
			'section' => 'sdon_menu',
			'label'   => __( 'Открытие подменю на ПК', 'sd-on-theme' ),
			'type'    => 'radio',
			'choices' => array(
				'hover' => __( 'При наведении', 'sd-on-theme' ),
				'click' => __( 'По нажатию', 'sd-on-theme' ),
			),
		)
	);

	sdon_customize_add(
		$wp_customize,
		'menu_uppercase',
		array(
			'section' => 'sdon_menu',
			'label'   => __( 'Пункты меню заглавными буквами', 'sd-on-theme' ),
			'type'    => 'checkbox',
		)
	);
}

/**
 * Раздел «Слайдер».
 *
 * @param WP_Customize_Manager $wp_customize Менеджер настроек.
 * @return void
 */
function sdon_customize_slider( $wp_customize ) {
	sdon_customize_add(
		$wp_customize,
		'slider_enable',
		array(
			'section'     => 'sdon_slider',
			'label'       => __( 'Включить слайдер на главной странице', 'sd-on-theme' ),
			'type'        => 'checkbox',
			'description' => __( 'Скрипт слайдера не загружается, если он отключён.', 'sd-on-theme' ),
		)
	);

	$enabled = static function () {
		return (bool) sdon_opt( 'slider_enable' );
	};

	sdon_customize_add(
		$wp_customize,
		'slider_count',
		array(
			'section'         => 'sdon_slider',
			'label'           => __( 'Количество слайдов', 'sd-on-theme' ),
			'type'            => 'range',
			'input_attrs'     => array(
				'min'  => 1,
				'max'  => SDON_MAX_SLIDES,
				'step' => 1,
			),
			'active_callback' => $enabled,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'slider_height',
		array(
			'section'         => 'sdon_slider',
			'label'           => __( 'Высота слайдера', 'sd-on-theme' ),
			'type'            => 'select',
			'choices'         => array(
				'wide'       => __( 'Широкоформатный', 'sd-on-theme' ),
				'tall'       => __( 'Высокий', 'sd-on-theme' ),
				'fullscreen' => __( 'Полноэкранный', 'sd-on-theme' ),
			),
			'active_callback' => $enabled,
		)
	);

	$controls = array(
		'slider_arrows'   => __( 'Кнопки «предыдущий / следующий»', 'sd-on-theme' ),
		'slider_dots'     => __( 'Индикаторы текущего слайда', 'sd-on-theme' ),
		'slider_autoplay' => __( 'Автоматическое переключение', 'sd-on-theme' ),
		'slider_swipe'    => __( 'Переключение свайпом на смартфонах', 'sd-on-theme' ),
		'slider_keyboard' => __( 'Переключение стрелками клавиатуры', 'sd-on-theme' ),
	);

	foreach ( $controls as $id => $label ) {
		sdon_customize_add(
			$wp_customize,
			$id,
			array(
				'section'         => 'sdon_slider',
				'label'           => $label,
				'type'            => 'checkbox',
				'active_callback' => $enabled,
			)
		);
	}

	sdon_customize_add(
		$wp_customize,
		'slider_interval',
		array(
			'section'         => 'sdon_slider',
			'label'           => __( 'Время показа слайда', 'sd-on-theme' ),
			'type'            => 'range',
			'unit'            => __( 'мс', 'sd-on-theme' ),
			'input_attrs'     => array(
				'min'  => 2000,
				'max'  => 20000,
				'step' => 500,
			),
			'active_callback' => $enabled,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'slider_speed',
		array(
			'section'         => 'sdon_slider',
			'label'           => __( 'Скорость анимации', 'sd-on-theme' ),
			'type'            => 'range',
			'unit'            => __( 'мс', 'sd-on-theme' ),
			'input_attrs'     => array(
				'min'  => 100,
				'max'  => 2000,
				'step' => 50,
			),
			'active_callback' => $enabled,
		)
	);

	$overlay_choices = array();

	for ( $percent = 0; $percent <= 100; $percent += 10 ) {
		$overlay_choices[ (string) $percent ] = $percent . '%';
	}

	for ( $i = 1; $i <= SDON_MAX_SLIDES; $i++ ) {
		$visible = static function () use ( $i, $enabled ) {
			return $enabled() && (int) sdon_opt( 'slider_count' ) >= $i;
		};

		sdon_customize_add(
			$wp_customize,
			"slide_{$i}_image",
			array(
				'section'         => 'sdon_slider',
				/* translators: %d: номер слайда. */
				'label'           => sprintf( __( 'Слайд %d — изображение фона', 'sd-on-theme' ), $i ),
				'type'            => 'image',
				'active_callback' => $visible,
			)
		);

		sdon_customize_add(
			$wp_customize,
			"slide_{$i}_title",
			array(
				'section'         => 'sdon_slider',
				/* translators: %d: номер слайда. */
				'label'           => sprintf( __( 'Слайд %d — заголовок', 'sd-on-theme' ), $i ),
				'type'            => 'text',
				'active_callback' => $visible,
			)
		);

		sdon_customize_add(
			$wp_customize,
			"slide_{$i}_text",
			array(
				'section'         => 'sdon_slider',
				/* translators: %d: номер слайда. */
				'label'           => sprintf( __( 'Слайд %d — текст', 'sd-on-theme' ), $i ),
				'type'            => 'textarea',
				'active_callback' => $visible,
			)
		);

		sdon_customize_add(
			$wp_customize,
			"slide_{$i}_button",
			array(
				'section'         => 'sdon_slider',
				/* translators: %d: номер слайда. */
				'label'           => sprintf( __( 'Слайд %d — показывать кнопку', 'sd-on-theme' ), $i ),
				'type'            => 'checkbox',
				'active_callback' => $visible,
			)
		);

		sdon_customize_add(
			$wp_customize,
			"slide_{$i}_button_text",
			array(
				'section'         => 'sdon_slider',
				/* translators: %d: номер слайда. */
				'label'           => sprintf( __( 'Слайд %d — текст кнопки', 'sd-on-theme' ), $i ),
				'description'     => __( 'По умолчанию: «Читать далее».', 'sd-on-theme' ),
				'type'            => 'text',
				'active_callback' => $visible,
			)
		);

		sdon_customize_add(
			$wp_customize,
			"slide_{$i}_url",
			array(
				'section'         => 'sdon_slider',
				/* translators: %d: номер слайда. */
				'label'           => sprintf( __( 'Слайд %d — ссылка кнопки', 'sd-on-theme' ), $i ),
				'type'            => 'url',
				'active_callback' => $visible,
			)
		);

		sdon_customize_add(
			$wp_customize,
			"slide_{$i}_overlay",
			array(
				'section'         => 'sdon_slider',
				/* translators: %d: номер слайда. */
				'label'           => sprintf( __( 'Слайд %d — затемнение изображения', 'sd-on-theme' ), $i ),
				'description'     => __( 'Помогает сохранить читаемость текста на светлых изображениях.', 'sd-on-theme' ),
				'type'            => 'select',
				'choices'         => $overlay_choices,
				'sanitize'        => 'sdon_sanitize_number',
				'active_callback' => $visible,
			)
		);
	}
}

/**
 * Раздел «Фон».
 *
 * @param WP_Customize_Manager $wp_customize Менеджер настроек.
 * @return void
 */
function sdon_customize_background( $wp_customize ) {
	sdon_customize_add(
		$wp_customize,
		'bg_type',
		array(
			'section' => 'sdon_background',
			'label'   => __( 'Тип фона', 'sd-on-theme' ),
			'type'    => 'radio',
			'choices' => array(
				'color'    => __( 'Один цвет', 'sd-on-theme' ),
				'gradient' => __( 'Градиент из двух цветов', 'sd-on-theme' ),
				'image'    => __( 'Изображение', 'sd-on-theme' ),
			),
		)
	);

	sdon_customize_add(
		$wp_customize,
		'bg_color',
		array(
			'section' => 'sdon_background',
			'label'   => __( 'Цвет фона', 'sd-on-theme' ),
			'type'    => 'color',
		)
	);

	$is_gradient = static function () {
		return 'gradient' === sdon_opt( 'bg_type' );
	};

	sdon_customize_add(
		$wp_customize,
		'bg_gradient_from',
		array(
			'section'         => 'sdon_background',
			'label'           => __( 'Градиент — цвет №1', 'sd-on-theme' ),
			'type'            => 'color',
			'active_callback' => $is_gradient,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'bg_gradient_to',
		array(
			'section'         => 'sdon_background',
			'label'           => __( 'Градиент — цвет №2', 'sd-on-theme' ),
			'type'            => 'color',
			'active_callback' => $is_gradient,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'bg_gradient_direction',
		array(
			'section'         => 'sdon_background',
			'label'           => __( 'Направление градиента', 'sd-on-theme' ),
			'type'            => 'select',
			'sanitize'        => 'sdon_sanitize_choice',
			'choices'         => array(
				'180deg' => __( 'Сверху вниз', 'sd-on-theme' ),
				'0deg'   => __( 'Снизу вверх', 'sd-on-theme' ),
				'90deg'  => __( 'Слева направо', 'sd-on-theme' ),
				'270deg' => __( 'Справа налево', 'sd-on-theme' ),
				'135deg' => __( 'По диагонали', 'sd-on-theme' ),
			),
			'active_callback' => $is_gradient,
		)
	);

	$is_image = static function () {
		return 'image' === sdon_opt( 'bg_type' );
	};

	sdon_customize_add(
		$wp_customize,
		'bg_image',
		array(
			'section'         => 'sdon_background',
			'label'           => __( 'Изображение фона', 'sd-on-theme' ),
			'type'            => 'image',
			'active_callback' => $is_image,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'bg_image_size',
		array(
			'section'         => 'sdon_background',
			'label'           => __( 'Масштабирование изображения', 'sd-on-theme' ),
			'type'            => 'select',
			'choices'         => array(
				'cover'   => __( 'Заполнить экран', 'sd-on-theme' ),
				'contain' => __( 'Поместить целиком', 'sd-on-theme' ),
				'auto'    => __( 'Исходный размер', 'sd-on-theme' ),
			),
			'active_callback' => $is_image,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'bg_image_repeat',
		array(
			'section'         => 'sdon_background',
			'label'           => __( 'Повтор изображения', 'sd-on-theme' ),
			'type'            => 'select',
			'sanitize'        => 'sdon_sanitize_choice',
			'choices'         => array(
				'no-repeat' => __( 'Без повтора', 'sd-on-theme' ),
				'repeat'    => __( 'Повторять', 'sd-on-theme' ),
				'repeat-x'  => __( 'Повторять по горизонтали', 'sd-on-theme' ),
				'repeat-y'  => __( 'Повторять по вертикали', 'sd-on-theme' ),
			),
			'active_callback' => $is_image,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'bg_image_position',
		array(
			'section'         => 'sdon_background',
			'label'           => __( 'Положение изображения', 'sd-on-theme' ),
			'type'            => 'select',
			'sanitize'        => 'sdon_sanitize_choice',
			'choices'         => array(
				'center center' => __( 'По центру', 'sd-on-theme' ),
				'center top'    => __( 'Сверху', 'sd-on-theme' ),
				'center bottom' => __( 'Снизу', 'sd-on-theme' ),
				'left center'   => __( 'Слева', 'sd-on-theme' ),
				'right center'  => __( 'Справа', 'sd-on-theme' ),
			),
			'active_callback' => $is_image,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'bg_attachment',
		array(
			'section' => 'sdon_background',
			'label'   => __( 'Прокрутка фона', 'sd-on-theme' ),
			'type'    => 'radio',
			'choices' => array(
				'scroll' => __( 'Прокручивается вместе со страницей', 'sd-on-theme' ),
				'fixed'  => __( 'Фиксирован', 'sd-on-theme' ),
			),
		)
	);

	sdon_customize_add(
		$wp_customize,
		'bg_animation',
		array(
			'section'     => 'sdon_background',
			'label'       => __( 'Анимированный фон', 'sd-on-theme' ),
			'description' => __( 'Анимация рисуется на canvas и останавливается, когда вкладка неактивна.', 'sd-on-theme' ),
			'type'        => 'select',
			'choices'     => sdon_bg_animation_choices(),
		)
	);

	$has_animation = static function () {
		return 'none' !== sdon_opt( 'bg_animation' );
	};

	sdon_customize_add(
		$wp_customize,
		'bg_animation_opacity',
		array(
			'section'         => 'sdon_background',
			'label'           => __( 'Насыщенность анимации', 'sd-on-theme' ),
			'type'            => 'range',
			'unit'            => '%',
			'input_attrs'     => array(
				'min'  => 5,
				'max'  => 100,
				'step' => 5,
			),
			'active_callback' => $has_animation,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'bg_animation_speed',
		array(
			'section'         => 'sdon_background',
			'label'           => __( 'Скорость анимации', 'sd-on-theme' ),
			'description'     => __( '100% — обычная скорость.', 'sd-on-theme' ),
			'type'            => 'range',
			'unit'            => '%',
			'input_attrs'     => array(
				'min'  => 10,
				'max'  => 300,
				'step' => 10,
			),
			'active_callback' => $has_animation,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'bg_animation_shuffle',
		array(
			'section'         => 'sdon_background',
			'label'           => __( 'Автоматически менять анимации', 'sd-on-theme' ),
			'description'     => __( 'Анимации переключаются в случайном порядке.', 'sd-on-theme' ),
			'type'            => 'checkbox',
			'active_callback' => $has_animation,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'bg_animation_shuffle_interval',
		array(
			'section'         => 'sdon_background',
			'label'           => __( 'Интервал переключения', 'sd-on-theme' ),
			'type'            => 'range',
			'unit'            => __( 'сек', 'sd-on-theme' ),
			'input_attrs'     => array(
				'min'  => 10,
				'max'  => 600,
				'step' => 5,
			),
			'active_callback' => static function () {
				return 'none' !== sdon_opt( 'bg_animation' ) && (bool) sdon_opt( 'bg_animation_shuffle' );
			},
		)
	);

	sdon_customize_add(
		$wp_customize,
		'bg_animation_mobile',
		array(
			'section'         => 'sdon_background',
			'label'           => __( 'Анимация на мобильных устройствах', 'sd-on-theme' ),
			'description'     => __( 'По умолчанию отключена: экономит батарею и не влияет на скорость загрузки.', 'sd-on-theme' ),
			'type'            => 'checkbox',
			'active_callback' => $has_animation,
		)
	);
}

/**
 * Раздел «Шрифты».
 *
 * @param WP_Customize_Manager $wp_customize Менеджер настроек.
 * @return void
 */
function sdon_customize_fonts( $wp_customize ) {
	$fonts = array(
		'font_body'     => __( 'Шрифт основного текста', 'sd-on-theme' ),
		'font_headings' => __( 'Шрифт заголовков', 'sd-on-theme' ),
		'font_menu'     => __( 'Шрифт меню', 'sd-on-theme' ),
	);

	foreach ( $fonts as $id => $label ) {
		sdon_customize_add(
			$wp_customize,
			$id,
			array(
				'section' => 'sdon_fonts',
				'label'   => $label,
				'type'    => 'font',
			)
		);
	}

	sdon_customize_add(
		$wp_customize,
		'font_size_base',
		array(
			'section'     => 'sdon_fonts',
			'label'       => __( 'Базовый размер текста', 'sd-on-theme' ),
			'type'        => 'range',
			'unit'        => 'px',
			'input_attrs' => array(
				'min'  => 13,
				'max'  => 24,
				'step' => 1,
			),
		)
	);

	sdon_customize_add(
		$wp_customize,
		'font_weight_body',
		array(
			'section' => 'sdon_fonts',
			'label'   => __( 'Насыщенность основного текста', 'sd-on-theme' ),
			'type'    => 'select',
			'choices' => array(
				'300' => __( 'Светлый (300)', 'sd-on-theme' ),
				'400' => __( 'Обычный (400)', 'sd-on-theme' ),
				'500' => __( 'Средний (500)', 'sd-on-theme' ),
			),
		)
	);

	sdon_customize_add(
		$wp_customize,
		'font_weight_headings',
		array(
			'section' => 'sdon_fonts',
			'label'   => __( 'Насыщенность заголовков', 'sd-on-theme' ),
			'type'    => 'select',
			'choices' => array(
				'500' => __( 'Средний (500)', 'sd-on-theme' ),
				'600' => __( 'Полужирный (600)', 'sd-on-theme' ),
				'700' => __( 'Жирный (700)', 'sd-on-theme' ),
				'800' => __( 'Очень жирный (800)', 'sd-on-theme' ),
			),
		)
	);

	sdon_customize_add(
		$wp_customize,
		'line_height_body',
		array(
			'section'     => 'sdon_fonts',
			'label'       => __( 'Межстрочный интервал', 'sd-on-theme' ),
			'type'        => 'range',
			'sanitize'    => 'sdon_sanitize_float',
			'input_attrs' => array(
				'min'  => '1.2',
				'max'  => '2.2',
				'step' => '0.05',
			),
		)
	);
}

/**
 * Раздел «Цвета».
 *
 * @param WP_Customize_Manager $wp_customize Менеджер настроек.
 * @return void
 */
function sdon_customize_colors( $wp_customize ) {
	$light = array(
		'color_primary'      => __( 'Основной цвет', 'sd-on-theme' ),
		'color_secondary'    => __( 'Дополнительный цвет', 'sd-on-theme' ),
		'color_text'         => __( 'Цвет текста', 'sd-on-theme' ),
		'color_headings'     => __( 'Цвет заголовков', 'sd-on-theme' ),
		'color_link'         => __( 'Цвет ссылок', 'sd-on-theme' ),
		'color_link_hover'   => __( 'Цвет ссылок при наведении', 'sd-on-theme' ),
		'color_surface'      => __( 'Цвет блоков и карточек', 'sd-on-theme' ),
		'color_menu_bg'      => __( 'Цвет фона меню', 'sd-on-theme' ),
		'color_menu_text'    => __( 'Цвет текста меню', 'sd-on-theme' ),
		'color_button_bg'    => __( 'Цвет кнопок', 'sd-on-theme' ),
		'color_button_text'  => __( 'Цвет текста кнопок', 'sd-on-theme' ),
		'color_button_hover' => __( 'Цвет кнопок при наведении', 'sd-on-theme' ),
		'color_footer_bg'    => __( 'Цвет футера', 'sd-on-theme' ),
		'color_footer_text'  => __( 'Цвет текста футера', 'sd-on-theme' ),
	);

	foreach ( $light as $id => $label ) {
		sdon_customize_add(
			$wp_customize,
			$id,
			array(
				'section' => 'sdon_colors',
				'label'   => $label,
				'type'    => 'color',
			)
		);
	}

	$dark = array(
		'dark_color_primary'     => __( 'Тёмная схема — основной цвет', 'sd-on-theme' ),
		'dark_color_bg'          => __( 'Тёмная схема — фон', 'sd-on-theme' ),
		'dark_color_surface'     => __( 'Тёмная схема — блоки и карточки', 'sd-on-theme' ),
		'dark_color_text'        => __( 'Тёмная схема — текст', 'sd-on-theme' ),
		'dark_color_headings'    => __( 'Тёмная схема — заголовки', 'sd-on-theme' ),
		'dark_color_link'        => __( 'Тёмная схема — ссылки', 'sd-on-theme' ),
		'dark_color_link_hover'  => __( 'Тёмная схема — ссылки при наведении', 'sd-on-theme' ),
		'dark_color_menu_bg'     => __( 'Тёмная схема — фон меню', 'sd-on-theme' ),
		'dark_color_menu_text'   => __( 'Тёмная схема — текст меню', 'sd-on-theme' ),
		'dark_color_button_bg'   => __( 'Тёмная схема — кнопки', 'sd-on-theme' ),
		'dark_color_button_text' => __( 'Тёмная схема — текст кнопок', 'sd-on-theme' ),
		'dark_color_footer_bg'   => __( 'Тёмная схема — футер', 'sd-on-theme' ),
		'dark_color_footer_text' => __( 'Тёмная схема — текст футера', 'sd-on-theme' ),
	);

	$dark_available = static function () {
		return 'light' !== sdon_opt( 'color_scheme_mode' ) || (bool) sdon_opt( 'dark_mode_toggle' );
	};

	foreach ( $dark as $id => $label ) {
		sdon_customize_add(
			$wp_customize,
			$id,
			array(
				'section'         => 'sdon_colors',
				'label'           => $label,
				'type'            => 'color',
				'active_callback' => $dark_available,
			)
		);
	}

	sdon_customize_add(
		$wp_customize,
		'content_opacity',
		array(
			'section'     => 'sdon_colors',
			'label'       => __( 'Прозрачность фона блоков с контентом', 'sd-on-theme' ),
			'description' => __( '100% — полностью непрозрачный фон. Меньшие значения показывают фон сайта сквозь блоки.', 'sd-on-theme' ),
			'type'        => 'range',
			'unit'        => '%',
			'input_attrs' => array(
				'min'  => 5,
				'max'  => 100,
				'step' => 5,
			),
		)
	);
}

/**
 * Раздел «Новости».
 *
 * @param WP_Customize_Manager $wp_customize Менеджер настроек.
 * @return void
 */
function sdon_customize_news( $wp_customize ) {
	sdon_customize_add(
		$wp_customize,
		'news_columns',
		array(
			'section'     => 'sdon_news',
			'label'       => __( 'Блоков в одном ряду (ПК)', 'sd-on-theme' ),
			'description' => __( 'На планшете и смартфоне количество колонок уменьшается автоматически.', 'sd-on-theme' ),
			'type'        => 'range',
			'input_attrs' => array(
				'min'  => 1,
				'max'  => 5,
				'step' => 1,
			),
		)
	);

	sdon_customize_add(
		$wp_customize,
		'news_per_page',
		array(
			'section'     => 'sdon_news',
			'label'       => __( 'Материалов на странице', 'sd-on-theme' ),
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 1,
				'max'  => 50,
				'step' => 1,
			),
		)
	);

	sdon_customize_add(
		$wp_customize,
		'news_excerpt_length',
		array(
			'section'  => 'sdon_news',
			'label'    => __( 'Количество символов в превью', 'sd-on-theme' ),
			'type'     => 'select',
			'choices'  => array(
				'100' => __( '100 символов', 'sd-on-theme' ),
				'150' => __( '150 символов', 'sd-on-theme' ),
				'200' => __( '200 символов', 'sd-on-theme' ),
				'300' => __( '300 символов', 'sd-on-theme' ),
				'0'   => __( 'Без ограничения', 'sd-on-theme' ),
			),
			'sanitize' => 'sdon_sanitize_number',
		)
	);

	$toggles = array(
		'news_show_image'    => __( 'Показывать изображение', 'sd-on-theme' ),
		'news_show_category' => __( 'Показывать категорию', 'sd-on-theme' ),
		'news_show_date'     => __( 'Показывать дату', 'sd-on-theme' ),
		'news_show_author'   => __( 'Показывать автора', 'sd-on-theme' ),
		'news_show_comments' => __( 'Показывать количество комментариев', 'sd-on-theme' ),
		'news_show_button'   => __( 'Показывать кнопку перехода', 'sd-on-theme' ),
	);

	foreach ( $toggles as $id => $label ) {
		sdon_customize_add(
			$wp_customize,
			$id,
			array(
				'section' => 'sdon_news',
				'label'   => $label,
				'type'    => 'checkbox',
			)
		);
	}

	sdon_customize_add(
		$wp_customize,
		'news_image_position',
		array(
			'section'         => 'sdon_news',
			'label'           => __( 'Положение картинки в блоке', 'sd-on-theme' ),
			'type'            => 'select',
			'choices'         => array(
				'center'        => __( 'Посередине', 'sd-on-theme' ),
				'center-top'    => __( 'Посередине сверху', 'sd-on-theme' ),
				'center-bottom' => __( 'Посередине снизу', 'sd-on-theme' ),
			),
			'active_callback' => static function () {
				return (bool) sdon_opt( 'news_show_image' );
			},
		)
	);

	sdon_customize_add(
		$wp_customize,
		'news_card_border',
		array(
			'section' => 'sdon_news',
			'label'   => __( 'Обводка блоков новостей', 'sd-on-theme' ),
			'type'    => 'checkbox',
		)
	);

	$has_border = static function () {
		return (bool) sdon_opt( 'news_card_border' );
	};

	sdon_customize_add(
		$wp_customize,
		'news_card_border_width',
		array(
			'section'         => 'sdon_news',
			'label'           => __( 'Толщина обводки', 'sd-on-theme' ),
			'type'            => 'range',
			'unit'            => 'px',
			'input_attrs'     => array(
				'min'  => 1,
				'max'  => 8,
				'step' => 1,
			),
			'active_callback' => $has_border,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'news_card_border_color',
		array(
			'section'         => 'sdon_news',
			'label'           => __( 'Цвет обводки', 'sd-on-theme' ),
			'type'            => 'color',
			'active_callback' => $has_border,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'single_show_image',
		array(
			'section'     => 'sdon_news',
			'label'       => __( 'Картинка в начале открытой новости', 'sd-on-theme' ),
			'description' => __( 'Отключите, если изображение не нужно показывать на странице материала.', 'sd-on-theme' ),
			'type'        => 'checkbox',
		)
	);

	sdon_customize_add(
		$wp_customize,
		'news_button_text',
		array(
			'section'         => 'sdon_news',
			'label'           => __( 'Текст кнопки перехода', 'sd-on-theme' ),
			'description'     => __( 'По умолчанию: «Читать далее».', 'sd-on-theme' ),
			'type'            => 'text',
			'active_callback' => static function () {
				return (bool) sdon_opt( 'news_show_button' );
			},
		)
	);
}

/**
 * Раздел «Футер».
 *
 * @param WP_Customize_Manager $wp_customize Менеджер настроек.
 * @return void
 */
function sdon_customize_footer( $wp_customize ) {
	sdon_customize_add(
		$wp_customize,
		'footer_widget_columns',
		array(
			'section'     => 'sdon_footer',
			'label'       => __( 'Колонок виджетов в футере', 'sd-on-theme' ),
			'description' => __( 'Виджеты добавляются в разделе «Внешний вид → Виджеты».', 'sd-on-theme' ),
			'type'        => 'range',
			'input_attrs' => array(
				'min'  => 1,
				'max'  => 4,
				'step' => 1,
			),
		)
	);

	sdon_customize_add(
		$wp_customize,
		'footer_show_menu',
		array(
			'section' => 'sdon_footer',
			'label'   => __( 'Показывать меню в футере', 'sd-on-theme' ),
			'type'    => 'checkbox',
		)
	);

	sdon_customize_add(
		$wp_customize,
		'footer_text',
		array(
			'section'     => 'sdon_footer',
			'label'       => __( 'Текст в футере', 'sd-on-theme' ),
			'description' => __( 'Короткое описание сайта или дополнительная информация.', 'sd-on-theme' ),
			'type'        => 'html',
		)
	);

	sdon_customize_add(
		$wp_customize,
		'footer_copyright',
		array(
			'section'     => 'sdon_footer',
			'label'       => __( 'Копирайт', 'sd-on-theme' ),
			'description' => __( 'Допускается простое HTML-форматирование и ссылки. Если пусто — подставляется «© год, название сайта».', 'sd-on-theme' ),
			'type'        => 'html',
		)
	);

	sdon_customize_add(
		$wp_customize,
		'footer_show_socials',
		array(
			'section' => 'sdon_footer',
			'label'   => __( 'Показывать ссылки на социальные сети', 'sd-on-theme' ),
			'type'    => 'checkbox',
		)
	);

	$socials = sdon_social_networks();

	$socials_visible = static function () {
		return (bool) sdon_opt( 'footer_show_socials' );
	};

	foreach ( $socials as $id => $label ) {
		sdon_customize_add(
			$wp_customize,
			$id,
			array(
				'section'         => 'sdon_footer',
				'label'           => $label,
				'type'            => 'url',
				'active_callback' => $socials_visible,
			)
		);
	}
}

/**
 * Раздел «Монстр».
 *
 * @param WP_Customize_Manager $wp_customize Менеджер настроек.
 * @return void
 */
function sdon_customize_monster( $wp_customize ) {
	$monster_visible = static function () {
		return sdon_monster_is_enabled();
	};

	sdon_customize_add(
		$wp_customize,
		'monster_desktop',
		array(
			'section'     => 'sdon_monster',
			'label'       => __( 'Монстр на компьютерах', 'sd-on-theme' ),
			'description' => __( 'Монстр выезжает из-за края экрана через случайные интервалы времени.', 'sd-on-theme' ),
			'type'        => 'checkbox',
		)
	);

	sdon_customize_add(
		$wp_customize,
		'monster_mobile',
		array(
			'section'     => 'sdon_monster',
			'label'       => __( 'Монстр на мобильных устройствах', 'sd-on-theme' ),
			'description' => __( 'Настраивается независимо от компьютерной версии.', 'sd-on-theme' ),
			'type'        => 'checkbox',
		)
	);

	sdon_customize_add(
		$wp_customize,
		'monster_side',
		array(
			'section'         => 'sdon_monster',
			'label'           => __( 'Откуда выезжает', 'sd-on-theme' ),
			'type'            => 'radio',
			'choices'         => sdon_monster_side_choices(),
			'active_callback' => $monster_visible,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'monster_size',
		array(
			'section'         => 'sdon_monster',
			'label'           => __( 'Размер монстра', 'sd-on-theme' ),
			'type'            => 'range',
			'unit'            => 'px',
			'input_attrs'     => array(
				'min'  => 100,
				'max'  => 480,
				'step' => 10,
			),
			'active_callback' => $monster_visible,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'monster_color',
		array(
			'section'         => 'sdon_monster',
			'label'           => __( 'Основной цвет монстра', 'sd-on-theme' ),
			'type'            => 'color',
			'active_callback' => $monster_visible,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'monster_accent',
		array(
			'section'         => 'sdon_monster',
			'label'           => __( 'Цвет свечения', 'sd-on-theme' ),
			'type'            => 'color',
			'active_callback' => $monster_visible,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'monster_min_delay',
		array(
			'section'         => 'sdon_monster',
			'label'           => __( 'Минимальная пауза до появления', 'sd-on-theme' ),
			'description'     => __( 'Время появления выбирается случайно между минимальной и максимальной паузой.', 'sd-on-theme' ),
			'type'            => 'range',
			'unit'            => __( 'с', 'sd-on-theme' ),
			'input_attrs'     => array(
				'min'  => 3,
				'max'  => 600,
				'step' => 1,
			),
			'active_callback' => $monster_visible,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'monster_max_delay',
		array(
			'section'         => 'sdon_monster',
			'label'           => __( 'Максимальная пауза до появления', 'sd-on-theme' ),
			'type'            => 'range',
			'unit'            => __( 'с', 'sd-on-theme' ),
			'input_attrs'     => array(
				'min'  => 5,
				'max'  => 1800,
				'step' => 5,
			),
			'active_callback' => $monster_visible,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'monster_cooldown',
		array(
			'section'         => 'sdon_monster',
			'label'           => __( 'Пауза после клика по монстру', 'sd-on-theme' ),
			'description'     => __( 'После клика монстр не показывается заданное время.', 'sd-on-theme' ),
			'type'            => 'range',
			'unit'            => __( 'с', 'sd-on-theme' ),
			'input_attrs'     => array(
				'min'  => 0,
				'max'  => 3600,
				'step' => 10,
			),
			'active_callback' => $monster_visible,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'monster_shy_distance',
		array(
			'section'         => 'sdon_monster',
			'label'           => __( 'Расстояние, с которого монстр прячется', 'sd-on-theme' ),
			'description'     => __( 'Монстр начинает прятаться, когда курсор медленно подбирается к нему ближе этого расстояния.', 'sd-on-theme' ),
			'type'            => 'range',
			'unit'            => 'px',
			'input_attrs'     => array(
				'min'  => 40,
				'max'  => 600,
				'step' => 10,
			),
			'active_callback' => $monster_visible,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'monster_sound',
		array(
			'section'         => 'sdon_monster',
			'label'           => __( 'Звук при клике', 'sd-on-theme' ),
			'description'     => __( 'Встроенные звуки синтезируются браузером и не требуют загрузки файлов.', 'sd-on-theme' ),
			'type'            => 'select',
			'choices'         => sdon_monster_sound_choices(),
			'active_callback' => $monster_visible,
		)
	);

	sdon_customize_add(
		$wp_customize,
		'monster_sound_file',
		array(
			'section'         => 'sdon_monster',
			'label'           => __( 'Свой звук (MP3, OGG, WAV)', 'sd-on-theme' ),
			'description'     => __( 'Используется, если выбран вариант «Свой звук».', 'sd-on-theme' ),
			'type'            => 'audio',
			'active_callback' => static function () {
				return sdon_monster_is_enabled() && 'custom' === sdon_opt( 'monster_sound' );
			},
		)
	);

	sdon_customize_add(
		$wp_customize,
		'monster_volume',
		array(
			'section'         => 'sdon_monster',
			'label'           => __( 'Громкость звука', 'sd-on-theme' ),
			'type'            => 'range',
			'unit'            => '%',
			'input_attrs'     => array(
				'min'  => 0,
				'max'  => 100,
				'step' => 5,
			),
			'active_callback' => static function () {
				return sdon_monster_is_enabled() && 'none' !== sdon_opt( 'monster_sound' );
			},
		)
	);
}

/**
 * Раздел «Дополнительные настройки».
 *
 * @param WP_Customize_Manager $wp_customize Менеджер настроек.
 * @return void
 */
function sdon_customize_extra( $wp_customize ) {
	$toggles = array(
		'lazy_loading'           => array( __( 'Ленивая загрузка изображений', 'sd-on-theme' ), __( 'Изображения загружаются по мере прокрутки страницы.', 'sd-on-theme' ) ),
		'seo_open_graph'         => array( __( 'Разметка Open Graph', 'sd-on-theme' ), __( 'Отключается автоматически, если разметку добавляет SEO-плагин.', 'sd-on-theme' ) ),
		'seo_schema'             => array( __( 'Микроразметка Schema.org', 'sd-on-theme' ), '' ),
		'respect_reduced_motion' => array( __( 'Учитывать системную настройку prefers-reduced-motion', 'sd-on-theme' ), __( 'Анимации отключаются, если посетитель просил уменьшить движение.', 'sd-on-theme' ) ),
		'preloader'              => array( __( 'Показывать индикатор загрузки страницы', 'sd-on-theme' ), '' ),
	);

	foreach ( $toggles as $id => $data ) {
		sdon_customize_add(
			$wp_customize,
			$id,
			array(
				'section'     => 'sdon_extra',
				'label'       => $data[0],
				'description' => $data[1],
				'type'        => 'checkbox',
			)
		);
	}
}

/**
 * Скрипты живого предпросмотра и панели настроек.
 *
 * @return void
 */
function sdon_customize_preview_js() {
	wp_enqueue_script(
		'sdon-customizer-preview',
		SDON_URI . '/assets/js/customizer-preview.js',
		array( 'customize-preview' ),
		SDON_VERSION,
		true
	);

	wp_localize_script( 'sdon-customizer-preview', 'sdonPreview', sdon_postmessage_map() );
}
add_action( 'customize_preview_init', 'sdon_customize_preview_js' );

/**
 * Стили контролов темы в панели настроек.
 *
 * @return void
 */
function sdon_customize_controls_assets() {
	wp_enqueue_style( 'sdon-customizer', SDON_URI . '/assets/css/customizer.css', array(), SDON_VERSION );
}
add_action( 'customize_controls_enqueue_scripts', 'sdon_customize_controls_assets' );
