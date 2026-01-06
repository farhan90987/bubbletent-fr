<?php
use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Shipping_Provider;
use MarketPress\GermanMarket\Shipping\Woocommerce_Shipping;

/**
 * Class for integrating with WooCommerce Blocks
 */
class GermanMarketBlockIntegration implements IntegrationInterface {
	
	public $blocks;
	
	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {

		return 'german-market-blocks';
	}

	/**
	 * Registering our blocks and calling parent construct
	 * 
	 * @return void
	 */
	public function __construct() {
		
		$this->blocks = array( 

			'german-market-cart-block-cartinfo' => array(
				'directory'			=> 'cartinfo',
				'editor-script'		=> 'index.js',
				'editor-asset'		=> 'index.asset.php',
				'frontend-script'	=> false,
				'editor-style'		=> false,
			),

			'german-market-checkout-block-checkboxes' => array(
				'directory'			=> 'checkout-checkboxes',
				'editor-script'		=> 'index.js',
				'editor-asset'		=> 'index.assets.php',
				'frontend-script'	=> 'frontend.js',
				'frontend-asset'	=> 'frontend.asset.php',
				'editor-style'		=> 'style-index.css',
			)

		);

		if ( 'on' === get_option( 'wgm_add_on_woocommerce_eu_vatin_check', 'off' ) ) {

			$this->blocks[ 'german-market-checkout-block-eu-vat-id' ] = array(
				'directory'			=> 'eu-vat-id',
				'editor-script'		=> 'index.js',
				'editor-asset'		=> 'index.assets.php',
				'frontend-script'	=> 'frontend.js',
				'frontend-asset'	=> 'frontend.asset.php',
				'editor-style'		=> 'style-index.css',
			);

		}

		if ( class_exists( '\MarketPress\GermanMarket\Shipping\Woocommerce_Shipping' ) ) {
			if ( true === \MarketPress\GermanMarket\Shipping\Woocommerce_Shipping::is_shipping_provider_activated() ) {

				$this->blocks[ 'german-market-checkout-block-shipping' ] = array(
					'directory'       => 'woocommerce-shipping',
					'editor-script'   => 'index.js',
					'editor-asset'    => 'index.assets.php',
					'frontend-script' => 'frontend.js',
					'frontend-asset'  => 'frontend.asset.php',
					'editor-style'    => 'style-index.css',
				);

			}
		}

		$product_blocks = German_Market_Product_Blocks_Registry::get_instance()->get_block_integration_data(); 
		$this->blocks = array_merge( $this->blocks, $product_blocks );

	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {
		
		$this->register_integrations();

		$this->register_blocks_frontend_scripts();
		$this->register_blocks_editor_scripts();
		$this->register_blocks_styles();
	}

	/**
	 * Register all Integrations: checkoutFilters, Slots, gatewayFees, purchaseOnAccountTrigger
	 * 
	 * @return void
	 */
	private function register_integrations() {

		$script_path = '/build/integrations.js';
		$style_path = '/build/integrations.css';
		$script_url = plugins_url( $script_path, \GermanMarketBlocks::$package_file );
		$style_url = plugins_url( $style_path, \GermanMarketBlocks::$package_file );

		$script_asset_path = dirname( \GermanMarketBlocks::$package_file ) . '/build/integrations.asset.php';

		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_path ),
			);

		if ( 'on' === get_option( 'german_market_blocks_enqueue_integration_styles', 'on' ) ) {
			wp_enqueue_style(
				'german-market-blocks-integrations',
				$style_url,
				[],
				$this->get_file_version( $style_path )
			);
		}

		wp_register_script(
			'german-market-blocks-integrations',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		/*
		wp_set_script_translations(
			'german-market-blocks',
			'german-market-blocks',
			dirname( \WooCommerce_Example_Plugin_Assets::$package_file ) . '/languages'
		);
		*/
	}

	/**
	 * Register frontend scripts for blocks
	 *
	 * @return void
	 */
	public function register_blocks_frontend_scripts() {

		foreach ( $this->blocks as $handle => $block ) {

			if ( isset( $block[ 'frontend-script' ] )  && false !== $block[ 'frontend-script' ] ) {
				
				$script_path       = '/build/blocks/' . $block[ 'directory' ] . '/' . $block[ 'frontend-script' ];
				$script_url        = plugins_url( $script_path, __FILE__ );
				$script_asset_path = dirname( __FILE__ ) . '/build/blocks/' . $block[ 'directory' ] . '/' . $block[ 'frontend-asset' ] ;
				$script_asset      = file_exists( $script_asset_path )
					? require $script_asset_path
					: array(
						'dependencies' => array(),
						'version'      => $this->get_file_version( $script_asset_path ),
					);
		
				wp_register_script(
					$handle,
					$script_url,
					$script_asset[ 'dependencies' ],
					$script_asset[ 'version' ],
					true
				);
			}
		}
	}

