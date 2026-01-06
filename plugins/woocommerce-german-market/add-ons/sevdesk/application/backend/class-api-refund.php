<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class German_Market_SevDesk_API_Refund
 *
 * @author MarketPress
 */
class German_Market_SevDesk_API_Refund {

	/**
	* API - send refund
	*
	* @param WC_ORDER $order
	* @return String ("SUCCESS" or "ERROR: {your error Message}")
	*/
	public static function send_refund( $refund, $show_errors = true ) {

		$api_token = sevdesk_woocommerce_api_get_api_token( $show_errors );

		// get all we need, may throws errors and exit
		if ( empty( $api_token ) ) {
			return 'ERROR';
		}

		$args = array(
			'api_token'		=> $api_token,
			'base_url'		=> sevdesk_woocommerce_api_get_base_url(),
			'refund'		=> sevdesk_woocommerce_api_check_order( $refund ),
			'order'			=> wc_get_order( $refund->get_parent_id() ),
			'invoice_pdf'	=> sevdesk_woocommerce_api_get_refund_pdf( $refund )
		);

		$order = wc_get_order( $refund->get_parent_id() );

		// build temp file, may throws an error and exits
		$temp_file = sevdesk_woocommerce_api_build_temp_file( $args, $show_errors );

		if ( empty( $temp_file ) ) {
			return 'ERROR';
		}

		$args[ 'temp_file' ] = $temp_file;

		// create customer or update user data
		$args[ 'customer' ] = sevdesk_woocommerce_api_contact( $order->get_user_id(), $args );

		do_action( 'sevdesk_woocommerce_api_before_send_refund', $order, $refund );

		// send voucher to sevdesk
		$voucher_id = sevdesk_woocommerce_api_send_voucher_refund( $args, $show_errors );

		// save sevdesk id as post meta
		$refund->update_meta_data( '_sevdesk_woocomerce_has_transmission', $voucher_id );
		$refund->save_meta_data();

		do_action( 'sevdesk_woocommerce_api_after_send_refund', $order, $refund );

		return 'SUCCESS';
	}

