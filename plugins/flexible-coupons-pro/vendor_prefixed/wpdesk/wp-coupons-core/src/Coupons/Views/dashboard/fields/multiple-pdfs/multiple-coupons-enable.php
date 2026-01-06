<?php

namespace FlexibleCouponsProVendor;

/**
 * Custom code field template.
 *
 * This template can be used in simple product PDF coupon settings or variations.
 */
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
$params = isset($params) ? (array) $params : [];
/**
 * @var PostMeta $meta
 */
$meta = $params['post_meta'];
$prod_post_id = $params['post_id'];
$is_premium = $params['is_premium'];
$custom_attributes = $params['custom_attributes'];
$is_multiple_pdfs = $params['is_multiple_pdfs'];
$loop_id = isset($params['loop']) ? '_variation' . $params['loop'] : '';
$loop_name = isset($params['loop']) ? "_variation[{$params['loop']}]" : '';
$parent_id = isset($params['parent_id']) ? $params['parent_id'] : null;
// Get the parent default meta value for variable.
$default = $meta->get_private($parent_id, 'fc_multiple_pdf_enable', 'no');
$value = $meta->get_private($prod_post_id, 'fc_multiple_pdf_enable', $default);
if (!$is_multiple_pdfs) {
    $custom_attributes = ['disabled' => 'disabled'];
}
\woocommerce_wp_checkbox(['id' => "fc_multiple_pdf_enable{$loop_id}", 'name' => "fc_multiple_pdf_enable{$loop_name}", 'value' => $value, 'label' => \esc_html__('Split coupons by product quantity', 'flexible-coupons-pro'), 'desc_tip' => \true, 'description' => \esc_html__('Enable this option to split coupons as different products in cart.', 'flexible-coupons-pro'), 'wrapper_class' => 'multiple-pdf-settings', 'class' => 'fc_multiple_pdf_enable checkbox', 'custom_attributes' => $custom_attributes]);
