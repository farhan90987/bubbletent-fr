<?php

final class GermanMarketBlocks {

	/*
	* Static variables
	*/
	public static $version = null;
	private static $instance = null;
	public static $package_base_name = null;
	public static $package_path = null;
	public static $package_url = null;
	public static $package_file	= __FILE__;

	/**
	* Singleton get_instance
	*
	* @return GermanMarketBlocks
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			
			/**
			 * Initialize static vars
			 */
			self::$version = Woocommerce_German_Market::$version;
			self::$package_base_name = plugin_basename( __FILE__ );
			self::$package_path = plugin_dir_path( __FILE__ );
			self::$package_url = plugins_url( '', self::$package_base_name );

			/**
			 * Create singleton instance
			 */
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Plugin constructor
	 */
	private function __construct() {

		spl_autoload_register( array( $this, 'autoload' ) );

		add_action( 'before_woocommerce_init', array( $this, 'declare_compatibility' ) );
		
		GermanMarketStoreAPIIntegration::initialize();
		German_Market_Blocks_Register_Blocks_And_Integrations::get_instance();
		German_Market_Blocks_Core_Functions_For_Blocks::get_instance();
		German_Market_Blocks_Payment_Method_Registry::get_instance();
		German_Market_Product_Blocks_Registry::get_instance();

		if ( 'on' === get_option( 'woocommerce_de_avoid_free_items_in_cart', 'off' ) ) {
			German_Market_Blocks_Avoid_Free_Items_in_Cart::get_instance();
		}

		if ( 'on' === get_option( 'gm_order_review_checkboxes_logging', 'off' ) ) {
			German_Market_Blocks_Checkbox_Logging::get_instance();
		}

		if ( 'on' === get_option( 'wgm_add_on_woocommerce_eu_vatin_check', 'off' ) ) {
			German_Market_Blocks_Core_Functions_For_EU_Vat_Check::get_instance();
		}

		if ( 'on' === get_option( 'wgm_add_on_woocommerce_shipping', 'off' ) ) {
			German_Market_Blocks_Core_Functions_For_Woocommerce_Shipping::get_instance();
		}
	}

	/**
	 * autoload classes on demand
	 *
	 * @param string $class
	 * @return void
	 */
	public function autoload( $class ) {

		$classes_and_files = array(
			'GermanMarketBlockIntegration'                                 => 'german-market-block-integration.php',
			'GermanMarketStoreAPIIntegration'                              => 'german-market-store-api-integration.php',
			'WGM_Gateway_Purchase_On_Account_Blocks_Support'               => 'gateways/class-purchase-on-account-blocks.php',
			'WGM_Gateway_Sepa_Direct_Debit_Blocks_Support'                 => 'gateways/class-sepa-direkt-debit-blocks.php',
			'German_Market_Blocks_Register_Blocks_And_Integrations'        => 'includes/class-register-blocks-and-integrations.php',
			'German_Market_Blocks_Methods'                                 => 'includes/abstract-class-blocks-methods.php',
			'German_Market_Blocks_Core_Functions_For_Blocks'               => 'includes/class-core-functions-for-blocks.php',
			'German_Market_Blocks_Payment_Method_Registry'                 => 'includes/class-payment-method-registry.php',
			'German_Market_Blocks_Avoid_Free_Items_in_Cart'                => 'includes/class-avoid-free-items-in-cart.php',
			'German_Market_Blocks_Checkbox_Logging'                        => 'includes/class-checkbox-logging.php',
			'German_Market_Blocks_Compatibility_Limitations'               => 'includes/class-compatibility-limitations.php',
			'German_Market_Blocks_Utils'                                   => 'includes/class-utils.php',
			'German_Market_Blocks_Core_Functions_For_EU_Vat_Check'         => 'includes/class-core-functions-for-eu-vat-check.php',
			'German_Market_Blocks_Core_Functions_For_Woocommerce_Shipping' => 'includes/class-core-functions-for-woocommerce-shipping.php',
			'German_Market_Product_Blocks_Registry'                        => 'includes/class-product-blocks-registry.php',
			'German_Market_Product_Block'                                  => 'includes/product-blocks/abstract-class-product-block.php',
		);

		if ( isset( $classes_and_files[ $class ] ) ) {
			require_once( $classes_and_files[ $class ] );
		}
	}

	/**
	 * Declare WooCommerce Compatibility for Cart & Checkout block
	 * 
	 * @wp-hook before_woocommerce_init
	 */
	public function declare_compatibility() {
		
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        	\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', Woocommerce_German_Market::$plugin_filename, true );
   		}
	}
}
