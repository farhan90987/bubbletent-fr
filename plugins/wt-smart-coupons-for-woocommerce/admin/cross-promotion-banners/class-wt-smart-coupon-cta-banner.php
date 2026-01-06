<?php
/**
 * Smart Coupons for WooCommerce Pro CTA meta box.
 *
 * @link
 * @since 2.2.1
 *
 * @package  Wt_Smart_Coupon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Wt_Smart_Coupon_Cta_Banner
 *
 * @since  2.2.1
 */
if ( ! class_exists( 'Wt_Smart_Coupon_Cta_Banner' ) ) {

	/**
	 * Class Wt_Smart_Coupon_Cta_Banner
	 *
	 * @since 2.2.1
	 */
	class Wt_Smart_Coupon_Cta_Banner {
		/**
		 * Constructor.
		 *
		 * @since 2.2.1
		 */
		public function __construct() {
			/**
			 * Filter hook to get the active plugins.
			 *
			 * @since 2.2.1
			 */
			if ( ! in_array( 'wt-smart-coupon-pro/wt-smart-coupon-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true )
			) {
				add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_action( 'wp_ajax_wt_dismiss_smart_coupon_cta_banner', array( $this, 'dismiss_banner' ) );
			}
		}

		/**
		 * Enqueue styles.
		 *
		 * @since 2.2.1
		 *
		 * @param string $hook The current admin page.
		 */
		public function enqueue_scripts( $hook ) {
			if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || get_post_type() !== 'shop_coupon' ) {
				return;
			}

			wp_enqueue_style(
				'wt-wbte-cta-banner',
				plugin_dir_url( __FILE__ ) . 'assets/css/wbte-cross-promotion-banners.css',
				array(),
				Wbte_Cross_Promotion_Banners::get_banner_version(),
			);

			wp_enqueue_script(
				'wt-wbte-cta-banner',
				plugin_dir_url( __FILE__ ) . 'assets/js/wbte-cross-promotion-banners.js',
				array( 'jquery' ),
				Wbte_Cross_Promotion_Banners::get_banner_version(),
				true
			);

			// Localize script with AJAX data.
			wp_localize_script(
				'wt-wbte-cta-banner',
				'wt_smart_coupon_cta_banner_ajax',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'wt_dismiss_smart_coupon_cta_banner_nonce' ),
					'action'   => 'wt_dismiss_smart_coupon_cta_banner',
				)
			);
		}

		/**
		 * Add the meta box to the coupon edit screen
		 *
		 * @since 2.2.1
		 */
		public function add_meta_box() {
			if ( ! defined( 'WT_SMART_COUPON_DISPLAY_BANNER' ) ) {
				add_meta_box(
					'wbte-sc-upgrade-to-pro',
					__( 'Create better coupon campaigns with advanced WooCommerce coupon features', 'wt-smart-coupons-for-woocommerce' ),
					array( $this, 'render_banner' ),
					'shop_coupon',
					'side',
					'core'
				);
				define( 'WT_SMART_COUPON_DISPLAY_BANNER', true );
			}
		}

		/**
		 * Render the banner HTML.
		 *
		 * @since 2.2.1
		 */
		public function render_banner() {

			$plugin_url        = 'https://www.webtoffee.com/product/smart-coupons-for-woocommerce/?utm_source=free_plugin_cross_promotion&utm_medium=marketing_coupons_tab&utm_campaign=Smart_coupons';
			$wt_admin_img_path = plugin_dir_url( __FILE__ ) . 'assets/images';

			?>
			<style type="text/css">
				#wbte-sc-upgrade-to-pro .postbox-header{  height: 80px; background: url( <?php echo esc_url( WT_SMARTCOUPON_MAIN_URL . 'admin/images/upgrade_box_icon.svg' ); ?> ) no-repeat 18px 18px #F9FCFF; padding-left: 65px; margin-bottom: 18px; background-size: 45px 45px; border-bottom: 1px dotted #CDE1F5; }
			</style>
			
			<div class="wt-cta-banner">
				<div class="wt-cta-content">

					<div class="wt-cta-features-header">
						<h2 style="font-size: 13px; font-weight: 700; color: #4750CB;"><?php esc_html_e( 'Smart Coupons for WooCommerce Pro' ); ?></h2>
					</div>

					<ul class="wt-cta-features">
						<li><?php esc_html_e( 'Auto-apply coupons', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Create attractive Buy X Get Y (BOGO) offers', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Create product quantity/subtotal based discounts', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Offer store credits and gift cards', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Set up smart giveaway campaigns', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Set advanced coupon rules and conditions', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li class="hidden-feature"><?php esc_html_e( 'Bulk generate coupons', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li class="hidden-feature"><?php esc_html_e( 'Shipping, purchase history, and payment method-based coupons', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li class="hidden-feature"><?php esc_html_e( 'Sign up coupons', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li class="hidden-feature"><?php esc_html_e( 'Cart abandonment coupons', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li class="hidden-feature"><?php esc_html_e( 'Create day-specific deals', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li class="hidden-feature"><?php esc_html_e( 'Display coupon banners and widgets', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li class="hidden-feature"><?php esc_html_e( 'Import coupons', 'wt-smart-coupons-for-woocommerce' ); ?></li>
					</ul>

					<div class="wt-cta-footer">
						<div class="wt-cta-footer-links">
							<a href="#" class="wt-cta-toggle" data-show-text="<?php esc_attr_e( 'View all premium features', 'wt-smart-coupons-for-woocommerce' ); ?>" data-hide-text="<?php esc_attr_e( 'Show less', 'wt-smart-coupons-for-woocommerce' ); ?>"><?php esc_html_e( 'View all premium features', 'wt-smart-coupons-for-woocommerce' ); ?></a>
							<a href="<?php echo esc_url( $plugin_url ); ?>" class="wt-cta-button" target="_blank"><img src="<?php echo esc_url( $wt_admin_img_path . '/promote_crown.svg' ); ?>" style="width: 15.01px; height: 10.08px; margin-right: 8px;"><?php esc_html_e( 'Get the plugin', 'wt-smart-coupons-for-woocommerce' ); ?></a>
						</div>
						<a href="#" class="wt-cta-dismiss" style="display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none;"><?php esc_html_e( 'Dismiss', 'wt-smart-coupons-for-woocommerce' ); ?></a>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Handle the dismiss action via AJAX
		 *
		 * @since 2.2.1
		 */
		public function dismiss_banner() {
			check_ajax_referer( 'wt_dismiss_smart_coupon_cta_banner_nonce', 'nonce' );

			// Check if user has permission.
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'Insufficient permissions', 'wt-smart-coupons-for-woocommerce' ) );
			}

			// Update the option to hide the banner.
			update_option( 'wt_hide_smart_coupon_cta_banner', true );

			wp_send_json_success();
		}
	}

	new Wt_Smart_Coupon_Cta_Banner();
}
