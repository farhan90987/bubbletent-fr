<?php
/**
 * A class to add custom data to the store API cart resource.
 *
 * @package WooCommerce Checkout Integration Example
 * @since   1.0.0
 */

use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;
use Automattic\WooCommerce\StoreApi\Exceptions\InvalidCartException;
use Automattic\WooCommerce\Blocks\StoreApi\Schemas\CheckoutSchema;

class GermanMarketStoreAPIIntegration {

	/**
	 * Stores Rest Extending instance.
	 *
	 * @var ExtendRestApi
	 */
	private static $extend;
	/**
	 * Plugin identifier, unique to each plugin.
	 *
	 * @var string
	 */
	const IDENTIFIER           = 'german-market-store-api-integration';
	const IDENTIFIER_EU_VAT_ID = 'german-market-eu-vat-id-store-api-integration';
	const IDENTIFIER_SHIPPING  = 'german-market-shipping-store-api-integration';

	/**
	 * Bootstraps the class and hooks required data.
	 */
	public static function initialize() {

		// Extend StoreAPI.
		self::$extend = Automattic\WooCommerce\StoreApi\StoreApi::container()->get( Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema::class );
		self::extend_store();

		add_action( 'woocommerce_store_api_checkout_order_processed', array( __CLASS__, 'checkout_order_processed' ) );
	}

	/**
	 * Register cart data handler.
	 */
	public static function extend_store() {

		if ( function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
			woocommerce_store_api_register_endpoint_data(
				array(
					'endpoint'        => CartSchema::IDENTIFIER,
					'namespace'       => self::IDENTIFIER,
					'data_callback'   => array( __CLASS__, 'extend_cart_data' ),
					'schema_callback' => array( __CLASS__, 'extend_cart_schema' ),
					'schema_type'     => ARRAY_A,
				)
			);
		}

		if ( is_callable( [ self::$extend, 'register_endpoint_data' ] ) ) {

			if ( German_Market_Blocks_Utils::has_checkout_page_a_german_market_block() ) {

				self::$extend->register_endpoint_data(
					[
						'endpoint'        => CheckoutSchema::IDENTIFIER,
						'namespace'       => self::IDENTIFIER,
						'schema_callback' => [ __CLASS__, 'extend_checkout_schema' ],
						'schema_type'     => ARRAY_A,
					]
				);
			}

			if ( German_Market_Blocks_Utils::has_checkout_page_eu_vat_id_block() ) {

				self::$extend->register_endpoint_data(
					[
						'endpoint'        => CheckoutSchema::IDENTIFIER,
						'namespace'       => self::IDENTIFIER_EU_VAT_ID,
						'schema_callback' => [ __CLASS__, 'extend_checkout_schema_eu_vat_id' ],
						'schema_type'     => ARRAY_A,
					]
				);
			}

			if ( German_Market_Blocks_Utils::has_checkout_page_shipping_block() && ( 'on' === get_option( 'wgm_add_on_woocommerce_shipping_dhl', 'off' ) ) ) {

				self::$extend->register_endpoint_data(
					[
						'endpoint'        => CheckoutSchema::IDENTIFIER,
						'namespace'       => self::IDENTIFIER_SHIPPING,
						'schema_callback' => [ __CLASS__, 'extend_checkout_schema_shipping' ],
						'schema_type'     => ARRAY_A,
					]
				);
			}
		}
	}

	/**
	 * Register checkout checkboxes into the Checkout endpoint.
	 *
	 * @return array Registered schema.
	 *
	 */
	public static function extend_checkout_schema() {

		$schema = array();
		$internal_use = true;
		$extend_cart_data = self::extend_cart_data( $internal_use );
		foreach ( $extend_cart_data[ 'checkboxes' ] as $checkbox ) {
			$schema[ $checkbox[ 'id' ] ] = array(
					'description' => __( 'Value for the checkbox:', 'woocommerce-german-market' ) . ' ' . $checkbox[ 'id' ],
					'type'        => 'bool', // Define the type, this should be a `string`,
					'context'     => array( 'view' ), // Define the contexts this should appear in This should be an array containing `view` and `edit`,
					'readonly'    => true, // Using a boolean value, make this field readonly,
					'optional'    => true, // Using a boolean value, make this field optional,
			);
		}
		
		return $schema;
	}

