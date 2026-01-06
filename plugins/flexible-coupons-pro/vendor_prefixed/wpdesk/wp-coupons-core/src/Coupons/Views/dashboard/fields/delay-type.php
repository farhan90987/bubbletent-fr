<?php

namespace FlexibleCouponsProVendor;

/**
 * Custom fields template.
 *
 * This template can be used in simple product PDF coupon settings or variations.
 *
 * @var PostMeta $meta
 * @var array<string, mixed> $params
 */
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers\Links;
$params = isset($params) ? (array) $params : [];
$meta = $params['post_meta'];
$product_id = $params['post_id'];
$is_sending = $params['is_sending'];
$is_variation = $params['is_variation'];
$loop_id = isset($params['loop']) ? '_variation' . $params['loop'] : '';
$loop_name = isset($params['loop']) ? "_variation[{$params['loop']}]" : '';
$parent_id = isset($params['parent_id']) ? $params['parent_id'] : null;
$default = $is_variation ? '' : 'disabled';
$value = $meta->get_private($product_id, 'fc_sending_delay_type', $default);
$delay_options = [$is_variation ?? '' => '', 'disabled' => \esc_html__('Disabled', 'flexible-coupons-pro'), 'simple_delay' => \esc_html__('Simple Delay', 'flexible-coupons-pro'), 'fixed_date_delay' => \esc_html__('Send email on Fixed date', 'flexible-coupons-pro'), 'customer_date_delay' => \esc_html__('Send email on Customer defined date', 'flexible-coupons-pro')];
$custom_attributes = [];
if (!$is_sending) {
    $custom_attributes = ['disabled' => 'disabled'];
}
\woocommerce_wp_select(['id' => 'fc_sending_delay_type' . $loop_id, 'name' => 'fc_sending_delay_type' . $loop_name, 'value' => $value, 'label' => \esc_html__('Email Delay Type', 'flexible-coupons-pro'), 'desc_tip' => \true, 'options' => $delay_options, 'description' => \sprintf('%s <a href="%s" target="_blank">%s</a><br/>', \esc_html__('Select the type of email delay. If you do not want to delay it, you can deactivate this feature by selecting Disabled.', 'flexible-coupons-pro'), \esc_url(Links::get_fcs_doc_delay_type_link()), \esc_html__('Read about each type of delay in the documentation →', 'flexible-coupons-pro')), 'wrapper_class' => !$is_sending ? 'read-only' : '', 'class' => 'fcs-delay-type short', 'custom_attributes' => $custom_attributes]);
