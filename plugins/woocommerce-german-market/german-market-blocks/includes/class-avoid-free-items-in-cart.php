<?php
use Automattic\WooCommerce\StoreApi\Exceptions\InvalidCartException;

defined( 'ABSPATH' ) || exit;

/**
 * This class implements the function "Avoid free items in cart" for the checkout block
 */
class German_Market_Blocks_Avoid_Free_Items_in_Cart extends German_Market_Blocks_Methods {
	
    /**
     * Call actions and filters
     *
     * @return void
     */
    public function init() {
		add_action( 'woocommerce_store_api_checkout_order_processed', array( __CLASS__, 'avoid_free_items_in_cart' ) );
    }

	/**
	 * Throws an error if setting is activated and there is at least one free item in the cart (using checkout block)
	 *
	 * @param $order
	 * @return void
	 */
	public static function avoid_free_items_in_cart( $order ) {
			
		if ( method_exists( 'WGM_Template', 'avoid_free_items_in_cart_checkout_block_check' ) ) {
			$has_free_items = WGM_Template::avoid_free_items_in_cart_checkout_block_check( $order );
			if ( $has_free_items ) {
				
				$errors = new \WP_Error();
				$code   = 'german-market-free-items-not-allowed-in-cart';

				$error_message = get_option( 'woocommerce_de_avoid_free_items_in_cart_message', __( 'Sorry, you can\'t proceed to checkout. Please contact our support.', 'woocommerce-german-market' ) );

				$errors->add( $code, $error_message );

				throw new InvalidCartException(
					'woocommerce_woocommerce_german_market_payment_error',
					$errors,
					409
				);
			}
		}
	}
}
