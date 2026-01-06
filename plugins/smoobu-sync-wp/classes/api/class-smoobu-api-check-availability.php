<?php
/**
 * Check availability of property for the mentioned dates from the API
 *
 * @package smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No skiddies please!' );
}

/**
 * Availability API class
 */
class Smoobu_Api_Check_Availability extends Smoobu_Api {
	/**
	 * Properties API endpoint url
	 *
	 * @var string
	 */
	protected $endpoint = SMOOBU_API_AVAILABILITY_CHECK_ENDPOINT;

	/**
	 * Checks availability of the property in the specified interval.
	 *
	 * @param string $arrival_date    selected arrival date ( yyyy-mm-dd ).
	 * @param string $departure_date  selected departure date ( yyyy-mm-dd ).
	 * @param string $property_id     property id for which availability needs to be checked.
	 * @return boolean
	 */
	public function check_availability( $arrival_date, $departure_date, $property_id ) {

		$customer_details = json_decode( get_option( 'smoobu_user_detail' ), true );
		$data             = array(
			'arrivalDate'   => $arrival_date,
			'departureDate' => $departure_date,
			'apartments'    => array( intval( $property_id ) ),
			'customerId'    => intval( $customer_details['id'] ),
		);

		// get data from Smoobu API.
		$this->handle_data( 'POST', $data );

		if ( ! empty( $this->data ) ) {
			$properties = ( $this->data )['availableApartments'];

			return in_array( intval( $property_id ), $properties, true );
		}
		return false;
	}
}
