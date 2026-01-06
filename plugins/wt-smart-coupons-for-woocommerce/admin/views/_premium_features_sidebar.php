<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
$custom_tick = '<img src="'. esc_url( WT_SMARTCOUPON_MAIN_URL ) .'admin/images/prem_crown.svg" alt="'. __( "Tick", "wt-smart-coupons-for-woocommerce" ) .'" style="margin-right:9.5px;">';
?>
<div class="wt_smart_coupon_pro_features">
    <div class="wt_smart_coupon_premium">
        <div class="wt_sc_upgrade_pro_main">
            <span><img src="<?php echo esc_url( WT_SMARTCOUPON_MAIN_URL . 'admin/images/upgrade_box_icon.svg' ); ?>"></span>
            <div class="wt_sc_upgrade_pro_main_hd"><?php _e( 'Make Irresistible Coupon Campaigns with Powerful Features', 'wt-smart-coupons-for-woocommerce' ); ?></div>
        </div>
        <div class="wt_sc_upgrade_pro_content">
            <h3 class="wt_sc_upgrade_pro_content_head"><?php _e( 'Smart Coupons for WooCommerce Pro', 'wt-smart-coupons-for-woocommerce' ); ?></h3>
            <ul class="ticked-list">
                <li><?php echo $custom_tick; ?></span><?php _e( 'Create advanced BOGO coupons', 'wt-smart-coupons-for-woocommerce' );?></li>
                <li><?php echo $custom_tick; ?></span><?php _e( 'Offer store credits and gift cards', 'wt-smart-coupons-for-woocommerce' );?></li>
                <li><?php echo $custom_tick; ?></span><?php _e( 'Set up smart giveaway campaigns', 'wt-smart-coupons-for-woocommerce' );?></li>
                <div class="wt-sc-pro-features-all-features">
                    <li><?php echo $custom_tick; ?></span><?php _e( 'Bulk generate coupons', 'wt-smart-coupons-for-woocommerce' );?></li>
                    <li><?php echo $custom_tick; ?></span><?php _e( 'Coupons to boost conversion rate:', 'wt-smart-coupons-for-woocommerce' );?></li>
                    <ul class="wt-sc-pro-features-all-features-bullet">
                        <li><?php _e('Purchase history-based coupons', 'wt-smart-coupons-for-woocommerce');?></li>
                        <li><?php _e('Sign up coupons', 'wt-smart-coupons-for-woocommerce');?></li>
                        <li><?php _e('Cart abandonment coupons', 'wt-smart-coupons-for-woocommerce');?></li>
                    </ul>
                    <li><?php echo $custom_tick; ?></span><?php _e( 'Create day-specific deals', 'wt-smart-coupons-for-woocommerce' );?></li>
                    <li><?php echo $custom_tick; ?></span><?php _e( 'Display coupon banners and widgets', 'wt-smart-coupons-for-woocommerce' );?></li>
                    <li><?php echo $custom_tick; ?></span><?php _e( 'Import coupons', 'wt-smart-coupons-for-woocommerce' );?></li>
                </div>
                <p class="wt-sc-pro-features-view-all"><?php _e( 'View all powerful options..', 'wt-smart-coupons-for-woocommerce' );?></p>
                <p class="wt-sc-pro-features-view-less"><?php _e( 'View less...', 'wt-smart-coupons-for-woocommerce' );?></p>
            </ul>
        </div>
        <div class="wt_sc_upgrade_pro_lower_green">
            <div class="wt_sc_upgrade_pro_button">
                <a style="background:#4750CB; font-size:16px; font-weight:500; border-radius:11px; line-height:58px; width:calc(100% - 32px); color:#fff; border:none; background-color:#4750CB;" class="button button-secondary" href="<?php echo esc_attr( $premium_url ) ; ?>" target="_blank"><?php esc_html_e( 'Unlock pro features', 'wt-smart-coupons-for-woocommerce' ); ?> <span class="dashicons dashicons-arrow-right-alt" style="line-height:58px;font-size:14px;"></span> </a>
            </div>
            <div class="wt_sc_upgrade_pro_icon_box" >
                <img src="<?php echo esc_url( WT_SMARTCOUPON_MAIN_URL . 'admin/images/prem_money.svg' );?>">
                <p><?php _e( '30 Day Money Back Guarantee', 'wt-smart-coupons-for-woocommerce' ); ?></p>
            </div>
            <div class="wt_sc_upgrade_pro_icon_box">
                <img src="<?php echo esc_url( WT_SMARTCOUPON_MAIN_URL . 'admin/images/prem_love.svg' );?>">
                <p><?php _e( '99% Customer Satisfaction rating', 'wt-smart-coupons-for-woocommerce' ); ?></p>
            </div>
        </div>
        

    </div>
</div>
<script>
    jQuery(document).ready(function ($) {
        $('.wt-sc-pro-features-view-all, .wt-sc-pro-features-view-less').on('click', function() {
            $('.wt-sc-pro-features-all-features').toggle();
            $('.wt-sc-pro-features-view-all, .wt-sc-pro-features-view-less').toggle();
        });
    });
</script>