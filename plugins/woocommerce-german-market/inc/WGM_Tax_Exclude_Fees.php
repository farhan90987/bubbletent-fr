<?php

/**
 * Class WGM_Tax_Exclude_Fees
 *
 */
class WGM_Tax_Exclude_Fees {

	/**
	 * @var WGM_Tax_Exclude_Fees
	 * @since 3.47
	 */
	private static $instance = null;

	/**
	* Singletone get_instance
	*
	* @static
	* @return WGM_Tax_Exclude_Fees
	*/
	public static function get_instance() {
		if ( self::$instance == NULL) {
			self::$instance = new WGM_Tax_Exclude_Fees();	
		}
		return self::$instance;
	}

	/**
	* Singletone constructor
	*
	* @return void
	*/
	private function __construct() {

		remove_filter( 'woocommerce_cart_totals_fee_html', array( 'WGM_Fee', 'show_gateway_fees_tax' ), 10, 2 );
		remove_filter( 'woocommerce_cart_totals_get_fees_from_cart_taxes', array( 'WGM_Fee', 'cart_totals_get_fees_from_cart_taxes' ), 10, 3 );	
		remove_filter( 'woocommerce_get_order_item_totals', array( 'WGM_Fee', 'add_tax_string_to_fee_order_item' ), 10, 2 );
		remove_filter( 'woocommerce_order_get_tax_totals', array( 'WGM_Fee', 'add_fee_to_order_tax_totals' ), 10, 2 );

		add_filter( 'german_market_return_before_recalc_taxes', array( $this, 'return_before_recalc_taxes' ), 10, 4 );
		add_filter( 'woocommerce_cart_totals_fee_html',  array( $this, 'cart_totals_fee_html' ), 10, 2 ); 
		add_filter( 'woocommerce_get_order_item_totals', array( $this, 'get_order_item_totals' ), 10, 2 );
	}

	/**
	 * No influence by German Market for fee taxes when recalculating in backend
	 * 
	 * @wp-hook german_market_return_before_recalc_taxes
	 * @param string $return_value
	 * @param string $order_item_type
	 * @param WC_Order_Item $order_item
	 * @param string $calculate_tax_for
	 * @return string
	 */ 
	public function return_before_recalc_taxes ( $return_value, $order_item_type, $order_item, $calculate_tax_for ) {
			
		if ( 'fee' === $order_item_type ) {
			$return_value = true;
		}
			
		return $return_value;
	}

	/**
	 * Output fee taxes (without own calculation) in cart and checkout
	 * 
	 * @wp-hook woocommerce_cart_totals_fee_html
	 * @param string $fee_html
	 * @param WC_Order_Item_Fee $fee
	 * @return string
	 */ 
	public function cart_totals_fee_html( $fee_html, $fee ) {
		
		if ( class_exists( 'WGM_Tax' ) ) {
			$tax_display 		= get_option( 'woocommerce_tax_display_cart' );

			if ( isset( $fee->tax_data ) && is_array( $fee->tax_data ) ) {
				foreach ( $fee->tax_data as $rate_id => $amount ) {
					$tax_label 	= WC_Tax::get_rate_label( $rate_id );
					$rate_percent = floatval( WC_Tax::get_rate_percent( $rate_id ) );
					$tax_string = WGM_Tax::get_excl_incl_tax_string( $tax_label, $tax_display, $rate_percent, wc_price( $fee->tax ) );
					$fee_html .= sprintf( '<br class="wgm-break" /><span class="wgm-tax product-tax"> %s </span>', $tax_string );
					break;
				}
			}
		}		
		
		return $fee_html;
	}

	/**
	 * Output fee taxes (without own calculation) in order
	 * 
	 * @wp-hook woocommerce_get_order_item_totals
	 * @param array $items
	 * @param WC_Order $order
	 * @return array
	 */ 
	public function get_order_item_totals ( $items, $order ) {

		if ( is_a( $order, 'WC_Order_Refund' ) ) {
			$parent_id = $order->get_parent_id();
			$order = wc_get_order( $parent_id );
		}
		
		$tax_display = get_option( 'woocommerce_tax_display_cart' );
		
		foreach ( $order->get_fees() as $key => $fee ) {

			$search_key = 'fee_' . $key;

			if ( ! array_key_exists( $search_key, $items ) ) {
				continue;
			}
			
			$rate = array();
			$taxes = $fee->get_taxes();
			
			if ( isset( $taxes[ 'total' ] ) ) {

				$fee_html = '';
				
				foreach ( $taxes[ 'total' ] as $tax_rate_key => $tax_infos ) {

					$label = apply_filters( 'wgm_translate_tax_label', WC_Tax::get_rate_label( $tax_rate_key ) );
					$sum   = $fee->get_total_tax();
					$rate  = floatval( WC_Tax::get_rate_percent( $tax_rate_key ) );
					$tax_string = WGM_Tax::get_excl_incl_tax_string( $label, $tax_display, $rate, wc_price( $sum ) );
					$fee_html .= sprintf( '<br class="wgm-break" /><span class="wgm-tax product-tax"> %s </span>', $tax_string );
					$items[ $search_key ][ 'value' ] .= $fee_html;
					
					break;
				}
			}
		}

		return $items;
	}
}
