<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class German_Market_Lexoffice_API_General
 *
 * @author MarketPress
 */
class German_Market_Lexoffice_API_General {

	/**
	* Get voucher status
	*
	* @param String $voucher_id
	* @param $return_bool
	* @return Boolean (true if voucher exists) | Array if $return_bool is set to false
	*/
	public static function get_vouchers_status( $voucher_id, $return_bool = true) {

		if ( $voucher_id == '' ) {
			return true;
		}

		$curl = curl_init();

		$token_bucket = new WGM_Token_Bucket( 'lexoffice-vouchers', 2 );
		$token_bucket->consume();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => German_Market_Lexoffice_API_Auth::get_base_url() . "v1/vouchers/" . $voucher_id ,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
		    "accept: application/json",
		    "authorization: Bearer ". lexoffice_woocomerce_api_get_bearer(),
		    "cache-control: no-cache"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		$response_array = json_decode( $response, true );

		if ( ! $return_bool ) {
			return $response_array;
		}

		// if there is no connection, pretend voucher is still available
		if ( isset( $response_array[ 'error' ] ) && $response_array[ 'error' ] == 'Not Found' || $response == '' ) {
			return false;
		}

		return true;
	}

	/**
	* Get beauty error text from json string if possible
	* @param String
	* @return String
	*/
	public static function get_error_text( $json, $order = null ) {

		// init
		$return = $json;

		$array = json_decode( $json, true );
		if ( isset( $array[ 'error_description' ] ) ) {
			$return = $array[ 'error_description' ];
		}
		// make a nice error message for unsupported tax rates
		if ( isset( $array[ 'IssueList' ][ 0 ][ 'i18nKey' ] ) ) {
			$error_key = $array[ 'IssueList' ][ 0 ][ 'i18nKey' ];
			$invalid_tax_rate = str_replace( 'invalid_taxrate_', '', $error_key );
			if ( $invalid_tax_rate != $error_key ) {
				$return = sprintf( __( 'Unsupported tax rate: %s.', 'woocommerce-german-market' ), $invalid_tax_rate . '%' );

				if ( is_object( $order ) ) {
					$infos =  json_decode( lexoffice_woocomerce_api_order_to_curlopt_postfields( $order, false ), true );
					
					if ( isset( $infos[ 'useCollectiveContact' ] ) && true === $infos[ 'useCollectiveContact' ] ) {

						$done_info = false;

						if ( isset( $infos[ 'voucherItems' ] ) ) {
							foreach ( $infos[ 'voucherItems' ] as $item ) {
								if ( isset( $item[ 'categoryId' ] ) ) {
									
									$tax_type = '';

									if ( '4ebd965a-7126-416c-9d8c-a5c9366ee473' === $item[ 'categoryId' ] ) {
										$tax_type = __( 'Distance selling taxable in EU country', 'woocommerce-german-market' );
									}

									if ( ! empty( $tax_type ) ) {
										$url  = admin_url() . 'admin.php?page=german-market&tab=lexoffice';
										$return .= PHP_EOL . sprintf( __( '"%s" is to be created for the collective customer. This is not possible. Please check in the <a href="%s">Lexware Office settings</a> that you do not use a collective customer, but that you create a contact in Lexware Office.', 'woocommerce-german-market' ), $tax_type, $url  );
										$done_info = true;
										break;
									}
								}
							}
						}

						if ( ! $done_info ) {
							$country = empty( $order->get_shipping_country() ) ? $order->get_billing_country() : $order->get_shipping_country();
							if ( 'DE' != $country ) {

								$tax_type = __( 'Distance selling taxable in EU country', 'woocommerce-german-market' );
								$url  = admin_url() . 'admin.php?page=german-market&tab=lexoffice';

								$return .= PHP_EOL . sprintf( __( '"%s" with European tax rates cannot be sent to Lexware Office using the collective customer. Please check in the <a href="%s">Lexware Office settings</a> that you do not use a collective customer, but that you create a contact in Lexware Office.', 'woocommerce-german-market' ), $tax_type, $url );
							}
						}
					}
				}
			
			} else {

				if ( 'collective_customer_not_applicable' === $array[ 'IssueList' ][ 0 ][ 'i18nKey' ] ) {
					if ( is_object( $order ) ) {
						$infos =  json_decode( lexoffice_woocomerce_api_order_to_curlopt_postfields( $order, false ), true );
						if ( isset( $infos[ 'voucherItems' ] ) ) {
							foreach ( $infos[ 'voucherItems' ] as $item ) {
								if ( isset( $item[ 'categoryId' ] ) ) {
									
									$tax_type = '';

									if ( '9075a4e3-66de-4795-a016-3889feca0d20' === $item[ 'categoryId' ] ) {
										$tax_type = __( 'Tax free intracommunity delivery', 'woocommerce-german-market' );
									}

									if ( ! empty( $tax_type ) ) {
										$return = PHP_EOL . sprintf( __( '"%s" is to be created for the collective customer. This is not possible. Please check in the <a href="%s">Lexware Office settings</a> that you do not use a collective customer, but that you create a contact in Lexware Office.', 'woocommerce-german-market' ), $tax_type, $url  );
										break;
									}
								}
							}
						}
					}
				
				}

			}

		} else if ( isset( $array[ 'message' ] ) && $array[ 'message' ] === 'Unauthorized' ) {
			$url  = admin_url() . 'admin.php?page=german-market&tab=lexoffice';
			$return = '<strong>Unauthorized.</strong>' . PHP_EOL . sprintf( __( 'To solve the problem, run the authorization again in the <a href="%s">Lexware Office settings</a>. Activate the "Revoke authorization" setting, save the settings and run the authorization again as described in the menu.', 'woocommerce-german-market' ), $url );
		} 

		return apply_filters( 'lexoffice_woocommerce_error_message', $return, $json );
	}

