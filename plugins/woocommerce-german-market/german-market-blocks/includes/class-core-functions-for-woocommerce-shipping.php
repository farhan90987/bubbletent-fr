<?php

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\StoreApi\Exceptions\InvalidCartException;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Home_Delivery;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Packstation;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Parcels;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Shipping_Provider;
use MarketPress\GermanMarket\Shipping\Woocommerce_Shipping;

/**
 * In this class we have imported some functions from the shipping add-ons
 * or we call them in a wrapper method 
 * or we have slightly modified these methods 
 * to use them in the the cart and checkout block
 */
class German_Market_Blocks_Core_Functions_For_Woocommerce_Shipping extends German_Market_Blocks_Methods {

	/* @var string */
	const IDENTIFIER_SHIPPING  = 'german-market-shipping-store-api-integration';

    /**
     * Call actions and filters
     *
     * @return void
     */
    public function init() {

	    // Validation.

	    add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( __CLASS__, 'order_validation' ), 10, 2 );
	    add_action( 'woocommerce_store_api_checkout_order_processed',           array( __CLASS__, 'order_processed' ), 99 );
    }

	/**
	 * Returns if date of birth is required using the GM Age Rating method.
	 *
	 * @return bool
	 */
	public static function is_date_of_birth_required() : bool {

		$cart                   = ( isset( WC()->cart ) && method_exists( WC()->cart, 'get_cart' ) ) ?? false;
		$date_of_birth_required = false;
		$chosen_shipping_method = ( isset( WC()->session ) && method_exists( WC()->session, 'get' ) && WC()->session->get( 'chosen_shipping_methods' ) !== null ) ? strtolower( WC()->session->get( 'chosen_shipping_methods' )[ 0 ] ) : '';

		if ( $cart && ( '' !== $chosen_shipping_method ) && ( false !== strpos( $chosen_shipping_method, Parcels::get_instance()->id ) || false !== strpos( $chosen_shipping_method, Home_Delivery::get_instance()->id ) ) ) {

			$ar_instance = WGM_Age_Rating::get_instance();
			$age_rating  = $ar_instance::get_age_rating_of_cart_or_order();
			$default     = Shipping_Provider::$options->get_option( 'service_ident_check_default', 0 );

			if (
				( 'on' === get_option( 'german_market_age_rating', 'off' ) && ( 0 !== $age_rating ) && ( 0 != $default ) ) ||
				( 'off' === get_option( 'german_market_age_rating', 'off' ) && ( 0 != $default ) )
			) {
				$date_of_birth_required = true;
			}
		}

		return $date_of_birth_required;
	}

	/**
	 * Returns if a DHL client number is required.
	 *
	 * @return bool
	 */
	public static function is_client_number_required() : bool {

		if ( ! isset( WC()->session ) || ! method_exists( WC()->session, 'get' ) || WC()->session->get( 'chosen_shipping_methods' ) === null ) {
			return false;
		}

		$client_number_required = false;
		$chosen_shipping_method = strtolower( WC()->session->get( 'chosen_shipping_methods' )[ 0 ] );

		if ( ( '' !== $chosen_shipping_method ) && class_exists( 'MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Parcels' ) ) {
			if ( preg_match( '/' . Parcels::get_instance()->id . '/i', $chosen_shipping_method ) ) {
				$client_number_required = true;
			}
		}
		if ( ( '' !== $chosen_shipping_method ) && class_exists( 'MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Packstation' ) ) {
			if ( preg_match( '/' . Packstation::get_instance()->id . '/i', $chosen_shipping_method ) ) {
				$client_number_required = true;
			}
		}

		return $client_number_required;
	}

	/**
	 * Checkout order validation.
	 *
	 * @Hook woocommerce_store_api_checkout_update_order_from_request
	 *
	 * @param WC_Order        $order order object
	 * @param WP_REST_Request $request API request object
	 *
	 * @return void
	 * @throws InvalidCartException
	 * @throws Exception
	 */
	public static function order_validation( WC_Order $order, WP_REST_Request $request ) {

		/**
		 * @param WC_Order        $order
		 * @param WP_REST_Request $request
		 */
		do_action( 'wgm_blocks_shipping_addon_before_order_validation', $order, $request );

		$data = $request[ 'extensions' ];

		if ( ! empty( $data[ self::IDENTIFIER_SHIPPING ] ) ) {

			// Delivery Day Validation.

			if ( isset( $data[ self::IDENTIFIER_SHIPPING ][ 'delivery_day' ] ) && ! empty( $data[ self::IDENTIFIER_SHIPPING ][ 'delivery_day' ] ) ) {
				$delivery_day            = date_create( $data[ self::IDENTIFIER_SHIPPING ][ 'delivery_day' ] );
				$delivery_day_min_object = Shipping_Provider::calculate_first_preferred_delivery_day( true );
				$delivery_day_max_object = clone $delivery_day_min_object;
				$delivery_day_max_object->add( new DateInterval( 'P6D' ) );
				if ( 0 == date_format( $delivery_day, 'w' ) ) {
					$errors = new WP_Error();
					$code   = 'german-market-shipping-delivery-day';

					$errors->add( $code, __( 'The Delivery Day you entered is not valid or allowed.' ), 'woocommerce-german-market' );

					throw new InvalidCartException(
						'woocommerce_woocommerce_german_market_shipping_error',
						$errors,
						409
					);
				} else {
					if ( ( $delivery_day < $delivery_day_min_object ) || ( $delivery_day > $delivery_day_max_object ) ) {
						$errors = new WP_Error();
						$code   = 'german-market-shipping-delivery-day';

						$errors->add( $code, __( 'The Delivery Day you entered is not valid or allowed.' ), 'woocommerce-german-market' );

						throw new InvalidCartException(
							'woocommerce_woocommerce_german_market_shipping_error',
							$errors,
							409
						);
					}
				}
			}

			// Date of Birth Validation.

			if ( self::is_date_of_birth_required() ) {

				if ( ! isset( $data[ self::IDENTIFIER_SHIPPING ][ 'date_of_birth' ] ) ) {
					$errors = new WP_Error();
					$code   = 'german-market-shipping-date-of-birth';

					$errors->add( $code, sprintf( __( 'Please enter your %s!', 'woocommerce-german-market' ), __( 'Date of Birth', 'woocommerce-german-market' ) ) );

					throw new InvalidCartException(
						'woocommerce_woocommerce_german_market_shipping_error',
						$errors,
						409
					);
				} else {
					$date_array = explode( '-', $data[ self::IDENTIFIER_SHIPPING ][ 'date_of_birth' ] );
					if ( is_array( $date_array ) && count( $date_array ) === 3 ) {
						if ( checkdate( $date_array[ 1 ], $date_array[ 2 ], $date_array[ 0 ] ) ) {
							WC()->session->set( 'wc_shipping_dhl_dob', sanitize_text_field( $data[ self::IDENTIFIER_SHIPPING ][ 'date_of_birth' ] ) );
						} else {
							WC()->session->__unset( 'wc_shipping_dhl_dob' );

							$errors = new WP_Error();
							$code   = 'german-market-shipping-date-of-birth';

							$errors->add( $code, __( 'The date of birth you have entered is not a valid date.', 'woocommerce-german-market' ) );

							throw new InvalidCartException(
								'woocommerce_woocommerce_german_market_shipping_error',
								$errors,
								409
							);
						}
					} else {
						WC()->session->__unset( 'wc_shipping_dhl_dob' );

						$errors = new WP_Error();
						$code   = 'german-market-shipping-date-of-birth';

						$errors->add( $code, __( 'The date of birth you have entered was in wrong format.', 'woocommerce-german-market' ) );

						throw new InvalidCartException(
							'woocommerce_woocommerce_german_market_shipping_error',
							$errors,
							409
						);
					}
				}

			}

			// Client Number Validation.

			if ( self::is_client_number_required() ) {

				if ( ! isset( $data[ self::IDENTIFIER_SHIPPING ][ 'client_number' ] ) ) {
					WC()->session->__unset( 'wc_shipping_dhl_client_number' );

					$errors = new WP_Error();
					$code   = 'german-market-shipping-client-number';

					$errors->add( $code, sprintf( __( 'Please enter your %s!', 'woocommerce-german-market' ), __( 'DHL Client Number', 'woocommerce-german-market' ) ) );

					throw new InvalidCartException(
						'woocommerce_woocommerce_german_market_shipping_error',
						$errors,
						409
					);
				} else {
					if ( ! is_numeric( $data[ self::IDENTIFIER_SHIPPING ][ 'client_number' ] ) ) {
						WC()->session->__unset( 'wc_shipping_dhl_client_number' );

						$errors = new WP_Error();
						$code   = 'german-market-shipping-client-number';

						$errors->add( $code, __( 'Your DHL client number is wrong. It contains 6-10 Digits.', 'woocommerce-german-market' ) );

						throw new InvalidCartException(
							'woocommerce_woocommerce_german_market_shipping_error',
							$errors,
							409
						);
					} else
					if ( strlen( $data[ self::IDENTIFIER_SHIPPING ][ 'client_number' ] ) < 6 || strlen( $data[ self::IDENTIFIER_SHIPPING ][ 'client_number' ] ) > 10 ) {
						WC()->session->__unset( 'wc_shipping_dhl_client_number' );

						$errors = new WP_Error();
						$code   = 'german-market-shipping-client-number';

						$errors->add( $code, __( 'Your DHL client number is wrong. It contains 6-10 Digits only.', 'woocommerce-german-market' ) );

						throw new InvalidCartException(
							'woocommerce_woocommerce_german_market_shipping_error',
							$errors,
							409
						);
					} else {
						WC()->session->set( 'wc_shipping_dhl_client_number', sanitize_text_field( $data[ self::IDENTIFIER_SHIPPING ][ 'client_number' ] ) );

						if ( is_user_logged_in() ) {
							// Store DHL Client ID in User meta data
							$user_id = get_current_user_id();
							update_user_meta( $user_id, 'wc_shipping_dhl_client_number', $data[ self::IDENTIFIER_SHIPPING ][ 'client_number' ] );
						}
					}
				}
			}

			// Pickup Location Validation.

			if ( 'on' === get_option( 'wgm_add_on_woocommerce_shipping_dhl', 'off' ) ) {
				if ( class_exists( '\MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Parcels' ) ) {
					if ( $order->has_shipping_method( Parcels::get_instance()->id ) ) {
						if ( ! empty( WC()->session->get( Parcels::get_instance()->field_id ) ) ) {
							$location_id = WC()->session->get( Parcels::get_instance()->field_id );
							if ( empty( Shipping_Provider::$ajax->get_terminal_by_id( $order, $location_id ) ) ) {
								$errors = new WP_Error();
								$code   = 'german-market-shipping-pickup-location';

								$errors->add( $code, sprintf( __( 'Please choose a %s.', 'woocommerce-german-market' ), __( 'Parcelshop', 'woocommerce-german-market' ) ) );

								throw new InvalidCartException(
									'woocommerce_woocommerce_german_market_shipping_error',
									$errors,
									409
								);
							}
						} else {
							$errors = new WP_Error();
							$code   = 'german-market-shipping-pickup-location';

							$errors->add( $code, sprintf( __( 'Please choose a %s.', 'woocommerce-german-market' ), __( 'Parcelshop', 'woocommerce-german-market' ) ) );

							throw new InvalidCartException(
								'woocommerce_woocommerce_german_market_shipping_error',
								$errors,
								409
							);
						}
					}
				}
				if ( class_exists( '\MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Packstation' ) ) {
					if ( $order->has_shipping_method( Packstation::get_instance()->id ) ) {
						if ( ! empty( WC()->session->get( Packstation::get_instance()->field_id ) ) ) {
							$location_id = WC()->session->get( Packstation::get_instance()->field_id );
							if ( empty( Shipping_Provider::$ajax->get_terminal_by_id( $order, $location_id ) ) ) {
								$errors = new WP_Error();
								$code   = 'german-market-shipping-pickup-location';

								$errors->add( $code, sprintf( __( 'Please choose a %s.', 'woocommerce-german-market' ), __( 'Packstation', 'woocommerce-german-market' ) ) );

								throw new InvalidCartException(
									'woocommerce_woocommerce_german_market_shipping_error',
									$errors,
									409
								);
							}
						} else {
							$errors = new WP_Error();
							$code   = 'german-market-shipping-pickup-location';

							$errors->add( $code, sprintf( __( 'Please choose a %s.', 'woocommerce-german-market' ), __( 'Packstation', 'woocommerce-german-market' ) ) );

							throw new InvalidCartException(
								'woocommerce_woocommerce_german_market_shipping_error',
								$errors,
								409
							);
						}
					}
				}
			}

		}

		do_action( 'wgm_blocks_shipping_addon_after_order_validation' );
	}

	/**
	 * Store document upload into user meta after order got processed.
	 *
	 * @Hook woocommerce_store_api_checkout_order_processed
	 *
	 * @param WC_Order $order order object
	 *
	 * @return void
	 */
	public static function order_processed( WC_Order $order ) {

		$update_order  = false;
		$dob           = WC()->session->get( 'wc_shipping_dhl_dob' );
		$delivery_day  = WC()->session->get( 'dhl_use_delivery_day' );
		$client_number = WC()->session->get( 'wc_shipping_dhl_client_number' );

		if ( $delivery_day ) {
			$order->update_meta_data( '_wgm_dhl_service_preferred_day', WC()->session->get( '_wgm_dhl_service_preferred_day', '' ) );
			$update_order = true;
		}

		if ( $dob ) {
			$order->update_meta_data( 'billing_dob', $dob );
			$update_order = true;
		}

		if ( $client_number ) {
			$order->update_meta_data( 'wc_shipping_dhl_client_number', $client_number );
			$update_order = true;
		}

		if (
			( class_exists( 'MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Parcels' ) && $order->has_shipping_method( Parcels::get_instance()->id ) ) ||
			( class_exists( 'MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Packstation' ) && $order->has_shipping_method( Packstation::get_instance()->id ) )
		) {
			if ( class_exists( 'MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Parcels' ) ) {
				$field = Parcels::get_instance()->field_id;
			}
			if ( class_exists( 'MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Packstation' ) ) {
				$field = Packstation::get_instance()->field_id;
			}
			$location_id = WC()->session->get( $field );
			$method      = ( class_exists( 'MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Parcels' ) && $order->has_shipping_method( Parcels::get_instance()->id ) ) ? 'parcelshops' : 'packstations';
			$terminal    = Shipping_Provider::$backend->get_terminal_by_id(  $location_id, $method );

			$order->update_meta_data( Parcels::get_instance()->field_id, $location_id );
			$order->update_meta_data( Woocommerce_Shipping::$terminal_data_field, $terminal );

			$update_order = true;
		}

		if ( $update_order ) {
			$order->save();
		}
	}
}
