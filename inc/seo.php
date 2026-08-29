<?php
/**
 * SEO: заголовки, описания, индексация, Open Graph, микроразметка Schema.org,
 * коды подтверждения прав и robots.txt.
 *
 * Мета-теги, за которые обычно отвечает SEO-плагин, не выводятся, если такой
 * плагин активен — это исключает дублирование.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Активен ли SEO-плагин, который сам добавляет мета-теги.
 *
 * @return bool
 */
function sdon_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'SEOPRESS_VERSION' )
		|| class_exists( 'All_in_One_SEO_Pack' )
		|| function_exists( 'aioseo' );
}

/**
 * Разделители заголовка: значение => символ.
 *
 * @return array<string, string>
 */
function sdon_seo_separator_chars() {
	return array(
		'dash'   => '–',
		'mdash'  => '—',
		'hyphen' => '-',
		'pipe'   => '|',
		'bullet' => '•',
		'middot' => '·',
		'slash'  => '/',
		'raquo'  => '»',
		'tilde'  => '~',
		'colon'  => ':',
	);
}

/**
 * Варианты разделителя заголовка для панели настроек.
 *
 * @return array<string, string>
 */
function sdon_seo_separators() {
	$labels = array();

	foreach ( sdon_seo_separator_chars() as $key => $char ) {
		$labels[ $key ] = sprintf(
			/* translators: %s: символ-разделитель. */
			__( 'Название сайта %s Заголовок страницы', 'sd-on-theme' ),
			$char
		);
	}

	return $labels;
}

/**
 * Выбранный символ-разделитель заголовка.
 *
 * @return string
 */
function sdon_seo_separator() {
	$chars = sdon_seo_separator_chars();
	$value = (string) sdon_opt( 'seo_separator' );

	return isset( $chars[ $value ] ) ? $chars[ $value ] : $chars['dash'];
}

/**
 * Допустимые размеры превью в выдаче.
 *
 * @return array<string, string>
 */
function sdon_seo_preview_sizes() {
	return array(
		'large'    => __( 'Большое превью', 'sd-on-theme' ),
		'standard' => __( 'Обычное превью', 'sd-on-theme' ),
		'none'     => __( 'Без превью', 'sd-on-theme' ),
	);
}

/**
 * Кого представляет сайт в микроразметке.
 *
 * @return array<string, string>
 */
function sdon_seo_schema_types() {
	return array(
		'organization' => __( 'Организацию или проект', 'sd-on-theme' ),
		'person'       => __( 'Человека', 'sd-on-theme' ),
	);
}

/**
 * Разделитель заголовка документа.
 *
 * @param string $separator Разделитель WordPress.
 * @return string
 */
function sdon_seo_document_title_separator( $separator ) {
	if ( ! sdon_is( 'seo_titles' ) || sdon_seo_plugin_active() ) {
		return $separator;
	}

	return sdon_seo_separator();
}
add_filter( 'document_title_separator', 'sdon_seo_document_title_separator' );

/**
 * Свой заголовок главной страницы.
 *
 * @param string $title Готовый заголовок.
 * @return string
 */
function sdon_seo_document_title( $title ) {
	if ( ! sdon_is( 'seo_titles' ) || sdon_seo_plugin_active() || ! is_front_page() || is_paged() ) {
		return $title;
	}

	$home = trim( (string) sdon_opt( 'seo_home_title' ) );

	return '' === $home ? $title : $home;
}
add_filter( 'pre_get_document_title', 'sdon_seo_document_title' );

/**
 * Описание текущей страницы для meta description и Open Graph.
 *
 * @return string
 */
