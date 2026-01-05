<?php
/**
 * Compatibility class for WooCommerce 3.0.0
 *
 * @package WC-compat
 *  author StoreApps
 * @version 1.1.0
 * @since WooCommerce 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SA_WC_Compatibility_3_0' ) ) {

	/**
	 * Class to check WooCommerce version is greater than and equal to 3.0.0
	 */
	class SA_WC_Compatibility_3_0 {

		/**
		 * Function to get WooCommerce version
		 *
		 * @return string version or null.
		 */
		public static function get_wc_version() {
			if ( defined( 'WC_VERSION' ) && WC_VERSION ) {
				return WC_VERSION;
			}
			if ( defined( 'WOOCOMMERCE_VERSION' ) && WOOCOMMERCE_VERSION ) {
				return WOOCOMMERCE_VERSION;
			}
			return null;
		}

		/**
		 * Function to compare current version of WooCommerce on site with active version of WooCommerce
		 *
		 * @param string $version Version number to compare.
		 * @return bool
		 */
		public static function is_wc_greater_than( $version ) {
			return version_compare( self::get_wc_version(), $version, '>' );
		}

		/**
		 * Function to check if WooCommerce is Greater Than And Equal To 3.0.0
		 *
		 * @return boolean
		 */
		public static function is_wc_gte_30() {
			return self::is_wc_greater_than( '2.6.14' );
		}

	}

}
