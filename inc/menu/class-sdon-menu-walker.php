<?php
/**
 * Walker главного меню темы.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Walker главного меню: добавляет кнопки раскрытия подменю для клавиатуры
 * и корректные ARIA-атрибуты.
 */
class SDON_Menu_Walker extends Walker_Nav_Menu {

	/**
	 * Начало элемента меню.
	 *
	 * @param string   $output Разметка.
	 * @param WP_Post  $item   Элемент меню.
	 * @param int      $depth  Уровень вложенности.
	 * @param stdClass $args   Аргументы wp_nav_menu().
	 * @param int      $id     Идентификатор.
	 * @return void
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		parent::start_el( $output, $item, $depth, $args, $id );

		if ( in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
			$output .= sprintf(
				'<button class="sdon-submenu-toggle" type="button" aria-expanded="false"><span class="screen-reader-text">%1$s</span><span class="sdon-submenu-toggle__icon" aria-hidden="true"></span></button>',
				esc_html__( 'Раскрыть подменю', 'sd-on-theme' )
			);
		}
	}
}
