<?php
/**
 * Карточка новости в сетке материалов.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

$sdon_excerpt = sdon_trimmed_excerpt();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( sdon_card_classes() ); ?>>
	<?php sdon_card_thumbnail(); ?>

	<div class="sdon-card__body">
		<?php if ( sdon_is( 'news_show_category' ) ) : ?>
			<?php sdon_post_categories(); ?>
		<?php endif; ?>

		<h2 class="sdon-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<?php sdon_post_meta( true ); ?>

		<?php if ( '' !== $sdon_excerpt ) : ?>
			<p class="sdon-card__excerpt"><?php echo esc_html( $sdon_excerpt ); ?></p>
		<?php endif; ?>

		<?php if ( sdon_is( 'news_show_button' ) ) : ?>
			<a class="sdon-button sdon-button--ghost sdon-card__button" href="<?php the_permalink(); ?>">
				<?php echo esc_html( sdon_read_more_text() ); ?>
				<span class="screen-reader-text"><?php the_title(); ?></span>
			</a>
		<?php endif; ?>
	</div>
</article>
