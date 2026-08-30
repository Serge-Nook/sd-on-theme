<?php
/**
 * Страница «Внешний вид → Настройки темы».
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

/** Идентификатор страницы настроек темы. */
define( 'SDON_ADMIN_PAGE', 'sdon-theme-settings' );

/**
 * Регистрация страницы настроек в меню «Внешний вид».
 *
 * @return void
 */
function sdon_register_admin_page() {
	add_theme_page(
		__( 'Настройки темы', 'sd-on-theme' ),
		__( 'Настройки темы', 'sd-on-theme' ),
		'edit_theme_options',
		SDON_ADMIN_PAGE,
		'sdon_render_admin_page'
	);
}
add_action( 'admin_menu', 'sdon_register_admin_page' );

/**
 * Стили страницы настроек.
 *
 * @param string $hook Текущий экран.
 * @return void
 */
function sdon_admin_assets( $hook ) {
	if ( 'appearance_page_' . SDON_ADMIN_PAGE !== $hook ) {
		return;
	}

	wp_enqueue_style( 'sdon-admin', SDON_URI . '/assets/css/admin.css', array(), SDON_VERSION );
}
add_action( 'admin_enqueue_scripts', 'sdon_admin_assets' );

/**
 * Ссылка на раздел настроек темы в Customizer.
 *
 * @param string $section Идентификатор раздела.
 * @return string
 */
function sdon_customizer_link( $section ) {
	return add_query_arg(
		array(
			'return'             => rawurlencode( admin_url( 'themes.php?page=' . SDON_ADMIN_PAGE ) ),
			'autofocus[section]' => $section,
		),
		admin_url( 'customize.php' )
	);
}

/**
 * Текущая вкладка страницы настроек.
 *
 * @return string
 */
function sdon_admin_current_tab() {
	$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'overview'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- переключение вкладок не изменяет данные.

	return in_array( $tab, array( 'overview', 'fonts', 'tools' ), true ) ? $tab : 'overview';
}

/**
 * Разметка страницы настроек темы.
 *
 * @return void
 */
function sdon_render_admin_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав для изменения настроек темы.', 'sd-on-theme' ) );
	}

	$tab  = sdon_admin_current_tab();
	$tabs = array(
		'overview' => __( 'Разделы настроек', 'sd-on-theme' ),
		'fonts'    => __( 'Свои шрифты', 'sd-on-theme' ),
		'tools'    => __( 'Сброс, экспорт и импорт', 'sd-on-theme' ),
	);
	?>
	<div class="wrap sdon-admin">
		<h1><?php esc_html_e( 'Настройки темы SD-ON', 'sd-on-theme' ); ?></h1>

		<?php sdon_admin_notices(); ?>

		<nav class="nav-tab-wrapper">
			<?php foreach ( $tabs as $slug => $label ) : ?>
				<a
					class="nav-tab<?php echo $slug === $tab ? ' nav-tab-active' : ''; ?>"
					href="<?php echo esc_url( admin_url( 'themes.php?page=' . SDON_ADMIN_PAGE . '&tab=' . $slug ) ); ?>"
				>
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<?php
		if ( 'fonts' === $tab ) {
			sdon_render_fonts_tab();
		} elseif ( 'tools' === $tab ) {
			sdon_render_tools_tab();
		} else {
			sdon_render_overview_tab();
		}
		?>
	</div>
	<?php
}

/**
 * Вкладка со списком разделов настроек.
 *
 * @return void
 */
