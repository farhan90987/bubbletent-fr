<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Dummy Payments Blocks integration
 *
 * @since 1.0.3
 */
final class WGM_Gateway_Sepa_Direct_Debit_Blocks_Support extends AbstractPaymentMethodType {

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
	protected $name = 'german_market_sepa_direct_debit';

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_german_market_sepa_direct_debit_settings', [] );
		require_once dirname( Woocommerce_German_Market::$plugin_filename ) . '/gateways/WGM_Gateway_Sepa_Direct_Debit.php';
		$this->gateway  = new WGM_Gateway_Sepa_Direct_Debit();
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
		$script_path       = '/build/gateways/sepa-direct-debit/blocks.js';
		$script_asset_path = dirname( \GermanMarketBlocks::$package_file ) . '/build/gateways/sepa-direct-debit/blocks.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require( $script_asset_path )
			: array(
				'dependencies' => array(),
				'version'      => \GermanMarketBlocks::$version
			);
		$script_url        = untrailingslashit( \GermanMarketBlocks::$package_url ) . $script_path;

		wp_register_script(
			'german-market-sepa-direct-debit-blocks',
			$script_url,
			$script_asset[ 'dependencies' ],
			$script_asset[ 'version' ],
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			//wp_set_script_translations( 'wc-dummy-payments-blocks', 'woocommerce-gateway-dummy', WC_Dummy_Payments::plugin_abspath() . 'languages/' );
		}

		return [ 'german-market-sepa-direct-debit-blocks' ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {

		$mandate_text = $this->get_setting( 'direct_debit_mandate' );

		if ( apply_filters( 'german_market_email_footer_the_content_filter', true, null ) ) {
            $mandate_preview = apply_filters( 'the_content', $mandate_text );
        } else {
            $mandate_preview = wpautop( WGM_Template::remove_vc_shortcodes( $mandate_text ) );
        }

		$confirmation_text = $this->get_setting( 'checkbox_confirmation_text' );
		if ( empty( $confirmation_text ) ) {
			$confirmation_text = __( 'I agree to the [link]sepa direct debit mandate[/link].', 'woocommerce-german-market' );
		}
		
		$confirmation_text = str_replace( 
			array( '[link]', '[/link]' ),
			array( '<a href="#" id="gm-sepa-mandate-preview-store" style="cursor: pointer;">', '</a>' ),
			$confirmation_text
		);

		return [
			'title'       				=> $this->get_setting( 'title' ),
			'description' 				=> $this->get_setting( 'description' ),
			'supports'   				=> array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] ),
			'fields'	  				=> $this->gateway::get_payment_fields(),
			'checkbox_confirmation' 	=> $this->get_setting( 'checkbox_confirmation' ),
			'creditor_information'		=> $this->get_setting( 'creditor_information' ),
			'creditor_identifier'		=> $this->get_setting( 'creditor_identifier' ),
			'creditor_account_holder'	=> $this->get_setting( 'creditor_account_holder' ),
			'creditor_iban'				=> $this->get_setting( 'iban' ),
			'creditor_bic'				=> $this->get_setting( 'bic' ),
			'mandate_id'				=> __( 'Will be communicated separately', 'woocommerce-german-market' ),
			'mandate_text'				=> $mandate_preview,
			'date_info'					=> date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) ),
			'checkbox_text'				=> $confirmation_text,
			'close_text'				=> apply_filters( 'german_market_sepa_close_mandate_preview', __( 'Close', 'woocommerce-german-market' ) ),

			'can_save_data'				=> 'on' === $this->get_setting( 'checkout_customer_can_save_payment_information' ) && is_user_logged_in(),
			'user_has_saved_data'		=> $this->gateway->get_stored_iban_from_user(),
			'user_saved_data'			=> $this->gateway->get_stored_data_from_user(),
			'save_data_checkbox_text'	=> apply_filters( 'german_market_sepa_checkout_save_data', __( 'Save SEPA payment data', 'woocommerce-german-market' ) ),
			'used_stored_data_text'		=> apply_filters( 'german_market_sepa_checkout_used_stored_data', __( 'Use stored SEPA payment data', 'woocommerce-german-market' ) ),
			'use_new_data_text'			=> apply_filters( 'german_market_sepa_checkout_enter_new_data', __( 'Enter new SEPA payment data', 'woocommerce-german-market' ) ),

		];
	}
}
