<?php

defined( 'ABSPATH' ) || exit;

/**
 * In this class we register our payment methods
 * so that they can be used in the checkout block
 * The registered classes inherit from AbstractPaymentMethodType
 * and are located in the "gateway" directory.
 */
class German_Market_Blocks_Payment_Method_Registry extends German_Market_Blocks_Methods {
	
    /**
     * Call actions and filters
     *
     * @return void
     */
    public function init() {

		// Support for gateway "purchase on account"
		add_action( 'woocommerce_blocks_loaded', array( __CLASS__, 'woocommerce_gateway_purchase_on_account_woocommerce_block_support' ) );

		// This is actually an integration
		// See react component PurchaseOnAccountTrigger
		// The callback sets a session variable if the payment method has been selected in the block
		// so German Market can add the fee
		add_action('woocommerce_blocks_loaded', function() {
			woocommerce_store_api_register_update_callback(
				array(
					'namespace' => 'german-market-blocks-gateway-fees',
					'callback'  => function( $data ) {

						if ( isset( $data[ 'germanMarketActivePaymentMethod' ] ) ) {
							WC()->session->set( 'german_market_wc_blocks_active_payment_method', $data[ 'germanMarketActivePaymentMethod' ] );
						}

					}
				)
			);

			woocommerce_store_api_register_update_callback(
				array(
					'namespace' => 'german-market-update-fee-taxes',
					'callback'  => function( $data ) {
						// nothing to do here, otherwise <TaxInfoFees /> does not work properly
					}
				)
			);
		});
    }

   /**
	 * Registers Payment Method "Purchase on account
	 * 
	 * @wp-hook 'woocommerce_blocks_loaded'	 *
	 */
	public static function woocommerce_gateway_purchase_on_account_woocommerce_block_support() {
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
		
			add_action(
				'woocommerce_blocks_payment_method_type_registration',
				function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
					$payment_method_registry->register( new WGM_Gateway_Purchase_On_Account_Blocks_Support );
					$payment_method_registry->register( new WGM_Gateway_Sepa_Direct_Debit_Blocks_Support );
				}
			);
		}
	}
}
