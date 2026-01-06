<?php

namespace FlexibleCouponsProVendor;

/**
 * Length field template.
 *
 * This template can be used in simple product PDF coupon settings or variations.
 */
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Settings;
$params = isset($params) ? (array) $params : [];
/**
 * @var PostMeta $meta
 */
$meta = $params['post_meta'];
$prod_post_id = $params['post_id'];
$is_premium = $params['is_premium'];
/**
 * @var Settings $settings
 */
$settings = $params['settings'];
$custom_attributes = \array_merge(['min' => 5, 'max' => 30], $params['custom_attributes']);
$loop_id = isset($params['loop']) ? '_variation' . $params['loop'] : '';
$loop_name = isset($params['loop']) ? "_variation[{$params['loop']}]" : '';
$parent_id = isset($params['parent_id']) ? $params['parent_id'] : null;
// Get the parent default meta value for variable.
$default = $meta->get_private($parent_id, 'flexible_coupon_coupon_code_length', $settings->get_fallback('coupon_code_random_length', 5));
$value = $meta->get_private($prod_post_id, 'flexible_coupon_coupon_code_length', $default);
\woocommerce_wp_text_input(['id' => 'fc_coupon_code_length' . $loop_id, 'name' => 'fc_coupon_code_length' . $loop_name, 'value' => $value, 'label' => \esc_html__('Number of random characters', 'flexible-coupons-pro'), 'type' => 'number', 'desc_tip' => \true, 'description' => \esc_html__('The number of random characters in the coupon code. Random characters will be used for generating unique coupon codes. Choose the number between 5 and 30.', 'flexible-coupons-pro'), 'wrapper_class' => !$is_premium ? 'read-only coupon-code-settings' : ' coupon-code-settings', 'custom_attributes' => $custom_attributes]);
