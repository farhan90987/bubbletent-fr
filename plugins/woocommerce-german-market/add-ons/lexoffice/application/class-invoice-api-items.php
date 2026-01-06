<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} 

if ( ! class_exists( 'German_Market_Lexoffice_Invoice_API_Items' ) ) {

	/**
	 * Class German_Market_Lexoffice_Invoice_API_Items
	 */
	class German_Market_Lexoffice_Invoice_API_Items {

		/**
		* Get voucher date
		*
		* @param WC_Order $order
		* @param Boolean $show_erros
		* @return String
		*/
		public static function get_voucher_date( $order_or_refund, $show_errors = true ) {
			return $order_or_refund->get_date_created()->format( self::get_date_time_format_lexoffice() );
		}

		/**
		* Get total price of order
		*
		* @param WC_Order $order
		* @param Boolean $show_erros
		* @return Array
		*/
		public static function get_total_price( $order_or_refund, $show_errors ) {
			
			$total_price = array(
				'currency'	=> $order_or_refund->get_currency()
			);

			return $total_price;
		}

		/**
		* Return date formate expected from lexoffice
		*
		* @return String
		*/
		public static function get_date_time_format_lexoffice() {

			$format = '';

			if ( defined( DateTimeInterface::RFC3339_EXTENDED ) ) {
				$format = DateTimeInterface::RFC3339_EXTENDED;
			} else {
				$format = "Y-m-d\TH:i:s.vP";
			}

			return $format;
		}

		/**
		* Returns finalize paramter "?finalize=true" if draft mode is deactivated
		* Returns empty string if draft mode is activated
		*
		* @return String
		*/
		public static function get_draft_mode_paramter() {
			return ( 'on' === get_option( 'woocommerce_de_lexoffice_invoice_api_draft_mode', 'off' ) ) ? "" : "?finalize=true";
		}

		/**
		* Get tax conditions
		*
		* Supported values for taxType: ross, net, vatfree, intraCommunitySupply, 
		* constructionService13b, externalService13b, thirdPartyCountryService, thirdPartyCountryDelivery.
		* Allowed for taxSubType are: distanceSales, electronicServices
		* 
		* @param WC_Order $order
		* @param Boolean $show_erros
		* @return Array
		*/
		public static function get_tax_conditions( $order_or_refund, $show_errors ) {

			if ( 'shop_order_refund' === $order_or_refund->get_type() ) {
				$order = wc_get_order( $order_or_refund->get_parent_id() );
			} else {
				$order = $order_or_refund;
			}

			if ( 'on' === get_option( 'woocommerce_de_kleinunternehmerregelung', 'off' ) ) {
				
				$tax_condition = array(
					'taxType' 		=> 'vatfree',
					'taxTypeNote'	=> get_option( 'gm_small_trading_exemption_notice_extern_products', WGM_Template::get_ste_string() ),
				);

			} else {

				$tax_type = 'gross' === self::get_prices_include_tax_in_woocommerce( $order ) ? 'gross' : 'net';
				
				$tax_exempt_status = WGM_Helper::wcvat_woocommerce_order_details_status( $order );
				$subtype = null;

				$tax_type_note = '';

				if ( 'tax_free_intracommunity_delivery' === $tax_exempt_status ) {
					
					$tax_type = 'intraCommunitySupply';
					$tax_type_note =  apply_filters( 'wcvat_woocommerce_vat_notice_eu', get_option( 'vat_options_notice', __( 'Tax free intracommunity delivery', 'woocommerce-german-market' ) ), $order );

				} else if ( 'tax_exempt_export_delivery' === $tax_exempt_status ) {
					
					$tax_type = 'thirdPartyCountryDelivery';
					$tax_type_note = apply_filters( 'wcvat_woocommerce_vat_notice_not_eu', get_option( 'vat_options_non_eu_notice', __( 'Tax-exempt export delivery', 'woocommerce-german-market' ) ), $order );

				} else {

					if ( German_Market_Lexoffice_API_General::order_has_non_german_taxes( $order ) ) {
						$subtype = apply_filters( 'german_market_lexoffice_invoice_api_tax_condition_sub_type', 'distanceSales', $order );
					}
				}

				$tax_condition = array(
					'taxType'	=> apply_filters( 'german_market_lexoffice_invoice_api_tax_condition_tax_type', $tax_type, $order )
				);

				$tax_type_note = apply_filters( 'german_market_lexoffice_invoice_api_tax_condition_tax_type_note', $tax_type_note, $order );

				if ( ! empty( $tax_type_note ) ) {
					$tax_condition[ 'taxTypeNote' ] = $tax_type_note;
				}

				if ( ! is_null( $subtype ) ) {
					$tax_condition[ 'taxSubType' ] = $subtype;
				}
			}

			return $tax_condition;
		}

		/**
		* Get filtered option woocommerce_prices_include_tax
		*
		* @param WC_Order $order
		* @return String (gross | net)
		*/
		public static function get_prices_include_tax_in_woocommerce( $order_or_refund ) {

			if ( 'shop_order_refund' === $order_or_refund->get_type() ) {
				$order = wc_get_order( $order_or_refund->get_parent_id() );
			} else {
				$order = $order_or_refund;
			}

			$tax_condition = get_option( 'woocommerce_de_lexoffice_invoice_api_tax_condition', 'gross' );

			if ( 'wc' === $tax_condition ) {
				
				$tax_condition = $order->get_prices_include_tax() ? 'gross' : 'net';

				if ( method_exists( $order, 'get_created_via' ) ) {
					$created_via = $order->get_created_via();
					if ( 'admin' === $created_via ) {
						$tax_condition = wc_prices_include_tax() ? 'gross' : 'net';
					}
				}
			}

			$tax_exempt_status = WGM_Helper::wcvat_woocommerce_order_details_status( $order );
			if ( ( ! empty( $tax_exempt_status ) ) || ( 'on' === get_option( 'woocommerce_de_kleinunternehmerregelung', 'off' ) ) ) {
				$tax_condition = 'net';
			}

			return apply_filters( 'woocommerce_de_lexoffice_api_get_tax_condition_in_woocommerce', $tax_condition, $order, $order_or_refund );
		}

		/**
		* Get shipping conditions
		*
		* Supported values are: ross, net, vatfree, intraCommunitySupply, 
		* constructionService13b, externalService13b, thirdPartyCountryService, thirdPartyCountryDelivery.
		* @param WC_Order $order
		* @param Boolean $show_erros
		* @return Array
		*/
		public static function get_shipping_conditions( $order, $show_errors ) {

			/*
			*  possible values: none, service, serviceperiod, delivery and deliveryperiod.
			*/
			$shipping_conditions = array(
				'shippingType'	=> 'delivery',
				'shippingDate'	=> $order->get_date_created()->format( self::get_date_time_format_lexoffice() ),
			);

			return apply_filters( 'woocommerce_de_lexoffice_api_get_shipping_conditions', $shipping_conditions, $order );
		}

		/**
		* Add line items to lexoffice invoice
		*
		* @param WC_Order $order
		* @param Boolean $show_erros
		* @return Array
		*/
		public static function get_line_items_of_order( $order, $show_errors = true, $is_refund = false ) {

			$line_items = array();

			foreach ( $order->get_items() as $key => $item ) {
				if ( method_exists( $item, 'get_quantity' ) ) {
					$line_items[] = self::get_line_item_by_order_item_of_order( $item, $order, $show_errors, $is_refund );
				}
			}

			$line_items = self::get_shipping_or_fee_line_items_of_order( $line_items, 'fees', $order, $show_errors, $is_refund );
			$line_items = self::get_shipping_or_fee_line_items_of_order( $line_items, 'shipping', $order, $show_errors, $is_refund );

			return $line_items;
		}

		/**
		* Get one lexoffice item by wc_order_item
		*
		* @param WC_Order_Item $wc_item
		* @param WC_Order $order
		* @param Boolean $show_erros
		* @return Array
		*/
		public static function get_line_item_by_order_item_of_order( $wc_item, $order, $show_errors = true, $is_refund = false ) {
			
			$line_item = array(
				'type'		=> 'custom',
				'name'		=> strip_tags( apply_filters( 'woocommerce_order_item_name', $wc_item->get_name(), $wc_item, false ) ),
				'quantity'	=> $is_refund ? abs( $wc_item->get_quantity() ) : $wc_item->get_quantity(),
				'unitName'	=> apply_filters( 'german_market_lexoffice_invoice_api_line_item_unit_name', __( 'Piece', 'woocommerce-german-market' ), $wc_item, $order ),
				'unitPrice'	=> array(
						'currency'			=>	$order->get_currency(),
						'taxRatePercentage'	=>	WGM_Tax::get_tax_rate_percent_by_item_and_order( $wc_item, $order ),
					),
			);

			// price
			$show_discounts = 'on' === get_option( 'woocommerce_de_lexoffice_invoice_show_discounts', 'off' );
			if ( $is_refund ) {
				$show_discounts = false;
			}

			$rounding = apply_filters( 'german_market_lexoffice_invoice_api_get_rounded_totals', true );

			if ( 'gross' === self::get_prices_include_tax_in_woocommerce( $order ) ) {
				
				if ( $show_discounts ) {
					$line_item[ 'unitPrice' ][ 'grossAmount' ] = $order->get_item_subtotal( $wc_item, true, $rounding );
					$line_item_incl_discount = $order->get_item_total( $wc_item, true );
					$line_item_excl_discount = floatval( $line_item[ 'unitPrice' ][ 'grossAmount' ] );
				} else {
					$line_item[ 'unitPrice' ][ 'grossAmount' ] = $order->get_item_total( $wc_item, true, $rounding );
					if ( $is_refund ) {
						$line_item[ 'unitPrice' ][ 'grossAmount' ] = abs( $line_item[ 'unitPrice' ][ 'grossAmount' ] );
					}
				}
				
				
			} else {
				
				if ( $show_discounts ) {
					$line_item[ 'unitPrice' ][ 'netAmount' ] = $order->get_item_subtotal( $wc_item, false, $rounding );
					$line_item_incl_discount = $order->get_item_total( $wc_item, false, false );
					$line_item_excl_discount = floatval( $line_item[ 'unitPrice' ][ 'netAmount' ] );
				} else {
					$line_item[ 'unitPrice' ][ 'netAmount' ] = $order->get_item_total( $wc_item, false, $rounding );

					if ( $is_refund ) {
						$line_item[ 'unitPrice' ][ 'netAmount' ] = abs( $line_item[ 'unitPrice' ][ 'netAmount' ] );
					}
				}
			}

			// round  up to 4 decimals
			if ( isset( $line_item[ 'unitPrice' ][ 'netAmount' ] ) ) {
				$line_item[ 'unitPrice' ][ 'netAmount' ] = round( $line_item[ 'unitPrice' ][ 'netAmount' ], 4 );
			}

			if ( isset( $line_item[ 'unitPrice' ][ 'grossAmount' ] ) ) {
				$line_item[ 'unitPrice' ][ 'grossAmount' ] = round( $line_item[ 'unitPrice' ][ 'grossAmount' ], 4 );
			}

			// discount handling per item
			if ( $show_discounts && ( $line_item_incl_discount !== $line_item_excl_discount ) ) {

				$discount_percent = 0.0;

				if ( $line_item_excl_discount > 0.0 && $line_item_incl_discount == 0.0 ) {
					$discount_percent = 100.0;
				} else if ( $line_item_excl_discount > 0.0 && $line_item_incl_discount > 0.0 ) {
					$discount_percent = ( $line_item_excl_discount - $line_item_incl_discount ) / $line_item_excl_discount * 100;
				}

				if ( ( floatval( $order->get_total_discount() ) > 0.0 ) && ( $discount_percent > apply_filters( 'german_market_lexoffice_invoice_api_discount_percent_to_infer_discount', 0.25 ) ) ) {
					$line_item[ 'discountPercentage' ] = round( $discount_percent, 2 );
				}
			}

			// description
			$description = self::get_description( $wc_item, $order, $is_refund );

			if ( ! empty( $description ) ) {
				$line_item[ 'description' ] = $description;
			}
			
			return apply_filters( 'german_market_lexoffice_invoice_api_line_item_by_order_item', $line_item, $wc_item, $order );
		}


		/**
		 * Get WC order item description for lexoffice depending on settings
		 * 
		 * @param WC_Order_Item $item
		 * @param WC_Order $order
		 * 
		 * @return String
		 */
		public static function get_description( $wc_item, $order, $is_refund = false ) {

			$description_elements = array();
			$option_suffix = $is_refund ? '_credit_note' : '';
			$product = apply_filters( 'woocommerce_order_item_product', $wc_item->get_product(), $wc_item );

			// sku
			if ( 'on' === get_option( 'woocommerce_de_lexoffice_invoice_show_sku' . $option_suffix, 'on' ) ) {
				if ( $product && method_exists( $product, 'get_sku' ) ) {

					$sku = $product->get_sku();
					if ( ! empty( $sku ) ) {
						$sku_text = __( 'SKU', 'woocommerce-german-market' ) . ': ' . $sku;
						$description_elements[ 'sku' ] = apply_filters( 'erman_market_lexoffice_invoice_api_description_sku', $sku_text, $sku, $product, $wc_item, $order );
					}
				}
			}

			// gtin
			if ( ( 'on' === get_option( 'gm_gtin_activation', 'off' ) ) && ( 'on' === get_option( 'woocommerce_de_lexoffice_invoice_show_gtin' . $option_suffix, 'on' ) ) ) {

				if ( $product && method_exists( $product, 'get_meta' ) ) {

					$gtin = $product->get_meta( '_gm_gtin' );
					if ( ! empty( $gtin ) ) {
						$gtin_text = __( 'GTIN', 'woocommerce-german-market' ) . ': ' . $gtin;
						$description_elements[ 'gtin' ] = apply_filters( 'erman_market_lexoffice_invoice_api_description_gtin', $gtin_text, $gtin, $product, $wc_item, $order );
					}
				}
			}

			// meta data
			if ( 'on' === get_option( 'woocommerce_de_lexoffice_invoice_show_item_meta' . $option_suffix, 'off' ) ) {

				ob_start();

				if ( apply_filters( 'german_market_lexoffice_invoice_api_description_item_meta_start', true, $wc_item, $order ) ) {
					do_action( 'woocommerce_order_item_meta_start', $wc_item->get_id(), $wc_item, $order, false );
				}

				$before_item_meta = ob_get_clean();

				ob_start();
				
				$wc_display_item_meta_args = apply_filters( 'german_market_lexoffice_invoice_api_description_item_meta_args', array(
					'before'    => '',
		            'after'     => '',
		            'separator' => '. ',
		            'echo'      => true,
		            'autop'     => false,
				) );

				wc_display_item_meta( $wc_item, $wc_display_item_meta_args );

				$item_meta = ob_get_clean();

				ob_start();

				if ( apply_filters( 'german_market_lexoffice_invoice_api_description_item_meta_end', true, $wc_item, $order ) ) {
					do_action( 'woocommerce_order_item_meta_end', $wc_item->get_id(), $wc_item, $order, false );
				}

				$after_item_meta = ob_get_clean();

				$data = array();
				foreach ( array( $before_item_meta, $item_meta, $after_item_meta ) as $element ) {
					if ( ! empty( $element ) ) {
						$data[] = $element;
					}
				}

				if ( ! empty( $data ) ) {

					$meta_data = implode( PHP_EOL, $data );
					$meta_data = str_replace( array( '<br>', '<br/>', '<br />' ), PHP_EOL, $meta_data );
					$meta_data = trim( strip_tags( $meta_data ) );
					$meta_data = apply_filters( 'german_market_lexoffice_invoice_api_description_item_meta_data', $meta_data, $wc_item, $order );

					$description_elements[ 'meta' ] = $meta_data;
				}
			}

			// short description
			if ( 'on' === get_option( 'woocommerce_de_lexoffice_invoice_api_line_item_short_description' . $option_suffix, 'off' ) ) {
				if ( $product && method_exists( $product, 'get_short_description' ) ) {
					$short_description = strip_tags( $product->get_short_description() );
					if ( ! empty( $short_description ) ) {
						
						if ( ! empty( $description ) ) {
							$description .= PHP_EOL;
						}

						$description_elements[ 'short_description' ] = $short_description;
					}
				}
			}

			$description_elements = apply_filters( 'german_market_lexoffice_invoice_api_description_elements', $description_elements, $wc_item, $product, $order );
			$description = implode( PHP_EOL, $description_elements );
			$description = apply_filters( 'german_market_lexoffice_invoice_api_description', $description, $description_elements, $wc_item, $product, $order );
			
			return $description;
		}

		/**
		 * Get "headline" item
		 * 
		 * @param String $name
		 * @param String $description
		 * @return String
		 */
		public static function get_text_line_item( $name, $description = '' ) {

			return array(
				'type' => 'text',
				'name' => $name,
				'description' => $description
			);
		}

		/**
		* Add shipping of fee of WC_Order to lexoffice line items
		*
		* @param Array $line_items
		* @param String $type
		* @param WC_Order $order
		* @param Boolean $show_erros
		* @return Array
		*/
		public static function get_shipping_or_fee_line_items_of_order( $line_items, $type, $order, $show_errors = true, $is_refund = false ){
			
			$shipping_items = array();
			
			// optional headline
			if ( $type === 'shipping' ) {
				$headline_text = get_option( 'woocommerce_de_lexoffice_invoice_headline_shipping', __( 'Shipping', 'woocommerce-german-market' ) );
			} else {
				$headline_text = get_option( 'woocommerce_de_lexoffice_invoice_headline_fees', __( 'Fees', 'woocommerce-german-market' ) );
			}

			if ( ! empty( $headline_text ) ) {
				$intro_headline = self::get_text_line_item( $headline_text );
			}

			$order_shipping_or_fees = WGM_Tax::get_shipping_or_fee_parts_by_order( $order, $type, true, self::get_prices_include_tax_in_woocommerce( $order ) );

			foreach ( $order_shipping_or_fees as $order_shipping_or_fee ) {
				foreach ( $order_shipping_or_fee as $shipping_or_fee_info ) {

					$shipping_name = isset( $shipping_or_fee_info[ 'name' ] ) ? $shipping_or_fee_info[ 'name' ] : $headline_text;
					$shipping_total_net = isset( $shipping_or_fee_info[ 'net' ] ) ? floatval( $shipping_or_fee_info[ 'net' ] ) : 0.0;
					$shipping_total_gross = isset( $shipping_or_fee_info[ 'gross' ] ) ? floatval( $shipping_or_fee_info[ 'gross' ] ) : 0.0;

					if ( 'gross' === self::get_prices_include_tax_in_woocommerce( $order ) ) {
						$amount_key = 'grossAmount';
						$shipping_total_value = $shipping_total_gross;
					} else {
						$amount_key = 'netAmount';
						$shipping_total_value = $shipping_total_net;
					}

					if ( $is_refund ) {
						$shipping_total_value = abs( $shipping_total_value );
					}

					$shipping_items[] = array(
							
						'type'		=> 'custom',
						'name'		=> apply_filters( 'woocommerce_de_lexoffice_invoice_api_shipping_name', $shipping_name, $shipping_or_fee_info, $order ),
						'quantity'	=> 1,
						'unitName'	=> apply_filters( 'german_market_lexoffice_invoice_api_line_shipping_unit_name', __( 'Piece', 'woocommerce-german-market' ), $shipping_or_fee_info, $order ),
						'unitPrice'	=> array(
								'currency'			=>	$order->get_currency(),
								'taxRatePercentage'	=>	isset( $shipping_or_fee_info[ 'rate_percent' ] ) ? floatval( $shipping_or_fee_info[ 'rate_percent' ] ) : 0.0,
								$amount_key => round( $shipping_total_value, 4 ),
							),
					);
				}
			}

			if ( ! empty( $shipping_items ) ) {
				
				if ( ! empty( $intro_headline ) ) {
					$shipping_items = array_merge( array( 0 => $intro_headline ), $shipping_items );
				}

				$line_items = array_merge( $line_items, $shipping_items );
			}

			return $line_items;
		}

		/**
		 * Add customer information to lexoffice invoice or credit note
		 * 
		 * @param Array $lexoffice_data
		 * @param WC_Order $order
		 * @return Array
		 */
		public static function add_contact( $lexoffice_data, $order_or_refund, $show_errors = true ) {

			if ( 'shop_order_refund' === $order_or_refund->get_type() ) {
				$order = wc_get_order( $order_or_refund->get_parent_id() );
			} else {
				$order = $order_or_refund;
			}

			$contact = German_Market_Lexoffice_API_Contact::add_user_to_voucher( array(), $order->get_user(), $order );

			if ( isset( $contact[ 'useCollectiveContact' ] ) && true === $contact[ 'useCollectiveContact' ] ) {
				$lexoffice_data[ 'address' ] = self::get_address( $order, $show_errors );
			} else {

				if ( 
					( 'lexoffice_contacts' === get_option( 'woocommerce_de_lexoffice_contacts', 'collective_contact' ) ) &&
					( 'off' === get_option( 'woocommerce_de_lexoffice_user_update', 'on' ) )
				) {
					$lexoffice_data[ 'address' ] = self::get_address( $order, $show_errors );
				}
				
				if ( isset( $contact[ 'contactId' ] ) ) {
					
					if ( ! isset( $lexoffice_data[ 'address' ] ) ) {
						$lexoffice_data[ 'address' ] = array();
					}

					$lexoffice_data[ 'address' ][ 'contactId' ] = $contact[ 'contactId' ];
				}	
			}

			return $lexoffice_data;
		}

		/**
		* Get adress array
		*
		* @param WC_Order $order
		* @param Boolean $show_erros
		* @return Array
		*/
		public static function get_address( $order, $show_errors = true ) {
			
			$address = array(); 
			
			if ( 'no' === get_option( 'woocommerce_de_lexoffice_invoice_api_use_lexoffice_contacts', 'no' ) ) {

				$name = empty( $order->get_billing_company() ) ? trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) : $order->get_billing_company();

				$adress = array(

					'name'			=> $name,
					'supplement'	=> $order->get_billing_address_2(),
					'street'		=> $order->get_billing_address_1(),
					'city'			=> $order->get_billing_city(),
					'zip'			=> $order->get_billing_postcode(),
					'countryCode'	=> $order->get_billing_country( 'edit' ),

				);

			}

			return $adress;
		}
	}
}
