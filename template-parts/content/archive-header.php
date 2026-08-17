<?php
/**
 * Заголовок архива, поиска или страницы новостей.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="sdon-archive-header">
	<?php if ( is_search() ) : ?>
		<h1 class="sdon-archive-header__title">
			<?php
			printf(
				/* translators: %s: поисковый запрос. */
				esc_html__( 'Результаты поиска: %s', 'sd-on-theme' ),
				'<span>' . esc_html( get_search_query() ) . '</span>'
			);
			?>
		</h1>
	<?php elseif ( is_archive() ) : ?>
		<h1 class="sdon-archive-header__title"><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
		<?php the_archive_description( '<div class="sdon-archive-header__description">', '</div>' ); ?>
	<?php elseif ( is_home() && ! is_front_page() ) : ?>
		<h1 class="sdon-archive-header__title"><?php echo esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) ); ?></h1>
	<?php endif; ?>
</header>
