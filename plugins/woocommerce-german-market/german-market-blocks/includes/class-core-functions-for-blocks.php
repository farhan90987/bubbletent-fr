<?php

defined( 'ABSPATH' ) || exit;

/**
 * In this class we have imported some functions from German Market Core 
 * or we call them in a wrapper method 
 * or we have slightly modified these methods 
 * to use them in the the cart and checkout block
 */
class German_Market_Blocks_Core_Functions_For_Blocks extends German_Market_Blocks_Methods {
	
    /**
     * Call actions and filters
     *
     * @return void
     */
    public function init() {

        // prepare cart item data to use it in blocks / integrations
        add_action( 'woocommerce_get_item_data', array( $this, 'prepare_cart_item_data' ), 10, 2 );

        // german market should not overwrite cod payment method from WC
		add_filter( 'gm_replace_cod_through_cash_on_delivery_v2', '__return_false' );

		// include css for opton Show order button in inner block "Checkout totals" instead of "Checkout Fields"
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_style_order_button' ), 15 );

		// checkout order button position
		if ( 'on' === get_option( 'german_market_blocks_move_order_button', 'off' ) ) {
			add_filter( 'render_block', array( $this, 'checkout_order_button_position' ), 9999, 2 );
		}
    }

	/**
	 * Show order button after "Checkout totals"
	 * Adds the inner block "woocommerce/checkout-actions-block" to "woocommerce/checkout-totals-block"
	 * Other buttons will be hidden by CSS
	 *
	 * @param String] $content
	 * @param Array $block
	 * @return void
	 */
	public function checkout_order_button_position( $content, $block ) {

		if ( 'woocommerce/checkout' === $block[ 'blockName' ] ) {
			
			$count = substr_count($content, 'wp-block-woocommerce-checkout-actions-block');

			if ( $count <= 1 ) {

				preg_match( '/<\/div>(\s*)<div[^<]*?data-block-name="woocommerce\/checkout-fields-block"/', $content, $matches );

				if ( ! empty( $matches ) ) { // current WC Versions
					
						$replacement = '<div class="german-market-checkout-fields-actions"><div data-block-name="woocommerce/checkout-actions-block" class="wp-block-woocommerce-checkout-actions-block"></div></div>' . $matches[ 0 ];
						$content = preg_replace( '/<\/div>(\s*)<div[^<]*?data-block-name="woocommerce\/checkout-fields-block"/', $replacement, $content );

					
				} else { // older WC Versions
					
					preg_match( '/<\/div>(\s*)<\/div>$/', $content, $matches );

					if ( ! empty( $matches ) ) {
						$replacement = '<div data-block-name="woocommerce/checkout-actions-block" class="wp-block-woocommerce-checkout-actions-block"></div></div></div>';
						$content = preg_replace( '/<\/div>(\s*)<\/div>$/', $replacement, $content );
					}
				}
			}
		}

		return $content;
	}

    /**
     * Inlcude style for "order button" position depending on our setting
     * 
     * @wp-hook wp_enqueue_scripts
     */
    public function enqueue_style_order_button() {

    	$style_path = '/additional-css/order-button-checkout-fields.css';

    	if ( 'on' === get_option( 'german_market_blocks_move_order_button', 'off' ) ) {
    		$style_path = '/additional-css/order-button-checkout-totals.css';
    	}
    
    	$style_url = plugins_url( $style_path, \GermanMarketBlocks::$package_file );

    	wp_enqueue_style(
			'german-market-blocks-order-button-position',
			$style_url,
			[],
			\GermanMarketBlocks::$version
		);

    }

    /**
	 * Prepare cart item data to show data in cart block
	 * 
	 * @wp-hook woocommerce_get_item_data
	 * @param Array $daata
	 * @param Array $cart_item
	 * @return Array
	 */
	public function prepare_cart_item_data ( $data, $cart_item ) {
	  
		$data[] = array(
			'key' => '_gm_line_item_tax',
			'value' => html_entity_decode( $this->get_cart_item_tax_string( $cart_item ) ),
			'hidden' => true
		);

		$data[] = array(
			'key' => '_gm_ppu',
			'value' => html_entity_decode( $this->get_cart_item_ppu( $cart_item ) ),
			'hidden' => true
		);

 		return $data;
 	}

    /**
	 * Get cart item price per unit, returns empty string with GM is deactivated
	 * 
	 * @param $cart_item
	 * @return String 
	 */
	public function get_cart_item_ppu( $cart_item ) {

		$ppu = '';
		if ( 'on' === get_option( 'woocommerce_de_show_ppu_checkout', 'off' ) ) {
			if ( class_exists( 'WGM_Price_Per_Unit' ) ) {
				$ppu = WGM_Price_Per_Unit::ppu_co_woocommerce_cart_item_price( '', $cart_item, $cart_item[ 'key' ] );
			}
		}

		return $ppu;
	}

