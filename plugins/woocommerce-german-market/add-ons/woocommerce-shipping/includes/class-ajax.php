<?php

namespace MarketPress\GermanMarket\Shipping;

use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Packstation;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Parcels as DHL_Parcels;
use MarketPress\GermanMarket\Shipping\Provider\DPD\Methods\Parcels as DPD_Parcels;
use WC_Session_Handler;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Ajax {

	public string $id;

	/**
	 * Class constructor.
	 *
	 * @acces protected
	 */
	protected function __construct() {}

	/**
	 * Send a JSON response of DPD terminals.
	 *
	 * @uses wc_ajax_get_dpd_parcels
	 * @uses wc_ajax_nopriv_get_dpd_parcels
	 * @uses wc_ajax_get_dhl_parcels
	 * @uses wc_ajax_nopriv_get_dhl_parcels
	 *
	 * @return void, wp_die()
	 */
	public function get_ajax_terminals() {

		$data = $this->get_terminals(
			WC()->customer->get_shipping_country(),
			WC()->customer->get_shipping_city(),
			WC()->customer->get_shipping_address(),
			WC()->customer->get_shipping_postcode()
		);

		wp_send_json( $data );
		wp_die();
	}

	/**
	 * Send a JSON response of DPD terminals.
	 *
	 * @uses wc_ajax_get_dpd_parcels_modal
	 * @uses wc_ajax_nopriv_get_dpd_parcels_modal
	 * @uses wc_ajax_get_dhl_parcels_modal
	 * @uses wc_ajax_nopriv_get_dhl_parcels_modal
	 *
	 * @return void, wp_die()
	 */
	public function get_ajax_terminals_modal() {

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
			if ( 'dhl' == $provider ) {
				$data = DHL_Parcels::get_instance()->get_grouped_terminals(
					$this->get_terminals(
						$country,
						$city,
						$street,
						$postcode,
					)
				);
			} else
			if ( 'dpd' == $provider ) {
				$data = DPD_Parcels::get_instance()->get_grouped_terminals(
					$this->get_terminals(
						$country,
						$city,
						$street,
						$postcode,
					)
				);
			}
		} else {
			$data = $this->get_terminals(
				$country,
				$city,
				$street,
				$postcode,
			);
		}

		wp_send_json( $data );
		wp_die();
	}

	/**
	 * Saves a selected terminal and COD variable to WC() session.
	 *
	 * @uses wc_ajax_choose_dpd_terminal
	 * @uses wc_ajax_nopriv_choose_dpd_terminal
	 * @uses wc_ajax_choose_dhl_terminal
	 * @uses wc_ajax_nopriv_choose_dhl_terminal
	 *
	 * @return void, wp_die()
	 */
	public function ajax_save_session_terminal() {

		if ( ! isset( $_POST[ 'security' ] ) ) {
			// Check if we request comes from WooCommerce Checkout Block.
			$data  = json_decode(file_get_contents('php://input'), true );
			$_POST = array_merge( $_POST, $data );

			if ( ! wp_verify_nonce( $_POST[ 'security' ], 'save-terminal' ) ) {
				die();
			}
		} else {
			check_ajax_referer( 'save-terminal', 'security' );
		}

		// Is session running?
		if ( isset ( WC()->session ) && ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );
		}

		WC()->session->set( wc_clean( $_POST[ 'terminal_field' ] ), wc_clean( $_POST[ 'terminal' ] ) );

		$cod = isset( $_REQUEST[ 'cod' ] ) ? filter_var( $_REQUEST[ 'cod' ], FILTER_SANITIZE_NUMBER_INT ) : '';

		if ( is_numeric( $cod ) ) {
			WC()->session->set( 'cod_for_parcel', $cod );
		}

		WC()->customer->set_shipping_company( $_POST[ 'terminal_details' ][ 'company' ] );
		WC()->customer->set_shipping_address_1( $_POST[ 'terminal_details' ][ 'street' ] );
		WC()->customer->set_shipping_postcode( $_POST[ 'terminal_details' ][ 'pcode' ] );
		WC()->customer->set_shipping_city( $_POST[ 'terminal_details' ][ 'city' ] );

		$response = array(
			'terminal_id' => WC()->session->get( wc_clean( $_POST[ 'terminal_field' ] ) ),
		);

		/**
		 * Do we have a request from backend modal?
		 */
		if ( isset( $_POST[ 'is_backend_modal' ] ) && $_POST[ 'is_backend_modal' ] ) {
			if ( ! empty( $_POST[ 'order_id' ] ) ) {
				$order = wc_get_order( wc_clean( $_POST[ 'order_id' ] ) );
				if ( $order ) {
					$terminal = $this->get_terminal_by_id( $order, wc_clean( $_POST[ 'terminal' ] ) );
					if ( ! empty( $terminal ) ) {
						$provider               = Woocommerce_Shipping::get_instance()->get_provider_by_id( $this->id );
						$response[ 'address' ]  = $provider::$backend->get_formatted_shipping_address( $order, $terminal, true );
						$response[ 'terminal' ] = $terminal;
					}
				}
			}
		}

		wp_send_json( $response );
		wp_die();
	}

}