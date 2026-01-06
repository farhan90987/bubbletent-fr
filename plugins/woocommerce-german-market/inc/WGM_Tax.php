<?php

/**
 * Class WGM_Tax
 *
 * This class contains helper functions to calculate the tax and some formatting functions
 *
 * @author  ChriCo
 */
class WGM_Tax {

	protected static $run_time_cache = array();

	/**
	* Add rate percent to tax labels if show tax == 'excluded'
	*
	* @since GM 3.2
	* @wp-hook woocommerce_cart_tax_totals
	* @param Array $tax_totals
	* @param WC_Cart OR WC_Order $cart_or_order
	* @return Array
	**/
	public static function woocommerce_cart_tax_or_order_totals( $tax_totals, $cart_or_order ) {

		foreach ( $tax_totals as $key => $tax ) {

			$label = $tax->label;
			
			// percent is not shown in the label, yet
			if ( str_replace( '%', '', $label ) == $label ) {

				$rate_id = isset( $tax->tax_rate_id ) ? $tax->tax_rate_id  : $tax->rate_id;

				if ( is_object( $cart_or_order ) && method_exists( $cart_or_order, 'get_id' ) ) {
					$rate_percent = WGM_Tax::get_rate_percent_by_rate_id_and_order( $rate_id, $cart_or_order );
				} else {
					$rate_percent = WC_Tax::get_rate_percent( $rate_id);
				}

				$tax_totals[ $key ]->label .= apply_filters( 'woocommerce_de_tax_label_add_if_tax_is_excl', ' (' . $rate_percent . ')', $rate_percent );
			}
		}

		return $tax_totals;
	}

	/**
	 * @param $enabled
	 *
	 * @return bool
	 */
	public static function is_cart_tax_enabled( $enabled ) {

		if ( ! is_cart() ) {
			return $enabled;
		}

		return ( $enabled && ! self::is_kur() );
	}

	/**
	 * Returns true if the current Shop has activated the "kur"-option (*K*lein*u*nternehmer*r*egelung).
	 *
	 * @author  ChriCo
	 *
	 * @issue   #418
	 * @return  bool true|false
	 */
	public static function is_kur() {

		return ( get_option( WGM_Helper::get_wgm_option( 'woocommerce_de_kleinunternehmerregelung' ) ) === 'on' );
	}

	/**
	 * Returns the formatted split tax html
	 *
	 * @param   array  $rates
	 * @param   string $type
	 *
	 * @return  string $html
	 */
	public static function get_split_tax_html( $rates, $type, $order = null ) {

		$html = '';
		$combined_rates = array();

		foreach ( $rates[ 'rates' ] as $key => $item ) {

			$item_rate 	= $item[ 'rate' ];
			$item_label	= $item[ 'label' ];

			if ( isset( $item[ 'sum' ] ) ) {
				$item[ 'sum' ] = floatval( $item[ 'sum' ] );
			}

			if ( is_object( $order ) && method_exists( $order, 'get_taxes' ) && isset( $item[ 'rate_id' ] ) ) {
				$item_rate = WGM_Tax::get_rate_percent_by_rate_id_and_order( $item[ 'rate_id' ], $order );
			}

			if ( ! isset( $combined_rates[ $item_label ] ) ) {
				$combined_rates[ $item_label ] = array();
			}

			if ( ! isset( $combined_rates[ $item_label ][ $item_rate ] ) ) {
				$combined_rates[ $item_label ][ $item_rate ] = $item;
			} else {
				$combined_rates[ $item_label ][ $item_rate ][ 'sum' ] += $item[ 'sum' ];
			}

			$combined_rates[ $item_label ][ $item_rate ][ 'rate' ] = $item_rate ;
		}

		$combined_rates_to_use = array();

		foreach ( $combined_rates as $rates_per_label ) {
			foreach ( $rates_per_label as $rate_per_label_and_rate ) {
				$combined_rates_to_use[] = $rate_per_label_and_rate;
			}
		}

		if ( apply_filters( 'german_market_get_split_tax_html_combined_rates', true ) ) {
			$used_rates = $combined_rates_to_use;
		} else {
			$used_rates = $rates[ 'rates' ];
		}

		foreach ( $used_rates as $item ) {

			$decimal_length = WGM_Helper::get_decimal_length( $item[ 'rate' ] );
			$formatted_rate = number_format_i18n( (float) $item[ 'rate' ], $decimal_length );

			$wc_price_args = array();

			if ( ( ! is_null( $order ) ) && WGM_Helper::method_exists( $order, 'get_currency' ) ) {
				$wc_price_args[ 'currency' ] = $order->get_currency();
			}

			$msg = WGM_Tax::get_excl_incl_tax_string( $item[ 'label' ], $type, $formatted_rate, wc_price( $item[ 'sum' ], $wc_price_args ) );

			$new_html = sprintf(
				'<br class="wgm-break" /><span class="wgm-tax product-tax">%s</span>',
				$msg
			);

			// don't add the same string twice (e.g. general tax output)
			$add_tax_line = apply_filters( 'german_market_get_split_tax_html_add_tax_line', true, $new_html, $html );
			if ( $add_tax_line ) {
				$html .= $new_html;
			}

		}

		if ( $html == '' ) {
			$html = sprintf(
				'<br class="wgm-break" /><span class="wgm-tax product-tax">%s</span>',
				apply_filters( 'wgm_zero_tax_rate_message', '', 'shipping' )
			);
		}

		return apply_filters( 'wgm_get_split_tax_html', $html, $rates, $type );

	}

	/**
	 * Returns the tax string for excl/incl tax
	 *
	 * @author  ChriCo
	 *
	 * @param   string $type
	 *
	 * @return  string $msg
	 */
	public static function get_excl_incl_tax_string( $label, $type, $rate, $amount ) {

		// init return value
		$msg = '';
		$rate_test_for_greater_than_zero = floatval( str_replace( ',', '.', $rate ) );

		// only if rate is > 0
		if ( $rate_test_for_greater_than_zero > 0 ) {
			if ( (string) $type === 'excl' ) {
				$msg = sprintf(
				/* translators: %1%s: tax %, %2$s: tax label, %3$s: tax amount */
					__( 'Plus %3$s %2$s (%1$s%%)', 'woocommerce-german-market' ),
					$rate,
					apply_filters( 'wgm_get_excl_incl_tax_string_tax_label', $label, $rate ),
					$amount
				);
			} else {
				$msg = sprintf(
				/* translators: %1%s: tax %, %2$s: tax label, %3$s: tax amount */
					__( 'Includes %3$s %2$s (%1$s%%)', 'woocommerce-german-market' ),
					$rate,
					apply_filters( 'wgm_get_excl_incl_tax_string_tax_label', $label, $rate ),
					$amount
				);
			}

		} else {
			$msg = apply_filters( 'wgm_zero_tax_rate_message', $msg, $type );
		}

		// some 3rd party plugins set rate to zero, but not the amount, let's repair this
		$is_rate_empty = empty( $rate );
		
		if ( WC()->customer ) {
			$is_vat_exempt = WC()->customer->is_vat_exempt();
		} else {
			$is_vat_exempt = false;
		}
		
		if ( ( $msg != '' && self::is_string_amount_equal_to_float_zero( $amount ) ) ) {
			
			if ( $is_rate_empty || $is_vat_exempt ) {
				$msg = apply_filters( 'wgm_zero_tax_rate_message', '', $type );
			}
			
		}
		
		return apply_filters( 'wgm_get_excl_incl_tax_string', $msg, $type, $rate, $amount, $label );
	}

