<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class German_Market_Lexoffice_API_Order
 *
 * @author MarketPress
 */
class German_Market_Lexoffice_API_Order {


	/**
	* API - send voucher
	*
	* @param WC_ORDER $order
	* @return String ("SUCCESS" or "ERROR: {your error Message}")
	*/
	public static function send_voucher( $order, $show_errors = true ) {

		///////////////////////////////////
		// can we start?
		///////////////////////////////////
		if ( ! apply_filters( 'woocommerce_de_lexoffice_force_transmission_even_if_not_completed', false, $order ) ) {

			if ( $order->get_status() != 'completed' ) {
				if ( $show_errors ) {
					return __( '<b>ERROR:</b> Order status is not completed. You can only send data to Lexware Office if the order status is completed.', 'woocommerce-german-market' );
				} else {
					return;
				}
			}

		}

		if ( ! class_exists( 'WP_WC_Invoice_Pdf_Create_Pdf' ) ) {
			if ( $show_errors ) {
				echo '<b>' . __( 'ERROR', 'woocommerce-german-market' ) . ':</b> ' . __( 'To be able to send inbound documents to Lexware Office, the "Invoice PDF" add-on from German Market must be activated.', 'woocommerce-german-market' );
				exit();
			} else {
				return;
			}
		}

		if ( apply_filters( 'woocommerce_de_lexoffice_api_dont_send', false, $order ) ) {
			return;
		}

		$order_lexoffice_status = $order->get_meta( '_lexoffice_woocomerce_has_transmission' );

		do_action( 'woocommerce_de_lexoffice_api_before_send', $order );

		if ( empty( $order_lexoffice_status ) ) {
			$response = lexoffice_woocomerce_api_send_voucher_post( $order, $show_errors );
		} else {
			$response = lexoffice_woocomerce_api_send_voucher_put( $order, $show_errors );
		}

		do_action( 'woocommerce_de_lexoffice_api_after_send', $order );

		$response_array = json_decode( $response, true );

		// evaluate response
		if ( ! isset ( $response_array[ 'id' ] ) ) {
			if ( $show_errors ) {
				return '<br><b>' . __( 'ERROR', 'woocommerce-german-market' ) . ':</b> ' . lexoffice_woocomerce_get_error_text( $response, $order );
			} else {
				return;
			}
		}

		// save lexoffice id as post meta
		$order->update_meta_data( '_lexoffice_woocomerce_has_transmission', $response_array[ 'id' ] );
		$order->save_meta_data();

		// transaction assignment since v3.39
		$transaction = new German_Market_Lexoffice_API_Transaction_Assignment( $order );

		///////////////////////////////////
		// send invoice pdf to lexoffice
		///////////////////////////////////
		$response_invoice_pdf = lexoffice_woocomerce_api_upload_invoice_pdf( $response_array[ 'id' ], $order, false, $show_errors );
		$response_array = json_decode( $response_invoice_pdf, true );

		return 'SUCCESS';
	}

	/**
	* API - create voucher, post method
	*
	* @param WC_ORDER $order
	* @return String
	*/
	public static function send_voucher_post( $order, $show_errors = true ) {

		$token_bucket = new WGM_Token_Bucket( 'lexoffice-vouchers', 2 );
		$token_bucket->consume();

		$curl = curl_init();

		curl_setopt_array( $curl,

			array(
			  	CURLOPT_URL => German_Market_Lexoffice_API_Auth::get_base_url() . "v1/vouchers",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => lexoffice_woocomerce_api_order_to_curlopt_postfields( $order, $show_errors ),
				CURLOPT_HTTPHEADER => array(
				    "accept: application/json",
				    "authorization: Bearer " . lexoffice_woocomerce_api_get_bearer(),
				    "cache-control: no-cache",
				    "content-type: application/json",
				  ),
			)

		);

		return curl_exec( $curl );
	}

