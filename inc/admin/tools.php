<?php
/**
 * Сброс, экспорт и импорт настроек темы.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Вкладка «Сброс, экспорт и импорт».
 *
 * @return void
 */
function sdon_render_tools_tab() {
	?>
	<h2><?php esc_html_e( 'Резервная копия настроек', 'sd-on-theme' ); ?></h2>
	<p><?php esc_html_e( 'Файл в формате JSON содержит все настройки темы и позволяет быстро перенести оформление на другой сайт.', 'sd-on-theme' ); ?></p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="sdon_export_settings" />
		<?php wp_nonce_field( 'sdon_export_settings' ); ?>
		<?php submit_button( __( 'Экспортировать настройки (JSON)', 'sd-on-theme' ), 'secondary', 'submit', false ); ?>
	</form>

	<h2><?php esc_html_e( 'Импорт настроек', 'sd-on-theme' ); ?></h2>
	<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="sdon_import_settings" />
		<?php wp_nonce_field( 'sdon_import_settings' ); ?>
		<p><input type="file" name="sdon_import_file" accept=".json,application/json" required /></p>
		<?php submit_button( __( 'Импортировать настройки', 'sd-on-theme' ), 'secondary', 'submit', false ); ?>
	</form>

	<h2><?php esc_html_e( 'Сброс настроек темы', 'sd-on-theme' ); ?></h2>
	<div class="notice notice-warning inline">
		<p><?php esc_html_e( 'Все пользовательские настройки темы будут возвращены к значениям по умолчанию.', 'sd-on-theme' ); ?></p>
	</div>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="sdon_reset_settings" />
		<?php wp_nonce_field( 'sdon_reset_settings' ); ?>
		<p>
			<label>
				<input type="checkbox" name="sdon_reset_confirm" value="1" required />
				<?php esc_html_e( 'Я подтверждаю сброс настроек темы', 'sd-on-theme' ); ?>
			</label>
		</p>
		<p>
			<button
				type="submit"
				class="button button-link-delete"
				onclick="return confirm('<?php echo esc_js( __( 'Все пользовательские настройки темы будут возвращены к значениям по умолчанию. Продолжить?', 'sd-on-theme' ) ); ?>');"
			>
				<?php esc_html_e( 'Сбросить настройки темы', 'sd-on-theme' ); ?>
			</button>
		</p>
	</form>
	<?php
}

/**
 * Экспорт настроек темы в JSON-файл.
 *
 * @return void
 */
function sdon_handle_export() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав для экспорта настроек.', 'sd-on-theme' ) );
	}

	check_admin_referer( 'sdon_export_settings' );

	$mods = get_theme_mods();
	$mods = is_array( $mods ) ? $mods : array();

	unset( $mods['nav_menu_locations'], $mods['sidebars_widgets'] );

	$payload = array(
		'theme'    => get_template(),
		'version'  => SDON_VERSION,
		'exported' => gmdate( 'c' ),
		'settings' => $mods,
	);

	$filename = sprintf( 'sd-on-theme-settings-%s.json', gmdate( 'Y-m-d' ) );

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

	echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	exit;
}
add_action( 'admin_post_sdon_export_settings', 'sdon_handle_export' );

/**
 * Импорт настроек темы из JSON-файла.
 *
 * @return void
 */
