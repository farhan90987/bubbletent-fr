<?php

namespace MarketPress\GermanMarket\Shipping\Provider\DHL;

use MarketPress\GermanMarket\Shipping\Helper;
use MarketPress\GermanMarket\Shipping\Options;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Home_Delivery;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Packstation;
use MarketPress\GermanMarket\Shipping\Provider\DHL\Methods\Parcels;
use MarketPress\GermanMarket\Shipping\Woocommerce_Shipping;
use WC_Shipping;
use WGM_Hpos;
use SoapFault;
use Exception;
use DateTime;
use DateInterval;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

define( 'WGM_SHIPPING_PRODUKT_DHL_PAKET',                   'V01PAK' );
define( 'WGM_SHIPPING_PRODUKT_DHL_WARENPOST',               'V62WP' );
define( 'WGM_SHIPPING_PRODUKT_DHL_KLEINPAKET',              'V62KP' );
define( 'WGM_SHIPPING_PRODUKT_DHL_EURO_PAKET_B2B',          'V54EPAK' );
define( 'WGM_SHIPPING_PRODUKT_DHL_PAKET_INTERNATIONAL',     'V53WPAK' );
define( 'WGM_SHIPPING_PRODUKT_DHL_WARENPOST_INTERNATIONAL', 'V66WPI' );

define( 'WGM_SHIPPING_DELIVERY_DUTY_UNPAID', 'DDU' );
define( 'WGM_SHIPPING_DELIVERY_DUTY_PAID', 'DDP' );
define( 'WGM_SHIPPING_DELIVERY_DUTY_PAID_EXCL_VAT', 'DXV' );
define( 'WGM_SHIPPING_DELIVERY_DUTY_PAID_EXCL_ALL', 'DDX' );

class Shipping_Provider {

	/**
	 * @acces public
	 * @static
	 *
	 * @var string
	 */
	public static string $tracking_link = 'https://www.dhl.de/de/privatkunden/dhl-sendungsverfolgung.html?piececode={tracking_number}';

	/**
	 * @acces public
	 *
	 * @var string
	 */
	public string $name;

	/**
	 * @acces public
	 *
	 * @var string
	 */
	public string $id;

	/**
	 * @acces public
	 *
	 * @var string
	 */
	public string $handle;

	/**
	 * @acces public
	 *
	 * @var string
	 */
	public string $addon_title;

	/**
	 * @acces public
	 *
	 * @var string
	 */
	public string $addon_description;

	/**
	 * @acces public
	 *
	 * @var string
	 */
	public string $addon_dashicon;

	/**
	 * @acces public
	 *
	 * @var string
	 */
	public string $addon_image;

	/**
	 * @acces public
	 *
	 * @var string
	 */
	public string $addon_video;

	/**
	 * @acces public
	 *
	 * @var string
	 */
	public string $api_packstation_finder_service;

	/**
	 * @acces public
	 *
	 * @var string
	 */
	public string $api_parcelshop_finder_service;

	/**
	 * @acces public
	 */
	public static ?Options $options = null;

	/**
	 * @acces public
	 *
	 * @var Navigation
	 */
	private static Navigation $navigation;

	/**
	 * @acces public
	 *
	 * @var Frontend
	 */
	public static Frontend $frontend;

	/**
	 * @acces public
	 *
	 * @var Backend
	 */
	public static Backend $backend;

	/**
	 * @acces public
	 *
	 * @var Labels
	 */
	public static Labels $labels;

	/**
	 * @acces public
	 *
	 * @var Internetmarke
	 */
	public static Internetmarke $internetmarke;

	/**
	 * @acces public
	 *
	 * @var Api
	 */
	public static Api $api;

	/**
	 * @acces public
	 * @static
	 *
	 * @var Ajax
	 */
	public static Ajax $ajax;

	/**
	 * Singleton.
	 *
	 * @acces protected
	 * @static
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * Singleton getInstance.
	 *
	 * @static
	 *
	 * @return self
	 */
	public static function get_instance() : self {

		return ( null !== self::$instance ) ? self::$instance : self::$instance = new self();
	}

