<?php
/**
 * Шаблонные функции вывода: мета-данные, навигация, кнопки.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Название сайта в шапке (с учётом собственного значения из настроек).
 *
 * @return string
 */
function sdon_site_title() {
	$custom = trim( (string) sdon_opt( 'header_title_custom' ) );

	return '' !== $custom ? $custom : get_bloginfo( 'name', 'display' );
}

/**
 * Подпись под названием сайта.
 *
 * @return string
 */
function sdon_site_tagline() {
	$custom = trim( (string) sdon_opt( 'header_tagline' ) );

	return '' !== $custom ? $custom : get_bloginfo( 'description', 'display' );
}

/**
 * Мета-данные записи: категория, дата, автор, комментарии.
 *
 * @param bool $in_card Выводится ли блок внутри карточки новости.
 * @return void
 */
function sdon_post_meta( $in_card = false ) {
	$show_date     = ! $in_card || sdon_is( 'news_show_date' );
	$show_author   = ! $in_card || sdon_is( 'news_show_author' );
	$show_comments = ! $in_card || sdon_is( 'news_show_comments' );

	if ( ! $show_date && ! $show_author && ! $show_comments ) {
		return;
	}
	?>
	<div class="sdon-meta">
		<?php if ( $show_date ) : ?>
			<time class="sdon-meta__item sdon-meta__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
				<?php echo esc_html( get_the_date() ); ?>
			</time>
		<?php endif; ?>

		<?php if ( $show_author ) : ?>
			<span class="sdon-meta__item sdon-meta__author">
				<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author">
					<?php echo esc_html( get_the_author() ); ?>
				</a>
			</span>
		<?php endif; ?>

		<?php if ( $show_comments && ( comments_open() || get_comments_number() ) ) : ?>
			<span class="sdon-meta__item sdon-meta__comments">
				<a href="<?php echo esc_url( get_comments_link() ); ?>">
					<?php
					printf(
						/* translators: %s: количество комментариев. */
						esc_html( _n( '%s комментарий', '%s комментариев', (int) get_comments_number(), 'sd-on-theme' ) ),
						esc_html( number_format_i18n( get_comments_number() ) )
					);
					?>
				</a>
			</span>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Категории записи в виде набора «плашек».
 *
 * @return void
 */
function sdon_post_categories() {
	$categories = get_the_category();

	if ( empty( $categories ) ) {
		return;
	}

	echo '<div class="sdon-categories">';

	foreach ( array_slice( $categories, 0, 2 ) as $category ) {
		printf(
			'<a class="sdon-category" href="%1$s">%2$s</a>',
			esc_url( get_category_link( $category ) ),
			esc_html( $category->name )
		);
	}

	echo '</div>';
}

/**
 * Текст кнопки «Читать далее» для карточек новостей.
 *
 * @return string
 */
function sdon_read_more_text() {
	$text = trim( (string) sdon_opt( 'news_button_text' ) );

	return '' !== $text ? $text : __( 'Читать далее', 'sd-on-theme' );
}

/**
 * Постраничная навигация в едином стиле.
 *
 * @return void
 */
function sdon_pagination() {
	the_posts_pagination(
		array(
			'mid_size'           => 2,
			'prev_text'          => __( 'Назад', 'sd-on-theme' ),
			'next_text'          => __( 'Вперёд', 'sd-on-theme' ),
			'screen_reader_text' => __( 'Навигация по страницам', 'sd-on-theme' ),
			'aria_label'         => __( 'Страницы', 'sd-on-theme' ),
		)
	);
}

/**
 * Цепочка навигации текущей страницы: от главной до текущего заголовка.
 *
 * @return array<int, array{title: string, url: string}>
 */
function sdon_breadcrumb_items() {
	if ( is_front_page() ) {
		return array();
	}

	$items = array(
		array(
			'title' => __( 'Главная', 'sd-on-theme' ),
			'url'   => home_url( '/' ),
		),
	);

	if ( is_singular( 'post' ) ) {
		$categories = get_the_category();

		if ( ! empty( $categories ) ) {
			$items[] = array(
				'title' => $categories[0]->name,
				'url'   => get_category_link( $categories[0] ),
			);
		}

		$items[] = array(
			'title' => get_the_title(),
			'url'   => '',
		);
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $ancestor ) {
			$items[] = array(
				'title' => get_the_title( $ancestor ),
				'url'   => get_permalink( $ancestor ),
			);
		}

		$items[] = array(
			'title' => get_the_title(),
			'url'   => '',
		);
	} elseif ( is_search() ) {
		$items[] = array(
			/* translators: %s: поисковый запрос. */
			'title' => sprintf( __( 'Результаты поиска: %s', 'sd-on-theme' ), get_search_query() ),
			'url'   => '',
		);
	} elseif ( is_404() ) {
		$items[] = array(
			'title' => __( 'Страница не найдена', 'sd-on-theme' ),
			'url'   => '',
		);
	} elseif ( is_archive() ) {
		$items[] = array(
			'title' => wp_strip_all_tags( get_the_archive_title() ),
			'url'   => '',
		);
	}

	return count( $items ) < 2 ? array() : $items;
}

/**
 * Хлебные крошки с микроразметкой Schema.org (BreadcrumbList).
 *
 * @return void
 */