	/**
	 * Register editor scripts for blocks
	 *
	 * @return void
	 */
	public function register_blocks_editor_scripts() {

		foreach ( $this->blocks as $handle => $block ) {

			if ( isset( $block[ 'editor-script' ] )  && false !== $block[ 'editor-script' ] ) {
				
				$script_path       = '/build/blocks/' . $block[ 'directory' ] . '/' . $block[ 'editor-script' ];
				$script_url        = plugins_url( $script_path, __FILE__ );
				$script_asset_path = dirname( __FILE__ ) . '/build/blocks/' . $block[ 'directory' ] . '/' . $block[ 'editor-asset' ] ;
				$script_asset      = file_exists( $script_asset_path )
					? require $script_asset_path
					: array(
						'dependencies' => array(),
						'version'      => $this->get_file_version( $script_asset_path ),
					);
		
				wp_register_script(
					$handle . '-editor',
					$script_url,
					$script_asset[ 'dependencies' ],
					$script_asset[ 'version' ],
					true
				);

				if ( isset( $block[ 'has-editor-script-translation' ] ) && true === $block[ 'has-editor-script-translation' ] ) {
					wp_set_script_translations( $handle . '-editor', 'woocommerce-german-market', GermanMarketBlocks::$package_path . 'languages' );
				}
			}
		}
	}

