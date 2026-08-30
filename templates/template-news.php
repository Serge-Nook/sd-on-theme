<?php
/**
 * Template Name: Страница новостей
 * Template Post Type: page
 *
 * Выводит сетку публикаций по настройкам раздела «Новости».
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/layout/open' );

$sdon_paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

$sdon_news = new WP_Query(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => (int) sdon_opt( 'news_per_page' ),
		'paged'          => $sdon_paged,
	)
);
?>
<h1 class="sdon-page-title"><?php the_title(); ?></h1>
<?php
while ( have_posts() ) {
	the_post();

	if ( '' !== trim( (string) get_the_content() ) ) {
		echo '<div class="sdon-page-description">';
		the_content();
		echo '</div>';
	}
}
?>

<?php if ( $sdon_news->have_posts() ) : ?>
	<div class="sdon-grid">
		<?php
		while ( $sdon_news->have_posts() ) {
			$sdon_news->the_post();
			get_template_part( 'template-parts/content/card' );
		}
		?>
	</div>

	<?php
	echo wp_kses_post(
		paginate_links(
			array(
				'total'              => (int) $sdon_news->max_num_pages,
				'current'            => $sdon_paged,
				'prev_text'          => esc_html__( 'Назад', 'sd-on-theme' ),
				'next_text'          => esc_html__( 'Вперёд', 'sd-on-theme' ),
				'before_page_number' => '<span class="screen-reader-text">' . esc_html__( 'Страница', 'sd-on-theme' ) . ' </span>',
			)
		)
	);
	?>
<?php else : ?>
	<?php get_template_part( 'template-parts/content/none' ); ?>
<?php endif; ?>

<?php
wp_reset_postdata();

get_template_part( 'template-parts/layout/close' );
get_footer();
