<?php
/**
 * Webhook API Endpoints & retrieving the data
 *
 * @package smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No skiddies please!' );
}

/**
 * Plugin Endpoints Class
 */
class Smoobu_Webhook {
	/**
	 * Class instance
	 *
	 * @var Smoobu_Webhook
	 */
	protected static $instance = null;

	/**
	 * Main instance
	 *
	 * @return Smoobu_Webhook
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
		add_action( 'rest_api_init', array( $this, 'routes' ) );
	}

	/**
	 * Register API endpoints routes
	 *
	 * @return void
	 */
	public function routes() {
		// all brokers data.
		$result = register_rest_route(
			'smoobu-calendar/v1',
			'/update',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'fetch_availability' ),
				'permission_callback' => '__return_true',
			)
		);
	}


	/**
	 * Webhook used to update proeprty availability after booking in Smoobu platform
	 *
	 * @param  WP_REST_Request $request request data.
	 * @return string|WP_Error|WP_REST_Response
	 */
	public function fetch_availability( WP_REST_Request $request ) {
		global $wpdb;

		Smoobu_Utility::write_log( 'Inside Webhook' );

		$request_body = json_decode( $request->get_body(), true );

		if (
			! empty( $request_body['action'] ) &&
			strcmp( $request_body['action'], 'updateRates' ) === 0 &&
			! empty( $request_body['data'] )
		) {
			Smoobu_Utility::write_log( 'update called' );
			Smoobu_Utility::write_log( $request_body['data'] );
			$result = Smoobu_Utility::update_data( $request_body['data'] );
			return rest_ensure_response( $result['message'] );
		} else {
			return rest_ensure_response(
				__( 'Incorrect Data.', 'smoobu-calendar' )
			);
		}

		return 'OK';
	}
}