	/**
	 * Register eu vat id field into the Checkout endpoint.
	 *
	 * @return array Registered schema.
	 *
	 */
	public static function extend_checkout_schema_eu_vat_id() {

		$schema = array(
			'billing_vat' => array(
				'description' => __( 'Value for the billing vat', 'woocommerce-german-market' ),
				'type'        => 'string', // Define the type, this should be a `string`,
				'context'     => array( 'view' ), // Define the contexts this should appear in This should be an array containing `view` and `edit`,
				'readonly'    => true, // Using a boolean value, make this field readonly,
				'optional'    => true, // Using a boolean value, make this field optional,
			)
		);

		return $schema;
	}

	/**
	 * Register eu vat id field into the Checkout endpoint.
	 *
	 * @return array Registered schema.
	 *
	 */
	public static function extend_checkout_schema_shipping() {

		$schema = array();

		$schema[ 'date_of_birth' ] = array(
			'description' => __( 'Value for the date of birth', 'woocommerce-german-market' ),
			'type'        => array( 'string', null ), // Define the type, this should be a `string`,
			'context'     => array( 'view' ), // Define the contexts this should appear in This should be an array containing `view` and `edit`,
			'readonly'    => true, // Using a boolean value, make this field readonly,
			'optional'    => true, // Using a boolean value, make this field optional,
		);

		if ( class_exists( 'German_Market_Blocks_Core_Functions_For_Woocommerce_Shipping' ) && class_exists( 'MarketPress\GermanMarket\Shipping\Provider\DHL\Shipping_Provider' ) ) {
			$schema[ 'client_number' ] = array(
				'description' => __( 'Value for the DHL client number', 'woocommerce-german-market' ),
				'type'        => array( 'string', null ), // Define the type, this should be a `string`,
				'context'     => array( 'view' ), // Define the contexts this should appear in This should be an array containing `view` and `edit`,
				'readonly'    => true, // Using a boolean value, make this field readonly,
				'optional'    => true, // Using a boolean value, make this field optional,
			);
		}

		if ( class_exists( 'German_Market_Blocks_Core_Functions_For_Woocommerce_Shipping' ) && class_exists( 'MarketPress\GermanMarket\Shipping\Provider\DHL\Shipping_Provider' ) && ( 'on' === get_option( 'wgm_dhl_service_preferred_day_enabled', 'off' ) ) ) {
			$schema[ 'delivery_day' ] = array(
				'description' => __( 'Value for the delivery day', 'woocommerce-german-market' ),
				'type'        => array( 'string', null ), // Define the type, this should be a `string`,
				'context'     => array( 'view' ), // Define the contexts this should appear in This should be an array containing `view` and `edit`,
				'readonly'    => true, // Using a boolean value, make this field readonly,
				'optional'    => true, // Using a boolean value, make this field optional,
			);
		}

		return $schema;
	}

