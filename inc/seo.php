<?php
/**
 * SEO: Open Graph и микроразметка Schema.org.
 *
 * Разметка не выводится, если активен популярный SEO-плагин — это исключает
 * дублирование мета-тегов.
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
 * Мета-теги Open Graph.
 *
 * @return void
 */
function sdon_open_graph() {
	if ( ! sdon_is( 'seo_open_graph' ) || sdon_seo_plugin_active() || is_404() ) {
		return;
	}

	$title       = is_front_page() ? get_bloginfo( 'name' ) : wp_get_document_title();
	$description = get_bloginfo( 'description' );
	$url         = home_url( add_query_arg( array() ) );
	$type        = is_singular() ? 'article' : 'website';
	$image       = '';

	if ( is_singular() ) {
		$description = sdon_trimmed_excerpt( 200 );
		$url         = get_permalink();

		if ( has_post_thumbnail() ) {
			$image = get_the_post_thumbnail_url( null, 'full' );
		}
	}

	if ( '' === $image ) {
		$logo_id = (int) get_theme_mod( 'custom_logo' );

		if ( $logo_id ) {
			$image = wp_get_attachment_image_url( $logo_id, 'full' );
		}
	}

	$tags = array(
		'og:locale'    => get_locale(),
		'og:site_name' => get_bloginfo( 'name' ),
		'og:type'      => $type,
		'og:title'     => $title,
		'og:url'       => $url,
	);

	if ( $description ) {
		$tags['og:description'] = $description;
	}

	if ( $image ) {
		$tags['og:image'] = $image;
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
		$image ? 'summary_large_image' : 'summary'
	);
}
add_action( 'wp_head', 'sdon_open_graph', 5 );

/**
 * Микроразметка Schema.org в формате JSON-LD.
 *
 * @return void
 */
function sdon_schema_json_ld() {
	if ( ! sdon_is( 'seo_schema' ) || sdon_seo_plugin_active() ) {
		return;
	}

	$graph = array(
		array(
			'@type' => 'WebSite',
			'@id'   => home_url( '/#website' ),
			'url'   => home_url( '/' ),
			'name'  => get_bloginfo( 'name' ),
		),
	);

	if ( get_bloginfo( 'description' ) ) {
		$graph[0]['description'] = get_bloginfo( 'description' );
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
			),
			'mainEntityOfPage' => get_permalink(),
			'isPartOf'         => array( '@id' => home_url( '/#website' ) ),
			'description'      => sdon_trimmed_excerpt( 200 ),
		);

		if ( has_post_thumbnail() ) {
			$article['image'] = get_the_post_thumbnail_url( null, 'full' );
		}

		$graph[] = $article;
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
