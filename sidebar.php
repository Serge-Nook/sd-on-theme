<?php
/**
 * Основная боковая колонка.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>
<aside class="sdon-sidebar sdon-sidebar--primary" aria-label="<?php esc_attr_e( 'Боковая колонка', 'sd-on-theme' ); ?>">
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