	/**
	 * Adds extension data to cart route responses.
	 *
	 * @return array
	 */
	public static function extend_cart_data( $internal_use = false ) {

		$gateway_purchase_on_account = new WGM_Gateway_Purchase_On_Account();
		$gateway_sepa = new WGM_Gateway_Sepa_Direct_Debit();

		// defaults for checkbox texts
		$default_shipping_provider_text = __( 'I agree that my personal data is send to the shipping service provider.', 'woocommerce-german-market' );
		$default_shipping_error_text    = __( 'You have to agree that your personal data is send to the shipping service provider.', 'woocommerce-german-market' );
		$digital_text                   = __( 'For digital content: You explicitly agree that we continue with the execution of our contract before expiration of the revocation period. You hereby also declare you are aware of the fact that you lose your right of revocation with this agreement.', 'woocommerce-german-market' );
		$digital_error_text             = __( 'Please confirm the waiver for your rights of revocation regarding digital content.', 'woocommerce-german-market' );
		$digital_info                   = __( 'Notice: Digital content are products not being delivered on any physical medium (e.g. software downloads, e-books etc.).', 'woocommerce-german-market' );
		$age_rating_default_text        = __( 'I confirm that I am at least [age] years of age.', 'woocommerce-german-market' );
		$age_rating_text                = get_option( 'german_market_checkbox_age_rating_text', $age_rating_default_text );
		$age_rating_default_error_text  = __( 'You have to confirm that you are at least [age] years of age.', 'woocommerce-german-market' );
		$age_rating_error_text          = get_option( 'german_market_checkbox_age_rating_error_text', $age_rating_default_error_text );

		// shipping provider checkbox
		$is_shipping_provider_active = get_option( 'german_market_checkbox_3_shipping_service_provider_activation', 'on' );
		
		if ( ! $internal_use ) {
			if ( $is_shipping_provider_active === 'on' ) {
				if ( ! WC()->cart->needs_shipping() ) {
					$is_shipping_provider_active = 'off';
				} else {
					
					$chosen_methods = array();
					if ( ! is_null( WC()->session ) ) {
						if ( isset( WC()->session->chosen_shipping_methods ) ) {
							$chosen_methods = WC()->session->chosen_shipping_methods;
						}
					}

					foreach ( $chosen_methods as $method ) {
						if ( ( str_replace( 'local_pickup', '', $method ) !== $method ) || ( str_replace( 'pickup_location', '', $method ) !== $method ) ) {
							$is_shipping_provider_active = 'off';
							break;
						}
					}
				}
			}
		}
		
		// digital checkbox
		$is_digital_checkbox_active = get_option( 'german_market_checkbox_2_digital_content_activation', 'on' );
		if ( ! $internal_use ) {
			if ( $is_digital_checkbox_active === 'on' ) {
				$is_digital_cart = WGM_Template::is_digital_cart_or_order();
				$is_digital_checkbox_active = ( $is_digital_cart != 'not_digital' ) ? 'on' : 'off';
			}
		}

		// age rating
		$is_age_rating_checkbox_active = 'off';
		if ( ! $internal_use ) {
			if ( ( get_option( 'german_market_age_rating', 'off' ) == 'on' ) && ( get_option( 'german_market_checkbox_age_rating_activation', 'on' ) == 'on' ) ) {

				$age_of_cart_or_order = WGM_Age_Rating::get_age_rating_of_cart_or_order();

				if ( $age_of_cart_or_order > 0 ) {
					$is_age_rating_checkbox_active = 'on';
					$age_rating_text = str_replace( '[age]', $age_of_cart_or_order, $age_rating_text );
					$age_rating_error_text = str_replace( '[age]', $age_of_cart_or_order, $age_rating_error_text );
				}
			}
		}

		// shipping tax strings
		$shipping_tax_strings = array();
		if ( WC()->cart ) {
			foreach ( WC()->cart->get_shipping_packages() as $package_id => $package ) {
				if ( WC()->session->__isset( 'shipping_for_package_'.$package_id ) ) {
					// Loop through shipping rates for the current package
					foreach ( WC()->session->get( 'shipping_for_package_'.$package_id )['rates'] as $shipping_rate ) {
						$tax_string = German_Market_Blocks_Core_Functions_For_Blocks::add_shipping_tax_notice( '', $shipping_rate );
						$shipping_tax_strings[ $shipping_rate->get_id() ] = html_entity_decode( strip_tags( $tax_string ) );
					}
				}
			}
		}

		// shipping total tax
		$cart_shipping_total_string = '';
		
		if ( WC()->cart ) {
			$cart_shipping_total_string = German_Market_Blocks_Core_Functions_For_Blocks::get_shipping_cart_total_taxes( WC()->cart );			
		}

		// total tax
		$cart_total_string = '';
		if ( WC()->cart ) {
			$cart_total_string = German_Market_Blocks_Core_Functions_For_Blocks::get_cart_tax_total_string( WC()->cart );
			if ( substr( $cart_total_string, 0, 3 ) === "\A " ) {
				$cart_total_string = substr( $cart_total_string, 3 );
			}
		}

		$cart_total_string = apply_filters( 'german_market_cart_total_tax_string_blocks', $cart_total_string, WC()->cart );

		// fee tax strings
		$fees = array();
		if ( WC()->cart ) {
			foreach ( WC()->cart->get_fees() as $key => $fee ) {
				$fees[ $key ] = $fee;
				$tax_info = str_replace( '<br class="wgm-break" />', "\A ", apply_filters( 'woocommerce_cart_totals_fee_html', '', $fee ) );
				if ( substr( $tax_info, 0, 3 ) === "\A " ) {
					$tax_info = substr( $tax_info, 3 );
				}
				$fees[ $key ]->tax_info = html_entity_decode( strip_tags( $tax_info ) );
			}
		}

		// EU VAT ID Check
		$is_local_pickup = false;
		if ( ! $internal_use ) {
			if ( WC()->cart->needs_shipping() ) {
				
				$chosen_methods = array();
				if ( ! is_null( WC()->session ) ) {
					if ( isset( WC()->session->chosen_shipping_methods ) ) {
						$chosen_methods = WC()->session->chosen_shipping_methods;
					}
				}

				foreach ( $chosen_methods as $method ) {
					if ( ( str_replace( 'local_pickup', '', $method ) !== $method ) || ( str_replace( 'pickup_location', '', $method ) !== $method ) ) {
						$is_local_pickup = true;
						break;
					}
				}
			}
		}

		$is_local_pickup = apply_filters( 'wcvat_is_local_pickup', $is_local_pickup );

		$user_vat_id = '';
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$user_vat_id = get_user_meta( $current_user->ID, 'billing_vat', true );
		}

