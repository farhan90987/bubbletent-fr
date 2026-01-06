<?php

namespace FlexibleCouponsProVendor;

/**
 * Product template field.
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
$product_templates = $params['product_templates'];
$loop_id = isset($params['loop']) ? '_variation' . $params['loop'] : '';
$loop_name = isset($params['loop']) ? "_variation[{$params['loop']}]" : '';
$parent_id = isset($params['parent_id']) ? $params['parent_id'] : null;
// Get the parent default meta value for variable.
$default = $meta->get_private($parent_id, 'flexible_coupon_product_template', '');
$value = $meta->get_private($prod_post_id, 'flexible_coupon_product_template', $default);
\woocommerce_wp_select(['id' => 'fc_product_template' . $loop_id, 'name' => 'fc_product_template' . $loop_name, 'value' => $value, 'label' => \esc_html__('Coupon template', 'flexible-coupons-pro'), 'desc_tip' => \true, 'options' => $product_templates, 'description' => \esc_html__('Select coupon template for this product', 'flexible-coupons-pro')]);
