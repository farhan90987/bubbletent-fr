<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class German_Market_SevDesk_API_Order
 *
 * @author MarketPress
 */
class German_Market_SevDesk_API_Order {

	/**
	* API - send order
	*
	* @param WC_ORDER $order
	* @return String ("SUCCESS" or "ERROR: {your error Message}")
	*/
	public static function send_order( $order, $show_errors = true ) {

		if ( apply_filters( 'sevdesk_woocomerce_api_send_order_dont_send', false, $order ) ) {
			return 'SUCCESS';
		}

		// get all we need, may throws errors and exit
		$api_token = sevdesk_woocommerce_api_get_api_token( $show_errors );

		if ( empty( $api_token ) ) {
			return 'ERROR';
		}
		
		$args = array(
			'api_token'		=> $api_token,
			'base_url'		=> sevdesk_woocommerce_api_get_base_url(),
			'order'			=> sevdesk_woocommerce_api_check_order( $order ),
			'invoice_pdf'	=> sevdesk_woocommerce_api_get_invoice_pdf( $order )
		);
		
		// build temp file, may throws an error and exits
		$temp_file = sevdesk_woocommerce_api_build_temp_file( $args, $show_errors );

		if ( empty( $temp_file ) ) {
			return 'ERROR';
		}

		$args[ 'temp_file' ] = $temp_file;

		// create customer or update user data
		$args[ 'customer' ] = sevdesk_woocommerce_api_contact( $order->get_user_id(), $args );

		do_action( 'sevdesk_woocommerce_api_before_send', $order );

		// send voucher to sevdesk
		$voucher_id = sevdesk_woocommerce_api_send_voucher( $args, $show_errors );

		// save sevdesk id as post meta
		$order->update_meta_data( '_sevdesk_woocomerce_has_transmission', $voucher_id );
		$order->save_meta_data();

		do_action( 'sevdesk_woocommerce_api_after_send', $order );

		return 'SUCCESS';
	}

