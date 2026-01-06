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
$value = $meta->get_private($prod_post_id, 'flexible_coupon_disable_pdf', 'no');
\woocommerce_wp_checkbox(['id' => "fc_disable_pdf{$loop_id}", 'name' => "fc_disable_pdf{$loop_name}", 'value' => $value, 'label' => \esc_html__('Disable the PDF coupon for this variation', 'flexible-coupons-pro'), 'desc_tip' => \true, 'description' => '', 'wrapper_class' => '', 'class' => 'fc_disable_pdf_coupon', 'custom_attributes' => $custom_attributes]);
