<?php
/**
 * Одиночная запись.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/layout/open' );

while ( have_posts() ) {
	the_post();

	get_template_part( 'template-parts/content/single' );

	the_post_navigation(
		array(
			'prev_text' => '<span class="sdon-post-nav__label">' . esc_html__( 'Предыдущий материал', 'sd-on-theme' ) . '</span> %title',
			'next_text' => '<span class="sdon-post-nav__label">' . esc_html__( 'Следующий материал', 'sd-on-theme' ) . '</span> %title',
		)
	);

	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}
}

get_template_part( 'template-parts/layout/close' );
get_footer();
