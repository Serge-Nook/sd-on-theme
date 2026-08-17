<?php
/**
 * Вторая боковая колонка (макет из трёх колонок).
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_active_sidebar( 'sidebar-2' ) ) {
	return;
}
?>
<aside class="sdon-sidebar sdon-sidebar--secondary" aria-label="<?php esc_attr_e( 'Вторая боковая колонка', 'sd-on-theme' ); ?>">
	<?php dynamic_sidebar( 'sidebar-2' ); ?>
</aside>
