<?php
/**
 * BOGO bulk container in bulk generate page
 *
 * @since 2.2.0
 * @package    Wt_Smart_Coupon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div id="wbte_sc_bulk_bogo_container">
    
    <div class="wbte_sc_admin_vrtl_nav">
        <div class="wbte_sc_admin_vrtl_nav_items">
            <div class="wbte_sc_admin_vrtl_nav_item active" data-section="general"><?php esc_html_e( 'General','wt-smart-coupons-for-woocommerce' ); ?></div>
            <div class="wbte_sc_admin_vrtl_nav_item" data-section="giveaway"><?php esc_html_e( 'Giveaway products','wt-smart-coupons-for-woocommerce' ); ?></div>
            <div class="wbte_sc_admin_vrtl_nav_item" data-section="trigger"><?php esc_html_e( 'Offer trigger','wt-smart-coupons-for-woocommerce' ); ?></div>
            <div class="wbte_sc_admin_vrtl_nav_item" data-section="limit"><?php esc_html_e( 'Limit','wt-smart-coupons-for-woocommerce' ); ?></div>
        </div>

        <div class="wbte_sc_admin_vrtl_nav_content">
            <div class="wbte_sc_admin_vrtl_nav_content_section active" data-section="general">
                <?php include plugin_dir_path( __FILE__ ) . '--bogo-bulk-general.php'; ?>
            </div>
            
            <div class="wbte_sc_admin_vrtl_nav_content_section" data-section="giveaway">
                <?php do_action( "wbte_sc_bogo_edit_step1_content" ); ?>
            </div>
            
            <div class="wbte_sc_admin_vrtl_nav_content_section" data-section="trigger">
                <?php do_action( "wbte_sc_bogo_edit_step2_content" ); ?>
            </div>
            
            <div class="wbte_sc_admin_vrtl_nav_content_section" data-section="limit">
                <p style="padding-left: 16px;"><?php esc_html_e( 'Apply offer','wt-smart-coupons-for-woocommerce' ); ?></p>
                <?php do_action( "wbte_sc_bogo_edit_step3_content" ); ?>
            </div>
        </div>
    </div>
</div>