	/**
	* API - update voucher, put method
	*
	* @param WC_ORDER $order || Refund
	* @return String
	*/
	public static function send_voucher_put( $order, $show_errors = true ) {

		$voucher_id = $order->get_meta( '_lexoffice_woocomerce_has_transmission' );
		$response_array = lexoffice_woocommerce_api_get_vouchers_status( $voucher_id, false );

		if (
				( isset( $response_array[ 'error' ] ) && $response_array[ 'error' ] == 'Not Found' ) ||
				( isset( $response_array[ 'message' ] ) && 'Unauthorized' === $response_array[ 'message' ] ) ||
				empty( $response_array ) || 
				is_null( $response_array ) 
		) {
			return lexoffice_woocomerce_api_send_voucher_post( $order, $show_errors );
		}

		$new_data_for_lexoffice = lexoffice_woocomerce_api_order_to_curlopt_postfields( $order, $show_errors );
		$new_data_for_lexoffice = json_decode( $new_data_for_lexoffice );
		$new_data_for_lexoffice->version 	= $response_array[ 'version' ];
		$new_data_for_lexoffice->id 		= $response_array[ 'id' ];
		if ( isset( $response_array[ 'organizationId' ] ) ) {
			$new_data_for_lexoffice->organizationId =  $response_array[ 'organizationId' ];
		}

		ini_set( 'serialize_precision', -1 );
		$new_data_for_lexoffice = json_encode( $new_data_for_lexoffice, JSON_PRETTY_PRINT );

		$token_bucket = new WGM_Token_Bucket( 'lexoffice-vouchers', 2 );
		$token_bucket->consume();

		$curl = curl_init();

		curl_setopt_array( $curl,

			array(
			  	CURLOPT_URL => German_Market_Lexoffice_API_Auth::get_base_url() . "v1/vouchers/" . $voucher_id,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "PUT",
				CURLOPT_POSTFIELDS => $new_data_for_lexoffice,
				CURLOPT_HTTPHEADER => array(
				    "accept: application/json",
				    "authorization: Bearer " . lexoffice_woocomerce_api_get_bearer(),
				    "cache-control: no-cache",
				    "content-type: application/json",
				  ),
			)

		);

		$response_post 	= curl_exec( $curl );
		$response_array = json_decode( $response_post, true );

		if ( ! isset( $response_array[ 'id' ] ) ) {

			if ( isset( $response_array[ 'IssueList' ][ 0 ][ 'i18nKey' ] ) ) {
				if ( $response_array[ 'IssueList' ][ 0 ][ 'i18nKey' ] == 'action_forbidden_voucher_state_or_payment' ) {

					if ( $show_errors ) {
						echo '<b>' . __( 'ERROR', 'woocommerce-german-market' ) . ':</b> ' . __( 'The voucher could not be updated. The voucher is may connected with a payment or has been marked as finished (transfered to tax authorities). To update the voucher you can try to remove the connected payment. If the voucher has been transfered to tax authorities it is bocked and you cannot update the voucher.', 'woocommerce-german-market' );

						exit();
					} else {
						return;
					}

				}
			}


		}

		return $response_post;
	}