function sdon_handle_import() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав для импорта настроек.', 'sd-on-theme' ) );
	}

	check_admin_referer( 'sdon_import_settings' );

	if ( empty( $_FILES['sdon_import_file']['tmp_name'] ) || ! empty( $_FILES['sdon_import_file']['error'] ) ) {
		wp_safe_redirect( sdon_admin_redirect_url( 'import-error', 'tools' ) );
		exit;
	}

	$tmp_name = $_FILES['sdon_import_file']['tmp_name']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- путь временного файла PHP, содержимое проверяется ниже.

	if ( ! is_uploaded_file( $tmp_name ) ) {
		wp_safe_redirect( sdon_admin_redirect_url( 'import-error', 'tools' ) );
		exit;
	}

	global $wp_filesystem;

	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();

	$contents = $wp_filesystem ? $wp_filesystem->get_contents( $tmp_name ) : false;
	$data     = $contents ? json_decode( $contents, true ) : null;

	if ( ! is_array( $data ) || empty( $data['settings'] ) || ! is_array( $data['settings'] ) ) {
		wp_safe_redirect( sdon_admin_redirect_url( 'import-error', 'tools' ) );
		exit;
	}

	$imported = sdon_sanitize_imported_settings( $data['settings'] );

	foreach ( $imported as $key => $value ) {
		set_theme_mod( $key, $value );
	}

	wp_safe_redirect( sdon_admin_redirect_url( 'import-done', 'tools' ) );
	exit;
}
add_action( 'admin_post_sdon_import_settings', 'sdon_handle_import' );

/**
 * Проверяет и приводит импортируемые значения к типам настроек темы.
 *
 * Ключи, которых нет в списке настроек темы, отбрасываются.
 *
 * @param array<string, mixed> $settings Значения из файла.
 * @return array<string, mixed>
 */
function sdon_sanitize_imported_settings( $settings ) {
	$defaults = sdon_defaults();
	$result   = array();

	foreach ( $settings as $key => $value ) {
		if ( ! array_key_exists( $key, $defaults ) ) {
			continue;
		}

		$default = $defaults[ $key ];

		if ( is_bool( $default ) ) {
			$result[ $key ] = (bool) $value;
			continue;
		}

		if ( is_int( $default ) ) {
			$result[ $key ] = (int) $value;
			continue;
		}

		if ( is_float( $default ) ) {
			$result[ $key ] = (float) $value;
			continue;
		}

		if ( ! is_scalar( $value ) ) {
			continue;
		}

		$value = (string) $value;

		$is_color = 0 === strpos( $key, 'color_' )
			|| 0 === strpos( $key, 'dark_color_' )
			|| 'bg_color' === $key
			|| in_array(
				$key,
				array( 'bg_gradient_from', 'bg_gradient_to', 'news_card_border_color', 'monster_color', 'monster_accent' ),
				true
			);

		if ( $is_color ) {
			$color = sanitize_hex_color( $value );

			if ( $color ) {
				$result[ $key ] = $color;
			}

			continue;
		}

		if ( 0 === strpos( $key, 'social_' ) || false !== strpos( $key, '_url' ) || false !== strpos( $key, '_image' ) || 'monster_sound_file' === $key ) {
			$result[ $key ] = esc_url_raw( $value );
			continue;
		}

		if ( in_array( $key, array( 'footer_text', 'footer_copyright' ), true ) ) {
			$result[ $key ] = sdon_sanitize_html( $value );
			continue;
		}

		$result[ $key ] = sanitize_text_field( $value );
	}

	return $result;
}

/**
 * Сброс настроек темы к значениям по умолчанию.
 *
 * @return void
 */
function sdon_handle_reset() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав для сброса настроек.', 'sd-on-theme' ) );
	}

	check_admin_referer( 'sdon_reset_settings' );

	if ( empty( $_POST['sdon_reset_confirm'] ) ) {
		wp_safe_redirect( sdon_admin_redirect_url( 'import-error', 'tools' ) );
		exit;
	}

	$mods = get_theme_mods();

	if ( is_array( $mods ) ) {
		foreach ( array_keys( $mods ) as $key ) {
			// Меню и виджеты не относятся к настройкам оформления и сохраняются.
			if ( in_array( $key, array( 'nav_menu_locations', 'sidebars_widgets' ), true ) ) {
				continue;
			}

			remove_theme_mod( $key );
		}
	}

	wp_safe_redirect( sdon_admin_redirect_url( 'reset-done', 'tools' ) );
	exit;
}
add_action( 'admin_post_sdon_reset_settings', 'sdon_handle_reset' );