	/**
	 * Check wheter a string presents an amount of zero in the curreny ($0.00 or 0.00€)
	 *
	 * @param String $amount_string
	 * @return Boolean
	 */
	private static function is_string_amount_equal_to_float_zero( $amount_string ) {

		// strip tags
		$amount_string = strip_tags( $amount_string );

		// remove &nbsp;
		$amount_string = str_replace( '&nbsp;', '', $amount_string );

		// get php decimal point
		$locale_info = localeconv();
		$php_decimal_point = $locale_info[ 'decimal_point' ];
		
		// remove currency symbol
		$amount_float = trim( str_replace( get_woocommerce_currency_symbol(), '', $amount_string ) );
		
		// remove html entities
		$amount_float = html_entity_decode( $amount_float );
		
		// remove thousand separator
		$amount_float = trim( str_replace( wc_get_price_thousand_separator(), '', $amount_float ) );

		// replace decimal seperator of woocommerce through php decimal separator 
		$amount_float = str_replace( wc_get_price_decimal_separator(), $php_decimal_point, $amount_float );
		
		// convert to float
		$amount_float = floatval( $amount_float );

		return $amount_float == 0.0;
	}

	/**
	 * Calculating the split tax on ajax callback in backend on "update tax"/"update sum"
	 *
	 * @wp-hook woocommerce_order_item_after_calculate_taxes
	 * @wp-hook woocommerce_order_item_shipping_after_calculate_taxes
	 * @wp-hook woocommerce_order_item_fee_after_calculate_taxes
	 *
	 * @param WC_Order_Item $order_item
	 * @param Array  $calculate_tax_for
	 *
	 * @return    void
	 */
	public static function recalc_taxes( $order_item, $calculate_tax_for ) {

		if ( ! ( $order_item->get_type() == 'fee' || $order_item->get_type() == 'shipping' ) ) {
			return;
		}

		if ( apply_filters( 'german_market_return_before_recalc_taxes', false, $order_item->get_type(), $order_item, $calculate_tax_for ) ) {
			return;
		}

		if ( WGM_Tax::is_kur() ) {
			return;
 		}
		
		$order = $order_item->get_order();
		$new_taxes = array();
		$use_split_tax = get_option( WGM_Helper::get_wgm_option( 'wgm_use_split_tax' ), 'on' );

		if ( $use_split_tax == 'off' ) {
			
			// here we always have the net costs, even if gross option is used (because it has already been calculated)
			if ( is_object( $order ) && method_exists( $order, 'get_items' ) ) {
				$tax_infos = WGM_Tax::get_calculate_net_rate_without_splittax( $order_item->get_total(), $order );
			} else {
				$tax_infos = WGM_Tax::get_calculate_net_rate_without_splittax( $order_item->get_total() );
			}
			
			foreach ( $tax_infos as $tax_id => $tax ) {
				$new_taxes[ $tax_id ] = $tax;
			}

		} else {

			if ( false === $order ) {
				return;
			}

			$split_rate_taxes = WGM_Tax::calculate_split_rate( $order_item->get_total(), $order, FALSE, '', 'shipping', false, false );
			foreach ( $split_rate_taxes[ 'rates' ] as $tax_id => $tax ) {
				$new_taxes[ $tax_id ] = $tax[ 'sum' ];
			}

		}

		$order_item->set_taxes( array( 'total' => $new_taxes ) );
		$order_item->save();

	}

	/**
	 * Calculating the split tax on ajax callback in backend on "update tax"/"update sum"
	 *
	 * @wp-hook    woocommerce_saved_order_items
	 *
	 * @param    int $order_id
	 *
	 * @return    void
	 */
	public static function re_calculate_tax_on_save_order_items( $order_id ) {

		$use_split_tax = get_option( WGM_Helper::get_wgm_option( 'wgm_use_split_tax' ), 'on' );

		if ( $use_split_tax == 'off' ) {
			return;
		}

		$order = wc_get_order( $order_id );

		// get all shipping items and remove them from order
		$all_shippings = $order->get_items( 'shipping' );
		$order->remove_order_items( 'shipping' );

		$shipping_taxes = array();

		// loop through all shipping items and create new ones with the split tax
		foreach ( $all_shippings as $shipping ) {

			// calculating the split tax
			$taxes = WGM_Tax::calculate_split_rate( $shipping[ 'cost' ], $order );

			$new_shipping        = new WC_Shipping_Flat_Rate();
			$new_shipping->label = $shipping[ 'name' ];
			$new_shipping->id    = $shipping[ 'method_id' ];
			$new_shipping->cost  = $shipping[ 'cost' ];
			$new_shipping->taxes = array();
			foreach ( $taxes[ 'rates' ] as $tax_id => $tax ) {
				$new_shipping->taxes[ $tax_id ] = $tax[ 'sum' ];

				if ( ! array_key_exists( $tax_id, $shipping_taxes ) ) {
					$shipping_taxes[ $tax_id ] = 0;
				}
				$shipping_taxes[ $tax_id ] += $tax[ 'sum' ];

			}

			// assign new shipping item to order
			$order->add_shipping( $new_shipping );
		}
		// re-calculate the shipping costs
		$order->calculate_shipping();

		// remove all taxes
		$order->remove_order_items( 'tax' );

		// get all line_items and loop through them to fetch the taxes
		$line_items = $order->get_items( 'line_item' );
		$line_taxes = array();
		foreach ( $line_items as $item ) {

			// no line tax data is given
			if ( empty( $item[ 'line_tax_data' ] ) ) {
				continue;
			}

			$taxes = maybe_unserialize( $item[ 'line_tax_data' ] );
			if ( ! is_array( $taxes ) ) {
				continue;
			}

			// loop through all total taxes (subtotal-discount)
			foreach ( $taxes[ 'total' ] as $rate_id => $tax_sum ) {
				if ( ! array_key_exists( $rate_id, $line_taxes ) ) {
					$line_taxes[ $rate_id ] = 0;
				}
				$line_taxes[ $rate_id ] += $tax_sum;
			}

		}

		// looping through all line_taxes and shipping taxes and saving the new tax sum
		// we don't add the fee-tax, because the fee-tax is added by another filter on display
		foreach ( array_keys( $line_taxes + $shipping_taxes ) as $rate_id ) {

			$line_tax = 0;
			if ( array_key_exists( $rate_id, $line_taxes ) ) {
				$line_tax = $line_taxes[ $rate_id ];
			}

			$shipping_tax = 0;
			if ( array_key_exists( $rate_id, $shipping_taxes ) ) {
				$shipping_tax = $shipping_taxes[ $rate_id ];
			}

			$order->add_tax(
				$rate_id,
				$line_tax,
				$shipping_tax
			);
		}

		$order->save();

	}

