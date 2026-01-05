<?php
/**
 * Class for WooCommerce Buy Now Settings
 *
 * @package     WooCommerce Buy Now
 *  author      StoreApps
 * @version     1.1.0
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SA_WC_Buy_Now_Settings' ) ) {

	/**
	 * Class for WooCommerce Buy Now Settings
	 */
	class SA_WC_Buy_Now_Settings {

		/**
		 * Variable to hold instance of Buy Now Settings
		 *
		 * @var $instance
		 * @since 2.4.1
		 */
		private static $instance = null;

		/**
		 * Constructor
		 */
		private function __construct() {
			add_filter( 'woocommerce_get_sections_sa-cfw-settings', array( $this, 'add_bn_section' ), 1 );
			add_filter( 'woocommerce_get_settings_sa-cfw-settings', array( $this, 'get_settings' ) );
			add_action( 'woocommerce_admin_field_sa_bn_full_row_text', array( $this, 'sa_bn_full_row_text' ) );

			add_action( 'admin_head', array( $this, 'header_styles_and_scripts' ) );
			add_action( 'admin_footer', array( $this, 'footer_styles_and_scripts' ) );
		}

		/**
		 * Get single instance of Buy Now Settings.
		 *
		 * @return SA_WC_Buy_Now_Settings Singleton object of SA_WC_Buy_Now_Settings
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
		 * Add section for Buy Now settings
		 *
		 * @param array $sections Existing sections.
		 * @return array
		 */
		public function add_bn_section( $sections = array() ) {
			$bn_section = array(
				'' => __( 'Buy Now', 'cashier' ),
			);
			return array_merge( $sections, $bn_section );
		}

		/**
		 *  Funtion to get settting for storewide settings tab
		 */
		public function get_settings() {
			global $current_section;
			$buy_now_settings = array();
			if ( '' === $current_section ) {

				$term_id_to_name = get_terms(
					array(
						'taxonomy'   => 'product_cat',
						'hide_empty' => false,
						'fields'     => 'id=>name',
					)
				);

				$product_category_terms = get_terms(
					array(
						'taxonomy'   => 'product_cat',
						'hide_empty' => false,
						'fields'     => 'all',
					)
				);

				$categories = array();

				if ( ! empty( $product_category_terms ) ) {
					foreach ( $product_category_terms as $product_category_term ) {
						$categories[ $product_category_term->term_id ] = $product_category_term->name;
						if ( ! empty( $product_category_term->parent ) ) {
							$categories[ $product_category_term->term_id ] .= '&nbsp;&mdash;&nbsp;' . $term_id_to_name[ $product_category_term->parent ];
						}
					}
				}

				$all_discount_types = wc_get_coupon_types();

				$coupons = get_option( 'wc_buy_now_storewide_coupons', array() );
				foreach ( $coupons as $index => $code ) {
					$coupon        = new WC_Coupon( $code );
					$discount_type = $coupon->get_discount_type();
					if ( ! empty( $discount_type ) ) {
						/* translators: 1. Text 'Type', 2. Coupons discount type */
						$discount_type = sprintf( __( ' ( %1$s: %2$s )', 'cashier' ), __( 'Type', 'cashier' ), $all_discount_types[ $discount_type ] );
					}
					$coupons[ $code ] = $code . $discount_type;
					unset( $coupons[ $index ] );
				}

				$shipping_methods = (array) WC()->shipping->load_shipping_methods();
				$shipping_methods = wp_list_pluck( $shipping_methods, 'method_title', 'id' );
				$shipping_methods = array( '' => '' ) + $shipping_methods;

				$buy_now_settings = array(
					array(
						'name' => __( 'Buy Now', 'cashier' ),
						'type' => 'title',
						'id'   => 'wc_buy_now_option',
						'desc' => __( 'Configure direct checkout, one or two click purchase and other Buy Now options here.', 'cashier' ),
					),
					array(
						'name'        => __( 'Label for "Buy Now" buttons', 'cashier' ),
						'desc'        => __( 'Fancy calling it something other than Buy Now? Like 1-Step Checkout / Express Buy? You can change it here.', 'cashier' ),
						'desc_tip'    => true,
						'id'          => 'wc_buy_now_button_text',
						'type'        => 'text',
						'placeholder' => __( 'Buy Now', 'cashier' ),
					),
					array(
						'name'  => __( 'Add to Cart and Checkout setup', 'cashier' ),
						'type'  => 'sa_bn_full_row_text',
						'class' => 'sa_bn_storewide_action_label',
						'css'   => 'padding-left: 0; padding-bottom: 0.5em;',
						'desc'  => __( 'Select one of these options', 'cashier' ),
					),
					array(
						'name'    => ' ',
						'id'      => 'wc_buy_now_set_for',
						'class'   => 'wc_buy_now_set_for',
						'default' => 'standard',
						'type'    => 'radio',
						'options' => array(
							'buy-now' => __( 'Replace "Add to cart" with "Buy Now" for all products', 'cashier' ),
						),
					),
					array(
						'id'      => 'wc_buy_now_set_for',
						'class'   => 'wc_buy_now_set_for',
						'default' => 'standard',
						'type'    => 'radio',
						'options' => array(
							'and-buy-now' => __( 'Add a "Buy Now" button next to the "Add to cart" button', 'cashier' ),
						),
					),
					array(
						'name'              => __( 'For these product categories', 'cashier' ),
						'desc'              => __( '"Buy Now" button will be added on products from these categories. Leave it empty to display on all products.', 'cashier' ),
						'desc_tip'          => true,
						'id'                => 'wc_buy_now_product_categories',
						'type'              => 'multiselect',
						'class'             => 'wc-enhanced-select',
						'css'               => 'width: 400px;',
						'default'           => '',
						'options'           => $categories,
						'custom_attributes' => array(
							'data-placeholder' => __( 'All product categories', 'cashier' ),
						),
					),
					array(
						'name'  => __( 'Additional settings for these "Buy Now" buttons', 'cashier' ),
						'type'  => 'sa_bn_full_row_text',
						'class' => 'sa_bn_additional_button_action_label',
						'css'   => 'padding-left: 5em; padding-bottom: 0.5em;',
						'desc'  => __( 'These settings will be applied to the "Buy Now" buttons for product categories selected above.', 'cashier' ),
					),
					array(
						'name'              => __( 'Apply coupons', 'cashier' ),
						'desc'              => __( 'Clicking on "Buy Now" button will automatically apply selected coupons to cart.', 'cashier' ),
						'desc_tip'          => true,
						'id'                => 'wc_buy_now_storewide_coupons',
						'type'              => 'multiselect',
						'class'             => 'select2_search_products_coupons',
						'css'               => 'width: 400px;',
						'default'           => '',
						'options'           => $coupons,
						'custom_attributes' => array(
							'data-placeholder' => __( 'Search a coupon...', 'cashier' ),
							'data-action'      => 'woocommerce_json_search_coupons',
							'data-security'    => wp_create_nonce( 'search-coupons' ),
						),
					),
					array(
						'name'              => __( 'Shipping method', 'cashier' ),
						'desc'              => __( '"Buy Now" button will automatically select this shipping method.', 'cashier' ),
						'id'                => 'wc_buy_now_storewide_shipping_method',
						'default'           => '',
						'type'              => 'select',
						'class'             => 'wc-enhanced-select-nostd',
						'css'               => 'min-width: 350px;',
						'desc_tip'          => true,
						'options'           => $shipping_methods,
						'custom_attributes' => array(
							'data-placeholder' => __( 'Select a shipping method...', 'cashier' ),
						),
					),
					array(
						'name'     => __( 'Redirect to page', 'cashier' ),
						'desc'     => __( 'Customer will be redirected to this page when "Buy Now" button is clicked. Leave it blank to complete the checkout.', 'cashier' ),
						'id'       => 'wc_buy_now_storewide_redirect_to_page',
						'type'     => 'single_select_page',
						'default'  => '',
						'class'    => 'wc-enhanced-select-nostd',
						'css'      => 'min-width:300px;',
						'desc_tip' => true,
					),
					array(
						'id'      => 'wc_buy_now_set_for',
						'class'   => 'wc_buy_now_set_for',
						'default' => 'standard',
						'type'    => 'radio',
						'options' => array(
							'standard' => __( 'I will configure Buy Now options in each product where I want it', 'cashier' ),
						),
					),
					array(
						'id'      => 'wc_buy_now_set_for',
						'class'   => 'wc_buy_now_set_for',
						'default' => 'standard',
						'type'    => 'radio',
						'options' => array(
							'express-checkout' => __( 'Add "Express Checkout" button on the cart page', 'cashier' ),
						),
					),
					array(
						'name'        => __( 'Label for "Express Checkout" button', 'cashier' ),
						'id'          => 'wc_buy_now_express_checkout_button_text',
						'type'        => 'text',
						'placeholder' => __( 'Express Checkout', 'cashier' ),
					),
					array(
						'name'     => __( 'Preserve existing cart items on Buy Now?', 'cashier' ),
						'desc'     => __( 'Exclude existing products and checkout only with the product whose Buy Now link is clicked', 'cashier' ),
						'id'       => 'wc_buy_now_is_preserve_cart',
						'type'     => 'checkbox',
						'default'  => 'yes',
						'desc_tip' => __( 'Uncheck this to complete order with existing items in cart plus product for which Buy Now is used. Keep it checked to checkout exclusively with the product whose Buy Now button is clicked and restore the old cart after order completion.', 'cashier' ),
					),
					array(
						'name'     => __( 'Checkout in a popup', 'cashier' ),
						'desc'     => __( 'Show checkout form in a popup on the same page for quicker checkout', 'cashier' ),
						'id'       => 'wc_buy_now_is_quick_checkout',
						'type'     => 'checkbox',
						'default'  => 'yes',
						'desc_tip' => __( 'Keep this checked to display checkout form as a popup when the Buy Now button is clicked. This setting will be used only for guest users and for those users whose previous checkout details are not available.', 'cashier' ),
					),
					array(
						'name'     => __( '2-Step purchase?', 'cashier' ),
						'desc'     => __( 'Confirm before direct checkout', 'cashier' ),
						'id'       => 'wc_buy_now_is_two_click',
						'type'     => 'checkbox',
						'default'  => 'no',
						'desc_tip' => __( 'Select this if you want to show a confirmation message and complete the order only if the user confirms. Leave it unchecked to automatically complete payment using available information from the customer’s account.', 'cashier' ),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'wc_buy_now_option',
					),
				);

			}
			return apply_filters( 'sa_wc_buy_now_settings', $buy_now_settings, $this );
		}

		/**
		 * Funtion to display heading for Buy Now storewide actions
		 *
		 * @param array $value The values to create element.
		 */
		public function sa_bn_full_row_text( $value = array() ) {
			?>
			<tr valign="top">
				<td colspan="2" style="<?php echo ( ! empty( $value['css'] ) ) ? esc_attr( $value['css'] ) : ''; ?>" class="<?php echo ( ! empty( $value['class'] ) ) ? esc_attr( $value['class'] ) : ''; ?>">
					<label><?php echo ( ! empty( $value['name'] ) ) ? esc_html( $value['name'] ) : ''; ?> <?php echo wc_help_tip( esc_html( $value['desc'] ) ); // phpcs:ignore ?></label>
				</td>
			</tr>
			<?php
		}

		/**
		 * Header styles & scripts
		 */
		public function header_styles_and_scripts() {
			do_action( 'sa_wc_buy_now_enhanced_select_script_start' );
		}

		/**
		 * Footer styles & scripts
		 */
		public function footer_styles_and_scripts() {
			do_action( 'sa_wc_buy_now_enhanced_select_script_end' );
		}

	}
}

SA_WC_Buy_Now_Settings::get_instance();
