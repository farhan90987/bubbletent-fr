<?php

namespace MarketPress\GermanMarket\Shipping\Provider\DHL;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Api_Error {

	/**
	 * Constructor.
	 */
	public function __construct() {}

	/**
	 * Get response error messages.
	 *
	 * @param array|string $response API response object
	 *
	 * @return string.
	 */
	public function get_response_error_message( $response ) : string {

		$multiple_errors_list = array();

		if ( ! is_array( $response[ 'items' ] ) ) {
			if ( ! empty( $response[ 'status' ][ 'detail' ] ) ) {
				return $response[ 'status' ][ 'detail' ];
			}
			else if ( isset( $response[ 'statusCode' ] ) && ( 401 === $response[ 'statusCode' ] ) ) {
				return __( 'Not Authorized', 'woocommerce-german-market' );
			} else {
				return $response;
			}
		}

		foreach ( $response[ 'items' ] as $item ) {

			$errors_list = $this->get_item_error_message( $item );

			foreach ( $errors_list as $key => $list ) {
				if ( ! isset( $multiple_errors_list[ $key ] ) || ! is_array( $multiple_errors_list[ $key ] ) ) {
					$multiple_errors_list[ $key ] = array();
				}

				if ( is_array( $list ) ) {
					$multiple_errors_list[ $key ] += $list;
				} else {
					$multiple_errors_list[ $key ][] = $list;
				}
			}
		}

		ksort( $multiple_errors_list );

		return $this->generate_error_message( $multiple_errors_list );
	}

	/**
	 * Get item errors.
	 *
	 * @param array $item .
	 *
	 * @return array
	 */
	protected function get_item_error_message( array $item ) : array {

		if ( isset( $item[ 'message' ] ) ) {
			return array(
				'Error' => $item[ 'message' ],
			);
		}

		$multiple_errors_list          = array();
		$multiple_errors_property_list = array();

		foreach ( $item[ 'validationMessages' ] as $message ) {

			if ( empty( $multiple_errors_list[ $message[ 'validationState'  ] ] ) ) {
				$multiple_errors_list[ $message[ 'validationState'  ] ] = array();
			}

			// Check if got a property from API result, in some cases we don't.

			if ( ! empty( $message[ 'property' ] ) ) {
				$property     = '( ' . $message[ 'property' ] . ' ) : ';
				$property_key = sanitize_key( $message[ 'property' ] );
			} else {
				$property    = '( DHL ) : ';
				$property_key = sanitize_key( 'DHL' );
			}

			if ( empty( $multiple_errors_property_list[ $property_key ] ) || ! in_array( $message[ 'validationMessage' ], $multiple_errors_property_list[ $property_key ] ) ) {

				if ( array_key_exists( 'property', $message ) && ( 'consignee.name1' === $message[ 'property' ] ) ) {
					$property                       = '';
					$message[ 'validationMessage' ] = __( 'DHL accepts a maximum of 24 characters for the recipient name. This applies to the first and last name fields. Please shorten the length of the entries.', 'woocommerce-german-market' );
				}

				$multiple_errors_property_list[ $property_key ][]         = $message[ 'validationMessage' ];
				$multiple_errors_list[ $message[ 'validationState'  ] ][] = $property . $message[ 'validationMessage' ];
			}
		}

		return $multiple_errors_list;
	}

	/**
	 * Returns a list of given errors and warnings.
	 *
	 * @param array $multiple_errors_list
	 *
	 * @return string
	 */
	protected function generate_error_message( array $multiple_errors_list ) : string {

		$error_message = '';

		if ( ! empty( $multiple_errors_list ) ) {
			foreach ( $multiple_errors_list as $errors ) {
				$error_message .= '<ul class="wgm-dhl-error">';
				foreach ( $errors as $error ) {
					$error_message .= '<li>' . $error . '</li>';
				}
				$error_message .= '</ul>';
			}
		}

		return $error_message;
	}

}