	/**
	* Create Curlopt Postfields
	*
	* @param WC_ORDER $order
	* @param String $file
	* @return String (JSON formated)
	*/
	public static function order_to_curlopt_postfields( $order, $file = null, $show_errors = true ) {

		// init data
		$user = $order->get_user();
		if ( ! $user ) {
			$user_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
		} else {
			$user_name = $user->display_name;
		}

		// Check Currency, only EUR is supported
		$currency = $order->get_currency();
		if ( 'EUR' !== $currency && apply_filters( 'woocommerce_de_lexoffice_only_euro_is_supported_currency', true ) ) {
			if ( $show_errors ) {
				echo sprintf( __( '"%s" is not a supported currency.', 'woocommerce-german-market' ), $currency );
				exit();
			} else {
				return;
			}
		}

		$tax_total = 0.0;
		$voucher_items = array();

		// allowed tax rates, values are net sums of items
		$rates = lexoffice_woocommerce_api_get_all_rates_in_shop();
		$allowed_tax_rates = array();
		foreach ( $rates as $rate ) {
			$allowed_tax_rates[ strval( $rate ) ] = 0.0;
		}
		$allowed_tax_rates = apply_filters( 'lexoffice_woocomerce_api_allowed_tax_rates', $allowed_tax_rates );

		$categoryId	 = get_option( 'woocommerce_de_kleinunternehmerregelung' ) == 'on' ? '7a1efa0e-6283-4cbf-9583-8e88d3ba5960': '8f8664a8-fd86-11e1-a21f-0800200c9a66';
		$categoryId = apply_filters( 'woocommerce_de_lexoffice_category_general', $categoryId );

		$is_collective_contact_allowed = German_Market_Lexoffice_API_General::is_collective_contact_allowed_for_order( $order );

		if ( 
			( 'lexoffice_contacts' === get_option( 'woocommerce_de_lexoffice_contacts', 'collective_contact' ) ) ||
			( ! $is_collective_contact_allowed )
		) {	

			// oss
			$date_created_oss = $order->get_date_created();
			$first_of_july = new DateTime( '2021-07-01 00:00:00' );

			if ( $date_created_oss >= $first_of_july ) {
				$oss_shipping_country = $order->get_shipping_country();
				if ( empty( $oss_shipping_country ) ) {
					$oss_shipping_country = $order->get_billing_country();
				}

				if ( 'DE' !== $oss_shipping_country ) {

					$eu_countries = WC()->countries->get_european_union_countries();
					
					if ( in_array( $oss_shipping_country, $eu_countries ) ) {
						$oss_info = lexoffice_woocommerce_api_get_oss_info();

						if ( 'destination' === $oss_info ) {
							$categoryId = '4ebd965a-7126-416c-9d8c-a5c9366ee473';
						} else if ( 'origin' === $oss_info ) {
							$categoryId = '7c112b66-0565-479c-bc18-5845e080880a';
						}

						$categoryId = apply_filters( 'woocommerce_de_lexoffice_category_eu', $categoryId );

					}
				}
			}

			// tax free intracommunity delivery OR tax exempt export delivery
			if ( get_option( 'woocommerce_de_kleinunternehmerregelung', 'off' ) != 'on' ) {
				if ( function_exists( 'wcvat_woocommerce_order_details_status' ) ) {
					$tax_exempt_status = wcvat_woocommerce_order_details_status( $order );
					if ( $tax_exempt_status == 'tax_free_intracommunity_delivery' ) {

						if ( apply_filters( 'woocommerce_de_lexoffice_tax_free_intracommunity_delivery_empty_company', ( ! empty( $order->get_billing_company() ) ) ) ) {
							$categoryId = '9075a4e3-66de-4795-a016-3889feca0d20';
							$categoryId = apply_filters( 'woocommerce_de_lexoffice_category_tax_free_intracommunity_delivery', $categoryId );
						}

					} else if ( $tax_exempt_status == 'tax_exempt_export_delivery' ) {
						
						$categoryId = '93d24c20-ea84-424e-a731-5e1b78d1e6a9';
						$categoryId = apply_filters( 'woocommerce_de_lexoffice_category_tax_exempt_export_delivery', $categoryId );

					}
				}
			}
		}

		///////////////////////////////////
		// first check if there is any item free of taxes
		///////////////////////////////////
		$tax_free_items = false;
		foreach ( $order->get_items() as $item ) {
			$tax = floatval( $order->get_line_tax( $item, false ) );
			if ( ! ( $tax > 0.0 ) ) {
				$tax_free_items = true;
			}
		}

		///////////////////////////////////
		// add order items as voucher items
		///////////////////////////////////
		$items = $order->get_items();
		foreach ( $items as $item ) {

			$tax_rate = false;

			$line_total = floatval( $order->get_line_total( $item, false, false ) );
			$line_tax_total = floatval( $order->get_line_tax( $item, false ) );

			if ( abs( $line_total ) > 0 ) {

				if ( is_object( $item ) && method_exists( $item, 'get_taxes' ) ) {
					$item_data = $item->get_taxes();
					if ( is_array( $item_data ) && isset( $item_data[ 'subtotal' ] ) && is_array( $item_data[ 'subtotal' ] ) ) {
						foreach ( $item_data[ 'subtotal' ] as $rate_key => $item_data_subtotal ) {
							if ( abs( floatval( $item_data_subtotal ) ) > 0 ) {
								$maybe_tax_rate = floatval( str_replace( '%', '', WC_Tax::get_rate_percent( $rate_key ) ) );
								if ( $maybe_tax_rate > 0 ) {
									if ( isset( $allowed_tax_rates[ strval( floatval( $maybe_tax_rate ) ) ] ) ) {
										$tax_rate = $maybe_tax_rate;
										break;
									}
								}
							}
						}
					}
				}	
			}

			if ( false === $tax_rate ) {

				if ( abs( $line_total ) > 0 ) {
					$tax_rate = round( ( $line_tax_total / ( $line_total ) ), 3 ) * 100;
				} else {

					if ( $line_total != 0.0 ) {
						$tax_rate = round( ( $line_tax_total / ( $line_total ) ), 3 ) * 100;
					} else {
						$tax_rate = 0.0;
					}

				}
			}

			if ( ! isset( $allowed_tax_rates[ strval( floatval( $tax_rate ) ) ] ) ) {

				// Fix Problems with discounts and wrong tax_amount, eg:
				// WooCommerce says: Total Net: 0.04, Tax: 0.01 => 25% rate, nonsense.
				// try to find tax amount without discounts
				$line_total_net 	= floatval( $order->get_line_subtotal( $item, false, false ) );
				$line_total_gross 	= floatval( $order->get_line_subtotal( $item, true, false ) );

				if ( abs( $line_total_net ) > 0 ) {

					$maybe_tax_rate 	= round( ( $line_total_gross/$line_total_net - 1), 3 ) * 100;
					$max_tax_rate_not_rounded = ( $line_total_gross/$line_total_net - 1 ) * 100;

					if ( isset( $allowed_tax_rates[ strval( floatval( $maybe_tax_rate ) ) ] ) ) {
						$tax_rate = $maybe_tax_rate;
					}

				}

			}

			// fix problems with small amounts 1
			if ( ! isset( $allowed_tax_rates[ strval( floatval( $tax_rate ) ) ] ) ) {
				$item_data = $item->get_taxes();
				if ( isset( $item_data[ 'subtotal' ] ) && is_array( $item_data[ 'subtotal' ] ) && count( $item_data[ 'subtotal' ] ) == 1 ) {

					if ( function_exists( 'array_key_first' ) ) {
						$tax_rate_id = array_key_first( $item_data[ 'subtotal' ] );
					} else {
						foreach ( $item_data[ 'subtotal' ] as $key => $value ) {
							$tax_rate_id = $key;
							break;
						}
					}

					$maybe_tax_rate = floatval( str_replace( '%', '', WC_Tax::get_rate_percent( $tax_rate_id ) ) );
					if ( isset( $allowed_tax_rates[ strval( floatval( $maybe_tax_rate ) ) ] ) ) {
						$tax_rate = $maybe_tax_rate;
					}
				}
			}

			// fix problems with small amounts 2
			if ( ! isset( $allowed_tax_rates[ strval( floatval( $tax_rate ) ) ] ) ) {

				$order_taxes =  $order->get_taxes();
				foreach ( $order_taxes as $tax ) {
					$tax_rate_percent = intval(  WC_Tax::get_rate_percent( $tax->get_rate_id() ) );
					if ( abs( $tax_rate_percent - $max_tax_rate_not_rounded ) <  1.0 ) {
						$tax_rate = $tax_rate_percent;
					}
				}
			}

			$tax_rate = apply_filters( 'woocommerce_de_lexoffice_tax_rate_before_check', $tax_rate, $item );

			// add for split tax calculations later
			$allowed_tax_rates[ strval( floatval( $tax_rate ) ) ] += $order->get_line_total( $item, false );

			// add tax to tax total
			$tax_total += $order->get_line_tax( $item );

			$voucher_items[] = array(
				'amount'			=> $order->get_line_total( $item, true ),
				'taxAmount'			=> $order->get_line_tax( $item ),
				'taxRatePercent'	=> $tax_rate,
				'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_item', $categoryId, $item, $order )
			);
		}

