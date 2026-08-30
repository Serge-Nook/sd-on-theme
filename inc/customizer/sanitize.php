<?php
/**
 * Функции санитаризации настроек темы.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Чекбокс.
 *
 * @param mixed $value Значение.
 * @return bool
 */
function sdon_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * Значение из списка допустимых вариантов контрола.
 *
 * @param string               $value   Значение.
 * @param WP_Customize_Setting $setting Настройка.
 * @return string
 */
function sdon_sanitize_select( $value, $setting ) {
	$value   = sanitize_key( str_replace( array( ' ' ), '', (string) $value ) );
	$control = $setting->manager->get_control( $setting->id );

	if ( $control && isset( $control->choices[ $value ] ) ) {
		return $value;
	}

	return (string) $setting->default;
}

/**
 * Значение из списка вариантов, допускающее произвольные символы (например «180deg»).
 *
 * @param string               $value   Значение.
 * @param WP_Customize_Setting $setting Настройка.
 * @return string
 */
function sdon_sanitize_choice( $value, $setting ) {
	$control = $setting->manager->get_control( $setting->id );

	if ( $control && isset( $control->choices[ $value ] ) ) {
		return $value;
	}

	return (string) $setting->default;
}

/**
 * Целое число в границах, заданных полем input_attrs.
 *
 * @param mixed                $value   Значение.
 * @param WP_Customize_Setting $setting Настройка.
 * @return int
 */
function sdon_sanitize_number( $value, $setting ) {
	$value   = (int) $value;
	$control = $setting->manager->get_control( $setting->id );

	if ( $control && isset( $control->input_attrs['min'] ) ) {
		$value = max( (int) $control->input_attrs['min'], $value );
	}

	if ( $control && isset( $control->input_attrs['max'] ) ) {
		$value = min( (int) $control->input_attrs['max'], $value );
	}

	return $value;
}

/**
 * Дробное число (например, межстрочный интервал).
 *
 * @param mixed $value Значение.
 * @return float
 */
function sdon_sanitize_float( $value ) {
	$value = (float) str_replace( ',', '.', (string) $value );

	return min( 2.4, max( 1.0, $value ) );
}

/**
 * HTML-текст с ограниченным набором тегов.
 *
 * @param string $value Значение.
 * @return string
 */
function sdon_sanitize_html( $value ) {
	return wp_kses( (string) $value, sdon_allowed_html() );
}

/**
 * Многострочный текст без HTML.
 *
 * @param string $value Значение.
 * @return string
 */
function sdon_sanitize_textarea( $value ) {
	return sanitize_textarea_field( (string) $value );
}

/**
 * Значение настройки шрифта: встроенный набор или загруженный шрифт.
 *
 * @param string               $value   Значение.
 * @param WP_Customize_Setting $setting Настройка.
 * @return string
 */
function sdon_sanitize_font( $value, $setting ) {
	$choices = sdon_font_choices();

	return isset( $choices[ $value ] ) ? $value : (string) $setting->default;
}
