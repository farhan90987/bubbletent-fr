<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class German_Market_Theme_Compatibility_ModernDefaultBlockTheme {

	/**
	* Moder Default Block Themes
	* twentytwentyfour
	* twentytwentythree
	* twentytwentytwo
	* jaxon
	*
	* @since 3.31
	* @wp-hook after_setup_theme
	* @return void
	*/
	public static function init() {

		remove_action( 'woocommerce_single_product_summary', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_single' ), 7 );
		remove_action( 'woocommerce_after_shop_loop_item_title', array( 'WGM_Template', 'woocommerce_de_price_with_tax_hint_loop' ), 5 );
		remove_filter( 'woocommerce_available_variation', array( 'WGM_Helper', 'prepare_variation_data' ), 10, 3 );
		remove_filter( 'woocommerce_blocks_product_grid_item_html',	array( 'WGM_Template', 'german_market_woocommerce_blocks_price' ), 10, 3 );

		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
		add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 4 );
		
		add_filter( 'woocommerce_get_price_html', function( $price, $product ) {
			$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 20 );

			foreach ( $debug_backtrace as $elem ) {
				
				if ( $elem[ 'function' ] === 'get_the_block_template_html' ) {
					
					return '<div class="legacy-itemprop-offers">' . $price . WGM_Template::get_wgm_product_summary( $product, 'block_single', false ) . '</div>';
					
				} else if ( $elem[ 'function' ] === 'render_block_core_post_template' || $elem[ 'function' ] === 'render_block_core_post_content' ) {
					
					return $price . WGM_Template::get_wgm_product_summary( $product, 'block_loop', false );
					
				} else if (  $elem[ 'function' ] === 'woocommerce_template_loop_price' ) {

					return $price . WGM_Template::get_wgm_product_summary( $product, 'loop', false );
				}
				
				
			}
			
			return $price;

		}, 10, 2 );
		
	}
}
