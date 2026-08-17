<?php
/**
 * Отдельная страница.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/layout/open' );

while ( have_posts() ) {
	the_post();

	get_template_part( 'template-parts/content/single' );

	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}
}

get_template_part( 'template-parts/layout/close' );
get_footer();
