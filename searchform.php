<?php
/**
 * Форма поиска.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

$sdon_search_id = wp_unique_id( 'sdon-search-' );
?>
<form role="search" method="get" class="sdon-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $sdon_search_id ); ?>" class="screen-reader-text">
		<?php esc_html_e( 'Поиск по сайту', 'sd-on-theme' ); ?>
	</label>
	<input
		type="search"
		id="<?php echo esc_attr( $sdon_search_id ); ?>"
		class="sdon-search__field"
		name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php esc_attr_e( 'Поиск…', 'sd-on-theme' ); ?>"
	/>
	<button class="sdon-search__submit" type="submit">
		<span class="screen-reader-text"><?php esc_html_e( 'Найти', 'sd-on-theme' ); ?></span>
		<span aria-hidden="true">&#9906;</span>
	</button>
</form>
