<?php

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Utilities\ArrayUtil;
use Automattic\WooCommerce\StoreApi\Exceptions\InvalidCartException;

/**
 * In this class we have imported some functions from the add-on "woocommerce-eu-vatin-check"
 * or we call them in a wrapper method 
 * or we have slightly modified these methods 
 * to use them in the the cart and checkout block
 */
class German_Market_Blocks_Core_Functions_For_EU_Vat_Check extends German_Market_Blocks_Methods {
	
    /**
     * Call actions and filters
     *
     * @return void
     */
    public function init() {

       // format totals for eu vatin add on (tax free strings)
		add_filter( 'german_market_cart_total_tax_string_blocks', array( $this, 'eu_vat_id_total_strings' ), 10, 2 );
		
		// save data
		add_filter( 'woocommerce_store_api_checkout_update_order_from_request', array( $this, 'save_billing_vat_to_order' ), 10, 2 );

    }

	/**
	 * This method gets the the data from the checkout block extension,
	 * and save it as meta data / note to the order
	 *
	 * @param \WC_Order $order
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function save_billing_vat_to_order( \WC_Order $order, \WP_REST_Request $request ) {
		
		$data = $request[ 'extensions' ];
		
		if ( isset( $data[ 'german-market-eu-vat-id-store-api-integration' ] ) )  {
			
			if ( isset( $data[ 'german-market-eu-vat-id-store-api-integration' ][ 'billing_vat' ] ) ) {

				$billing_vat = $data[ 'german-market-eu-vat-id-store-api-integration' ][ 'billing_vat' ];
				
				$billing_vat = sanitize_text_field( $data[ 'german-market-eu-vat-id-store-api-integration' ][ 'billing_vat' ] );
				$billing_vat = str_replace( ' ', '', $billing_vat );

				$error_message = $this->wcvat_woocommerce_after_checkout_validation( $order, $billing_vat );
				
				// Check erorrs even though errors are already checked in checkout block
				if ( ! empty( $error_message ) ) {
					$errors = new \WP_Error();
					$code   = 'german-market-free-items-not-allowed-in-cart';

					$errors->add( $code, $error_message );

					throw new InvalidCartException(
						'woocommerce_woocommerce_german_market_payment_error',
						$errors,
						409
					);
				}

				if ( ! empty( $billing_vat ) ) {
					
					// save in order
					$order->update_meta_data( 'billing_vat', $billing_vat );

					// save in user profile
					$user_id = $order->get_user_id();
					if ( $user_id > 0 ) {
						if ( apply_filters( 'wcvat_save_billing_vat_in_user_meta', true ) ) {
							update_user_meta( $user_id, 'billing_vat', $billing_vat );
						}
					}

					// logging
					if ( 'on' === get_option( 'german_market_vat_logging', 'off' ) ) {
			
						if ( apply_filters( 'wcvat_vat_logging_enabled', true, $order, $billing_vat ) ) {
			
							$validator 				= new WC_VAT_Validator( $billing_vat );
							$api_response_formatted = $validator->get_api_response_formatted();
							
							if ( $api_response_formatted && ! empty( $api_response_formatted ) ) {
								$order->add_order_note( $api_response_formatted, apply_filters( 'wcvat_vat_logging_is_customer_note', 0 ), apply_filters( 'wcvat_vat_logging_added_by_user', false ) );
			
								// addionaly, save raw api response as meta-data
								if ( apply_filters( 'wcvat_logging_save_raw_api_response_as_meta', true ) ) {
									$api_response_raw = $validator->get_api_response();
									if ( $api_response_raw && ! empty( $api_response_raw ) ) {
										$order->update_meta_data( '_wcvat_raw_api_response', $api_response_raw );
									}
								}
							}	
						}
					}
					
					// save order after updating
					$order->save();
				}
			}
		}
	}

	/**
	 * Get "tax free" string in cart / checkout block for totals
	 *
	 * @param String $cart_total_tax_string
	 * @param WC_Cart $wc_cart
	 * @return String
	 */
	public function eu_vat_id_total_strings( $cart_total_tax_string, $wc_cart ) {

		if ( empty( $cart_total_tax_string ) ) {
			if ( true === WGM_Session::get( 'eu_vatin_check_exempt' ) && true === WGM_Session::get( 'eu_vatin_is_success' ) && '' !== WGM_Session::get( 'eu_vatin_check_billing_vat' ) ) {
				$cart_total_tax_string = apply_filters( 'wcvat_woocommerce_vat_notice_eu_checkout', get_option( 'vat_options_notice', __( 'Tax free intracommunity delivery', 'woocommerce-german-market' ) ), $wc_cart );
			}

			$eu_countries = WC()->countries->get_european_union_countries();
			$customer = WC()->customer;
			if ( is_object( $customer ) && method_exists( $customer, 'get_taxable_address' ) ) {
				list( $country, $state, $postcode, $city ) = $customer->get_taxable_address();

				if ( 
					( ! in_array( $country, $eu_countries ) ) ||
					( WGM_Helper::is_vat_postcode_exemptions( $country, $postcode ) ) 
				) {
					$cart_total_tax_string = apply_filters( 'wcvat_woocommerce_vat_notice_not_eu_checkout', get_option( 'vat_options_non_eu_notice', __( 'Tax-exempt export delivery', 'woocommerce-german-market' ) ), $wc_cart );
				}
			}					
		}

		return $cart_total_tax_string;
	}
	
