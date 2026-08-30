<?php
/**
 * Виджет темы «Последние материалы» с миниатюрами.
 *
 * @package SD_ON_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Список последних записей с миниатюрой и датой.
 */
class SDON_Recent_Posts_Widget extends WP_Widget {

	/**
	 * Конструктор.
	 */
	public function __construct() {
		parent::__construct(
			'sdon_recent_posts',
			__( 'SD-ON: последние материалы', 'sd-on-theme' ),
			array(
				'description'                 => __( 'Последние записи с миниатюрами и датой публикации.', 'sd-on-theme' ),
				'customize_selective_refresh' => true,
			)
		);
	}

	/**
	 * Вывод виджета.
	 *
	 * @param array $args     Аргументы области виджетов.
	 * @param array $instance Настройки виджета.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		$title  = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Последние материалы', 'sd-on-theme' );
		$number = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : 5;

		$query = new WP_Query(
			array(
				'posts_per_page'      => $number,
				'post_status'         => 'publish',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);

		if ( ! $query->have_posts() ) {
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- разметка задаётся темой.
		echo $args['before_title'] . esc_html( apply_filters( 'widget_title', $title ) ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- разметка задаётся темой.

		echo '<ul class="sdon-recent-posts">';

		while ( $query->have_posts() ) {
			$query->the_post();
			?>
			<li class="sdon-recent-posts__item">
				<?php if ( has_post_thumbnail() ) : ?>
					<a class="sdon-recent-posts__image" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
						<?php the_post_thumbnail( 'thumbnail', array( 'loading' => 'lazy' ) ); ?>
					</a>
				<?php endif; ?>
				<div class="sdon-recent-posts__body">
					<a class="sdon-recent-posts__title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				</div>
			</li>
			<?php
		}

		echo '</ul>';

		wp_reset_postdata();

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- разметка задаётся темой.
	}

	/**
	 * Форма настроек виджета.
	 *
	 * @param array $instance Текущие настройки.
	 * @return void
	 */
	public function form( $instance ) {
		$title  = isset( $instance['title'] ) ? $instance['title'] : '';
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Заголовок:', 'sd-on-theme' ); ?></label>
			<input
				class="widefat"
				id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				type="text"
				value="<?php echo esc_attr( $title ); ?>"
			/>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Количество записей:', 'sd-on-theme' ); ?></label>
			<input
				class="tiny-text"
				id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>"
				type="number"
				min="1"
				max="20"
				value="<?php echo esc_attr( $number ); ?>"
			/>
		</p>
		<?php
	}

	/**
	 * Сохранение настроек.
	 *
	 * @param array $new_instance Новые значения.
	 * @param array $old_instance Прежние значения.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title'  => sanitize_text_field( $new_instance['title'] ?? '' ),
			'number' => min( 20, max( 1, absint( $new_instance['number'] ?? 5 ) ) ),
		);
	}
}
