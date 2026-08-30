<?php
/**
 * Страница 404.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/layout/open' );
?>
<section class="sdon-error-404">
	<h1><?php esc_html_e( 'Страница не найдена', 'sd-on-theme' ); ?></h1>
	<p><?php esc_html_e( 'Возможно, страница была удалена или адрес указан неверно.', 'sd-on-theme' ); ?></p>
	<?php get_search_form(); ?>
	<a class="sdon-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'На главную', 'sd-on-theme' ); ?></a>
</section>
<?php
get_template_part( 'template-parts/layout/close' );
get_footer();
