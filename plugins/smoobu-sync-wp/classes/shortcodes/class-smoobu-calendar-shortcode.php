<?php
/**
 * Calendar shortcode
 *
 * @package smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No skiddies please!' );
}

/**
 * Show calendar shortcode
 */
class Smoobu_Calendar_Shortcode {
	/**
	 * Class instance
	 *
	 * @var Smoobu_Calendar_Shortcode
	 */
	protected static $instance = null;

	/**
	 * Shortcode params
	 *
	 * @var array
	 */
	private $params;

	/**
	 * Busy days to show in calendar
	 *
	 * @var array
	 */
	private $busy_days = array();

	/**
	 * Main instance
	 *
	 * @return Smoobu_Calendar_Shortcode
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_shortcode( 'smoobu_calendar', array( $this, 'show' ) );
	}

	/**
	 * Show calendar
	 *
	 * Calls function that handles attributes and set calendar variables. After that, loads calendar template
	 *
	 * @param array $atts shortcode attributes.
	 * @return string
	 */
	public function show( $atts ) {
		$this->handle_params( $atts );

		if ( ! empty( $this->params['property_id'] ) ) {
			$calendar = new Smoobu_Calendar( $this->params['property_id'], $this->params['layout'] );
			$calendar->run();

			// stream to output buffer in order to return it and not to print to screen immediately.
			ob_start();

			// load calendar template.
			Smoobu_Main::load_template(
				'calendar',
				array(
					'property_id' => $this->params['property_id'],
					'layout'      => $calendar->get_layout_json(),
					'link'        => esc_url_raw( $this->params['link'] ),
				)
			);

			return ob_get_clean();
		}

		return false;
	}

	/**
	 * Restructure shortcode attributes
	 *
	 * @param array $atts shortcode attributes.
	 * @return void
	 */
	private function handle_params( $atts ) {
		$this->params = shortcode_atts(
			array(
				'property_id' => null,
				'layout'      => false, // default value is set in Smoobu_Calendar->set_layout().
				'link'        => '#',
			),
			$atts
		);
	}
}
