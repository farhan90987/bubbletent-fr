<?php
/**
 * Calendar block
 *
 * @package smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No skiddies please!' );
}

/**
 * Calendar block class
 */
final class Smoobu_Blocks {
	/**
	 * Class instance
	 *
	 * @var Smoobu_Blocks
	 */
	protected static $instance = null;

	/**
	 * Main instance
	 *
	 * @return Smoobu_Blocks
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor, loads main actions, methods etc.
	 */
	public function __construct() {
		// register blocks.
		add_action( 'init', array( $this, 'calendar' ) );
	}

	/**
	 * Main function where block and related JS is being registered
	 *
	 * @return void
	 */
	public function calendar() {
		if ( ! function_exists( 'register_block_type' ) ) {
			// Gutenberg is not active.
			return;
		}

		// Include the generated asset file.
		$calendar_asset_file = include SMOOBU_PATH . 'assets/js/blocks/calendar.min.asset.php';

		wp_register_script(
			'smoobu-calendar-block',
			SMOOBU_URI . 'assets/js/blocks/calendar.min.js',
			$calendar_asset_file['dependencies'],
			$calendar_asset_file['version'],
			true
		);

		register_block_type(
			'smoobu-calendar/calendar',
			array(
				'editor_script'   => 'smoobu-calendar-block',
				'render_callback' => array( 'Smoobu_Blocks', 'calendar_render_callback' ),
			)
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'smoobu-calendar-block', 'smoobu-calendar' );
		}
	}

	/**
	 * Calendar block, visible in the frontend, rendering
	 *
	 * @param array $attributes block attributes.
	 * @return string
	 */
	public static function calendar_render_callback( $attributes ) {
		if ( ! empty( $attributes['property_id'] ) ) {
			$layout   = isset( $attributes['layout'] ) ? $attributes['layout'] : '1 x 2';
			$calendar = new Smoobu_Calendar( $attributes['property_id'], $layout );
			$calendar->run();

			// stream to output buffer in order to return it and not to print to screen immediately.
			ob_start();

			// load calendar template.
			Smoobu_Main::load_template(
				'calendar',
				array(
					'property_id' => $attributes['property_id'],
					'layout'      => $calendar->get_layout_json(),
					'link'        => $attributes['link'],
				)
			);

			return ob_get_clean();
		}

		return false;
	}
}
