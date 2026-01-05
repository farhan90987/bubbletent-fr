<?php
/**
 * Authorize.net CIM integration In Buy Now
 *
 * @package woocommerce-buy-now
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SA_Buy_Now_WC_Authorize_Net_CIM' ) ) {

	/**
	 * Class for adding Authorize.net CIM integration In Buy Now
	 */
	class SA_Buy_Now_WC_Authorize_Net_CIM {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'buy_now_post_fields', array( $this, 'buy_now_post_fields_for_wc_authorize_net_cim' ), 10, 2 );

			// Filter for Buy Now's one click checkout supported gateways.
			add_filter( 'sa_wc_buy_now_supported_gateways', array( $this, 'add_gateway_support' ) );
		}

		/**
		 * Modify Post fields for adding data for Authorize.net CIM
		 *
		 * @param  integer $last_order_id Order ID.
		 * @param  WP_User $current_user User object.
		 */
		public function buy_now_post_fields_for_wc_authorize_net_cim( $last_order_id = 0, $current_user = null ) {

			if ( ( isset( $_REQUEST['woocommerce-process-checkout-nonce'] ) ) && ! wp_verify_nonce( wc_clean( wp_unslash( $_REQUEST['woocommerce-process-checkout-nonce'] ) ), 'woocommerce-process_checkout' ) ) { //phpcs:ignore
				return;
			} elseif ( ( isset( $_POST['_wpnonce'] ) ) && ! wp_verify_nonce( wc_clean( wp_unslash( $_POST['_wpnonce'] ) ), 'woocommerce-process_checkout' ) ) { //phpcs:ignore
				return;
			}

			$post_payment_method = ( ! empty( $_POST['payment_method'] ) ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : ''; // phpcs:ignore

			if ( ! empty( $last_order_id ) && ! empty( $post_payment_method ) && 'authorize_net_cim_credit_card' === $post_payment_method ) {

				$last_profile_id = get_post_meta( $last_order_id, '_wc_authorize_net_cim_credit_card_payment_token', true );

				if ( ! empty( $last_profile_id ) ) {

					$_POST['wc-authorize-net-cim-credit-card-payment-token'] = $last_profile_id; // phpcs:ignore

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

			$supported_gateways[] = 'authorize_net_cim_credit_card';

			return $supported_gateways;
		}
	}

}

new SA_Buy_Now_WC_Authorize_Net_CIM();
