<?php
/**
 * Register All Metaboxes.
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

if ( ! class_exists( 'SA_CFW_COG_Meta' ) ) {

	/**
	 *  SA_CFW_COG_Meta Class.
	 *
	 * @return object of SA_CFW_COG_Meta having all functionality of SA_CFW_COG_Meta
	 */
	class SA_CFW_COG_Meta {

		/**
		 * Variable to hold instance of SA_CFW_COG_Meta
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Get single instance of SA_CFW_COG_Meta.
		 *
		 * @return SA_CFW_COG_Meta Singleton object of SA_CFW_COG_Meta
		 */
		public static function get_instance() {

			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 */
		private function __construct() {

			// WP Meta Boxes.
			add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );

			// WooCommerce Meta Boxes.
			add_action( 'woocommerce_product_options_general_product_data', array( $this, 'product_options' ) );
			add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'variable_product_options' ), 11, 3 );

			add_action( 'save_post_product', array( $this, 'save_product_meta' ) );
			add_action( 'woocommerce_save_product_variation', array( $this, 'save_variable_meta' ), 10, 2 );

			// COG Meta from Order Page.
			add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'hide_cog_meta_shop_order' ) );

			add_action( 'woocommerce_process_product_meta_variable', array( $this, 'validate_cog' ) );
		}

		/**
		 * Hook to hide order item meta in order page.
		 *
		 * @param array $meta All hidden meta of order page.
		 * @return array.
		 */
		public function hide_cog_meta_shop_order( $meta = array() ) {
			$meta[] = '_sa_cfw_total_cog';
			$meta[] = '_sa_cfw_total_profit';
			return $meta;
		}

		/**
		 * Method to validate cost of good price.
		 *
		 * @param int $post_id Id of the product.
		 * @return bool.
		 */
		public function validate_cog( $post_id ) {
			$product = wc_get_product( $post_id );
			if ( $product instanceof WC_Product ) {
				if (
					is_callable( array( $product, 'get_available_variations' ) ) &&
					is_callable( array( $product, 'get_meta' ) )
				) {
					// Enable Validation to check whether to show admin notice or not.
					$enable_validation = '';
					$loop              = 1;
					$variations        = $product->get_children();
					$parent_cog        = $product->get_meta( 'sa_cfw_cog_amount' );

					if ( ! empty( $parent_cog ) && is_scalar( $parent_cog ) ) {
						return true;
					}

					if ( ! empty( $variations ) ) {
						foreach ( $variations as $variation_id ) {
							$_variation = new WC_Product_Variation( $variation_id );
							if ( is_callable( array( $_variation, 'get_meta' ) ) ) {
								$cog               = $_variation->get_meta( 'sa_cfw_cog_amount' );
								$this_cog_validate = empty( $cog ) ? 0 : 1;
								if (
									1 < $loop &&
									$enable_validation !== $this_cog_validate &&
									empty( $parent_cog )
								) {
									sa_cfw_cog()->add_notice(
										'cog_required',
										__( 'You left to enter cost of good of some variation', 'cashier' ),
										'notice-warning'
									);
									return;
								}
								$enable_validation = empty( $cog ) ? 0 : 1;
								$loop++;
							}
						}
					}
				}
			}
		}

		/**
		 * Action to register meta boxes in WordPress admin.
		 */
		public function register_meta_boxes() {

			// Add Profit Meta Box in Order Page.
			add_meta_box( 'sa-cfw-cog-order', esc_html__( 'Profit table', 'cashier' ), array( $this, 'order_profit_box' ), 'shop_order', 'side' );

			// Add Profit Meta Box in Product Page.
			add_meta_box( 'sa-cfw-cog-product', esc_html__( 'Profit table', 'cashier' ), array( $this, 'product_profit_box' ), 'product', 'side' );
		}

		/**
		 * Output of Profit table in order page.
		 *
		 * @param WP_Post $post Post Object.
		 */
		public function order_profit_box( $post = null ) {
			if ( ! $post instanceof WP_Post ) {
				return false;
			}
			$order = wc_get_order( $post->ID );

			if ( is_callable( array( $order, 'get_meta' ) ) ) {
				$meta_table = array(
					array(
						'label' => esc_html__( 'Profit from order', 'cashier' ) . wp_kses_post( wc_help_tip( __( 'This is gross profit', 'cashier' ) ) ),
						'value' => wp_kses_post( wc_price( $order->get_meta( 'sa_cfw_total_profit' ) ) ),
					),
					array(
						'label' => esc_html__( 'Total COGs', 'cashier' ) . wp_kses_post( wc_help_tip( __( 'Total cost of goods of the order', 'cashier' ) ) ),
						'value' => wp_kses_post( wc_price( $order->get_meta( 'sa_cfw_total_cog' ) ) ),
					),
				);

				$this->render_meta_table( $meta_table );
			}
		}

		/**
		 * Output of Profit table in Product page.
		 *
		 * @param WP_Post $post Post Object.
		 */
		public function product_profit_box( $post = null ) {
			if ( ! $post instanceof WP_Post ) {
				return false;
			}

			$order_details = sa_cfw_cog()->get_order_details_by_product( $post->ID );

			$meta_table = array(
				array(
					'label' => esc_html__( 'Total items sold', 'cashier' ) . wp_kses_post( wc_help_tip( __( 'total items sold of the product', 'cashier' ) ) ),
					'value' => isset( $order_details['quantity'] ) ? esc_html( $order_details['quantity'] ) : 0,
				),
				array(
					'label' => esc_html__( 'Total profit', 'cashier' ) . wp_kses_post( wc_help_tip( __( 'This is gross profit', 'cashier' ) ) ),
					'value' => isset( $order_details['profit'] ) ? wp_kses_post( wc_price( $order_details['profit'] ) ) : wp_kses_post( wc_price( 0 ) ),
				),
			);

			$this->render_meta_table( $meta_table );
		}

		/**
		 * Add Inputs to Product General Tab.
		 */
		public function product_options() {
			?>
			<div class="options_group sa_cfw_cog">
				<?php
					woocommerce_wp_text_input(
						array(
							'id'                => 'sa_cfw_cog_amount',
							'class'             => 'sa-cfw-cog-amount',
							/* translators: %s: currency symbol */
							'label'             => sprintf( __( 'Cost of Good (%s)', 'cashier' ), get_woocommerce_currency_symbol() ),
							'type'              => 'number',
							'description'       => __( 'Cost of Good of the product. For variable product, this will be used if Cost of Good is not set for any of the variations', 'cashier' ),
							'desc_tip'          => true,
							'custom_attributes' => array(
								'step' => '1',
								'min'  => '0',
							),
						)
					);
				?>
			</div>
			<?php
		}

		/**
		 * COG fields for variation
		 *
		 * @param int     $loop           Position in the loop.
		 * @param array   $variation_data Variation data.
		 * @param WP_Post $variation      Post data.
		 */
		public function variable_product_options( $loop = 0, $variation_data = array(), $variation = null ) {
			$variation_id = ( ! empty( $variation->ID ) ) ? $variation->ID : 0;
			if ( empty( $variation_id ) ) {
				return;
			}

			$_variation = new WC_Product_Variation( $variation_id );

			if ( ! is_callable( array( $_variation, 'get_meta' ) ) ) {
				return;
			}
			?>
			<div class="options_group sa_cfw_cog">
				<?php
					woocommerce_wp_text_input(
						array(
							'id'                => "sa_cfw_cog_cost_{$loop}",
							'name'              => "sa_cfw_cog_variable_amount[{$loop}]",
							'class'             => 'sa-cfw-cog-amount',
							/* translators: %s: currency symbol */
							'label'             => sprintf( __( 'Cost of Good (%s)', 'cashier' ), get_woocommerce_currency_symbol() ),
							'type'              => 'number',
							'value'             => $_variation->get_meta( 'sa_cfw_cog_amount' ),
							'description'       => __( 'Cost of Good of the product' ),
							'wrapper_class'     => 'form-row form-row-full',
							'desc_tip'          => true,
							'custom_attributes' => array(
								'step' => '1',
								'min'  => '0',
							),
						)
					);
				?>
			</div>
			<?php
		}

		/**
		 * Function to save cog values in product meta
		 *
		 * @param int     $post_id The post id.
		 * @param WP_Post $post The post object.
		 */
		public function save_product_meta( $post_id = 0, $post = null ) {
			// Check the nonce.

			if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( wc_clean( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				return;
			}

			if ( empty( $post_id ) ) {
				return;
			}

			$product = wc_get_product( $post_id );

			$amount = ( isset( $_POST['sa_cfw_cog_amount'] ) ) ? sanitize_text_field( wp_unslash( $_POST['sa_cfw_cog_amount'] ) ) : false;

			if ( false !== $amount ) {
				if ( is_object( $product ) ) {
					if ( is_callable( array( $product, 'update_meta_data' ) ) ) {
						$product->update_meta_data( 'sa_cfw_cog_amount', floatval( $amount ) );
					}
					if ( is_callable( array( $product, 'save' ) ) ) {
						$product->save();
					}
				}
			}

		}


		/**
		 * Function for save cog value in each variation
		 *
		 * @param  int $variation_id Variation ID.
		 * @param  int $i            Loop ID.
		 */
		public function save_variable_meta( $variation_id = 0, $i = 0 ) {
			// Check the nonce.
			if ( empty( $_POST['security'] ) || ! wp_verify_nonce( wc_clean( wp_unslash( $_POST['security'] ) ), 'save-variations' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				return;
			}

			if ( empty( $variation_id ) ) {
				return;
			}

			$amount = ( isset( $_POST['sa_cfw_cog_variable_amount'][ $i ] ) ) ? sanitize_text_field( wp_unslash( $_POST['sa_cfw_cog_variable_amount'][ $i ] ) ) : false;

			if ( false !== $amount ) {
				$_variation = new WC_Product_Variation( $variation_id );
				if ( is_object( $_variation ) ) {
					if ( is_callable( array( $_variation, 'update_meta_data' ) ) ) {
						$_variation->update_meta_data( 'sa_cfw_cog_amount', floatval( $amount ) );
					}
					if ( is_callable( array( $_variation, 'save' ) ) ) {
						$_variation->save();
					}
				}
			}
		}

		/**
		 * Method to render table under meta box.
		 *
		 * @param  array $data Table data.
		 * @return void.
		 */
		public function render_meta_table( $data = array() ) {
			?>
			<table style="width: 100%;border-spacing: 0 10px;">
				<?php foreach ( $data as $tr ) : ?>
				<tr>
					<?php
					printf( '<th style="text-align:left">%s</th>', isset( $tr['label'] ) ? wp_kses_post( $tr['label'] ) : '' );
					printf( '<td>%s</td>', isset( $tr['value'] ) ? wp_kses_post( $tr['value'] ) : '' );
					?>
				</tr>
				<?php endforeach; ?>
			</table>
			<?php
		}
	}
}

SA_CFW_COG_Meta::get_instance();