	/**
	 * Class constructor.
	 */
	public function __construct() {

		$this->name                 = Helper::get_provider_from_namespace( __NAMESPACE__ );
		$this->id                   = strtolower( $this->name );
		$this->handle               = 'wgm_shipping_' . $this->id;
		$this->addon_title          = __( 'DHL Shipping', 'woocommerce-german-market' );
		$this->addon_description    = __( 'You can use this add-on to generate DHL labels. This also includes deliveries to packstations/parcel stores, preferred day delivery and much more. The product "Deutsche Post Internetmarke" can also be used with the add-on.', 'woocommerce-german-market' ) . '<br /><br />' . sprintf ( __( "You can find the documentation for the add-on <a href=\"%s\" target=\"_blank\">here</a>.", 'woocommerce-german-market' ), __('https://marketpress.com/documentation/german-market/dhl-shipping-add-on/', 'woocommerce-german-market' ) );
		$this->addon_dashicon       = '';
		$this->addon_image          = plugins_url() . '/woocommerce-german-market/add-ons/woocommerce-shipping/includes/provider/dhl/assets/images/DHL_logo_rgb.png';
		$this->addon_video          = '';

		// Check if addon is activated.

		if ( 'on' !== get_option( Woocommerce_Shipping::build_german_market_addon_option_key( $this->id ) ) ) {
			return;
		}

		self::$options       = new Options( $this );
		self::$internetmarke = Internetmarke::get_instance( $this->id );
		self::$navigation    = new Navigation( $this->id );
		self::$frontend      = new Frontend( $this->id );
		self::$backend       = new Backend( $this->id );
		self::$labels        = new Labels( $this->id );
		self::$api           = new Api( $this );
		self::$ajax          = new Ajax( $this->id );

		$this->init_hooks();

		if ( is_admin() ) {
			$this->init_backend_hooks();
		} else {
			$this->init_frontend_hooks();
		}
	}

