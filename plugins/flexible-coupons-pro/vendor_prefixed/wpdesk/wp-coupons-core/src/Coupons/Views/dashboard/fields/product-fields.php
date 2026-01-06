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
$is_premium = $params['is_premium'];
$product_fields = $params['product_fields'];
$custom_attributes = $params['custom_attributes'];
$is_pl = 'pl_PL' === \get_locale();
$utm = '?utm_source=flexible-coupons-product-edition&amp;utm_medium=link&amp;utm_campaign=flexible-coupons-pro';
$pro_url = $is_pl ? 'https://www.wpdesk.pl/sklep/flexible-coupons-woocommerce/' . $utm : 'https://www.wpdesk.net/products/flexible-coupons-woocommerce/' . $utm;
$loop_id = isset($params['loop']) ? '_variation' . $params['loop'] : '';
$loop_name = isset($params['loop']) ? "_variation[{$params['loop']}]" : '';
$parent_id = isset($params['parent_id']) ? $params['parent_id'] : null;
foreach ($product_fields->get() as $pf_id => $product_field) {
    if (isset($product_field['can_disable']) && \true === $product_field['can_disable']) {
        $default = !$is_premium ? 'no' : 'yes';
        echo '<div class="checkbox-wrapper">';
        echo '<div class="checkbox-wrapper-left">';
        \woocommerce_wp_checkbox(['id' => $pf_id . $loop_id, 'name' => $pf_id . $loop_name, 'value' => \esc_attr($meta->get_private($prod_post_id, $pf_id, $meta->get_private($parent_id, $pf_id, $default))), 'label' => $product_field['title'], 'desc_tip' => \true, 'description' => \esc_html__('Show or hide this field in product page.', 'flexible-coupons-pro'), 'wrapper_class' => !$is_premium ? 'read-only' : '', 'custom_attributes' => $custom_attributes]);
        echo '</div>';
        echo '<div class="checkbox-wrapper-right">';
        \woocommerce_wp_checkbox(['id' => $pf_id . '_required' . $loop_id, 'name' => $pf_id . '_required' . $loop_name, 'value' => \esc_attr($meta->get_private($prod_post_id, $pf_id . '_required', $meta->get_private($parent_id, $pf_id . '_required', $default))), 'label' => \esc_html__('Required', 'flexible-coupons-pro'), 'wrapper_class' => !$is_premium ? 'read-only' : '', 'custom_attributes' => $custom_attributes]);
        echo '</div>';
        echo '</div>';
    }
}
