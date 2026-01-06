<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Dummy Payments Blocks integration
 *
 * @since 1.0.3
 */
final class WGM_Gateway_Purchase_On_Account_Blocks_Support extends AbstractPaymentMethodType {

	/**
	 * The gateway instance.
	 *
	 * @var WC_Gateway_Dummy
	 */
	private $gateway;

	/**
	 * Payment method name/id/slug.
	 *
	 * @var string
	 */
	protected $name = 'german_market_purchase_on_account';

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_german_market_purchase_on_account_settings', [] );
		require_once dirname( Woocommerce_German_Market::$plugin_filename ) . '/gateways/WGM_Gateway_Purchase_On_Account.php';
		$this->gateway  = new WGM_Gateway_Purchase_On_Account();
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return true; //$this->gateway->is_available();
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$script_path       = '/build/gateways/purchase-on-account/blocks.js';
		$script_asset_path = dirname( \GermanMarketBlocks::$package_file ) . '/build/gateways/purchase-on-account/blocks.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require( $script_asset_path )
			: array(
				'dependencies' => array(),
				'version'      => \GermanMarketBlocks::$version
			);
		$script_url        = untrailingslashit( \GermanMarketBlocks::$package_url ) . $script_path;

		wp_register_script(
			'german-market-purchase-on-account-blocks',
			$script_url,
			$script_asset[ 'dependencies' ],
			$script_asset[ 'version' ],
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			//wp_set_script_translations( 'wc-dummy-payments-blocks', 'woocommerce-gateway-dummy', WC_Dummy_Payments::plugin_abspath() . 'languages/' );
		}

		return [ 'german-market-purchase-on-account-blocks' ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'supports'    => array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] ),
			'deactivate_ship_to_different_address' => $this->gateway->deactivate_ship_to_different_address,
		];
	}
}
