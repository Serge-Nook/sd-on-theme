<?php
/**
 * Главное меню: многоуровневое и раздвижное.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

require_once SDON_DIR . '/inc/menu/class-sdon-menu-walker.php';

/**
 * Вывод главного меню.
 *
 * @param string $location Расположение меню.
 * @param array  $args     Дополнительные аргументы wp_nav_menu().
 * @return void
 */
function sdon_nav_menu( $location = 'primary', $args = array() ) {
	if ( ! has_nav_menu( $location ) ) {
		if ( 'primary' === $location && current_user_can( 'edit_theme_options' ) ) {
			printf(
				'<p class="sdon-menu-empty"><a href="%1$s">%2$s</a></p>',
				esc_url( admin_url( 'nav-menus.php' ) ),
				esc_html__( 'Меню не создано — создать меню', 'sd-on-theme' )
			);
		}

		return;
	}

	wp_nav_menu(
		wp_parse_args(
			$args,
			array(
				'theme_location' => $location,
				'container'      => false,
				'menu_class'     => 'sdon-menu',
				'depth'          => 3,
				'walker'         => new SDON_Menu_Walker(),
			)
		)
	);
}

/**
 * Кнопка открытия мобильного (или раздвижного) меню.
 *
 * @return void
 */
function sdon_menu_toggle_button() {
	?>
	<button
		class="sdon-menu-toggle"
		type="button"
		aria-expanded="false"
		aria-controls="sdon-primary-navigation"
		data-sdon-menu-toggle
	>
		<span class="sdon-menu-toggle__bars" aria-hidden="true"></span>
		<span class="screen-reader-text"><?php esc_html_e( 'Открыть меню', 'sd-on-theme' ); ?></span>
	</button>
	<?php
}