	/**
	* Get all tax rates used in the shop
	*
	* @return Array
	**/
	public static function get_all_rates_in_shop() {

		// Tax Rates
		$all_rates = get_transient( 'german_market_all_rates_in_shop' );

		if ( false === $all_rates ) {
			$all_rates = array();
		}

		if ( ! empty( $all_rates ) ) {
			return $all_rates;
		}

		$tax_classes = WC_Tax::get_tax_classes();

		array_unshift( $tax_classes, 'standard' );

		foreach ( $tax_classes as $tax_class ) {

		 	$rates = WC_Tax::get_rates_for_tax_class( $tax_class );

		 	if ( empty( $rates ) && 'standard' === $tax_class ) {
		 		$rates = WC_Tax::get_rates_for_tax_class( '' );
		 	}

		 	if ( empty( $rates ) ) {
		 		continue;
		 	}

		 	foreach ( $rates as $rate ) {

		 		$tax_rate = floatval( $rate->tax_rate );
		 		if ( ! in_array( $tax_rate, $all_rates ) ) {
		 			$all_rates[] = $tax_rate;
		 		}
		 	};

		}

		if ( ! empty( $all_rates ) ) {

			if ( ! in_array( 0.0, $all_rates ) ) {
				$all_rates[] = 0.0;
			}

			set_transient( 'german_market_all_rates_in_shop', $all_rates, 10 );
		} else {
			$all_rates = array( 0.0, 7.0, 19.0 );
		}

		sort( $all_rates );
		return $all_rates;
	}