	/**
	 * Init global hooks for backend and frontend.
	 */
	private function init_hooks() {

		// Load shipping methods.
		add_action( 'woocommerce_shipping_init',                                               array( $this, 'init_shipping_methods' ) );
		add_filter( 'woocommerce_shipping_methods',                                            array( $this, 'add_shipping_methods' ) );
		add_filter( 'woocommerce_package_rates',                                               array( $this, 'include_free_shipping_methods' ), 15, 2 );

		// Reload text domain.
		add_action( 'woocommerce_email',                                                       array( '\MarketPress\GermanMarket\Shipping\Woocommerce_Shipping', 'reload_shipping_textdomain' ), 1, 1 );

		// Load global styles.
		add_action( 'wp_enqueue_scripts',                                                      array( '\MarketPress\GermanMarket\Shipping\Woocommerce_Shipping', 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts',                                                   array( '\MarketPress\GermanMarket\Shipping\Woocommerce_Shipping', 'enqueue_styles' ) );

		// Checkout save terminal data to order.
		add_action( 'woocommerce_checkout_update_order_meta',                                  array( self::$backend, 'process_checkout_save_terminal_to_order' ), 30, 1 );

		// Ajax callback for selecting terminal / packstation.
		add_action( 'wc_ajax_choose_' . $this->id . '_terminal',                               array( self::$ajax, 'ajax_save_session_terminal' ) );
		add_action( 'wc_ajax_nopriv_choose_' . $this->id . '_terminal',                        array( self::$ajax, 'ajax_save_session_terminal' ) );

		// Hide Shipping Methods if free shipping is available.
		add_filter( 'wgm_shipping_hide_flat_rates_shipping_if_free_available_rate_identifier', array( $this, 'add_shipping_provider_methods' ) );

		// Shipping Label Generation on new order.
		add_action('woocommerce_thankyou',                                                     array( self::$labels, 'automatic_shipping_label_generator_new_order' ), 100 );
	}

	/**
	 * Includes DHL Shipping Methods if WC 9.9 Option is used and our methods are free.
	 *
	 * @Hook woocommerce_package_rates
	 *
	 * @param array $rates $package['rates'] Package rates.
	 * @param array $package Package of cart items.
	 *
	 * @return array
	 */
	function include_free_shipping_methods( $rates, $package ) {

		if ( 'yes' !== get_option( 'woocommerce_shipping_hide_rates_when_free', 'no' ) ) {
			return $rates;
		}

		$free_shipping_found = false;
		$wc_shipping         = WC_Shipping::instance();

		foreach ( $wc_shipping->load_shipping_methods( $package ) as $shipping_method ) {

			if ( ! in_array( $shipping_method->id, array( 'dhl_home_delivery', 'dhl_packstation', 'dhl_parcelshops' ) ) ) {
				continue;
			}

			$free_shipping = false;
			$cost          = $shipping_method->get_option( 'cost' );
			$weight_based  = $shipping_method->get_option( 'calc_weight_based' );
			$weight        = isset( WC()->cart ) ? WC()->cart->get_cart_contents_weight() : 0;

			if ( ( $cost == 0 ) && ! ( ( 'weight' == $shipping_method->type ) || ( 'yes' === $weight_based ) ) ) {
				$free_shipping_found = true;
				$shipping_rates      = $shipping_method->get_rates_for_package( $package );
				foreach ( $shipping_rates as $rate ) {
					$rates[ $rate->id ] = $rate;
				}

				continue;
			}

			if ( WC()->cart && ! empty( $shipping_method->free_min_amount ) && $shipping_method->free_min_amount > 0 ) {
				$total = WC()->cart->get_displayed_subtotal();

				if ( WC()->cart->display_prices_including_tax() ) {
					$total = round( $total - ( WC()->cart->get_discount_total() + WC()->cart->get_discount_tax() ), wc_get_price_decimals() );
				} else {
					$total = round( $total - WC()->cart->get_discount_total(), wc_get_price_decimals() );
				}

				if ( $total >= $shipping_method->free_min_amount ) {
					$free_shipping       = true;
					$free_shipping_found = true;
				}
			}

			if ( ! $free_shipping && ( ( 'weight' == $shipping_method->type ) || ( 'yes' === $weight_based ) ) ) {
				$weight_rates = explode( ',', $shipping_method->cost_rates );

				foreach ( $weight_rates as $rate ) {
					$data = explode( ':', $rate );

					if ( $weight >= $data[ 0 ] ) {
						if ( isset( $data[ 1 ] ) ) {
							$cost = str_replace( ',', '.', $data[ 1 ] );
						}
					}
				}

				if ( $cost <= 0 ) {
					$free_shipping       = true;
					if ( ! $free_shipping_found ) {
						$free_shipping_found = true;
					}
				}
			}

			if ( $free_shipping ) {
				$shipping_rates = $shipping_method->get_rates_for_package( $package );
				foreach ( $shipping_rates as $rate ) {
					$rates[ $rate->id ] = $rate;
				}
			}
		}

		// Remove shipping methods with costs > 0 if we have a free method available.
		if ( $free_shipping_found ) {
			foreach ( $rates as $rate_key => $rate ) {
				if ( $rate->cost > 0 ) {
					unset( $rates[ $rate_key ] );
				}
			}
		}

		return $rates;
	}

	/**
	 * Init admin hooks.
	 *
	 * @acces private
	 *
	 * @return void
	 */
	private function init_backend_hooks() {

		// Load global scripts for backend.
		add_action( 'admin_enqueue_scripts',                                                          array( self::$backend, 'global_enqueue_scripts' ) );

		// Updating Options.
		add_action( 'admin_init',                                                                     array( self::$navigation, 'update_package_boxes_options' ), 10 );
		add_action( 'admin_init',                                                                     array( self::$navigation, 'update_google_map_options' ), 10 );
		add_action( 'admin_init',                                                                     array( self::$navigation, 'update_warehouses_options' ), 10 );

		// Extend German Market Menu.
		add_filter( 'woocommerce_de_ui_left_menu_items',                                              array( self::$navigation, 'extend_german_market_navigation' ), 10, 1 );

		// Enqueue Scripts.
		add_action( 'admin_enqueue_scripts',                                                          array( self::$backend, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts',                                                          array( self::$backend, 'enqueue_scripts' ) );

		// Save terminal after Backend edit post.
		if ( Woocommerce_Shipping::$hpos_active ) {
			// Save terminal after Backend edit post. (WooCommerce Version >= 7.8 HPOS)
			add_action( 'woocommerce_process_shop_order_meta',                                        array( self::$backend, 'maybe_save_changed_terminal_hpos' ), 10, 2 );
		} else {
			// Save service fields after Backend edit post. (WooCommerce Version < 7.8)
			add_action( 'save_post',                                                                  array( self::$backend, 'maybe_save_changed_terminal' ), 10, 1 );
		}

		// Custom order actions.
		add_action( 'woocommerce_order_actions',                                                      array( self::$backend, 'add_order_actions' ) );
		add_filter( 'wgm_shipping_woocommerce_order_actions',                                         array( self::$backend, 'add_additional_order_actions' ), 10, 2 );
		add_action( 'woocommerce_order_action_' . $this->id . '_print_parcel_label',                  array( self::$labels, 'do_print_parcel_label' ) );
		add_action( 'woocommerce_order_action_' . $this->id . '_cancel_shipment',                     array( self::$labels, 'do_cancel_shipment' ) );

		// Create or download label from order actions.
		add_filter( 'woocommerce_admin_order_actions',                                                array( self::$backend, 'backend_icon_download' ), 10, 2 );
		add_action( 'wp_ajax_woocommerce_' . $this->id . '_ajax_shipping_label_download',             array( self::$labels, 'ajax_download_order_shipping_label' ) );

		// Shipping Label Generation on order status change.
		add_action( 'woocommerce_order_status_changed',                                               array( self::$labels, 'automatic_shipping_label_generator' ), 10, 4 );

		// Add Meta Box to order page.
		add_action( 'add_meta_boxes',                                                                 array( self::$backend, 'add_shipping_services_meta_box' ) );

		if ( Woocommerce_Shipping::$hpos_active ) {
			// Save terminal after Backend edit post. (WooCommerce Version >= 7.8 HPOS)
			add_action( 'woocommerce_process_shop_order_meta',                                        array( self::$backend, 'save_services_fields_for_packaging_hpos' ), 10, 2 );
		} else {
			// Save service fields after Backend edit post. (WooCommerce Version < 7.8)
			add_action( 'save_post',                                                                  array( self::$backend, 'save_services_fields_for_packaging' ), 10, 1 );
		}

		// Add Internetmarke Layer to footer area.
		add_action( 'admin_footer',                                                                   array( self::$internetmarke, 'generate_internetmarke_wizard' ), 10, 1 );

		// Internetmarke load wallet balance.
		add_action( 'wp_ajax_woocommerce_internetmarke_load_wallet_balance',                          array( self::$internetmarke, 'load_wallet_balance' ) );

		// Internetmarke load products by category.
		add_action( 'wp_ajax_woocommerce_internetmarke_load_products_and_services_by_category',       array( self::$internetmarke, 'load_products_and_services_by_category' ) );

		// Internetmarke load image preview.
		add_action( 'wp_ajax_woocommerce_internetmarke_load_image_preview',                           array( self::$internetmarke, 'load_image_preview' ) );

		// Internetmarke process checkout.
		add_action( 'wp_ajax_woocommerce_internetmarke_process_checkout',                             array( self::$internetmarke, 'process_checkout' ) );

		// Internetmarke download label from order actions.
		add_filter( 'woocommerce_admin_order_actions',                                                array( self::$internetmarke, 'backend_icon_download' ), 10, 2 );

		// Internetmarke label download.
		add_action( 'wp_ajax_woocommerce_dhl_shipping_internetmarke_label_download',                  array( self::$internetmarke, 'download_label' ) );

		// Bulk order actions.
		if ( Woocommerce_Shipping::$hpos_active ) {
			add_action( 'admin_init', function() {
				add_filter( WGM_Hpos::get_hook_for_order_bulk_actions(),                             array( self::$backend, 'define_orders_bulk_actions' ), 10 );
				add_filter( 'handle_' . WGM_Hpos::get_hook_for_order_bulk_actions(),                 array( self::$labels, 'handle_orders_bulk_actions' ), 10, 3 );
			} );
		} else {
			add_filter( 'bulk_actions-edit-shop_order',                                               array( self::$backend, 'define_orders_bulk_actions' ), 10 );
			add_filter( 'handle_bulk_actions-edit-shop_order',                                        array( self::$labels, 'handle_orders_bulk_actions' ), 10, 3 );
		}

		add_filter( 'admin_notices',                                                                  array( self::$backend, 'bulk_admin_notices' ) );

		// Disable Shipping method for Cash on Delivery Payment mmethod.
		add_filter( 'german_market_gateway_cash_on_delviery_enable_for_shipping_methods',             array( self::$backend, 'gateway_cash_on_delivery_enable_for_shipping_methods' ), 10, 2 );
		add_filter( 'german_market_gateway_cash_on_delviery_enable_for_shipping_methods_enter_if',    '__return_true' );

		// Add Tracking Column to Orders and fill with content / tracking links.
		if ( Woocommerce_Shipping::$hpos_active ) {
			add_action( 'admin_init', function() {
				add_filter( 'manage_' . WGM_Hpos::get_edit_shop_order_screen() . '_columns', array( '\MarketPress\GermanMarket\Shipping\Woocommerce_Shipping', 'add_order_tracking_column_header' ), 20 );
				add_filter( WGM_Hpos::get_hook_manage_shop_order_custom_column(),            array( self::$backend, 'add_order_tracking_column_content' ), 10, 2 );
			} );
		} else {
			add_filter( 'manage_edit-shop_order_columns',                                    array( '\MarketPress\GermanMarket\Shipping\Woocommerce_Shipping', 'add_order_tracking_column_header' ), 20 );
			add_action( 'manage_shop_order_posts_custom_column',                             array( self::$backend, 'add_order_tracking_column_content' ), 10, 2 );
		}

		// Tracking Link Information in Costumer Email.
		$tracking_link_enabled  = self::$options->get_option( 'parcel_tracking_enabled', 'off' );
		$tracking_link_position = self::$options->get_option( 'parcel_tracking_position', 'bottom' );

		if ( $tracking_link_enabled == 'on' ) {
			if ( 'top' === $tracking_link_position) {
				add_action( 'woocommerce_email_order_details',                                        array( self::$backend, 'add_order_tracking_link_email' ), 5, 4 );
			} else
			if ( 'bottom' === $tracking_link_position ) {
				add_action( 'woocommerce_email_order_meta',                                           array( self::$backend, 'add_order_tracking_link_email' ), 5, 4 );
			}
		}

		add_action( 'wp_ajax_set_checkout_session',                                                   array( '\MarketPress\GermanMarket\Shipping\Woocommerce_Shipping', 'set_checkout_session' ) );
		add_action( 'wp_ajax_nopriv_set_checkout_session',                                            array( '\MarketPress\GermanMarket\Shipping\Woocommerce_Shipping', 'set_checkout_session' ) );

		// Preferred Delivery Date
		if ( 'on' === self::$options->get_option( 'service_preferred_day_enabled', 'off' ) ) {
			add_action( 'wp_ajax_apply_preferred_delivery_date_fee',                                  array( self::$ajax, 'apply_preferred_delivery_date_fee' ) );
			add_action( 'wp_ajax_nopriv_apply_preferred_delivery_date_fee',                           array( self::$ajax, 'apply_preferred_delivery_date_fee' ) );
		}

		add_action( 'wp_ajax_woocommerce_' . $this->id . '_shipping_label_download',                  array( self::$labels, 'download_order_shipping_label' ) );
		add_action( 'wp_ajax_woocommerce_' . $this->id . '_retoure_shipping_label_download',          array( self::$labels, 'download_order_shipping_retoure_label' ) );
		add_action( 'wp_ajax_woocommerce_' . $this->id . '_export_documents_download',                array( self::$labels, 'download_order_shipping_export_documents' ) );

		// Ajax Label Creation from Admin Order.
		add_action( 'wp_ajax_woocommerce_' . $this->id . '_admin_order_create_label',                 array( self::$labels, 'ajax_order_shipment_creation' ) );

		// Ajax calculate parcel total weight from Admin Order after input change.
		add_action( 'wp_ajax_woocommerce_' . $this->id . '_admin_order_calculate_parcel_total_weight',array( self::$labels, 'ajax_order_calculate_parcel_total_weight' ) );

		// Ajax Cancel Shipment from Admin Order.
		add_action( 'wp_ajax_woocommerce_' . $this->id . '_admin_order_cancel_shipment',              array( self::$labels, 'ajax_do_cancel_shipment' ) );

		add_action( 'admin_notices',                                                                  array( 'MarketPress\GermanMarket\Shipping\Helper', 'display_flash_notices' ), 12 );

		// Add special fields to product shipping tab.
		add_action( 'woocommerce_product_options_shipping_product_data',                              array( self::$backend, 'add_export_information_fields_to_product_data' ) );
		add_action( 'woocommerce_process_product_meta',                                               array( self::$backend, 'save_export_information_product_meta' ), 10, 2 );
	}

	/**
	 * Init frontend hooks.
	 *
	 * @acces private
	 *
	 * @return void
	 */
	private function init_frontend_hooks() {

		// Load global scripts for frontend.
		add_action( 'wp_enqueue_scripts',                                     array( self::$frontend, 'global_enqueue_scripts' ) );

		add_action( 'wp_enqueue_scripts',                                     array( self::$frontend, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts',                                     array( self::$frontend, 'enqueue_scripts' ) );

		add_filter( 'woocommerce_locate_template',                            array( self::$frontend, 'locate_template' ), 20, 3 );
		add_filter( 'woocommerce_locate_core_template',                       array( self::$frontend, 'locate_template' ), 20, 3 );

		add_filter( 'woocommerce_checkout_process',                           array( self::$frontend, 'checkout_client_number_process' ) );

		add_action( 'woocommerce_checkout_update_order_review',               array( self::$ajax, 'checkout_save_session_fields' ), 10, 1 );

		// AJAX methods
		add_action( 'wc_ajax_get_' . $this->id . '_parcels',                  array( self::$ajax, 'get_ajax_terminals' ) );
		add_action( 'wc_ajax_nopriv_get_' . $this->id . '_parcels',           array( self::$ajax, 'get_ajax_terminals' ) );
		add_action( 'wc_ajax_get_' . $this->id . '_parcels_modal',            array( self::$ajax, 'get_ajax_terminals_modal' ) );
		add_action( 'wc_ajax_nopriv_get_' . $this->id . '_parcels_modal',     array( self::$ajax, 'get_ajax_terminals_modal' ) );

		add_action( 'wc_ajax_get_' . $this->id . '_packstations',              array( self::$ajax, 'get_ajax_packstations' ) );
		add_action( 'wc_ajax_nopriv_get_' . $this->id . '_packstations',       array( self::$ajax, 'get_ajax_packstations' ) );
		add_action( 'wc_ajax_get_' . $this->id . '_packstations_modal',        array( self::$ajax, 'get_ajax_packstations_modal' ) );
		add_action( 'wc_ajax_nopriv_get_' . $this->id . '_packstations_modal', array( self::$ajax, 'get_ajax_packstations_modal' ) );
		add_action( 'wc_ajax_get_' . $this->id . '_selected_terminal_info',        array( self::$ajax, 'get_terminal_info_from_session' ) );
		add_action( 'wc_ajax_nopriv_get_' . $this->id . '_selected_terminal_info', array( self::$ajax, 'get_terminal_info_from_session' ) );

		// Available payment methods
		add_filter( 'woocommerce_available_payment_gateways',                 array( self::$frontend, 'available_payment_gateways' ), 10, 1 );

		// Download Retoure Label Link in "My Account" section
		add_action( 'woocommerce_order_details_after_order_table',            array( self::$frontend, 'add_retoure_label_download_link' ), 100, 1 );

		// Preferred Delivery Date
		if ( 'on' === self::$options->get_option( 'service_preferred_day_enabled', 'off' ) ) {
			add_action( 'woocommerce_cart_calculate_fees',                    array( self::$ajax, 'add_delivery_day_fee' ) );
		}

		// Handle personal id field
		$age_rating = get_option( 'german_market_age_rating', 'off' );
		$default    = Shipping_Provider::$options->get_option( 'service_ident_check_default', 0 );

		if ( ( 'on' === $age_rating ) || ( 0 !== $default ) ) {
			add_action( 'wp',                                                 array( self::$frontend, 'maybe_checkout_add_dob_field' ), 10, 1 );
			add_action( 'woocommerce_checkout_update_order_review',           array( self::$frontend, 'maybe_checkout_add_dob_field' ), 10, 1 );
			add_action( 'woocommerce_checkout_update_order_meta',             array( self::$frontend, 'process_checkout_dob_field' ), 30, 1 );
			add_action( 'woocommerce_after_checkout_validation',              array( self::$frontend, 'checkout_validate_dob_field' ), 10, 2 );
		}
	}

	/**
	 * Load and initialize shipping methods.
	 *
	 * @Hook woocommerce_shipping_init
	 *
	 * @acces public
	 *
	 * @return void
	 */
	public function init_shipping_methods() {

		if ( ! empty( Woocommerce_Shipping::get_instance()->providers ) ) {
			$providers = Woocommerce_Shipping::get_instance()->providers;
			if ( ! empty( $providers[ $this->id ][ 'methods' ] ) ) {
				foreach ( $providers[ $this->id ][ 'methods' ] as $method_id => $method ) {
					if ( method_exists( $method, 'init_actions_and_filters' ) ) {
						$method->init_actions_and_filters();
					}
				}
			}
		}
	}

	/**
	 * This function is fired in frontend and backend to return the methods.
	 *
	 * @Hook woocommerce_shipping_methods
	 *
	 * @param array $methods shipping methods
	 *
	 * @return array
	 */
	public function add_shipping_methods( array $methods ) : array {

		if ( ! empty( Woocommerce_Shipping::get_instance()->providers ) ) {
			$providers = Woocommerce_Shipping::get_instance()->providers;
			if ( ! empty( $providers[ $this->id ][ 'methods' ] ) ) {
				foreach ( $providers[ $this->id ][ 'methods' ] as $method_id => $method ) {
					$methods[ $method_id ] = get_class( $method );
				}
			}
		}

		return $methods;
	}

	/**
	 * Returns if shipping provider is supported for the shop base country.
	 *
	 * @static
	 *
	 * @return bool
	 */
	public static function is_base_country_supported() : bool {

		$supported_countries = self::get_supported_base_countries();
		$base_country        = wc_get_base_location()[ 'country' ];
		$shipper_country     = self::$options->get_option( 'shipping_shop_address_country' );

		if ( ( $base_country !== $shipper_country ) && ( ! empty( $shipper_country ) ) ) {
			$base_country = $shipper_country;
		}

		return in_array( $base_country, $supported_countries, true );
	}

	/**
	 * Returns supported base countries.
	 *
	 * @access private
	 * @static
	 *
	 * @return array
	 */
	private static function get_supported_base_countries() : array {

		return array(
			'DE',
		);
	}

	/**
	 * Calculate first possible preferred delivery date based on current date and settings.
	 *
	 * @static
	 *
	 * @return int|DateTime
	 * @throws Exception
	 */
	public static function calculate_first_preferred_delivery_day( $return_datetime = false ) {

		$preferred_day                 = 2; // Current day plus 2 days is the minimum by default
		$current_day                   = new DateTime( date( 'Y-m-d 00:00', time() ), wp_timezone() );
		$current_day                   = $current_day->add( DateInterval::createFromDateString( '2 days' ) );
		$current_day_time              = new DateTime( 'now', wp_timezone() );
		$preferred_day_cutoff          = Shipping_Provider::$options->get_option( 'service_preferred_day_cutoff', '12:00' );
		$preferred_day_processing_days = Shipping_Provider::$options->get_option( 'service_preferred_day_processing_days', 0 );

		// Check for Sundays

		$day_of_week = date('w', $current_day->getTimestamp() );

		if ( 0 == $day_of_week ) {
			$current_day    = $current_day->add( DateInterval::createFromDateString( '1 day' ) );
			$preferred_day += 1;
		}

		// Check Cut-off-Time

		$cutoff_time_array  = explode( ':', $preferred_day_cutoff );
		$current_time_array = explode( ':', date( 'H:i', $current_day_time->getTimestamp() ) );

		if ( $current_time_array[ 0 ] > $cutoff_time_array[ 0 ] ) {
			$current_day    = $current_day->add( DateInterval::createFromDateString( '1 day' ) );
			$preferred_day += 1;
		} else
		if ( $current_time_array[ 0 ] == $cutoff_time_array[ 0 ] ) {
			if ( $cutoff_time_array[ 1 ] <= $current_time_array[ 1 ] ) {
				$current_day    = $current_day->add( DateInterval::createFromDateString( '1 day' ) );
				$preferred_day += 1;
			}
		}

		// Add processing days

		if ( $preferred_day_processing_days > 0 ) {
			$interval       = ( 1 === $preferred_day_processing_days ) ? '1 day' : $preferred_day_processing_days . ' days';
			$current_day    = $current_day->add( DateInterval::createFromDateString( $interval ) );
			$preferred_day += $preferred_day_processing_days;
		}

		// Check for Sundays

		$day_of_week = date('w', $current_day->getTimestamp() );

		if ( 0 == $day_of_week ) {
			$current_day    = $current_day->add( DateInterval::createFromDateString( '1 day' ) );
			$preferred_day += 1;
		}

		return ( false === $return_datetime ) ? $preferred_day : $current_day;
	}

	/**
	 * Add shipping methods to array to exclude them if free shipping is available.
	 *
	 * @param array $methods
	 *
	 * @return array
	 */
	public function add_shipping_provider_methods( array $methods ) : array {

		$methods[] = Parcels::get_instance()->id;
		$methods[] = Packstation::get_instance()->id;
		$methods[] = Home_Delivery::get_instance()->id;

		return $methods;
	}
}
