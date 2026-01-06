<?php

namespace FlexibleCouponsProVendor;

use FlexibleCouponsProVendor\WPDesk\View\Renderer\Renderer;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
/**
 * Renders the Product Form Fields section in the product edit screen.
 *
 * @var bool $is_premium Is the main plugin the PRO version.
 * @var string $pro_url The URL for the PRO version upgrade.
 * @var Renderer $renderer The template renderer instance.
 * @var PostMeta $post_meta The post meta data handler.
 * @var int $prod_post_id The ID of the product post.
 * @var array|string[] $product_fields The available product fields to render.
 * @var array|string[] $custom_attributes Custom attributes for the fields.
 */
echo '<div class="fc-options-group fc-custom-fields-group"><div class="input-container">';
echo '<h3>' . \esc_html__('Product Form Fields', 'flexible-coupons-pro') . '</h3>';
if (!$is_premium) {
    $renderer->output_render('fields/addon', ['text' => \__('Upgrade to PRO', 'flexible-coupons-pro'), 'tooltip_text' => \__('Upgrade to PRO and enable options below', 'flexible-coupons-pro'), 'link' => $pro_url, 'is_addon' => \false]);
}
$renderer->output_render('fields/product-fields', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_premium' => $is_premium, 'product_fields' => $product_fields, 'custom_attributes' => $custom_attributes]);
echo '</div></div>';
