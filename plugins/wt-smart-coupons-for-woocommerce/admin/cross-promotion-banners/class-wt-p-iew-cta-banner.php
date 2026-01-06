<?php
/**
 * Class Wt_P_IEW_Cta_Banner
 *
 * This class is responsible for displaying the CTA banner on the product edit page.
 *
 * @package Wt_Smart_Coupon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'Wt_P_IEW_Cta_Banner' ) ) {
	/**
	 * Class Wt_P_IEW_Cta_Banner
	 */
	class Wt_P_IEW_Cta_Banner {
		/**
		 * Constructor.
		 */
		public function __construct() {
			// Check if premium plugin is active.
			/**
			 * Filter hook to alter active plugins.
			 *
			 * @since 2.2.2
			 * @param array $active_plugins Active plugins.
			 * @return array Active plugins.
			 */
			$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
			if (
				! in_array( 'wt-import-export-for-woo-product/wt-import-export-for-woo-product.php', $active_plugins, true ) &&
				! in_array( 'import-export-suite-for-woocommerce/import-export-suite-for-woocommerce.php', $active_plugins, true ) 
			) {
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
				add_action( 'wp_ajax_wt_dismiss_product_ie_cta_banner', array( $this, 'dismiss_banner' ) );
			}
		}
		/**
		 * Enqueue required scripts and styles.
		 *
		 * @param string $hook The current admin page.
		 */
		public function enqueue_scripts( $hook ) {
			if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || get_post_type() !== 'product' ) {
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
				'wt_product_ie_cta_banner_ajax',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'wt_dismiss_product_ie_cta_banner_nonce' ),
					'action'   => 'wt_dismiss_product_ie_cta_banner',
				)
			);
		}

		/**
		 * Add the meta box to the product edit screen.
		 */
		public function add_meta_box() {
			global $wpdb;

			$total_products = $wpdb->get_var(
				"
                SELECT COUNT(ID)
                FROM {$wpdb->posts}
                WHERE post_type = 'product'
                AND post_status NOT IN ('trash')
            "
			);

			// Show banner if there are 50 or more products.
			if ( ! defined( 'WT_PRODUCT_IMPORT_EXPORT_DISPLAY_BANNER' ) && 50 <= $total_products ) {
				add_meta_box(
					'wt_product_import_export_pro',
					__( 'Product Import Export for WooCommerce', 'wt-smart-coupons-for-woocommerce' ),
					array( $this, 'render_banner' ),
					'product',
					'side',
					'low'
				);
				define( 'WT_PRODUCT_IMPORT_EXPORT_DISPLAY_BANNER', true );
			}
		}

		/**
		 * Render the banner HTML.
		 */
		public function render_banner() {

			$plugin_url        = 'https://www.webtoffee.com/product/product-import-export-woocommerce/?utm_source=free_plugin_cross_promotion&utm_medium=add_new_product_tab&utm_campaign=Product_import_export';
			$wt_admin_img_path = plugin_dir_url( __FILE__ ) . 'assets/images';

			?>
			<style type="text/css">
				#wt_product_import_export_pro .postbox-header{  height: 80px; background: url( <?php echo esc_url( $wt_admin_img_path . '/product-ie.svg' ); ?> ) no-repeat 18px 18px #F9FCFF; padding-left: 65px; margin-bottom: 18px; background-size: 45px 45px; border-bottom: 1px dotted #CDE1F5; }
			</style>
			<div class="wt-cta-banner">
				<div class="wt-cta-content">

					<ul class="wt-cta-features">
						<li><?php esc_html_e( 'Import, export, or update WooCommerce products', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Supports all types of products (Simple, variable, subscription grouped, and external)', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Multiple file formats - CSV, XML, Excel, and TSV', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Advanced filters and customizations for better control', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li class="hidden-feature"><?php esc_html_e( 'Bulk update WooCommerce product data', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li class="hidden-feature"><?php esc_html_e( 'Import via FTP/SFTP and URL', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li class="hidden-feature"><?php esc_html_e( 'Schedule automated import & export', 'wt-smart-coupons-for-woocommerce' ); ?></li>
						<li class="hidden-feature"><?php esc_html_e( 'Export and Import custom fields and third-party plugin fields', 'wt-smart-coupons-for-woocommerce' ); ?></li>
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
		 * Handle the dismiss action via AJAX.
		 */
		public function dismiss_banner() {
			check_ajax_referer( 'wt_dismiss_product_ie_cta_banner_nonce', 'nonce' );

			// Check if user has permission.
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'Insufficient permissions' );
			}

			// Update the option to hide the banner.
			update_option( 'wt_hide_product_ie_cta_banner', true );

			wp_send_json_success( 'Banner dismissed successfully' );
		}
	}

	new Wt_P_IEW_Cta_Banner();
}
