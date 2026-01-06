<?php

namespace FlexibleCouponsProVendor;

/**
 * Free shipping field template.
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
$loop_id = isset($params['loop']) ? '_variation' . $params['loop'] : '';
$loop_name = isset($params['loop']) ? "_variation[{$params['loop']}]" : '';
$parent_id = isset($params['parent_id']) ? $params['parent_id'] : null;
// Get the parent default meta value for variable.
$default = $meta->get_private($parent_id, 'flexible_coupon_product_free_shipping', 'no');
$value = $meta->get_private($prod_post_id, 'flexible_coupon_product_free_shipping', $default);
if (\wc_shipping_enabled()) {
    \woocommerce_wp_checkbox(['id' => 'fc_product_free_shipping' . $loop_id, 'name' => 'fc_product_free_shipping' . $loop_name, 'value' => $value, 'label' => \esc_html__('Allow free shipping', 'flexible-coupons-pro'), 'desc_tip' => \true, 'description' => \esc_html__('Check if you want the coupon to provide free shipping. The coupon requirement for free shipping must be included in the shipping method.', 'flexible-coupons-pro'), 'wrapper_class' => !$is_premium ? 'read-only' : '']);
}
