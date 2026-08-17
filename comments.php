<?php
/**
 * Список комментариев и форма ответа.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="sdon-comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="sdon-comments__title">
			<?php
			printf(
				/* translators: %s: количество комментариев. */
				esc_html( _n( '%s комментарий', '%s комментариев', (int) get_comments_number(), 'sd-on-theme' ) ),
				esc_html( number_format_i18n( get_comments_number() ) )
			);
			?>
		</h2>

		<ol class="sdon-comments__list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 56,
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => esc_html__( 'Назад', 'sd-on-theme' ),
				'next_text' => esc_html__( 'Вперёд', 'sd-on-theme' ),
			)
		);
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="sdon-comments__closed"><?php esc_html_e( 'Комментарии закрыты.', 'sd-on-theme' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply'  => esc_html__( 'Оставить комментарий', 'sd-on-theme' ),
			'class_submit' => 'sdon-button',
		)
	);
	?>
</section>
