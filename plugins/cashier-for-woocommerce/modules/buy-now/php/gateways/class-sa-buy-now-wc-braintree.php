<?php
/**
 * Braintree integration In Buy Now
 *
 * @package woocommerce-buy-now
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SA_Buy_Now_WC_Braintree' ) ) {

	/**
	 * Class for adding Braintree integration In Buy Now
	 */
	class SA_Buy_Now_WC_Braintree {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'buy_now_post_fields', array( $this, 'buy_now_post_fields_for_wc_braintree' ), 10, 2 );

			// Filter for Buy Now's one click checkout supported gateways.
			add_filter( 'sa_wc_buy_now_supported_gateways', array( $this, 'add_gateway_support' ) );
		}

		/**
		 * Modify Post fields for adding data for Braintree
		 *
		 * @param  integer $last_order_id Order ID.
		 * @param  WP_User $current_user User object.
		 */
		public function buy_now_post_fields_for_wc_braintree( $last_order_id = 0, $current_user = null ) {

			if ( ! empty( $last_order_id ) && $current_user instanceof WP_User ) {
				$order               = wc_get_order( $last_order_id );
				$last_payment_method = ( ! empty( $order ) ) ? $order->get_payment_method() : '';

				if ( 'braintree_credit_card' === $last_payment_method || 'braintree_paypal' === $last_payment_method ) {
					$token = get_post_meta( $last_order_id, '_wc_' . $last_payment_method . '_payment_token', true );

					if ( ! empty( $token ) ) {
						$payment_method                                      = str_replace( '_', '-', $last_payment_method );
						$_POST[ 'wc-' . $payment_method . '-payment-token' ] = $token;
					} else {
						// Filter to invalidate checkout process.
						add_filter( 'wc_bn_valid_for_process_checkout', array( $this, 'invalidate_checkout' ) );
					}
				}
			}
		}

		/**
		 * Function to invalidate checkout process.
		 *
		 * @param boolean $is_valid Is checkout valid.
		 * @return boolean
		 */
		public function invalidate_checkout( $is_valid = false ) {
			return false;
		}

		/**
		 * Function to add this gateway to list of supported gateways for Buy Now's one click checkout.
		 *
		 * @param array $supported_gateways list of supported gateways.
		 * @return array $supported_gateways list of supported gateways.
		 */
		public function add_gateway_support( $supported_gateways = array() ) {

			if ( ! is_array( $supported_gateways ) ) {
				$supported_gateways = array();
			}

			$supported_gateways[] = 'braintree_credit_card';
			$supported_gateways[] = 'braintree_paypal';

			return $supported_gateways;
		}
	}
}

new SA_Buy_Now_WC_Braintree();
