<?php
/**
 * Class for WooCommerce Buy Now Product Admin fields
 *
 * @package     WooCommerce Buy Now
 *  author      StoreApps
 * @version     1.1.0
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SA_WC_Buy_Now_Product_Admin_Fields' ) ) {

	/**
	 * Class for WooCommerce Buy Now Product Admin Fields
	 */
	class SA_WC_Buy_Now_Product_Admin_Fields {

		/**
		 * Variable to hold instance of Buy Now Product Admin Fields
		 *
		 * @var $instance
		 * @since 2.4.1
		 */
		private static $instance = null;

		/**
		 * Constructor
		 */
		public function __construct() {

			add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_buy_now_product_data_tab' ) );

			add_action( 'woocommerce_product_data_panels', array( $this, 'buy_now_product_data_fields' ) );

			add_action( 'save_post', array( $this, 'save_buy_now_product_data' ), 10, 2 );

			add_action( 'admin_footer', array( $this, 'buy_now_product_data_style_and_script' ) );

			add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'register_textarea_for_buy_now_link' ), 15, 3 );
		}

		/**
		 * Get single instance of Buy Now Product Admin Fields
		 *
		 * @return SA_WC_Buy_Now_Product_Admin_Fields Singleton object of SA_WC_Buy_Now_Product_Admin_Fields
		 * @since 2.4.1
		 */
		public static function get_instance() {

			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Handle call to functions which is not available in this class
		 *
		 * @param string $function_name Function name.
		 * @param array  $arguments Array of arguments passed.
		 * @return mixed result of function call
		 */
		public function __call( $function_name, $arguments = array() ) {

			global $wc_buy_now;

			if ( ! is_callable( array( $wc_buy_now, $function_name ) ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( array( $wc_buy_now, $function_name ), $arguments );
			} else {
				return call_user_func( array( $wc_buy_now, $function_name ) );
			}

		}

		/**
		 * Register textarea to add buy now link.
		 *
		 * @param int     $loop Variation loop count.
		 * @param array   $variation_data Variation data.
		 * @param WP_Post $variation Post object.
		 */
		public function register_textarea_for_buy_now_link( $loop, $variation_data, $variation ) {
			woocommerce_wp_textarea_input(
				array(
					'id'                => 'sa_wc_buy_now_link_' . $variation->ID,
					'label'             => __( 'Buy Now Link', 'cashier' ),
					'desc_tip'          => true,
					'description'       => __( 'Copy this link and use it wherever you want.', 'cashier' ),
					'wrapper_class'     => 'form-row form-row-full',
					'custom_attributes' => array(
						'readonly' => 'readonly',
						'onClick'  => 'this.select()',
					),
				)
			);
		}

		/**
		 * Function to add tab for Buy Now fields under product data
		 *
		 * @param array $product_data_tabs Product data tabs.
		 * @return array $product_data_tabs Product data tabs including Buy Now tabs.
		 */
		public function add_buy_now_product_data_tab( $product_data_tabs = array() ) {

			$excluded_product_types = $this->get_excluded_product_types();

			$class = array();

			if ( count( $excluded_product_types ) > 0 ) {

				foreach ( $excluded_product_types as $product_type ) {
					$class[] = 'hide_if_' . $product_type; // hide buy now product tab if this product type is excluded.
				}
			}

			$product_data_tabs['wc-buy-now'] = array(
				'label'    => __( 'Buy Now', 'cashier' ),
				'target'   => 'wc_buy_now_product_data',
				'class'    => $class,
				'priority' => 20,
			);

			return $product_data_tabs;
		}

		/**
		 * Buy Now Product data fields
		 */
		public function buy_now_product_data_fields() {
			global $thepostid, $post;

			do_action( 'sa_wc_buy_now_enhanced_select_script_start' );

			$all_discount_types = wc_get_coupon_types();

			?>
			<div id="wc_buy_now_product_data" class="panel woocommerce_options_panel">
				<div class="options_group">
					<?php
					$bn_action = get_post_meta( $thepostid, 'sa_wc_bn_action', true );

					if ( empty( $bn_action ) ) {
						$bn_action = 'bn_default';
						update_post_meta( $thepostid, 'sa_wc_bn_action', $bn_action );
					}

					woocommerce_wp_radio(
						array(
							'id'      => 'sa_wc_bn_action',
							'label'   => __( 'Show', 'cashier' ),
							'value'   => $bn_action,
							'options' => array(
								'bn_default' => esc_html__( 'Default "Add to cart" button', 'cashier' ),
								'bn_only'    => esc_html__( 'Only "Buy Now" button', 'cashier' ),
								'bn_both'    => esc_html__( 'Both "Add to cart" & "Buy Now" buttons', 'cashier' ),
							),
						)
					);

					?>
					<p class="form-field">
						<label for="sa_wc_buy_now_coupon"><?php echo esc_html__( 'Apply coupons', 'cashier' ); ?></label>
						<select class="select2_search_products_coupons" style="width: 50%;" multiple="multiple" id="sa_wc_buy_now_coupon" name="sa_wc_buy_now_coupon[]" data-placeholder="<?php echo esc_attr__( 'Search for a coupon&hellip;', 'cashier' ); ?>" data-action="woocommerce_json_search_coupons" data-security="<?php echo esc_attr( wp_create_nonce( 'search-coupons' ) ); ?>" disabled="disabled" >
							<?php
								$coupon_titles = get_post_meta( $thepostid, 'sa_wc_buy_now_coupon', true );

							if ( ! empty( $coupon_titles ) ) {

								$coupon_titles = array_filter( array_map( 'trim', explode( ',', $coupon_titles ) ) );

								foreach ( $coupon_titles as $coupon_title ) {

									$coupon = new WC_Coupon( $coupon_title );

									$discount_type = $coupon->get_discount_type();

									if ( ! empty( $discount_type ) ) {
										/* translators: 1. Text 'Type', 2. Coupons discount type */
										$discount_type = sprintf( __( ' ( %1$s: %2$s )', 'cashier' ), __( 'Type', 'cashier' ), $all_discount_types[ $discount_type ] );
									}

									echo '<option value="' . esc_attr( $coupon_title ) . '"' . selected( true, true, false ) . '>' . esc_html( $coupon_title . $discount_type ) . '</option>';
								}
							}
							?>
						</select>
						<?php echo wc_help_tip( __( 'Clicking on "Buy Now" button will automatically apply selected coupons to cart.', 'cashier' ) ); // phpcs:ignore ?>
					</p>
					<?php
						$shipping_methods = (array) WC()->shipping->load_shipping_methods();
						$shipping_methods = wp_list_pluck( $shipping_methods, 'method_title', 'id' );
						$shipping_methods = array( '' => '' ) + $shipping_methods;
						woocommerce_wp_select(
							array(
								'id'                => 'sa_wc_buy_now_shipping_method',
								'label'             => __( 'Shipping method', 'cashier' ),
								'description'       => __( '"Buy Now" button will automatically select this shipping method.', 'cashier' ),
								'desc_tip'          => true,
								'class'             => 'wc-enhanced-select-nostd',
								'style'             => 'min-width: 50%;',
								'options'           => $shipping_methods,
								'custom_attributes' => array(
									'data-placeholder' => __( 'Select a shipping method...', 'cashier' ),
									'disabled'         => 'disabled',
								),
							)
						);

						$pages = (array) get_pages();
						$pages = wp_list_pluck( $pages, 'post_title', 'ID' );
						$pages = array( '' => '' ) + $pages;
						woocommerce_wp_select(
							array(
								'id'                => 'sa_wc_buy_now_redirect_page',
								'label'             => __( 'Redirect to page', 'cashier' ),
								'description'       => __( 'Customer will be redirected to this page when "Buy Now" button is clicked. Leave it blank to complete the checkout. Enter a URL to redirect after adding to cart.', 'cashier' ),
								'desc_tip'          => true,
								'class'             => 'wc-enhanced-select-nostd',
								'style'             => 'min-width: 50%;',
								'options'           => $pages,
								'custom_attributes' => array(
									'data-placeholder' => __( 'Select a page...', 'cashier' ),
									'disabled'         => 'disabled',
								),
							)
						);

						woocommerce_wp_textarea_input(
							array(
								'id'                => 'sa_wc_buy_now_link_' . $post->ID,
								'label'             => __( 'Buy Now link', 'cashier' ),
								'desc_tip'          => true,
								'description'       => __( 'Copy this link and use it in social media, email or any other place to promote this product.', 'cashier' ),
								'custom_attributes' => array(
									'readonly' => 'readonly',
									'onClick'  => 'this.select()',
								),
							)
						);
					?>
					<p class="bn-note-varition form-field"><strong><?php echo esc_html__( 'Note:', 'cashier' ); ?></strong>&nbsp;<?php echo esc_html__( 'Buy Now link is available under each individual variation.', 'cashier' ); ?></p>
				</div>
			</div>
			<?php

		}

		/**
		 * Function to save buy now data in product
		 *
		 * @param  integer $post_id The post id.
		 * @param  WP_Post $post    Post object.
		 */
		public function save_buy_now_product_data( $post_id = 0, $post = array() ) {

			if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) { // phpcs:ignore
				return;
			}
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}
			if ( is_int( wp_is_post_revision( $post ) ) ) {
				return;
			}
			if ( is_int( wp_is_post_autosave( $post ) ) ) {
				return;
			}
			if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) { // phpcs:ignore
				return;
			}
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
			if ( 'product' !== $post->post_type ) {
				return;
			}

			$product_types_to_exclude = $this->get_excluded_product_types();

			$post_product_type = ( ! empty( $_POST['product-type'] ) ) ? sanitize_text_field( wp_unslash( $_POST['product-type'] ) ) : ''; // phpcs:ignore

			if ( ! in_array( $post_product_type, $product_types_to_exclude, true ) ) {

				if ( ! empty( $_POST['sa_wc_bn_action'] ) ) {
					update_post_meta( $post_id, 'sa_wc_bn_action', wc_clean( wp_unslash( $_POST['sa_wc_bn_action'] ) ) ); // phpcs:ignore
				}

				if ( ! empty( $_POST['sa_wc_buy_now_coupon'] ) ) { // phpcs:ignore
					$coupon_titles = implode( ',', wc_clean( wp_unslash( $_POST['sa_wc_buy_now_coupon'] ) ) ); // phpcs:ignore
					update_post_meta( $post_id, 'sa_wc_buy_now_coupon', $coupon_titles );
				} else {
					update_post_meta( $post_id, 'sa_wc_buy_now_coupon', array() );
				}

				if ( ! empty( $_POST['sa_wc_buy_now_shipping_method'] ) ) { // phpcs:ignore
					update_post_meta( $post_id, 'sa_wc_buy_now_shipping_method', sanitize_text_field( wp_unslash( $_POST['sa_wc_buy_now_shipping_method'] ) ) ); // phpcs:ignore
				} else {
					update_post_meta( $post_id, 'sa_wc_buy_now_shipping_method', '' );
				}

				if ( ! empty( $_POST['sa_wc_buy_now_redirect_page'] ) ) { // phpcs:ignore
					update_post_meta( $post_id, 'sa_wc_buy_now_redirect_page', sanitize_text_field( wp_unslash( $_POST['sa_wc_buy_now_redirect_page'] ) ) ); // phpcs:ignore
				} else {
					update_post_meta( $post_id, 'sa_wc_buy_now_redirect_page', '' );
				}
			}

		}

		/**
		 * Additional style & script for Buy Now Product fields
		 */
		public function buy_now_product_data_style_and_script() {
			global $post;

			if ( empty( $post->post_type ) || 'product' !== $post->post_type ) {
				return;
			}

			if ( ! wp_script_is( 'jquery' ) ) {
				wp_enqueue_script( 'jquery' );
			}

			?>
			<style type="text/css">
				#woocommerce-product-data ul.wc-tabs li.wc-buy-now_options a:before {
					font-family: WooCommerce; content: '\e008';
				}
				#sa-wc-buy-now-link-container {
					padding: 0 10px;
				}
			</style>
			<?php
			do_action( 'sa_wc_buy_now_enhanced_select_script_end' );
		}

	}

}

SA_WC_Buy_Now_Product_Admin_Fields::get_instance();
