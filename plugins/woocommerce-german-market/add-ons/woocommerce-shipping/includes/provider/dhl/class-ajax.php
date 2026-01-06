<?php

namespace MarketPress\GermanMarket\Shipping\Provider\DHL;

use MarketPress\GermanMarket\Shipping\Ajax as Shipping_Ajax;
use MarketPress\GermanMarket\Shipping\Helper;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Packstation;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Parcels;
use WC_Cart;
use WC_Order;
use WC_Session_Handler;
use DateInterval;
use DateTime;
use Exception;
use function DeepCopy\deep_copy;

class Ajax extends Shipping_Ajax {

	/**
	 * Singleton.
	 *
	 * @acces protected
	 * @static
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * Singleton getInstance.
	 *
	 * @static
	 *
	 * @return self
	 */
	public static function get_instance() : self {

		return ( null !== self::$instance ) ? self::$instance : self::$instance = new self();
	}

	/**
	 * Class constructor.
	 *
	 * @param string $id
	 */
	public function __construct( string $id ) {

		parent::__construct();

		$this->id = $id;
	}

	/**
	 * Saves terminal and timeshift field to WC() session.
	 *
	 * @Hook woocommerce_checkout_update_order_review
	 *
	 * @param array|string $post_data
	 *
	 * @return void
	 */
	public function checkout_save_session_fields( $post_data ) {

		parse_str( $post_data, $posted );

		// Is session running?
		if ( isset ( WC()->session ) && ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );
		}