		// order total without fees and shipping
		$total_without_fees_and_shipping = array_sum( $allowed_tax_rates );

		///////////////////////////////////
		// add shipping as voucher items, regading split tax
		///////////////////////////////////
		$shippings = $order->get_items( 'shipping' );

		foreach ( $shippings as $shipping ) {

			$shipping_net_total = 0;
			$shipping_tax 		= $shipping->get_taxes();

			// check if there are no taxes
			if ( ! ( array_sum( $shipping_tax[ 'total' ] ) ) > 0.0 ) {

				$voucher_items[] = array(
					'amount'			=> $shipping->get_total(),
					'taxAmount'			=> 0.0,
					'taxRatePercent'	=> 0.0,
					'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_shipping', $categoryId, $order )
				);

				continue;
			}

			if ( apply_filters( 'woocommerce_de_lexoffice_use_split_tax_shiping_taxes', false ) ) {

				add_filter( 'gm_split_tax_rounding_precision', function( $precision ) {
					return 100;
				});

				$use_split_tax = get_option( WGM_Helper::get_wgm_option( 'wgm_use_split_tax' ), 'on' );

				if ( 'on' === $use_split_tax ) {


					$shipping_split_tax = WGM_Tax::calculate_split_rate( $order->get_total_shipping(), $order, FALSE, '', 'shipping', true, false );

					$shipping_tax[ 'total' ] = array();
					foreach ( $shipping_split_tax[ 'rates' ] as $key => $infos ) {
						$shipping_tax[ 'total' ][ $key ] = $infos[ 'sum' ];
					}

				}
			}

			$net_parts 	  			= array();
			$net_parts_not_rounded	= array();
			$tax_parts				= array();

			$biggest_amount_for_rounding_corrections_key 	= null;
			$biggest_amount_for_rounding_corrections_value 	= 0;

			$smallest_amount_for_rounding_corrections_key 	= null;
			$smallest_amount_for_rounding_corrections_value = 0;

