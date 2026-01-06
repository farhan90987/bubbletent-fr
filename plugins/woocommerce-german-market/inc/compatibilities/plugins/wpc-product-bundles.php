<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WGM_Plugin_Compatibility_WPC_Product_Bundles
 * Compatibility functions for WPC Product Bundles plugin.
 *
 * @author MarketPress
 */
class WGM_Plugin_Compatibility_WPC_Product_Bundles {

	static $instance = NULL;

	/**
	 * singleton getInstance
	 *
	 * @access public
	 * @static
	 *
	 * @return WGM_Plugin_Compatibility_WPC_Product_Bundles
	 */
	public static function get_instance() {

		if ( self::$instance == NULL) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function __construct() {

		add_filter( 'woocommerce_cart_contents_weight', function( $weight ) {
			$weight = 0.0;

			foreach ( WC()->cart->get_cart() as $values ) {
				if ( $values[ 'data' ]->has_weight() ) {
					$weight += (float) $values[ 'data' ]->get_weight() * $values[ 'quantity' ];
				}
			}

			return $weight;
		}, 20, 1 );
	}

}
