<?php
/**
 * Содержимое одиночной записи.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'sdon-entry' ); ?>>
	<header class="sdon-entry__header">
		<?php sdon_post_categories(); ?>
		<h1 class="sdon-entry__title"><?php the_title(); ?></h1>
		<?php if ( 'post' === get_post_type() ) : ?>
			<?php sdon_post_meta(); ?>
		<?php endif; ?>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="sdon-entry__thumbnail">
			<?php the_post_thumbnail( 'large', array( 'sizes' => '(max-width: 899px) 100vw, 800px' ) ); ?>
			<?php if ( get_the_post_thumbnail_caption() ) : ?>
				<figcaption><?php echo esc_html( get_the_post_thumbnail_caption() ); ?></figcaption>
			<?php endif; ?>
		</figure>
	<?php endif; ?>

	<div class="sdon-entry__content">
		<?php the_content(); ?>
		<?php
		wp_link_pages(
			array(
				'before' => '<nav class="sdon-page-links">' . esc_html__( 'Страницы:', 'sd-on-theme' ),
				'after'  => '</nav>',
			)
		);
		?>
	</div>

	<?php if ( has_tag() ) : ?>
		<footer class="sdon-entry__footer">
			<?php the_tags( '<div class="sdon-tags">', '', '</div>' ); ?>
		</footer>
	<?php endif; ?>
</article>