			foreach ( $shipping_tax[ 'total' ] as $rate_id => $rate_amount ) {

				if ( empty( $rate_amount ) ) {
					continue;
				}

				$percent = str_replace( '%', '', WGM_Tax::get_rate_percent_by_rate_id_and_order( $rate_id, $order ) );
				$percent = floatval( str_replace( ',', '.', $percent ) );

				$net_parts_not_rounded[ strval( $percent ) ]  = $rate_amount / $percent * 100;
				$net_parts[ strval( $percent ) ]	= round( $net_parts_not_rounded[ strval( $percent ) ], 2 );
				$tax_parts[ strval( $percent ) ]	= $rate_amount;

				// maybe we have to do a rounding correction
				if ( $rate_amount >= $biggest_amount_for_rounding_corrections_value ) {
					$biggest_amount_for_rounding_corrections_value = $rate_amount;
					$biggest_amount_for_rounding_corrections_key   = $percent;
				}

				if ( ! $smallest_amount_for_rounding_corrections_key ) {

					$smallest_amount_for_rounding_corrections_value = $rate_amount;
					$smallest_amount_for_rounding_corrections_key 	= $percent;

				} else {

					if ( $rate_amount <= $smallest_amount_for_rounding_corrections_value ) {
						$smallest_amount_for_rounding_corrections_value = $rate_amount;
						$smallest_amount_for_rounding_corrections_key 	= $percent;
					}

				}

			}

			$sum_of_nets = array_sum( $net_parts );
			$sum_of_nets_not_rounded = round( array_sum( $net_parts_not_rounded ), 2 );

			// correction if there is just one tax rate and percent calculation did wrong rounding
			if ( count( $net_parts_not_rounded ) == 1 ) {

				if ( ! $tax_free_items ) {
					foreach ( $net_parts_not_rounded as $key => $value ) {
						$net_parts_not_rounded[ $key ] = $shipping->get_total();
						$net_parts[ $key ] = $shipping->get_total();
					}

					$sum_of_nets = array_sum( $net_parts );
					$sum_of_nets_not_rounded = round( array_sum( $net_parts_not_rounded ), 2 );
				}
			}

			// do we have a shipping part free of taxes?
			if ( $tax_free_items ) {
				if ( floatval( $shipping->get_total() ) != $sum_of_nets_not_rounded ) {

					$last_shipping_part = $shipping->get_total() - $sum_of_nets_not_rounded;
					$net_parts_not_rounded[ 0 ] = $last_shipping_part;
					$net_parts[ 0 ]				= round( $net_parts_not_rounded[ 0 ], 2 );
					$tax_parts[ 0 ]				= 0.0;

					if ( $last_shipping_part >= $biggest_amount_for_rounding_corrections_value ) {
						$biggest_amount_for_rounding_corrections_value = $last_shipping_part;
						$biggest_amount_for_rounding_corrections_key   = 0;
					}

					$sum_of_nets = array_sum( $net_parts );
					$sum_of_nets_not_rounded = round( array_sum( $net_parts_not_rounded ), 2 );

				}

			}

			// maybe we have to do a rounding correction in some of the parts
			if ( $sum_of_nets != floatval( $shipping->get_total() ) ) {

				$diff = round( floatval( $shipping->get_total() ) - $sum_of_nets, 2 );
				if ( $smallest_amount_for_rounding_corrections_key ) {
					$net_parts[ strval( $smallest_amount_for_rounding_corrections_key ) ] += $diff;
				}

			}

