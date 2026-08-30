<?php
/**
 * Футер сайта.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

$sdon_footer_columns = max( 1, (int) sdon_opt( 'footer_widget_columns' ) );
$sdon_has_widgets    = false;

for ( $sdon_i = 1; $sdon_i <= $sdon_footer_columns; $sdon_i++ ) {
	if ( is_active_sidebar( 'footer-' . $sdon_i ) ) {
		$sdon_has_widgets = true;
		break;
	}
}
?>
	<footer class="sdon-footer">
		<?php if ( $sdon_has_widgets ) : ?>
			<div class="sdon-container sdon-footer__widgets">
				<?php for ( $sdon_i = 1; $sdon_i <= $sdon_footer_columns; $sdon_i++ ) : ?>
					<?php if ( is_active_sidebar( 'footer-' . $sdon_i ) ) : ?>
						<div class="sdon-footer__column">
							<?php dynamic_sidebar( 'footer-' . $sdon_i ); ?>
						</div>
					<?php endif; ?>
				<?php endfor; ?>
			</div>
		<?php endif; ?>

		<?php if ( trim( (string) sdon_opt( 'footer_text' ) ) !== '' ) : ?>
			<div class="sdon-container sdon-footer__text">
				<?php echo wp_kses( sdon_opt( 'footer_text' ), sdon_allowed_html() ); ?>
			</div>
		<?php endif; ?>

		<?php if ( sdon_is( 'footer_show_menu' ) && has_nav_menu( 'footer' ) ) : ?>
			<nav class="sdon-container sdon-footer__menu" aria-label="<?php esc_attr_e( 'Меню в футере', 'sd-on-theme' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'sdon-footer-menu',
						'depth'          => 1,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<div class="sdon-container sdon-footer__bottom">
			<div class="sdon-footer__copyright">
				<?php echo wp_kses( sdon_footer_copyright(), sdon_allowed_html() ); ?>
			</div>
			<?php sdon_social_links(); ?>
		</div>
	</footer>
</div><!-- .sdon-site -->

<?php sdon_back_to_top_button(); ?>
<?php wp_footer(); ?>
</body>
</html>
