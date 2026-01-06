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
$is_multiple_pdfs = $params['is_multiple_pdfs'];
$loop_id = isset($params['loop']) ? '_variation' . $params['loop'] : '';
$loop_name = isset($params['loop']) ? "_variation[{$params['loop']}]" : '';
$parent_id = isset($params['parent_id']) ? $params['parent_id'] : null;
// Get the parent default meta value for variable.
$default = $meta->get_private($parent_id, 'fc_multiple_pdf_forms_limit', '');
$value = $meta->get_private($prod_post_id, 'fc_multiple_pdf_forms_limit', $default);
$custom_attributes = ['min' => 0, 'step' => 1];
if (!$is_multiple_pdfs) {
    $custom_attributes['disabled'] = 'disabled';
}
\woocommerce_wp_text_input(['id' => "fc_multiple_pdf_forms_limit{$loop_id}", 'name' => "fc_multiple_pdf_forms_limit{$loop_name}", 'value' => $value, 'type' => 'number', 'label' => \esc_html__('Limit recipient forms per product', 'flexible-coupons-pro'), 'desc_tip' => \true, 'description' => \esc_html__('Enter the maximum number of recipient forms on the product page.', 'flexible-coupons-pro'), 'wrapper_class' => 'multiple-pdf-settings fc_multiple_pdf_advanced', 'class' => 'fc_multiple_pdf_forms_limit short', 'custom_attributes' => $custom_attributes]);