    /**
	 * Get cart item tax string, code is copied from WGM_Template::show_taxes_in_cart_theme_template
	 * 
	 * @param $cart_item
	 * @return String 
	 */
	public function get_cart_item_tax_string( $cart_item ) {

		$tax_output = '';
		$subtotal = '';

		$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item[ 'data' ], $cart_item, $cart_item[ 'key' ] );

		if ( $_product->is_taxable() ){

			$_tax = new WC_Tax();

			if ( is_object( WC()->customer ) ){
				list( $country, $state, $postcode, $city ) = WC()->customer->get_taxable_address();
			}

			$t = $_tax->find_rates( array(
				'country' 	=>  $country,
				'state' 	=> $state,
				'tax_class' => $_product->get_tax_class(),
				'postcode'	=> $postcode,
			) );

			// Setup.
			$tax_display        = get_option('woocommerce_tax_display_cart');
			$tax_amount         = wc_price( $cart_item[ 'line_subtotal_tax' ] );

			if ( ! empty( $t ) ) {
				$tax                = array_shift( $t );
				$tax_label          = apply_filters( 'wgm_translate_tax_label', $tax[ 'label' ] );
				$tax_decimals       = class_exists( 'WGM_Helper' ) ? WGM_Helper::get_decimal_length( $tax[ 'rate' ] ) : 0;
				$tax_rate_formatted = number_format_i18n( (float)$tax[ 'rate' ], $tax_decimals );
			} else {
				$tax                = array();
				$tax_label          = apply_filters( 'wgm_translate_tax_label', '' );
				$tax_decimals       = false;
				$tax_rate_formatted = '';
			}

			$tax_string = WGM_Tax::get_excl_incl_tax_string( $tax_label, $tax_display, $tax_rate_formatted, $tax_amount );

			if ( apply_filters( 'gm_show_taxes_in_cart_theme_template_return_empty_string', false, $cart_item ) ) {
				$tax_output = '';
			} else {
				$tax_output = apply_filters( 'gm_cart_template_in_theme_show_taxes_markup', $subtotal . '<br class="wgm-break"/><span class="wgm-tax">' . $tax_string . '</span>', $subtotal, $tax_string, $cart_item );
			}
		}

