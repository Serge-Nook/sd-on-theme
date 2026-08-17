<?php
/**
 * Шаблон результатов поиска.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/layout/open' );

get_template_part( 'template-parts/content/archive-header' );

if ( have_posts() ) {
	echo '<div class="sdon-grid">';

	while ( have_posts() ) {
		the_post();
		get_template_part( 'template-parts/content/card' );
	}

	echo '</div>';

	sdon_pagination();
} else {
	get_template_part( 'template-parts/content/none' );
}

get_template_part( 'template-parts/layout/close' );
get_footer();
