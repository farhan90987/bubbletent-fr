<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WGM_Special_Tax_Information_For_Tax_Class
 *
 * @author MarketPress
 */
class WGM_Special_Tax_Information_For_Tax_Class {

	public $option_number = 0;
	public $tax_class = '';
	public static $registered_tax_classes = array();

	public function __construct( $tax_class, $option_number ) {

		$this->tax_class = $tax_class;
		$this->option_number = $option_number;

		if ( 'off' !== $tax_class ) {
			if ( ! in_array( $tax_class, self::$registered_tax_classes ) ) {

				add_filter( 'wgm_get_tax_line', array( $this, 'wgm_get_tax_line' ), 10, 2 );
				add_filter( 'gm_cart_template_in_theme_show_taxes_markup', array( $this, 'gm_cart_template_in_theme_show_taxes_markup' ), 10, 4 );
				add_filter( 'wgm_template_add_mwst_rate_to_product_order_item', array( $this, 'wgm_template_add_mwst_rate_to_product_order_item' ), 10, 5 );
				add_filter( 'german_market_general_tax_output_product', array( $this, 'exception_for_general_tax_output' ), 10, 2 );

				self::$registered_tax_classes[ $tax_class ] = $this;
				do_action( 'german_market_special_tax_information_for_tax_class_after_construct', $this, self::$registered_tax_classes );
			}
		}
	}

	/**
	 * Get tax information by get_option
	 * We cannot use the option value as class property, because of language switching plugins
	 * 
	 * @return String
	 */ 
	public function get_tax_information() {
		return get_option( 'german_market_special_tax_output_tax_information_' . $this->option_number, '' );
	}

	/**
	 * Exception for "general tax output" / add-on: "EU VAT Checkout"
	 * 
	 * @wp-hook german_market_general_tax_output_product
	 * @param Bool $is_not_exception
	 * @param WC_Product $product
	 * @return Bool
	 */
	public function exception_for_general_tax_output( $is_not_exception, $product ) {

		if ( is_object( $product ) && method_exists( $product, 'get_tax_class' ) ){

			$product_tax_class = empty( $product->get_tax_class() ) ? 'standard' : $product->get_tax_class();

			if ( $this->tax_class === $product_tax_class ) {
				$is_not_exception = false;
			}
		}

		return $is_not_exception;
	}

	/**
	 * Filter tax line in shop
	 * 
	 * @wp-hook wgm_get_tax_line
	 * @param String $tax_line
	 * @param WC_Product $product
	 * @return String
	 */
	public function wgm_get_tax_line( $tax_line, $product ) {

		if ( is_object( $product ) && method_exists( $product, 'get_tax_class' ) ){

			$product_tax_class = empty( $product->get_tax_class() ) ? 'standard' : $product->get_tax_class();

			if ( $this->tax_class === $product_tax_class ) {
				$tax_line = $this->get_tax_information();
			} 
		}

		return $tax_line;
	}

	/**
	 * Filter tax line in cart
	 * 
	 * @wp-hook gm_cart_template_in_theme_show_taxes_markup
	 * @param String $text
	 * @param String $subtotal
	 * @param String $tax_string
	 * @param Array $cart_item
	 * @return String
	 */
	public function gm_cart_template_in_theme_show_taxes_markup( $text, $subtotal, $tax_string, $cart_item = null ) {

		if ( is_array( $cart_item ) && isset( $cart_item[ 'data' ] ) ) {
			$product = $cart_item[ 'data' ];
			if ( is_object( $product ) && method_exists( $product, 'get_tax_class' ) ){

				$product_tax_class = empty( $product->get_tax_class() ) ? 'standard' : $product->get_tax_class();

				if ( $this->tax_class === $product_tax_class ) {
					$text = $subtotal . '<br class="wgm-break" /><span class="wgm-tax">' . $this->get_tax_information() . '</span>';
				}
			}
		}

		return $text;
	}

	/**
	 * Filter tax line in order
	 * 
	 * @wp-hook wgm_template_add_mwst_rate_to_product_order_item
	 * @param String $order_item_total_string
	 * @param String $subtotal
	 * @param String $complete_tax_string
	 * @param WC_Product $product
	 * @param WC_Order_Item_Product 
	 * @return String $order_item
	 */
	public function wgm_template_add_mwst_rate_to_product_order_item( $order_item_total_string, $subtotal, $complete_tax_string, $product = null, $order_item = null ) {
		
		if ( is_object( $order_item ) && method_exists( $order_item, 'get_tax_class' ) ){

			$product_tax_class = empty( $order_item->get_tax_class() ) ? 'standard' : $order_item->get_tax_class();
			
			if ( $this->tax_class === $product_tax_class  ) {
				$order_item_total_string = $subtotal . '<span class="product-tax">' . $this->get_tax_information() . '</span>';
			}
		}
		
		return $order_item_total_string;
	}
}
