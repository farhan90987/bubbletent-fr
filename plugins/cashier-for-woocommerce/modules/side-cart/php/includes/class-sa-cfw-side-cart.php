<?php
/**
 * Main class for Side Cart
 *
 * @since       1.6.0
 *  author      StoreApps
 * @version     1.1.0
 *
 * @package     cashier/includes/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SA_CFW_Side_Cart' ) ) {

	/**
	 * Main Side Cart Class.
	 *
	 * @return object of SA_CFW_Side_Cart having all functionality of Side Cart
	 */
	class SA_CFW_Side_Cart {

		/**
		 * Variable to hold instance of Side Cart
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Cashier plugin data.
		 *
		 * @var array
		 */
		public $plugin_data = array();

		/**
		 * Get single instance of Side Cart.
		 *
		 * @return SA_CFW_Side_Cart Singleton object of SA_CFW_Side_Cart
		 */
		public static function get_instance() {

			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Cloning is forbidden.
		 */
		private function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'cashier' ), '1.0.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 */
		public function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'cashier' ), '1.0.0' );
		}

		/**
		 * Constructor
		 */
		private function __construct() {

			$this->includes();

			$this->plugin_data = self::get_plugin_data();

			// add_action( 'wp_footer', array( $this, 'add_side_cart' ) );

			// Filter to integrate cashier plugin template to WooCommerce template structure.
			add_filter( 'woocommerce_locate_template', array( $this, 'side_cart_get_template' ), 1, 3 );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		/**
		 * Function to handle WC compatibility related function call from appropriate class
		 *
		 * @param string $function_name Function to call.
		 * @param array  $arguments Array of arguments passed while calling $function_name.
		 * @return mixed Result of function call.
		 */
		public function __call( $function_name, $arguments = array() ) {

			if ( ! is_callable( 'SA_WC_Compatibility_4_1', $function_name ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( 'SA_WC_Compatibility_4_1::' . $function_name, $arguments );
			} else {
				return call_user_func( 'SA_WC_Compatibility_4_1::' . $function_name );
			}

		}

		/**
		 * Includes
		 */
		public function includes() {
			include_once 'class-sa-cfw-side-cart-ajax.php';
		}

		/**
		 * Add Side Cart Popup
		 */
		public function add_side_cart() {

			// Check if the current page is not checkout page.
			if ( ! is_checkout() ) {
				wc_get_template( 'side-cart.php', $this->template_args() );
			}
		}

		/**
		 * All arguments for sidecart templates
		 *
		 * @return Array
		 */
		public function template_args() {

			WC()->cart->calculate_totals();
			WC()->cart->maybe_set_cart_cookies();

			$template_args = array(
				'cart_items'   => $this->cart_items_args(),
				'cart_notice'  => $this->cart_notice_args(),
				'cart_totals'  => $this->get_totals(),
				'cart_coupons' => $this->coupon_args(),
				'item_count'   => WC()->cart->cart_contents_count,
				'allowed_html' => wp_kses_allowed_html( 'post' ),
			);
			return $template_args;
		}

		/**
		 * All arguments for cart templates
		 *
		 * @return Array
		 */
		public function cart_items_args() {

			$cart_args = array(
				'product_classes'  => 'cfw-sc-product product',
				'cart_items'       => empty( WC()->cart->get_cart() ) ? null : array_reverse( WC()->cart->get_cart() ),
				'empty_cart_text'  => esc_html__( 'No Item Found', 'cashier' ),
				'empty_cart_image' => '',
				'show_pimage'      => true,
				'show_pname'       => true,
				'show_pprice'      => true,
				'update_qty'       => 'yes',
				'sc_classes'       => 'cfw-sc-qty qty-input',
				'step'             => 0,
				'min_value'        => 1,
				'max_value'        => -1,
				'input_value'      => 1,
				'placeholder'      => '',
				'inputmode'        => 'numeric',
				'show_premove'     => true,
			);

			return apply_filters( 'cfw_sc_cart_args', $cart_args );
		}

		/**
		 * All arguments for Cart Notice
		 *
		 * @return Array
		 */
		public function cart_notice_args() {
			$args = array(
				'notices'      => WC()->session->get( 'wc_notices', array() ),
				'notice_types' => apply_filters( 'woocommerce_notice_types', array( 'error', 'success', 'notice' ) ),
			);

			return apply_filters( 'cfw_sc_cart_notice_args', $args );
		}

		/**
		 * All arguments for Coupon Section
		 *
		 * @return Array
		 */
		public function coupon_args() {
			$args = array(
				'show_coupon' => true,
				'coupons'     => WC()->cart->get_coupons(),
			);

			return apply_filters( 'cfw_sc_cart_coupon_args', $args );
		}

		/**
		 * Calculation of all totals for checkout
		 *
		 * @return Array
		 */
		public function get_totals() {
			$totals = array(
				array(
					'label' => esc_html__( 'Subtotal', 'cashier' ),
					'value' => WC()->cart->get_cart_subtotal(),
				),
				array(
					'label' => esc_html__( 'Shipping', 'cashier' ),
					'value' => wc_price( $this->get_total_shipping_cost() ),
				),
				array(
					'label' => esc_html__( 'Tax', 'cashier' ),
					'value' => wc_price( WC()->cart->get_taxes_total() ),
				),
				array(
					'label' => esc_html__( 'Discount', 'cashier' ),
					'value' => $this->get_total_discount() ? wc_price( '-' . $this->get_total_discount() ) : 0,
				),
				array(
					'label' => esc_html__( 'Total', 'cashier' ),
					'value' => WC()->cart->get_total(),
				),
			);
			return apply_filters( 'cfw_sc_cart_total_args', array( 'cart_totals' => $totals ) );
		}

		/**
		 * Calculation of Total Discount with adding all coupons
		 *
		 * @return Float
		 */
		public function get_total_discount() {
			$dis_price = 0;
			if ( ! WC()->cart->is_empty() ) {
				foreach ( WC()->cart->get_coupons() as $code => $coupon ) {
					$dis_price += WC()->cart->get_coupon_discount_amount( $coupon->get_code(), WC()->cart->display_cart_ex_tax );
				}
			}
			return $dis_price;
		}

		/**
		 * Calculation of Total Shipping
		 *
		 * @return Float
		 */
		public function get_total_shipping_cost() {

			$packages = WC()->shipping()->get_packages();
			foreach ( $packages as $i => $package ) {
				$chosen_method     = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
				$available_methods = $package['rates'];
				foreach ( $available_methods as $method ) {
					if ( $chosen_method === $method->id ) {
						return $this->shipping_method_cost_calculate( $method );
					}
				}
			}
		}

		/**
		 * Calculation of cost by single method
		 *
		 * @param  WC_Shipping_Rate $method Shipping method rate data.
		 * @return int|float
		 */
		public function shipping_method_cost_calculate( $method ) {
			$has_cost  = 0 < $method->cost;
			$hide_cost = ! $has_cost && in_array( $method->get_method_id(), array( 'free_shipping', 'local_pickup' ), true );
			$cost      = 0;
			if ( $has_cost && ! $hide_cost ) {
				if ( WC()->cart->display_prices_including_tax() ) {
					$cost = $method->cost + $method->get_shipping_tax();
				} else {
					$cost = $method->cost;
				}
			}

			return $cost;
		}

		/**
		 * Enqueue Scripts
		 */
		public function enqueue_scripts() {
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			wp_enqueue_style( 'sa-cfw-style', plugin_dir_url( SA_SC_PLUGIN_DIRNAME ) . 'css/cfw-sc' . $suffix . '.css', array(), $this->plugin_data['Version'] );

			wp_register_script( 'sa-cfw-sidecart', plugin_dir_url( SA_SC_PLUGIN_DIRNAME ) . 'js/side-cart' . $suffix . '.js', array( 'jquery' ), $this->plugin_data['Version'], true );
			$script_data = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => array(
					'cart_refresh' => wp_create_nonce( 'cfw-cart-refresh' ),
					'remove_cart'  => wp_create_nonce( 'cfw-remove-cart' ),
					'add_quantity' => wp_create_nonce( 'cfw-add-quantity' ),
					'coupon'       => wp_create_nonce( 'cfw-coupon' ),
					'add_to_cart'  => wp_create_nonce( 'cfw-add-to-cart' ),
				),
			);
			wp_localize_script( 'sa-cfw-sidecart', 'cfw_ajax_vars', $script_data );
			wp_enqueue_script( 'sa-cfw-sidecart' );
		}

		/**
		 * Get template files for Side Cart Popup
		 *
		 * @param  string $template Template Content.
		 * @param  string $template_name Template name.
		 * @param  string $template_path Current Template path.
		 */
		public function side_cart_get_template( $template, $template_name, $template_path ) {

			if ( ! $template_path ) {
				$template_path = WC()->template_url;
			}

			$default_path = untrailingslashit( plugin_dir_path( SA_SC_PLUGIN_FILE ) ) . '/templates/';

			$plugin_base_dir = substr( plugin_basename( SA_SC_PLUGIN_FILE ), 0, strpos( plugin_basename( SA_SC_PLUGIN_FILE ), '/' ) + 1 ) . 'sidecart/';

			// Look within passed path within the theme - this is priority.
			$_template = locate_template(
				array(
					'woocommerce/' . $plugin_base_dir . $template_name,
					$plugin_base_dir . $template_name,
					$template_name,
				)
			);

			if ( $_template ) {
				$template = $_template;
			}

			if ( ! $_template && file_exists( $default_path . $template_name ) ) {
				$template = $default_path . $template_name;
			}

			return $template;

		}

		/**
		 * Function to log messages generated by Side Cart plugin
		 *
		 * @param  string $level   Message type. Valid values: debug, info, notice, warning, error, critical, alert, emergency.
		 * @param  string $message The message to log.
		 */
		public function log( $level = 'notice', $message = '' ) {

			if ( empty( $message ) ) {
				return;
			}

			if ( defined( 'WC_PLUGIN_FILE' ) && ! empty( WC_PLUGIN_FILE ) ) {
				if ( function_exists( 'wc_get_logger' ) ) {
					$logger  = wc_get_logger();
					$context = array( 'source' => 'cashier' );
					$logger->log( $level, $message, $context );
				} elseif ( file_exists( plugin_dir_path( WC_PLUGIN_FILE ) . 'includes/class-wc-logger.php' ) ) {
					include_once plugin_dir_path( WC_PLUGIN_FILE ) . 'includes/class-wc-logger.php';
					$logger = new WC_Logger();
					$logger->add( 'cashier', $message );
				} else {
					error_log( 'cashier: ' . $message ); // phpcs:ignore
				}
			} else {
				error_log( 'cashier: ' . $message ); // phpcs:ignore
			}

		}

		/**
		 * Function to fetch plugin's data
		 */
		public static function get_plugin_data() {
			return get_plugin_data( SA_CFW_PLUGIN_FILE );
		}

	}

}

SA_CFW_Side_Cart::get_instance();
