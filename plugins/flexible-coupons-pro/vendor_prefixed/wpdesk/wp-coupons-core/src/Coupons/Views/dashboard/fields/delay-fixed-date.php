<?php

namespace FlexibleCouponsProVendor;

/**
 * Custom fields template.
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
$is_sending = $params['is_sending'];
$loop_id = isset($params['loop']) ? '_variation' . $params['loop'] : '';
$loop_name = isset($params['loop']) ? "_variation[{$params['loop']}]" : '';
$parent_id = isset($params['parent_id']) ? $params['parent_id'] : null;
$default = '';
$value = $meta->get_private($prod_post_id, 'fc_sending_delay_fixed_date', $default);
$custom_attributes = [];
if (!$is_sending) {
    $custom_attributes = ['disabled' => 'disabled'];
}
\woocommerce_wp_text_input(['id' => 'fc_sending_delay_fixed_date' . $loop_id, 'name' => 'fc_sending_delay_fixed_date' . $loop_name, 'type' => 'datetime-local', 'value' => $value, 'label' => \esc_html__('Date', 'flexible-coupons-pro'), 'desc_tip' => \true, 'wrapper_class' => !$is_sending ? 'read-only coupon-code-settings' : 'coupon-code-settings', 'class' => 'show_if_fixed_date_delay short fc_variation_base_on', 'custom_attributes' => $custom_attributes]);
