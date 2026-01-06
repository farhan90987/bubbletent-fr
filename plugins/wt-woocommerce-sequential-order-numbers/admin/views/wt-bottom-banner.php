<div class="wt_seq_upgrade_to_pro_bottom_banner">
    <div class="wt_seq_upgrade_to_pro_bottom_banner_hd">
        <?php esc_html_e('Upgrade to Sequential Order Number for WooCommerce Pro to get hold of advanced features.', 'wt-woocommerce-sequential-order-numbers');?>
    </div>
    <a class="wt_seq_upgrade_to_pro_bottom_banner_btn" href="https://woocommerce.com/products/webtoffee-sequential-order-numbers/" target="_blank">
        <?php esc_html_e('UPGRADE TO PREMIUM', 'wt-woocommerce-sequential-order-numbers'); ?>
    </a>
    <div class="wt_seq_upgrade_to_pro_bottom_banner_feature_list_main">
        <?php
            foreach($pro_upgarde_features as $pro_upgarde_feature)
            { 
                ?>
                <div class="wt_seq_upgrade_to_pro_bottom_banner_feature_list">
                    <?php 
                    // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
                    echo esc_html_e($pro_upgarde_feature,'wt-woocommerce-sequential-order-numbers');
                    ?>
                </div>
                <?php
            }
        ?> 
    </div>
</div>
<style type="text/css">
    .wt_seq_upgrade_to_pro_bottom_banner{ float:left;  width:100%; box-sizing:border-box; padding:35px; color:#ffffff; height:auto; background:#35678b; margin-top:20px;}
    .wt_seq_upgrade_to_pro_bottom_banner_hd{ float:left; width:60%; border-left:solid 5px #feb439; font-size:20px; font-weight:bold; padding-left:10px; line-height:28px; margin-top:10px;}
    .wt_seq_upgrade_to_pro_bottom_banner_btn{ background:#0cc572; border-radius:5px; color:#fff; text-decoration:none; font-size:16px; font-weight:bold; float:left; padding:20px 15px; margin-left:10px; margin-top:10px; }
    .wt_pklist_upgrade_to_pro_bottom_banner_btn:hover{ color:#fff; }
    .wt_seq_upgrade_to_pro_bottom_banner_feature_list_main{ float:left; width:100%; margin-top:30px; }
    .wt_seq_upgrade_to_pro_bottom_banner_feature_list{ float:left; box-sizing:border-box; width:31%; margin-right:2%; padding:3px 0px 3px 20px; font-size:13px; color:#fff; background:url(<?php echo esc_url($tick); ?>) no-repeat left 5px; }
    @media screen and (max-width:768px) {
      .wt_seq_upgrade_to_pro_bottom_banner_feature_list{ width:100%; margin:auto; }
}
</style>