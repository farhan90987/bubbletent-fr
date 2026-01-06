<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
/**
 *  @since 1.4.4
 */
?>
<div class="wt-sc-tab-content" data-id="<?php echo esc_attr($target_id);?>">
    <h3>
        <?php esc_html_e( 'Help links', 'wt-smart-coupons-for-woocommerce' ); ?>
    </h3> 
    <?php $admin_img_path = WT_SMARTCOUPON_MAIN_URL.'admin/images/'; ?>
    <ul class="wt-smartcoupon-help-links">
        <li>
            <img style="height: 90px;" src="<?php echo esc_url( "{$admin_img_path}documentation.png" );?>">
            <h3><?php esc_html_e( 'Documentation', 'wt-smart-coupons-for-woocommerce' ); ?></h3>
            <p><?php esc_html_e( 'Refer to our documentation to set up and get started', 'wt-smart-coupons-for-woocommerce' ); ?></p>
            <a target="_blank" href="<?php echo esc_url( 'https://www.webtoffee.com/smart-coupons-for-woocommerce-userguide/' ); ?>" class="wbte_sc_button wbte_sc_button-filled wbte_sc_button-medium">
                <?php esc_html_e( 'Documentation', 'wt-smart-coupons-for-woocommerce' ); ?>        
            </a>
        </li>
        <li>
            <img src="<?php echo esc_url( "{$admin_img_path}support.png" );?>">
            <h3><?php esc_html_e( 'Help and Support', 'wt-smart-coupons-for-woocommerce' ); ?></h3>
            <p><?php esc_html_e( 'We would love to help you on any queries or issues.', 'wt-smart-coupons-for-woocommerce' ); ?></p>
            <a target="_blank" href="<?php echo esc_url( 'https://www.webtoffee.com/support/' ); ?>" class="wbte_sc_button wbte_sc_button-filled wbte_sc_button-medium">
                <?php esc_html_e( 'Contact Us', 'wt-smart-coupons-for-woocommerce' ); ?>
            </a>
        </li>
    </ul>
</div>