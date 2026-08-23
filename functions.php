<?php
/**
 * Точка входа темы SD-ON Theme.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

define( 'SDON_VERSION', '1.3.0' );
define( 'SDON_DIR', get_template_directory() );
define( 'SDON_URI', get_template_directory_uri() );

/** Максимальное количество слайдов главного слайдера. */
define( 'SDON_MAX_SLIDES', 10 );

require_once SDON_DIR . '/inc/defaults.php';
require_once SDON_DIR . '/inc/helpers/helpers.php';
require_once SDON_DIR . '/inc/helpers/fonts.php';
require_once SDON_DIR . '/inc/setup.php';
require_once SDON_DIR . '/inc/enqueue.php';
require_once SDON_DIR . '/inc/dynamic-css.php';
require_once SDON_DIR . '/inc/template-tags.php';
require_once SDON_DIR . '/inc/menu/menu.php';
require_once SDON_DIR . '/inc/slider/slider.php';
require_once SDON_DIR . '/inc/widgets/widgets.php';
require_once SDON_DIR . '/inc/monster-creatures.php';
require_once SDON_DIR . '/inc/monster.php';
require_once SDON_DIR . '/inc/cookie-notice.php';
require_once SDON_DIR . '/inc/seo.php';
require_once SDON_DIR . '/inc/customizer/sanitize.php';
require_once SDON_DIR . '/inc/customizer/customizer.php';

if ( is_admin() ) {
	require_once SDON_DIR . '/inc/admin/admin-page.php';
	require_once SDON_DIR . '/inc/admin/fonts-manager.php';
	require_once SDON_DIR . '/inc/admin/tools.php';
}