		if ( isset( $posted[ Parcels::get_instance()->field_id ] ) && ! empty( $posted[ Parcels::get_instance()->field_id ] ) ) {
			WC()->session->set( Parcels::get_instance()->field_id, $posted[ Parcels::get_instance()->field_id ] );
		}
		if ( isset( $posted[ 'wc_shipping_dhl_home_delivery_shifts' ] ) && ! empty( $posted[ 'wc_shipping_dhl_home_delivery_shifts' ] ) ) {
			WC()->session->set( 'wc_shipping_dhl_home_delivery_shifts', $posted[ 'wc_shipping_dhl_home_delivery_shifts' ] );
		}
		if ( isset( $posted[ 'wc_shipping_dhl_client_number' ] ) && ! empty( $posted[ 'wc_shipping_dhl_client_number' ] ) ) {
			WC()->session->set( 'wc_shipping_dhl_client_number', $posted[ 'wc_shipping_dhl_client_number' ] );
		}
	}

	/**
	 * Prefork a API call for getting terminals.
	 *
	 * @param string $country
	 * @param string $city
	 * @param string $street
	 * @param string $zipCode
	 *
	 * @return array
	 */
	public function get_terminals( string $country, string $city, string $street, string $zipCode ) : array {

		$terminals        = array();
		$api_result_limit = Shipping_Provider::$options->get_option( 'api_results_limit', 15 );

		if ( ! empty( Shipping_Provider::$api ) ) {

			$data = array(
				'limit'   => $api_result_limit,
				'country' => $country,
				'city'    => $city,
				'street'  => $street,
				'zipCode' => $zipCode,
			);

			$terminals = Shipping_Provider::$api->find_parcelshops( $data );
		}

		return $terminals;
	}

	/**
	 * Prefork a API call for getting terminals.
	 *
	 * @param string $country
	 * @param string $city
	 * @param string $street
	 * @param string $zipCode
	 *
	 * @return array
	 */
	public function get_packstations( string $country, string $city, string $street, string $zipCode ) : array {

		$terminals        = array();
		$api_result_limit = Shipping_Provider::$options->get_option( 'api_results_limit', 15 );

		if ( ! empty( Shipping_Provider::$api ) ) {

			$data = array(
				'limit'   => $api_result_limit,
				'country' => $country,
				'city'    => $city,
				'street'  => $street,
				'zipCode' => $zipCode,
			);

			$terminals = Shipping_Provider::$api->find_packstations( $data );
		}

		return $terminals;
	}

	/**
	 * Ajax API Call to receive packstations for a given location.
	 *
	 * @uses wc_ajax_get_dhl_packstations
	 * @uses wc_ajax_nopriv_get_dhl_packstations
	 *
	 * @return void, die()
	 */
	public function get_ajax_packstations() {

		$data = $this->get_packstations(
			WC()->customer->get_shipping_country(),
			WC()->customer->get_shipping_city(),
			WC()->customer->get_shipping_address(),
			WC()->customer->get_shipping_postcode()
		);

		wp_send_json( $data );
		wp_die();
	}

	/**
	 * Ajax API Call to receive packstations for a given location.
	 *
	 * @uses wc_ajax_get_dhl_packstations_modal
	 * @uses wc_ajax_nopriv_get_dhl_packstations_modal
	 *
	 * @return void, die()
	 */
	public function get_ajax_packstations_modal() {

		// Check if we request comes from WooCommerce Checkout Block.
		if ( ! isset( $_POST[ 'country' ] ) ) {
			$data = json_decode(file_get_contents('php://input'), true );
			if ( ! empty( $data ) ) {
				$country  = ! empty( $data[ 'country' ] ) ? Helper::get_country_code_from_country_name( $data[ 'country' ] ) : '';
				$city     = $data[ 'city' ] ?? '';
				$street   = $data[ 'street' ] ?? '';
				$postcode = $data[ 'postcode' ] ?? '';
				$grouped  = $data[ 'grouped' ] ?? false;
				$provider = $data[ 'provider' ] ?? false;
				$type     = $data[ 'type' ] ?? false;
			}
		} else {
			$country  = $_POST[ 'country' ];
			$city     = $_POST[ 'city' ] ?? '';
			$street   = $_POST[ 'street' ] ?? '';
			$postcode = $_POST[ 'postcode' ] ?? '';
			$grouped  = false;
			$provider = false;
			$type     = false;
		}

		if ( 1 == $grouped ) {
			$data = Packstation::get_instance()->get_grouped_terminals(
				$this->get_packstations(
					$country,
					$city,
					$street,
					$postcode
				)
			);
		} else {
			$data = $this->get_packstations(
				$country,
				$city,
				$street,
				$postcode
			);
		}

		wp_send_json( $data );
		wp_die();
	}

	/**
	 * This method is used by checkout block to retrieve location address when changing shipping method.
	 *
	 * @Hook get_dpd_selected_terminal_info
	 *
	 * @return void
	 */
	public function get_terminal_info_from_session() {

		$data     = json_decode(file_get_contents('php://input'), true );
		$response = array(
			'terminal_id'      => '',
			'terminal_address' => '',
		);

		if ( ! empty( $data ) ) {
			$country  = WC()->customer->get_shipping_country();
			$city     = WC()->customer->get_shipping_city();
			$street   = WC()->customer->get_shipping_address_1();
			$postcode = WC()->customer->get_shipping_country();
			if ( class_exists( '\MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Parcels' ) ) {
				if ( 'parcels' === $data[ 'type' ] ) {
					$selected_location_id = WC()->session->get( Parcels::get_instance()->field_id );
					if ( ! empty( $selected_location_id ) ) {
						$selected_location_address = Parcels::get_instance()->get_selected_terminal( $country, $city, $street, $postcode, $selected_location_id );
					}
				}
			}
			if ( class_exists( '\MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Packstation' ) ) {
				if ( 'packstation' === $data[ 'type' ] ) {
					$selected_location_id = WC()->session->get( Packstation::get_instance()->field_id );
					if ( ! empty( $selected_location_id ) ) {
						$selected_location_address = Packstation::get_instance()->get_selected_terminal( $country, $city, $street, $postcode, $selected_location_id );
					}
				}
			}

			if ( ! empty( $selected_location_address ) ) {
				$response = array(
					'terminal_id'      => $selected_location_id,
					'terminal_address' => $selected_location_address,
				);
			}
		}

		wp_send_json( $response );
		die();
	}

	/**
	 * Used by Ajax to return terminal.
	 *
	 * @acces public
	 *
	 * @param WC_Order   $order
	 * @param int|string $terminal_id
	 *
	 * @return array
	 */
	public function get_terminal_by_id( WC_Order $order, $terminal_id ) : array {

		$method = ( $order->has_shipping_method( Parcels::get_instance()->id ) ) ? 'parcelshops' : 'packstations';

		// Is session running?
		if ( isset ( WC()->session ) && ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );
		}

		if ( 'parcelshops' == $method ) {
			$terminals = WC()->session->get( $this->id . '_parcelshops' );
			if ( empty( $terminals[ 'terminals' ] ) ) {
				$terminals = Shipping_Provider::$api->find_parcelshops( array(
					'street'  => $order->get_shipping_address_1(),
					'city'    => $order->get_shipping_city(),
					'zipCode' => $order->get_shipping_postcode(),
					'country' => $order->get_shipping_country()
				) );
			}
		} else {
			$terminals = WC()->session->get( $this->id . '_packstations' );
			if ( empty( $terminals[ 'terminals' ] ) ) {
				$terminals = Shipping_Provider::$api->find_packstations( array(
					'street'  => $order->get_shipping_address_1(),
					'city'    => $order->get_shipping_city(),
					'zipCode' => $order->get_shipping_postcode(),
					'country' => $order->get_shipping_country()
				) );
			}
		}

		return Shipping_Provider::$backend->get_terminal_by_id( $terminal_id, $method );
	}

	/**
	 * Set session variable for adding or removing Preferred Delivery Day fee.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function apply_preferred_delivery_date_fee() {

		if ( isset( $_POST[ 'fee' ] ) && isset( $_POST[ 'day' ] ) ) {

			if ( 'add' === $_POST[ 'fee' ] ) {

				// Date Validation.

				// We are using 'deep_copy' function to clone the DateTime object.

				$preferred_first_day           = Shipping_Provider::calculate_first_preferred_delivery_day( true );
				$preferred_last_day            = deep_copy( $preferred_first_day );
				$preferred_last_day            = $preferred_last_day->add( DateInterval::createFromDateString( '6 days' ) );
				$preferred_last_day            = $preferred_last_day->add( DateInterval::createFromDateString( '86399 seconds' ) );
				$preferred_customer_day_string = $_POST[ 'day' ];

				// Check for valid date format e.g. 2023-12-31

				if ( ! preg_match( '/^([19|20]+\d\d)[-](0[1-9]|1[012])[-.](0[1-9]|[12][0-9]|3[01])$/', $preferred_customer_day_string ) ) {
					WC()->session->__unset( 'dhl_use_delivery_day' );
					WC()->session->__unset( '_wgm_dhl_service_preferred_day' );
					wp_send_json_error(
						array(
							'msg' => __( 'The preferred delivery date format is invalid.', 'woocommerce-german-market' ),
						)
					);
				}

				// Check if we have a valid date.

				$check_date = date_parse( $preferred_customer_day_string );

				if ( ! empty( $check_date[ 'warnings' ] ) ) {
					WC()->session->__unset( 'dhl_use_delivery_day' );
					WC()->session->__unset( '_wgm_dhl_service_preferred_day' );
					wp_send_json_error(
						array(
							'msg' => __( 'The preferred delivery date is not a valid date.', 'woocommerce-german-market' ),
						)
					);
				}

				// Check if preferred date is between first and last possible date.

				$preferred_customer_day = new DateTime( $preferred_customer_day_string . ' ' . date( 'H:i:s', time() ), wp_timezone() );

				if ( ( $preferred_customer_day < $preferred_first_day ) || ( $preferred_customer_day > $preferred_last_day ) ) {
					WC()->session->__unset( 'dhl_use_delivery_day' );
					WC()->session->__unset( '_wgm_dhl_service_preferred_day' );
					wp_send_json_error(
						array(
							'msg' => __( 'The preferred delivery date is not a valid date.', 'woocommerce-german-market' ),
						)
					);
				}

				WC()->session->set( 'dhl_use_delivery_day', true );
			} else
			if ( 'remove' === $_POST[ 'fee' ] ) {
				WC()->session->__unset( 'dhl_use_delivery_day' );
			}
			WC()->session->set( '_wgm_dhl_service_preferred_day', $_POST[ 'day' ] );

			wp_send_json_success(
				array(
					'msg' => 'fee ' . ( 'add' === $_POST[ 'fee' ] ? 'added' : 'removed' ),
				)
			);
		} else {
			WC()->session->__unset( 'dhl_use_delivery_day' );
			WC()->session->__unset( '_wgm_dhl_service_preferred_day' );
		}
	}

	/**
	 * Add fee if session variable is set.
	 *
	 * @Hook woocommerce_cart_calculate_fees
	 *
	 * @param WC_Cart $cart
	 *
	 * @return void
	 */
	public function add_delivery_day_fee( $cart ) {

		if ( is_admin() && ! defined( 'DOING_AJAX' ) && ! defined( 'REST_REQUEST' ) ) {
			return; // Do not add the fee when in the admin area
		}

		// Only on checkout page
		if ( ! defined( 'REST_REQUEST' ) ) {
			if( ! ( is_checkout() && ! is_wc_endpoint_url() ) ) {
				return;
			}
		}

		$delivery_day_fee = Shipping_Provider::$options->get_option( 'service_preferred_day_fee', 1.2 );

		if ( $delivery_day_fee <= 0 ) {
			return;
		}

		if ( WC()->session->get( 'dhl_use_delivery_day' ) ) {
			WC()->cart->add_fee( __('DHL Delivery Day', 'woocommerce-german-market'), $delivery_day_fee );
		}
	}

}