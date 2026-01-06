<?php

namespace MarketPress\GermanMarket\Shipping;

use ReflectionClass;
use ReflectionException;
use WC_Emails;
use WC_Session_Handler;
use WGM_Hpos;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class Woocommerce_Shipping {

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
	 * Service provider placeholder.
	 *
	 * @acces public
	 *
	 * @var array
	 */
	public array $providers = array();

	/**
	 * Terminal data field.
	 *
	 * @acces public
	 * @static
	 *
	 * @var string
	 */
	public static string $terminal_data_field;

	/**
	 * @acces public
	 * @static
	 *
	 * @var bool
	 */
	public static bool $hpos_active = false;

	/**
	 * Order Meta utilities placeholder.
	 *
	 * @acces public
	 * @static
	 *
	 * @var Order_Meta
	 */
	public static Order_Meta $order_meta;

	/**
	 * @access private
	 * @static
	 * @var array|string[]
	 */
	private static array $free_shipping_method_ids = array( 'free_shipping' );

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
	 *
	 * @acces protected
	 *
	 * @throws ReflectionException
	 */
	protected function __construct() {

		// Check High-Performance-Order-Storage compatibility.
		if ( class_exists( 'WGM_Hpos' ) ) {
			if ( WGM_Hpos::is_hpos_enabled() ) {
				self::$hpos_active = true;
			}
		}

		// auto-load classes on demand
		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}
		spl_autoload_register( array( $this, 'autoload' ) );

		$this->providers = $this->get_providers();
		self::$terminal_data_field = '_wgm_shipping_terminal_data';

		if ( ! empty( $this->providers ) ) {
			// Extend German Market add-ons overview.
			add_filter( 'woocommerce_de_add_ons_menu_list', array( $this, 'extend_german_market_addons_overview' ), 20 );
			// Hide flat rate shipping methods if free shipping is available.
			add_filter( 'woocommerce_package_rates', 		array( $this, 'hide_shipping_provider_rates_when_free_is_available' ), 20, 2 );
		}

		// Add checkbox to product data in admin.
		/*
		if ( is_admin() {
			add_action( 'woocommerce_product_options_shipping_product_data', array( '\MarketPress\GermanMarket\Shipping\Backend', 'add_keepflat_checkbox_to_product_data' ) );
			add_action( 'woocommerce_process_product_meta',                  array( '\MarketPress\GermanMarket\Shipping\Backend', 'save_keepflat_product_meta' ), 10, 2 );
		}
		*/

		// Database utilities.
		self::$order_meta = Order_Meta::get_instance();
	}

	/**
	 * Class autoloader.
	 *
	 * @acces private
	 *
	 * @param string $class class name
	 *
	 * @return void
	 */
	private function autoload( string $class ) {

		// Just trigger on our own namespace.
		if ( false !== strpos( $class, __NAMESPACE__ ) ) {
			$var      = false;
			$prefix   = __NAMESPACE__;
			$base_dir = __DIR__;

			$len = strlen( $prefix );
			if ( strncmp( $prefix, $class, $len ) !== 0 ) {
				return;
			}

			$relative_class_name = substr( $class, $len );
			$class_filename      = strtolower( str_replace( '_', '-', substr( $relative_class_name, strrpos( $relative_class_name, '\\' ) + 1 ) ) );

			if ( strpos( $class, 'Methods' ) ) {
				$class_filename = 'shipping-method-' . $class_filename;
			}

			$is_exception_class = false;
			$exception_path     = '/exceptions/';

			if ( strpos( $class_filename, 'exception' ) ) {
				$is_exception_class = true;
			}

			$relative_class_name = substr( $relative_class_name, 0, strrpos( $relative_class_name, '\\' ) + 1 );
			$filename            = $base_dir . strtolower( str_replace( '\\', '/', $relative_class_name ) . ( ! empty( $is_exception_class ) ? $exception_path : '' ) . 'class-' . $class_filename . '.php' );

			// Check if class file exists.
			if ( file_exists( $filename ) && is_readable( $filename ) ) {
				require_once $filename;
			}
		}
	}

	/**
	 * Returns all available shipping provider names.
	 *
	 * @static
	 *
	 * @return array
	 */
	public static function get_available_provider_names() : array {

		$provider_directory_path = __DIR__ . DIRECTORY_SEPARATOR . 'provider';
		$providers               = array();

		if ( ! is_dir( $provider_directory_path ) ) {
			return $providers;
		}

		$providers = scandir( $provider_directory_path );

		if ( is_array( $providers ) ) {
			foreach ( $providers as $provider ) {
				if ( '.' == $provider || '..' == $provider ) {
					continue;
				}
				$providers[] = $provider;
			}
		}

		return $providers;
	}

	/**
	 * Returns all available shipping providers.
	 *
	 * @acces private
	 *
	 * @return array
	 *
	 * @throws ReflectionException
	 */
	private function get_providers() : array {

		if ( empty( $this->providers ) ) {

			$provider_directory_path = __DIR__ . DIRECTORY_SEPARATOR . 'provider';

			if ( ! is_dir( $provider_directory_path ) ) {
				return $this->providers;
			}

			$providers = scandir( $provider_directory_path );

			if ( is_array( $providers ) ) {
				foreach ( $providers as $provider ) {
					if ( '.' == $provider || '..' == $provider ) {
						continue;
					}
					$files = scandir( $provider_directory_path . DIRECTORY_SEPARATOR . $provider );
					if ( is_array( $files ) ) {
						if ( ! in_array( 'class-shipping-provider.php', $files ) ) {
							continue;
						}
						$namespace = __NAMESPACE__ . '\Provider\\' . strtoupper( $provider ) . '\Shipping_Provider';
						$class     = new ReflectionClass( $namespace );
						if ( method_exists( $class->name, 'get_instance' ) ) {
							$instance = $class->name::get_instance();
							$this->providers[ $provider ] = array(
								'instance'  => $instance,
								'method_id' => $instance->id,
								'methods'   => $this->get_shipping_methods( $provider ),
							);
						}
					}
				}
			}
		}

		return $this->providers;
	}

	/**
	 * Load and initialize shipping methods.
	 *
	 * @acces private
	 *
	 * @param string $provider
	 *
	 * @return array
	 * @throws ReflectionException
	 */
	private function get_shipping_methods( string $provider ) : array {

		if ( empty( $provider ) ) {
			return array();
		}

		// Check if addon is activated.
		if ( 'on' != get_option( self::build_german_market_addon_option_key( $provider ) ) ) {
			return array();
		}

		if ( empty( $this->providers[ $provider ][ 'methods' ] ) ) {

			$shipping_methods = array();

			$dir = __DIR__ . DIRECTORY_SEPARATOR . 'provider' . DIRECTORY_SEPARATOR . $provider . DIRECTORY_SEPARATOR . 'methods';

			if ( ! is_dir( $dir ) ) {
				return $shipping_methods;
			}

			$methods = @scandir( $dir );

			if ( is_array( $methods ) ) {
				foreach( $methods as $method ) {
					if ( ( '.' == $method ) || ( '..' == $method ) ) {
						continue;
					}
					$method_name = preg_replace( '#(class-shipping-method-)(.+)(\.php)#', '$2', $method );
					$method_name = str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $method_name ) ) );
					$namespace   = __NAMESPACE__ . '\Provider\\' . strtoupper( $provider ) . '\Methods\\' . $method_name;
					$class       = new ReflectionClass( $namespace );
					if ( method_exists( $class->name, 'get_instance' ) ) {
						$instance                       = $class->name::get_instance();
						$method_id                      = $instance->id;
						$shipping_methods[ $method_id ] = $instance;
					}
				}
			}

			$this->providers[ $provider ][ 'methods' ] = $shipping_methods;
		}

		return $this->providers[ $provider ][ 'methods' ];
	}

	/**
	 * WooCommerce email refresh text domain.
	 *
	 * @Hook woocommerce_email
	 *
	 * @param WC_Emails $order_id
	 *
	 * @return void
	 */
	public static function reload_shipping_textdomain( WC_Emails $order_id ) {

		WC()->shipping();
	}

	/**
	 * Adds 'Tracking' column header to 'Orders' page immediately after 'Status' column.
	 *
	 * @hook manage_edit-shop_order_columns
	 *
	 * @static
	 *
	 * @param array $columns
	 *
	 * @return array $new_columns
	 */
	public static function add_order_tracking_column_header( array $columns ) : array {

		$new_columns = array();

		foreach ( $columns as $column_name => $column_info ) {
			$new_columns[ $column_name ] = $column_info;
			if ( 'order_status' === $column_name ) {
				$new_columns[ 'order_tracking' ] = __( 'Tracking-Number', 'woocommerce-german-market' );
			}
		}

		return $new_columns;
	}

	/**
	 * Save COD (Cash on Delivery) to WC() session if is 0 or 1.
	 *
	 * @uses wp_ajax_set_checkout_session
	 * @uses wp_ajax_nopriv_set_checkout_session
	 *
	 * @static
	 *
	 * @return void
	 */
	public static function set_checkout_session() {

		$cod = filter_var( $_REQUEST[ 'cod' ], FILTER_SANITIZE_NUMBER_INT );

		if ( is_numeric( $cod ) ) {
			// Is session running?
			if ( isset ( WC()->session ) && ! WC()->session->has_session() ) {
				WC()->session->set_customer_session_cookie( true );
			}
			WC()->session->set( 'cod_for_parcel', $cod );
		}

		wp_die();
	}

	/**
	 * Returns instance of given provider id.
	 *
	 * @acces public
	 *
	 * @param string $id
	 *
	 * @return object|bool
	 */
	public function get_provider_by_id( string $id ) : ?object {

		if ( ! empty( $this->providers ) && isset( $this->providers[ $id ] ) ) {
			return $this->providers[ $id ][ 'instance' ];
		}

		return false;
	}

	/**
	 * Extend German Market addons overview.
	 * Format of array:
	 * array(
	 *     'title'				=> __( 'Example Addon Title', 'woocommerce-german-market' ),
	 *     'description'		=> __( 'Example Description', 'woocommerce-german-market' ),
	 *     'dashicon'			=> 'backup',
	 *     'image'				=> '',
	 *     'on-off'			    => get_option( 'wgm_add_on_example_addon_name' ) == 'on' ? 'on' : 'off',
	 *     'id'				    => 'wgm_add_on_example_addon_name',
	 *     'video'				=> 'https://marketpress-videos.s3.eu-central-1.amazonaws.com/german-market/example-video-tutorial-url.mp4',
	 * )
	 *
	 * @uses woocommerce_de_add_ons_menu_list
	 *
	 * @acces public
	 *
	 * @param array $addons
	 *
	 * @return array
	 */
	public function extend_german_market_addons_overview( array $addons ) : array {

		foreach ( $this->providers as $provider_id => $provider ) {
			$addons[] = array(
				'title'       => property_exists( $provider[ 'instance' ], 'addon_title' )       ? $provider[ 'instance' ]->addon_title       : '',
				'description' => property_exists( $provider[ 'instance' ], 'addon_description' ) ? $provider[ 'instance' ]->addon_description : '',
				'dashicon'    => property_exists( $provider[ 'instance' ], 'addon_dashicon' )    ? $provider[ 'instance' ]->addon_dashicon    : '',
				'image'       => property_exists( $provider[ 'instance' ], 'addon_image' )       ? $provider[ 'instance' ]->addon_image       : '',
				'on-off'      => 'on' == get_option( self::build_german_market_addon_option_key( $provider_id ) ) ? 'on' : 'off',
				'id'          => self::build_german_market_addon_option_key( $provider_id ),
				'video'       => property_exists( $provider[ 'instance' ], 'addon_video' )       ? $provider[ 'instance' ]->addon_video       : '',
			);
		}

		return $addons;
	}

	/**
	 * Build and returns the option key string for given .
	 *
	 * @acces public
	 * @static
	 *
	 * @param string $provider_id
	 *
	 * @return string
	 */
	public static function build_german_market_addon_option_key( string $provider_id ) : string {

		return 'wgm_add_on_' . strtolower( substr( __CLASS__, strlen( __NAMESPACE__ ) + 1 ) . '_' . $provider_id );
	}

	/**
	 * Register the global stylesheets.
	 *
	 * @Wp-hook wp_enqueue_scripts
	 * @Wp-hook admin_enqueue_scripts
	 *
	 * @static
	 *
	 * @return void
	 */
	public static function enqueue_styles() {

		wp_enqueue_style( 'wgm-woocommerce-shipping', WGM_SHIPPING_URL . '/assets/css/global' . WGM_SHIPPING_MINIFY . '.css', array(), WGM_SHIPPING_VERSION );
	}

	/**
	 * Hide flat rates shipping methods if free shipping is available.
	 *
	 * @hook woocommerce_package_rates
	 *
	 * @param array $rates shipping methods
	 * @param array $package
	 *
	 * @return array
	 */
	public function hide_shipping_provider_rates_when_free_is_available( array $rates, $package ) : array {

		if ( 'on' === get_option( 'wgm_dual_shipping_option', 'off' ) || ( true === apply_filters( 'wgm_shipping_hide_shipping_provider_rates_when_free_is_available', false ) ) ) {

			$free_shipping_is_available = false;
			$rate_identifier            = apply_filters( 'wgm_shipping_hide_flat_rates_shipping_if_free_available_rate_identifier', array() );

			if ( empty( $rate_identifier ) ) {
				return $rates;
			}

			do_action( 'wgm_shipping_before_check_free_shipping_available', $rates, $package, self::$free_shipping_method_ids );

			foreach ( $rates as $rate ) {
				if ( in_array( $rate->method_id, self::$free_shipping_method_ids ) ) {
					$free_shipping_is_available = true;
					break;
				}
			}

			if ( $free_shipping_is_available ) {

				$new_rates = $rates;

				foreach ( $rates as $key => $rate ) {
					if ( in_array( $rate->method_id, $rate_identifier ) && ( $rate->cost > 0 ) ) {
						unset( $new_rates[ $key ] );
					}
				}

				$rates = $new_rates;
			}
		}

		return $rates;
	}

	/**
	 * Public function to add a shipping method id to array of allowed free shipping methods.
	 *
	 * @param string $shipping_method_id
	 *
	 * @return void
	 */
	public static function add_free_shipping_method_id( $shipping_method_id ) {
		if ( ! in_array( $shipping_method_id, self::$free_shipping_method_ids ) ) {
			self::$free_shipping_method_ids[] = $shipping_method_id;
		}
	}

	/**
	 * Returns if a shipping provider is activated.
	 *
	 * @return bool
	 */
	public static function is_shipping_provider_activated() : bool {

		$providers    = self::get_available_provider_names();
		$is_activated = false;

		foreach ( $providers as $provider ) {
			if ( 'on' === get_option( 'wgm_add_on_woocommerce_shipping_' . $provider, 'off' ) ) {
				$is_activated = true;
				break;
			}
		}

		return $is_activated;
	}

}
