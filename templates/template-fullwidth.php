<?php
/**
 * Template Name: Во всю ширину (без боковых колонок)
 * Template Post Type: page
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="sdon-container sdon-layout sdon-layout--full">
	<main id="sdon-content" class="sdon-main">
		<?php sdon_breadcrumbs(); ?>
		<?php
		while ( have_posts() ) {
			the_post();
			get_template_part( 'template-parts/content/single' );

			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		}
		?>
	</main>
</div>
<?php
get_footer();
