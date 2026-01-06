<?php

namespace FlexibleCouponsProVendor;

/**
 * Suffix field template.
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
$settings = $params['settings'];
/**
 * @var Settings $settings
 */
$custom_attributes = $params['custom_attributes'];
$loop_id = isset($params['loop']) ? '_variation' . $params['loop'] : '';
$loop_name = isset($params['loop']) ? "_variation[{$params['loop']}]" : '';
$parent_id = isset($params['parent_id']) ? $params['parent_id'] : null;
// Get the parent default meta value for variable.
$default = $meta->get_private($parent_id, 'flexible_coupon_coupon_code_suffix', $settings->get_fallback('coupon_code_suffix', ''));
$value = $meta->get_private($prod_post_id, 'flexible_coupon_coupon_code_suffix', $default);
\woocommerce_wp_text_input(['id' => 'fc_coupon_code_suffix' . $loop_id, 'name' => 'fc_coupon_code_suffix' . $loop_name, 'value' => $value, 'label' => \esc_html__('Coupon code suffix', 'flexible-coupons-pro'), 'desc_tip' => \true, 'description' => \esc_html__('Define the suffix which will be used as a end of your coupon code. Leave empty if you don’t want to use the suffix. Use {order_id} shortcode if you want to use the order number.', 'flexible-coupons-pro'), 'wrapper_class' => !$is_premium ? 'read-only coupon-code-settings' : 'coupon-code-settings', 'custom_attributes' => $custom_attributes]);
