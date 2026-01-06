<?php

namespace FlexibleCouponsProVendor;

/**
 * Custom fields template for import select.
 *
 * @var array<string, mixed> $params
 */
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
/**
 * @var PostMeta $meta
 * @var array<string, mixed> $params
 * @var array<string, string> $options
 * @var string $current_import_id
 */
$meta = $params['post_meta'];
$product_id = $params['post_id'];
$disabled = $params['disabled'];
$options = $params['options'];
$loop_id = isset($params['loop']) ? '_variation' . $params['loop'] : '';
$loop_name = isset($params['loop']) ? "_variation[{$params['loop']}]" : '';
$value = $meta->get_private($product_id, 'fc_product_email_template_id', '');
$custom_attributes = [];
if ($disabled) {
    $custom_attributes = ['disabled' => 'disabled'];
}
\woocommerce_wp_select(['id' => 'fc_product_email_template_id' . $loop_id, 'name' => 'fc_product_email_template_id' . $loop_name, 'value' => $value, 'label' => \esc_html__('Select Email Template', 'flexible-coupons-pro'), 'desc_tip' => \true, 'options' => $options, 'description' => \esc_html__('Select an email template to associate with this product.', 'flexible-coupons-pro'), 'class' => 'short', 'custom_attributes' => $custom_attributes]);
