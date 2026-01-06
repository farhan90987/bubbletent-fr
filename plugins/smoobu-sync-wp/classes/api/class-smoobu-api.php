<?php
/**
 * API class
 *
 * @package smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No skiddies please!' );
}

/**
 * Plugin Api Class
 */
class Smoobu_Api {
	/**
	 * Smoobu API key
	 *
	 * @var string
	 */
	protected $api_key = '';

	/**
	 * Parameters passed to API
	 *
	 * @var array
	 */
	protected $params = array();

	/**
	 * Raw response from the API
	 *
	 * @var array
	 */
	protected $response = array();

	/**
	 * Error (if wrong API response)
	 *
	 * @var string
	 */
	protected $error = '';

	/**
	 * Processed API data
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Stored API endpoint.
	 *
	 * @var string
	 */
	protected $endpoint = '';

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->api_key = get_option( 'smoobu_api_key' );

		// if params are set, join them to the endpoint URI.
		if ( ! empty( $this->params ) ) {
			$this->endpoint = $this->endpoint . '?' . http_build_query( $this->params );
		}
	}

	/**
	 * Function to check API connection
	 *
	 * @param string $api_key api key.
	 * @param string $endpoint endpoint link.
	 * @return string
	 */
	public function get_api_check( $api_key = '', $endpoint = '' ) {
		if ( ! empty( $api_key ) ) {
			$this->api_key = $api_key;
		}

		if ( ! empty( $endpoint ) ) {
			$this->endpoint = $endpoint;
		}

		// get data from Smoobu API.
		$this->handle_data();

		return $this->get_error();
	}

	/**
	 * Handles data returned from API
	 *
	 * @param string $method method to be used to make an API call.
	 * @param array  $data   data to use for the post method call.
	 * @return void
	 */
	public function handle_data( $method = '', $data = array() ) {
		// call the API and catch response.
		switch ( $method ) {
			case 'POST':
				$this->request( $data );
				break;
			case 'GET':
			default:
				$this->response();
				break;
		}

		// handle API data.
		if ( is_wp_error( $this->response ) ) {
			// if error.
			$this->error = $this->response->get_error_message();

			if ( 0 === strpos( $this->error, 'cURL error' ) ) {
				$this->error = __( 'Sorry, but we have some technical issues. Please try again in a few minutes.', 'smoobu-calendar' );
			}
		} else {
			// if API call was successful.
			if ( is_array( $this->response ) ) {
				if ( empty( $this->response['body'] ) ) {
					$this->error = __( 'No results found', 'smoobu-calendar' );
				} elseif ( 200 !== $this->response['response']['code'] ) {
					// translators: API response error message.
					$this->error = sprintf( __( 'API provider returned error message: %s', 'smoobu-calendar' ), $this->response['response']['message'] );
				} else {
					$this->data = json_decode( $this->response['body'], true );
				}
			} else {
				$this->error = __( 'Unexpected error happened', 'smoobu-calendar' );
			}
		}
	}

	/**
	 * Get raw response from the API and assign it to $response variable
	 *
	 * @return void
	 */
	private function response() {
		$this->response = wp_safe_remote_get(
			$this->endpoint,
			array(
				'timeout'     => 45,
				'redirection' => 5,
				'headers'     => array(
					'Content-Type' => 'application/json; charset=utf-8',
					'Api-Key'      => $this->api_key,
				),
				'cookies'     => array(),
			)
		);
	}


	/**
	 * Make a post request to an API endpoint and store returned
	 * result in $response.
	 *
	 * @param array $data data to use for the post method call.
	 * @return void
	 */
	private function request( $data ) {
		$this->response = wp_remote_post(
			$this->endpoint,
			array(
				'timeout'     => 45,
				'redirection' => 5,
				'headers'     => array(
					'Content-Type'  => 'application/json; charset=utf-8',
					'Cache-Control' => 'no-cache',
					'Api-Key'       => $this->api_key,
				),
				'cookies'     => array(),
				'body'        => wp_json_encode( $data ),
			)
		);
	}

	/**
	 * Return processed API error
	 *
	 * @return string
	 */
	public function get_error() {
		if ( ! empty( $this->error ) ) {
			return $this->error;
		} else {
			return false;
		}
	}
}
