<?php
/**
 * Регистрация виджетов темы.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

require_once SDON_DIR . '/inc/widgets/class-sdon-recent-posts-widget.php';

/**
 * Регистрация виджетов темы.
 *
 * @return void
 */
function sdon_register_widgets() {
	register_widget( 'SDON_Recent_Posts_Widget' );
}
add_action( 'widgets_init', 'sdon_register_widgets' );