	/**
	* send refund as voucher to sevdesk
	*
	* @param Array $args
	* @return String
	*/
	public static function send_voucher_refund( $args, $show_errors = true ) {

		// init
		$api_version = German_Market_SevDesk_API_V2::get_bookkeeping_system_version();
		$refund = $args[ 'refund' ];
		$voucherPos = array();
		$complete_refund_amount = $refund->get_amount() * ( -1 );
		$item_sum_refunded = 0.0;
		$refund_reason = $refund->get_reason() == '' ? '' : sprintf( __( '(%s)', 'woocommerce-german-market' ), $refund->get_reason() );

		$wc_prices_include_taxes = $refund->get_prices_include_tax();
		$sum_key = $wc_prices_include_taxes ? 'sumGross' : 'sumNet';
		$net_attr = $wc_prices_include_taxes ? 'false' : 'true';

		// tax free intracommunity delivery OR tax exempt export delivery
		$tax_type = 'default';
		if ( get_option( 'woocommerce_de_kleinunternehmerregelung', 'off' ) != 'on' ) {
			if ( function_exists( 'wcvat_woocommerce_order_details_status' ) ) {
				$tax_exempt_status = wcvat_woocommerce_order_details_status( $args[ 'order' ] );
				if ( $tax_exempt_status == 'tax_free_intracommunity_delivery' ) {
					$tax_type = 'eu';
				} else if ( $tax_exempt_status == 'tax_exempt_export_delivery' ) {
					$tax_type = 'noteu';
				}
			}
		}

		if ( '1.0' === $api_version ) {
			$booking_accounts_object_name = 'AccountingType';
			$booking_accounts_key = 'accountingType';
			$booking_accounts_default_order = 26;
			$booking_accounts_default_refund = 27;
			$booking_account_filter_suffix = '';
			$booking_account_option_suffix = '';
			$booking_account_rounding_correction = 41;
			$tax_type_key = 'taxType';
		} else {
			$booking_accounts_object_name = 'AccountDatev';
			$booking_accounts_key = 'accountDatev';
			$booking_accounts_default_order = 3631;
			$booking_accounts_default_refund = 3712;
			$booking_account_filter_suffix = '_v2';
			$booking_account_option_suffix = '_v2';
			$booking_account_rounding_correction = 3631;
			$tax_type_key = 'taxRule';
			$tax_type = array(
				'id' => German_Market_SevDesk_API_V2::get_tax_rule_id_by_tax_type( $tax_type, $refund ),
				'objectName' => 'TaxRule',
			);
		}

		$accountingType = array ( 
			
			'id' => apply_filters( 	'woocommerce_de_sevdesk_booking_account_refunds' . $booking_account_filter_suffix, 
									get_option( 'woocommerce_de_sevdesk_booking_account_refunds' . $booking_account_option_suffix, $booking_accounts_default_refund ),
									$args ),
			'objectName' => $booking_accounts_object_name
		);

		///////////////////////////////////
		// build voucher positions, 1st: order items
		///////////////////////////////////
		foreach ( $refund->get_items() as $item ) {
			
			if ( ! ( abs( $refund->get_line_total( $item, true, true ) ) > 0.0 ) ) {
				continue;
			} 

			$tax_gross_minus_net    = $refund->get_item_subtotal( $item, true, false ) - $refund->get_item_subtotal( $item, false, false );
			$tax_rate 				= round( $tax_gross_minus_net / $refund->get_item_subtotal( $item, false, true ) * 100, apply_filters( 'sevdesk_woocommerce_api_vat_rate_rounding', 1 ) );

			$refund_account_type = $accountingType;

			if ( get_option( 'woocommerce_de_sevdesk_individual_product_booking_accounts', 'off' ) == 'on' ) {

				if ( WGM_Helper::method_exists( $item, 'get_product' ) ) {
					$_product = apply_filters( 'woocommerce_order_item_product', $item->get_product(), $item );
				} else {
					$_product = apply_filters( 'woocommerce_order_item_product', $refund->get_product_from_item( $item ), $item );
				}

				if ( WGM_Helper::method_exists( $_product, 'get_meta' ) ) {

					$account_product = ( $_product->get_type() == 'variation' ) ? wc_get_product( $_product->get_parent_id() ) : $_product;

					if ( '1.0' === $api_version ) {
						$refund_account = intval( $account_product->get_meta( '_sevdesk_field_refund_account' ) );
					} else {
						$refund_account = German_Market_SevDesk_API_V2::get_product_datev_booking_account( $account_product, 'refund' );
					}

					if ( $refund_account > 0 ) {
						$refund_account_type = array ( 
							'id' 			=> $refund_account,
							'objectName' 	=> $booking_accounts_object_name
						);
					}
				}

			}

			$voucherPos[] = apply_filters( 'sevdesk_woocommerce_api_voucher_pos_refund', 
				
				array(
					$sum_key 		=> abs( $refund->get_line_total( $item, $wc_prices_include_taxes, false ) ),
					'net'			=> $net_attr,
					'objectName'	=> 'VoucherPos',
					$booking_accounts_key => $refund_account_type,
					'mapAll' 		=> 'true',
					'comment' 		=> trim( __( 'Refund', 'woocommerce-german-market' ) . ': ' . $item[ 'name' ] . ' ' . $refund_reason ),
					$tax_type_key	=> $tax_type,
					'taxRate'		=> $tax_rate,
				),
				$item,
				$args
			);

			$item_sum_refunded += abs( $refund->get_line_total( $item, true, true ) );

		}

		///////////////////////////////////
		// Shipping
		///////////////////////////////////
		if ( get_option( 'wgm_use_split_tax', 'on' ) === 'on' && apply_filters( 'sevdesk_woocommerce_use_refund_split_tax_calc', true ) ) {

			$refund_shippings = WGM_Tax::get_shipping_or_fee_parts_by_order( $refund, 'shipping' );
			foreach ( $refund_shippings as $refund_shipping ) {
				foreach ( $refund_shipping as $refund_shipping_tax_part ) {

					$item_sum_refunded += abs( $refund_shipping_tax_part[ 'gross' ]  );

					$shipping_rate = round( abs( $refund_shipping_tax_part[ 'rate_percent' ] ), apply_filters( 'sevdesk_woocommerce_api_vat_rate_rounding', 1 ) );
					
					$voucherPos[] = apply_filters( 'sevdesk_woocommerce_api_voucher_pos_shipping_refund', 
							
						array(
							$sum_key		=> $wc_prices_include_taxes ? abs( $refund_shipping_tax_part[ 'gross' ] ) : abs( $refund_shipping_tax_part[ 'net' ] ),
							'net'			=> $net_attr,
							'objectName'	=> 'VoucherPos',
							$booking_accounts_key => $accountingType,
							'mapAll' 		=> 'true',
							'comment' 		=> sprintf( __( 'Refund Shipping: %s', 'woocommerce-german-market' ), $refund->get_shipping_method() ),
							$tax_type_key	=> $tax_type,
							'taxRate'		=> $shipping_rate,
						),

						$args
					);
				}
			}

		} else {	
			
			$shipping = floatval( $refund->get_total_shipping() );
			$shipping_tax = floatval( $refund->get_shipping_tax() );
			$shipping_gross = floatval( $shipping + $shipping_tax );

			if ( abs( $shipping_gross ) > 0.0 ) {
				
				$item_sum_refunded += abs( $shipping_gross );

				$shipping_rate = round( $shipping_tax / $shipping * 100, apply_filters( 'sevdesk_woocommerce_api_vat_rate_rounding', 1 ) );
				
				$voucherPos[] = apply_filters( 'sevdesk_woocommerce_api_voucher_pos_shipping_refund', 
						
					array(
						$sum_key		=> $wc_prices_include_taxes ? abs( $shipping_gross ) : abs( $shipping ),
						'net'			=> $net_attr,
						'objectName'	=> 'VoucherPos',
						$booking_accounts_key => $accountingType,
						'mapAll' 		=> 'true',
						'comment' 		=> sprintf( __( 'Refund Shipping: %s', 'woocommerce-german-market' ), $refund->get_shipping_method() ),
						$tax_type_key	=> $tax_type,
						'taxRate'		=> $shipping_rate,
					),

					$args
				);
			}
		}

		///////////////////////////////////
		// Fees
		///////////////////////////////////
		if ( get_option( 'wgm_use_split_tax', 'on' ) === 'on' && apply_filters( 'sevdesk_woocommerce_use_refund_split_tax_calc', true ) ) {

			$refund_fees = WGM_Tax::get_shipping_or_fee_parts_by_order( $refund, 'fees' );
			foreach ( $refund_fees as $refund_fee ) {
				foreach ( $refund_fee as $refund_fee_tax_part ) {

					$item_sum_refunded += abs( $refund_fee_tax_part[ 'gross' ] );
					$fee_rate = round( abs( $refund_fee_tax_part[ 'rate_percent' ] ), apply_filters( 'sevdesk_woocommerce_api_vat_rate_rounding', 1 ) );

					$voucherPos[] = apply_filters( 'sevdesk_woocommerce_api_voucher_pos_general_refund', 
						
						array(
							$sum_key		=> $wc_prices_include_taxes ? abs( $refund_fee_tax_part[ 'gross' ] ) : abs( $refund_fee_tax_part[ 'net' ] ),
							'net'			=> $net_attr,
							'objectName'	=> 'VoucherPos',
							$booking_accounts_key => apply_filters( 'sevdesk_woocommerce_api_voucher_pos_fee_accounting_type' . $booking_account_filter_suffix, $accountingType, $refund_fee ),
							'mapAll' 		=> 'true',
							'comment' 		=> sprintf( __( 'Refund Fee: %s', 'woocommerce-german-market' ), $refund_fee_tax_part[ 'name' ] ),
							$tax_type_key 	=> $tax_type,
							'taxRate'		=> $fee_rate,
						),

						$args
					);
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

					$item_sum_refunded += abs( $fee_gross );
					$fee_rate = round( $fee_tax / $fee_total * 100, apply_filters( 'sevdesk_woocommerce_api_vat_rate_rounding', 1 ) );

					$voucherPos[] = apply_filters( 'sevdesk_woocommerce_api_voucher_pos_general_refund', 
						
						array(
							$sum_key		=> $wc_prices_include_taxes ? abs( $fee_gross ) : abs( $fee_total ),
							'net'			=> $net_attr,
							'objectName'	=> 'VoucherPos',
							$booking_accounts_key => apply_filters( 'sevdesk_woocommerce_api_voucher_pos_fee_accounting_type' . $booking_account_filter_suffix, $accountingType, $fee ),
							'mapAll' 		=> 'true',
							'comment' 		=> sprintf( __( 'Refund Fee: %s', 'woocommerce-german-market' ), $fee_name ),
							$tax_type_key	=> $tax_type,
							'taxRate'		=> $fee_rate,
						),

						$args
					);


				}

			}
		}

		///////////////////////////////////
		// general refund item or rounding ocrrection
		///////////////////////////////////
		if ( $item_sum_refunded < abs( $complete_refund_amount ) ) {

			$amount_of_general_refund = ( abs( $complete_refund_amount ) - $item_sum_refunded ) * ( -1 );

			if ( ! abs( round( $amount_of_general_refund, 2 ) == 0.0 ) ) {

				if ( abs( $amount_of_general_refund ) < 0.02 ) {
					$accountingType= array ( 
						'id' => $booking_account_rounding_correction,
						'objectName' => $booking_accounts_object_name
					);
				}

				$voucherPos[] = apply_filters( 'sevdesk_woocommerce_api_voucher_pos_general_refund', 
					
					array(
						$sum_key		=> abs( $amount_of_general_refund ),
						'net'			=> $net_attr,
						'objectName'	=> 'VoucherPos',
						$booking_accounts_key => $accountingType,
						'mapAll' 		=> 'true',
						'comment' 		=> trim( __( 'General Refund', 'woocommerce-german-market' ) . ' ' . $refund_reason ),
						$tax_type_key	=> $tax_type,
						'taxRate'		=> 0,
					),

					$args
				);
			}

		}

		///////////////////////////////////
		// build voucher
		///////////////////////////////////

		$refund_voucher_paid_status = ( $args[ 'order' ]->is_paid() && apply_filters( 'woocommerce_de_sevdesk_mark_refund_as_paid', true ) ) ? 1000 : 100;

		$voucher_description = get_option( 'sevdesk_voucher_description_refund', sevdesk_woocommerce_get_default_value( 'sevdesk_voucher_description_refund' ) );
		$voucher_description = str_replace( '{{order-number}}', $args[ 'order']->get_order_number(), $voucher_description );
		$voucher_description = str_replace( '{{refund-id}}', $refund->get_id(), $voucher_description );

		$voucher = array(
			
			'voucher'=>array(
				'objectName'	=> 'Voucher',
				'mapAll'		=> 'true',
				'voucherDate'	=> apply_filters( 'sevdesk_woocommerce_api_voucher_date', $refund->get_date_created()->format( 'Y-m-d' ), $refund ),
				'deliveryDate'	=> apply_filters( 'sevdesk_woocommerce_api_delivery_date_refund', $refund->get_date_created()->format( 'Y-m-d' ), $refund ),
				'description'	=> apply_filters( 'sevdesk_woocommerce_api_voucher_description', $voucher_description, $args ),
				'status'		=> 100,
				'total'			=> abs( $complete_refund_amount ),
				'comment'		=> 'null',
				'payDate'		=> 'null',
				$tax_type_key	=> $tax_type,
				'creditDebit'	=> 'C',
				'voucherType'	=> 'VOU',
				'currency'		=> $refund->get_currency(),
				'propertyForeignCurrencyDeadline' => $args[ 'order' ]->get_date_created()->getTimestamp(),
			),

			'filename' => $args[ 'temp_file' ],
			'voucherPosSave' => $voucherPos,
			'voucherPosDelete' => 'null'
		);

		// set customer
		if ( ! is_null( $args[ 'customer' ] ) ) {
			$voucher[ 'voucher' ][ 'supplier' ] = $args[ 'customer' ];
		} else {
			$voucher[ 'voucher' ][ 'supplier' ] = null;
			$voucher[ 'voucher' ][ 'supplierName' ] = apply_filters( 'woocommerce_de_sevdesk_supplier_name', trim( $args[ 'order' ]->get_billing_first_name() . ' ' . $args[ 'order' ]->get_billing_last_name() ), $args );
		}

		// filter
		$voucher = apply_filters( 'sevdesk_woocommerce_api_set_voucher', $voucher, $args );

		$ch = curl_init();

		$data = http_build_query( $voucher, '', '&', PHP_QUERY_RFC1738 );

		curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . 'Voucher/Factory/saveVoucher' );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Authorization:' . $args[ 'api_token' ] ,'Content-Type:application/x-www-form-urlencoded' ) );
		curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

