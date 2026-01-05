<?php
/**
 * Main class for Cost of Goods
 *
 * @since       1.7.0
 *  author      StoreApps
 * @version     1.0.0
 *
 * @package     cashier/cost-of-goods
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SA_CFW_Cost_Of_Goods' ) ) {

	/**
	 *  Main Cost of Goods Class.
	 *
	 * @return object of SA_CFW_Cost_Of_Goods having all functionality of Cost of Goods
	 */
	class SA_CFW_Cost_Of_Goods {

		/**
		 * Variable to hold instance of Cost of Goods
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
		 * Get single instance of Cost of Goods.
		 *
		 * @return SA_CFW_Cost_Of_Goods Singleton object of SA_CFW_Cost_Of_Goods
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

			add_action( 'init', array( $this, 'init' ) );

			// Hook fires when a order status changed to completed.
			add_action( 'woocommerce_order_status_completed', array( $this, 'calculate_order_cog' ) );

			// Hook fires when order is refunded.
			add_action( 'woocommerce_order_refunded', array( $this, 'calculate_order_cog' ) );

			// Filter to register Cashier - COG' email classes.
			add_filter( 'woocommerce_email_classes', array( $this, 'register_email_classes' ) );

			add_action( 'admin_notices', array( $this, 'cfw_admin_notices' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

			add_action( 'woocommerce_order_status_processing', array( $this, 'send_mail_to_admin' ) );

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
		 * Init
		 */
		public function init() {
			$this->plugin_data = self::get_plugin_data();
			$this->includes();
		}

		/**
		 * Include Files.
		 */
		public function includes() {
			include_once 'class-sa-cfw-cog-meta.php';
		}


		/**
		 * Send mail to admin if an order is completed with loss.
		 *
		 * @param string|int $order_id Order id.
		 */
		public function send_mail_to_admin( $order_id = '' ) {
			if ( empty( $order_id ) ) {
				return;
			}
			$order  = wc_get_order( $order_id );
			$profit = $this->get_order_profit( $order );
			if ( 0 > floatval( $profit ) ) {
				$mailer = WC()->mailer();
				if ( $mailer->emails['SA_CFW_COG_Non_Profit_Email']->is_enabled() ) {
					// Trigger email.
					do_action(
						'sa_cfw_cog_non_profit_email',
						apply_filters(
							'sa_cfw_cog_non_profit_email_args',
							array(
								'order_id' => $order_id,
							)
						)
					);
				}
			}
		}

		/**
		 *  Register Cashier - COG's email classes to WooCommerce's emails class list
		 *
		 * @param array $email_classes available email classes list.
		 * @return array $email_classes modified email classes list
		 */
		public function register_email_classes( $email_classes = array() ) {

			include_once 'emails/class-sa-cfw-cog-non-profit-email.php';

			// Add the email class to the list of email classes that WooCommerce loads.
			$email_classes['SA_CFW_COG_Non_Profit_Email'] = new SA_CFW_COG_Non_Profit_Email();

			return $email_classes;
		}

		/**
		 * Get profit of an order.
		 *
		 * @param WC_Order|Object $order WC_Order instance.
		 *
		 * @return int|bool.
		 */
		public function get_order_profit( $order = '' ) {
			if ( ! $order instanceof WC_Order ) {
				return false;
			}
			// Total sell price for cog enabled products.
			$total_sell_price = 0;
			// Total profit only for cog enabled products.
			$profit = 0;
			if ( is_callable( array( $order, 'get_items' ) ) && ! empty( $order->get_items() ) ) {
				foreach ( $order->get_items() as $item_id => $item ) {
					if ( $item instanceof WC_Order_Item && is_callable( array( $item, 'get_product' ) ) ) {
						// Get Product Instance.
						$product = $item->get_product();

						if ( $product instanceof WC_Product && is_callable( array( $product, 'get_id' ) ) ) {
							$product_id       = $product->get_id();
							$item_quantity    = absint( $item->get_quantity() );
							$item_cog         = $this->get_cog_amount( $product_id );
							$item_total_price = $item_quantity * $item->get_total();
							$item_total_cog   = $item_quantity * $item_cog;

							if ( $item_cog ) {
								$profit += ( $item_total_price - $item_total_cog );
							}
						}
					}
				}
			}
			return $profit;
		}

		/**
		 * Calaculate the cost of goods of a order.
		 *
		 * @param  int|bool $order_id Order ID.
		 * @return void|bool     Retutn false if unable to calculate and update.
		 */
		public function calculate_order_cog( $order_id = -1 ) {

			if ( -1 === $order_id ) {
				return false;
			}

			$order = wc_get_order( $order_id );

			if ( ! $order instanceof WC_Order ) {
				return false;
			}

			if (
				! is_callable( array( $order, 'get_status' ) ) ||
				! is_callable( array( $order, 'get_items' ) ) ||
				! is_callable( array( $order, 'get_qty_refunded_for_item' ) ) ||
				! is_callable( array( $order, 'get_total_refunded_for_item' ) )
			) {
				return false;
			}

			$total_order_cog    = 0;
			$total_order_profit = 0;

			$is_refunded = 'refunded' === $order->get_status();

			if ( empty( $order->get_items() ) ) {
				return;
			}

			foreach ( $order->get_items() as $item_id => $item ) {

				if ( $is_refunded ) {
					$this->update_item_cog_cost( $item, 0, 0 );
					continue;
				}

				if (
					! is_callable( array( $item, 'get_product' ) ) ||
					! is_callable( array( $item, 'get_quantity' ) ) ||
					! is_callable( array( $item, 'get_total' ) )
				) {
					continue;
				}
				// Get Product Instance WC_Product.
				$product = $item->get_product();

				if ( ! $product instanceof WC_Product ) {
					continue;
				}

				if (
					! is_callable( array( $product, 'get_price' ) ) ||
					! is_callable( array( $product, 'get_type' ) )
				) {
					continue;
				}

				$cog = $product->get_meta( 'sa_cfw_cog_amount' );
				$cog = apply_filters( 'sa_cfw_cog_amount_for_item', $cog ? $cog : $product->get_price(), $item, $order );

				if ( $product->get_type() === 'variation' ) {
					$_variation = new WC_Product_Variation( $product->get_id() );
					$cog        = $_variation->get_meta( 'sa_cfw_cog_amount' );

					if ( ! $cog ) {
						$parent_product = wc_get_product( $product->get_parent_id() );
						$cog            = $parent_product->get_meta( 'sa_cfw_cog_amount' );
					}
				}

				$item_quantity = $item->get_quantity();
				$item_total    = $item->get_total();

				$refund_quantity = absint( $order->get_qty_refunded_for_item( $item_id ) );
				$refund_amount   = $order->get_total_refunded_for_item( $item_id );

				if ( $refund_quantity ) {
					$item_quantity -= $refund_quantity;
				}

				if ( $refund_amount ) {
					$item_total -= $refund_amount;
				}

				$total_item_cog    = $item_quantity * $cog;
				$total_item_profit = $item_total - $total_item_cog;

				$total_order_cog    += $total_item_cog;
				$total_order_profit += $total_item_profit;

				$this->update_item_cog_cost( $item, $total_item_profit, $total_item_cog );

			}

			$this->update_order_cog_cost( $order, $total_order_profit, $total_order_cog );

		}

		/**
		 * Function for update the costs to the Order Item.
		 *
		 * @param  WC_Order_Item_Product $item         Order Item Profit.
		 * @param  int|float             $total_profit Total Profit.
		 * @param  int                   $total_cog    Total COG.
		 */
		public function update_item_cog_cost( $item, $total_profit = 0, $total_cog = 0 ) {

			if ( ! $item instanceof WC_Order_Item_Product ) {
				return false;
			}
			if (
				! is_callable( array( $item, 'get_product' ) ) ||
				! is_callable( array( $item, 'get_id' ) )
			) {
				return false;
			}

			$product = $item->get_product();

			if (
				$product instanceof WC_Product &&
				is_callable( array( $product, 'get_meta' ) ) &&
				is_callable( array( $product, 'update_meta_data' ) ) &&
				is_callable( array( $product, 'save' ) )
			) {
				$item_id     = $item->get_id();
				$prev_profit = $product->get_meta( 'sa_cfw_total_profit' );
				$item_profit = ( ! empty( $prev_profit ) && is_array( $prev_profit ) ) ? $prev_profit : array();

				$item_profit[ $item_id ] = $total_profit;

				$product->update_meta_data( 'sa_cfw_total_profit', wc_clean( $item_profit ) );
				$product->save();
			}

			$item->update_meta_data( '_sa_cfw_total_cog', $total_cog );
			$item->update_meta_data( '_sa_cfw_total_profit', $total_profit );
			$item->save();
		}

		/**
		 * Function for update the costs to the Order.
		 *
		 * @param  WC_Order|WC_Order_Refund $order        Order Object.
		 * @param  int|float                $total_profit Total Profit.
		 * @param  int                      $total_cog    Total COG.
		 */
		public function update_order_cog_cost( $order, $total_profit = 0, $total_cog = 0 ) {

			if ( $order instanceof WC_Order || $order instanceof WC_Order_Refund ) {

				if (
					is_callable( array( $order, 'update_meta_data' ) ) &&
					is_callable( array( $order, 'save' ) )
				) {
					$order->update_meta_data( 'sa_cfw_total_profit', $total_profit );
					$order->update_meta_data( 'sa_cfw_total_cog', $total_cog );
					$order->save();
				}
			}
		}

		/**
		 * Function to get order details ( profit, quantity ) by Product id.
		 *
		 * @param  int $product_id Product id.
		 * @return array
		 */
		public function get_order_details_by_product( $product_id ) {

			$_product     = wc_get_product( $product_id );
			$total_profit = 0;

			if ( $_product->get_type() === 'variable' ) {
				$variations = $_product->get_children();
				if ( ! empty( $variations ) ) {
					foreach ( $variations as $variation_id ) {
						$_variation    = new WC_Product_Variation( $variation_id );
						$profits       = $_variation->get_meta( 'sa_cfw_total_profit' );
						$profit_amount = ! empty( $profits ) && is_array( $profits ) ? array_sum( $profits ) : 0;
						$total_profit += $profit_amount;
					}
				}
			} else {
				$profits       = $_product->get_meta( 'sa_cfw_total_profit' );
				$profit_amount = ! empty( $profits ) && is_array( $profits ) ? array_sum( $profits ) : 0;
				$total_profit += $profit_amount;
			}

			return array(
				'profit'   => $total_profit,
				'quantity' => (int) get_post_meta( $product_id, 'total_sales', true ),
			);
		}

		/**
		 * Callback Method to get cog amount from product meta.
		 *
		 * @param  Object $product Product object.
		 *
		 * @return int|bool.
		 */
		public function get_cog_amount_meta( $product = '' ) {

			if ( is_object( $product ) && is_callable( array( $product, 'get_meta' ) ) ) {
				$cog = $product->get_meta( 'sa_cfw_cog_amount' );
				return $cog ? $cog : false;
			}

			return false;
		}

		/**
		 * Callback Method to get cog amount from product meta.
		 *
		 * @param  string|int $product_id Product id.
		 *
		 * @return int|bool.
		 */
		public function get_cog_amount( $product_id = ' ' ) {

			$product = wc_get_product( $product_id );

			$cog = $this->get_cog_amount_meta( $product );
			if ( $cog ) {
				return $cog;
			}

			if ( is_callable( array( $product, 'get_type' ) ) && 'variation' === $product->get_type() ) {
				if ( is_callable( array( $product, 'get_parent_id' ) ) ) {
					$parent_id = $product->get_parent_id();
					// Parent product.
					$_product = wc_get_product( $parent_id );
					$cog      = $this->get_cog_amount_meta( $_product );
					if ( $cog ) {
						return $cog;
					}
				}
			}

			return false;
		}

		/**
		 * Callback Method to show plugin's admin notices.
		 *
		 * @return void
		 */
		public function cfw_admin_notices() {
			$notices = get_transient( 'cfw_cog_admin_notice' );
			if ( ! empty( $notices ) && is_array( $notices ) ) {
				foreach ( $notices as $notice ) {
					?>
					<div class="notice <?php echo ( $notice['type'] ) ? esc_attr( $notice['type'] ) : ''; ?> is-dismissible">
						<p><?php echo isset( $notice['message'] ) ? wp_kses_post( $notice['message'] ) : ''; ?></p>
					</div>
					<?php
				}
			}
			delete_transient( 'cfw_cog_admin_notice' );
		}

		/**
		 * Add notice to cashier's admin notice.
		 *
		 * @param string $name     Name of the notice.
		 * @param string $message  Notice Text.
		 * @param string $type     Notice Type.
		 *
		 * @return bool
		 */
		public function add_notice( $name = '', $message = '', $type = 'info' ) {
			if ( empty( $name ) || ! is_scalar( $name ) ) {
				return false;
			} else {
				$name = sanitize_text_field( $name );
			}
			$notices          = get_transient( 'cfw_cog_admin_notice' );
			$notices          = is_array( $notices ) ? $notices : array();
			$notices[ $name ] = array(
				'type'    => $type,
				'message' => $message,
			);
			return set_transient( 'cfw_cog_admin_notice', $notices );
		}

		/**
		 * Admin Enqueue Scripts
		 */
		public function admin_enqueue_scripts() {
			$suffix = SCRIPT_DEBUG ? '' : '.min';

			wp_register_style(
				'sa-cfw-cog-admin-style',
				plugin_dir_url( SA_COG_PLUGIN_DIRNAME ) . 'css/cfw-cog-admin' . $suffix . '.css',
				array(),
				$this->plugin_data['Version']
			);
			wp_register_script(
				'sa-cfw-cog-admin',
				plugin_dir_url( SA_COG_PLUGIN_DIRNAME ) . 'js/cfw-cog-admin' . $suffix . '.js',
				array( 'jquery' ),
				$this->plugin_data['Version'],
				true
			);

			$post_type   = get_current_screen()->post_type;
			$screen_base = get_current_screen()->base;

			if ( 'post' === $screen_base && ( 'shop_order' === $post_type || 'product' === $post_type ) ) {
				wp_enqueue_script( 'jquery-ui-tooltip' );
			}

			if ( 'post' === $screen_base && 'product' === $post_type ) {
				wp_enqueue_style( 'sa-cfw-cog-admin-style' );
				wp_enqueue_script( 'sa-cfw-cog-admin' );
			}
		}

		/**
		 * Function to get template base directory for Cashier's email templates
		 *
		 * @param  string $template_name Template name.
		 * @return string $template_base_dir Base directory for Cashier' email templates.
		 */
		public function get_template_base_dir( $template_name = '' ) {

			$template_base_dir = '';
			$plugin_base_dir   = substr( plugin_basename( SA_COG_PLUGIN_FILE ), 0, strpos( plugin_basename( SA_COG_PLUGIN_FILE ), '/' ) + 1 );
			$wc_sc_base_dir    = 'woocommerce/' . $plugin_base_dir;

			$template = locate_template(
				array(
					$wc_sc_base_dir . $template_name,
				)
			);

			if ( ! empty( $template ) ) {
				$template_base_dir = $wc_sc_base_dir;
			} else {
				$template = locate_template(
					array(
						$plugin_base_dir . $template_name,
					)
				);
				if ( ! empty( $template ) ) {
					$template_base_dir = $plugin_base_dir;
				}
			}

			$template_base_dir = apply_filters( 'sa_cfw_cog_template_base_dir', $template_base_dir, $template_name );

			return $template_base_dir;
		}

		/**
		 * Function to log messages generated by Cost of Goods plugin
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

SA_CFW_Cost_Of_Goods::get_instance();