		$vat_id_data = array(
			'label' 					=> get_option( 'vat_options_label', __( 'EU VAT Identification Number (VATIN)', 'woocommerce-german-market' ) ),
			'display_option'			=> get_option( 'german_market_display_vat_number_field', 'eu_optional' ),
			'show' 						=> false,
			'required' 					=> false,
			'last_error' 				=> WGM_Session::get( 'eu_vatin_last_error' ),
			'default_error' 			=> __( 'The VATIN is not valid!', 'woocommerce-german-market' ),
			'last_validated_number' 	=> WGM_Session::get( 'eu_vatin_check_billing_vat' ),
			'is_success' 				=> WGM_Session::get( 'eu_vatin_is_success' ),
			'required_error_if_empty' 	=> __( 'Please enter a valid VAT Identification Number.', 'woocommerce-german-market' ),
			'validating' 				=> __( 'Validating ...', 'woocommerce-german-market' ),
			'default_value'				=> apply_filters( 'wcvat_woocommerce_billing_fields_vat_value', $user_vat_id ),
		);

		$customer_taxable_address = array();
		if ( function_exists( 'WC' ) ) {
			$customer = WC()->customer;
			if ( is_object( $customer ) && method_exists( $customer, 'get_taxable_address' ) ) {

				$customer_taxable_address = WC()->customer->get_taxable_address();

				$country = $customer_taxable_address[ 0 ];

				if ( '' !== $country ) {
					
					$eu_countries = WC()->countries->get_european_union_countries();
					$is_eu_country = in_array( $country, $eu_countries );
					$display_vat_field = apply_filters( 'wcvat_display_vat_field', get_option( 'german_market_display_vat_number_field', 'eu_optional' ) );

					$base_location = wc_get_base_location();
					$base_country = $base_location[ 'country' ];
					$is_base_country = $country === $base_country;
					$vat_id_data[ 'country' ] = $country;
					$vat_id_data[ 'base_country' ] = $base_country;
					$vat_id_data[ 'is_base_country' ] = $is_base_country;
					
					$vat_id_data[ 'is_eu_country' ] = $is_eu_country;

					if ( 'eu_optional' === $display_vat_field && $is_eu_country && ( ! $is_base_country ) ) {
						$vat_id_data[ 'show' ] = true;
					} else if ( 'eu_mandatory' === $display_vat_field && $is_eu_country && ( ! $is_base_country ) ) {
						$vat_id_data[ 'show' ] = true;
						$vat_id_data[ 'required' ] = true;
					} else if ( 'always_optional' === $display_vat_field ) {
						$vat_id_data[ 'show' ] = true;
					} else if ( 'always_mandatory' === $display_vat_field ) {
						$vat_id_data[ 'show' ] = true;
						$vat_id_data[ 'required' ] = true;
					}

				}
			}
		}

