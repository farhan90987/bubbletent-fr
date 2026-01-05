<?php
/**
 * Ajax class for Side Cart
 *
 * @since       1.6.0
 *  author      StoreApps
 * @version     1.0.0
 *
 * @package     cashier/includes/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SA_CFW_Side_Cart_Ajax' ) ) {

	/**
	 *  Frontend Ajax Side Cart Class.
	 */
	class SA_CFW_Side_Cart_Ajax {

		/**
		 * Variable to hold instance of Side Cart Ajax
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Get single instance of Side Cart Ajax.
		 *
		 * @return SA_CFW_Side_Cart_Ajax Singleton object of SA_CFW_Side_Cart_Ajax
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
			add_action( 'wp_ajax_sc_cart_refreshed', array( $this, 'refresh_content' ) );
			add_action( 'wp_ajax_nopriv_sc_cart_refreshed', array( $this, 'refresh_content' ) );

			add_action( 'wp_ajax_sc_product_remove', array( $this, 'product_remove' ) );
			add_action( 'wp_ajax_nopriv_sc_product_remove', array( $this, 'product_remove' ) );

			add_action( 'wp_ajax_sc_product_update', array( $this, 'item_update' ) );
			add_action( 'wp_ajax_nopriv_sc_product_update', array( $this, 'item_update' ) );

			add_action( 'wp_ajax_sc_add_coupon', array( $this, 'apply_coupon' ) );
			add_action( 'wp_ajax_nopriv_sc_add_coupon', array( $this, 'apply_coupon' ) );

			add_action( 'wp_ajax_sc_woo_ajax_add_to_cart', array( $this, 'woo_ajax_add_to_cart' ) );
			add_action( 'wp_ajax_nopriv_sc_woo_ajax_add_to_cart', array( $this, 'woo_ajax_add_to_cart' ) );

		}

		/**
		 * Refreshed Cart Content
		 * called by Ajax: sc_cart_refreshed
		 */
		public function refresh_content() {

			// prevent processing requests external of the site.
			check_ajax_referer( 'cfw-cart-refresh', 'security' );

			ob_clean();
			return wp_send_json_success( $this->cart_content() );
		}

		/**
		 * Product Remove
		 * called by Ajax: sc_product_remove
		 */
		public function product_remove() {

			// prevent processing requests external of the site.
			check_ajax_referer( 'cfw-remove-cart', 'security' );

			$post = ( ! empty( $_POST ) ) ? wp_unslash( $_POST ) : array();

			$id = ! empty( $post['item_key'] ) ? sanitize_text_field( $post['item_key'] ) : '';
			ob_clean();

			if ( $id ) {
				WC()->cart->remove_cart_item( $id );
			}

			return wp_send_json_success( $this->cart_content() );
		}

		/**
		 * Product Item Update
		 * called by Ajax: sc_product_update
		 */
		public function item_update() {

			// prevent processing requests external of the site.
			check_ajax_referer( 'cfw-add-quantity', 'security' );

			$post = ( ! empty( $_POST ) ) ? wp_unslash( $_POST ) : array();

			$item_key = ! empty( $post['item_key'] ) ? sanitize_text_field( $post['item_key'] ) : '';
			$qty      = ! empty( $post['quantity'] ) ? sanitize_text_field( $post['quantity'] ) : 1;

			ob_clean();

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				if ( $item_key === $cart_item_key ) {
					WC()->cart->set_quantity( $item_key, $qty, true );
				}
			}

			return wp_send_json_success( $this->cart_content() );
		}

		/**
		 * Apply Coupon: Add, Remove
		 * called by Ajax: sc_apply_coupon
		 */
		public function apply_coupon() {

			// prevent processing requests external of the site.
			check_ajax_referer( 'cfw-coupon', 'security' );

			$post = ( ! empty( $_POST ) ) ? wp_unslash( $_POST ) : array();

			$coupon_code = ! empty( $post['coupon'] ) ? sanitize_text_field( $post['coupon'] ) : '';
			$action      = ! empty( $post['act'] ) ? sanitize_text_field( $post['act'] ) : '';

			if ( ! empty( $coupon_code ) ) {
				if ( 'add' === $action && ! WC()->cart->has_discount( $coupon_code ) ) {
					WC()->cart->apply_coupon( $coupon_code );
				}
				if ( 'remove' === $action && WC()->cart->has_discount( $coupon_code ) ) {
					WC()->cart->remove_coupon( $coupon_code );
				}
			}

			return wp_send_json_success( $this->cart_content() );
		}

		/**
		 * Add Product
		 * called by Ajax: sc_woo__ajax_add_to_cart
		 */
		public function woo_ajax_add_to_cart() {

			// prevent processing requests external of the site.
			check_ajax_referer( 'cfw-add-to-cart', 'security' );

			$post = ( ! empty( $_POST ) ) ? wp_unslash( $_POST ) : array();

			$product_id   = ! empty( $post['product_id'] ) ? absint( $post['product_id'] ) : 0;
			$quantity     = ! empty( $post['quantity'] ) ? wc_stock_amount( $post['quantity'] ) : 1;
			$variation_id = ! empty( $post['variation_id'] ) ? absint( $post['variation_id'] ) : 0;

			if ( WC()->cart->add_to_cart( $product_id, $quantity, $variation_id ) ) {
				do_action( 'cfw_sc_woo_ajax_added_to_cart', $product_id );
				WC_AJAX::get_refreshed_fragments();
			} else {
				$data = array(
					'error'       => true,
					'product_url' => apply_filters( 'cfw_sc_woo_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
				);
				return wp_send_json_success( $data );
			}
			wp_die();
		}

		/**
		 * Refreshed Side Cart Contents
		 */
		public function cart_content() {
			ob_start();
			$cashier       = SA_CFW_Side_Cart::get_instance();
			$template_args = $cashier->template_args();
			?>
			<?php wc_get_template( 'cart-notice.php', $template_args['cart_notice'] ); ?>
			<?php wc_get_template( 'cart-contents.php', array_merge( $template_args['cart_items'], array( 'allowed_html' => $template_args['allowed_html'] ) ) ); ?>

			<div class="cart-meta">
				<?php wc_get_template( 'cart-coupons.php', array_merge( $template_args['cart_coupons'], array( 'allowed_html' => $template_args['allowed_html'] ) ) ); ?>
				<?php wc_get_template( 'cart-totals.php', array_merge( $template_args['cart_totals'], array( 'allowed_html' => $template_args['allowed_html'] ) ) ); ?>
			</div>
			<?php
			return array(
				'content' => ob_get_clean(),
				'total'   => WC()->cart->cart_contents_count,
			);
		}

	}

}

SA_CFW_Side_Cart_Ajax::get_instance();
