<?php
  $wf_admin_img_path=WT_SEQUENCIAL_ORDNUMBER_URL . 'admin/images/';
  $seq_order_logo_url=WT_SEQUENCIAL_ORDNUMBER_URL.'assets/images/logo_seq.png';
?>
<style type="text/css">
.wt_seq_settings_left{
  float: left;
  width: 77%;
}
.wt_seq_settings_right{
  float: left;
  width: 23%;
}
.wt-seq-sidebar{
  background: #FFFFFF;
  border-radius: 7px;
  padding: 0;
}
.wt-seqpro-header{
  background: #FFFFFF;
  border-radius: 7px;
  padding: 8px;
  margin: 0;
}
.wt-seqpro-name{
  border-radius: 3px;
  margin: 0;
  padding: 16px;
  display: flex;
  align-items: center;
}
.wt-seqpro-name img{
  width: 36px;
  height: auto;
  box-shadow: 4px 4px 4px rgba(92, 98, 215, 0.23);
  border-radius: 7px;
}
.wt-seqpro-name h4{
  font-style: normal;
  font-weight: 600;
  font-size: 14px;
  line-height: 15px;
  margin: 0 0 0 12px;
  color: #5D63D9;
}
.wt-seqpro-mainfeatures{
  padding: 7px;
  background-color: #F4F4FF;
}
.wt-seqpro-mainfeatures ul{
  padding: 0;
  margin: 15px 25px 20px 25px;
}
.wt-seqpro-mainfeatures li{
  font-style: normal;
  font-weight: bold;
  font-size: 12px;
  line-height:16px;
  letter-spacing: -0.01em;
  list-style: none;
  position: relative;
  color: #091E80;
  padding-left: 28px;
}
.wt-seqpro-mainfeatures li.money-back:before{
  content: '';
  position: absolute;
  left: 0;
  height:24px ;
  width: 16px;
  background-image: url(<?php echo esc_url($wf_admin_img_path.'money-back.svg'); ?>);
  background-position: center;
  background-repeat: no-repeat;
  background-size: contain;
}
.wt-seqpro-mainfeatures li.support:before{
  content: '';
  position: absolute;
  left: 0;
  height:24px ;
  width: 16px;
  background-image: url(<?php echo esc_url($wf_admin_img_path.'support.svg'); ?>);
  background-position: center;
  background-repeat: no-repeat;
  background-size: contain;
}
.wt-seqpro-btn-wrapper{
  display: block;
  margin: 20px auto 20px;
  text-align: center;
}
.wt-seqpro-blue-btn{
  background: linear-gradient(90.67deg, #2608DF -34.86%, #3284FF 115.74%);
  box-shadow: 0px 4px 13px rgba(46, 80, 242, 0.39);
  border-radius: 5px;
  padding: 10px 15px 10px 38px;
  display: inline-block;
  font-style: normal;
  font-weight: bold;
  font-size: 14px;
  line-height: 18px;
  color: #FFFFFF;
  text-decoration: none;
  transition: all .2s ease;
  position: relative;
  border: none;
}
.wt-seqpro-blue-btn:before{
  content: '';
  position: absolute;
  height: 15px;
  width: 18px;
  background-image: url(<?php echo esc_url($wf_admin_img_path.'white-crown.svg'); ?>);
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  left: 15px;
}
.wt-seqpro-blue-btn:hover{
  box-shadow: 0px 4px 13px rgba(46, 80, 242, 0.5);
  text-decoration: none;
  transform: translateY(2px);
  transition: all .2s ease;
  color: #FFFFFF;
}
.wt-seqpro-features{
  padding: 10px 15px 25px 15px;
}
.wt-seqpro-features ul{
  padding: 0;
  margin: 0;
}
.wt-seqpro-features li{
  font-style: normal;
  font-weight: 500;
  font-size: 12px;
  line-height: 17px;
  color: #001A69;
  list-style: none;
  position: relative;
  padding-left: 49px;
  margin: 0 0 15px 0;
  display: flex;
  align-items: center;

}
.wt-seqpro-newfeat li:before{
  content: '';
  position: absolute;
  height: 39px;
  width: 39px;
  background-image: url(<?php echo esc_url($wf_admin_img_path.'new-badge.svg'); ?>);
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  left: 0;
}
.wt-seqpro-allfeat li:before{
  content: '';
  position: absolute;
  height: 18px;
  width: 18px;
  background-image: url(<?php echo esc_url($wf_admin_img_path.'tick.svg'); ?>);
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  left: 10px;
}
ul.wt-seqpro-newfeat li {
  margin-bottom: 30px;
}
.wt-seqpro-outline-btn{
  border: 1px solid #007FFF;
  background: #fff;
  border-radius: 5px;
  padding: 10px 15px 10px 38px;
  display: inline-block;
  font-style: normal;
  font-weight: bold;
  font-size: 14px;
  line-height: 18px;
  color: #007FFF;
  text-decoration: none;
  transition: all .2s ease;
  position: relative;
  background: transparent;
}
.wt-seqpro-outline-btn:before{
  content: '';
  position: absolute;
  height: 15px;
  width: 18px;
  background-image: url(<?php echo esc_url($wf_admin_img_path.'blue-crown.svg'); ?>);
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  left: 15px;
}
.wt-seqpro-outline-btn:hover{
  text-decoration: none;
  transform: translateY(2px);
  transition: all .2s ease;
  color: #007FFF;
}
p.submit{float: left; width: 100%;}
.bfcm_doc_settings {
        position: absolute; /* Allows positioning relative to the closest positioned ancestor */
        right: 20px; /* Adjust to position it on the right */
        z-index: 2; /* Ensure it appears above other content */
        border: none;
        background-color:##f4f9fc;
    }
</style>
<?php 
if(Wt_Advanced_Order_Number_Admin::wt_is_enable_rtl_support()) /* checks the current language need RTL support */
{
  ?>
  <style type="text/css">
    .wt_seq_order_settings_left,.wt_seq_order_settings_right{float: right !important;}
    .wfte_branding{float: left !important;}
  </style>
  
  <?php
}
?>
<div class="wt-seq-sidebar wt_gopro_block" style="margin-bottom: 1em;margin-top: 20px;">
<div class="wt-seq-sidebar wt_gopro_block">
<?php
if (Wtso_Bfcm_Twenty_Twenty_Four::is_bfcm_season()) { ?>
        <div class="bfcm_doc_settings">
            <img class="bfcm-coupon-img" src="<?php echo esc_url( plugins_url( 'images/bfcm-30-off-coupon.svg', dirname(__FILE__)) ); ?>" alt="30% Off Coupon">
        </div>
    <?php } ?>
  <div class="wt-seqpro-header">

    <div class="wt-seqpro-name">
      <img src="<?php echo esc_url($seq_order_logo_url); ?>" alt="featured img" width="36" height="36">
      <h4 class="wt-product-name"><?php echo esc_html__('Sequential Order Number for WooCommerce Pro','wt-woocommerce-sequential-order-numbers'); ?></h4>
    </div>
    <div class="wt-seqpro-mainfeatures">
      <ul>
        <li class="money-back"><?php echo esc_html__('30 Day Money Back Guarantee','wt-woocommerce-sequential-order-numbers'); ?></li>
        <li class="support"><?php echo esc_html__('Fast and Superior Support','wt-woocommerce-sequential-order-numbers'); ?></li>
      </ul>
    </div>
  </div>
  <div class="wt-seqpro-features">
    <ul class="wt-seqpro-newfeat">
      <li><?php echo esc_html__('Custom series for orders from a specific date/ID.','wt-woocommerce-sequential-order-numbers'); ?></li>
    </ul>
    <ul class="wt-seqpro-allfeat">
      <li><?php echo esc_html__('Auto reset sequence per month/year etc.','wt-woocommerce-sequential-order-numbers'); ?></li>
      <li><?php echo esc_html__('Add custom suffix for order numbers','wt-woocommerce-sequential-order-numbers'); ?></li>
      <li><?php echo esc_html__('Date suffix in order numbers.','wt-woocommerce-sequential-order-numbers'); ?></li>
      <li><?php echo esc_html__('Custom sequence for free orders.','wt-woocommerce-sequential-order-numbers'); ?></li>
      <li><?php echo esc_html__('Increment sequence in custom series.','wt-woocommerce-sequential-order-numbers'); ?></li>
      <li><?php echo esc_html__('More order number templates.','wt-woocommerce-sequential-order-numbers'); ?></li>
    </ul>
    <div class="wt-seqpro-btn-wrapper">
        <a href="https://woocommerce.com/products/webtoffee-sequential-order-numbers/" class="wt-seqpro-blue-btn" target="_blank"><?php echo esc_html__('UPGRADE TO PREMIUM','wt-woocommerce-sequential-order-numbers'); ?></a>
      </div>
  </div>
</div>
</div>
<script type="text/javascript">
  jQuery(document).ready(function($){
    //To add class name to submit button
    $( "p.submit" ).last().addClass("wt_seq_submit_btn");
    $('.wt_seq_goto_pro').insertAfter($('.wt_seq_submit_btn'));
  });  
</script>    