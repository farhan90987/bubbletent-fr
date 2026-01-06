<?php

use MarketPress\GermanMarket\Shipping\Provider\DHL\Shipping_Provider;
use function DeepCopy\deep_copy;

defined( 'ABSPATH' ) || exit;

class German_Market_Blocks_Register_Blocks_And_Integrations extends German_Market_Blocks_Methods {
	
    /**
     * Call actions and filters
     *
     * @return void
     */
    public function init() {
        
        add_action( 'block_categories_all', array( $this, 'register_block_category' ), 10, 2 );

        add_action( 'init', array( $this, 'register_blocks' ) );

        add_action(
		    'woocommerce_blocks_cart_block_registration',
		    function( $integration_registry ) {
		        $integration_registry->register( new GermanMarketBlockIntegration() );
		    }
		);

		add_action( 'woocommerce_blocks_loaded', function() {
			add_action(
				'woocommerce_blocks_checkout_block_registration',
				function( $integration_registry ) {
					$integration_registry->register( new GermanMarketBlockIntegration() );
				}
			);
		});

		add_action(
			'woocommerce_blocks_mini-cart_block_registration',
			function( $integration_registry ) {
				$integration_registry->register( new GermanMarketBlockIntegration() );
			}
		);

		add_action('woocommerce_blocks_loaded', function() {
			woocommerce_store_api_register_update_callback(
				array(
					'namespace' => 'german-market-blocks-shipping',
					'callback'  => function( $data ) {

						// Delivery Day
						if ( isset( $data[ 'delivery_day' ] ) ) {
							if ( '' !== $data[ 'delivery_day' ] ) {

								// Date Validation.
								// We are using 'deep_copy' function to clone the DateTime object.

								$preferred_first_day           = Shipping_Provider::calculate_first_preferred_delivery_day( true );
								$preferred_last_day            = deep_copy( $preferred_first_day );
								$preferred_last_day            = $preferred_last_day->add( DateInterval::createFromDateString( '6 days' ) );
								$preferred_last_day            = $preferred_last_day->add( DateInterval::createFromDateString( '86399 seconds' ) );
								$preferred_customer_day_string = sanitize_text_field( $data[ 'delivery_day' ] );

								// Check for valid date format e.g. 2023-12-31

								if ( ! preg_match( '/^([19|20]+\d\d)[-](0[1-9]|1[012])[-.](0[1-9]|[12][0-9]|3[01])$/', $preferred_customer_day_string ) ) {
									WC()->session->__unset( 'dhl_use_delivery_day' );
									WC()->session->__unset( '_wgm_dhl_service_preferred_day' );

									return;
								}

								// Check if we have a valid date.

								$check_date = date_parse( $preferred_customer_day_string );

								if ( ! empty( $check_date[ 'warnings' ] ) ) {
									WC()->session->__unset( 'dhl_use_delivery_day' );
									WC()->session->__unset( '_wgm_dhl_service_preferred_day' );

									return;
								}

								// Check if preferred date is between first and last possible date.

								$preferred_customer_day = new DateTime( $preferred_customer_day_string . ' ' . date( 'H:i:s', time() ), wp_timezone() );

								if ( ( $preferred_customer_day < $preferred_first_day ) || ( $preferred_customer_day > $preferred_last_day ) ) {
									WC()->session->__unset( 'dhl_use_delivery_day' );
									WC()->session->__unset( '_wgm_dhl_service_preferred_day' );

									return;
								}

								WC()->session->set( 'dhl_use_delivery_day', true );
								WC()->session->set( '_wgm_dhl_service_preferred_day', $data[ 'delivery_day' ] );
							} else {
								WC()->session->__unset( 'dhl_use_delivery_day' );
								WC()->session->__unset( '_wgm_dhl_service_preferred_day' );
							}
						}
					}
				)
			);

			woocommerce_store_api_register_update_callback(
				array(
					'namespace' => 'german-market-blocks-eu-vat-id',
					'callback'  => function( $data ) {

						$set_to_not_vat_exempt = true;
						$has_validation = false;
						$is_valid = false;

						if ( isset( $data[ 'show' ] ) && true === $data[ 'show' ] ) {
					
							if ( isset( $data[ 'country' ] ) && isset( $data[ 'vat_number' ] ) ) {
								
								if ( ! empty( trim( $data[ 'vat_number' ] ) ) ) {

									$eu_countries = WC()->countries->get_european_union_countries();

									// only validate if country is eu country
									if ( ! in_array( $data[ 'country' ], $eu_countries ) ) {

										$set_to_not_vat_exempt = true;
										$has_validation = false;
										$is_valid = true;

									} else {

										$validator = new WC_VAT_Validator( $data[ 'vat_number' ], $data[ 'country' ] );

										if ( $validator->is_valid() !== FALSE ) {
											
											if ( apply_filters( 'wcvat_check_vat_is_billing_country_base_country', $data[ 'country' ] !== WC()->countries->get_base_country(), $data[ 'country' ] ) ) {
												WGM_Session::add( 'eu_vatin_check_exempt', true );
												$set_to_not_vat_exempt = false;
												WC()->customer->set_is_vat_exempt( true );
											}

											WGM_Session::add( 'eu_vatin_check_billing_vat', $data[ 'vat_number' ] );
											WGM_Session::remove( 'eu_vatin_last_error' );
											$is_valid = true;

										} else {
											WGM_Session::add( 'eu_vatin_last_error', $validator->get_last_error_message() );
										}

										$has_validation = true;
									}
								}
							}
						}

						if ( $set_to_not_vat_exempt ) {
							WGM_Session::add( 'eu_vatin_check_exempt', false );
							//WGM_Session::remove( 'eu_vatin_check_billing_vat' );
							WC()->customer->set_is_vat_exempt( false );
						}

						if ( ! $has_validation ) {
							WGM_Session::remove( 'eu_vatin_last_error' );
							WGM_Session::remove( 'eu_vatin_check_billing_vat' );
						}

						if ( ! $is_valid ) {
							WGM_Session::add( 'eu_vatin_is_success', false );
							WGM_Session::remove( 'eu_vatin_check_billing_vat' );
						} else {
							WGM_Session::add( 'eu_vatin_is_success', true );
						}
					}
				)
			);
		});
    }

