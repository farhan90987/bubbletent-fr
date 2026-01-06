<?php

defined( 'ABSPATH' ) || exit;

/**
 * This class implements the function "Logging Checkout Checkboxes" for the checkout block
 */
class German_Market_Blocks_Checkbox_Logging extends German_Market_Blocks_Methods {
	
    /**
     * Call actions and filters
     *
     * @return void
     */
    public function init() {
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( __CLASS__, 'logging' ), 10, 2 );
    }

	/**
	 * This method gets the the data from the checkout block extension,
	 * formats them to be used in German Market Core
	 * and calls WGM_Template::checkbox_logging to save the log
	 *
	 * @param \WC_Order $order
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public static function logging( \WC_Order $order, \WP_REST_Request $request ) {
        
        $posted_data = array();

		// sepa checkbox
		$data = $request->get_params();
		if ( isset( $data[ 'payment_method' ] ) && 'german_market_sepa_direct_debit' === $data[ 'payment_method' ] ) {

			if ( isset( $data[ 'payment_data' ] ) && is_array( $data[ 'payment_data' ] ) ) {

				$saved_errors = array();

				foreach ( $data[ 'payment_data' ] as $data ) {
					if ( isset( $data[ 'key' ] ) && 'german-market-sepa-checkbox-mandate-text' === $data[ 'key' ] ) {
						$posted_data[ 'german-market-sepa-checkbox-mandate-text' ] = $data[ 'value' ];
					}
				}
			}
		}
		
		// all checkboxes
		$checkbox_data = $request[ 'extensions' ];
		
		if ( isset( $checkbox_data[ 'german-market-store-api-integration' ] ) )  {
			
			foreach ( $checkbox_data[ 'german-market-store-api-integration' ] as $key => $value ) {
				if ( true === $value ) {
					$posted_data[ $key ] = 'yes';
				}
			}
			WGM_Template::checkbox_logging( $order->get_id(), array(), $order, $posted_data );
		}
	}	
}