		return $tax_output;
	}

    /**
	 * Render dynamic block in Cart for "learn more about ..."
	 * 
	 * @param $block_attributes
	 * @param $content
	 */
	public static function get_disclaimer_cart( $block_attributes, $content ) {

		$output = '';
		if ( 'on' === get_option( 'woocommerce_de_disclaimer_cart', 'on' ) ) {
			if ( class_exists( 'WGM_Template' ) ) {
				$output = WGM_Template::disclaimer_line();
			}
			
		}
		return $output;
	}

	/**
	 * Slighty changed from WGM_Shipping to use it for blocks
	 *
	 * @param   string   $label
	 * @param   stdClass $method
	 *
	 * @return  string $label
	 */
	public static function add_shipping_tax_notice( $label, $method ) {

		if ( WGM_Tax::is_kur() ) {
			return $label;
		}
		// shipping->cost is already rounded with rounding precision 2, we need all decimal places
		$slug = str_replace( ':', '_', $method->id );
		$option_name = 'woocommerce_' . $slug . '_settings';
		$option = get_option( $option_name );
		$method_cost_check = round( $method->cost, 2 );
		if ( is_array( $option ) ) {
			foreach ( $option as $maybe_cost ) {

				if ( ! is_array( $maybe_cost ) ) {
					$maybe_cost_float = round( floatval( str_replace( ',', '.', $maybe_cost ) ), 2 );
					if ( $maybe_cost_float == $method_cost_check ) {
						$method->cost = floatval( str_replace( ',', '.', $maybe_cost ) );
						break;
					}
				}
			}
		}
		
		$method = apply_filters( 'woocommerce_de_add_shipping_tax_notice_method', $method );

		$use_split_tax = get_option( WGM_Helper::get_wgm_option( 'wgm_use_split_tax' ), 'on' );
		
		if ( $use_split_tax == 'off' ) {

			$label = '';
			
			if ( $method->cost > 0 ) {

				// get the tax rate
				$rate = array();

				// get the rate id
				$taxes           = $method->taxes;
				$tax_rate_key    = array_keys( $taxes );
				$tax_rate_key    = reset( $tax_rate_key );

				$rate[ 'label' ] = WC_Tax::get_rate_label( $tax_rate_key );
				$rate[ 'sum' ]   = reset( $taxes );
				$rate[ 'rate' ]  = WC_Tax::get_rate_percent( $tax_rate_key );

				// set rates
				$rates              = array();
				$rates[ 'rates' ][] = $rate;

				$rates[ 'rates' ] = apply_filters( 'woocommerce_find_rates', $rates[ 'rates' ] );

				// append the split taxes to shipping-string
				$label .=  WGM_Tax::get_split_tax_html( $rates, get_option( 'woocommerce_tax_display_cart' ) );
			}

			$label = str_replace( '<br class="wgm-break" />', "\A ", $label );
			return $label;
		}

		$label = '';

		$the_rates 				= array();
		$the_rates[ 'rates' ] 	= array();
		$the_rates[ 'sum' ] 	= 0;

		$the_rates[ 'sum' ] = array_sum( $method->taxes );

		foreach ( $method->taxes as $tax_rate_key => $rate ) {

			if ( $rate == 0.0 ) {
				continue;
			}

			$the_rates[ 'rates' ][ $tax_rate_key ] = array();
			$the_rates[ 'rates' ][ $tax_rate_key ][ 'sum' ] 		= $rate;
			$the_rates[ 'rates' ][ $tax_rate_key ][ 'rate_id' ] 	= $tax_rate_key;
			$the_rates[ 'rates' ][ $tax_rate_key ][ 'label' ] 		= WC_Tax::get_rate_label( $tax_rate_key );
			$the_rates[ 'rates' ][ $tax_rate_key ][ 'rate' ]  		= WC_Tax::get_rate_percent( $tax_rate_key );
		}

		$the_rates[ 'rates' ] = apply_filters( 'woocommerce_find_rates', $the_rates[ 'rates' ] );

		if ( $method->cost > 0 ) {
			// append the split taxes to shipping-string
			$label .= WGM_Tax::get_split_tax_html( $the_rates, get_option( 'woocommerce_tax_display_cart' ) );
		}

		$label = str_replace( '<br class="wgm-break" />', "\A ", $label );
		return apply_filters( 'wgm_cart_shipping_method_full_label', $label, $method, $the_rates );

	}

	/**
	 * Get total shipping tax strings
	 * Not included in GM code, because never outputed in cart or checkout shortcode in that way
	 *
	 * @param WC_Cart $cart
	 * @return String
	 */
	public static function get_shipping_cart_total_taxes( $cart ) {

		$tax_string = '';
		$tax_string_array = array();

		$type = get_option( 'woocommerce_tax_display_cart' );

		foreach ( $cart->get_shipping_taxes() as $rate_id => $amount ) {

			$label = WC_Tax::get_rate_label( $rate_id );
			$rate_percent = WC_Tax::get_rate_percent_value( $rate_id );

			$tax_line = WGM_Tax::get_excl_incl_tax_string( $label, $type, $rate_percent, wc_price( $amount ) );
			
			if ( ! empty( $tax_line ) ) {
				if ( ! in_array( $tax_line, $tax_string_array ) ) {
					$tax_string_array[] = $tax_line;
				}
			}
		}

		if ( ! empty( $tax_string_array ) ) {
			$tax_string = implode( "\A ", $tax_string_array );
		}

		return $tax_string;
	}

	/**
	 * Get total tax string
	 *
	 * @param WC_Cart $cart
	 * @return String
	 */
	public static function get_cart_tax_total_string( $cart ) {
		
		$cart_total_string = '';
		
		if ( ! WGM_Tax::is_kur() ) {
		
			$filter_added = false;

			if ( ! has_filter( 'woocommerce_rate_label', array( 'WGM_Template', 'add_rate_to_label' ) ) ) {
				add_filter( 'woocommerce_rate_label', array( 'WGM_Template', 'add_rate_to_label' ), 10, 2 );
				$filter_added = true;
			}

			$cart_total_string = WGM_Template::get_totals_tax_string( $cart->get_tax_totals(), get_option( 'woocommerce_tax_display_cart' ) );

			if ( $filter_added ) {
				remove_filter( 'woocommerce_rate_label', array( 'WGM_Template', 'add_rate_to_label' ), 10, 2 );
			}
			$cart_total_string = str_replace( '<br class="wgm-break" />', "\A ", $cart_total_string );
			if ( substr( $cart_total_string, 0, 2 ) === "\A " ) {
				$cart_total_string = substr( $cart_total_string, 2 );
			}

		} else {
			$cart_total_string = WGM_Template::get_ste_string();
		}

		return $cart_total_string;
	}
}