		if ( $is_local_pickup ) {
			//$vat_id_data[ 'show' ] = false;
		}

		$vat_id_data = apply_filters( 'wcvat_store_cart_api_data', $vat_id_data );
		
		$cart_data = array(

			'customer_taxable_address'       => $customer_taxable_address,
			'vat_id_data'                    => $vat_id_data,
			'purchase_on_account_visibility' => ( $internal_use ) ? false : $gateway_purchase_on_account->check_availability(),
			'sepa_visibility'                => ( $internal_use ) ? false : $gateway_sepa->check_availability(),
			'shipping_tax_strings'           => $shipping_tax_strings,
			'cart_shipping_total_string'     => html_entity_decode( strip_tags( $cart_shipping_total_string ) ),
			'cart_total_tax'                 => html_entity_decode( strip_tags( $cart_total_string ) ),
			'fees'	                         => $fees,

			'checkboxes' => array(
				
				'terms_privacy_revocation' => array(
					'id'						=> 'terms',
					'activation'				=> get_option( 'german_market_checkbox_1_tac_pd_rp_activation', 'on' ),
					'optin'						=> get_option( 'german_market_checkbox_1_tac_pd_rp_opt_in', 'on' ),
					'text' 						=> $internal_use ? '' : WGM_Template::get_terms_text(),
					'error_text'				=> $internal_use ? '' : WGM_Template::get_terms_error_text(),
				),

				'digital_checkbox' => array(
					'id'						=> 'widerruf-digital-acknowledgement',
					'activation'				=> $is_digital_checkbox_active,
					'optin'						=> get_option( 'german_market_checkbox_2_digital_content_opt_in', 'on' ),
					'text' 						=> get_option( 'woocommerce_de_checkbox_text_digital_content', $digital_text ),
					'error_text'				=> get_option( 'woocommerce_de_checkbox_error_text_digital_content', $digital_error_text ),
					'info_text'					=> get_option( 'woocommerce_de_checkbox_text_digital_content_notice', $digital_info ),
				),

				'age_rating' => array(
					'id'						=> 'age-rating',
					'activation'				=> $is_age_rating_checkbox_active,
					'optin'						=> get_option( 'german_market_checkbox_age_rating_opt_in', 'on' ),
					'text' 						=> $age_rating_text,
					'error_text'				=> $age_rating_error_text,
				),

				'shipping_service_provider' => array(
					'id'						=> 'shipping-service-provider',
					'activation'				=> $is_shipping_provider_active,
					'optin'						=> get_option( 'german_market_checkbox_3_shipping_service_provider_opt_in', 'on' ),
					'text' 						=> get_option( 'german_market_checkbox_3_shipping_service_provider_text', $default_shipping_provider_text ),
					'error_text'				=> get_option( 'german_market_checkbox_3_shipping_service_provider_error_text', $default_shipping_error_text ),
				),

				'custom_checkbox' => array(
					'id'						=> 'german-market-custom-checkbox',
					'activation'				=> get_option( 'german_market_checkbox_4_custom_activation', 'off' ),
					'optin'						=> get_option( 'german_market_checkbox_4_custom_opt_in', 'on' ),
					'text' 						=> WGM_Template::replace_placeholders_terms_privacy_revocation( get_option( 'german_market_checkbox_4_custom_text', '' ) ),
					'error_text'				=> get_option( 'german_market_checkbox_4_custom_error_text', '' ),
				),
			)
		);

		// product depending checkboxes
		$product_depending_checkbox_instance = WGM_Checkbox_Product_Depending::get_instance();
		$nr_of_checkboxes = $product_depending_checkbox_instance->get_nr_of_checkboxes();