			foreach ( $net_parts as $percent_string => $amount ) {

				$percent = floatval( $percent_string );

				$voucher_items[] = array(
					'amount'			=> round( $amount + $tax_parts[ $percent_string ], 2 ),
					'taxAmount'			=> round( $tax_parts[ $percent_string ], 2 ),
					'taxRatePercent'	=> $percent,
					'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_shipping', $categoryId, $order )
				);

			}
		}

		///////////////////////////////////
		// add fees as voucher items, regading split tax
		///////////////////////////////////
		$fees = $order->get_items( 'fee' );

		foreach ( $fees as $fee ) {

			$fee_net_total  = 0;
			$fee_tax 		= $fee->get_taxes();

			// check if there are no taxes
			if ( ! ( array_sum( $fee_tax[ 'total' ] ) ) > 0.0 ) {

				$voucher_items[] = array(
					'amount'			=> round( $fee->get_total(), 2 ),
					'taxAmount'			=> 0.0,
					'taxRatePercent'	=> 0.0,
					'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_shipping', $categoryId, $order )
				);

				continue;
			}

			$net_parts 	  			= array();
			$net_parts_not_rounded	= array();
			$tax_parts				= array();

			$biggest_amount_for_rounding_corrections_key 	= null;
			$biggest_amount_for_rounding_corrections_value 	= 0;

			$smallest_amount_for_rounding_corrections_key 	= null;
			$smallest_amount_for_rounding_corrections_value = 0;

			foreach ( $fee_tax[ 'total' ] as $rate_id => $rate_amount ) {

				if ( empty( $rate_amount ) ) {
					continue;
				}

				$percent = str_replace( '%', '', WGM_Tax::get_rate_percent_by_rate_id_and_order( $rate_id, $order ) );
				$percent = floatval( str_replace( ',', '.', $percent ) );

				$net_parts_not_rounded[ strval( $percent ) ] 	= $rate_amount / $percent * 100;
				$net_parts[ strval( $percent ) ]				= round( $net_parts_not_rounded[ strval( $percent ) ], 2 );
				$tax_parts[ strval( $percent ) ]				= $rate_amount;

				// maybe we have to do a rounding correction
				if ( $rate_amount >= $biggest_amount_for_rounding_corrections_value ) {
					$biggest_amount_for_rounding_corrections_value = $rate_amount;
					$biggest_amount_for_rounding_corrections_key   = $percent;
				}

				if ( ! $smallest_amount_for_rounding_corrections_key ) {

					$smallest_amount_for_rounding_corrections_value = $rate_amount;
					$smallest_amount_for_rounding_corrections_key 	= $percent;

				} else {

					if ( $rate_amount <= $smallest_amount_for_rounding_corrections_value ) {
						$smallest_amount_for_rounding_corrections_value = $rate_amount;
						$smallest_amount_for_rounding_corrections_key 	= $percent;
					}

				}

			}

			$sum_of_nets = array_sum( $net_parts );
			$sum_of_nets_not_rounded = round( array_sum( $net_parts_not_rounded ), 2 );

			// correction if there is just one tax rate and percent calculation did wrong rounding
			if ( count( $net_parts_not_rounded ) == 1 ) {

				if ( ! $tax_free_items ) {
					foreach ( $net_parts_not_rounded as $key => $value ) {
						$net_parts_not_rounded[ $key ] = $fee->get_total();
						$net_parts[ $key ] = $fee->get_total();
					}

					$sum_of_nets = array_sum( $net_parts );
					$sum_of_nets_not_rounded = round( array_sum( $net_parts_not_rounded ), 2 );
				}
			}

			// do we have a fee part free of taxes?
			if ( $tax_free_items ) {
				if ( floatval( $fee->get_total() ) != $sum_of_nets_not_rounded ) {

					$last_fee_part 				= $fee->get_total() - $sum_of_nets_not_rounded;
					$net_parts_not_rounded[ 0 ] = $last_fee_part;
					$net_parts[ 0 ]				= round( $net_parts_not_rounded[ 0 ], 2 );
					$tax_parts[ 0 ]				= 0.0;

					if ( $last_fee_part >= $biggest_amount_for_rounding_corrections_value ) {
						$biggest_amount_for_rounding_corrections_value = $last_fee_part;
						$biggest_amount_for_rounding_corrections_key   = 0;
					}

					$sum_of_nets = array_sum( $net_parts );
					$sum_of_nets_not_rounded = round( array_sum( $net_parts_not_rounded ), 2 );

				}
			}

			// maybe we have to do a rounding correction in some of the parts
			if ( $sum_of_nets != floatval( $fee->get_total() ) ) {

				$diff = round( floatval( $fee->get_total() ) - $sum_of_nets, 2 );

					if ( $smallest_amount_for_rounding_corrections_key ) {
						$net_parts[ strval( $smallest_amount_for_rounding_corrections_key ) ] += $diff;
					}

			}

			foreach ( $net_parts as $percent_string => $amount ) {

				$percent = floatval( $percent_string );

				$voucher_items[] = array(
					'amount'			=> round( $amount + $tax_parts[ $percent_string ], 2 ),
					'taxAmount'			=> $tax_parts[ $percent_string ],
					'taxRatePercent'	=> $percent,
					'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_fees', $categoryId, $order )
				);

			}

		}

		///////////////////////////////////
		/// rebuild voucher items, max. three vouchers, one for each tax rate
		///////////////////////////////////

		// correction for items < 0
		// @since GM 3.6.3
		foreach ( $voucher_items as $key => $voucher_item ) {

			if ( $voucher_item[ 'taxRatePercent' ] == 0 ) {

				if ( $voucher_item[ 'amount' ] > 0.0 ) {

					$test_rate_percent = round( $voucher_item[ 'taxAmount' ] / ( $voucher_item[ 'amount' ] - $voucher_item[ 'taxAmount' ] ) * 100 );

					if ( in_array( $test_rate_percent, lexoffice_woocommerce_api_get_all_rates_in_shop() ) ) {
						$voucher_items[ $key ][ 'taxRatePercent' ] = $test_rate_percent;
					}

				}

			}

		}

		if ( apply_filters( 'lexoffice_rebuild_voucher_items', true ) ) {

			// init
			$rates = lexoffice_woocommerce_api_get_all_rates_in_shop();
			$voucher_items_rebuild_helper = array();
			foreach ( $rates as $rate ) {
				$voucher_items_rebuild_helper[ strval( $rate ) ] = array(
						'amount'			=> 0.0,
						'taxAmount'			=> 0.0,
						'taxRatePercent'	=> $rate,
						'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_split', $categoryId, $order )
					);
			}
			$voucher_items_rebuild_helper = apply_filters( 'lexoffice_woocomerce_api_voucher_items_rebuild', $voucher_items_rebuild_helper );

			// rebuild
			foreach ( $voucher_items as $voucher_item ) {
				$voucher_items_rebuild_helper[ strval( floatval( $voucher_item[ 'taxRatePercent' ] ) ) ][ 'amount' ] += floatval( $voucher_item[ 'amount' ] );
				$voucher_items_rebuild_helper[ strval( floatval( $voucher_item[ 'taxRatePercent' ] ) ) ][ 'taxAmount' ] += floatval( $voucher_item[ 'taxAmount' ] );
			}

			// check if amount > 0
			$voucher_items_rebuild = array(); // rebuild
			$total_tax_amount = 0.0;
			$total_amount = 0.0;
			foreach ( $voucher_items_rebuild_helper as $voucher_item_rebuild_helper ) {

				$voucher_item_rebuild_helper[ 'taxAmount' ] = $voucher_item_rebuild_helper[ 'amount' ] / ( 100.0 + $voucher_item_rebuild_helper[ 'taxRatePercent' ] ) * $voucher_item_rebuild_helper[ 'taxRatePercent' ];
				$voucher_item_rebuild_helper[ 'taxAmount' ] = round( $voucher_item_rebuild_helper[ 'taxAmount' ], 2 );
				$voucher_item_rebuild_helper[ 'amount' ] = round( $voucher_item_rebuild_helper[ 'amount' ], 2 );

				$total_tax_amount += $voucher_item_rebuild_helper[ 'taxAmount' ];
				$total_amount += round( $voucher_item_rebuild_helper[ 'amount' ], 2 );

				if ( $voucher_item_rebuild_helper[ 'amount' ] > 0.0 ) {
					$voucher_items_rebuild[] = $voucher_item_rebuild_helper;
				}

			}

		} else {

			$voucher_items_rebuild = $voucher_items;
			$total_tax_amount = 0.0;
			$total_amount = 0.0;
			foreach ( $voucher_items_rebuild as $key => $voucher_item ) {
				$voucher_items_rebuild[ $key ][ 'taxAmount' ]	= round( $voucher_item[ 'taxAmount' ], 2 );
				$voucher_items_rebuild[ $key ][ 'amount' ]		= round( $voucher_item[ 'amount' ], 2 );
				$total_tax_amount += $voucher_items_rebuild[ $key ][ 'taxAmount' ];
				$total_amount += $voucher_items_rebuild[ $key ][ 'amount' ];
			}

		}

		///////////////////////////////////
		// rounding error handling
		///////////////////////////////////
		if ( round( $total_amount, wc_get_price_decimals() ) != round( $order->get_total(), wc_get_price_decimals() ) ) {
			$difference = round( $order->get_total(), wc_get_price_decimals() ) - round( $total_amount, wc_get_price_decimals() );
			$difference = round( $difference, wc_get_price_decimals() );

			$rounding_error_handling_condition = $difference > 0.0;
			$rounding_error_handling_condition = apply_filters( 'lexoffice_woocomerce_api_voucher_items_rebuild_rounding_error_handling_condition', $rounding_error_handling_condition, $difference );

			if ( $rounding_error_handling_condition ) {

				$rounding_category_id = 'aba9020f-d0a6-47ca-ace6-03d6ed492351';

				if ( '9075a4e3-66de-4795-a016-3889feca0d20' === $categoryId || '93d24c20-ea84-424e-a731-5e1b78d1e6a9' === $categoryId ) {
					$rounding_category_id = $categoryId;
				}

				$voucher_items_rebuild[] = array(
						'amount'			=>	$difference,
					    'taxAmount'			=>	0.0,
					    'taxRatePercent'	=>	0.0,
					    'categoryId'		=>  apply_filters( 'woocommerce_de_lexoffice_category_rounding_correction', $categoryId, $order )
				);

				$total_amount = round( $order->get_total(), wc_get_price_decimals() );

			}

		}

		///////////////////////////////////
		// build array for order
		///////////////////////////////////

		// due date
		$due_date_meta_data = $order->get_meta( '_wgm_due_date' );

		if ( $due_date_meta_data == '' ) {

			$due_date_days_after_order_date = 0; // init
			$payment_method_id = $order->get_payment_method();
			$gateways = WC()->payment_gateways()->payment_gateways();
			if ( isset( $gateways[ $payment_method_id ] ) ) {
				$gateway = $gateways[ $payment_method_id ];
				$gateway_setting = WGM_Payment_Settings::get_option( 'lexoffice_due_date', $gateway );
				if ( ! empty( $gateway_setting ) ) {
					$due_date_days_after_order_date = intval( $gateway_setting );
				} else {

					$current_payment_gateway = $gateway->id;

					if ( $current_payment_gateway == 'bacs' ) {
						$due_date_days_after_order_date = 10;
					} else if ( $current_payment_gateway == 'cheque' ) {
						$due_date_days_after_order_date = 14;
					} else if ( $current_payment_gateway == 'paypal' ) {
						$due_date_days_after_order_date = 0;
					} else if ( $current_payment_gateway == 'cash_on_delivery' ) {
						$due_date_days_after_order_date = 7;
					} else if ( $current_payment_gateway == 'german_market_purchase_on_account' ) {
						$due_date_days_after_order_date = 30;
					} else {
						$due_date_days_after_order_date = 0;
					}
				}
			}

			$due_date = clone $order->get_date_created();
			$voucher_date = apply_filters( 'lexoffice_woocommerce_api_order_voucher_date', $due_date->format( 'Y-m-d' ), $order );
			$due_date = new DateTime( $voucher_date );
			$due_date->add( new DateInterval( 'P' . $due_date_days_after_order_date .'D' ) ); // add days
			$due_date_meta_data = $due_date->format( 'Y-m-d' );

		} else {

			// due date is set as meta
			$date_created = clone $order->get_date_created();
			$voucher_date = apply_filters( 'lexoffice_woocommerce_api_order_voucher_date', $date_created->format( 'Y-m-d' ), $order );
		}

		// build data
		$array = array(
			'type'					=> 'salesinvoice',
			'voucherNumber'			=> apply_filters( 'lexoffice_woocommerce_api_order_voucher_number', $order->get_order_number(), $order ),
			'voucherDate'			=> $voucher_date,
			'dueDate'				=> apply_filters( 'lexoffice_woocomerce_api_order_due_date', $due_date_meta_data, $order ),
			'totalGrossAmount'		=> round( $total_amount, 2 ),
			'totalTaxAmount'		=> round( $total_tax_amount, 2 ),
			'taxType'				=> 'gross',
			'remark'				=> sprintf( __( 'Order from %s', 'woocommerce-german-market' ), $user_name ),
			'voucherItems'			=> $voucher_items_rebuild,
		);

		// an order with toal 0 and empty voucher_items cannot be send to lexoffice
		if ( $total_amount == 0.0 && empty( $voucher_items_rebuild ) ) {
			if ( is_admin() && wp_doing_ajax() ) {
				if ( $show_errors ) {
					echo sprintf( __( '<b>ERROR:</b> You cannot send an order to Lexware Office that has a total of 0,00 %s', 'woocommerce-german-market' ), get_woocommerce_currency_symbol() );
					exit();
				} else {
					return;
				}
			} else {
				error_log( sprintf( __( '<b>ERROR:</b> You cannot send an order to Lexware Office that has a total of 0,00 %s', 'woocommerce-german-market' ), get_woocommerce_currency_symbol() ) );
				return;
			}
		}

		// add user or collective contact to voucher
		$array = lexoffice_woocommerce_api_add_user_to_voucher( $array, $user, $order );

		// add invoice pdf
		if ( $file ) {
			$array[ 'voucherImages' ] = array( $file );
		}

		// filter
		$array = apply_filters( 'lexoffice_woocomerce_api_order_to_curlopt_postfields_array', $array, $order, $voucher_items_rebuild, $voucher_items );

		ini_set( 'serialize_precision', -1 );
		$json = json_encode( $array, JSON_PRETTY_PRINT );

		return $json;
	}
	
}