function sdon_breadcrumbs() {
	$items = sdon_is( 'breadcrumbs' ) ? sdon_breadcrumb_items() : array();

	if ( empty( $items ) ) {
		return;
	}

	echo '<nav class="sdon-breadcrumbs" aria-label="' . esc_attr__( 'Хлебные крошки', 'sd-on-theme' ) . '"><ol itemscope itemtype="https://schema.org/BreadcrumbList">';

	$position = 1;

	foreach ( $items as $item ) {
		echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';

		if ( $item['url'] ) {
			printf(
				'<a itemprop="item" href="%1$s"><span itemprop="name">%2$s</span></a>',
				esc_url( $item['url'] ),
				esc_html( $item['title'] )
			);
		} else {
			printf( '<span itemprop="name" aria-current="page">%s</span>', esc_html( $item['title'] ) );
		}

		printf( '<meta itemprop="position" content="%d" /></li>', (int) $position );

		++$position;
	}

	echo '</ol></nav>';
}

/**
 * Кнопка переключения светлой и тёмной схемы.
 *
 * @return void
 */
function sdon_dark_mode_toggle() {
	if ( ! sdon_is( 'dark_mode_toggle' ) ) {
		return;
	}
	?>
	<button class="sdon-scheme-toggle" type="button" data-sdon-scheme-toggle aria-pressed="false">
		<span class="sdon-scheme-toggle__icon" aria-hidden="true"></span>
		<span class="screen-reader-text"><?php esc_html_e( 'Переключить тёмную тему', 'sd-on-theme' ); ?></span>
	</button>
	<?php
}

/**
 * Кнопка «Наверх».
 *
 * @return void
 */
function sdon_back_to_top_button() {
	if ( ! sdon_is( 'back_to_top' ) ) {
		return;
	}

	$style     = sdon_choice( 'back_to_top_style', sdon_back_to_top_styles() );
	$animation = sdon_choice( 'back_to_top_animation', sdon_back_to_top_animations() );
	$icon      = sdon_choice( 'back_to_top_icon', sdon_back_to_top_icons() );
	$position  = 'left' === sdon_opt( 'back_to_top_position' ) ? 'left' : 'right';

	$classes = sprintf(
		'sdon-to-top sdon-to-top--%1$s sdon-to-top--anim-%2$s sdon-to-top--%3$s',
		$style,
		$animation,
		$position
	);
	?>
	<button class="<?php echo esc_attr( $classes ); ?>" type="button" data-sdon-to-top hidden>
		<?php sdon_back_to_top_icon( $icon ); ?>
		<span class="screen-reader-text"><?php esc_html_e( 'Наверх', 'sd-on-theme' ); ?></span>
	</button>
	<?php
}

/**
 * Значок кнопки «Наверх».
 *
 * @param string $icon Идентификатор значка.
 * @return void
 */
function sdon_back_to_top_icon( $icon ) {
	$paths = array(
		'arrow'    => 'M12 5l7 7-1.4 1.4L13 8.8V19h-2V8.8l-4.6 4.6L5 12z',
		'chevron'  => 'M12 8.2l7 7-1.4 1.4L12 11l-5.6 5.6L5 15.2z',
		'double'   => 'M12 4.4l6.6 6.6-1.4 1.4L12 7.2 6.8 12.4 5.4 11zM12 11.6l6.6 6.6-1.4 1.4L12 14.4l-5.2 5.2-1.4-1.4z',
		'triangle' => 'M12 6l8 12H4z',
		'rocket'   => 'M12 2c3.2 2.4 5 6.1 5 10.2l-2.2 2.2H9.2L7 12.2C7 8.1 8.8 4.4 12 2zm-1 9.2a1.4 1.4 0 102.8 0 1.4 1.4 0 00-2.8 0zM9.4 16h5.2l-1 3.2-1.6 2.6-1.6-2.6z',
	);

	$path = isset( $paths[ $icon ] ) ? $paths[ $icon ] : $paths['arrow'];
	?>
	<svg class="sdon-to-top__icon" width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
		<path d="<?php echo esc_attr( $path ); ?>" fill="currentColor" />
	</svg>
	<?php
}

/**
 * Ссылки на социальные сети.
 *
 * @return void
 */
function sdon_social_links() {
	if ( ! sdon_is( 'footer_show_socials' ) ) {
		return;
	}

	$socials = sdon_socials();

	if ( empty( $socials ) ) {
		return;
	}

	echo '<ul class="sdon-socials">';

	foreach ( $socials as $social ) {
		printf(
			'<li><a class="sdon-social sdon-social--%1$s" href="%2$s" rel="noopener noreferrer nofollow" target="_blank">%3$s</a></li>',
			esc_attr( $social['key'] ),
			esc_url( $social['url'] ),
			esc_html( $social['label'] )
		);
	}

	echo '</ul>';
}

/**
 * Изображение записи для карточки новости с корректными srcset и sizes.
 *
 * @return void
 */
function sdon_card_thumbnail() {
	if ( ! sdon_is( 'news_show_image' ) || ! has_post_thumbnail() ) {
		return;
	}

	$columns = max( 1, (int) sdon_opt( 'news_columns' ) );
	$sizes   = sprintf(
		'(max-width: 599px) 100vw, (max-width: 899px) 50vw, %dvw',
		max( 15, (int) floor( 100 / $columns ) )
	);

	?>
	<a class="sdon-card__image" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php
		the_post_thumbnail(
			'sdon-card',
			array(
				'sizes'    => $sizes,
				'loading'  => sdon_is( 'lazy_loading' ) ? 'lazy' : 'eager',
				'decoding' => 'async',
			)
		);
		?>
	</a>
	<?php
}
