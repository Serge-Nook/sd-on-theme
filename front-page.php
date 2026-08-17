<?php
/**
 * Главная страница: статическая страница или сетка последних новостей.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( 'posts' === get_option( 'show_on_front' ) ) {
	get_template_part( 'index' );
	return;
}

get_header();
get_template_part( 'template-parts/layout/open' );

while ( have_posts() ) {
	the_post();
	get_template_part( 'template-parts/content/single' );
}

get_template_part( 'template-parts/layout/close' );
get_footer();