	/**
	 * Calculating the tax based on default rate and reduced rate
	 *
	 * @param   int                   $price
	 * @param   WC_Cart|WC_Order|null $cart_or_order
	 *
	 * @return  array $rates array(
	 *                          'sum'   => Integer,
	 *                          'rates  => array(
	 *                              rate_id => array(
	 *                                  'sum'       => Integer
	 *                                  'rate'      => String
	 *                                  'rate_id'   => Integer
	 *                              ),
	 *                              ...
	 *                          )
	 */
	public static function calculate_split_rate( $price, $cart_or_order = NULL, $bypass_digital = FALSE, $fee_id = '', $type = 'shipping', $use_as_gross = true, $check_condition = true, $rate = NULL ) {

		$price = floatval( $price );
		$input_price = $price;

		$count = array();

		$tax_totals = array();
		$line_items = array();
		
		if ( is_object( $cart_or_order ) && method_exists( $cart_or_order, 'get_status' ) ) {
			if ( 'checkout-draft' === $cart_or_order->get_status() ) {
				$cart_or_order = null;
			}
		}

		if ( $cart_or_order === NULL ) {
			$line_items = WC()->cart->get_cart();
			$tax_totals = WC()->cart->get_tax_totals();
		} else if ( is_a( $cart_or_order, 'WC_Cart' ) ) {
			$line_items = $cart_or_order->get_cart();
			$tax_totals = WC()->cart->get_tax_totals();
		} else if ( WGM_Hpos::is_order( $cart_or_order ) ) {
			$line_items = $cart_or_order->get_items();
			$tax_totals = $cart_or_order->get_total_tax();
		}
		
		// for 3rd party plugins that sets taxes to zero

		// make condition
		if ( is_array( $tax_totals ) ){
			$condition = empty( $tax_totals );
		} else {
			$condition = ! ( $tax_totals > 0.0 );
		}

		$condition = apply_filters( 'german_market_calculate_split_rate_condition', $condition, $cart_or_order );

		$total              = 0;
		$digital_exception  = FALSE;
		
		if ( is_a( $cart_or_order, 'WC_Cart' ) ) {

			$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
			$current_gateway    = WGM_Session::get( 'payment_method', 'first_checkout_post_array' );
			
			if ( isset( $available_gateways[ $current_gateway ] ) ) {
				$gateway           = $available_gateways[ $current_gateway ];
				$digital_exception = ( ( $gateway->id == 'cash_on_delivery' && $gateway->settings[ 'enable_for_virtual' ] !== 'yes' ) || ( $gateway->id == 'german_market_purchase_on_account' && $gateway->settings[ 'enable_for_virtual' ] !== 'yes' ) );
			}
			
		} elseif ( WGM_Hpos::is_order( $cart_or_order ) ) {
			$gateway           = wc_get_payment_gateway_by_order( $cart_or_order );
			if ( $gateway ) {
				$digital_exception = ( ( $gateway->id == 'cash_on_delivery' && $gateway->settings[ 'enable_for_virtual' ] !== 'yes' ) || ( $gateway->id == 'german_market_purchase_on_account' && $gateway->settings[ 'enable_for_virtual' ] !== 'yes' ) );
			}
		}

		foreach ( $line_items as $item ) {

			if ( apply_filters( 'german_market_split_tax_continue_item', false, $item, $rate, $cart_or_order ) ) {
				continue;
			}

			$product_id   = absint( $item[ 'product_id' ] );
			$variation_id = absint( $item[ 'variation_id' ] );

			if ( $variation_id !== 0 ) {
				$id = $variation_id;
			} else {
				$id = $product_id;
			}

			if ( $digital_exception && WGM_Helper::is_digital( $id ) ) {
				continue;
			}

			if ( $bypass_digital == TRUE && WGM_Helper::is_digital( $id ) ) {
				continue;
			}

			if ( $type == 'shipping' && apply_filters( 'german_market_is_digital_for_shipping_split_tax', WGM_Helper::is_digital( $id ), $id ) ) {
				continue;
			}

			$_product = wc_get_product( $id );

			if ( $_product && WGM_Helper::method_exists( $_product, 'get_tax_class' ) ) {
				$tax_class = $_product->get_tax_class();
			} elseif ( isset( $item[ 'tax_class' ] ) ) {
				$tax_class = $item[ 'tax_class' ];
			} else {
				// default to a empty tax class
				$tax_class = '';
			}

			// If the Costumer object is not available, we're most likely in an order
			if ( is_a( $cart_or_order, 'WC_Order' ) ) {
				
				if (  get_option( 'woocommerce_tax_based_on' ) === 'base' ) {

					$default 		= wc_get_base_location();
					$country  		= $default[ 'country' ];
					$state  		= $default[ 'state' ];
					$postcode 		= get_option( 'woocommerce_store_postcode', '' );

				} else {

					if ( $cart_or_order->needs_shipping_address() ) {
					
						$country = $cart_or_order->get_shipping_country();
						$state   = $cart_or_order->get_shipping_state();
						$postcode= $cart_or_order->get_shipping_postcode();
					
					} else {

						$country = $cart_or_order->get_billing_country();
						$state   = $cart_or_order->get_billing_state();
						$postcode= $cart_or_order->get_billing_postcode();

					}	

				}

			} else {
				list( $country, $state, $postcode, $city ) = WC()->customer->get_taxable_address();
			}

			$tax_rate_args = array(
				'country'   => $country,
				'state'     => $state,
				'tax_class' => $tax_class,
				'postcode'	=> $postcode,
			);

			if ( empty( $tax_rate_args[ 'country' ] ) ) {
				$base_location = wc_get_base_location();
				$tax_rate_args[ 'country' ] = $base_location[ 'country' ];
			}

			$tax         = WC_Tax::find_rates( $tax_rate_args );
			$current_tax = current( $tax );
			$rate_id     = key( $tax );

			/**
			 * wir müssen "line_total" benutzen, denn das ist der tatsächlich Betrag nach Abzug
			 * von Rabatten/Gutscheinen auf "line_subtotal"
			 *
			 * @issue 392
			 *
			 * --------
			 *
			 * line_subtotal wird aufgrund von @issue 488 wieder verwendet
			 */

			if ( ! isset( $item[ 'line_subtotal' ] ) ) {
				continue;
			}

			if ( array_key_exists( $rate_id, $count ) ) {
				$count[ $rate_id ][ 'total' ] += $item[ 'line_subtotal' ];
			} else {
				$count[ $rate_id ][ 'total' ] = $item[ 'line_subtotal' ];
				if ( isset( $current_tax[ 'rate' ] ) ) {
					$count[ $rate_id ][ 'rate' ]  = $current_tax[ 'rate' ];
				}  else {
					$count[ $rate_id ][ 'rate' ]  = 0;
				}
				
			}

			if ( isset( $current_tax[ 'label' ] ) ) {
				$count[ $rate_id ][ 'label' ] = $current_tax[ 'label' ];
			} else {
				$count[ $rate_id ][ 'label' ] = '';
			}

			$total += $item[ 'line_subtotal' ];

			// support for 3rd party plugins that sets taxes to zero

		}

		$out = array(
			'sum'   => 0,
			'rates' => array()
		);

		$old_price_gross = $price;

		if ( get_option( 'gm_gross_shipping_costs_and_fees', 'off' ) == 'on' && $use_as_gross ) {

			// caluclate divisor
			$divisor_sum = 0;
			foreach ( $count as $rate_id => $item ) {
				$divisor_sum += $item[ 'total' ] * $item[ 'rate' ];
			}

			if ( $total > 0 ) {
				$divisor = 1 + ( $divisor_sum / ( 100 * $total ) );
				$price = $price / $divisor;
			}
			
			$out[ 'use_as_gross' ] = apply_filters( 'gm_calculate_split_rate_vat_exempt_use_as_gross', $price, $old_price_gross );

		}

		// check condition and return "zero taxes"
		if ( apply_filters( 'german_market_calculate_split_rate_return_zero_condition', $condition, $out ) && $check_condition ) {
			if ( apply_filters( 'german_market_calculate_split_rate_return_zero', true ) ) {
				
				$return_array = array(
					'sum'		=> 0,
					'rates' 	=> array(),
					'rate'		=> 0,
				);

				if ( get_option( 'gm_gross_shipping_costs_and_fees', 'off' ) == 'on' && $use_as_gross ) {
					if ( isset( $out[ 'use_as_gross' ] ) ) {
						$return_array[ 'use_as_gross' ] = $out[ 'use_as_gross' ];
					}
				}

				return apply_filters( 'german_market_calculate_split_rate_return_zero_rates', $return_array, $input_price, $cart_or_order, $line_items, $tax_totals );
			}
		}
		
		foreach ( $count as $rate_id => $item ) {

			if ( $total > 0 ) {
				$sum = ( ( $price / $total * $item[ 'total' ] ) / 100 ) * $item[ 'rate' ];
				
				$precision = apply_filters( 'gm_split_tax_rounding_precision', 10 );
				
				if ( $precision ) {
					$sum = round( $sum, $precision );
				}

				if ( get_option( 'gm_gross_shipping_costs_and_fees', 'off' ) == 'on' && $use_as_gross ) {
					$old_price_gross -= $sum;
				}
				
			
			} else {
				$sum = 0;
			}

			$out[ 'rates' ][ $rate_id ] = array(
				'sum'     => $sum,
				'rate'    => $item[ 'rate' ],
				'rate_id' => $rate_id,
				'label'   => $item[ 'label' ]
			);

			$out[ 'sum' ] += $sum;

		}

		if ( get_option( 'gm_gross_shipping_costs_and_fees', 'off' ) == 'on' && $use_as_gross ) {
			$out[ 'use_as_gross' ] = $old_price_gross;
		}


		return $out;

	}

