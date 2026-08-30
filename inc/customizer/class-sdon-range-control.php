<?php
/**
 * Контрол-ползунок с отображением текущего значения.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_Customize_Control' ) ) {
	return;
}

/**
 * Ползунок для числовых настроек темы.
 */
class SDON_Range_Control extends WP_Customize_Control {

	/**
	 * Тип контрола.
	 *
	 * @var string
	 */
	public $type = 'sdon-range';

	/**
	 * Единица измерения, показываемая рядом со значением.
	 *
	 * @var string
	 */
	public $unit = '';

	/**
	 * Разметка контрола.
	 *
	 * @return void
	 */
	public function render_content() {
		$input_id = '_customize-input-' . $this->id;
		?>
		<label class="sdon-range">
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php if ( $this->description ) : ?>
				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
			<?php endif; ?>
			<span class="sdon-range__row">
				<input
					type="range"
					id="<?php echo esc_attr( $input_id ); ?>"
					<?php $this->input_attrs(); ?>
					value="<?php echo esc_attr( $this->value() ); ?>"
					<?php $this->link(); ?>
					oninput="this.nextElementSibling.textContent=this.value"
				/>
				<output class="sdon-range__value"><?php echo esc_html( $this->value() ); ?></output>
				<?php if ( $this->unit ) : ?>
					<span class="sdon-range__unit"><?php echo esc_html( $this->unit ); ?></span>
				<?php endif; ?>
			</span>
		</label>
		<?php
	}
}