	/**
	 * This is a copy from WC_Order::get_tax_location (protected)
	 * It just returns the country of the tax location of the order
	 *
	 * @param WC_Order $order
	 * @return String
	 */
	public function get_tax_location_country( $order ) {
		
		$tax_based_on = get_option( 'woocommerce_tax_based_on' );

		if ( 'shipping' === $tax_based_on && ! $order->get_shipping_country() ) {
			$tax_based_on = 'billing';
		}

		$args = array(
			'country'  => 'billing' === $tax_based_on ? $order->get_billing_country() : $order->get_shipping_country(),
		);

		/**
		 * Filters whether apply base tax for local pickup shipping method or not.
		 *
		 * @since 6.8.0
		 * @param boolean apply_base_tax Whether apply base tax for local pickup. Default true.
		 */
		$apply_base_tax = true === apply_filters( 'woocommerce_apply_base_tax_for_local_pickup', true );

		/**
		 * Filters local pickup shipping methods.
		 *
		 * @since 6.8.0
		 * @param string[] $local_pickup_methods Local pickup shipping method IDs.
		 */
		$local_pickup_methods = apply_filters( 'woocommerce_local_pickup_methods', array( 'legacy_local_pickup', 'local_pickup' ) );

		$shipping_method_ids = ArrayUtil::select( $order->get_shipping_methods(), 'get_method_id', ArrayUtil::SELECT_BY_OBJECT_METHOD );

		// Set shop base address as a tax location if order has local pickup shipping method.
		if ( $apply_base_tax && count( array_intersect( $shipping_method_ids, $local_pickup_methods ) ) > 0 ) {
			$tax_based_on = 'base';
		}

		// Default to base.
		if ( 'base' === $tax_based_on || empty( $args['country'] ) ) {
			$args['country']  = WC()->countries->get_base_country();
		}

		$args = apply_filters( 'woocommerce_order_get_tax_location', $args, $order );
		return $args[ 'country' ];
	}

	/**
	 * Validate billing_vat for an order (check tax_location)
	 * It just returns the country of the tax location of the order
	 * This is a modified version of the function "wcvat_woocommerce_after_checkout_validation"
	 *
	 * @param WC_Order $order
	 * @param String $billing_vat
	 * 
	 * @return String
	 */
	public function wcvat_woocommerce_after_checkout_validation( $order, $billing_vat ) {

		$error = '';

		$vat_required = false;
		$base_location = wc_get_base_location();
		$base_country = $base_location[ 'country' ];
		$eu_countries = WC()->countries->get_european_union_countries();
		$display_vat_field 	= apply_filters( 'wcvat_display_vat_field', get_option( 'german_market_display_vat_number_field', 'eu_optional' ) );
		$tax_location_country = $this->get_tax_location_country( $order );
		$is_optional_and_eu_country = false;

		if ( 'always_mandatory' == $display_vat_field ) {
			$vat_required = true;
			if ( ! in_array( $tax_location_country, $eu_countries ) ) {
				$vat_required = false;
			}
		} else if ( 'eu_mandatory' == $display_vat_field ) {
			// check if country is an EU country
			if ( $tax_location_country != $base_country ) {
				if ( in_array( $tax_location_country, $eu_countries ) ) {
					$vat_required = true;
				}
			}
		} else {
			$vat_required = false;

			if ( in_array( $tax_location_country, $eu_countries ) ) {
				$is_optional_and_eu_country = true;
			}
		}
	
		$vat_required = apply_filters( 'wcvat_vat_field_is_required', $vat_required );
		
		if ( ( ! isset( $billing_vat ) || '' == trim( $billing_vat ) ) && ( true === $vat_required || 'always_mandatory' == $display_vat_field ) ) {
	
			$error = __( 'Please enter a valid VAT Identification Number.', 'woocommerce-german-market' );	
	
		} else if ( isset( $billing_vat ) && ( '' != $billing_vat ) && ( true === $vat_required || $is_optional_and_eu_country ) ) {
	
			// set the input
			$input = array( strtoupper( substr( $billing_vat, 0, 2 ) ), strtoupper( substr( $billing_vat, 2 ) ) );
	
			// set country to billing country by default
			$country = $tax_location_country;
			$validator = new WC_VAT_Validator( $input, $country );
	
			if ( ! $validator->is_valid() ) {
				
				if ( $validator->has_errors() ) {
					
					if ( $validator->get_last_error_code() != '200' ) {
						$error = $validator->get_last_error_message();
					} else {
						$error = __( 'Please enter a valid VAT Identification Number registered in a country of the EU.', 'woocommerce-german-market' );
					}
				}
			}
		}

		return $error;
	}

}