	 /**
	 * Register product related block categories.
	 *
	 * @param array[]                 $block_categories Array of categories for block types.
	 * @param WP_Block_Editor_Context $editor_context   The current block editor context.
     * @return Array
	 */
    public function register_block_category( $block_categories, $editor_context ) {
        
        $block_categories[] = array(
            'slug'  => 'german-market',
            'title' => __( 'German Market', 'woocommerce-german-market' ),
            'icon'  => null,
        );

        return $block_categories;
    }

    /**
	 * Register innerBlocks for cart and checkout block
	 * 
	 * @wp-hook init
	 */
	public function register_blocks() {
		
        register_block_type( GermanMarketBlocks::$package_path . '/build/blocks/cartinfo', array(
			'render_callback' => array( 'German_Market_Blocks_Core_Functions_For_Blocks', 'get_disclaimer_cart' ),
			'title'	=> __( 'German Market Cart Info', 'woocommerce-german-market' ),
			'description' => __( 'Show German Market cart info', 'woocommerce-german-market' ),
		) );

		register_block_type( GermanMarketBlocks::$package_path . '/build/blocks/checkout-checkboxes', array(
			'title'	=> __( 'German Market Checkboxes', 'woocommerce-german-market' ),
			'description' => __( 'Adds the German Market checkboxes to the checkout', 'woocommerce-german-market' ),
		) );

		if ( 'on' === get_option( 'wgm_add_on_woocommerce_eu_vatin_check', 'off' ) ) {
			register_block_type( GermanMarketBlocks::$package_path . '/build/blocks/eu-vat-id', array(
				'title'	=> __( 'EU VAT Number Check', 'woocommerce-german-market' ),
				'description' => __( 'Adds the input field from the German Market add-on "EU VAT Number Check" to the checkout', 'woocommerce-german-market' ),
			) );
		}

		if ( class_exists( '\MarketPress\GermanMarket\Shipping\Woocommerce_Shipping' ) ) {
			if ( true === \MarketPress\GermanMarket\Shipping\Woocommerce_Shipping::is_shipping_provider_activated() ) {
				register_block_type( GermanMarketBlocks::$package_path . '/build/blocks/woocommerce-shipping', array(
					'title'       => __( 'Additional Shipping Information', 'woocommerce-german-market' ),
					'description' => __( 'Add this block to enter a date of birth, client number and select a parcel shop or packstation', 'woocommerce-german-market' ),
				) );
			}
		}

		/**
		 * This filter allows our checkbox block to be moved within the parant block 
		 * (works in backend without filter, but not in frontend)
		 * 
		 * see: https://github.com/woocommerce/woocommerce-blocks/discussions/10951
		 */
		add_filter( '__experimental_woocommerce_blocks_add_data_attributes_to_namespace', function( $namespaces ) {
			return array_merge( $namespaces, array( 'german-market' ) );
		}, 10, 1 );
    }
}
