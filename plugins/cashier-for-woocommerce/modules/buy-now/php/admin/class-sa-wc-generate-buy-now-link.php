<?php
/**
 * Class for Generate Buy Now Link
 *
 * @package     WooCommerce Buy Now
 *  author      StoreApps
 * @version     1.1.0
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SA_WC_Generate_Buy_Now_Link' ) ) {

	/**
	 * Class for Generate Buy Now Link
	 */
	class SA_WC_Generate_Buy_Now_Link {

		/**
		 * Variable to hold instance of Generate Buy Now Link
		 *
		 * @var $instance
		 * @since 2.4.1
		 */
		private static $instance = null;

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'woocommerce_admin_field_bn_click_to_copy_button', array( $this, 'bn_click_to_copy_button' ) );
		}

		/**
		 * Get single instance of Generate Buy Now Link.
		 *
		 * @return SA_WC_Generate_Buy_Now_Link Singleton object of SA_WC_Generate_Buy_Now_Link
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
		 *  Funtion to display create buy now link settings
		 */
		public function render_bn_create_link_fields() {
			?>
			<h1><?php echo esc_html__( 'Cashier', 'cashier' ); ?></h1>
			<br>
			<div id="wc_buy_now_product_data">
				<?php woocommerce_admin_fields( $this->get_bn_create_link_setttings() ); ?>
			</div>
			<?php
		}

		/**
		 *  Funtion to get settings for create buy now link tab
		 */
		public function get_bn_create_link_setttings() {

			$shipping_methods = (array) WC()->shipping->load_shipping_methods();
			$shipping_methods = wp_list_pluck( $shipping_methods, 'method_title', 'id' );
			$shipping_methods = array( '' => '' ) + $shipping_methods;

			$pages = (array) get_pages();
			$pages = wp_list_pluck( $pages, 'post_title', 'ID' );
			$pages = array( '' => '' ) + $pages;

			$settings = array(
				array(
					'title' => __( 'Generate a Buy Now link', 'woocommerce' ),
					'type'  => 'title',
					'desc'  => __( 'Generate a special Buy Now link to share in blog posts, emails, social media promotions or elsewhere.', 'woocommerce' ),
					'id'    => 'wc_buy_now_create_link',
				),
				array(
					'name'              => __( 'Products to add to cart', 'cashier' ),
					'desc'              => __( 'Select one or more products that you would like ordered with this link.', 'cashier' ),
					'id'                => 'buy_now_generate_link_product',
					'type'              => 'multiselect',
					'options'           => array(),
					'class'             => 'select2_search_products_coupons',
					'custom_attributes' => array(
						'data-placeholder' => esc_attr__( 'Search for a product&hellip;', 'cashier' ),
						'data-action'      => 'wc_buy_now_json_search_products_and_variations',
						'data-security'    => esc_attr( wp_create_nonce( 'search-products' ) ),
					),
					'css'               => 'min-width:300px;',
					'desc_tip'          => true,
				),
				array(
					'name'              => __( 'Select coupons', 'cashier' ),
					'desc'              => __( 'Optionally, select one or more coupons that you want to apply with this link.', 'cashier' ),
					'id'                => 'buy_now_generate_link_coupon',
					'type'              => 'multiselect',
					'options'           => array(),
					'class'             => 'select2_search_products_coupons',
					'custom_attributes' => array(
						'data-placeholder' => esc_attr__( 'Search for a coupon&hellip;', 'cashier' ),
						'data-action'      => 'woocommerce_json_search_coupons',
						'data-security'    => esc_attr( wp_create_nonce( 'search-coupons' ) ),
					),
					'css'               => 'min-width:300px;',
					'desc_tip'          => true,
				),
				array(
					'name'              => __( 'Shipping method', 'cashier' ),
					'desc'              => __( 'Optionally, select shipping method for the order.', 'cashier' ),
					'id'                => 'buy_now_shipping_method',
					'type'              => 'select',
					'class'             => 'wc-enhanced-select-nostd',
					'options'           => $shipping_methods,
					'custom_attributes' => array(
						'data-placeholder' => esc_attr__( 'Select a shipping method&hellip;', 'cashier' ),
					),
					'css'               => 'min-width:300px;',
					'desc_tip'          => true,
				),
				array(
					'name'              => __( 'Redirect to page', 'cashier' ),
					'desc'              => __( 'Leave this blank to complete checkout as quickly as possible, or enter a URL to redirect to a page after the above items are added to cart.', 'cashier' ),
					'id'                => 'buy_now_redirect_link',
					'type'              => 'single_select_page',
					'class'             => 'wc-enhanced-select-nostd',
					'options'           => $pages,
					'custom_attributes' => array(
						'data-placeholder' => __( 'Select a page&hellip', 'cashier' ),
					),
					'css'               => 'min-width:300px;',
					'desc_tip'          => true,
				),
				array(
					'name'              => __( 'Here\'s your Buy Now link', 'cashier' ),
					'desc'              => __( 'Copy this link and use wherever you want.', 'cashier' ),
					'id'                => 'buy_now_generated_link',
					'type'              => 'textarea',
					'custom_attributes' => array(
						'readonly' => 'readonly',
					),
					'css'               => 'min-width:600px;',
					'desc_tip'          => true,
				),
				array(
					'type' => 'bn_click_to_copy_button',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'wc_buy_now_create_link',
				),
			);

			return $settings;
		}

		/**
		 *  Funtion to display click to copy button
		 */
		public function bn_click_to_copy_button() {
			?>
			<tr valign="top">
				<th scope="row" class="titledesc"></th>
				<td style="padding-top:0">
					<button class="button button-primary button-hero bn-click-to-copy-btn" id="bn-click-to-copy-btn" onclick="copy_to_clipboard()" data-clipboard-action="copy" data-clipboard-target="#buy_now_generated_link" disabled="disabled" ><?php echo esc_html__( 'Click to copy', 'cashier' ); ?></button>
				</td>
			</tr>
			<?php
		}
	}
}
