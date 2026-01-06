<?php
/**
 * Smart coupon header
 *
 * @package    Wt_Smart_Coupon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wbte_sc_edit_header">
    <div class="wbte_sc_header_plugin_info">
        <img class="wbte_sc_header_plugin_logo" src="<?php echo esc_url( "{$admin_img_path}voucher_tag.svg" );?>" alt="<?php esc_html_e( 'Smart Coupon', 'wt-smart-coupons-for-woocommerce' ); ?>">
        <div class="wbte_sc_header_plugin_name">
            <?php esc_html_e( 'Smart Coupon', 'wt-smart-coupons-for-woocommerce' ) ?>
        </div>
    </div>
    <div class="wbte_sc_header_dev_by">
        <span><?php esc_html_e( 'Developed by', 'wt-smart-coupons-for-woocommerce' ) ?></span>
        <img class="wbte_sc_header_webtoffee_logo" src="<?php echo esc_url( "{$admin_img_path}wbte_logo.svg" );?>" alt="<?php esc_html_e( 'WebToffee', 'wt-smart-coupons-for-woocommerce' ); ?>">
    </div>
</div>