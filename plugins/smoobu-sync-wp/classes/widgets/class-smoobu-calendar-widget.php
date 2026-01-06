<?php
/**
 * Calendar widget
 *
 * @package smoobu-calendar
 */

/**
 * Calendar widget
 */
class Smoobu_Calendar_Widget extends WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		// initiate.
		parent::__construct( 'smoobu_calendar_widget', 'Smoobu Calendar Widget' );
	}

	/**
	 * Widget view
	 *
	 * @param array $args     args.
	 * @param array $instance instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		// get data.
		// if title not set.
		if ( empty( $instance['title'] ) ) {
			$instance['title'] = '';
		}

		$title       = apply_filters( 'widget_title', $instance['title'] );
		$property_id = $instance['property_id'];
		$layout      = $instance['layout'];
		$link        = $instance['link'];

		$calendar = new Smoobu_Calendar( $property_id, $layout );
		$calendar->run();

		// start widget output.
		echo wp_kses_post( $args['before_widget'] );

		if ( ! empty( $title ) ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}

		// load calendar template.
		Smoobu_Main::load_template(
			'calendar',
			array(
				'property_id' => $property_id,
				'layout'      => $calendar->get_layout_json(),
				'link'        => esc_url_raw( $link ),
			)
		);

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Form to set widget variables
	 *
	 * @param array $instance various widget values.
	 * @return void
	 */
	public function form( $instance ) {
		$title       = '';
		$property_id = '';

		if ( ! empty( $instance['title'] ) ) {
			$title = $instance['title'];
		}

		if ( ! empty( $instance['property_id'] ) ) {
			$property_id = $instance['property_id'];
		}

		if ( ! empty( $instance['layout'] ) ) {
			$layout = $instance['layout'];
		} else {
			// change default layout for widget to 1x1 as 1x3 often breaks sidebars.
			$layout = '1x1';
		}

		if ( ! empty( $instance['link'] ) ) {
			$link = $instance['link'];
		}

		$title_field_id       = $this->get_field_id( 'title' );
		$property_id_field_id = $this->get_field_id( 'property_id' );
		$layout_field_id      = $this->get_field_id( 'layout' );
		$link_id              = $this->get_field_id( 'link' );
		?>
		<p>
			<label for="<?php echo esc_attr( $title_field_id ); ?>"><?php esc_html_e( 'Title', 'smoobu-calendar' ); ?>: </label>
			<input class="widefat" id="<?php echo esc_attr( $title_field_id ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $property_id_field_id ); ?>"><?php esc_html_e( 'Property', 'smoobu-calendar' ); ?>: </label>
			<select class="widefat" id="<?php echo esc_attr( $property_id_field_id ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'property_id' ) ); ?>">
				<?php Smoobu_Utility::available_properties_options( $property_id ); ?>
			</select>
		</p>
		<p>
		<label for="<?php echo esc_attr( $layout_field_id ); ?>"><?php esc_html_e( 'Layout', 'smoobu-calendar' ); ?>: </label>
			<select class="widefat" id="<?php echo esc_attr( $layout_field_id ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'layout' ) ); ?>">
				<?php Smoobu_Utility::available_layouts_options( $layout ); ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $link_id ); ?>"><?php esc_html_e( 'Redirect Link', 'smoobu-calendar' ); ?>: </label>
			<input
				class="widefat"
				id="<?php echo esc_attr( $link_id ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'link' ) ); ?>"
				type="text"
				value="<?php echo esc_attr( $link ); ?>"
			/>
		</p>
		<?php
	}

	/**
	 * Update widget.
	 *
	 * @param array $new_instance new values array.
	 * @param array $old_instance old values array.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		if ( ! empty( $new_instance['title'] ) ) {
			$instance['title'] = $new_instance['title'];
		}

		if ( ! empty( $new_instance['property_id'] ) ) {
			$instance['property_id'] = $new_instance['property_id'];
		}

		if ( ! empty( $new_instance['layout'] ) ) {
			$instance['layout'] = $new_instance['layout'];
		}

		if ( ! empty( $new_instance['link'] ) ) {
			$instance['link'] = $new_instance['link'];
		}

		return $instance;
	}
}