	/**
	* Get lexoffice oss setting info
	*
	* @return String
	**/
	public static function get_oss_info() {

		$transient_info = get_transient( 'lexoffice_woocommerce_api_get_oss_info' );
		if ( false !== $transient_info ) {
			return $transient_info;
		}

		$token_bucket = new WGM_Token_Bucket( 'lexoffice-profile', 2 );
		$token_bucket->consume();

		$curl = curl_init();

		curl_setopt_array( $curl,
			array(
			  	CURLOPT_URL => German_Market_Lexoffice_API_Auth::get_base_url() . "v1/profile/",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "GET",
				CURLOPT_HTTPHEADER => array(
				    "accept: application/json",
				    "authorization: Bearer " . lexoffice_woocomerce_api_get_bearer(),
				    "cache-control: no-cache",
				    "content-type: application/json",
				  ),
			)
		);

		$response = curl_exec( $curl );
		$response_array = json_decode( $response, true );
		curl_close( $curl );

		$oss_info = '';

		if ( isset( $response_array[ 'distanceSalesPrinciple' ] ) ) {
			$oss_info = strtolower( $response_array[ 'distanceSalesPrinciple' ] );
		}

		set_transient( 'lexoffice_woocommerce_api_get_oss_info', $oss_info, MINUTE_IN_SECONDS );

		return $oss_info;
	}

	/**
	* Before a refund is transmitted to lexoffice => transmit order (if not done yet)
	* Voucher API
	*
	* @since 3.22.1.1
	* @wp-hook woocommerce_de_lexoffice_api_before_send_refund
	* @param WC_Order $order
	* @param WC_Order_refund $refund
	* @return void
	*/
	public static function send_order_before_refund( $order, $refund ) {

		$order_lexoffice_status = $order->get_meta( '_lexoffice_woocomerce_has_transmission' );

		if ( empty( $order_lexoffice_status ) || apply_filters( 'woocommerce_de_lexoffice_force_transmit_order_before_refund', false, $refund, $order ) ) {
			add_filter( 'woocommerce_de_lexoffice_force_transmission_even_if_not_completed', '__return_true', 42 );
			lexoffice_woocomerce_api_send_voucher( $order, false );
			remove_filter( 'woocommerce_de_lexoffice_force_transmission_even_if_not_completed', '__return_true', 42 );
		}
	}

	/**
	 * Check if collective can be used for this order
	 * Can't be used for distance sales (EU tax rates), tax exempt export, free tax free intracommunity
	 * 
	 * @param WC_Order
	 * @return Boolean
	 */
	public static function is_collective_contact_allowed_for_order( $order ) {

		$is_collective_contact_allowed = true;

		if ( ! empty( WGM_Helper::wcvat_woocommerce_order_details_status( $order ) ) ) {
			$is_collective_contact_allowed = false;
		} else {
			
			$is_collective_contact_allowed = ! self::order_has_non_german_taxes( $order );

			if ( 
				'invoice' === get_option( 'woocommerce_de_lexoffice_voucher_or_invoice', 'voucher' ) &&
				$is_collective_contact_allowed && 
				self::order_has_non_german_destination( $order ) 
			) {
				$is_collective_contact_allowed = false;
			}
		}

		return $is_collective_contact_allowed;
	}

	/**
	 * Does the order has a non German destination?
	 * 
	 * @param WC_Order $order
	 * @return Boolean
	 */
	public static function order_has_non_german_destination( $order ) {

		$destination_country = $order->get_shipping_country();
		if ( empty( $destination_country ) ) {
			$destination_country = $order->get_billing_country();
		}

		return 'DE' !== $destination_country;
	}

	/**
	* Get allowed tax rates in lexoffice
	*
	* @return Array
	*/
	public static function get_allowed_german_tax_rates() {
		return apply_filters( 'lexoffice_woocomerce_api_allowed_tax_rates', array( 0.0, 7.0, 19.0 ) );
	}

	/**
	 * Does the order has non german taxes?
	 * 
	 * @param WC_Order $order
	 * @return Boolean
	 */
	public static function order_has_non_german_taxes( $order ) {

		$order_has_non_german_taxes = false;

		$allowed_german_tax_rates = self::get_allowed_german_tax_rates();
		$order_taxes = $order->get_taxes();
		foreach ( $order_taxes as $key => $order_tax ) {
				
			$check_tax = floatval( $order_tax->get_rate_percent() );
			
			if ( ! in_array( $check_tax, $allowed_german_tax_rates ) ) {
				$order_has_non_german_taxes = true;
				breaK;
			}
		}

		return $order_has_non_german_taxes;
	}
}
