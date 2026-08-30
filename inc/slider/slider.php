<?php
/**
 * Слайдер главной страницы.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Слайды, заполненные пользователем.
 *
 * @return array<int, array<string, mixed>>
 */
function sdon_slides() {
	$count  = max( 1, min( SDON_MAX_SLIDES, (int) sdon_opt( 'slider_count' ) ) );
	$slides = array();

	for ( $i = 1; $i <= $count; $i++ ) {
		$image = (string) sdon_opt( "slide_{$i}_image" );
		$title = (string) sdon_opt( "slide_{$i}_title" );
		$text  = (string) sdon_opt( "slide_{$i}_text" );

		if ( '' === $image && '' === trim( $title ) && '' === trim( $text ) ) {
			continue;
		}

		$button_text = trim( (string) sdon_opt( "slide_{$i}_button_text" ) );

		$slides[] = array(
			'index'       => $i,
			'image'       => $image,
			'title'       => $title,
			'text'        => $text,
			'button'      => (bool) sdon_opt( "slide_{$i}_button" ),
			'button_text' => '' !== $button_text ? $button_text : __( 'Читать далее', 'sd-on-theme' ),
			'url'         => (string) sdon_opt( "slide_{$i}_url" ),
			'overlay'     => (int) sdon_opt( "slide_{$i}_overlay" ),
		);
	}

	return $slides;
}

/**
 * Нужно ли выводить слайдер на текущей странице.
 *
 * @return bool
 */
function sdon_slider_is_visible() {
	if ( ! sdon_is( 'slider_enable' ) || ! is_front_page() || is_paged() ) {
		return false;
	}

	return ! empty( sdon_slides() );
}

/**
 * Разметка слайдера.
 *
 * @return void
 */
function sdon_render_slider() {
	if ( ! sdon_slider_is_visible() ) {
		return;
	}

	$slides = sdon_slides();
	$total  = count( $slides );
	?>
	<section
		class="sdon-slider sdon-slider--<?php echo esc_attr( sdon_opt( 'slider_height' ) ); ?>"
		aria-roledescription="carousel"
		aria-label="<?php esc_attr_e( 'Главные материалы', 'sd-on-theme' ); ?>"
		data-sdon-slider
	>
		<div class="sdon-slider__track" data-sdon-slider-track>
			<?php foreach ( $slides as $position => $slide ) : ?>
				<article
					class="sdon-slide sdon-slide--<?php echo esc_attr( $slide['index'] ); ?><?php echo 0 === $position ? ' is-active' : ''; ?>"
					role="group"
					aria-roledescription="<?php esc_attr_e( 'слайд', 'sd-on-theme' ); ?>"
					aria-label="<?php echo esc_attr( sprintf( /* translators: 1: номер слайда, 2: всего слайдов. */ __( '%1$d из %2$d', 'sd-on-theme' ), $position + 1, $total ) ); ?>"
					<?php echo 0 === $position ? '' : 'aria-hidden="true"'; ?>
				>
					<?php if ( $slide['image'] ) : ?>
						<img
							class="sdon-slide__image"
							src="<?php echo esc_url( $slide['image'] ); ?>"
							alt="<?php echo esc_attr( $slide['title'] ); ?>"
							loading="<?php echo 0 === $position ? 'eager' : 'lazy'; ?>"
							decoding="async"
							<?php echo 0 === $position ? 'fetchpriority="high"' : ''; ?>
						/>
					<?php endif; ?>

					<div class="sdon-slide__overlay" aria-hidden="true"></div>

					<div class="sdon-slide__content sdon-container">
						<?php if ( $slide['title'] ) : ?>
							<h2 class="sdon-slide__title"><?php echo esc_html( $slide['title'] ); ?></h2>
						<?php endif; ?>

						<?php if ( $slide['text'] ) : ?>
							<p class="sdon-slide__text"><?php echo esc_html( $slide['text'] ); ?></p>
						<?php endif; ?>

						<?php if ( $slide['button'] && $slide['url'] ) : ?>
							<a class="sdon-button sdon-slide__button" href="<?php echo esc_url( $slide['url'] ); ?>">
								<?php echo esc_html( $slide['button_text'] ); ?>
							</a>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<?php if ( $total > 1 && sdon_is( 'slider_arrows' ) ) : ?>
			<button class="sdon-slider__arrow sdon-slider__arrow--prev" type="button" data-sdon-slider-prev>
				<span aria-hidden="true">&#8249;</span>
				<span class="screen-reader-text"><?php esc_html_e( 'Предыдущий слайд', 'sd-on-theme' ); ?></span>
			</button>
			<button class="sdon-slider__arrow sdon-slider__arrow--next" type="button" data-sdon-slider-next>
				<span aria-hidden="true">&#8250;</span>
				<span class="screen-reader-text"><?php esc_html_e( 'Следующий слайд', 'sd-on-theme' ); ?></span>
			</button>
		<?php endif; ?>

		<?php if ( $total > 1 && sdon_is( 'slider_dots' ) ) : ?>
			<div class="sdon-slider__dots" role="tablist" aria-label="<?php esc_attr_e( 'Выбор слайда', 'sd-on-theme' ); ?>">
				<?php foreach ( $slides as $position => $slide ) : ?>
					<button
						class="sdon-slider__dot<?php echo 0 === $position ? ' is-active' : ''; ?>"
						type="button"
						role="tab"
						aria-selected="<?php echo 0 === $position ? 'true' : 'false'; ?>"
						data-sdon-slider-dot="<?php echo esc_attr( $position ); ?>"
					>
						<span class="screen-reader-text">
							<?php
							printf(
								/* translators: %d: номер слайда. */
								esc_html__( 'Слайд %d', 'sd-on-theme' ),
								(int) $position + 1
							);
							?>
						</span>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>
	<?php
}