	/**
	* send order as voucher to sevdesk
	*
	* @param Array $args
	* @return String
	*/
	public static function send_voucher( $args, $show_errors = true ) {

		// init
		$api_version = German_Market_SevDesk_API_V2::get_bookkeeping_system_version();
		$order = $args[ 'order' ];
		$voucherPos = array();
		$sum_totals_splitted = array();
		$sum_totals_splitted_for_shipping = array();
		$total_without_fees_and_shipping_for_shipping = 0.0;
		$total_without_fees_and_shipping = 0.0;
		$total_gross = 0.0;
		$item_tax_rates = array();

		$wc_prices_include_taxes = $order->get_prices_include_tax();
		$sum_key = $wc_prices_include_taxes ? 'sumGross' : 'sumNet';
		$net_attr = $wc_prices_include_taxes ? 'false' : 'true';

		// tax free intracommunity delivery OR tax exempt export delivery
		$tax_type = 'default';
		if ( get_option( 'woocommerce_de_kleinunternehmerregelung', 'off' ) != 'on' ) {
			if ( function_exists( 'wcvat_woocommerce_order_details_status' ) ) {
				$tax_exempt_status = wcvat_woocommerce_order_details_status( $order );
				if ( $tax_exempt_status == 'tax_free_intracommunity_delivery' ) {
					$tax_type = 'eu';
				} else if ( $tax_exempt_status == 'tax_exempt_export_delivery' ) {
					$tax_type = 'noteu';
				}
			}
		}

		// 26 == revenues
		// 27 == sales deduction
		// 41 == rounding differences
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
				'id' => German_Market_SevDesk_API_V2::get_tax_rule_id_by_tax_type( $tax_type, $order ),
				'objectName' => 'TaxRule',
			);
		}

		///////////////////////////////////
		// build voucher positions, 1st: order items
		///////////////////////////////////
		
		$accountingType = array ( 
			
			'id' => apply_filters( 	'woocommerce_de_sevdesk_booking_account_order_items' . $booking_account_filter_suffix, 
									get_option( 'woocommerce_de_sevdesk_booking_account_order_items' . $booking_account_option_suffix, $booking_accounts_default_order ),
									$args ),
			'objectName' => $booking_accounts_object_name
		);

		foreach ( $order->get_items() as $item ) {

			$line_quantity = floatval( $item[ 'qty' ] );
			$item_tax = $order->get_item_tax( $item, false );

			if ( abs( $order->get_line_total( $item, false, false ) ) > 0 ) {
				$tax_rate = round( ( abs( $item_tax ) * abs( $line_quantity ) ) / abs( $order->get_line_total( $item, false, false ) ) * 100, apply_filters( 'sevdesk_woocommerce_api_vat_rate_rounding', 1 ) );
			} else {
				$tax_rate = 0.0;
			}

			// when coupons are applied or an refund has been made later, tax rate is maybe set to zero, correct it in the following lines
			if ( $tax_rate == 0 || ( $tax_rate != 7 && $tax_rate != 19 && $tax_rate != 0.0 ) ) {
				$item_gross = $order->get_line_subtotal( $item, true, false );
				$item_net 	= $order->get_line_subtotal( $item, false, false );
				$item_tax 	= $item_gross - $item_net;

				if ( $item_net > 0 ) {
					$maybe_tax_rate = round( ( $item_tax ) / $item_net * 100, apply_filters( 'sevdesk_woocommerce_api_vat_rate_rounding', 1 ) );
				} else {
					$maybe_tax_rate = 0;
				}

				if ( $maybe_tax_rate > 0 ) {
					$tax_rate = $maybe_tax_rate;
				}

			}

			if ( $tax_rate == 0 || ( $tax_rate != 7 && $tax_rate != 19 && $tax_rate != 0.0 ) ) {

				if ( method_exists( $order, 'get_line_tax' ) && abs( $order->get_line_tax( $item ) ) > 0.0 ) {

					if ( method_exists( $item, 'get_data' ) ) {

						$item_data	= $item->get_data();
						$item_tax	= array();

						$rate_id 	= false;

						if ( isset( $item_data[ 'taxes' ][ 'subtotal' ] ) ) {
							$item_tax = $item_data[ 'taxes' ][ 'subtotal' ];
						} else if ( isset( $item_data[ 'taxes' ][ 'total' ] ) ) {
							$item_tax = $item_data[ 'taxes' ][ 'total' ];
						}

						if ( ! empty( $item_tax ) ) {

							foreach ( $item_tax as $maybe_rate_id => $tax_amount ) {

								if ( empty( $tax_amount ) ) {
									continue;
								}

								$rate_id 	= $maybe_rate_id;
								break;
							}

						}
					
						if ( $rate_id ) {
							if ( floatval( WC_Tax::get_rate_percent( $rate_id ) ) === 0.0 ) {

								$tax_rate_percents = array();
								$order_taxes = $order->get_taxes();

								foreach ( $order_taxes as $key => $order_tax ) {
									$tax_rate_percents[ $order_tax->get_rate_id() ] = $order_tax->get_rate_percent() . '%';
								}

								if ( isset( $tax_rate_percents[ $rate_id ] ) ) {
									$tax_percent = floatval( $tax_rate_percents[ $rate_id ] );
								}

							} else {
								$tax_rate = floatval( WC_Tax::get_rate_percent( $rate_id ) );
							}
							
						}

					}

				}

			}

			$item_tax_rates[ $item->get_id() ] = $tax_rate;

			if ( WGM_Helper::method_exists( $item, 'get_product' ) ) {
				$_product = apply_filters( 'woocommerce_order_item_product', $item->get_product(), $item );
			} else {
				$_product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
			}
			
			$add_to_sum_totals_splitted = true;

			if ( is_object( $_product ) && method_exists( $_product, 'get_id' ) ) {
				if ( WGM_Helper::is_digital( $_product->get_id() ) ) {
					$add_to_sum_totals_splitted = false;
				}
			}

			if ( $add_to_sum_totals_splitted ) {

				if ( ! isset( $sum_totals_splitted_for_shipping[ $tax_rate ] ) ) {
					$sum_totals_splitted_for_shipping[ $tax_rate ] = 0.0;
				}

				$sum_totals_splitted_for_shipping[ $tax_rate ] += $order->get_line_total( $item, false, false );
			}

			if ( ! isset( $sum_totals_splitted[ $tax_rate ] ) ) {
				$sum_totals_splitted[ $tax_rate ] = 0.0;
			}

			$sum_totals_splitted[ $tax_rate ] += $order->get_line_total( $item, false, false );

			// get sku
			$sku = '';
			if ( WGM_Helper::method_exists( $_product, 'get_sku' ) ) {
				$sku = $_product->get_sku();
				if ( $sku != '' ) {
					$sku = ' ' . $sku . ' ';
				}
			}

			$order_account_type = $accountingType;

			if ( get_option( 'woocommerce_de_sevdesk_individual_product_booking_accounts', 'off' ) == 'on' ) {

				if ( WGM_Helper::method_exists( $_product, 'get_meta' ) ) {

					$account_product = ( $_product->get_type() == 'variation' ) ? wc_get_product( $_product->get_parent_id() ) : $_product;

					if ( '1.0' === $api_version ) {
						$order_account = intval( $account_product->get_meta( '_sevdesk_field_order_account' ) );
					} else {
						$order_account = German_Market_SevDesk_API_V2::get_product_datev_booking_account( $account_product, 'order' );
					}

					if ( $order_account > 0 ) {
						$order_account_type = array ( 
							'id' 			=> $order_account,
							'objectName' 	=> $booking_accounts_object_name
						);
					}
				}

			}

			if ( apply_filters( 'sevdesk_woocommerce_api_voucher_use_pos_discount', true ) ) {
				$voucher_pos_sum = $order->get_line_subtotal( $item, $wc_prices_include_taxes, false );
			} else {
				$voucher_pos_sum = $order->get_line_total( $item, $wc_prices_include_taxes, false );
			}

			$voucherPos[] = apply_filters( 'sevdesk_woocommerce_api_voucher_pos', 
				
				array(
					$sum_key 		=> $voucher_pos_sum,
					'net'			=> $net_attr,
					'objectName'	=> 'VoucherPos',
					$booking_accounts_key => $order_account_type,
					'mapAll' 		=> 'true',
					'comment' 		=> sprintf( _x( '%sx%s%s', 'qty x(times) sku item names', 'woocommerce-german-market' ), $item[ 'qty' ], $sku, $item[ 'name' ] ),
					$tax_type_key 	=> $tax_type,
					'taxRate'		=> $tax_rate,
				),

				$item, $args 

			);
			
			if ( $add_to_sum_totals_splitted ) {
				$total_without_fees_and_shipping_for_shipping += $order->get_line_total( $item, false, true );
			}

			$total_without_fees_and_shipping += $order->get_line_total( $item, false, true );
			
			if ( apply_filters( 'sevdesk_woocommerce_api_voucher_use_pos_discount', true ) ) {
				$total_gross += $order->get_line_subtotal( $item, true, true );
			} else {
				$total_gross += $order->get_line_total( $item, true, true );
			}
		}

		///////////////////////////////////
		// build voucher positions, 2nd: discounts (tax splitted)
		///////////////////////////////////
		$accountingType= array ( 
			'id' => $booking_accounts_default_refund,
			'objectName' => $booking_accounts_object_name
		);

		$discount_net_splitted = array();
		$discount_gross_splitted = array();

		foreach ( $order->get_items() as $item ) {

			if ( isset( $item_tax_rates[ $item->get_id() ] ) ) {
				$tax_rate = $item_tax_rates[ $item->get_id() ];
			} else {
				// init
				if ( $order->get_line_total( $item, false, true ) > 0 ) {
					$tax_rate = round( $order->get_line_tax( $item ) / $order->get_line_total( $item, false, true ) * 100, apply_filters( 'sevdesk_woocommerce_api_vat_rate_rounding', 1 ) );
				} else {
					$tax_rate = 0;
				}

				// when coupons are applied, tax rate is maybe set to zero, correct it in the following lines
				if ( $tax_rate == 0 ) {
					$item_gross = $order->get_line_subtotal( $item, true, false );
					$item_net 	= $order->get_line_subtotal( $item, false, false );
					$item_tax 	= $item_gross - $item_net;

					if ( $item_net > 0 ) {
						$maybe_tax_rate = round( ( $item_tax ) / $item_net * 100, 1 );
					} else {
						$maybe_tax_rate = 0;
					}

					if ( $maybe_tax_rate > 0 ) {
						$tax_rate = $maybe_tax_rate;
					}

				}
			}

			if ( ! isset( $discount_net_splitted[ $tax_rate ] ) ) {
				$discount_net_splitted[ $tax_rate ] = 0.0;
			}

			if ( ! isset( $discount_gross_splitted[ $tax_rate ] ) ) {
				$discount_gross_splitted[ $tax_rate ] = 0.0;
			}

			$discount_net 	= $order->get_line_total( $item, false, false ) - $order->get_line_subtotal( $item, false, false );
			$discount_gross	= $order->get_line_total( $item, true, false ) - $order->get_line_subtotal( $item, true, false );

			// continue if there is no disocunt
			if ( ! $discount_net > 0.0 ) {
				continue;
			}

			$discount_net_splitted[ $tax_rate ] += $discount_net;
			$discount_gross_splitted[ $tax_rate ] += $discount_gross;

		}

		foreach ( $discount_net_splitted as $tax_rate => $discount_sum ) {
			
			// continue if there is no discount
			if ( ! $discount_sum > 0.0 ) {
				continue;
			}

			if ( apply_filters( 'sevdesk_woocommerce_api_voucher_use_pos_discount', true ) ) {
				
				$voucherPos[] = apply_filters( 'sevdesk_woocommerce_api_voucher_pos_discount', 
					
					array(
						$sum_key		=> $wc_prices_include_taxes ? $discount_sum / 100 * ( 100 + $tax_rate ) : $discount_sum,
						'net'			=> $net_attr,
						'objectName'	=> 'VoucherPos',
						$booking_accounts_key => $accountingType,
						'mapAll' 		=> 'true',
						'comment' 		=> __( 'Discount', 'woocommerce-german-market' ),
						$tax_type_key 	=> $tax_type,
						'taxRate'		=> $tax_rate,
					),

					$args 
				);

				$total_gross += round( $discount_sum * ( 100 + $tax_rate ) / 100, 2 );
			}

		}

		///////////////////////////////////
		// build voucher positions, 3rd: shipping (tax splitted)
		///////////////////////////////////
		if ( floatval( $order->get_total_shipping() ) > 0.0 ) {
		
			$accountingType= array ( 
				'id' => apply_filters( 'woocommerce_de_sevdesk_booking_account_order_shipping' . $booking_account_filter_suffix, 
						get_option( 'woocommerce_de_sevdesk_booking_account_order_shipping' . $booking_account_option_suffix, $booking_accounts_default_order ),
						$args ),
				'objectName' => $booking_accounts_object_name
			);

			$shipping_split_tax = WGM_Tax::calculate_split_rate( $order->get_total_shipping(), $order, FALSE, '', 'shipping', false, true );

			if ( get_option( 'wgm_use_split_tax', 'on' ) == 'on' ) {

				$shipping_rates = $shipping_split_tax[ 'rates' ];
				
				foreach ( $shipping_rates as $shipping_rate ) {

					if ( $sum_totals_splitted_for_shipping[ floatval( $shipping_rate[ 'rate' ] ) ] >= 0.0 ) {

						// shipping part net
						$this_shipping_part_net 	= round( $sum_totals_splitted_for_shipping[ floatval( $shipping_rate[ 'rate' ] ) ], 2 ) / $total_without_fees_and_shipping_for_shipping * $order->get_total_shipping();

						if ( get_option( 'gm_gross_shipping_costs_and_fees', 'off' ) === 'off' ) {
							$this_shipping_part_net = round( $this_shipping_part_net, 2 );
						}

						$voucherPos[] = apply_filters( 'sevdesk_woocommerce_api_voucher_pos_shipping', 
						
							array(
								$sum_key		=> $wc_prices_include_taxes ? $this_shipping_part_net + $shipping_rate[ 'sum' ] : round( $this_shipping_part_net, 2 ),
								'net'			=> $net_attr,
								'objectName'	=> 'VoucherPos',
								$booking_accounts_key => $accountingType,
								'mapAll' 		=> 'true',
								'comment' 		=> sprintf( __( 'Shipping: %s', 'woocommerce-german-market' ), $order->get_shipping_method() ),
								$tax_type_key 	=> $tax_type,
								'taxRate'		=> round( $shipping_rate[ 'rate' ], 1 ),
							),

							$args 
						);

						$total_gross += round( round( $this_shipping_part_net, 2 ) * ( 100 + $shipping_rate[ 'rate' ] ) / 100.0, 2 );

					}

				}

				if ( empty( $shipping_rates ) ) {

					$voucherPos[] = apply_filters( 'sevdesk_woocommerce_api_voucher_pos_shipping', 
						
						array(
							$sum_key		=> $order->get_total_shipping(),
							'net'			=> $net_attr,
							'objectName'	=> 'VoucherPos',
							$booking_accounts_key => $accountingType,
							'mapAll' 		=> 'true',
							'comment' 		=> sprintf( __( 'Shipping: %s', 'woocommerce-german-market' ), $order->get_shipping_method() ),
							$tax_type_key 	=> $tax_type,
							'taxRate'		=> 0,
						),

						$args 
					);

					$total_gross += round( $order->get_total_shipping(), 2 );

				}

			} else {

				$shippings = $order->get_shipping_methods();
				
				foreach ( $shippings as $shipping ) {

					$shipping_tax = floatval( $shipping->get_total_tax() );
					$shipping_net = floatval( $shipping->get_total() );

					$tax_rate = round( $shipping_tax / $shipping_net * 100, apply_filters( 'sevdesk_woocommerce_api_vat_rate_rounding', 1 ) );

					$voucherPos[] = apply_filters( 'sevdesk_woocommerce_api_voucher_pos_shipping', 
						
						array(
							$sum_key		=> $wc_prices_include_taxes ? $shipping_tax + $shipping_net : $shipping_net, // MAYBE NEW ROUNDING (see $total_gross in L:829)
							'net'			=> $net_attr,
							'objectName'	=> 'VoucherPos',
							$booking_accounts_key => $accountingType,
							'mapAll' 		=> 'true',
							'comment' 		=> sprintf( __( 'Shipping: %s', 'woocommerce-german-market' ), $shipping->get_method_title() ),
							$tax_type_key 	=> $tax_type,
							'taxRate'		=> $tax_rate,
						),

						$args 
					);

				}

				$total_gross += round( $order->get_total_shipping(), 2 ) + $order->get_shipping_tax();

			}

		}

		///////////////////////////////////
		// build voucher positions, 4th: fees (tax splitted)
		///////////////////////////////////
		$accountingType = array ( 
			'id' => apply_filters( 'woocommerce_de_sevdesk_booking_account_order_fees' . $booking_account_filter_suffix, 
					get_option( 'woocommerce_de_sevdesk_booking_account_order_fees' . $booking_account_option_suffix, $booking_accounts_default_order ),
					$args ),
			'objectName' => $booking_accounts_object_name
		);

		// calc total fees
		$fee_total = 0.0;
		$fees = $order->get_fees();
		$fee_names = array();
		foreach ( $fees as $fee ) {
			$fee_names[] = $fee[ 'name' ];
			$fee_total += floatval( $fee[ 'line_total' ] );
		}

		if ( $fee_total > 0.0 ) {

			$fee_label = ( count( $fee_names ) > 1 ) ? __( 'Fees', 'woocommerce-german-market' ) : __( 'Fee', 'woocommerce-german-market' );
			$fee_split_tax = WGM_Tax::calculate_split_rate( $fee_total, $order, FALSE, '', 'fee', false, true );
			$fee_rates = $fee_split_tax[ 'rates' ];

			if ( get_option( 'wgm_use_split_tax', 'on' ) == 'on' && apply_filters( 'sevdesk_woocommerce_api_voucher_pos_fee_use_split_tax', true ) ) {

				foreach ( $fee_rates as $fee_rate ) {

					if ( $sum_totals_splitted[ floatval( $fee_rate[ 'rate' ] ) ] >= 0.0 ) {

						// shipping part net
						$this_fee_part_net 	= round( $sum_totals_splitted[ floatval( $fee_rate[ 'rate' ] ) ], 2 ) / $total_without_fees_and_shipping * $fee_total;

						if ( get_option( 'gm_gross_shipping_costs_and_fees', 'off' ) === 'off' ) {
							$this_fee_part_net = round( $this_fee_part_net, 2 );
						}

						$voucherPos[] = apply_filters( 'sevdesk_woocommerce_api_voucher_pos_fee', 
						
							array(
								$sum_key		=> $wc_prices_include_taxes ? $this_fee_part_net + floatval( $fee_rate[ 'sum' ] ) : $this_fee_part_net,
								'net'			=> $net_attr,
								'objectName'	=> 'VoucherPos',
								$booking_accounts_key => $accountingType,
								'mapAll' 		=> 'true',
								'comment' 		=> sprintf( _x( '%s: %s', 'Example: "Fee: Per Nachnahme" or "Fees: Per Nachnahme, Exportgebühr"', 'woocommerce-german-market' ), $fee_label, implode( ', ', $fee_names ) ),
								$tax_type_key 	=> $tax_type,
								'taxRate'		=> round( $fee_rate[ 'rate' ], 1 ),
							),

							$args 
						);

						$total_gross += round( round( $this_fee_part_net, 2 ) * ( $fee_rate[ 'rate' ] + 100 ) / 100.0, 2 );

					}

				}

				if ( empty( $fee_rates ) ) {

					$voucherPos[] = apply_filters( 'sevdesk_woocommerce_api_voucher_pos_fee', 
						
						array(
							$sum_key		=> $fee_total,
							'net'			=> $net_attr,
							'objectName'	=> 'VoucherPos',
							$booking_accounts_key => $accountingType,
							'mapAll' 		=> 'true',
							'comment' 		=> sprintf( _x( '%s: %s', 'Example: "Fee: Per Nachnahme" or "Fees: Per Nachnahme, Exportgebühr"', 'woocommerce-german-market' ), $fee_label, implode( ', ', $fee_names ) ),
							$tax_type_key 	=> $tax_type,
							'taxRate'		=> 0,
						),

						$args 
					);

					$total_gross += round( $fee_total, 2 );

				}

			} else {

				$fee_label = __( 'Fee', 'woocommerce-german-market' );

				foreach ( $order->get_fees() as $fee ) {

					$fee_taxes = $fee->get_taxes();

					if ( isset( $fee_taxes[ 'total' ] ) && count( $fee_taxes[ 'total' ] ) === 1 ) {
						foreach ( $fee_taxes[ 'total' ] as $rate_id => $tax_amount ) {
							
							$tax_rate = WGM_Tax::get_rate_percent_by_rate_id_and_order( $rate_id, $order );
							$tax_rate = floatval( $tax_rate );
							
							if ( $tax_rate === 0.0 ) {
								$tax_rate = round( $fee->get_total_tax() / $fee->get_total() * 100, 2 );
							}
						}

					} else {
						$tax_rate = round( $fee->get_total_tax() / $fee->get_total() * 100, 2 );
					}
					
					$voucherPos[] = apply_filters( 'sevdesk_woocommerce_api_voucher_pos_fee', 
						
						array(
							$sum_key		=> $wc_prices_include_taxes ? $fee->get_total() + $fee->get_total_tax() : $fee->get_total(),
							'net'			=> $net_attr,
							'objectName'	=> 'VoucherPos',
							$booking_accounts_key => apply_filters( 'sevdesk_woocommerce_api_voucher_pos_fee_accounting_type' . $booking_account_filter_suffix, $accountingType, $fee ),
							'mapAll' 		=> 'true',
							'comment' 		=> sprintf( _x( '%s: %s', 'Example: "Fee: Per Nachnahme" or "Fees: Per Nachnahme, Exportgebühr"', 'woocommerce-german-market' ), $fee_label, $fee->get_name() ),
							$tax_type_key 	=> $tax_type,
							'taxRate'		=> $tax_rate,
						),

						$args 
					);

					$total_gross += round( round( $fee->get_total(), 2 ) * ( $tax_rate + 100 ) / 100.0, 2 );

				}

			}

		}

		///////////////////////////////////
		// build voucher positions, 5th: rounding correction
		///////////////////////////////////

		if ( round( $order->get_total(), 2 ) != round( $total_gross, 2 ) ) {

			$accountingType= array ( 
				'id' => $booking_account_rounding_correction,
				'objectName' => $booking_accounts_object_name
			);

			$voucherPos[] = apply_filters( 'sevdesk_woocommerce_api_voucher_pos_shipping', 
				
				array(
					$sum_key		=> round( $order->get_total() - $total_gross, 2 ),
					'net'			=> $net_attr,
					'objectName'	=> 'VoucherPos',
					$booking_accounts_key => $accountingType,
					'mapAll' 		=> 'true',
					$tax_type_key 	=> $tax_type,
					'taxRate'		=> 0,
					'comment'		=> apply_filters( 'sevdesk_woocommerce_api_voucher_rounding_differences_label', __( 'Rounding differences', 'woocommerce-german-market' ) ),
				),

				$args 
			);

		}

		///////////////////////////////////
		// build voucher
		///////////////////////////////////
		$status_option = apply_filters( 'woocommerce_de_sevdesk_mark_voucher_as_paid_and_do_check_account', get_option( 'woocommerce_de_sevdesk_payment_status', 'completed' ) == 'completed', $order );
		$voucher_paid_status = ( $order->is_paid() && $status_option ) ? 1000 : 100;

		// Get Voucher Date
		$voucher_date = $order->get_date_created()->format( 'Y-m-d' ); // Date Created of Order
		
		// Try to get invoice date
		$invoice_date = $voucher_date;
		$maybe_invoice_date = $order->get_meta( '_wp_wc_running_invoice_number_date' );
		if ( ! empty( $maybe_invoice_date ) ) {
			$invoice_date_time = new DateTime();
			$invoice_date_time->setTimestamp( $maybe_invoice_date );
			$invoice_date = $invoice_date_time->format( 'Y-m-d' );
		}

		if ( apply_filters( 'sevdesk_woocommerce_api_use_invoice_date_as_voucher_date', false ) || ( get_option( 'woocommerce_de_sevdesk_voucher_date', 'order_date' ) == 'invoice_date' ) ) {
			$voucher_date = $invoice_date;
		}

		$voucher_description = get_option( 'sevdesk_voucher_description_order', sevdesk_woocommerce_get_default_value( 'sevdesk_voucher_description_order' ) );
		$voucher_description = str_replace( '{{order-number}}', $args[ 'order']->get_order_number(), $voucher_description );

		$voucher = array(
			
			'voucher'=>array(
				'objectName'	=> 'Voucher',
				'mapAll'		=> 'true',
				'voucherDate'	=> $voucher_date,
				'deliveryDate'	=> apply_filters( 'sevdesk_woocommerce_api_delivery_date_order', $voucher_date, $order ),
				'description'	=> apply_filters( 'sevdesk_woocommerce_api_voucher_description', $voucher_description, $args ),
				'status'		=> 100,
				'comment'		=> 'null',
				'payDate'		=> 'null',
				$tax_type_key 	=> $tax_type,
				'creditDebit'	=> 'D',
				'voucherType'	=> 'VOU',
				'currency'		=> $order->get_currency(),
				'propertyForeignCurrencyDeadline' => $order->get_date_created()->getTimestamp(),
			),

			'filename' => $args[ 'temp_file' ],
			'voucherPosSave' => $voucherPos,
			'voucherPosDelete' => 'null'
		);

		// due date (paymentDeadline)
		$due_date = $args[ 'order' ]->get_meta( '_wgm_due_date' );
		if ( ! empty( $due_date ) ) {
			$voucher[ 'voucher' ][ 'paymentDeadline' ] = $due_date;
		}

		// set customer
		if ( ! is_null( $args[ 'customer' ] ) ) {
			$voucher[ 'voucher' ][ 'supplier' ] = $args[ 'customer' ];
		} else {
			$voucher[ 'voucher' ][ 'supplier' ] = null;
			$voucher[ 'voucher' ][ 'supplierName' ] = apply_filters( 'woocommerce_de_sevdesk_supplier_name', trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ), $args );
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
				echo sevdesk_woocommerce_api_get_error_message( $error_message, $order );
				exit();
			} else {
				error_log( 'German Market sevdesk Add-On: ' . $error_message );
				return '';
			}
		}

		$voucher_id = $result_array[ 'objects' ][ 'voucher' ][ 'id' ];
		
		// if order is paid
		$status_option = apply_filters( 'woocommerce_de_sevdesk_mark_voucher_as_paid_and_do_check_account', get_option( 'woocommerce_de_sevdesk_payment_status', 'completed' ) == 'completed', $order );
		if ( $order->is_paid() && $status_option ) {

			$book_account = apply_filters( 'woocommerce_de_sevdesk_check_account', get_option( 'woocommerce_de_sevdesk_check_account', '' ) );

			// individual check account
			if ( get_option( 'woocommerce_de_sevdesk_individual_gateway_check_accounts', 'off' ) == 'on' ) {
				$payment_method_id = $order->get_payment_method();
				$gateways = WC()->payment_gateways()->payment_gateways();
				if ( isset( $gateways[ $payment_method_id ] ) ) {
					$gateway = $gateways[ $payment_method_id ];
					$gateway_setting = WGM_Payment_Settings::get_option( 'sevdesk_check_account', $gateway );
					if ( ! empty( $gateway_setting ) ) {
						if ( $book_account !== $gateway_setting ) {
							$book_account = intval( $gateway_setting );
						}
					}
				}
			}

			$type = sevdesk_woocommerce_get_type_of_check_account( $book_account );
			
			if ( 'offline' === $type ) {

				if ( $book_account != '' ) {

					$paid_date = $order->get_date_paid();
					
					if ( ! $paid_date ) {
						$paid_date = $order->get_date_completed();
					}

					if ( ! $paid_date ) {
						$paid_date = $order->get_date_created();
					}

					$sum_gross = 0.0;
					foreach ( $result_array[ 'objects' ][ 'voucherPos' ] as $voucherPos_elem ) {
						$sum_gross += $voucherPos_elem[ 'sumGross' ];
					}

					$data = 'Voucher/' . $voucher_id . '/bookAmmount?ammount=' . $sum_gross . '&date=' . $paid_date->format( 'Y-m-d' ) . '&type=null&checkAccount[id]=' . $book_account . '&checkAccount[objectName]=CheckAccount&checkAccountTransaction=null&createFeed=1';

					$ch = curl_init();
					curl_setopt( $ch, CURLOPT_URL, $args[ 'base_url' ] . $data );
					curl_setopt( $ch, CURLOPT_PUT, 1 );
					curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Authorization:' . get_option( 'woocommerce_de_sevdesk_api_token' ) ,'Content-Type:application/x-www-form-urlencoded' ) );
					curl_setopt( $ch, CURLOPT_USERAGENT, sevdesk_woocommerce_get_user_agent() );
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
					curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
					$response = curl_exec( $ch );
					$result_array = json_decode( $response, true );
					curl_close( $ch );

					sevdesk_woocommerce_api_curl_error_validaton( $response );
					$result_array = json_decode( $response, true );

				}
			}

		}

		return $voucher_id;
	}

}
