<?php
/**
 * Выплывающее снизу окно с соглашением об использовании временных файлов.
 *
 * Текст, подписи кнопок и ссылка на страницу соглашения задаются в настройках
 * темы. Согласие запоминается в localStorage браузера, поэтому окно
 * показывается один раз и затем не чаще, чем раз в указанное число дней.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Включено ли окно соглашения.
 *
 * @return bool
 */
function sdon_cookie_is_enabled() {
	return sdon_is( 'cookie_notice' );
}

/**
 * Текст настройки или значение по умолчанию.
 *
 * @param string $key      Ключ настройки.
 * @param string $fallback Значение по умолчанию.
 *
 * @return string
 */
function sdon_cookie_text( $key, $fallback ) {
	$value = trim( (string) sdon_opt( $key ) );

	return '' !== $value ? $value : $fallback;
}

/**
 * Ссылка на страницу соглашения и политики.
 *
 * @return string
 */
function sdon_cookie_link() {
	$url = trim( (string) sdon_opt( 'cookie_link' ) );

	if ( '' === $url ) {
		$policy = (int) get_option( 'wp_page_for_privacy_policy' );

		if ( $policy > 0 && 'publish' === get_post_status( $policy ) ) {
			$url = (string) get_permalink( $policy );
		}
	}

	return esc_url_raw( $url );
}

/**
 * Срок повторного показа окна в днях.
 *
 * @return int
 */
function sdon_cookie_days() {
	return min( 365, max( 1, (int) sdon_opt( 'cookie_days' ) ) );
}

/**
 * Настройки окна соглашения для JavaScript.
 *
 * @return array<string, mixed>
 */
function sdon_cookie_settings() {
	return array(
		'days'    => sdon_cookie_days(),
		'storage' => 'sdonCookieConsent',
	);
}

/**
 * Анимированный кот, лежащий на окне соглашения.
 *
 * @return void
 */
function sdon_cookie_cat_svg() {
	?>
	<svg class="sdon-cookie__cat" viewBox="0 0 200 96" role="presentation" focusable="false" aria-hidden="true">
		<g class="sdon-cookie__cat-body">
			<path class="sdon-cookie__cat-tail" d="M34 78c-16 2-26-8-24-22 1-9 9-14 15-11 5 3 4 10-1 11-4 1-6-2-5-5" />
			<ellipse class="sdon-cookie__cat-fur" cx="104" cy="76" rx="66" ry="20" />
			<ellipse class="sdon-cookie__cat-fur" cx="150" cy="62" rx="26" ry="24" />
			<path class="sdon-cookie__cat-ear" d="M132 44 128 20l22 14z" />
			<path class="sdon-cookie__cat-ear" d="M170 44 176 20l-20 14z" />
			<path class="sdon-cookie__cat-inner" d="M134 42l-2-14 13 9z" />
			<path class="sdon-cookie__cat-inner" d="M168 42l4-14-12 9z" />
			<g class="sdon-cookie__cat-eyes">
				<ellipse cx="141" cy="58" rx="4" ry="5" />
				<ellipse cx="159" cy="58" rx="4" ry="5" />
			</g>
			<path class="sdon-cookie__cat-nose" d="M150 66l-5-4h10z" />
			<g class="sdon-cookie__cat-whiskers">
				<path d="M138 68 118 64" />
				<path d="M138 71 119 73" />
				<path d="M162 68 182 64" />
				<path d="M162 71 181 73" />
			</g>
			<g class="sdon-cookie__cat-paws">
				<ellipse cx="88" cy="90" rx="14" ry="6" />
				<ellipse cx="118" cy="90" rx="14" ry="6" />
			</g>
		</g>
	</svg>
	<?php
}

/**
 * Разметка окна соглашения в подвале страницы.
 *
 * @return void
 */
function sdon_cookie_markup() {
	if ( ! sdon_cookie_is_enabled() ) {
		return;
	}

	$title  = sdon_cookie_text( 'cookie_title', __( 'Временные файлы', 'sd-on-theme' ) );
	$text   = sdon_cookie_text(
		'cookie_text',
		__( 'Сайт использует временные файлы (cookie), чтобы запоминать настройки внешнего вида и собирать обезличенную статистику.', 'sd-on-theme' )
	);
	$accept = sdon_cookie_text( 'cookie_accept_text', __( 'Принимаю', 'sd-on-theme' ) );
	$reject = sdon_cookie_text( 'cookie_decline_text', __( 'Отказаться', 'sd-on-theme' ) );
	$link   = sdon_cookie_link();
	$anchor = sdon_cookie_text( 'cookie_link_text', __( 'Соглашение и политика конфиденциальности', 'sd-on-theme' ) );
	?>
	<div
		class="sdon-cookie"
		data-sdon-cookie
		role="dialog"
		aria-modal="false"
		aria-labelledby="sdon-cookie-title"
		hidden
	>
		<?php if ( sdon_is( 'cookie_cat' ) ) : ?>
			<?php sdon_cookie_cat_svg(); ?>
		<?php endif; ?>
		<div class="sdon-cookie__inner">
			<div class="sdon-cookie__content">
				<p class="sdon-cookie__title" id="sdon-cookie-title"><?php echo esc_html( $title ); ?></p>
				<p class="sdon-cookie__text"><?php echo esc_html( $text ); ?></p>
				<?php if ( '' !== $link ) : ?>
					<a class="sdon-cookie__link" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $anchor ); ?></a>
				<?php endif; ?>
			</div>
			<div class="sdon-cookie__actions">
				<button class="sdon-cookie__button sdon-cookie__button--accept" type="button" data-sdon-cookie-accept>
					<?php echo esc_html( $accept ); ?>
				</button>
				<?php if ( sdon_is( 'cookie_decline' ) ) : ?>
					<button class="sdon-cookie__button sdon-cookie__button--decline" type="button" data-sdon-cookie-decline>
						<?php echo esc_html( $reject ); ?>
					</button>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'sdon_cookie_markup', 25 );
