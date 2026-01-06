<?php

namespace FlexibleCouponsProVendor;

$params = isset($params) ? (array) $params : [];
$prod_post_id = (int) $params['post_id'];
$custom_attributes = $params['custom_attributes'];
$loop_id = isset($params['loop']) ? '_variation' . $params['loop'] : '';
$loop_name = isset($params['loop']) ? "_variation[{$params['loop']}]" : '';
$is_premium = $params['is_premium'];
$pro_url = $params['pro_url'];
echo '<div>';
if (!$is_premium) {
    echo '<p class="form-field marketing-content">';
    \printf(
        /* translators: %1$s: anchor opening tag, %2$s: anchor closing tag */
        \esc_html__('%1$sUpgrade to PRO →%2$s and enable options below', 'flexible-coupons-pro'),
        \sprintf('<a href="%s" target="_blank" class="pro-link">', \esc_url($pro_url) . '&utm_content=variations'),
        '</a>'
    );
    echo '</p>';
}
\woocommerce_wp_checkbox(['id' => "fc_variation_base_on{$loop_id}", 'name' => "fc_variation_base_on{$loop_name}", 'value' => \get_post_meta($prod_post_id, '_flexible_coupon_variation_base_on', \true), 'label' => \esc_html__('Manage Flexible PDF Coupons settings for variation', 'flexible-coupons-pro'), 'desc_tip' => \true, 'description' => \esc_html__('Enable individual Flexible PDF Coupons options at variation level', 'flexible-coupons-pro'), 'wrapper_class' => 'form-row form-row-full options show_if_pdf_coupon', 'class' => 'fc_variation_base_on', 'custom_attributes' => $custom_attributes]);
echo '</div>';
