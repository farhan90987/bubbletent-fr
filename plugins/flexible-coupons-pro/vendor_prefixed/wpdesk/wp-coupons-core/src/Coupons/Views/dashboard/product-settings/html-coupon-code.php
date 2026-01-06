<?php

namespace FlexibleCouponsProVendor;

use FlexibleCouponsProVendor\WPDesk\View\Renderer\Renderer;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers\Links;
/**
 * Renders the coupon code settings section on the product edit page.
 *
 * @var Renderer $renderer The template renderer instance.
 * @var PostMeta $post_meta The post meta data handler.
 * @var int $prod_post_id The ID of the product post.
 * @var bool $is_premium Is the main plugin the PRO version.
 * @var array|string[] $custom_attributes Custom attributes for the fields.
 * @var array|string[] $settings Plugin settings.
 * @var bool $is_code_import Is the coupon code import addon enabled.
 * @var string $pro_url The URL for the PRO version upgrade.
 */
echo '<div class="fc-options-group"><div class="input-container">';
echo '<h3>' . \esc_html__('Coupon code settings', 'flexible-coupons-pro') . '</h3>';
$renderer->output_render('fields/coupon-code-enable', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_premium' => $is_premium, 'custom_attributes' => $custom_attributes, 'settings' => $settings]);
echo '<div class="show_if_variation_manage_prefix">';
$renderer->output_render('fields/coupon-code-prefix', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_premium' => $is_premium, 'custom_attributes' => $custom_attributes, 'settings' => $settings]);
$renderer->output_render('fields/coupon-code-suffix', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_premium' => $is_premium, 'custom_attributes' => $custom_attributes, 'settings' => $settings]);
$renderer->output_render('fields/coupon-code-length', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_premium' => $is_premium, 'custom_attributes' => $custom_attributes, 'settings' => $settings]);
echo '</div>';
if (!$is_code_import) {
    $renderer->output_render('fields/addon', ['text' => \__('Coupon Codes Import', 'flexible-coupons-pro'), 'tooltip_text' => \__('Buy Flexible PDF Coupons PRO - Coupon Codes Import and enable options', 'flexible-coupons-pro'), 'link' => Links::get_fcci_buy_link()]);
}
if ($is_code_import && !$is_premium) {
    $renderer->output_render('fields/addon', ['text' => \__('Upgrade to PRO', 'flexible-coupons-pro'), 'tooltip_text' => \__('Upgrade to PRO and enable options below', 'flexible-coupons-pro'), 'link' => $pro_url, 'is_addon' => \false]);
}
$renderer->output_render('fields/coupon-code-import-list', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'disabled' => !$is_code_import || !$is_premium, 'options' => \apply_filters('fc/field/code-import-list/options', ['' => \__('Disabled', 'flexible-coupons-pro')])]);
echo '</div></div>';