	public static function add_tax_part( $parts, $product ) {

		$parts[ 'tax' ] = self::text_including_tax( $product );

		return $parts;
	}

	/**
	 * print including tax for products
	 *
	 * @access public
	 * @static
	 * @author jj, ap
	 *
	 * @param WC_Product $product
	 *
	 * @return string
	 */
	public static function text_including_tax( $product, $cart = false, $is_preview = false ) {

		$is_preview = apply_filters( 'german_market_text_including_tax_is_preview', $is_preview );

		ob_start();
		do_action( 'wgm_before_tax_display_single' );

		$is_taxable = FALSE;
		if ( WGM_Helper::method_exists( $product, 'is_taxable' ) ) {
			$is_taxable = $product->is_taxable();
		}

		$classes = apply_filters( 'wgm_tax_display_text_classes', '' ); ?>

		<div class="wgm-info woocommerce-de_price_taxrate <?php echo $classes; ?>"><?php

			if ( get_option( WGM_Helper::get_wgm_option( 'woocommerce_de_kleinunternehmerregelung' ) ) == 'on' ) {

				do_action( 'wgm_before_variation_kleinunternehmerreglung_notice' ); 

				$stre_string = WGM_Template::get_ste_string();
				if ( WGM_Helper::method_exists( $product, 'get_type' ) ) {
					if ( $product->get_type() == 'external' ) {
						$stre_string = get_option( 'gm_small_trading_exemption_notice_extern_products', $stre_string );
					}
				}

				?>

				<span class="wgm-kleinunternehmerregelung"><?php echo $stre_string; ?></span>

				<?php
				do_action( 'wgm_after_variation_kleinunternehmerreglung_notice' );

			} elseif ( $is_taxable ) {
				echo trim( self::get_tax_line( $product, $cart, $is_preview ) );
			}
			?>
</div>
		<?php

		do_action( 'wgm_after_tax_display_single' );

		return ob_get_clean();
	}

	public static function get_tax_line( WC_Product $product, $cart = false, $is_preview = false ) {
		
		if ( ! $cart ) {
			if ( WGM_Helper::method_exists( $product, 'get_id' ) && isset( self::$run_time_cache[ 'get_tax_line_' . $product->get_id() ] ) ) {
				return self::$run_time_cache[ 'get_tax_line_' . $product->get_id() ];
			}
		}

		if ( ( ! $is_preview ) && is_null( WC()->customer ) ) {
			return apply_filters( 'german_market_get_tax_line_customer_is_null', '', $product, $cart );
		}

		$tax_print_include_enabled = apply_filters( 'woocommerce_de_print_including_tax', TRUE );

		if ( ! $cart ) {
			$tax_display = get_option( 'woocommerce_tax_display_shop' );
		} else {
			$tax_display = get_option( 'woocommerce_tax_display_cart' );
		}

		$tax_line = '';

		if ( ! ( $product instanceof WC_Product_Variable ) ) {

			$product_tax_class = $product->get_tax_class();
			
			if ( ! $is_preview ) {
				$location          = WC()->customer->get_taxable_address();

				$tax_rate_args = array(
					'country'   => $location[ 0 ],
					'state'     => $location[ 1 ],
					'postcode'  => $location[ 2 ],
					'tax_class' => ( $product_tax_class == 'standard' ? '' : $product_tax_class )
				);

			} else {

				$tax_rate_args = array(
					'country'   => WC()->countries->get_base_country(),
					'state'     => WC()->countries->get_base_country(),
					'postcode'  => WC()->countries->get_base_country(),
					'tax_class' => ( $product_tax_class == 'standard' ? '' : $product_tax_class )
				);

			}

			$args_string = implode( '_', $tax_rate_args );

			if ( isset( self::$run_time_cache[ 'tax_rates_' . $args_string ] ) ) {
				$tax_rates = self::$run_time_cache[ 'tax_rates_' . $args_string ];
			} else {
				$tax_rates = WC_Tax::find_rates( $tax_rate_args );
				self::$run_time_cache[ 'tax_rates_' . $args_string ] = $tax_rates;
			}
			
			$count_rates = 0;
			foreach ( $tax_rates as $rate ) {

				if ( $tax_print_include_enabled ) {

					$decimal_length = WGM_Helper::get_decimal_length( $rate[ 'rate' ] );
					$formatted_rate = number_format_i18n( (float) $rate[ 'rate' ], $decimal_length );
					// @todo
					if ( $tax_display == 'incl' ) {
						$tmp_line = sprintf(
						/* translators: %1$s%%: tax rate %, %2$s: tax rate label */
							__( 'Includes %1$s%% %2$s', 'woocommerce-german-market' ),
							$formatted_rate,
							apply_filters( 'wgm_get_tax_line_tax_label', $rate[ 'label' ], $rate, $product )
						);
					} else {
						$tmp_line = sprintf(
						/* translators: %1$s%%: tax rate %, %2$s: tax rate label */
							__( 'Plus %1$s%% %2$s', 'woocommerce-german-market' ),
							$formatted_rate,
							apply_filters( 'wgm_get_tax_line_tax_label', $rate[ 'label' ], $rate, $product )
						);
					}

					$count_rates++;

					if ( $count_rates < count( $tax_rates ) ) {
						$tmp_line .= '<br>';
					}

					$tax_line .= apply_filters(
						'wgm_tax_text',
						$tmp_line,
						$product,
						$tmp_line, // legacy argument
						$rate,
						$tax_display
					);

				} else {

					$tax_line = __( 'VAT not applicable', 'woocommerce-german-market' );
				}
			}

			if ( trim( $tax_line ) === '' ) {
				$tax_line = apply_filters( 'wgm_zero_tax_rate_message', '', 'product_tax_line' );
			}

		} else {

			/**
			 * For variable products, display only a generic string in the product summary.
			 * Detailed tax information is shown when the user actually selects a variation
			 */

			$tax_string = WGM_Helper::get_default_tax_label();

			// Default Text String to avoid checking all variations
			$avoid_checking_all_variations = apply_filters( 'woocommerce_de_variations_have_the_same_tax_string', '', $product );
			
			if ( $avoid_checking_all_variations != '' ) {
				return $avoid_checking_all_variations;
			}

			// Check all variations if the tax class is the same for all of them. Then show the actual tax information
			$all_variations_have_the_same_tax_class = true;
			$tax_classes = array();
			
			$the_unique_tax_class = false;

			$tax_class_info = WGM_Template::get_variable_data_quick( $product, 'tax_class' );
			
			if ( isset( $tax_class_info[ 'have_same_tax_class' ] ) ) {
				$all_variations_have_the_same_tax_class = $tax_class_info[ 'have_same_tax_class' ];
			}
			
			if ( $all_variations_have_the_same_tax_class && isset( $tax_class_info[ 'same_tax_class' ] ) ) {
				$the_unique_tax_class = WC_Tax::get_rates( $tax_class_info[ 'same_tax_class' ] );
			}

			// Exception: $the_unique_tax_class is empty
			$the_unique_tax_class_is_empty = empty( $the_unique_tax_class ) ? true : false;

			if ( $all_variations_have_the_same_tax_class && $the_unique_tax_class_is_empty ) {

				$tax_line = apply_filters( 'wgm_zero_tax_rate_message', '', 'product_tax_line' );

			} else if ( $all_variations_have_the_same_tax_class && $the_unique_tax_class ) {

				$the_unique_tax_class = array_shift( $the_unique_tax_class );
				$decimal_length = WGM_Helper::get_decimal_length( $the_unique_tax_class[ 'rate' ] );
				$formatted_rate = number_format_i18n( (float) $the_unique_tax_class[ 'rate' ], $decimal_length );

				// Tax included.
				if ( $tax_display == 'incl' ) {

					$tax_line = sprintf(
					/* translators: %s: tax included */
						__( 'Includes %1$s%% %2$s', 'woocommerce-german-market' ),
							$formatted_rate,
							apply_filters( 'wgm_get_tax_line_tax_label', $the_unique_tax_class[ 'label' ], $the_unique_tax_class, $product )
					);

				} else { // Tax to be added.

					$tax_line = sprintf(
					/* translators: %s: tax to be added */
						__( 'Plus %1$s%% %2$s', 'woocommerce-german-market' ),
							$formatted_rate,
							apply_filters( 'wgm_get_tax_line_tax_label', $the_unique_tax_class[ 'label' ], $the_unique_tax_class, $product )
					);
				}

				// if the tax rate of all variations is 0%
				if ( (float) $the_unique_tax_class[ 'rate' ] == 0.0 ) {
					//$tax_line = apply_filters( 'wgm_zero_tax_rate_message', '', 'product_tax_line_zero_all_variations', $tax_line );
				}

			} else { // variations have not the same tax class

				// Tax included.
				if ( $tax_display == 'incl' ) {

					$tax_line = sprintf(
					/* translators: %s: tax included */
						__( 'Includes %s', 'woocommerce-german-market' ),
						apply_filters( 'wgm_get_tax_line_tax_label_variations_different_tax', $tax_string, $product )
					);

				} else { // Tax to be added.

					$tax_line = sprintf(
					/* translators: %s: tax to be added */
						__( 'Plus %s', 'woocommerce-german-market' ),
						apply_filters( 'wgm_get_tax_line_tax_label_variations_different_tax', $tax_string, $product )
					);
				}

			}

		}
		
		// support for 3rd party plugins - check if taxes are set to zero
		$price_incl_taxes = wc_price( wc_get_price_including_tax( $product ) );
		$price_excl_taxes = wc_price( wc_get_price_excluding_tax( $product ) );

		if ( $price_incl_taxes == $price_excl_taxes ) {

			if ( WC()->customer ) {
				$is_vat_exempt = WC()->customer->is_vat_exempt();
			} else {
				$is_vat_exempt = false;
			}
			
			if ( empty( $product->get_tax_status() ) || $is_vat_exempt ) {
				$tax_line = apply_filters( 'wgm_zero_tax_rate_message', '', 'product_tax_line' );
			}
			
		}

		if ( ! $cart ) {
			if ( WGM_Helper::method_exists( $product, 'get_id' ) ) {
				self::$run_time_cache[ 'get_tax_line_' . $product->get_id() ] = apply_filters( 'wgm_get_tax_line', $tax_line, $product );
			}
		}
		
		return apply_filters( 'wgm_get_tax_line', $tax_line, $product );
	}

