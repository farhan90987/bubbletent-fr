<?php
/**
 * WooCommerce Buy Now Paypal Handler Functions
 *
 * @package    cashier/modules/buy-now/php/gateways/paypal-standard/
 * @since      4.0.1
 * @version    1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SA_Buy_Now_WC_PayPal_Handler' ) ) {

	/**
	 * Class for adding PayPal handler functions In Buy Now
	 */
	class SA_Buy_Now_WC_PayPal_Handler {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'woocommerce_init', array( $this, 'sa_bn_wc_load_paypal_files' ) );
		}

		/**
		 * Load files required to handle paypal reference transactions when paypal is enabled.
		 */
		public function sa_bn_wc_load_paypal_files() {
			if ( is_object( WC()->payment_gateways ) && is_callable( array( WC()->payment_gateways, 'payment_gateways' ) ) ) {
				$payment_gateways = WC()->payment_gateways->payment_gateways();
				if ( is_array( $payment_gateways ) && isset( $payment_gateways['paypal'] ) ) {
					$paypal_gateway = $payment_gateways['paypal'];
					if ( is_object( $paypal_gateway ) && is_callable( array( $paypal_gateway, 'is_available' ) ) && true === $paypal_gateway->is_available() ) {
						require_once 'includes/sa-buy-now-wc-paypal-helper-functions.php';
						require_once 'abstracts/class-sa-buy-now-wc-sv-api-base.php';
						require_once 'includes/class-sa-buy-now-wc-paypal-reference-transaction-api.php';
						require_once 'includes/class-sa-buy-now-wc-paypal-reference-transaction-api-request.php';
						require_once 'includes/class-sa-buy-now-wc-paypal-reference-transaction-api-response.php';
						require_once 'includes/class-sa-buy-now-wc-paypal-reference-transaction-api-response-checkout.php';
						require_once 'includes/class-sa-buy-now-wc-paypal-reference-transaction-api-response-billing-agreement.php';
						require_once 'includes/class-sa-buy-now-wc-paypal-reference-transaction-api-response-payment.php';
						require_once 'includes/class-sa-buy-now-wc-paypal-do-reference-transaction-api-response-payment.php';
						require_once 'includes/class-sa-buy-now-wc-paypal-standard-ipn-handler.php';
						require_once 'includes/class-sa-buy-now-wc-paypal-reference-transaction-ipn-handler.php';
						require_once 'includes/class-sa-buy-now-wc-paypal-standard-ipn-failure-handler.php';
						require_once 'class-sa-buy-now-wc-paypal-standard.php';
					}
				}
			}

		}
	}
}

new SA_Buy_Now_WC_PayPal_Handler();
