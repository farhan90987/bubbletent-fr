<?php

namespace MarketPress\GermanMarket\Shipping\Provider\DHL;

use MarketPress\GermanMarket\Shipping\Helper;
use MarketPress\GermanMarket\Shipping\Navigation as Shipping_Navigation;
use SoapFault;
use WC_Admin_Settings;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Navigation extends Shipping_Navigation {

	/**
	 * Class constructor.
	 *
	 * @acces public
	 *
	 * @param string $provider_id
	 */
	public function __construct( string $provider_id ) {

		parent::__construct();

		$this->id = $provider_id;

		// Add API Status output element.
		add_action( 'woocommerce_admin_field_api_status',           array( $this, 'wgm_api_status' ) );
		add_action( 'woocommerce_admin_field_internetmarke_status', array( $this, 'wgm_internetmarke_status' ) );

		if ( WGM_SHIPPING_PRODUKT_DHL_WARENPOST === Shipping_Provider::$options->get_option( 'paket_default_product_national' ) ) {
			Shipping_Provider::$options->update_option( 'paket_default_product_national', WGM_SHIPPING_PRODUKT_DHL_KLEINPAKET );
		}
	}

	/**
	 * Update Google Map Option when saving options
	 *
	 * @wp-hook admin_init
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	public static function update_google_map_options( $options ) {

		if ( isset( $_POST[ 'submit_save_wgm_options' ] ) ) {
			if ( wp_verify_nonce( $_POST[ 'update_wgm_settings' ], 'woocommerce_de_update_wgm_settings' ) ) {
				if ( isset( $_POST[ Shipping_Provider::$options->build_option_key( 'google_map_enabled' ) ] ) ) {
					Shipping_Provider::$options->update_option( 'google_map_enabled', 'on' );
				}
			}
		}
	}

	/**
	 * Save Shop Address Information as WordPress Option.
	 *
	 * @wp-hook admin_init
	 *
	 * @param array $options
	 *
	 * @return void
	 */
	public static function update_warehouses_options( $options ) {

		if ( isset( $_POST[ 'submit_save_wgm_options' ] ) && isset( $_POST[ Shipping_Provider::$options->build_option_key( 'shipping_shop_address_name' ) ] ) ) {
			if ( wp_verify_nonce( $_POST[ 'update_wgm_settings' ], 'woocommerce_de_update_wgm_settings' ) ) {

				Shipping_Provider::$options->update_option( 'shipping_shop_address_company',  $_POST[ Shipping_Provider::$options->build_option_key( 'shipping_shop_address_company' ) ] );
				Shipping_Provider::$options->update_option( 'shipping_shop_address_name',     $_POST[ Shipping_Provider::$options->build_option_key( 'shipping_shop_address_name' ) ] );
				Shipping_Provider::$options->update_option( 'shipping_shop_address_street',   $_POST[ Shipping_Provider::$options->build_option_key( 'shipping_shop_address_street' ) ] );
				Shipping_Provider::$options->update_option( 'shipping_shop_address_house_no', $_POST[ Shipping_Provider::$options->build_option_key( 'shipping_shop_address_house_no' ) ] );
				Shipping_Provider::$options->update_option( 'shipping_shop_address_city',     $_POST[ Shipping_Provider::$options->build_option_key( 'shipping_shop_address_city' ) ] );
				Shipping_Provider::$options->update_option( 'shipping_shop_address_country',  $_POST[ Shipping_Provider::$options->build_option_key( 'shipping_shop_address_country' ) ] );
				Shipping_Provider::$options->update_option( 'shipping_shop_address_phone',    $_POST[ Shipping_Provider::$options->build_option_key( 'shipping_shop_address_phone' ) ] );
				Shipping_Provider::$options->update_option( 'shipping_shop_address_email',    $_POST[ Shipping_Provider::$options->build_option_key( 'shipping_shop_address_email' ) ] );
			}
		}
	}

	/**
	 * Integrate our Add-on into the German Market Menu
	 *
	 * @uses woocommerce_de_ui_left_menu_items
	 *
	 * @param array $add_ons
	 *
	 * @return array
	 */
	public function extend_german_market_navigation( array $add_ons ) : array {

		$add_ons[ 321 ] = array(
			'title'   => Shipping_Provider::get_instance()->addon_title,
			'slug'    => 'wgm-shipping-' . $this->id,
			'submenu' => array(

				array(
					'title'    => __( 'API & DHL Options', 'woocommerce-german-market' ),
					'slug'     => 'api-options',
					'callback' => array( $this, 'api_options' ),
					'options'  => 'yes',
				),
				array(
					'title'    => __( 'Shop Address', 'woocommerce-german-market' ),
					'slug'     => 'shop-address',
					'callback' => array( $this, 'shop_address' ),
					'options'  => 'yes',
				),
				array(
					'title'    => __( 'Return Settings', 'woocommerce-german-market' ),
					'slug'     => 'retoure-address',
					'callback' => array( $this, 'retoure_address' ),
					'options'  => 'yes',
				),
				array(
					'title'    => __( 'Bank Account', 'woocommerce-german-market' ),
					'slug'     => 'bank-account',
					'callback' => array( $this, 'bank_account' ),
					'options'  => 'yes',
				),
				array(
					'title'    => __( 'Parcels Configuration', 'woocommerce-german-market' ),
					'slug'     => 'parcels',
					'callback' => array( $this, 'parcels' ),
					'options'  => 'yes',
				),
				/*
				array(
					'title'    => __( 'Package Boxes', 'woocommerce-german-market' ),
					'slug'     => 'boxes',
					'callback' => array( $this, 'package_boxes' ),
					'options'  => 'yes',
				),
				*/
				array(
					'title'    => __( 'Parcel Tracking', 'woocommerce-german-market' ),
					'slug'     => 'parcels-tracking',
					'callback' => array( $this, 'parcels_tracking' ),
					'options'  => 'yes',
				),
				array(
					'title'    => __( 'Internetmarke', 'woocommerce-german-market' ),
					'slug'     => 'internetmarke',
					'callback' => array( $this, 'internetmarke' ),
					'options'  => 'yes',
				),
			),
		);

		return $add_ons;
	}

	/**
	 * API Options Submenu.
	 *
	 * @return array
	 */
	public function api_options() : array {

		$description = '';

		if ( false === Helper::check_shop_address_settings( Shipping_Provider::get_instance()->id ) ) {
			$description .= "<b style='color: red;'>" . __( 'Please fill in your Shop Senders Address. These options are mandatory for printing shipping labels. Otherwise the DHL shipping methods will be disabled in checkout process.', 'woocommerce-german-market' ) . "</b><br/><br/>";
		}

		if ( ! Shipping_Provider::is_base_country_supported() ) {
			$description .= "<b style='color: red;'>" . sprintf( __( 'Please note: your shop base country is not supported by %s shipping provider. %s is available in %s only.', 'woocommerce-german-market' ), strtoupper( Shipping_Provider::get_instance()->id ), strtoupper( Shipping_Provider::get_instance()->id ), __( 'Germany', 'woocommerce-german-market' ) ) . "</b><br/><br/>";
		}

		$description .= "<b>" . sprintf( __( 'By activating this German Market-Add-On, additional shipping methods for DHL are now available in the <a target="_blank" href="%s">WooCommerce settings</a>.', "woocommerce-german-market" ), admin_url( 'admin.php?page=wc-settings&tab=shipping' ) ) . "</b><br/><br/>";

		$settings[] = array(
			'title' => __( 'API Options', 'woocommerce-german-market' ),
			'type'  => 'title',
			'desc'  => $description . __( 'Please fill in your login information for using the DHL API (using Google Maps API is optional needed for DHL parcel shops/packstation).', 'woocommerce-german-market' ),
			'id'    => 'section_api',
		);

		$settings[] = array(
			'name'     => __( 'Sandbox', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Activate this option for testing. The requests then proceed through the DHL test server.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'test_mode' ),
			'type'     => 'wgm_ui_checkbox',
		);

		$settings[] = array(
			'name'     => __( 'DHL Username', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Please enter your DHL Username. (This is the same one you use to log in to DHL to create manual labels).', 'woocommerce-german-market' ),
			'desc'     => sprintf( __( 'For using the DHL API please provide us your login information for the %s.', 'woocommerce-german-market' ), '<a href="https://geschaeftskunden.dhl.de" target="_blank">' . __( 'DHL Business Portal', 'b2b-market' ) . '</a>' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'global_username' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'custom_attributes' => array(
				'autocomplete' => 'off',
			),
		);

		$settings[] = array(
			'name'     => __( 'DHL Password', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Please enter your DHL password.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'global_signature' ),
			'type'     => 'password',
			'css'      => 'width: 400px;',
			'custom_attributes' => array(
				'autocomplete' => 'off',
			),
		);

		$settings[] = array(
			'name'     => __( 'DHL EKP', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Your DHL Account Number (please provide the first 10 digits)', 'woocommerce-german-market' ),
			'desc'     => __( 'Please fill in the first 10 chars / digits of your DHL account number.<br/>If you dont have a DHL account, you can register and create one on <a href="https://www.dhl.de/kundewerden" target="_blank">this page</a>.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'global_ekp' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
		);

		$settings[] = array(
			'name'     => __( 'API Connection-Status', 'woocommerce-german-market' ),
			'desc_tip' => __( 'This indicator tells you, if you are successfully connected and authorized to the DHL REST API. If your credentials failed it shows "Not Authorized".', 'woocommerce-german-market' ),
			'type'     => 'api_status',
		);

		$settings[] = array(
			'name'     => __( 'Google Maps API', 'woocommerce-german-market' ),
			'desc'     => __( 'Activate this option to allow your customers to select the DHL packstation or parcel shop via Google Maps at checkout.</br>Please note that you may need to expand your privacy policy. If you do not have a Google Maps API key yet, you must register at the <a href="https://console.developers.google.com/?hl=de&pli=1" target="_blank">Google API Console</a>.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'google_map_enabled' ),
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'no',
		);

		$settings[] = array(
			'name'     => __( 'Google Maps API Key', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Google Maps is used to display a map of packstations/parcel shops on the checkout page based from the entered address.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'google_map_key' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
		);

		$settings[] = array(
			'name'     => __( 'Limit API Results', 'woocommerce-german-market' ),
			'desc_tip' => __( 'This setting can be used to limit/increase the results of the parcel shops/packstations displayed. A limit is useful to reduce the number of API requests.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'api_results_limit' ),
			'type'     => 'number',
			'css'      => 'width: 400px;',
			'default'  => 15,
			'custom_attributes' => array(
				'min'      => 5,
				'max'      => 99,
			),
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'section_api',
		);

		$settings[] = array(
			'title' => __( 'DHL Accounting Numbers', 'woocommerce-german-market' ),
			'type'  => 'title',
			'desc'  => __( 'For each DHL product that you would like to use, please enter your participation number here. The participation number consists of the last two characters of the respective accounting number, which you will find in your DHL contract data (for example, 01).', 'woocommerce-german-market' ),
			'id'    => 'section_accounting_numbers',
		);

		$settings[] = array(
			'name'    => __( 'DHL Parcel', 'woocommerce-german-market' ),
			'id'      => Shipping_Provider::$options->build_option_key( 'paket_dhl_participation_V01PAK' ),
			'type'    => 'text',
			'default' => '',
			'css'     => 'width: 400px;',
		);

		$settings[] = array(
			'name'    => __( 'DHL Kleinpaket', 'woocommerce-german-market' ),
			'id'      => Shipping_Provider::$options->build_option_key( 'paket_dhl_participation_V62WP' ),
			'type'    => 'text',
			'default' => '',
			'css'     => 'width: 400px;',
		);

		$settings[] = array(
			'name'    => __( 'DHL Euro Parcel (B2B)', 'woocommerce-german-market' ),
			'id'      => Shipping_Provider::$options->build_option_key( 'paket_dhl_participation_V54EPAK' ),
			'type'    => 'text',
			'default' => '',
			'css'     => 'width: 400px;',
		);

		$settings[] = array(
			'name'    => __( 'DHL Parcel International', 'woocommerce-german-market' ),
			'id'      => Shipping_Provider::$options->build_option_key( 'paket_dhl_participation_V53WPAK' ),
			'type'    => 'text',
			'default' => '',
			'css'     => 'width: 400px;',
		);

		$settings[] = array(
			'name'    => __( 'DHL Post International', 'woocommerce-german-market' ),
			'id'      => Shipping_Provider::$options->build_option_key( 'paket_dhl_participation_V66WPI' ),
			'type'    => 'text',
			'default' => '',
			'css'     => 'width: 400px;',
		);

		$settings[] = array(
			'name'    => __( 'DHL Returns', 'woocommerce-german-market' ),
			'id'      => Shipping_Provider::$options->build_option_key( 'paket_dhl_participation_return' ),
			'type'    => 'text',
			'default' => '',
			'css'     => 'width: 400px;',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'section_accounting_numbers',
		);

		return $settings;
	}

	/**
	 * Shop Address Submenu
	 * We need to acquire more and split senders information for shipping labels
	 *
	 * @return array
	 */
	public function shop_address() : array {

		// The country/state
		$store_raw_country = get_option( 'woocommerce_default_country' );

		// Split the country/state
		$split_country = explode( ':', $store_raw_country );

		// Country separated or set 'Germany' as default:
		$store_country = ( '' !== $split_country[ 0 ] ? $split_country[ 0 ] : 'DE' );

		$description = '';
		$required    = ' *';

		if ( false === Helper::check_shop_address_settings( Shipping_Provider::get_instance()->id ) ) {
			$description .= "<b style='color: red;'>" . __( 'Please fill in your Shop Address or a Sender Reference. These options are mandatory for printing shipping labels. Otherwise the DHL shipping methods will be disabled in checkout process.', 'woocommerce-german-market' ) . "</b><br/><br/>";
		}

		$settings[] = array(
			'title' => __( 'Shop Address', 'woocommerce-german-market' ),
			'type'  => 'title',
			'desc'  => $description . __( 'This informations are required for the generation of shipping labels. (* Fields are required except if the sender reference has been specified, in this case the data will be used from your DHL backend).', 'woocommerce-german-market' ),
			'id'    => 'section_shop_address',
		);

		$settings[] = array(
			'name'     => __( 'Sender Reference', 'woocommerce-german-market' ),
			'desc_tip' => __( 'If you have configured the sender data in the GKP (business customer portal), the sender address from the GKP can be used instead of a manually entered sender address if you add your sender reference here. The sender reference can also be used to add a company logo that is stored in the GKP to the shipping label.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_gkp_shipper_reference' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'default'  => '',
		);

		$settings[] = array(
			'name'     => __( 'Company Name', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Name of your company', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_address_company' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'default'  => '',
		);

		$settings[] = array(
			'name'     => __( 'First/Last Name', 'woocommerce-german-market' ) . $required,
			'desc_tip' => __( 'Please enter your first and last name.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_address_name' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
		);

		$settings[] = array(
			'name'     => __( 'Street', 'woocommerce-german-market' ) . $required,
			'desc_tip' => __( 'Please enter the street name (without house number).', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_address_street' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'default'  => Helper::get_street_name( get_option( 'woocommerce_store_address', '' ) ),
		);

		$settings[] = array(
			'name'     => __( 'House Number', 'woocommerce-german-market' ) . $required,
			'desc_tip' => __( 'Please enter the house number.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_address_house_no' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'default'  => Helper::get_house_no( get_option( 'woocommerce_store_address', '' ) ),
		);

		$settings[] = array(
			'name'     => __( 'Zip Code', 'woocommerce-german-market' ) . $required,
			'desc_tip' => __( 'Please enter your zip code.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_address_zip_code' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'default'  => get_option( 'woocommerce_store_postcode', '' ),
		);

		$settings[] = array(
			'name'     => __( 'City', 'woocommerce-german-market' ) . $required,
			'desc_tip' => __( 'Please enter your city.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_address_city' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'default'  => get_option( 'woocommerce_store_city', '' ),
		);

		$settings[] = array(
			'name'    => __( 'Country', 'woocommerce-german-market' ) . $required,
			'id'      => Shipping_Provider::$options->build_option_key( 'shipping_shop_address_country' ),
			'type'    => 'select',
			'options' => WC()->countries->get_countries(),
			'default' => $store_country,
			'class'   => 'wc-enhanced-select',
			'css'     => 'width: 400px;',
		);

		$settings[] = array(
			'name'     => __( 'Phone Number', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Please enter your phone number (without country code).', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_address_phone' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
		);

		$settings[] = array(
			'name'     => __( 'Email Address', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Please enter your valid email address.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_address_email' ),
			'type'     => 'text',
			'default'  => get_option( 'admin_email', '' ),
			'css'      => 'width: 400px;',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'section_shop_address',
		);

		return $settings;
	}

	/**
	 * Retoure Address Submenu
	 * We need to acquire more and split senders information for shipping labels
	 *
	 * @return array
	 */
	public function retoure_address() : array {

		// The country/state
		$store_raw_country = get_option( 'woocommerce_default_country' );

		// Split the country/state
		$split_country = explode( ':', $store_raw_country );

		// Country separated or set 'Germany' as default:
		$store_country = ( '' !== $split_country[ 0 ] ? $split_country[ 0 ] : 'DE' );

		$required = ' *';

		$settings[] = array(
			'title' => __( 'Return Labels', 'woocommerce-german-market' ),
			'type'  => 'title',
			'id'    => 'section_retoure_labels',
		);

		$settings[] = array(
			'name'     => __( 'Print Return Labels', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Activate this option if you want to create return labels.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'label_retoure_enabled' ),
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'off',
		);

		$settings[] = array(
			'name'     => __( 'Download in "My Account" section', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Activate this option if you want customers to be able to download return labels in the "My account" section.', 'woocommerce-german-market' ),
			'desc'     => __( 'If this setting is activated and an order reaches the status "completed", the customer can download the return label in their "My account" area.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'retoure_label_download' ),
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'no'
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'section_retoure_labels',
		);

		$description = '';

		if ( false === Helper::check_shop_address_settings( Shipping_Provider::get_instance()->id ) ) {
			$description .= "<b style='color: red;'>" . __( 'Please fill in your return address. These options are mandatory for printing return labels.', 'woocommerce-german-market' ) . "</b><br/><br/>";
		}

		$settings[] = array(
			'title' => __( 'Return Address', 'woocommerce-german-market' ),
			'type'  => 'title',
			'desc'  => $description . __( 'This informations are required if return labels are to be created.', 'woocommerce-german-market' ),
			'id'    => 'section_retoure_address',
		);

		$settings[] = array(
			'name'     => __( 'Company Name', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Name of your company', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_retour_address_company' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'default'  => '',
		);

		$settings[] = array(
			'name'     => __( 'First/Last Name', 'woocommerce-german-market' ) . $required,
			'desc_tip' => __( 'Please enter your first and last name.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_retour_address_name' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
		);

		$settings[] = array(
			'name'     => __( 'Street', 'woocommerce-german-market' ) . $required,
			'desc_tip' => __( 'Please enter the street name (without house number).', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_retour_address_street' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'default'  => Helper::get_street_name( get_option( 'woocommerce_store_address', '' ) ),
		);

		$settings[] = array(
			'name'     => __( 'House Number', 'woocommerce-german-market' ) . $required,
			'desc_tip' => __( 'Please enter the house number.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_retour_address_house_no' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'default'  => Helper::get_house_no( get_option( 'woocommerce_store_address', '' ) ),
		);

		$settings[] = array(
			'name'     => __( 'Zip Code', 'woocommerce-german-market' ) . $required,
			'desc_tip' => __( 'Please enter your zip code.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_retour_address_zip_code' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'default'  => get_option( 'woocommerce_store_postcode', '' ),
		);

		$settings[] = array(
			'name'     => __( 'City', 'woocommerce-german-market' ) . $required,
			'desc_tip' => __( 'Please enter your city.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_retour_address_city' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'default'  => get_option( 'woocommerce_store_city', '' ),
		);

		$settings[] = array(
			'name'    => __( 'Country', 'woocommerce-german-market' ) . $required,
			'id'      => Shipping_Provider::$options->build_option_key( 'shipping_shop_retour_address_country' ),
			'type'    => 'select',
			'options' => WC()->countries->get_countries(),
			'default' => $store_country,
			'class'   => 'wc-enhanced-select',
			'css'     => 'width: 400px;',
		);

		$settings[] = array(
			'name'     => __( 'Phone Number', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Please enter your phone number (without country code).', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_retour_address_phone' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
		);

		$settings[] = array(
			'name'     => __( 'Email Address', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Please enter your valid email address.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_retour_address_email' ),
			'type'     => 'text',
			'default'  => get_option( 'admin_email', '' ),
			'css'      => 'width: 400px;',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'section_retoure_address',
		);

		return $settings;
	}

	/**
	 * Bank Account Submenu
	 *
	 * @return array
	 */
	public function bank_account() : array {

		$settings[] = array(
			'title' => __( 'Bank Account', 'woocommerce-german-market' ),
			'type'  => 'title',
			'desc'  => __( 'If the payment method "cash on delivery" (COD) has been selected, the following information will be required.', 'woocommerce-german-market' ),
			'id'    => 'section_bank_account',
		);

		$settings[] = array(
			'name'     => __( 'Account Holder', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_cod_bank_account_holder' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'default'  => get_option( 'wgm_shipping_shop_cod_bank_account_holder', '' ),
		);

		$settings[] = array(
			'name'     => __( 'Account Number', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_cod_bank_account_number' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'default'  => get_option( 'wgm_shipping_shop_cod_bank_account_number', '' ),
		);

		$settings[] = array(
			'name'     => __( 'Bank Name', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_cod_bank_name' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'default'  => get_option( 'wgm_shipping_shop_cod_bank_name', '' ),
		);

		$settings[] = array(
			'name'     => __( 'IBAN', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_cod_iban' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'default'  => get_option( 'wgm_shipping_shop_cod_iban', '' ),
		);

		$settings[] = array(
			'name'     => __( 'BIC', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_cod_bic' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'default'  => get_option( 'wgm_shipping_shop_cod_bic', '' ),
		);

		$settings[] = array(
			'name'     => sprintf( __( 'Reference (Line %d)', 'woocommerce-german-market' ), 1 ),
			'desc'     => sprintf( __( 'You can use the following placeholder: %s.', 'woocommerce-german-market' ), '<code>{order_id}</code>, <code>{invoice_number}</code>, <code>{email}</code>' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_cod_reference' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'default'  => __( 'Order', 'woocommerce-german-market' ) . ' ' . '{order_id}',
		);

		$settings[] = array(
			'name'     => sprintf( __( 'Reference (Line %d)', 'woocommerce-german-market' ), 2 ),
			'desc'     => sprintf( __( 'You can use the following placeholder: %s.', 'woocommerce-german-market' ), '<code>{order_id}</code>, <code>{invoice_number}</code>, <code>{email}</code>' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'shipping_shop_cod_reference_2' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'default'  => '{email}',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'section_bank_account',
		);

		return $settings;
	}

	/**
	 * Parcel Configuration Submenu
	 *
	 * @return array
	 */
	public function parcels() : array {

		$settings[] = array(
			'title' => __( 'DHL Standard Parcel Services', 'woocommerce-german-market' ),
			'type'  => 'title',
			'id'    => 'section_parcels',
		);

		$options = array(
			WGM_SHIPPING_PRODUKT_DHL_PAKET      => __( 'DHL Parcel', 'woocommerce-german-market' ),
			WGM_SHIPPING_PRODUKT_DHL_KLEINPAKET => __( 'DHL Kleinpaket', 'woocommerce-german-market' ),
		);

		$settings[] = array(
			'name'    => __( 'Default National Service', 'woocommerce-german-market' ),
			'id'      => Shipping_Provider::$options->build_option_key( 'paket_default_product_national' ),
			'type'    => 'select',
			'options' => $options,
			'default' => WGM_SHIPPING_PRODUKT_DHL_PAKET,
			'class'   => 'wc-enhanced-select',
			'css'     => 'width: 400px;',
		);

		$settings[] = array(
			'name'    => __( 'Default International Service', 'woocommerce-german-market' ),
			'id'      => Shipping_Provider::$options->build_option_key( 'paket_default_product_international' ),
			'type'    => 'select',
			'options' => array(
				WGM_SHIPPING_PRODUKT_DHL_EURO_PAKET_B2B          => __( 'DHL Euro Parcel (B2B)', 'woocommerce-german-market' ),
				WGM_SHIPPING_PRODUKT_DHL_PAKET_INTERNATIONAL     => __( 'DHL Parcel International', 'woocommerce-german-market' ),
				WGM_SHIPPING_PRODUKT_DHL_WARENPOST_INTERNATIONAL => __( 'DHL Post International', 'woocommerce-german-market' ),
			),
			'default' => WGM_SHIPPING_PRODUKT_DHL_PAKET_INTERNATIONAL,
			'class'   => 'wc-enhanced-select',
			'css'     => 'width: 400px;',
		);

		$settings[] = array(
			'name'     => __( 'Shipping conditions', 'woocommerce-german-market' ),
			'desc_tip' => sprintf( __( 'This value is mandatory for international shipments.', 'woocommerce-german-market' ), __( 'DHL Euro Parcel (B2B)', 'woocommerce-german-market' ) ),
			'id'       => Shipping_Provider::$options->build_option_key( 'paket_default_terms_of_trade' ),
			'type'     => 'select',
			'options'  => array(
				WGM_SHIPPING_DELIVERY_DUTY_UNPAID        => __( 'Delivery Duty Unpaid', 'woocommerce-german-market' ),
				WGM_SHIPPING_DELIVERY_DUTY_PAID          => __( 'Delivery Duty Paid', 'woocommerce-german-market' ),
				WGM_SHIPPING_DELIVERY_DUTY_PAID_EXCL_VAT => sprintf( __( 'Delivered Duty Paid (%s)', 'woocommerce-german-market' ), __( 'excl. VAT', 'woocommerce-german-market' ) ),
				WGM_SHIPPING_DELIVERY_DUTY_PAID_EXCL_ALL => sprintf( __( 'Delivery Duty Paid (%s)', 'woocommerce-german-market' ), __( 'excl. customs duties, taxes and VAT', 'woocommerce-german-market' ) ),
			),
			'default'  => WGM_SHIPPING_DELIVERY_DUTY_PAID_EXCL_VAT,
			'class'    => 'wc-enhanced-select',
			'css'      => 'width: 400px;',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'section_parcels',
		);

		$settings[] = array(
			'title' => __( 'Parcels & Labels Configuration', 'woocommerce-german-market' ),
			'type'  => 'title',
			'id'    => 'section_labels',
		);

		$settings[] = array(
			'title'    => __( 'Default parcel weight in kg', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Define a standard weight for your parcel in case the total weight cannot be calculated automatically, e.g. if no weight has been defined for the product.', 'woocommerce-german-market' ),
			'type'     => 'number',
			'id'       => Shipping_Provider::$options->build_option_key( 'default_parcel_weight' ),
			'default'  => 2,
			'custom_attributes' => array(
				'min'  => 0,
				'step' => 0.05,
			),
		);

		$settings[] = array(
			'title'    => __( 'Minimum parcel weight in kg', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Define a minimum weight of your parcel. If the calculated weight of your items is smaller than this value, the minimum parcel weight will be used.', 'woocommerce-german-market' ),
			'type'     => 'number',
			'id'       => Shipping_Provider::$options->build_option_key( 'minimum_parcel_weight' ),
			'default'  => 0.5,
			'custom_attributes' => array(
				'min'  => 0,
				'step' => 0.05,
			),
		);

		$settings[] = array(
			'title'    => __( 'Additional parcel weight in kg or %', 'woocommerce-german-market' ),
			'desc_tip' => __( 'This weight is added to each parcel weight. This is useful for packages, extras such as flyers or free inserts. You can specify the weight in kilograms (e.g. "10") or use a percentage value (e.g. "10%").', 'woocommerce-german-market' ),
			'type'     => 'text',
			'id'       => Shipping_Provider::$options->build_option_key( 'default_additional_parcel_weight' ),
			'default'  => 0,
		);

		$settings[] = array(
			'name'    => __( 'Default Label Format', 'woocommerce-german-market' ),
			'id'      => Shipping_Provider::$options->build_option_key( 'label_size' ),
			'type'    => 'select',
			'options' => array(
				'A4'             => __( 'A4', 'woocommerce-german-market' ),
				'910-300-700'    => sprintf( __( 'Laser printer %d x %d mm', 'woocommerce-german-market' ), 105, 205 ),
				'910-300-700-oZ' => sprintf( __( 'Laser printer %d x %d mm', 'woocommerce-german-market' ), 105, 205 ) . ' ' . __( '(no info)', 'woocommerce-german-market' ),
				'910-300-600'    => sprintf( __( 'Thermo printer %d x %d mm', 'woocommerce-german-market' ), 103, 199 ),
				'910-300-610'    => sprintf( __( 'Thermo printer %d x %d mm', 'woocommerce-german-market' ), 103, 202 ),
				'910-300-710'    => sprintf( __( 'Laser printer %d x %d mm', 'woocommerce-german-market' ), 105, 208 ),
				'910-300-410'    => sprintf( __( 'Laser printer %d x %d mm', 'woocommerce-german-market' ), 103, 150 ),
				'910-300-300'    => sprintf( __( 'Laser printer %d x %d mm', 'woocommerce-german-market' ), 105, 148 ),
				'910-300-300-oZ' => sprintf( __( 'Laser printer %d x %d mm', 'woocommerce-german-market' ), 105, 148 ) . ' ' . __( '(without additional labels)', 'woocommerce-german-market' ),
				'100x70mm'       => __( '100 x 70 mm (only for Warenpost)', 'woocommerce-german-market' ),
			),
			'default' => '910-300-700',
			'class'   => 'wc-enhanced-select',
			'css'     => 'width: 400px;',
		);

		$settings[] = array(
			'name'    => __( 'Parcel Distribution', 'woocommerce-german-market' ),
			'id'      => Shipping_Provider::$options->build_option_key( 'parcel_distribution' ),
			'type'    => 'select',
			'options' => array(
				'1' => __( 'Group all products in one delivery', 'woocommerce-german-market' ),
				'2' => __( 'Group same products in one delivery', 'woocommerce-german-market' ),
				'3' => __( 'For each individual product a separate delivery', 'woocommerce-german-market' ),
			),
			'default' => '1',
			'class'   => 'wc-enhanced-select',
			'css'     => 'width: 400px;',
		);

		$settings[] = array(
			'name'     => __( 'Generate label automatically', 'woocommerce-german-market' ),
			'desc'     => __( 'Activate this option if you want the label to be created automatically when the order status changes to the status set in the following option.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'label_auto_creation' ),
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'no',
		);

		$settings[] = array(
			'name'    => __( 'Select order status for automatic generation', 'woocommerce-german-market' ),
			'id'      => Shipping_Provider::$options->build_option_key( 'label_auto_creation_status' ),
			'type'    => 'multiselect',
			'options' => Helper::filter_get_order_statuses( wc_get_order_statuses() ),
			'default' => 'wc-processing',
			'class'   => 'wc-enhanced-select',
			'css'     => 'width: 400px;',
		);

		$settings[] = array(
			'name'     => __( 'Send shipping label to separate email', 'woocommerce-german-market' ),
			'desc'     => __( 'Activate this option if you want that the created label will be sent to a separate email address.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'label_email_enabled' ),
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'no'
		);

		$settings[] = array(
			'name'     => __( 'Email Address', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'label_email_address' ),
			'type'     => 'email',
			'css'      => 'width: 400px;',
			'default'  => get_option( 'admin_email', '' ),
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'section_labels',
		);

		$settings[] = array(
			'title' => __( 'Preferred Day Configuration', 'woocommerce-german-market' ),
			'type'  => 'title',
			'desc'  => __( 'This option allows customers to specify a preferred delivery day at the checkout. This option can only be used for national shipping.', 'woocommerce-german-market' ),
			'id'    => 'section_preferred_day',
		);

		$settings[] = array(
			'name'     => __( 'Activate', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'service_preferred_day_enabled' ),
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'off',
		);

		$settings[] = array(
			'name'              => __( 'Fee', 'woocommerce-german-market' ),
			'desc_tip'          => __( 'Add a gross price as a charge for delivery on the day of delivery. Set the value to 0 to offer the service for free.', 'woocommerce-german-market' ),
			'id'                => Shipping_Provider::$options->build_option_key( 'service_preferred_day_fee' ),
			'type'              => 'number',
			'default'           => '1.2',
			'custom_attributes' => array(
				'min'  => 0,
				'step' => 0.05,
			)
		);

		$settings[] = array(
			'name'     => __( 'Cut-off-Time', 'woocommerce-german-market' ),
			'desc_tip' => __( 'The cut-off-time is the latest possible order time up to which the earliest delivery day (day of order + 2 working days) can be guaranteed. After exceeding this time, the earliest available delivery day in the checkout will be increased by one day (day of the order + 3 working days).', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'service_preferred_day_cutoff' ),
			'type'     => 'time',
			'default'  => '12:00',
		);

		$settings[] = array(
			'name'              => __( 'Additional Processing Days', 'woocommerce-german-market' ),
			'desc_tip'          => __( 'If more time is needed to process shipments, a static number of days can be defined here, which is added to the earliest possible delivery day.', 'woocommerce-german-market' ),
			'id'                => Shipping_Provider::$options->build_option_key( 'service_preferred_day_processing_days' ),
			'type'              => 'number',
			'default'           => 0,
			'custom_attributes' => array(
				'min'  => 0,
			)
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'section_preferred_day',
		);

		$settings[] = array(
			'title' => __( 'Default Services Configuration', 'woocommerce-german-market' ),
			'type'  => 'title',
			'id'    => 'section_services',
		);

		$settings[] = array(
			'name'     => __( 'Visual Age Check', 'woocommerce-german-market' ),
			'desc_tip' => __( 'With this option, the "Visual age check" can be activated and the relevant age can be selected. DHL will check the age on delivery. To verify the age during the ordering process, the "Age Rating" option must be activated in the "Products" tab in German Market and configured on the product.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'service_age_visual_default' ),
			'type'     => 'select',
			'options'  => array(
				'0'   => __( 'None', 'woocommerce-german-market' ),
				'A16' => __( 'Minimum Age 16 Years', 'woocommerce-german-market' ),
				'A18' => __( 'Minimum Age 18 Years', 'woocommerce-german-market' ),
			),
			'default'  => '0',
			'class'    => 'wc-enhanced-select',
			'css'      => 'width: 400px;',
		);

		$settings[] = array(
			'name'     => __( 'Ident Check', 'woocommerce-german-market' ),
			'desc_tip' => __( 'With this option, the "Ident-Check" can be activated and the relevant age can be selected.  To verify the age during the ordering process, the "Age Rating" option must be activated in the "Products" tab in German Market and configured on the product.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'service_ident_check_default' ),
			'type'     => 'select',
			'options'  => array(
				'0'   => __( 'None', 'woocommerce-german-market' ),
				'ALL' => __( 'Complete Ident Check', 'woocommerce-german-market' ),
				'A16' => __( 'Minimum Age 16 Years', 'woocommerce-german-market' ),
				'A18' => __( 'Minimum Age 18 Years', 'woocommerce-german-market' ),
			),
			'default'  => '0',
			'class'    => 'wc-enhanced-select',
			'css'      => 'width: 400px;',
		);

		$settings[] = array(
			'name'     => __( 'Endorsement Type (only for international services)', 'woocommerce-german-market' ),
			'desc_tip' => __( 'This option can be used to select how DHL should handle international shipments that cannot be delivered.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'service_endorsement_default' ),
			'type'     => 'select',
			'options'  => array(
				'0'           => __( 'Please select', 'woocommerce-german-market' ),
				'IMMEDIATE'   => __( 'Sending back to sender', 'woocommerce-german-market' ),
				'ABANDONMENT' => __( 'Abandonment of parcel', 'woocommerce-german-market' ),
			),
			'default'  => '0',
			'class'    => 'wc-enhanced-select',
			'css'      => 'width: 400px;',
		);

		$settings[] = array(
			'name'     => __( 'GoGreen Plus', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Check in your DHL account whether you can request the GoGreen Plus service for every domestic shipment, if not, deactivate the option.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'service_gogreenplus_default' ),
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'off',
		);

		$settings[] = array(
			'name'     => __( 'Additional Insurance', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Add transport insurance to the shipment.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'service_transport_insurance_default' ),
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'off',
		);

		$settings[] = array(
			'name'     => __( 'No Neighbor Delivery', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Exclude delivery to a neighbor.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'service_no_neighbor_delivery_default' ),
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'off',
		);

		$settings[] = array(
			'name'     => __( 'Named Person Only', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Parcels should only be delivered to the recipient in person or to an authorized person.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'service_named_person_default' ),
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'off',
		);

		$settings[] = array(
			'name'     => __( 'Premium (only for international services)', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Premium delivery for international shipments.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'service_premium_default' ),
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'off',
		);

		$settings[] = array(
			'name'     => __( 'Bulky Goods', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Send parcels as bulky goods.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'service_bulky_goods_default' ),
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'off',
		);

		$settings[] = array(
			'name'     => __( 'Only Codeable Addresses', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Labels are only generated if the address has been successfully verified by DHL.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'service_codeable_default' ),
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'off',
		);

		$settings[] = array(
			'name'     => __( 'Retail Outlet Routing', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Undeliverable parcels should be sent to the nearest post office for pick-up instead of being returned directly.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'service_outlet_routing_default' ),
			'type'     => 'wgm_ui_checkbox',
			'default'  => 'off',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'section_services',
		);

		return $settings;
	}

	/**
	 * Package Boxes Configuration Submenu
	 *
	 * @return array
	 */
	public function package_boxes() : array {

		$settings[] = array(
			'title' => __( 'Package Boxes', 'woocommerce-german-market' ),
			'type'  => 'title',
			'desc'  => __( 'Please define your available package boxes formats whose you are using on this setting page. These are important to calculate needed package boxes for delivery.<br/>If you leave these package boxes empty, we will use a native calculation for the needed amount of parcels.', 'woocommerce-german-market' ),
			'id'    => 'section_boxes',
		);

		$settings[] = array(
			'name' => __( 'Package Boxes', 'woocommerce-german-market' ),
			'id'   => $this->id . '_package_boxes',
			'type' => 'boxes_repeatable',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'section_boxes',
		);

		return $settings;
	}

	/**
	 * Parcel Tracking Configuration Submenu
	 *
	 * @return array
	 */
	public function parcels_tracking() : array {

		$settings[] = array(
			'title' => __( 'Configuration', 'woocommerce-german-market' ),
			'type'  => 'title',
			'desc'  => __( 'The tracking information for customer emails can be configured in the following settings.', 'woocommerce-german-market' ),
			'id'    => 'section_tracking',
		);

		$settings[] = array(
			'name'     => __( 'Show tracking information in email', 'woocommerce-german-market' ),
			'desc_tip' => __( 'Activate this option to include tracking information into your customer emails.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'parcel_tracking_enabled' ),
			'type'     => 'wgm_ui_checkbox',
		);

		$settings[] = array(
			'name'    => __( 'Position of information', 'woocommerce-german-market' ),
			'id'      => Shipping_Provider::$options->build_option_key( 'parcel_tracking_position' ),
			'type'    => 'select',
			'options' => array(
				'top'    => __( 'Above "Order Details"', 'woocommerce-german-market' ),
				'bottom' => __( 'After "Order Details"', 'woocommerce-german-market' ),
			),
			'default' => 'bottom',
			'class'   => 'wc-enhanced-select',
			'css'     => 'width: 400px;',
		);

		$settings[] = array(
			'name'     => __( 'Text for your email template', 'woocommerce-german-market' ),
			'desc' => __( 'Please use {dhl_tracking_link} to place the tracking link in your text.', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'parcel_tracking_email_text' ),
			'type'     => 'text',
			'default'  => __( 'Tracking link: {dhl_tracking_link}', 'woocommerce-german-market' ),
			'css'      => 'width: 400px;',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'section_tracking',
		);

		return $settings;
	}

	/**
	 * Internetmarke Configuration Submenu
	 *
	 * @return array
	 * @throws SoapFault
	 */
	public function internetmarke() : array {

		$page_formats_result  = Shipping_Provider::$internetmarke->internetmarke_page_formats;
		$page_formats         = array();

		foreach ( $page_formats_result as $format ) {
			$page_formats[ $format[ 'id' ] ] = $format[ 'name' ];
		}

		asort( $page_formats ); // sort in alphabetically order without losing index value

		// Check for too many rejected API login request.

		$locked = '';
		if ( 'locked' === get_transient( 'wgm_shipping_dhl_internetmarke_login_locked' ) ) {
			$locked = __( '<p style="color:red"><b>Please note:</b> We have detected too many incorrect login attempts to the Deutsche Post API (Internetmarke). For security reasons, it is temporarily not possible to connect to the API. Please try again later and remove the details for "Username" and "Password" below. A new login can take between one hour and 24 hours.</p>', 'woocommerce-german-market' );
		}

		// Check if SOAP is activated.

		$soap = '';
		if ( ! extension_loaded('soap' ) || ! class_exists('SoapClient' ) ) {
			$soap = __( '<p style="color:red"><b>Please note:</b> The Internetmarke service is not / only limited available because SOAP is not activated. Please activate SOAP on your server to be able to use the Internetmarke.</p>', 'woocommerce-german-market' );
		}

		$settings[] = array(
			'title' => __( 'Deutsche Post Internetmarke', 'woocommerce-german-market' ),
			'type'  => 'title',
			'desc'  => __( 'The settings for the "Deutsche Post Internetmarke" product can be configured in the following settings.', 'woocommerce-german-market' ) . $soap . $locked,
			'id'    => 'section_internetmarke',
		);

		$settings[] = array(
			'name'     => __( 'Username', 'woocommerce-german-market' ),
			'desc'     => sprintf( __( 'For using the Deutsche Post INTERNETMARKE API please provide your login information for the <a href="%s" target="_blank">Deutsche Post Portokasse</a> portal. If you aren\'t registered yet, you can signup on <a href="%s" target="_blank">this page</a>.', 'woocommerce-german-market' ), Internetmarke::PORTOKASSE_URL, Internetmarke::SIGNUP_URL ),
			'id'       => Shipping_Provider::$options->build_option_key( 'internetmarke_portokasse_email' ),
			'type'     => 'text',
			'css'      => 'width: 400px;',
			'custom_attributes' => array(
				'autocomplete' => 'off',
			),
		);

		$settings[] = array(
			'name'     => __( 'Password', 'woocommerce-german-market' ),
			'id'       => Shipping_Provider::$options->build_option_key( 'internetmarke_portokasse_password' ),
			'type'     => 'password',
			'css'      => 'width: 400px;',
			'custom_attributes' => array(
				'autocomplete' => 'off',
			),
		);

		$settings[] = array(
			'name'     => __( 'API Connection-Status', 'woocommerce-german-market' ),
			'type'     => 'internetmarke_status',
		);

		$settings[] = array(
			'name'    => __( 'Page Format', 'woocommerce-german-market' ),
			'id'      => Shipping_Provider::$options->build_option_key( 'internetmarke_page_format' ),
			'type'    => 'select',
			'options' => $page_formats,
			'default' => '',
			'class'   => 'wc-enhanced-select',
			'css'     => 'width: 400px;',
		);

		$settings[] = array(
			'name'    => __( 'Preferred output format', 'woocommerce-german-market' ),
			'id'      => Shipping_Provider::$options->build_option_key( 'internetmarke_stamp_result_format' ),
			'type'    => 'select',
			'options' => array(
				'pdf' => 'PDF',
				'png' => 'PNG',
			),
			'default' => 'pdf',
			'class'   => 'wc-enhanced-select',
			'css'     => 'width: 400px;',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'section_internetmarke',
		);

		return $settings;
	}

	/**
	 * @Hook woocommerce_admin_field_api_status
	 *
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function wgm_api_status( $value ) {

		// Description handling
		$field_description = WC_Admin_Settings::get_field_description( $value );
		extract( $field_description );

		$api_status = Shipping_Provider::$api->test_connection();

		switch ( $api_status ) {
			default:
			case 100:
				$api_status_text  = __( 'No Credentials', 'woocommerce-german-market' );
				$api_status_class = '';
				break;
			case 200:
				$api_status_text  = __( 'Connected', 'woocommerce-german-market' );
				$api_status_class = 'success';
				break;
			case 300:
				$api_status_text  = __( 'Server Error', 'woocommerce-german-market' );
				$api_status_class = 'error';
				break;
			case 401:
				$api_status_text  = __( 'Not Authorized', 'woocommerce-german-market' );
				$api_status_class = 'error';
				break;
		}

		?>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?><?php echo $tooltip_html; ?></th>
			<td class="forminp forminp-checkbox">
				<span class="wgm-api-status <?php echo $api_status_class; ?>"><?php echo esc_html( $api_status_text ); ?></span>
			</td>
		</tr>
		<?php
	}

	/**
	 * @Hook woocommerce_admin_field_internetmarke_status
	 *
	 * @param mixed $value
	 *
	 * @return void
	 * @throws SoapFault
	 */
	public function wgm_internetmarke_status( $value ) {

		// Description handling
		$field_description = WC_Admin_Settings::get_field_description( $value );
		extract( $field_description );

		$api_status = Shipping_Provider::$internetmarke->test_connection();

		switch ( $api_status ) {
			default:
			case 100:
				$api_status_text  = __( 'No Credentials', 'woocommerce-german-market' );
				$api_status_class = '';
				break;
			case 200:
				$api_status_text  = __( 'Connected', 'woocommerce-german-market' );
				$api_status_class = 'success';
				break;
			case 300:
				$api_status_text  = __( 'Server Error', 'woocommerce-german-market' );
				$api_status_class = 'error';
				break;
			case 400:
			case 401:
			case 402:
			case 403:
				$api_status_text  = __( 'Not Authorized', 'woocommerce-german-market' );
				$api_status_class = 'error';
				break;
		}

		?>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?><?php echo $tooltip_html; ?></th>
			<td class="forminp forminp-checkbox">
				<span class="wgm-api-status <?php echo $api_status_class; ?>"><?php echo esc_html( $api_status_text ); ?></span>
			</td>
		</tr>
		<?php
	}
}
