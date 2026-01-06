<?php
/**
 * Update availability from the API
 *
 * @package smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No skiddies please!' );
}

/**
 * Availability API class
 */
class Smoobu_Api_Availability extends Smoobu_Api {
	/**
	 * Properties API endpoint url
	 *
	 * @var string
	 */
	protected $endpoint = SMOOBU_API_AVAILABILITY_ENDPOINT;


	/**
	 * Construct, we set request params here
	 *
	 * @param string $start_date     date from which the availability neds to be checked.
	 * @param string $end_date       date to which the availability needs to be checked.
	 * @param array  $apartments_ids the id of the apartment for which it has to be checked.
	 */
	public function __construct( $start_date = '', $end_date = '', $apartments_ids = array() ) {
		$this->params = array(
			'start_date' => ( empty( $start_date ) ? $this->get_start_date() : $start_date ),
			'end_date'   => ( empty( $end_date ) ? $this->get_end_date() : $end_date ),
			'apartments' => ( empty( $apartments_ids ) ? $this->get_apartments_ids() : $apartments_ids ),
		);

		parent::__construct();
	}

	/**
	 * Get availability checking start date
	 *
	 * @return string
	 */
	private function get_start_date() {
		$start_date = gmdate( 'Y-m-01' );
		$start_date = apply_filters( 'smoobu_availability_start_date', $start_date );

		return $start_date;
	}

	/**
	 * Get availability checking end date
	 *
	 * @return string
	 */
	private function get_end_date() {
		$end_date = gmdate( 'Y-m-t', strtotime( '+2 year' ) );
		$end_date = apply_filters( 'smoobu_availability_end_date', $end_date );

		return $end_date;
	}

	/**
	 * Set apartments IDs array for params
	 *
	 * @return array
	 */
	private function get_apartments_ids() {
		$apartments_ids = array();

		$apartments = Smoobu_Utility::get_available_properties();

		if ( ! empty( $apartments ) ) {
			foreach ( $apartments as $apartment ) {
				$apartments_ids[] = $apartment->id;
			}
		}

		return $apartments_ids;
	}


	/**
	 * Fetch properties from the API
	 *
	 * @return void
	 */
	public function fetch_availability() {
		global $wpdb;

		// get data from Smoobu API.
		$this->handle_data();

		if ( ! empty( $this->data ) ) {
			$availabilities = $this->data['data'];

			if ( ! empty( $availabilities ) ) {

				foreach ( $availabilities as $property_id => $availability ) {

					$busy_days = array();
					$open_days = array();
					// insert all busy dates.
					foreach ( $availability as $date => $attributes ) {
						// insert only if the date is already taken.
						if ( !isset( $attributes['available'], $attributes['price'] ) || $attributes['price'] == '' || $attributes['available'] == '0' ) {
							// @TODO - depending on testing results, might need to remake to multiple insert with one query.
							$busy_days[] = $date;

						} else {
							$open_days[ $date ] = $attributes;
						}
					}
					// Inserts/Replaces the rates and busy dates data.
					// phpcs:ignore
					asort( $busy_days, 2 );
					ksort( $open_days, 2 );
					$wpdb->replace(
						$wpdb->prefix . 'smoobu_calendar_availability',
						array(
							'property_id' => $property_id,
							'busy_dates'  => wp_json_encode( $busy_days ),
							'open_dates'  => wp_json_encode( $open_days ),
						)
					);
				}

			}
		}
	}


	/**
	 * Gets the data from rates endpoint.
	 *
	 * @return array|boolean
	 */
	public function fetch_prices() {
		$this->handle_data();
		if ( ! empty( $this->data ) ) {
			return $this->data['data'];
		}
		return false;
	}

}
