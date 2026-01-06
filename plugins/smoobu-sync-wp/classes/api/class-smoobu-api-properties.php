<?php
/**
 * Update properties from the API
 *
 * @package smoobu-calendar
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No skiddies please!' );
}

/**
 * Properties API class
 */
class Smoobu_Api_Properties extends Smoobu_Api {
	/**
	 * Properties API endpoint url
	 *
	 * @var string
	 */
	protected $endpoint = SMOOBU_API_PROPERTIES_ENDPOINT;

	/**
	 * Fetch properties from the API
	 *
	 * @return void
	 */
	public function fetch_properties() {
		// get data from Smoobu API.
		$this->handle_data();

		if ( ! empty( $this->data ) ) {
			$properties = wp_json_encode( ( $this->data )['apartments'] );
			update_option( 'smoobu_properties_list', $properties );

			// Updates property details like add-ons and maxOccupancy.
			$this->update_property_details( ( $this->data )['apartments'] );
		}
	}

	/**
	 * Updates property details like add-ons and maxOccupancy.
	 *
	 * @param array $properties Data about the listed properties.
	 * @return void
	 */
	public function update_property_details( $properties ) {

		// Update the table.
		global $wpdb;
		$table_name = $wpdb->prefix . 'smoobu_property_details';
		// phpcs:ignore
		$delete = $wpdb->query(
			$wpdb->prepare(
				"TRUNCATE TABLE %i", 		// phpcs:ignore
				$table_name
			)
		); // delete all records.
		foreach ( $properties as $property ) {
			$this->endpoint .= '/' . $property['id'];
			$this->handle_data();
			if ( ! empty( $this->data ) ) {
				$property_data = $this->data;
				$max_occupancy = $property_data['rooms']['maxOccupancy'];
			}
			$this->endpoint = SMOOBU_API_ADDON_ENDPOINT . '/' . $property['id'];
			$this->handle_data();
			if ( ! empty( $this->data ) ) {
				$addon_data = wp_json_encode( $this->data );
			}

			// Reverts back the endpoint for any next requests.
			$this->endpoint = SMOOBU_API_PROPERTIES_ENDPOINT;
			// phpcs:ignore
			$wpdb->insert(
				$table_name,
				array(
					'property_id' => $property['id'],
					'max_guests'  => $max_occupancy,
					'add_ons'     => $addon_data,
				)
			);
		}

	}
}