function sdon_render_overview_tab() {
	$sections = array(
		'sdon_general'    => array( __( 'Основные настройки', 'sd-on-theme' ), __( 'Цветовая схема, кнопка «Наверх», хлебные крошки.', 'sd-on-theme' ) ),
		'sdon_layout'     => array( __( 'Макет сайта', 'sd-on-theme' ), __( 'Одна, две или три колонки, максимальная ширина сайта.', 'sd-on-theme' ) ),
		'sdon_header'     => array( __( 'Шапка сайта', 'sd-on-theme' ), __( 'Логотип, название, подпись, поиск.', 'sd-on-theme' ) ),
		'sdon_menu'       => array( __( 'Меню', 'sd-on-theme' ), __( 'Закреплённое или обычное, многоуровневое или раздвижное.', 'sd-on-theme' ) ),
		'sdon_slider'     => array( __( 'Слайдер', 'sd-on-theme' ), __( 'До 10 слайдов, затемнение, кнопки и автопрокрутка.', 'sd-on-theme' ) ),
		'sdon_background' => array( __( 'Фон', 'sd-on-theme' ), __( 'Цвет, градиент, изображение, анимации.', 'sd-on-theme' ) ),
		'sdon_fonts'      => array( __( 'Шрифты', 'sd-on-theme' ), __( 'Шрифты текста, заголовков и меню, размеры и насыщенность.', 'sd-on-theme' ) ),
		'sdon_colors'     => array( __( 'Цвета', 'sd-on-theme' ), __( 'Светлая и тёмная цветовые схемы.', 'sd-on-theme' ) ),
		'sdon_news'       => array( __( 'Новости', 'sd-on-theme' ), __( 'Сетка материалов и содержимое карточки.', 'sd-on-theme' ) ),
		'sdon_footer'     => array( __( 'Футер', 'sd-on-theme' ), __( 'Виджеты, меню, соцсети, копирайт.', 'sd-on-theme' ) ),
		'sdon_seo'        => array( __( 'SEO', 'sd-on-theme' ), __( 'Заголовки, описания, индексация, соцкарточки, микроразметка, robots.txt.', 'sd-on-theme' ) ),
		'sdon_extra'      => array( __( 'Дополнительные настройки', 'sd-on-theme' ), __( 'Ленивая загрузка, доступность, индикатор загрузки.', 'sd-on-theme' ) ),
	);
	?>
	<p class="sdon-admin__intro">
		<?php esc_html_e( 'Все настройки открываются в предпросмотре: изменения видны сразу, без сохранения и перезагрузки страницы.', 'sd-on-theme' ); ?>
	</p>

	<div class="sdon-admin__grid">
		<?php foreach ( $sections as $section => $data ) : ?>
			<div class="sdon-admin__card">
				<h2><?php echo esc_html( $data[0] ); ?></h2>
				<p><?php echo esc_html( $data[1] ); ?></p>
				<a class="button button-primary" href="<?php echo esc_url( sdon_customizer_link( $section ) ); ?>">
					<?php esc_html_e( 'Настроить', 'sd-on-theme' ); ?>
				</a>
			</div>
		<?php endforeach; ?>
	</div>

	<h2><?php esc_html_e( 'Что ещё пригодится', 'sd-on-theme' ); ?></h2>
	<ul class="sdon-admin__links">
		<li><a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php esc_html_e( 'Меню сайта', 'sd-on-theme' ); ?></a></li>
		<li><a href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>"><?php esc_html_e( 'Виджеты боковых колонок и футера', 'sd-on-theme' ); ?></a></li>
		<li><a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>"><?php esc_html_e( 'Добавить новость', 'sd-on-theme' ); ?></a></li>
	</ul>
	<?php
}

/**
 * Уведомления об итогах операций на странице настроек.
 *
 * @return void
 */
function sdon_admin_notices() {
	$notice = isset( $_GET['sdon_notice'] ) ? sanitize_key( wp_unslash( $_GET['sdon_notice'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- только вывод сообщения.

	if ( '' === $notice ) {
		return;
	}

	$messages = array(
		'font-added'   => array( 'success', __( 'Шрифт загружен и доступен в настройках шрифтов.', 'sd-on-theme' ) ),
		'font-deleted' => array( 'success', __( 'Шрифт удалён.', 'sd-on-theme' ) ),
		'font-error'   => array( 'error', __( 'Не удалось загрузить шрифт. Проверьте формат файла: WOFF2, WOFF, TTF или OTF.', 'sd-on-theme' ) ),
		'reset-done'   => array( 'success', __( 'Настройки темы возвращены к значениям по умолчанию.', 'sd-on-theme' ) ),
		'import-done'  => array( 'success', __( 'Настройки импортированы.', 'sd-on-theme' ) ),
		'import-error' => array( 'error', __( 'Не удалось импортировать настройки: файл повреждён или это не файл настроек темы.', 'sd-on-theme' ) ),
	);

	if ( ! isset( $messages[ $notice ] ) ) {
		return;
	}

	printf(
		'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
		esc_attr( $messages[ $notice ][0] ),
		esc_html( $messages[ $notice ][1] )
	);
}

/**
 * URL страницы настроек с уведомлением.
 *
 * @param string $notice Код уведомления.
 * @param string $tab    Вкладка.
 * @return string
 */
function sdon_admin_redirect_url( $notice, $tab = 'overview' ) {
	return add_query_arg(
		array(
			'page'        => SDON_ADMIN_PAGE,
			'tab'         => $tab,
			'sdon_notice' => $notice,
		),
		admin_url( 'themes.php' )
	);
}
