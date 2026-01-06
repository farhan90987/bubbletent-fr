<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class responsible for defining REST API routes and their callback logic.
 */
class WPWOOF_REST {

	/**
	 * Constructor registers REST routes.
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register all REST routes in this method.
	 */
	public function register_routes() {
		register_rest_route(
			'wpwoof/v1',
			'/feeds_update',
			[
				'methods'  => 'GET',
				'callback' => [ $this, 'handle_feeds_update' ],
			]
		);

		// You can add more routes here in the future if needed
	}

	/**
	 * Handle feeds update logic for the /feeds_update route.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return array
	 */
	public function handle_feeds_update( \WP_REST_Request $request ): array {
		global $woocommerce_wpwoof_common;

		// Execute the feeds update logic
		$woocommerce_wpwoof_common->run_scheduled_feeds();

		// Return a JSON response
		return [
			'success' => true,
			'message' => 'Feeds updated successfully!',
		];
	}
}