function sdon_seo_description() {
	$length = (int) sdon_opt( 'seo_description_length' );
	$length = min( 320, max( 80, $length ) );

	if ( is_front_page() ) {
		$home = trim( (string) sdon_opt( 'seo_home_description' ) );

		return '' === $home ? (string) get_bloginfo( 'description' ) : $home;
	}

	if ( is_singular() ) {
		return sdon_trimmed_excerpt( $length );
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term && '' !== $term->description ) {
			return sdon_seo_shorten( wp_strip_all_tags( $term->description ), $length );
		}
	}

	if ( is_author() ) {
		$bio = (string) get_the_author_meta( 'description', (int) get_queried_object_id() );

		if ( '' !== trim( $bio ) ) {
			return sdon_seo_shorten( wp_strip_all_tags( $bio ), $length );
		}
	}

	return (string) get_bloginfo( 'description' );
}

/**
 * Обрезает текст по границе слова.
 *
 * @param string $text   Текст.
 * @param int    $length Максимальная длина.
 * @return string
 */
function sdon_seo_shorten( $text, $length ) {
	$text = trim( preg_replace( '/\s+/u', ' ', (string) $text ) );

	if ( mb_strlen( $text ) <= $length ) {
		return $text;
	}

	$text = mb_substr( $text, 0, $length );
	$last = mb_strrpos( $text, ' ' );

	if ( false !== $last ) {
		$text = mb_substr( $text, 0, $last );
	}

	return $text . '…';
}

/**
 * Мета-тег description.
 *
 * @return void
 */
function sdon_seo_meta_description() {
	if ( ! sdon_is( 'seo_meta_description' ) || sdon_seo_plugin_active() || is_404() ) {
		return;
	}

	$description = trim( sdon_seo_description() );

	if ( '' === $description ) {
		return;
	}

	printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
}
add_action( 'wp_head', 'sdon_seo_meta_description', 2 );

/**
 * Canonical-адрес текущей страницы.
 *
 * @return string
 */
function sdon_seo_canonical_url() {
	if ( is_front_page() ) {
		return home_url( '/' );
	}

	if ( is_singular() ) {
		return (string) get_permalink();
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			$link = get_term_link( $term );

			return is_wp_error( $link ) ? '' : (string) $link;
		}
	}

	if ( is_author() ) {
		return (string) get_author_posts_url( (int) get_queried_object_id() );
	}

	if ( is_post_type_archive() ) {
		$link = get_post_type_archive_link( (string) get_query_var( 'post_type' ) );

		return $link ? (string) $link : '';
	}

	if ( is_home() ) {
		$page_id = (int) get_option( 'page_for_posts' );

		return $page_id ? (string) get_permalink( $page_id ) : home_url( '/' );
	}

	return '';
}

/**
 * Тег link rel="canonical".
 *
 * @return void
 */
function sdon_seo_canonical() {
	if ( ! sdon_is( 'seo_canonical' ) || sdon_seo_plugin_active() || is_404() || is_search() ) {
		return;
	}

	$url = sdon_seo_canonical_url();

	if ( '' === $url ) {
		return;
	}

	$page = (int) get_query_var( 'paged' );

	if ( $page > 1 ) {
		$url = get_pagenum_link( $page, false );
	}

	printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $url ) );
}
add_action( 'wp_head', 'sdon_seo_canonical', 3 );

/**
 * Отключает канонический тег ядра, когда его выводит тема.
 *
 * @return void
 */
function sdon_seo_disable_core_canonical() {
	if ( sdon_is( 'seo_canonical' ) && ! sdon_seo_plugin_active() ) {
		remove_action( 'wp_head', 'rel_canonical' );
	}
}
add_action( 'template_redirect', 'sdon_seo_disable_core_canonical' );

/**
 * Директивы для поисковых роботов.
 *
 * @param array<string, mixed> $robots Директивы ядра.
 * @return array<string, mixed>
 */
