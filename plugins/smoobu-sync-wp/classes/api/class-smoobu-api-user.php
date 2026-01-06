<?php
/**
 * Update User from the API
 *
 * @package smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No skiddies please!' );
}

/**
 * Users API class
 */
class Smoobu_Api_User extends Smoobu_Api {
	/**
	 * Users API endpoint url
	 *
	 * @var string
	 */
	protected $endpoint = SMOOBU_API_USER_ENDPOINT;

	/**
	 * Fetch users from the API
	 *
	 * @return void
	 */
	public function fetch_user() {
		// get data from Smoobu API.
		$this->handle_data();

		if ( ! empty( $this->data ) ) {
			$properties = wp_json_encode( ( $this->data ) );
			update_option( 'smoobu_user_detail', $properties );
		}
	}
}
