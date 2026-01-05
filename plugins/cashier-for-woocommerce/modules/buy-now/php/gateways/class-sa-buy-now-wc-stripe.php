<?php
/**
 * Stripe integration In Buy Now
 *
 * @package woocommerce-buy-now/includes/gateways
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SA_Buy_Now_WC_Stripe' ) ) {

	/**
	 * Class for adding Stripe integration In Buy Now
	 */
	class SA_Buy_Now_WC_Stripe {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'buy_now_post_fields', array( $this, 'buy_now_post_fields_for_stripe' ), 10, 2 );

			// Filter for Buy Now's one click checkout supported gateways.
			add_filter( 'sa_wc_buy_now_supported_gateways', array( $this, 'add_gateway_support' ) );
		}

		/**
		 * Modify Post fields for adding data for Stripe
		 *
		 * @param  integer $last_order_id Order ID.
		 * @param  WP_User $current_user User object.
		 */
		public function buy_now_post_fields_for_stripe( $last_order_id = 0, $current_user = null ) {

			$order   = wc_get_order( $last_order_id );
			$user_id = $current_user->ID;

			$last_payment_method = ( ! empty( $order ) ) ? $order->get_payment_method() : '';

			if ( 'stripe' === $last_payment_method ) {
				// Improvements: Check if default token is from stripe i.e. having gateway id as stripe.
				$default_token = WC_Payment_Tokens::get_customer_default_token( $user_id );
				$token_id      = ( is_object( $default_token ) ) ? $default_token->get_id() : '';

				if ( ! empty( $token_id ) ) {
					$_POST['wc-stripe-payment-token'] = $token_id;
				} else {
					// Filter to invalidate checkout process.
					add_filter( 'wc_bn_valid_for_process_checkout', array( $this, 'invalidate_checkout' ) );
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

			$supported_gateways[] = 'stripe';

			return $supported_gateways;
		}
	}
}

new SA_Buy_Now_WC_Stripe();
