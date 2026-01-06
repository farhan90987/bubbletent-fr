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
$loop_id = isset($params['loop']) ? '_variation' . $params['loop'] : '';
$loop_name = isset($params['loop']) ? "_variation[{$params['loop']}]" : '';
$parent_id = isset($params['parent_id']) ? $params['parent_id'] : null;
// Get the parent default meta value for variable.
$default = $meta->get_private($parent_id, 'flexible_coupon_coupon_code', 'no');
$value = $meta->get_private($prod_post_id, 'flexible_coupon_coupon_code', $default);
\woocommerce_wp_checkbox(['id' => "fc_coupon_code{$loop_id}", 'name' => "fc_coupon_code{$loop_name}", 'value' => $value, 'label' => \esc_html__('Define own coupon code', 'flexible-coupons-pro'), 'desc_tip' => \true, 'description' => \esc_html__('Enable this option to overwrite plugin settings and configure your own coupon code settings for this product.', 'flexible-coupons-pro'), 'wrapper_class' => 'coupon-code-settings', 'class' => 'fc_coupon_own_code checkbox', 'custom_attributes' => $custom_attributes]);