	/**
	 * If a user becomes vat exempted (or it is not vat exempted any more)
	 * the variation prices aren't correct in the shop.
	 * This is also happening without German Market!
	 * So you can use this also for other plugin compabilties
	 *
	 * @since v3.2
	 * @wp-hook woocommerce_get_variation_prices_hash
	 * @param String $hash
	 * @return $String
	 */
	public static function woocommerce_get_variation_prices_hash( $hash ) {

		if ( ! is_admin() ) {
	        $hash[] = get_current_user_id() . WC()->customer->is_vat_exempt();
	    }

	    return $hash;  
	}

	/**
	 * Add new line before product tax with css
	 * 
	 * @wp-hook woocommerce_email_styles
	 * @since 3.38
	 * @param String $css
	 * @param $email
	 * @return String
	 */
	public static function new_line_excl_incl_string_in_emails_with_css( $css, $email = null ) {
		$css .= PHP_EOL . 'tr.order_item .product-tax { display: block; }'. PHP_EOL;
   		return $css;
	}

	/**
	* Add a line break to incl excl string in emails
	*
	* @since v3.2
	* @wp-hook woocommerce_email_order_details
	* @param WC_Order $order
	* @param Bool $send_to_admin
	* @param Bool $plain_text
	* @param $email
	* @return void
	**/
	public static function new_line_excl_incl_string_in_emails( $order, $sent_to_admin, $plain_text, $email = false ) {
		add_filter( 'wgm_get_excl_incl_tax_string', array( __CLASS__, 'email_wgm_get_excl_incl_tax_string' ), 10, 4 );
	}

	/**
	* Add a line break to incl excl string in emails
	*
	* @since v3.5.2
	* @wp-hook gm_before_email_customer_confirm_order
	* @param WC_Order $order
	* @param Bool $send_to_admin
	* @param Bool $plain_text
	* @return void
	**/
	public static function new_line_excl_incl_string_in_email_customer_confirm_order( $order, $sent_to_admin, $plain_text ) {
		add_filter( 'wgm_get_excl_incl_tax_string', array( __CLASS__, 'email_wgm_get_excl_incl_tax_string' ), 10, 4 );
	}

	/**
	* Add a line break to incl excl string in emails
	*
	* @since v3.2
	* @last change: v3.5 - removed <br /> again, too much line break in emails, may remove that completely in next WC update
	* @wp-hook wgm_get_excl_incl_tax_string
	* @param String $msg
	* @param String $type
	* @param String $rate
	* @param String $amount
	* @return String
	**/
	public static function email_wgm_get_excl_incl_tax_string( $msg, $type, $rate, $amount ) {
		return apply_filters( 'email_wgm_get_excl_incl_tax_string', '<br />' . $msg, $type, $rate, $amount );
	}

	/**
	* Remove tax line form order item totals if "kur" ist active
	*
	* @since v3.2.2
	* @wp-hook woocommerce_get_order_item_totals
	* @param Array $total_rows
	* @param WC_Order $order
	* @return Array
	**/
	public static function remove_tax_order_item_totals( $total_rows, $order ) {
		unset( $total_rows[ 'tax' ] );
		return $total_rows;
	}

	/**
	* Calculate new net rate if splittax is disabled and "gross function" is disabled, too
	*
	* @since v3.7.1
	* @param  float $net_cost
	* @return Array
	**/
	public static function get_calculate_net_rate_without_splittax( $net_cost, $order = false ) {

		$new_rate 		= array();

		if ( false === $order ) {
			
			if ( WC()->customer->is_vat_exempt() ) {
				return $new_rate;
			}

			$applied_rate = self::get_applied_tax_class_if_splittax_is_off();
		
		} else {
			
			if ( 'yes' === $order->get_meta( 'is_vat_exempt' ) ) {
				return $new_rate;
			}

			$exception = false;
			if ( is_object( $order ) && method_exists( $order, 'get_status' ) ) {
				if ( 'checkout-draft' === $order->get_status() ) {
					$applied_rate = self::get_applied_tax_class_if_splittax_is_off();
					$exception = true;
				}
			}
			
			if ( ! $exception ) {
				$applied_rate = self::get_applied_tax_class_if_splittax_is_off_by_order( $order );
			}
			
		}
		

		if ( is_array( $applied_rate ) ) {
			
			$tax = floatval( $net_cost ) * floatval( $applied_rate[ 'rate' ] ) / 100.0;

			$precision = apply_filters( 'gm_split_tax_rounding_precision', 10 );
			if ( $precision ) {
				$tax = round( $tax, $precision );
			}

			$new_rate = array( $applied_rate[ 'key' ] => $tax );

		}

		return $new_rate;

	}

