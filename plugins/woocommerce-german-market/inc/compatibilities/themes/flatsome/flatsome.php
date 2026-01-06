<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class German_Market_Theme_Compatibility_Flatsome {

	/**
	* Theme Flatsome
	*
	* @tested with theme version 3.11.3
	* @return void
	*/
	public static function init() {
		
		add_action( 'woocommerce_after_template_part', array( __CLASS__, 'theme_flatsome_price_data' ), 10, 4 );
		add_filter( 'german_market_compatibility_elementor_price_data', '__return_false' );

		// shortcode checkout
		if ( 'off' == get_option( 'gm_deactivate_checkout_hooks', 'off' ) ) {
			add_action( 'wp_head', function () { ?>
				<style>
					.woocommerce-checkout h3#order_review_heading {display:none;}
				</style>
			<?php } );

			add_action( 'woocommerce_checkout_before_order_review', function() {
				echo '<h3 id="order_review_payment_heading">' . __( 'Payment Method', 'woocommerce-german-market' ) . '</h3>';
			}, 10 );

			add_action( 'woocommerce_checkout_before_order_review', 'woocommerce_checkout_payment', 20 );

			if ( get_option( 'gm_order_review_checkboxes_before_order_review', 'off' ) == 'on' ) {
				add_action( 'woocommerce_checkout_order_review', array( 'WGM_Template', 'add_review_order' ), 5 );
			}

			add_action( 'woocommerce_checkout_order_review', function() {
				echo '<h3 id="order_review_heading_bottom">' . __( 'Your order', 'woocommerce' ) . '</h3>';
			}, 6 );
		}
	}

	/**
	* Theme Flatsome: Add German Market Data after single price
	*
	* @since 	v3.7.2
	* @tested with theme version 3.11.3
	* @wp-hook 	woocommerce_after_template_part
	* @param 	String $template_name
	* @param 	String $template_path
	* @param 	String $located
	* @param 	Array $args
	* @return 	void
	*/
	public static function theme_flatsome_price_data( $template_name, $template_path, $located, $args ) {

		if ( $template_name == 'single-product/price.php' || $template_name == '/single-product/price.php' ) {

			$debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 1 );

			if ( isset( $debug_backtrace[ 0 ][ 'function' ] ) && ( $debug_backtrace[ 0 ][ 'function' ] === 'theme_flatsome_price_data' ) ) {

				add_filter( 'wgm_product_summary_parts', array( 'WGM_Theme_Compatibilities', 'theme_support_hide_gm_price_in_loop' ), 10, 3 );
				add_filter( 'wgm_product_summary_parts', array('WGM_Theme_Compatibilities', 'theme_support_hide_gm_price_in_single' ), 10, 3 );
				WGM_Template::woocommerce_de_price_with_tax_hint_single();
				remove_filter( 'wgm_product_summary_parts', array( 'WGM_Theme_Compatibilities', 'theme_support_hide_gm_price_in_single' ), 10, 3 );
				remove_filter( 'wgm_product_summary_parts', array( 'WGM_Theme_Compatibilities', 'theme_support_hide_gm_price_in_loop' ), 10, 3 );

			}
		}
	}
}
