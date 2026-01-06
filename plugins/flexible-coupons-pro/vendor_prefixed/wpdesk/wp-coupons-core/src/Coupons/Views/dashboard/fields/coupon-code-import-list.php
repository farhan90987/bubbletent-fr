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
$is_code_import = $params['is_code_import'] ?? [];
$options = $params['options'];
$loop_id = isset($params['loop']) ? '_variation' . $params['loop'] : '';
$loop_name = isset($params['loop']) ? "_variation[{$params['loop']}]" : '';
$value = $meta->get_private($product_id, '_product_coupon_import_id', '');
$custom_attributes = [];
if (!$is_code_import) {
    $custom_attributes = ['disabled' => 'disabled'];
}
\woocommerce_wp_select(['id' => '_product_coupon_import_id' . $loop_id, 'name' => '_product_coupon_import_id' . $loop_name, 'value' => $value, 'label' => \esc_html__('Select Import', 'flexible-coupons-pro'), 'desc_tip' => \true, 'options' => $options, 'description' => \esc_html__('Select an import to associate coupon codes from that import with this product.', 'flexible-coupons-pro'), 'class' => 'short', 'custom_attributes' => $custom_attributes]);