		$response = curl_exec( $ch );
		$result_array = json_decode( $response, true );

		curl_close( $ch );

		// error handling
		if ( ! isset( $result_array[ 'objects' ][ 'voucher' ][ 'id' ] ) ) {
			if ( isset( $result_array[ 'error' ][ 'message' ] ) ) {
				$error_message = $result_array[ 'error' ][ 'message' ];
			} else {
				$error_message = __( 'Voucher could not be sent', 'woocommerce-german-market' );
			}

			if ( $show_errors ) {
				echo sevdesk_woocommerce_api_get_error_message( $error_message, $args[ 'order' ] );
				exit();
			} else {
				error_log( 'German Market sevdesk Add-On: ' . $error_message );
				return '';
			}
		}

		$voucher_id = $result_array[ 'objects' ][ 'voucher' ][ 'id' ];

		// if order is paid
		if ( apply_filters( 'woocommerce_de_sevdesk_mark_refund_as_paid', true ) ) {

			$book_account = apply_filters( 'woocommerce_de_sevdesk_check_account', get_option( 'woocommerce_de_sevdesk_check_account', '' ) );

			// individual check account
			if ( get_option( 'woocommerce_de_sevdesk_individual_gateway_check_accounts', 'off' ) == 'on' ) {
				$payment_method_id = $args[ 'order' ]->get_payment_method();
				$gateways = WC()->payment_gateways()->payment_gateways();
				if ( isset( $gateways[ $payment_method_id ] ) ) {
					$gateway = $gateways[ $payment_method_id ];
					$gateway_setting = WGM_Payment_Settings::get_option( 'sevdesk_check_account', $gateway );
					if ( ! empty( $gateway_setting ) ) {
						if ( $gateway_setting != $tax_type ) {
							$book_account = intval( $gateway_setting );
						}
					}
				}
			}

			$type = sevdesk_woocommerce_get_type_of_check_account( $book_account );
			
			if ( 'offline' === $type ) {

				if ( $book_account != '' ) {
					$completed_date = new DateTime();

					$data = 'Voucher/' . $voucher_id . '/bookAmmount?ammount=' . $complete_refund_amount . '&date=' . $completed_date->format( 'Y-m-d' ) . '&type=null&checkAccount[id]=' . $book_account . '&checkAccount[objectName]=CheckAccount&checkAccountTransaction=null&createFeed=1';

					$ch = curl_init();
					curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . $data );
					curl_setopt( $ch, CURLOPT_PUT, 1 );
					curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Authorization:' . get_option( 'woocommerce_de_sevdesk_api_token' ) ,'Content-Type:application/x-www-form-urlencoded' ) );
					curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
					curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
					$response = curl_exec( $ch );
					curl_close( $ch );
					sevdesk_woocommerce_api_curl_error_validaton( $response );

				}
			}

		}

		return $result_array[ 'objects' ][ 'voucher' ][ 'id' ];
	}

}