<?php
/**
 * Smoobu API Booking
 *
 * @package smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( 'No skiddies please!' );
}

/**
 * Handles bookings through the Smoobu API.
 */
class Smoobu_Api_Booking {

    /**
     * Smoobu API key.
     *
     * @var string
     */
    protected $api_key = '';

    /**
     * Raw response from the API.
     *
     * @var array
     */
    protected $response = array();

    /**
     * Error message.
     *
     * @var string
     */
    protected $error = '';

    /**
     * Processed API data.
     *
     * @var array
     */
    protected $data = array();

    /**
     * API endpoint URL.
     *
     * @var string
     */
    protected $endpoint = SMOOBU_API_BOOKINGS_ENDPOINT;

    /**
     * Class instance.
     *
     * @var Smoobu_Api_Booking
     */
    protected static $instance = null;

    /**
     * Returns the main instance of the class.
     *
     * @return Smoobu_Api_Booking
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        $this->api_key = get_option( 'smoobu_api_key' );
    }

    /**
     * Makes a booking through the Smoobu API.
     *
     * @param array $data Details of the customer.
     * @return array Response data or error message.
     */
    public static function make_booking( $data ) {
        return self::instance()->handle_data( 'POST', $data );
    }

    /**
     * Cancels a booking through the Smoobu API.
     *
     * @param string $booking_id Reservation / Booking ID.
     * @return array Response data or error message.
     */
    public static function cancel_booking( $booking_id ) {
        return self::instance()->handle_data( 'DELETE', array( 'booking_id' => $booking_id ) );
    }

    /**
     * Handles the API request based on the method and data provided.
     *
     * @param string $method HTTP method to be used for the API call.
     * @param array  $data   Data to use for the API call.
     * @return array Response data or error message.
     */
    public function handle_data( $method, $data ) {
        switch ( $method ) {
            case 'POST':
                $this->post_request( $data );
                break;
            case 'DELETE':
                if ( isset( $data['booking_id'] ) ) {
                    $this->delete_request( $data['booking_id'] );
                }
                break;
            case 'GET':
            default:
                $this->get_response();
                break;
        }

        $this->process_response();
        return ! empty( $this->error ) ? array( 'error' => $this->error ) : $this->data;
    }

    /**
     * Makes a POST request to the API endpoint.
     *
     * @param array $data Data to use for the POST request.
     * @return void
     */
    private function post_request( $data ) {
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
                'body'        => wp_json_encode( $data ),
            )
        );
    }

    /**
     * Makes a DELETE request to cancel a booking.
     *
     * @param string $booking_id Booking ID to cancel.
     * @return void
     */
    private function delete_request( $booking_id ) {
        $endpoint = $this->endpoint . '/' . $booking_id;
        $this->response = wp_remote_request(
            $endpoint,
            array(
                'method'      => 'DELETE',
                'timeout'     => 45,
                'redirection' => 5,
                'headers'     => array(
                    'Content-Type'  => 'application/json; charset=utf-8',
                    'Cache-Control' => 'no-cache',
                    'Api-Key'       => $this->api_key,
                )
            )
        );
    }

    /**
     * Gets the raw response from the API.
     *
     * @return void
     */
    private function get_response() {
        $this->response = wp_safe_remote_get(
            $this->endpoint,
            array(
                'timeout'     => 45,
                'redirection' => 5,
                'headers'     => array(
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Api-Key'      => $this->api_key,
                ),
            )
        );
    }

    /**
     * Processes the API response and sets the error or data properties.
     *
     * @return void
     */
    private function process_response() {
        if ( is_wp_error( $this->response ) ) {
            $this->error = $this->response->get_error_message();

            if ( strpos( $this->error, 'cURL error' ) === 0 ) {
                $this->error = __( 'Sorry, but we have some technical issues. Please try again in a few minutes.', 'smoobu-calendar' );
            }
        } else {

            if (is_array($this->response)) {
                if (empty($this->response['body'])) {
                    $this->error = __('No results found', 'smoobu-calendar');
                } elseif (200 === $this->response['response']['code'] || 201 === $this->response['response']['code']) {
                    $this->data = json_decode($this->response['body'], true);
                } elseif (200 !== $this->response['response']['code']) {
                    $response_body = json_decode($this->response['body'], true);

                    if (isset($response_body['validation_messages'])) {
                        $validation_messages = '';
                        
                        if (isset($response_body['validation_messages']['error'])) {
                            $validation_messages .= $response_body['validation_messages']['error'] . '<br>';
                        } else {
                            $validation_messages .= __('Unknown error', 'smoobu-calendar') . '<br>';
                        }
                    
                        $this->error = sprintf(
                            __('API provider returned error message: %s. Validation errors: %s', 'smoobu-calendar'),
                            $this->response['response']['message'],
                            $validation_messages
                        );
                    } else {
                        $this->error = sprintf(
                            __('API provider returned error message: %s', 'smoobu-calendar'),
                            $this->response['response']['message']
                        );
                    }
            
                    error_log("Smoobu_Api_Response_detail");
                    error_log(print_r($response_body, true));
                    
                } else {
                    $this->data = json_decode($this->response['body'], true);
                }
            } else {
                $this->error = __('Unexpected error happened', 'smoobu-calendar');
            }
            
        }
    }

    /**
     * Returns the error message if any.
     *
     * @return string|false
     */
    public function get_error() {
        return ! empty( $this->error ) ? $this->error : false;
    }
}