function sdon_seo_robots( $robots ) {
	if ( sdon_seo_plugin_active() ) {
		return $robots;
	}

	$noindex = ( is_search() && sdon_is( 'seo_noindex_search' ) )
		|| ( is_author() && sdon_is( 'seo_noindex_author' ) )
		|| ( is_date() && sdon_is( 'seo_noindex_date' ) )
		|| ( is_tag() && sdon_is( 'seo_noindex_tag' ) )
		|| ( is_paged() && sdon_is( 'seo_noindex_paged' ) );

	if ( $noindex ) {
		$robots['noindex']  = true;
		$robots['follow']   = true;
		$robots['nofollow'] = false;
		$robots['index']    = false;

		return $robots;
	}

	$preview = (string) sdon_opt( 'seo_max_image_preview' );
	$sizes   = sdon_seo_preview_sizes();

	if ( ! isset( $sizes[ $preview ] ) ) {
		$preview = 'large';
	}

	$robots['max-image-preview'] = $preview;
	$robots['max-snippet']       = -1;
	$robots['max-video-preview'] = -1;

	return $robots;
}
add_filter( 'wp_robots', 'sdon_seo_robots', 20 );

/**
 * Мета-теги подтверждения прав в панелях веб-мастеров.
 *
 * @return void
 */
function sdon_seo_verification() {
	$codes = array(
		'google-site-verification' => 'seo_verify_google',
		'yandex-verification'      => 'seo_verify_yandex',
		'msvalidate.01'            => 'seo_verify_bing',
		'mailru-verification'      => 'seo_verify_mailru',
	);

	foreach ( $codes as $name => $option ) {
		$code = trim( (string) sdon_opt( $option ) );

		if ( '' === $code ) {
			continue;
		}

		printf(
			'<meta name="%1$s" content="%2$s" />' . "\n",
			esc_attr( $name ),
			esc_attr( $code )
		);
	}
}
add_action( 'wp_head', 'sdon_seo_verification', 1 );

/**
 * Убирает из head теги, не нужные поисковым системам.
 *
 * @return void
 */
function sdon_seo_clean_head() {
	if ( ! sdon_is( 'seo_clean_head' ) ) {
		return;
	}

	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
}
add_action( 'init', 'sdon_seo_clean_head' );

/**
 * Перенаправляет страницу вложения на родительскую запись или файл.
 *
 * @return void
 */
function sdon_seo_attachment_redirect() {
	if ( ! is_attachment() || ! sdon_is( 'seo_attachment_redirect' ) ) {
		return;
	}

	$attachment = get_queried_object();

	if ( ! $attachment instanceof WP_Post ) {
		return;
	}

	$parent = (int) $attachment->post_parent;
	$target = $parent ? get_permalink( $parent ) : wp_get_attachment_url( $attachment->ID );

	if ( $target ) {
		wp_safe_redirect( $target, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'sdon_seo_attachment_redirect', 1 );

/**
 * Дополняет robots.txt служебными правилами и картой сайта.
 *
 * @param string $output Содержимое robots.txt.
 * @param string $is_public Открыт ли сайт для индексации.
 * @return string
 */
function sdon_seo_robots_txt( $output, $is_public ) {
	if ( ! sdon_is( 'seo_robots_txt' ) || '1' !== (string) $is_public ) {
		return $output;
	}

	$rules = array(
		'Disallow: /wp-admin/',
		'Allow: /wp-admin/admin-ajax.php',
		'Disallow: /?s=',
		'Disallow: /search/',
		'Disallow: /*?replytocom',
		'Disallow: /trackback/',
		'Disallow: /comments/feed/',
	);

	$extra = trim( (string) sdon_opt( 'seo_robots_txt_extra' ) );

	if ( '' !== $extra ) {
		foreach ( preg_split( '/\r\n|\r|\n/', $extra ) as $line ) {
			$line = trim( $line );

			if ( '' !== $line ) {
				$rules[] = $line;
			}
		}
	}

	$output .= implode( "\n", $rules ) . "\n";

	$sitemap = function_exists( 'wp_sitemaps_get_server' ) ? home_url( '/wp-sitemap.xml' ) : '';

	if ( $sitemap ) {
		$output .= "\nSitemap: " . $sitemap . "\n";
	}

	return $output;
}
add_filter( 'robots_txt', 'sdon_seo_robots_txt', 10, 2 );

/**
 * Изображение для социальных карточек.
 *
 * @return string
 */
function sdon_seo_social_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		return (string) get_the_post_thumbnail_url( null, 'full' );
	}

	$custom = trim( (string) sdon_opt( 'seo_og_image' ) );

	if ( '' !== $custom ) {
		return $custom;
	}

	$logo_id = (int) get_theme_mod( 'custom_logo' );

	if ( $logo_id ) {
		return (string) wp_get_attachment_image_url( $logo_id, 'full' );
	}

	return '';
}

