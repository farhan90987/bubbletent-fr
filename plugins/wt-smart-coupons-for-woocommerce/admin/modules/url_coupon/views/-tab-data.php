<?php
/**
 * URL coupon tab data
 *
 * @link
 * @since 1.3.5
 *
 * @package  Wt_Smart_Coupon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wt_section_title">
	<h3><?php esc_html_e( 'URL coupon', 'wt-smart-coupons-for-woocommerce' ); ?></h3>
	<p><?php esc_html_e( 'The plugin auto generates a unique URL for all the coupons created in your store. Visiting the URL associated with a coupon will automatically redirect the users to the cart page by applying the coupon. You can embed a URL in a button, and your customer can click the button to apply the coupon.', 'wt-smart-coupons-for-woocommerce' ); ?></p>
	<p>
		<b><?php esc_html_e( 'Prerequisite:', 'wt-smart-coupons-for-woocommerce' ); ?> </b><?php esc_html_e( 'Ensure that you have created a coupon with the required configuration to use it as a URL coupon.', 'wt-smart-coupons-for-woocommerce' ); ?>
	</p>
	<p><b><?php esc_html_e( 'URL coupon format:', 'wt-smart-coupons-for-woocommerce' ); ?> {site_url}/?wt_coupon={coupon_code}</b> </p>
	
	<div style="background:#efefef; padding:5px 15px; color:#666">
		<p><?php esc_html_e( 'A sample URL coupon will be in the given format:', 'wt-smart-coupons-for-woocommerce' ); ?>, https://www.webtoffee.com/cart/?wt_coupon=flat30</p>
		<div>
			<?php esc_html_e( 'In the above example,', 'wt-smart-coupons-for-woocommerce' ); ?>
			<ul class="wt_sc_coupon_url_structure">
				<li>'https://www.webtoffee.com/cart/' <?php esc_html_e( 'corresponds to the site URL', 'wt-smart-coupons-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( "'?wt_coupon' refers to the URL coupon key", 'wt-smart-coupons-for-woocommerce' ); ?></li>
				<li><?php esc_html_e( "'flat30' is the coupon code", 'wt-smart-coupons-for-woocommerce' ); ?></li>
			</ul>
		</div>
	</div>
</div>

<?php
require plugin_dir_path( __FILE__ ) . '_upgrade_to_pro.php';
?>