		for ( $i = 1; $i <= $nr_of_checkboxes; $i++ ) {
			
			$checkbox_is_active = 'off';
			if ( ! $internal_use ) {
				if ( get_option( 'gm_checkbox_product_depending_activation_' .$i , 'off' ) == 'on' ) {
					if ( $product_depending_checkbox_instance->cart_or_order_needs_checkbox( $i ) ) {
						$checkbox_is_active = 'on';
					}
				}
			}

			$cart_data[ 'checkboxes' ][ 'product_depending_' . $i ] = array( 
				'id'						=> 'german-market-product-depending-checkbox-' . $i,
				'activation'				=> $checkbox_is_active,
				'optin'						=> get_option( 'gm_checkbox_product_depending_opt_in_' . $i, 'on' ),
				'text' 						=> WGM_Template::replace_placeholders_terms_privacy_revocation( get_option( 'gm_checkbox_product_depending_text_' . $i, '' ) ),
				'error_text'				=> get_option( 'gm_checkbox_product_depending_error_text_' . $i, '' ),
			);

		}

		return apply_filters( 'german_market_store_api_cart_data', $cart_data );
	}

	/**
	 * Register schema into cart endpoint.
	 *
	 * @return  array  Registered schema.
	 */
	public static function extend_cart_schema() {

		$schema = array(

			'customer_taxable_address' => array(
				'description' => __( 'Taxable address of customer', 'woocommerce-german-market' ),
				'type'        => 'array',
				'readonly'    => true,
			),

			'vat_id_data' => array(
				'description' => __( 'Data for VAT ID field check', 'woocommerce-german-market' ),
				'type'        => 'array',
				'readonly'    => true,
			),

			'purchase_on_account_visibility' => array(
				'description' => __( 'Is payment gateway "Purchase on Account" (by German Market) visible?', 'woocommerce-german-market' ),
				'type'        => 'bool',
				'readonly'    => true,
			),

			'sepa_visibility' => array(
				'description' => __( 'Is payment gateway "SEPA" (by German Market) visible?', 'woocommerce-german-market' ),
				'type'        => 'bool',
				'readonly'    => true,
			),

			'shipping_tax_strings' => array(
				'description' => __( 'Tax strings for shipping methods', 'woocommerce-german-market' ),
				'type'        => 'array',
				'readonly'    => true,
			),

			'cart_shipping_total_string' => array(
				'description' => __( 'Total tax string for Shipping', 'woocommerce-german-market' ),
				'type'        => 'string',
				'readonly'    => true,
			),

			'cart_total_tax' => array(
				'description' => __( 'Total tax string', 'woocommerce-german-market' ),
				'type'        => 'string',
				'readonly'    => true,
			),

			'fees' => array(
				'description' => __( 'Tax strings for fees', 'woocommerce-german-market' ),
				'type'        => 'array',
				'readonly'    => true,
			),

			'checkboxes' => array(
				'description' => __( 'checkbox texts and options', 'woocommerce-german-market' ),
				'type'        => 'array',
				'readonly'    => true,
			)
		);

		return $schema;
	}

	/**
	 * Validates the order payment gateway.
	 *
	 * @throws InvalidCartException
	 *
	 * @param  \WC_Order  $order  Order object.
	 */
	public static function checkout_order_processed( $order ) {

		// Bail out early.
		if ( ! $order->needs_payment() ) {
			return;
		}

		$chosen_gateway = $order->get_payment_method();
		if ( 'german_market_purchase_on_account' === $chosen_gateway ) {
			$gateway = new WGM_Gateway_Purchase_On_Account();
			$is_valid = $gateway->check_availability();

			// Return error if necessary.
			if ( ! $is_valid ) {

				$errors = new \WP_Error();
				$code   = self::IDENTIFIER . '-error';

				$errors->add( $code, __( 'Invalid payment option.', 'woocommerce-german-market' ) );

				throw new InvalidCartException(
					'woocommerce_woocommerce_german_market_payment_error',
					$errors,
					409
				);
			}
		}
	}
}