/**
 * Мета-теги Open Graph и карточки X (Twitter).
 *
 * @return void
 */
function sdon_open_graph() {
	if ( ! sdon_is( 'seo_open_graph' ) || sdon_seo_plugin_active() || is_404() ) {
		return;
	}

	$title       = wp_get_document_title();
	$description = sdon_seo_description();
	$url         = sdon_seo_canonical_url();
	$type        = is_singular( 'post' ) ? 'article' : 'website';
	$image       = sdon_seo_social_image();

	if ( '' === $url ) {
		$url = home_url( add_query_arg( array() ) );
	}

	$tags = array(
		'og:locale'    => get_locale(),
		'og:site_name' => get_bloginfo( 'name' ),
		'og:type'      => $type,
		'og:title'     => $title,
		'og:url'       => $url,
	);

	if ( '' !== trim( $description ) ) {
		$tags['og:description'] = $description;
	}

	if ( '' !== $image ) {
		$tags['og:image'] = $image;
	}

	if ( 'article' === $type ) {
		$tags['article:published_time'] = get_the_date( DATE_W3C );
		$tags['article:modified_time']  = get_the_modified_date( DATE_W3C );
	}

	foreach ( $tags as $property => $content ) {
		printf(
			'<meta property="%1$s" content="%2$s" />' . "\n",
			esc_attr( $property ),
			esc_attr( $content )
		);
	}

	printf(
		'<meta name="twitter:card" content="%s" />' . "\n",
		'' !== $image ? 'summary_large_image' : 'summary'
	);

	$twitter = trim( (string) sdon_opt( 'seo_twitter_site' ) );

	if ( '' !== $twitter ) {
		printf(
			'<meta name="twitter:site" content="@%s" />' . "\n",
			esc_attr( ltrim( $twitter, '@' ) )
		);
	}
}
add_action( 'wp_head', 'sdon_open_graph', 5 );

/**
 * Издатель сайта для микроразметки: организация или человек.
 *
 * @return array<string, mixed>
 */
function sdon_seo_publisher() {
	$is_person = 'person' === (string) sdon_opt( 'seo_schema_type' );
	$name      = trim( (string) sdon_opt( 'seo_schema_name' ) );

	$publisher = array(
		'@type' => $is_person ? 'Person' : 'Organization',
		'@id'   => home_url( '/#publisher' ),
		'name'  => '' === $name ? get_bloginfo( 'name' ) : $name,
		'url'   => home_url( '/' ),
	);

	$logo = trim( (string) sdon_opt( 'seo_schema_logo' ) );

	if ( '' === $logo ) {
		$logo_id = (int) get_theme_mod( 'custom_logo' );
		$logo    = $logo_id ? (string) wp_get_attachment_image_url( $logo_id, 'full' ) : '';
	}

	if ( '' !== $logo ) {
		$publisher['logo'] = array(
			'@type' => 'ImageObject',
			'url'   => $logo,
		);
	}

	$same_as = array();

	foreach ( sdon_socials() as $social ) {
		if ( 'rss' === $social['key'] ) {
			continue;
		}

		$same_as[] = $social['url'];
	}

	if ( ! empty( $same_as ) ) {
		$publisher['sameAs'] = $same_as;
	}

	return $publisher;
}

/**
 * Цепочка навигации в формате JSON-LD.
 *
 * @return array<string, mixed>|null
 */