	/**
	* Get Applied Tax Rate from Order
	*
	* @since v3.34
	* @param WC_Order Order
	* @return Array
	**/
	public static function get_applied_tax_class_if_splittax_is_off_by_order( $order ) {

		$used_rate 				= null;
		$used_rate_id 			= null;
		$used_tax_rate_option  	= get_option( 'gm_tax_class_if_splittax_is_off', 'highest_rate' );
		$line_items 			= $order->get_items();
		$cart_taxes 			= array();

		$taxes = $order->get_taxes();
		foreach ( $order->get_taxes() as $key => $tax ) {
			$cart_taxes[ $tax->get_rate_id() ] = $tax->get_tax_total();
		}

		if ( empty( $cart_taxes ) ) {

			foreach ( $line_items as $item ) {
				
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

							$rate_id = $maybe_rate_id;
							break;
						}
					}

					if ( ! empty( $rate_id ) ) {
						if ( ! isset( $cart_taxes[ $rate_id ] ) ) {
							$cart_taxes[ $rate_id ] = 0;
						}

						$cart_taxes[ $rate_id ] += $order->get_line_tax( $item );
					}
				}
			}
		}

		// highest rate
		if ( $used_tax_rate_option == 'highest_rate' ) {

			$highest_rate = 0;

			foreach ( $cart_taxes as $key => $amount ) {

				$tax_rate = WC_Tax::_get_tax_rate( $key );

				if ( $tax_rate[ 'tax_rate' ] > $highest_rate ) {
					$used_rate_id 		= $key;
					$highest_rate 		= $tax_rate[ 'tax_rate' ];
				}

			}

		} else if ( $used_tax_rate_option == 'lowest_rate' ) {

			$lowest_rate = null;

			foreach ( $cart_taxes as $key => $amount ) {

				$tax_rate = WC_Tax::_get_tax_rate( $key );
				
				if ( ! $lowest_rate ) {
					$lowest_rate 		= $tax_rate[ 'tax_rate' ];
					$used_rate_id		= $key;
					continue;
				}

				if ( $tax_rate[ 'tax_rate' ] < $lowest_rate ) {
					$used_rate_id 		= $key;
					$lowest_rate 		= $tax_rate[ 'tax_rate' ];
				}

			}

		} else if ( $used_tax_rate_option == 'highest_amount' ) {

			$highest_amount = 0;

			foreach ( $cart_taxes as $key => $amount ) {

				if ( $amount > $highest_amount ) {
					$highest_amount = $amount;
					$used_rate_id 	= $key; 
				}

			}

		} else if ( $used_tax_rate_option == 'lowest_amount' ) {

			$lowest_amount = null;

			foreach ( $cart_taxes as $key => $amount ) {

				$tax_rate = WC_Tax::_get_tax_rate( $key );
				
				if ( ! $lowest_amount ) {
					$lowest_amount 		= $amount;
					$used_rate_id		= $key;
					continue;
				}

				if ( $amount < $lowest_amount ) {
					$used_rate_id 		= $key;
					$lowest_amount 		= $amount;
				}

			}

		} else {

			// generate tax class
			$location = $order->get_taxable_location();

			$tax_class = $used_tax_rate_option;
			if ( $tax_class == 'standard_rate' ) {
				$tax_class = '';
			}

			$tax_rate_args = array(
				'country'   => $location[ 'country' ],
				'state'     => $location[ 'state' ],
				'city'		=> $location[ 'city' ],
				'postcode'	=> $location[ 'postcode' ],
				'tax_class' => $tax_class,
			);

			$tax         	= WC_Tax::find_rates( $tax_rate_args );
			$used_rate_id   = key( $tax );

		}

		if ( $used_rate_id ) {

			$tax_rate = WC_Tax::_get_tax_rate( $used_rate_id );

			$used_rate = array(

				'rate'		=> apply_filters( 'woocommerce_rate_percent', $tax_rate[ 'tax_rate' ], $used_rate_id ),
				'label'		=> $tax_rate[ 'tax_rate_name' ],
				'shipping'	=> $tax_rate[ 'tax_rate_shipping' ] == 1 ? 'yes' : 'no',
				'compound'	=> $tax_rate[ 'tax_rate_compound' ] == 1 ? 'yes' : 'no',
				'key'		=> $used_rate_id,

			);

		}

		return $used_rate;
	}

	/**
	* Get Applied Tax Rate from Cart
	*
	* @since v3.7.1
	* @return Array
	**/
	public static function get_applied_tax_class_if_splittax_is_off() {

		$used_rate 				= null;
		$used_rate_id 			= null;
		$cart_taxes 			= WC()->cart->get_cart_contents_taxes();

		$used_tax_rate_option  	= get_option( 'gm_tax_class_if_splittax_is_off', 'highest_rate' );

		if ( empty( $cart_taxes ) ) {

			if ( 'highest_amount' === $used_tax_rate_option ) {
				$used_tax_rate_option = 'highest_rate';
			} else if ( 'lowest_amount' === $used_tax_rate_option ) {
				$used_tax_rate_option = 'lowest_rate';
			}

			$line_items = WC()->cart->get_cart();
			
			foreach ( $line_items as $item ) {

				$product_id   = absint( $item[ 'product_id' ] );
				$variation_id = absint( $item[ 'variation_id' ] );

				if ( $variation_id !== 0 ) {
					$id = $variation_id;
				} else {
					$id = $product_id;
				}

				$product = wc_get_product( $id );
				if ( is_object( $product ) && method_exists( $product, 'get_tax_class' ) ) {
					$tax_class = $product->get_tax_class();

					list( $country, $state, $postcode, $city ) = WC()->customer->get_taxable_address();
					
					$tax_rate_args = array(
						'country'   => $country,
						'state'     => $state,
						'tax_class' => $tax_class,
						'postcode'	=> $postcode,
					);

					if ( empty( $tax_rate_args[ 'country' ] ) ) {
						$base_location = wc_get_base_location();
						$tax_rate_args[ 'country' ] = $base_location[ 'country' ];
					}

					$tax         = WC_Tax::find_rates( $tax_rate_args );
					$current_tax = current( $tax );
					$rate_id     = key( $tax );
					
					if ( ! empty( $rate_id ) ) {
						if ( ! isset( $cart_taxes[ $rate_id ] ) ) {
							$cart_taxes[ $rate_id ] = 0;
						}
					}
				}
			}
		}
		
		// highest rate
		if ( $used_tax_rate_option == 'highest_rate' ) {

			$highest_rate = 0;

			foreach ( $cart_taxes as $key => $amount ) {

				$tax_rate = WC_Tax::_get_tax_rate( $key );
				
				if ( $tax_rate[ 'tax_rate' ] > $highest_rate ) {
					$used_rate_id 		= $key;
					$highest_rate 		= $tax_rate[ 'tax_rate' ];
				}

			}

		} else if ( $used_tax_rate_option == 'lowest_rate' ) {

			$lowest_rate = null;

			foreach ( $cart_taxes as $key => $amount ) {

				$tax_rate = WC_Tax::_get_tax_rate( $key );
				
				if ( ! $lowest_rate ) {
					$lowest_rate 		= $tax_rate[ 'tax_rate' ];
					$used_rate_id		= $key;
					continue;
				}

				if ( $tax_rate[ 'tax_rate' ] < $lowest_rate ) {
					$used_rate_id 		= $key;
					$lowest_rate 		= $tax_rate[ 'tax_rate' ];
				}

			}

		} else if ( $used_tax_rate_option == 'highest_amount' ) {

			$highest_amount = 0;

			foreach ( $cart_taxes as $key => $amount ) {

				if ( $amount > $highest_amount ) {
					$highest_amount = $amount;
					$used_rate_id 	= $key; 
				}

			}

		} else if ( $used_tax_rate_option == 'lowest_amount' ) {

			$lowest_amount = null;

			foreach ( $cart_taxes as $key => $amount ) {

				$tax_rate = WC_Tax::_get_tax_rate( $key );
				
				if ( ! $lowest_amount ) {
					$lowest_amount 		= $amount;
					$used_rate_id		= $key;
					continue;
				}

				if ( $amount < $lowest_amount ) {
					$used_rate_id 		= $key;
					$lowest_amount 		= $amount;
				}

			}

		} else {

			// generate tax class
			list( $country, $state, $postcode, $city ) = WC()->customer->get_taxable_address();
			
			$tax_class = $used_tax_rate_option;
			if ( $tax_class == 'standard_rate' ) {
				$tax_class = '';
			}

			$tax_rate_args = array(
				'country'   => $country,
				'state'     => $state,
				'city'		=> $city,
				'postcode'	=> $postcode,
				'tax_class' => $tax_class,
			);

			$tax         	= WC_Tax::find_rates( $tax_rate_args );
			$used_rate_id   = key( $tax );

		}

		if ( $used_rate_id ) {

			$tax_rate = WC_Tax::_get_tax_rate( $used_rate_id );

			$used_rate = array(

				'rate'		=> apply_filters( 'woocommerce_rate_percent', $tax_rate[ 'tax_rate' ], $used_rate_id ),
				'label'		=> $tax_rate[ 'tax_rate_name' ],
				'shipping'	=> $tax_rate[ 'tax_rate_shipping' ] == 1 ? 'yes' : 'no',
				'compound'	=> $tax_rate[ 'tax_rate_compound' ] == 1 ? 'yes' : 'no',
				'key'		=> $used_rate_id,

			);

		}

		return $used_rate;
	}

	/**
	* Calculate new net rate if splittax is disabled, but "gross function" is enabled
	*
	* @since v3.5
	* @param  float $net_cost
	* @return Array
	**/
	public static function calculate_gross_rate_without_splittax( $net_cost ) {
		
		// get chosen tax class
		$applied_rate 	= self::get_applied_tax_class_if_splittax_is_off();
		$new_rates 		= array();

		if ( get_option( 'gm_tax_class_if_splittax_is_off', 'highest_rate' ) == 'no_tax') {
			return array( 'net_sum' => $net_cost, 'taxes' => array() );
		}

		if ( is_array( $applied_rate ) ) {

			$net_sum = floatval( $net_cost ) / ( 100 + floatval( $applied_rate[ 'rate' ] ) ) * 100;
			$tax = floatval( $net_sum ) * floatval( $applied_rate[ 'rate' ] ) / 100;

			$precision = apply_filters( 'gm_split_tax_rounding_precision', 10 );
				
			if ( $precision ) {
				$net_sum 	= round( $net_sum, $precision );
				$tax 		= round( $tax, $precision );
			}

			$applied_rate_key = $applied_rate[ 'key' ];
			unset( $applied_rate[ 'key' ] );

			$new_rates = array(

				'net_sum' => $net_sum,
				'taxes'	  => array( $applied_rate_key => $tax ),
				'rates'	  => $applied_rate

			);

			if ( WC()->customer->is_vat_exempt() ) {
				$new_rates[ 'taxes' ] = array();
				$new_rates = apply_filters( 'gm_calculate_gross_rate_without_splittax_vat_exempt_rate', $new_rates, $net_cost, $applied_rate );
			}
		}
		return $new_rates;
	}

	/**
	* WooCommerce summarize taxes by code, 
	* this code should contain the percent rate, 
	* so rates with same label but different percent are not summarized
	*
	* @wp-hook woocommerce_rate_code
	* @param String $code_string
	* @param Integer $key
	* @return String
	*/
	public static function woocommerce_rate_code_add_percent_to_code( $code_string, $key ) {

		$percent = WC_Tax::get_rate_percent( $key );
		if ( ! empty( $percent ) ) {
			$code_string .= '-' . str_replace( '%', '', $percent );
		}

		return $code_string;
	}

	/**
	* Get rate percent of tax_rate_id and an WC_Order, 
	* Should be used because of the case that WC_Tax::get_rate_percent returns 0 if
	* the rate_id does not exist anymore in WooCommerce
	*
	* @param String $rate_id
	* @param WC_Order $order
	* @return String
	*/
	public static function get_rate_percent_by_rate_id_and_order( $rate_id, $order ) {

		$tax_rate_percents = array();
		
		if ( is_object( $order ) &&  method_exists( $order, 'get_taxes' ) ) {

			if ( isset( self::$run_time_cache[ 'order_tax_rates_' . $order->get_id() ] ) ) {
				$tax_rate_percents = self::$run_time_cache[ 'order_tax_rates_' . $order->get_id() ];
			} else {

				$taxes      = $order->get_taxes();
				foreach ( $taxes as $tax ) {
					$tax_rate_percents[ $tax->get_rate_id() ] = $tax->get_rate_percent();
				}

				self::$run_time_cache[ 'order_tax_rates_' . $order->get_id() ] = $tax_rate_percents;
			}
		}

		if ( isset( $tax_rate_percents[ $rate_id ] ) ) {
			$rate_percent = $tax_rate_percents[ $rate_id ] . '%';
		} else {
			$rate_percent = WC_Tax::get_rate_percent( $rate_id );
		}

		return $rate_percent;
	}

	/**
	* Get Array of all tax information of shipping of fees of an order
	* usefull for refunds with split tax
	* 
	* @param WC_Order $order
	* @param String $type
	* @return Array
	*/
	public static function get_shipping_or_fee_parts_by_order( $order, $type = 'shipping', $rounding_trick = false, $rounding_trick_net_or_gross = 'net' ) {

		$parts_return = array();
		if ( $type === 'shipping' ) {
			$parts = $order->get_shipping_methods();
		} else if ( $type === 'fees' ) {
			$parts = $order->get_fees();
		} else {
			return $parts_return;
		}
		
		foreach ( $parts as $key => $part_object ) {

			$total = $part_object->get_total();
			$total_tax = $part_object->get_total_tax();
			$net_sum = 0;
			$net_sum_rounded = 0;
			$gross_sum_rounded = 0;

			$taxes_array = $part_object->get_taxes( 'edit' );
			
			if ( isset( $taxes_array[ 'total' ] ) ) {
				
				$included_taxes = array();
				$highest_element = null;
				$highes_element_value = 0.0;
				$count = 0;

				foreach ( $taxes_array[ 'total' ] as $rate_id => $sum ) {
					
					$sum = floatval( $sum );
					$tax_percent = floatval( WGM_Tax::get_rate_percent_by_rate_id_and_order( $rate_id, $order ) );
					
					if ( $tax_percent > 0 && abs( $sum ) > 0 ) {
						
						$net 	= $sum / $tax_percent * 100;
						$gross 	= $sum * ( 1 + 1 / $tax_percent * 100 );

						if ( is_null( $highest_element ) ) {
							$highest_element = $count;
							$highes_element_value = 'net' === $rounding_trick_net_or_gross ? $net : $gross;
						} else {

							$maybe_highes_element_value = 'net' === $rounding_trick_net_or_gross ? $net : $gross;
							
							if ( $maybe_highes_element_value > $highes_element_value ) {
								$highest_element = $count;
								$highes_element_value = $maybe_highes_element_value;
							}
						}

						$included_taxes[ $count ] = array(
							'rate_id'		=> $rate_id,
							'rate_percent'	=> $tax_percent,
							'tax_value'		=> $sum,
							'net'			=> $net,
							'gross'			=> $gross,
							'name'			=> method_exists( $part_object, 'get_name' ) ? $part_object->get_name() : '',
						);

						$net_sum += $net;
						$net_sum_rounded += round( $net, 2 );
						$gross_sum_rounded += round( $gross, 2 );

						$count++;
					}
				}

				$included_taxes = self::get_shipping_or_fee_parts_by_order_rounding_trick( $included_taxes, $rounding_trick, $rounding_trick_net_or_gross, $total, $net_sum_rounded, $gross_sum_rounded, $highest_element, $order, $total_tax );
			}
			
			if ( round( $net_sum, 2 ) != round( $total, 2 ) ) {
				$included_taxes[] = array(
					'rate_percent'	=> 0,
					'tax_value'		=> 0,
					'net'			=> $total - $net_sum,
					'gross'			=> $total - $net_sum,
					'name'			=> method_exists( $part_object, 'get_name' ) ? $part_object->get_name() : '',

				);

				$maybe_highes_element_value = $total - $net_sum;
				if ( $maybe_highes_element_value > $highes_element_value ) {
					$highest_element = $count;
				}

				$net_sum_rounded += round( $maybe_highes_element_value, 2 ); // maybe no rounding here
				$gross_sum_rounded += round( $maybe_highes_element_value, 2 );

				$included_taxes = self::get_shipping_or_fee_parts_by_order_rounding_trick( $included_taxes, $rounding_trick, $rounding_trick_net_or_gross, $total, $net_sum_rounded, $gross_sum_rounded, $highest_element, $order, $total_tax );

			} else {

				if ( 1 === count( $included_taxes ) ) {

					if ( method_exists( $part_object, 'get_total' ) ) {
						$included_taxes[ 0 ][ 'net' ] = floatval( $part_object->get_total( 'edit' ) );
						
						if ( method_exists( $part_object, 'get_taxes' ) ) {
							// $part_object->get_total_tax( 'edit' ) is rounded!
							$taxes = $part_object->get_taxes();
							if ( isset( $taxes_array[ 'total' ] ) ) {
								$total_taxes = 0.0;
								
								foreach ( $taxes_array[ 'total' ] as $key => $value ) {
									$total_taxes += floatval( $value );
								}
							}

							$included_taxes[ 0 ][ 'gross' ] = floatval( $part_object->get_total( 'edit' ) ) + $total_taxes;
							$included_taxes[ 0 ][ 'tax_value' ] = $total_taxes;
						}
					}
				}
			}

			$parts_return[] = $included_taxes;
		}

		return $parts_return;
	}

	/**
	 * Rounding trick
	 * Add a cent or less to the biggest amount to be equal with the total
	 * 
	 * @param Array $included_taxes
	 * @param Boolean $rounding_trick
	 * @param String $rounding_trick_net_or_gross
	 * @param Float $total
	 * @param Float $net_sum_rounded
	 * @param Float $gross_sum_rounded
	 * @param Integer $highest_element
	 * @param WC_Order $order
	 * @param Float $total_tax
	 * @return Array
	 */
	public static function get_shipping_or_fee_parts_by_order_rounding_trick( $included_taxes, $rounding_trick, $rounding_trick_net_or_gross, $total, $net_sum_rounded, $gross_sum_rounded, $highest_element, $order, $total_tax ) {

		if ( true === apply_filters( 'german_market_get_shipping_or_fee_parts_by_order_rounding_trick', $rounding_trick, $order ) ) {
					
			if ( 'net' === $rounding_trick_net_or_gross ) {

				$diff = round( $total - $net_sum_rounded, 3 );

				if ( isset( $included_taxes[ $highest_element ][ 'net' ] ) ) {

					if ( $diff > 0 && abs( $diff ) > 0.005 && abs( $diff ) < 0.02 ) {
						$included_taxes[ $highest_element ][ 'net' ] = round( $included_taxes[ $highest_element ][ 'net' ], 4 ) + $diff;
					} else if ( $diff < 0 && abs( $diff ) > 0.005 && abs( $diff ) < 0.02 ) {
						$included_taxes[ $highest_element ][ 'net' ] = round( $included_taxes[ $highest_element ][ 'net' ], 4 ) - abs( $diff );
					}
				}

			} else {

				$diff = round( round( $total + $total_tax, 2 ) - $gross_sum_rounded, 3 );

				if ( isset( $included_taxes[ $highest_element ][ 'gross' ] ) ) {
					if ( $diff > 0.0 && abs( $diff ) > 0.005 && abs( $diff ) < 0.02) {
						$included_taxes[ $highest_element ][ 'gross' ] = round( $included_taxes[ $highest_element ][ 'gross' ], 2 ) + $diff;
					} else if ( $diff < 0 && abs( $diff ) > 0.005 && abs( $diff ) < 0.02 ) {
						$included_taxes[ $highest_element ][ 'gross' ] = round( $included_taxes[ $highest_element ][ 'gross' ], 2 ) - abs( $diff );
					}
				}
			}
		}
		
		return $included_taxes;
	}

	/**
	* Get tax rate percent of order item
	*
	* @param WC_Order_Item $wc_item
	* @param WC_Order $order
	* @param Boolean $show_erros
	* @return Integer
	*/
	public static function get_tax_rate_percent_by_item_and_order( $item, $order, $show_errors = true ) {

		$rate_percent = 0;

		if ( method_exists( $order, 'get_line_tax' ) && abs( $order->get_line_tax( $item ) ) > 0.0 ) {

			if ( method_exists( $item, 'get_data' ) ) {

				$item_data	= $item->get_data();
				$item_tax	= array();

				$rate_id 	= false;

				// same here: get_tax_rate_percent_by_item_and_order()
				if ( isset( self::$run_time_cache[ 'order_tax_rates_' . $order->get_id() ] ) ) {
					$tax_rate_percents = self::$run_time_cache[ 'order_tax_rates_' . $order->get_id() ];
				} else {
					
					$taxes      = $order->get_taxes();
					foreach ( $taxes as $key => $tax ) {
						$tax_rate_percents[ $tax->get_rate_id() ] = floatval( apply_filters( 'woocommerce_rate_percent', $tax->get_rate_percent(), $tax->get_rate_id() ) );
					}
				
					self::$run_time_cache[ 'order_tax_rates_' . $order->get_id() ] = $tax_rate_percents;
				}

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

						$rate_id = $maybe_rate_id;
						break;
					}

				}
				
				if ( isset( $tax_rate_percents[ $rate_id ] ) ) {
					$rate_percent = floatval( $tax_rate_percents[ $rate_id ] );
				} else if ( $rate_id ) {
					$rate_percent = floatval( WC_Tax::get_rate_percent( $rate_id ) );
				}

				if ( 0.0 === $rate_percent && $order->get_line_subtotal( $item, false ) > 0.0 ) {
					$rate_percent = round( $order->get_line_tax( $item ) / $order->get_line_subtotal( $item, false ) * 100, 1 );

					if ( $rate_id ) {
						self::$run_time_cache[ 'order_tax_rates_' . $order->get_id() ][ $rate_id ] = $rate_percent;
					}
				}
			}
		}

		return abs( $rate_percent );
	}
}
