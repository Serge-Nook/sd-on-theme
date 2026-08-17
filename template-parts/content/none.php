<?php
/**
 * Сообщение, когда материалы не найдены.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="sdon-no-results">
	<h2><?php esc_html_e( 'Ничего не найдено', 'sd-on-theme' ); ?></h2>

	<?php if ( is_search() ) : ?>
		<p><?php esc_html_e( 'По вашему запросу ничего не найдено. Попробуйте изменить формулировку.', 'sd-on-theme' ); ?></p>
	<?php else : ?>
		<p><?php esc_html_e( 'Здесь пока нет материалов.', 'sd-on-theme' ); ?></p>
	<?php endif; ?>

	<?php get_search_form(); ?>
</section>