function sdon_seo_breadcrumb_schema() {
	$items = sdon_breadcrumb_items();

	if ( empty( $items ) ) {
		return null;
	}

	$base     = sdon_seo_canonical_url();
	$base     = '' === $base ? home_url( add_query_arg( array() ) ) : $base;
	$list     = array();
	$position = 1;

	foreach ( $items as $item ) {
		$entry = array(
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => $item['title'],
		);

		if ( '' !== $item['url'] ) {
			$entry['item'] = $item['url'];
		}

		$list[] = $entry;

		++$position;
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => $base . '#breadcrumb',
		'itemListElement' => $list,
	);
}

/**
 * Микроразметка Schema.org в формате JSON-LD.
 *
 * @return void
 */
function sdon_schema_json_ld() {
	if ( ! sdon_is( 'seo_schema' ) || sdon_seo_plugin_active() ) {
		return;
	}

	$publisher = sdon_seo_publisher();

	$website = array(
		'@type'      => 'WebSite',
		'@id'        => home_url( '/#website' ),
		'url'        => home_url( '/' ),
		'name'       => get_bloginfo( 'name' ),
		'publisher'  => array( '@id' => $publisher['@id'] ),
		'inLanguage' => get_bloginfo( 'language' ),
	);

	if ( get_bloginfo( 'description' ) ) {
		$website['description'] = get_bloginfo( 'description' );
	}

	if ( sdon_is( 'seo_schema_search' ) ) {
		$website['potentialAction'] = array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		);
	}

	$graph = array( $publisher, $website );

	if ( sdon_is( 'seo_schema_breadcrumbs' ) ) {
		$breadcrumb = sdon_seo_breadcrumb_schema();

		if ( $breadcrumb ) {
			$graph[] = $breadcrumb;
		}
	}

	if ( is_singular( 'post' ) ) {
		$article = array(
			'@type'            => 'Article',
			'@id'              => get_permalink() . '#article',
			'headline'         => get_the_title(),
			'datePublished'    => get_the_date( DATE_W3C ),
			'dateModified'     => get_the_modified_date( DATE_W3C ),
			'author'           => array(
				'@type' => 'Person',
				'name'  => get_the_author(),
				'url'   => get_author_posts_url( (int) get_the_author_meta( 'ID' ) ),
			),
			'publisher'        => array( '@id' => $publisher['@id'] ),
			'mainEntityOfPage' => get_permalink(),
			'isPartOf'         => array( '@id' => home_url( '/#website' ) ),
			'description'      => sdon_seo_description(),
			'inLanguage'       => get_bloginfo( 'language' ),
		);

		if ( has_post_thumbnail() ) {
			$article['image'] = get_the_post_thumbnail_url( null, 'full' );
		}

		$graph[] = $article;
	} elseif ( is_page() ) {
		$graph[] = array(
			'@type'      => 'WebPage',
			'@id'        => get_permalink() . '#webpage',
			'url'        => get_permalink(),
			'name'       => get_the_title(),
			'isPartOf'   => array( '@id' => home_url( '/#website' ) ),
			'inLanguage' => get_bloginfo( 'language' ),
		);
	} elseif ( is_category() || is_tag() || is_tax() || is_post_type_archive() ) {
		$graph[] = array(
			'@type'       => 'CollectionPage',
			'@id'         => sdon_seo_canonical_url() . '#collection',
			'url'         => sdon_seo_canonical_url(),
			'name'        => wp_strip_all_tags( get_the_archive_title() ),
			'description' => sdon_seo_description(),
			'isPartOf'    => array( '@id' => home_url( '/#website' ) ),
			'inLanguage'  => get_bloginfo( 'language' ),
		);
	}

	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}
add_action( 'wp_head', 'sdon_schema_json_ld', 6 );

/**
 * Подстановка alt из подписи вложения, если alt не заполнен.
 *
 * @param array<string, string> $attr       Атрибуты изображения.
 * @param WP_Post               $attachment Вложение.
 * @return array<string, string>
 */
function sdon_image_alt_fallback( $attr, $attachment ) {
	if ( empty( $attr['alt'] ) ) {
		$attr['alt'] = trim( wp_strip_all_tags( $attachment->post_title ) );
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'sdon_image_alt_fallback', 10, 2 );
