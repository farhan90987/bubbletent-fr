<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class German_Market_Lexoffice_API_Refund
 *
 * @author MarketPress
 */
class German_Market_Lexoffice_API_Refund {

	/**
	* API - send refund
	*
	* @param WC_ORDER $order
	* @return String ("SUCCESS" or "ERROR: {your error Message}")
	*/
	public static function send_refund( $refund, $show_errors = true ) {

		$refund_lexoffice_status = $refund->get_meta( '_lexoffice_woocomerce_has_transmission' );

		$order_id 				= $refund->get_parent_id();
		$order 					= wc_get_order( $order_id );

		do_action( 'woocommerce_de_lexoffice_api_before_send_refund', $order, $refund );

		if ( $refund_lexoffice_status == '' ) {
			$response = lexoffice_woocomerce_api_send_refund_post( $refund, $show_errors );
		} else {
			$response = lexoffice_woocomerce_api_send_refund_put( $refund, $show_errors );
		}

		do_action( 'woocommerce_de_lexoffice_api_after_send_refund', $order, $refund );

		$response_array = json_decode( $response, true );

		// evaluate response
		if ( ! isset ( $response_array[ 'id' ] ) ) {
			if ( $show_errors ) {
				return '<b>' . __( 'ERROR', 'woocommerce-german-market' ) . ':</b> ' . lexoffice_woocomerce_get_error_text( $response );
			} else {
				return;
			}
		}

		// save sevdesk id as post meta
		$refund->update_meta_data( '_lexoffice_woocomerce_has_transmission', $response_array[ 'id' ] );
		$refund->save_meta_data();

		///////////////////////////////////
		// send refund pdf to lexoffice
		///////////////////////////////////
		$response_invoice_pdf = lexoffice_woocomerce_api_upload_invoice_pdf( $response_array[ 'id' ], $refund, true, $show_errors );
		$response_array = json_decode( $response_invoice_pdf, true );

		return 'SUCCESS';
	}

