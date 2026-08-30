<?php
/**
 * Загрузка и удаление пользовательских шрифтов.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Допустимые форматы шрифтов и их MIME-типы.
 *
 * @return array<string, string>
 */
function sdon_font_mimes() {
	return array(
		'woff2' => 'font/woff2',
		'woff'  => 'font/woff',
		'ttf'   => 'font/ttf',
		'otf'   => 'font/otf',
	);
}

/**
 * Вкладка «Свои шрифты».
 *
 * @return void
 */
function sdon_render_fonts_tab() {
	$fonts = sdon_custom_fonts();
	?>
	<h2><?php esc_html_e( 'Загрузка собственного шрифта', 'sd-on-theme' ); ?></h2>
	<p>
		<?php esc_html_e( 'Поддерживаются форматы WOFF2, WOFF, TTF и OTF. Приоритетным для веба считается WOFF2 — он весит меньше и загружается быстрее.', 'sd-on-theme' ); ?>
	</p>

	<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="sdon_upload_font" />
		<?php wp_nonce_field( 'sdon_upload_font' ); ?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="sdon-font-name"><?php esc_html_e( 'Название шрифта', 'sd-on-theme' ); ?></label></th>
				<td>
					<input class="regular-text" type="text" id="sdon-font-name" name="sdon_font_name" required />
					<p class="description"><?php esc_html_e( 'Под этим названием шрифт появится в списке доступных шрифтов темы.', 'sd-on-theme' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sdon-font-files"><?php esc_html_e( 'Файлы шрифта', 'sd-on-theme' ); ?></label></th>
				<td>
					<input type="file" id="sdon-font-files" name="sdon_font_files[]" accept=".woff2,.woff,.ttf,.otf" multiple required />
					<p class="description"><?php esc_html_e( 'Можно загрузить несколько форматов одного шрифта одновременно.', 'sd-on-theme' ); ?></p>
				</td>
			</tr>
		</table>

		<?php submit_button( __( 'Загрузить шрифт', 'sd-on-theme' ) ); ?>
	</form>

	<h2><?php esc_html_e( 'Загруженные шрифты', 'sd-on-theme' ); ?></h2>

	<?php if ( empty( $fonts ) ) : ?>
		<p><?php esc_html_e( 'Пользовательские шрифты пока не загружены.', 'sd-on-theme' ); ?></p>
	<?php else : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Название', 'sd-on-theme' ); ?></th>
					<th><?php esc_html_e( 'Форматы', 'sd-on-theme' ); ?></th>
					<th><?php esc_html_e( 'Действия', 'sd-on-theme' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $fonts as $key => $font ) : ?>
					<tr>
						<td><?php echo esc_html( $font['label'] ); ?></td>
						<td><?php echo esc_html( strtoupper( implode( ', ', array_keys( $font['files'] ) ) ) ); ?></td>
						<td>
							<a
								class="button button-link-delete"
								href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=sdon_delete_font&font=' . rawurlencode( $key ) ), 'sdon_delete_font_' . $key ) ); ?>"
								onclick="return confirm('<?php echo esc_js( __( 'Удалить этот шрифт? Настройки, использующие его, вернутся к системному шрифту.', 'sd-on-theme' ) ); ?>');"
							>
								<?php esc_html_e( 'Удалить', 'sd-on-theme' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<p class="description">
			<?php esc_html_e( 'Выбрать шрифт для заголовков или основного текста можно в разделе «Шрифты» настроек темы.', 'sd-on-theme' ); ?>
		</p>
	<?php endif; ?>
	<?php
}

/**
 * Разрешает загрузку файлов шрифтов только во время обработки нашей формы.
 *
 * @param array<string, string> $mimes Разрешённые MIME-типы.
 * @return array<string, string>
 */
function sdon_allow_font_mimes( $mimes ) {
	foreach ( sdon_font_mimes() as $ext => $mime ) {
		$mimes[ $ext ] = $mime;
	}

	return $mimes;
}

/**
 * Обработка загрузки шрифта.
 *
 * @return void
 */
function sdon_handle_font_upload() {
	if ( ! current_user_can( 'edit_theme_options' ) || ! current_user_can( 'upload_files' ) ) {
		wp_die( esc_html__( 'Недостаточно прав для загрузки шрифтов.', 'sd-on-theme' ) );
	}

	check_admin_referer( 'sdon_upload_font' );

	$name = isset( $_POST['sdon_font_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sdon_font_name'] ) ) : '';

	if ( '' === $name || empty( $_FILES['sdon_font_files']['name'][0] ) ) {
		wp_safe_redirect( sdon_admin_redirect_url( 'font-error', 'fonts' ) );
		exit;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';

	add_filter( 'upload_mimes', 'sdon_allow_font_mimes' );

	$allowed = sdon_font_mimes();
	$files   = array();
	$count   = count( $_FILES['sdon_font_files']['name'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- значения проверяются ниже через wp_handle_upload().

	for ( $index = 0; $index < $count; $index++ ) {
		$file = array(
			'name'     => sanitize_file_name( wp_unslash( $_FILES['sdon_font_files']['name'][ $index ] ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			'type'     => isset( $_FILES['sdon_font_files']['type'][ $index ] ) ? sanitize_mime_type( wp_unslash( $_FILES['sdon_font_files']['type'][ $index ] ) ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			'tmp_name' => isset( $_FILES['sdon_font_files']['tmp_name'][ $index ] ) ? $_FILES['sdon_font_files']['tmp_name'][ $index ] : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			'error'    => isset( $_FILES['sdon_font_files']['error'][ $index ] ) ? (int) $_FILES['sdon_font_files']['error'][ $index ] : UPLOAD_ERR_NO_FILE,
			'size'     => isset( $_FILES['sdon_font_files']['size'][ $index ] ) ? (int) $_FILES['sdon_font_files']['size'][ $index ] : 0,
		);

		$extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

		if ( ! isset( $allowed[ $extension ] ) ) {
			continue;
		}

		$upload = wp_handle_upload(
			$file,
			array(
				'test_form' => false,
				'mimes'     => $allowed,
			)
		);

		if ( isset( $upload['error'] ) || empty( $upload['url'] ) ) {
			continue;
		}

		$files[ $extension ] = array(
			'url'  => $upload['url'],
			'file' => $upload['file'],
		);
	}

	remove_filter( 'upload_mimes', 'sdon_allow_font_mimes' );

	if ( empty( $files ) ) {
		wp_safe_redirect( sdon_admin_redirect_url( 'font-error', 'fonts' ) );
		exit;
	}

	$fonts = sdon_custom_fonts();
	$key   = sanitize_key( sanitize_title( $name ) );

	if ( '' === $key ) {
		$key = 'font-' . count( $fonts );
	}

	$urls  = array();
	$paths = array();

	foreach ( $files as $extension => $data ) {
		$urls[ $extension ]  = $data['url'];
		$paths[ $extension ] = $data['file'];
	}

	$fonts[ $key ] = array(
		'label' => $name,
		'files' => $urls,
		'paths' => $paths,
	);

	update_option( SDON_FONTS_OPTION, $fonts, false );

	wp_safe_redirect( sdon_admin_redirect_url( 'font-added', 'fonts' ) );
	exit;
}
add_action( 'admin_post_sdon_upload_font', 'sdon_handle_font_upload' );

/**
 * Удаление пользовательского шрифта вместе с файлами.
 *
 * @return void
 */
function sdon_handle_font_delete() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав для удаления шрифтов.', 'sd-on-theme' ) );
	}

	$key = isset( $_GET['font'] ) ? sanitize_key( wp_unslash( $_GET['font'] ) ) : '';

	check_admin_referer( 'sdon_delete_font_' . $key );

	$fonts = sdon_custom_fonts();

	if ( ! isset( $fonts[ $key ] ) ) {
		wp_safe_redirect( sdon_admin_redirect_url( 'font-error', 'fonts' ) );
		exit;
	}

	if ( ! empty( $fonts[ $key ]['paths'] ) ) {
		foreach ( $fonts[ $key ]['paths'] as $path ) {
			wp_delete_file( $path );
		}
	}

	unset( $fonts[ $key ] );

	update_option( SDON_FONTS_OPTION, $fonts, false );

	// Настройки, использовавшие удалённый шрифт, возвращаются к системному.
	$font_value = 'custom:' . $key;

	foreach ( array( 'font_body', 'font_headings', 'font_menu' ) as $setting ) {
		$current = get_theme_mod( $setting );

		if ( $current === $font_value ) {
			set_theme_mod( $setting, sdon_default( $setting ) );
		}
	}

	wp_safe_redirect( sdon_admin_redirect_url( 'font-deleted', 'fonts' ) );
	exit;
}
add_action( 'admin_post_sdon_delete_font', 'sdon_handle_font_delete' );
