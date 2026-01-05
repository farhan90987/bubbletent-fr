<?php
/**
 * Main class for Min Max
 *
 * @package     cashier/includes/
 *  author      StoreApps
 * @since       1.2.0
 * @version     1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SA_CFW_Min_Max' ) ) {

	/**
	 *  Main Min Max Class.
	 *
	 * @return object of SA_CFW_Min_Max having all functionality of Min Max
	 */
	class SA_CFW_Min_Max {

		/**
		 * Variable to hold instance of Min Max
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Get single instance of Min Max.
		 *
		 * @return SA_CFW_Min_Max Singleton object of SA_CFW_Min_Max
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
		 *
		 * @since 1.0.0
		 */
		private function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'cashier' ), '1.0.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0.0
		 */
		public function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'cashier' ), '1.0.0' );
		}

		/**
		 * Constructor
		 */
		private function __construct() {
			add_action( 'woocommerce_product_options_general_product_data', array( $this, 'product_options' ) );
			add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'variable_product_options' ), 11, 3 );

			add_action( 'woocommerce_process_product_meta', array( $this, 'process_product_meta' ), 10, 2 );
			add_action( 'woocommerce_save_product_variation', array( $this, 'process_variable_product_meta' ), 10, 2 );

			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'add_to_cart_validation' ), 1, 5 );
			add_filter( 'woocommerce_update_cart_validation', array( $this, 'update_cart_validation' ), 1, 4 );
			add_action( 'woocommerce_check_cart_items', array( $this, 'check_cart_items' ) );

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
		 * Product options
		 */
		public function product_options() {
			?>
			<div class="options_group sa_cfw_min_max show_if_simple">
				<?php
					woocommerce_wp_text_input(
						array(
							'id'                => 'sa_cfw_min_qty',
							'label'             => __( 'Minimum quantity', 'cashier' ),
							'type'              => 'number',
							'placeholder'       => __( '1', 'cashier' ),
							'description'       => __( 'Optional. If set, at least this much quantity of the product should be in the cart.', 'cashier' ),
							'desc_tip'          => true,
							'custom_attributes' => array(
								'step' => '1',
								'min'  => '1',
							),
						)
					);

					woocommerce_wp_text_input(
						array(
							'id'                => 'sa_cfw_max_qty',
							'label'             => __( 'Maximum quantity', 'cashier' ),
							'type'              => 'number',
							'placeholder'       => __( 'No limit', 'cashier' ),
							'description'       => __( 'Optional. If set, customers can buy this much quantity of the product at the maximum per order.', 'cashier' ),
							'desc_tip'          => true,
							'custom_attributes' => array(
								'step' => '1',
							),
						)
					);
				?>
			</div>
			<?php
		}

		/**
		 * Min Max fields for variation
		 *
		 * @param int     $loop           Position in the loop.
		 * @param array   $variation_data Variation data.
		 * @param WP_Post $variation Post data.
		 */
		public function variable_product_options( $loop = 0, $variation_data = array(), $variation = null ) {
			$variation_id = ( ! empty( $variation->ID ) ) ? $variation->ID : 0;
			if ( empty( $variation_id ) ) {
				return;
			}
			$min_qty = get_post_meta( $variation_id, 'sa_cfw_min_qty', true );
			$max_qty = get_post_meta( $variation_id, 'sa_cfw_max_qty', true );
			?>
			<div class="options_group sa_cfw_min_max show_if_variable">
				<?php
					woocommerce_wp_text_input(
						array(
							'id'                => "sa_cfw_min_qty_{$loop}",
							'name'              => "sa_cfw_min_qty[{$loop}]",
							'value'             => $min_qty,
							'label'             => __( 'Minimum quantity', 'cashier' ),
							'type'              => 'number',
							'placeholder'       => __( '1', 'cashier' ),
							'description'       => __( 'Optional. If set, at least this much quantity of the product should be in the cart.', 'cashier' ),
							'desc_tip'          => true,
							'wrapper_class'     => 'form-row form-row-first',
							'custom_attributes' => array(
								'step' => '1',
								'min'  => '1',
							),
						)
					);

					woocommerce_wp_text_input(
						array(
							'id'                => "sa_cfw_max_qty_{$loop}",
							'name'              => "sa_cfw_max_qty[{$loop}]",
							'value'             => $max_qty,
							'label'             => __( 'Maximum quantity', 'cashier' ),
							'type'              => 'number',
							'placeholder'       => __( 'No limit', 'cashier' ),
							'description'       => __( 'Optional. If set, customers can buy this much quantity of the product at the maximum per order.', 'cashier' ),
							'desc_tip'          => true,
							'wrapper_class'     => 'form-row form-row-last',
							'custom_attributes' => array(
								'step' => '1',
							),
						)
					);
				?>
			</div>
			<?php
		}

		/**
		 * Function to save min max details
		 *
		 * @param int    $post_id The post id.
		 * @param object $post The post object.
		 */
		public function process_product_meta( $post_id = 0, $post = null ) {

			// Check the nonce.
			if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( wc_clean( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				return;
			}

			if ( empty( $post_id ) ) {
				return;
			}

			$product_type = WC_Product_Factory::get_product_type( $post_id );

			if ( in_array( $product_type, array( 'variable', 'variable-subscription' ), true ) ) {
				return;
			}

			if ( isset( $_POST['sa_cfw_min_qty'] ) && is_scalar( $_POST['sa_cfw_min_qty'] ) ) { // phpcs:ignore
				update_post_meta( $post_id, 'sa_cfw_min_qty', wc_clean( wp_unslash( $_POST['sa_cfw_min_qty'] ) ) ); // phpcs:ignore
			}
			if ( isset( $_POST['sa_cfw_max_qty'] ) && is_scalar( $_POST['sa_cfw_max_qty'] ) ) { // phpcs:ignore
				update_post_meta( $post_id, 'sa_cfw_max_qty', wc_clean( wp_unslash( $_POST['sa_cfw_max_qty'] ) ) ); // phpcs:ignore
			}

		}

		/**
		 * Function for saving min max details in product meta
		 *
		 * @param  integer $variation_id Variation ID.
		 * @param  integer $i Loop ID.
		 */
		public function process_variable_product_meta( $variation_id = 0, $i = 0 ) {

			// Check the nonce.
			if ( empty( $_POST['security'] ) || ! wp_verify_nonce( wc_clean( wp_unslash( $_POST['security'] ) ), 'save-variations' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				return;
			}

			if ( empty( $variation_id ) ) {
				return;
			}

			if ( isset( $_POST['sa_cfw_min_qty'][ $i ] ) ) { // phpcs:ignore
				update_post_meta( $variation_id, 'sa_cfw_min_qty', wc_clean( wp_unslash( $_POST['sa_cfw_min_qty'][ $i ] ) ) ); // phpcs:ignore
			}
			if ( isset( $_POST['sa_cfw_max_qty'][ $i ] ) ) { // phpcs:ignore
				update_post_meta( $variation_id, 'sa_cfw_max_qty', wc_clean( wp_unslash( $_POST['sa_cfw_max_qty'][ $i ] ) ) ); // phpcs:ignore
			}

		}

		/**
		 * Validating the quantity on add to cart action with the quantity of the same product available in the cart.
		 *
		 * @param boolean $passed Valid or not.
		 * @param integer $product_id The product id.
		 * @param integer $quantity The quantity of the product to be added.
		 * @param integer $variation_id The variations id.
		 * @param array   $variations Additional variation data.
		 * @return boolean
		 */
		public function add_to_cart_validation( $passed = true, $product_id = 0, $quantity = 0, $variation_id = 0, $variations = array() ) {

			if ( false === $passed ) {
				return $passed;
			}

			$the_id = ( $variation_id > 0 ) ? absint( $variation_id ) : absint( $product_id );

			$product = wc_get_product( $the_id );

			$product_name = ( is_object( $product ) && is_callable( array( $product, 'get_name' ) ) ) ? $product->get_name() : '';

			$min_qty = get_post_meta( $the_id, 'sa_cfw_min_qty', true );
			$max_qty = get_post_meta( $the_id, 'sa_cfw_max_qty', true );

			if ( empty( $min_qty ) && empty( $max_qty ) ) {
				return $passed;
			}

			$cart_item_quantities = $this->get_cart_item_quantities();

			$notices = array();

			if ( array_key_exists( $the_id, $cart_item_quantities ) ) {
				if ( ! empty( $min_qty ) && ( $quantity + $cart_item_quantities[ $the_id ] ) < $min_qty ) {
					/* translators: 1. Minimum quantity required 2. Product name 3. Cart link with label */
					$notices[] = sprintf( __( 'You should have a minimum of %1$d %2$s\'s in %3$s.', 'cashier' ), $min_qty, $product_name, '<a href="' . esc_url( wc_get_cart_url() ) . '"><strong><em>' . __( 'your cart', 'cashier' ) . '</em></strong></a>' );
					if ( ( $min_qty - $cart_item_quantities[ $the_id ] ) > 0 ) {
						/* translators: 1. Quantity the can be added 2. Product name */
						$notices[] = sprintf( __( 'Add %1$d more %2$s.', 'cashier' ), ( $min_qty - $cart_item_quantities[ $the_id ] ), $product_name );
					}
					$passed = false;
				}
				if ( ! empty( $max_qty ) && ( $quantity + $cart_item_quantities[ $the_id ] ) > $max_qty ) {
					/* translators: 1. Minimum quantity required 2. Product name 3. Cart link with label 4. Quantity in the cart */
					$notices[] = sprintf( __( 'You can have a maximum of %1$d %2$s\'s in %3$s. You already have %4$d.', 'cashier' ), $max_qty, $product_name, '<a href="' . esc_url( wc_get_cart_url() ) . '"><strong><em>' . __( 'your cart', 'cashier' ) . '</em></strong></a>', $cart_item_quantities[ $the_id ] );
					if ( ( $max_qty - $cart_item_quantities[ $the_id ] ) > 0 ) {
						/* translators: 1. Quantity the can be added */
						$notices[] = sprintf( __( 'You can add %d more.', 'cashier' ), ( $max_qty - $cart_item_quantities[ $the_id ] ) );
					}
					$passed = false;
				}
			} else {
				if ( ! empty( $min_qty ) && $quantity < $min_qty ) {
					/* translators: 1. Minimum quantity required 2. Product name 3. Cart link with label */
					$notices[] = sprintf( __( 'You should add a minimum of %1$d %2$s\'s in %3$s.', 'cashier' ), $min_qty, $product_name, '<a href="' . esc_url( wc_get_cart_url() ) . '"><strong><em>' . __( 'your cart', 'cashier' ) . '</em></strong></a>' );
					$passed    = false;
				}
				if ( ! empty( $max_qty ) && $quantity > $max_qty ) {
					/* translators: 1. Minimum quantity required 2. Product name 3. Cart link with label */
					$notices[] = sprintf( __( 'You can have a maximum of %1$d %2$s\'s in %3$s.', 'cashier' ), $max_qty, $product_name, '<a href="' . esc_url( wc_get_cart_url() ) . '"><strong><em>' . __( 'your cart', 'cashier' ) . '</em></strong></a>' );
					$passed    = false;
				}
			}

			if ( ! empty( $notices ) ) {
				wc_add_notice( implode( ' ', $notices ), 'error' );
			}

			return $passed;
		}

		/**
		 * Validating product quantity when cart is updated
		 *
		 * @param boolean $passed Valid or not.
		 * @param string  $cart_item_key The cart item key.
		 * @param array   $values The cart item.
		 * @param integer $quantity The quantity to be updated.
		 * @return boolean
		 */
		public function update_cart_validation( $passed = true, $cart_item_key = '', $values = array(), $quantity = 0 ) {

			if ( false === $passed ) {
				return $passed;
			}

			$product_id   = ( ! empty( $values['product_id'] ) ) ? $values['product_id'] : 0;
			$variation_id = ( ! empty( $values['variation_id'] ) ) ? $values['variation_id'] : 0;

			$the_id = ( $variation_id > 0 ) ? absint( $variation_id ) : absint( $product_id );

			$product = wc_get_product( $the_id );

			$product_name = ( is_object( $product ) && is_callable( array( $product, 'get_name' ) ) ) ? $product->get_name() : '';

			$min_qty = get_post_meta( $the_id, 'sa_cfw_min_qty', true );
			$max_qty = get_post_meta( $the_id, 'sa_cfw_max_qty', true );

			if ( empty( $min_qty ) && empty( $max_qty ) ) {
				return $passed;
			}

			$cart_item_quantities = $this->get_cart_item_quantities();

			$notices = array();

			if ( array_key_exists( $the_id, $cart_item_quantities ) ) {
				if ( ! empty( $min_qty ) && $quantity < $min_qty ) {
					/* translators: 1. Minimum quantity required 2. Product name 3. Cart link with label */
					$notices[] = sprintf( __( 'You should have a minimum of %1$d %2$s\'s in %3$s.', 'cashier' ), $min_qty, $product_name, '<a href="' . esc_url( wc_get_cart_url() ) . '"><strong><em>' . __( 'your cart', 'cashier' ) . '</em></strong></a>' );
					if ( ( $min_qty - $cart_item_quantities[ $the_id ] ) > 0 ) {
						/* translators: 1. Quantity the can be added 2. Product name */
						$notices[] = sprintf( __( 'Add %1$d more %2$s.', 'cashier' ), ( $min_qty - $cart_item_quantities[ $the_id ] ), $product_name );
					}
					$passed = false;
				}
				if ( ! empty( $max_qty ) && $quantity > $max_qty ) {
					/* translators: 1. Minimum quantity required 2. Product name 3. Cart link with label 4. Quantity in the cart */
					$notices[] = sprintf( __( 'You can have a maximum of %1$d %2$s\'s in %3$s. You already have %4$d.', 'cashier' ), $max_qty, $product_name, '<a href="' . esc_url( wc_get_cart_url() ) . '"><strong><em>' . __( 'your cart', 'cashier' ) . '</em></strong></a>', $cart_item_quantities[ $the_id ] );
					if ( ( $max_qty - $cart_item_quantities[ $the_id ] ) > 0 ) {
						/* translators: 1. Quantity the can be added */
						$notices[] = sprintf( __( 'You can add %d more.', 'cashier' ), ( $max_qty - $cart_item_quantities[ $the_id ] ) );
					}
					$passed = false;
				}
			} else {
				if ( ! empty( $min_qty ) && $quantity < $min_qty ) {
					/* translators: 1. Minimum quantity required 2. Product name 3. Cart link with label */
					$notices[] = sprintf( __( 'You should add a minimum of %1$d %2$s\'s in %3$s.', 'cashier' ), $min_qty, $product_name, '<a href="' . esc_url( wc_get_cart_url() ) . '"><strong><em>' . __( 'your cart', 'cashier' ) . '</em></strong></a>' );
					$passed    = false;
				}
				if ( ! empty( $max_qty ) && $quantity > $max_qty ) {
					/* translators: 1. Minimum quantity required 2. Product name 3. Cart link with label */
					$notices[] = sprintf( __( 'You can have a maximum of %1$d %2$s\'s in %3$s.', 'cashier' ), $max_qty, $product_name, '<a href="' . esc_url( wc_get_cart_url() ) . '"><strong><em>' . __( 'your cart', 'cashier' ) . '</em></strong></a>' );
					$passed    = false;
				}
			}

			if ( ! empty( $notices ) ) {
				wc_add_notice( implode( ' ', $notices ), 'error' );
			}

			return $passed;
		}

		/**
		 * Check cart items
		 *
		 * @return boolean
		 */
		public function check_cart_items() {
			$return = true;
			$cart   = ( isset( WC()->cart ) ) ? WC()->cart : '';

			if ( $cart instanceof WC_Cart ) {
				$cart_contents = WC()->cart->get_cart();
				$notices       = array();
				foreach ( $cart_contents as $cart_item_key => $cart_item ) {
					$product    = ( ! empty( $cart_item['data'] ) ) ? $cart_item['data'] : null;
					$product_id = ( is_object( $product ) && is_callable( array( $product, 'get_id' ) ) ) ? $product->get_id() : 0;
					if ( is_object( $product ) && is_callable( array( $product, 'get_meta' ) ) ) {
						$min_qty = $product->get_meta( 'sa_cfw_min_qty' );
						$max_qty = $product->get_meta( 'sa_cfw_max_qty' );
					} else {
						$min_qty = get_post_meta( $product_id, 'sa_cfw_min_qty', true );
						$max_qty = get_post_meta( $product_id, 'sa_cfw_max_qty', true );
					}
					if ( empty( $min_qty ) && empty( $max_qty ) ) {
						continue;
					}
					$quantity     = ( ! empty( $cart_item['quantity'] ) ) ? absint( $cart_item['quantity'] ) : 1;
					$product_name = ( is_object( $product ) && is_callable( array( $product, 'get_name' ) ) ) ? $product->get_name() : '';
					if ( ! empty( $min_qty ) && $quantity < $min_qty ) {
						/* translators: 1. Minimum quantity required 2. Product name 3. Cart link with label */
						$notices[] = sprintf( __( 'You should have a minimum of %1$d %2$s\'s in %3$s.', 'cashier' ), $min_qty, $product_name, '<a href="' . esc_url( wc_get_cart_url() ) . '"><strong><em>' . __( 'your cart', 'cashier' ) . '</em></strong></a>' );
					}
					if ( ! empty( $max_qty ) && $quantity > $max_qty ) {
						/* translators: 1. Minimum quantity required 2. Product name 3. Cart link with label */
						$notices[] = sprintf( __( 'You should have a maximum of %1$d %2$s\'s in %3$s.', 'cashier' ), $max_qty, $product_name, '<a href="' . esc_url( wc_get_cart_url() ) . '"><strong><em>' . __( 'your cart', 'cashier' ) . '</em></strong></a>' );
					}
				}
				if ( ! empty( $notices ) ) {
					wc_add_notice( implode( ' ', $notices ), 'error' );
					$return = false;
				}
			}
			return $return;
		}

		/**
		 * Get cart item quantities
		 *
		 * @return array
		 */
		public function get_cart_item_quantities() {
			$cart_item_quantities = array();

			$cart = ( isset( WC()->cart ) ) ? WC()->cart : '';

			if ( $cart instanceof WC_Cart ) {
				$cart_contents = WC()->cart->get_cart();
				foreach ( $cart_contents as $cart_item_key => $cart_item ) {
					$product    = ( ! empty( $cart_item['data'] ) ) ? $cart_item['data'] : null;
					$product_id = ( is_object( $product ) && is_callable( array( $product, 'get_id' ) ) ) ? $product->get_id() : 0;
					$quantity   = ( ! empty( $cart_item['quantity'] ) ) ? absint( $cart_item['quantity'] ) : 1;
					if ( empty( $cart_item_quantities[ $product_id ] ) ) {
						$cart_item_quantities[ $product_id ] = 0;
					}
					$cart_item_quantities[ $product_id ] += $quantity;
				}
			}

			return $cart_item_quantities;
		}

		/**
		 * Function to log messages generated by Min Max plugin
		 *
		 * @param  string $level   Message type. Valid values: debug, info, notice, warning, error, critical, alert, emergency.
		 * @param  string $message The message to log.
		 */
		public function log( $level = 'notice', $message = '' ) {

			if ( empty( $message ) ) {
				return;
			}

			if ( function_exists( 'wc_get_logger' ) ) {
				$logger  = wc_get_logger();
				$context = array( 'source' => 'cashier' );
				$logger->log( $level, $message, $context );
			} else {
				include_once plugin_dir_path( WC_PLUGIN_FILE ) . 'includes/class-wc-logger.php';
				$logger = new WC_Logger();
				$logger->add( 'cashier', $message );
			}

		}

		/**
		 * Function to fetch plugin's data
		 */
		public function get_plugin_data() {
			return get_plugin_data( SA_CFW_PLUGIN_FILE );
		}

	}

}

SA_CFW_Min_Max::get_instance();