	/**
	 * Register styles for blocks
	 *
	 * @return void
	 */
	public function register_blocks_styles() {

		foreach ( $this->blocks as $handle => $block ) {

			if ( apply_filters( 'german_market_register_blocks_styles_exclude', false, $handle, $block ) ) {
				continue;
			}
			
			if ( isset( $block[ 'editor-style' ] )  && false !== $block[ 'editor-style' ] ) {
				
				$style_path  = '/build/blocks/' . $block[ 'directory' ] . '/' . $block[ 'editor-style' ];

				$style_url  = plugins_url( $style_path, __FILE__ );
				wp_enqueue_style(
					$handle,
					$style_url,
					[],
					$this->get_file_version( $style_path )
				);
			}
		}
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {

		$scripts = array( 'german-market-blocks-integrations' );
		foreach ( $this->blocks as $handle => $block ) {
			if ( isset( $block[ 'frontend-script' ] )  && false !== $block[ 'frontend-script' ] ) {
				$scripts[] = $handle;
			}
		}

		return $scripts;
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		$scripts = array( 'german-market-blocks-integrations' );
		foreach ( $this->blocks as $handle => $block ) {
			if ( isset( $block[ 'editor-script' ] )  && false !== $block[ 'editor-script' ] ) {
				$scripts[] = $handle . '-editor';
			}
		}

		return $scripts;
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_script_data() {

		$german_market_default_texts = WGM_Helper::get_translatable_options();
		$estimate_cart_text          = isset( $german_market_default_texts[ 'woocommerce_de_estimate_cart_text' ] ) ? $german_market_default_texts[ 'woocommerce_de_estimate_cart_text' ] : '';
		$order_button_text           = isset( $german_market_default_texts[ 'woocommerce_de_order_button_text' ] ) ? $german_market_default_texts[ 'woocommerce_de_order_button_text' ] : '';

		$default_manual_order_confirmation_payment_methods_hint_text = isset( $german_market_default_texts[ 'woocommerce_de_manual_order_confirmation_payment_methods_hint_text' ] ) ? $german_market_default_texts[ 'woocommerce_de_manual_order_confirmation_payment_methods_hint_text' ] : '';

		$today           = current_time('Y-m-d' );
		$today_object    = new DateTime( $today );
		$date_min_object = clone $today_object;

		if ( 'on' !== get_option( Woocommerce_Shipping::build_german_market_addon_option_key( 'dhl' ) ) ) {
			$date_of_birth_required             = false;
			$client_number_required             = false;
			$preferred_day_service_price_string = '';
			$delivery_day_min_date              = '';
			$delivery_day_max_date              = '';
			$date_max_object                    = clone $today_object;
			$selected_location_id               = '';
			$selected_location_address          = '';
			$dhl_client_id                      = '';
		} else {
			$date_of_birth_required   = ( class_exists( 'German_Market_Blocks_Core_Functions_For_Woocommerce_Shipping' ) && German_Market_Blocks_Core_Functions_For_Woocommerce_Shipping::is_date_of_birth_required() );
			$client_number_required   = ( class_exists( 'German_Market_Blocks_Core_Functions_For_Woocommerce_Shipping' ) && German_Market_Blocks_Core_Functions_For_Woocommerce_Shipping::is_client_number_required() );

			$preferred_day_service_price        = class_exists( '\MarketPress\GermanMarket\Shipping\Provider\DHL\Shipping_Provider' ) && method_exists( Shipping_Provider::$options, 'get_option' ) ? Shipping_Provider::$options->get_option( 'service_preferred_day_fee', 1.2 ) : 0;
			$preferred_day_service_price_string = '';

			if ( $preferred_day_service_price > 0 ) {
				$preferred_day_service_price_string = '<p class="wgm-shipping-dhl-preferred-day-price-string">' . sprintf( __( 'There is an additional charge of <span class="price">%s</span> (%s) for this service.', 'woocommerce-german-market' ), wc_price( $preferred_day_service_price ), __( 'incl VAT', 'woocommerce-german-market' ) ) . '</p>';
			}

			$date_min_object->sub( new DateInterval( 'P' . apply_filters( 'wgm_shipping_maximal_customer_age_in_years', 100 ) . 'Y' ) );
			$date_max_object = clone $today_object;
			$date_max_object->sub( new DateInterval( 'P' . apply_filters( 'wgm_shipping_minimal_customer_age_in_years', 14 ) . 'Y' ) );

			$delivery_day_min_date = '';
			$delivery_day_max_date = '';
			if ( class_exists( '\MarketPress\GermanMarket\Shipping\Provider\DHL\Shipping_Provider' ) ) {
				$delivery_day_min_object = Shipping_Provider::calculate_first_preferred_delivery_day( true );
				$delivery_day_max_object = clone $delivery_day_min_object;
				$delivery_day_max_object->add( new DateInterval( 'P6D' ) );
				$delivery_day_min_date = $delivery_day_min_object->format( 'Y-m-d' );
				$delivery_day_max_date = $delivery_day_max_object->format( 'Y-m-d' );
			}

			// Check if we have a selected pickup location.

			$dhl_client_id             = ( isset( WC()->session ) ) ? WC()->session->get( 'wc_shipping_dhl_client_number' ) : '';
			$selected_location_id      = '';
			$selected_location_address = '';
			$chosen_shipping_method    = ( isset( WC()->session ) && method_exists( WC()->session, 'get' ) && WC()->session->get( 'chosen_shipping_methods' ) !== null ) ? WC()->session->get( 'chosen_shipping_methods' )[ 0 ] : '';

			if ( empty( $dhl_client_id ) ) {
				if ( is_user_logged_in() ) {
					$dhl_client_id = get_user_meta( get_current_user_id(), 'wc_shipping_dhl_client_number', true );
				} else {
					$dhl_client_id = '';
				}
			}
		}

		// Language.

		$datepicker_language = substr( get_locale(), 0, 2 );
		$datepicker_language = ( 'en' === $datepicker_language || 'de' === $datepicker_language ) ? $datepicker_language : 'en';

		if ( ! empty( $chosen_shipping_method ) && isset( WC()->customer ) ) {
			$country  = WC()->customer->get_shipping_country();
			$city     = WC()->customer->get_shipping_city();
			$street   = WC()->customer->get_shipping_address_1();
			$postcode = WC()->customer->get_shipping_country();
			if ( 'on' === get_option( 'wgm_add_on_woocommerce_shipping_dhl', 'off' ) ) {
				if ( class_exists( '\MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Parcels' ) ) {
					if ( false !== strpos( $chosen_shipping_method, \MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Parcels::get_instance()->id ) ) {
						$selected_location_id = WC()->session->get( \MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Parcels::get_instance()->field_id );
						if ( ! empty( $selected_location_id ) ) {
							$selected_location_address = \MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Parcels::get_instance()->get_selected_terminal( $country, $city, $street, $postcode, $selected_location_id );
						}
					}
				}
				if ( class_exists( '\MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Packstation' ) ) {
					if ( false !== strpos( $chosen_shipping_method, \MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Packstation::get_instance()->id ) ) {
						$selected_location_id = WC()->session->get( \MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Packstation::get_instance()->field_id );
						if ( ! empty( $selected_location_id ) ) {
							$selected_location_address = \MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Packstation::get_instance()->get_selected_terminal( $country, $city, $street, $postcode, $selected_location_id );
						}
					}
				}
			}
			if ( 'on' === get_option( 'wgm_add_on_woocommerce_shipping_dpd', 'off' ) ) {
				if ( class_exists( '\MarketPress\GermanMarket\Shipping\Provider\DPD\Methods\Parcels' ) ) {
					if ( false !== strpos( $chosen_shipping_method, \MarketPress\GermanMarket\Shipping\Provider\DPD\Methods\Parcels::get_instance()->id ) ) {
						$selected_location_id = WC()->session->get( \MarketPress\GermanMarket\Shipping\Provider\DPD\Methods\Parcels::get_instance()->field_id );
						if ( ! empty( $selected_location_id ) ) {
							$selected_location_address = \MarketPress\GermanMarket\Shipping\Provider\DPD\Methods\Parcels::get_instance()->get_selected_terminal( $country, $city, $street, $postcode, $selected_location_id );
						}
					}
				}
			}
		}

		return array(

	    	'move_order_button'	                                  => get_option( 'german_market_blocks_move_order_button', 'off' ),
			'woocommerce_de_estimate_cart'                        => get_option( 'woocommerce_de_estimate_cart', 'on' ),
			'woocommerce_de_estimate_cart_text'                   => wp_kses_post( get_option( 'woocommerce_de_estimate_cart_text', $estimate_cart_text ) ),
	    	'woocommerce_de_order_button_text'                    => get_option( 'woocommerce_de_order_button_text', $order_button_text ),
			'manual_order_confirmation_payment_methods_hint_text' => get_option( 'woocommerce_de_manual_order_confirmation_payment_methods_hint_text', $default_manual_order_confirmation_payment_methods_hint_text ),
			'manual_order_confirmation_payment_methods_show_text' => ( 'on' === get_option( 'woocommerce_de_manual_order_confirmation', 'off' ) ) && ( 'on' === get_option( 'woocommerce_de_manual_order_confirmation_payment_methods_only_pay_order', 'on' ) ) ? 'on' : 'off',
			'manual_order_confirmation'							  => get_option( 'woocommerce_de_manual_order_confirmation', 'off' ),

			'editor_checkbox_1_label' => __( 'In this block your checkout checkboxes of the plugin "German Market" will be output.', 'woocommerce-german-market' ),
			'editor_checkbox_2_label' => sprintf(
				__( 'The settings for your "German Market" checkout checkboxes can be found in <a href="%s" target="_blank">this menu</a>.', 'woocommerce-german-market' ),
				get_admin_url() . 'admin.php?page=german-market&tab=general&sub_tab=checkout_checkboxes'
			),
			// Setup Frontend Data for EU VAT ID Addon.

		    'eu_vat_id_label' =>  get_option( 'vat_options_label', __( 'EU VAT Identification Number (VATIN)', 'woocommerce-german-market' ) ),

			'use_extension_cart_update_in_gateway_fees' => apply_filters( 'german_market_blocks_use_extension_cart_update_in_gateway_fees', 'yes' ),
			'use_extension_cart_update_in_gateway_tax_info_fees' => apply_filters( 'german_market_blocks_use_extension_cart_update_in_gateway_tax_info_fees', 'yes' ),

			// Setup Frontend Data for Shipping Addon.

			'shipping_label'                           => __( 'Additional shipping information', 'woocommerce-german-market' ),
			'datepicker_language'                      => $datepicker_language,
		    'delivery_day_label'                       => __( 'Delivery Day', 'woocommerce-german-market' ),
		    'delivery_day_fee_text'                    => $preferred_day_service_price_string,
		    'delivery_day_min_date'                    => $delivery_day_min_date,
		    'delivery_day_max_date'                    => $delivery_day_max_date,
		    'delivery_day_value'                       => ( isset( WC()->session ) && ! empty( WC()->session->get( '_wgm_dhl_service_preferred_day' ) ) ) ? WC()->session->get( '_wgm_dhl_service_preferred_day' ) : '',
		    'delivery_day_enabled'                     => 'on' === get_option( 'wgm_dhl_service_preferred_day_enabled', 'off' ),
			'date_of_birth_label'                      => __( 'Date of Birth', 'woocommerce-german-market' ),
			'date_of_birth_value'                      => ( isset( WC()->session ) && ! empty( WC()->session->get( 'wc_shipping_dhl_dob' ) ) ) ? WC()->session->get( 'wc_shipping_dhl_dob' ) : '',
		    'date_of_birth_min_date'                   => $date_min_object->format( 'Y-m-d' ),
		    'date_of_birth_max_date'                   => $date_max_object->format( 'Y-m-d' ),
			'date_of_birth_required'                   => $date_of_birth_required,
			'date_of_birth_error_text'                 => sprintf( __( 'Please enter your %s!', 'woocommerce-german-market' ), __( 'Date of Birth', 'woocommerce-german-market' ) ),
		    'client_number_label'                      => __( 'DHL Client Number', 'woocommerce-german-market' ),
			'client_number_required'                   => $client_number_required,
			'client_number_error_text'                 => sprintf( __( 'Please enter your %s!', 'woocommerce-german-market' ), __( 'DHL client number', 'woocommerce-german-market' ) ),
			'pickup_location_ajax_url'                 => WC_AJAX::get_endpoint( '%%endpoint%%' ),
		    'pickup_location_ajax_nonce'               => wp_create_nonce( 'save-terminal' ),
			'pickup_location_text'                     => __( 'Pickup Location', 'woocommerce-german-market' ),
			'pickup_location_anchor_text'              => __( 'Open Map', 'woocommerce-german-market' ),
			'pickup_location_map_available'            => ( ( 'on' === get_option( 'wgm_dhl_google_map_enabled' ) ) && ( '' !== get_option( 'wgm_dhl_google_map_key', '' ) ) ),
		    'pickup_location_google_map_key_dhl'       => get_option( 'wgm_dhl_google_map_key', '' ),
		    'pickup_location_google_map_base_url'      => 'https://maps.googleapis.com/maps/api/geocode/json',
			'pickup_location_selected_id'              => $selected_location_id,
			'pickup_location_selected_address'         => $selected_location_address,
			'pickup_location_dhl_client_id'            => $dhl_client_id,
			'pickup_location_modal_text_zipcode'       => __( 'Zip Code', 'woocommerce-german-market' ),
		    'pickup_location_modal_text_city'          => __( 'City', 'woocommerce-german-market' ),
		    'pickup_location_modal_text_street'        => __( 'Street', 'woocommerce-german-market' ),
			'pickup_location_modal_text_search'        => __( 'Search', 'woocommerce-german-market' ),
		    'pickup_location_modal_text_select'        => __( 'Select', 'woocommerce-german-market' ),
			'pickup_location_modal_text_packstation'   => __( 'Packstation', 'woocommerce-german-market' ),
			'pickup_location_modal_text_address'       => __( 'Address', 'woocommerce-german-market' ),
			'pickup_location_modal_text_opening_hours' => __( 'Opening Hours', 'woocommerce-german-market' ),
			'pickup_location_modal_text_monday'        => __( 'Monday', 'woocommerce-german-market' ),
		    'pickup_location_modal_text_tuesday'       => __( 'Tuesday', 'woocommerce-german-market' ),
		    'pickup_location_modal_text_wednesday'     => __( 'Wednesday', 'woocommerce-german-market' ),
		    'pickup_location_modal_text_thursday'      => __( 'Thursday', 'woocommerce-german-market' ),
		    'pickup_location_modal_text_friday'        => __( 'Friday', 'woocommerce-german-market' ),
		    'pickup_location_modal_text_saturday'      => __( 'Saturday', 'woocommerce-german-market' ),
		    'pickup_location_modal_text_sunday'        => __( 'Sunday', 'woocommerce-german-market' ),
			'pickup_location_modal_text_contact'       => __( 'Contact', 'woocommerce-german-market' ),
			'pickup_location_select_choose_option'     => __( 'Choose a Pickup Point', 'woocommerce-german-market' ),
		    'pickup_location_select_search'            => __( 'Search...', 'woocommerce-german-market' ),
		    'empty_additional_shipping_text'           => __( 'No additional shipping information required.', 'woocommerce-german-market' ),
		    'addon_url'                                => plugin_dir_url( 'woocommerce-german-market/woocommerce-german-market.php' ) . '/add-ons/woocommerce-shipping',
	    );
	}

	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( $file ) {

		$file = dirname( \GermanMarketBlocks::$package_file ) . $file;

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}
		
		// As above, let's assume that WooCommerce_Example_Plugin_Assets::VERSION resolves to some versioning number our
		// extension uses.
		return \GermanMarketBlocks::$version;
	}
}
