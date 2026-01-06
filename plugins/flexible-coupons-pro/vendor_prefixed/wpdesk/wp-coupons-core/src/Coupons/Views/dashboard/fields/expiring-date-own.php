<?php

namespace FlexibleCouponsProVendor;

/**
 * Own date field template.
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
$default = $meta->get_private($parent_id, 'flexible_coupon_expiring_date_own', 30);
$value = $meta->get_private($prod_post_id, 'flexible_coupon_expiring_date_own', $default);
\woocommerce_wp_text_input(['id' => 'fc_expiring_date_own' . $loop_id, 'name' => 'fc_expiring_date_own' . $loop_name, 'value' => $value, 'label' => \esc_html__('Own expiration time', 'flexible-coupons-pro'), 'desc_tip' => \true, 'type' => 'number', 'description' => \esc_html__('Define own time from purchase to expiration of a generated coupon.', 'flexible-coupons-pro'), 'wrapper_class' => !$is_premium ? 'read-only expiring-date-own' : 'expiring-date-own', 'class' => 'expiring-date-select select short', 'custom_attributes' => ['min' => 1]]);
