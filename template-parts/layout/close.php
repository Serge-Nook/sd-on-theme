<?php
/**
 * Закрывающая разметка основной области и боковые колонки.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

$sdon_columns = sdon_layout_columns();
?>
	</main>

	<?php if ( $sdon_columns >= 2 ) : ?>
		<?php get_sidebar(); ?>
	<?php endif; ?>

	<?php if ( $sdon_columns >= 3 ) : ?>
		<?php get_sidebar( 'secondary' ); ?>
	<?php endif; ?>
</div><!-- .sdon-layout -->