	/**
	* API - create refund voucher, post method
	*
	* @param WC_ORDER $order
	* @return String
	*/
	public static function send_refund_post( $refund, $show_errors = true ) {

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
				CURLOPT_POSTFIELDS => lexoffice_woocomerce_api_refund_to_curlopt_postfields( $refund, null, $show_errors ),
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
	* API - update refund, put method
	*
	* @param WC_ORDER $order || Refund
	* @return String
	*/
	public static function send_refund_put( $refund, $show_errors = true ) {

		$voucher_id = $refund->get_meta( '_lexoffice_woocomerce_has_transmission' );
		$response_array = lexoffice_woocommerce_api_get_vouchers_status( $voucher_id, false );

		
		if (
				( isset( $response_array[ 'error' ] ) && $response_array[ 'error' ] == 'Not Found' ) ||
				( isset( $response_array[ 'message' ] ) && 'Unauthorized' === $response_array[ 'message' ] ) ||
				empty( $response_array ) || 
				is_null( $response_array ) 
		) {
			return lexoffice_woocomerce_api_send_refund_post( $refund, $show_errors );
		}

		$new_data_for_lexoffice = lexoffice_woocomerce_api_refund_to_curlopt_postfields( $refund, null, $show_errors );
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

		return curl_exec( $curl );

	}

	/**
	* Create Curlopt Postfields from a refund
	*
	* @param WC_Order_Refund $refund
	* @param String $file
	* @return String (JSON formated)
	*/
	public static function refund_to_curlopt_postfields( $refund, $file = null, $show_errors = true ) {

		// init data
		$order_id 				= $refund->get_parent_id();
		$order 					= wc_get_order( $order_id );
		$complete_refund_amount = $refund->get_amount() * ( -1 );
		$item_sum_refunded 		= 0.0;
		$item_tax_refunded 		= 0.0;
		$refund_reason 			= $refund->get_reason() == '' ? '' : sprintf( __( '(%s)', 'woocommerce-german-market' ), $refund->get_reason() );
		$voucher_items 			= array();
		$categoryId	 			= get_option( 'woocommerce_de_kleinunternehmerregelung' ) == 'on' ? '7a1efa0e-6283-4cbf-9583-8e88d3ba5960': '8f8664a8-fd86-11e1-a21f-0800200c9a66';
		$categoryId 			= apply_filters( 'woocommerce_de_lexoffice_category_general', $categoryId );
		$currency				= $order->get_currency();

		if ( get_option( 'woocommerce_de_lexoffice_contacts', 'collective_contact' ) == 'lexoffice_contacts' ) {

			// oss
			$date_created_oss = $order->get_date_created();
			$first_of_july = new DateTime( '2021-07-01 00:00:00' );

			if ( $date_created_oss >= $first_of_july ) {
				// oss
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

		// allowed tax rates, values are net sums of items
		$rates = lexoffice_woocommerce_api_get_all_rates_in_shop();
		$allowed_tax_rates = array();
		foreach ( $rates as $rate ) {
			$allowed_tax_rates[ strval( $rate ) ] = 0.0;
		}
		$allowed_tax_rates = apply_filters( 'lexoffice_woocomerce_api_allowed_tax_rates', $allowed_tax_rates );

		$user = $order->get_user();
		if ( ! $user ) {
			$user_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
		} else {
			$user_name = $user->display_name;
		}

		$voucher_items = array();

		///////////////////////////////////
		// build voucher positions, 1st: order items
		///////////////////////////////////
		foreach ( $refund->get_items() as $item ) {

			if ( ! ( abs( $refund->get_line_total( $item, true, true ) ) > 0.0 ) ) {
				continue;
			}

			$tax_rate = false;

			
			if ( is_object( $item ) && method_exists( $item, 'get_taxes' ) ) {
				$item_data = $item->get_taxes();
				if ( is_array( $item_data ) && isset( $item_data[ 'subtotal' ] ) && is_array( $item_data[ 'subtotal' ] ) && count( $item_data[ 'subtotal' ] ) == 1 ) {

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

			if ( false === $tax_rate ) {

				if ( abs( $refund->get_line_total( $item, false, true ) ) > 0 ) {
					$tax_rate = round( $refund->get_line_tax( $item ) / $refund->get_line_total( $item, false, false ) * 100, 1 );
				} else {
					$tax_rate = 0.0;
				}
				
			}

			if ( ! isset( $allowed_tax_rates[ strval( floatval( $tax_rate ) ) ] ) ) {

				// Fix Problems with discounts and wrong tax_amount, eg:
				// WooCommerce says: Total Net: 0.04, Tax: 0.01 => 25% rate, nonsense.
				// try to find tax amount without discounts
				$line_total_net 	= floatval( $refund->get_line_subtotal( $item, false, false ) );
				$line_total_gross 	= floatval( $refund->get_line_subtotal( $item, true, false ) );

				if ( abs( $line_total_net ) > 0 ) {

					$maybe_tax_rate 			= round( ( $line_total_gross / $line_total_net - 1), 3 ) * 100;
					$max_tax_rate_not_rounded 	= ( $line_total_gross / $line_total_net - 1 ) * 100;

					if ( isset( $allowed_tax_rates[ strval( floatval( $maybe_tax_rate ) ) ] ) ) {
						$tax_rate = abs( $maybe_tax_rate );
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

			$voucher_items[] = array(
				'amount'			=> abs( $refund->get_line_total( $item, true ) ),
				'taxAmount'			=> abs( $refund->get_line_tax( $item ) ),
				'taxRatePercent'	=> $tax_rate,
				'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_item', $categoryId, $item, $order )
			);

			$item_sum_refunded += abs( $refund->get_line_total( $item, true, true ) );
			$item_tax_refunded += abs( $refund->get_line_tax( $item ) );

		}

		///////////////////////////////////
		// Shipping
		///////////////////////////////////
		if ( get_option( 'wgm_use_split_tax', 'on' ) === 'on' && apply_filters( 'woocommerce_de_lexoffice_use_refund_split_tax_calc', true ) ) {

			$refund_shippings = WGM_Tax::get_shipping_or_fee_parts_by_order( $refund, 'shipping' );
			foreach ( $refund_shippings as $refund_shipping ) {
				foreach ( $refund_shipping as $refund_shipping_tax_part ) {

					$voucher_items[] = array(
						'amount'			=> abs( $refund_shipping_tax_part[ 'gross' ] ),
						'taxAmount'			=> abs( $refund_shipping_tax_part[ 'tax_value' ] ),
						'taxRatePercent'	=> abs( $refund_shipping_tax_part[ 'rate_percent' ] ),
						'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_shipping', $categoryId, $order )
					);

					$item_sum_refunded += abs( $refund_shipping_tax_part[ 'gross' ] );
					$item_tax_refunded += abs( $refund_shipping_tax_part[ 'tax_value' ] );
				}
			}

		} else {

			$shipping = floatval( $refund->get_total_shipping() );
			$shipping_tax = floatval( $refund->get_shipping_tax() );
			$shipping_gross = floatval( $shipping + $shipping_tax );

			if ( abs( $shipping_gross ) > 0.0 && abs( $shipping ) > 0.0 ) {

				$shipping_rate = round( $shipping_tax / $shipping * 100, 0 );

				$voucher_items[] = array(
					'amount'			=> abs( $shipping_gross ),
					'taxAmount'			=> abs( $shipping_tax ),
					'taxRatePercent'	=> abs( $shipping_rate ),
					'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_shipping', $categoryId, $order )
				);

				$item_sum_refunded += abs( $shipping_gross );
				$item_tax_refunded += abs( $shipping_tax );
			}

		}

		///////////////////////////////////
		// Fees
		///////////////////////////////////
		if ( get_option( 'wgm_use_split_tax', 'on' ) === 'on' && apply_filters( 'woocommerce_de_lexoffice_use_refund_split_tax_calc', true ) ) {

			$refund_fees = WGM_Tax::get_shipping_or_fee_parts_by_order( $refund, 'fees' );

			foreach ( $refund_fees as $refund_fee ) {
				foreach ( $refund_fee as $refund_fee_tax_part ) {

					$voucher_items[] = array(
						'amount'			=> abs( $refund_fee_tax_part[ 'gross' ] ),
						'taxAmount'			=> abs( $refund_fee_tax_part[ 'tax_value' ] ),
						'taxRatePercent'	=> abs( $refund_fee_tax_part[ 'rate_percent' ] ),
						'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_fee', $categoryId, $order )
					);

					$item_sum_refunded += abs( $refund_fee_tax_part[ 'gross' ] );
					$item_tax_refunded += abs( $refund_fee_tax_part[ 'tax_value' ] );
				}
			}

		} else {
			$fees = $refund->get_fees();

			foreach ( $fees as $fee ) {
				$fee_name 	= $fee[ 'name' ];
				$fee_total	= $fee->get_total();
				$fee_tax 	= $fee->get_total_tax();
				$fee_gross 	= $fee_total + $fee_tax;

				if ( abs( $fee_gross ) > 0.0 ) {

					$fee_rate = round( $fee_tax / $fee_total * 100, 0 );

					$voucher_items[] = array(
						'amount'			=> abs( $fee_gross ),
						'taxAmount'			=> abs( $fee_tax ),
						'taxRatePercent'	=> $fee_rate,
						'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_fee', $categoryId, $order )
					);

					$item_sum_refunded += abs( $fee_gross );
					$item_tax_refunded += abs( $fee_tax );
				}

			}
		}

		///////////////////////////////////
		// general refund item or rounding ocrrection
		///////////////////////////////////
		if ( $item_sum_refunded < abs( $complete_refund_amount ) ) {

			$amount_of_general_refund = ( abs( $complete_refund_amount ) - $item_sum_refunded ) * ( -1 );

			$voucher_items[] = array(
				'amount'			=> abs( $amount_of_general_refund ),
				'taxAmount'			=> 0,
				'taxRatePercent'	=> 0,
				'categoryId'		=> apply_filters( 'woocommerce_de_lexoffice_category_id_fee', $categoryId, $order )
			);

			$item_sum_refunded += abs( $amount_of_general_refund );

		}

		///////////////////////////////////
		/// rebuild voucher items, max. three vouchers, one for each tax rate
		///////////////////////////////////

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

		// due date
		$due_date_days_after_order_date = 0; // init
		$payment_method_id = $order->get_payment_method();
		$gateways = WC()->payment_gateways()->payment_gateways();
		if ( isset( $gateways[ $payment_method_id ] ) ) {
			$gateway = $gateways[ $payment_method_id ];
			$gateway_setting = WGM_Payment_Settings::get_option( 'lexoffice_due_date', $gateway );
			if ( ! empty( $gateway_setting ) ) {
				$due_date_days_after_order_date = intval( $gateway_setting );
			}
		}
		$due_date = clone $refund->get_date_created();
		$voucher_date = apply_filters( 'lexoffice_woocommerce_api_order_voucher_date', $due_date->format( 'Y-m-d' ), $refund );
		$due_date->add( new DateInterval( 'P' . $due_date_days_after_order_date .'D' ) ); // add days

		// build data
		$array = array(
			'type'					=> 'salescreditnote',
			'voucherNumber'			=> apply_filters( 'lexoffice_woocommerce_api_order_voucher_number', $refund->get_id(), $refund ),
			'voucherDate'			=> $voucher_date,
			'dueDate'				=> apply_filters( 'lexoffice_woocomerce_api_refund_due_date', $due_date->format( 'Y-m-d' ), $refund ),
			'totalGrossAmount'		=> round( $total_amount, 2),
			'totalTaxAmount'		=> $total_tax_amount,
			'taxType'				=> 'gross',
			'remark'				=> trim( sprintf( __( 'Refund #%s for Order #%s', 'woocommerce-german-market' ), $refund->get_id(), $order->get_order_number() ) . ' ' . $refund_reason ),
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
		$array = lexoffice_woocommerce_api_add_user_to_voucher( $array, $user, $refund );

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
