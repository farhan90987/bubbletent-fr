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
$default = 'dd-mm-yy';
$value = $meta->get_private($prod_post_id, 'fc_sending_delay_value', $default);
$custom_attributes = [];
if (!$is_sending) {
    $custom_attributes = ['disabled' => 'disabled'];
}
\woocommerce_wp_select(['id' => 'fc_sending_delay_value' . $loop_id, 'name' => 'fc_sending_delay_value' . $loop_name, 'value' => $value, 'label' => \esc_html__('Delay', 'flexible-coupons-pro'), 'desc_tip' => \true, 'options' => \array_combine(\range(1, 12), \range(1, 12)), 'wrapper_class' => !$is_sending ? 'read-only' : '', 'class' => 'show_if_simple_delay short fc_variation_base_on', 'custom_attributes' => $custom_attributes]);
