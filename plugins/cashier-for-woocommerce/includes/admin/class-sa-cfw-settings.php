<?php
/**
 * Cashier Storewide Settings
 *
 * @package     cashier/includes/admin/
 *  author      StoreApps
 * @since       1.0.0
 * @version     1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Settings_Page' ) ) {
	include_once WP_PLUGIN_DIR . '/woocommerce/includes/admin/settings/class-wc-settings-page.php';
}

if ( ! class_exists( 'SA_CFW_Settings' ) ) {

	/**
	 * Class for handling storewide settings for Cashier
	 */
	class SA_CFW_Settings extends WC_Settings_Page {

		/**
		 * Variable to hold instance of SA_CFW_Settings
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->id    = 'sa-cfw-settings';
			$this->label = __( 'Cashier', 'woocommerce' );

			parent::__construct();
		}

		/**
		 * Get single instance of SA_CFW_Settings
		 *
		 * @return SA_CFW_Settings Singleton object of SA_CFW_Settings
		 */
		public static function get_instance() {
			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

	}

}

SA_CFW_Settings::get_instance();
