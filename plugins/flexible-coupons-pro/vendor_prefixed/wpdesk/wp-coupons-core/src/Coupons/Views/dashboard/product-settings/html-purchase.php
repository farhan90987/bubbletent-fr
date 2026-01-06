<?php

namespace FlexibleCouponsProVendor;

use FlexibleCouponsProVendor\WPDesk\View\Renderer\Renderer;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers\Links;
/**
 * Renders the product purchase and email delay settings on the product edit page.
 *
 * @var bool $is_multiple_pdfs Is the Multiple PDFs addon enabled.
 * @var bool $is_premium Is the main plugin the PRO version.
 * @var string $pro_url The URL for the PRO version upgrade.
 * @var Renderer $renderer The template renderer instance.
 * @var PostMeta $post_meta The post meta data handler.
 * @var int $prod_post_id The ID of the product post.
 * @var array|string[] $custom_attributes Custom attributes for the fields.
 * @var array|string[] $settings Plugin settings.
 * @var bool $is_sending Is the advanced sending addon enabled.
 */
echo '<div class="fc-options-group fc-multiple-pdfs-options-wrapper"><div class="input-container">';
echo '<h3>' . \esc_html__('Product purchase settings', 'flexible-coupons-pro') . '</h3>';
if (!$is_multiple_pdfs) {
    $renderer->output_render('fields/addon', ['text' => \__('Multiple PDFs', 'flexible-coupons-pro'), 'tooltip_text' => \__('Buy Flexible PDF Coupons PRO - Multiple PDFs and enable options', 'flexible-coupons-pro'), 'link' => Links::get_fcmpdf_link()]);
}
if ($is_multiple_pdfs && !$is_premium) {
    $renderer->output_render('fields/addon', ['text' => \__('Upgrade to PRO', 'flexible-coupons-pro'), 'tooltip_text' => \__('Upgrade to PRO and enable options below', 'flexible-coupons-pro'), 'link' => $pro_url, 'is_addon' => \false]);
}
$renderer->output_render('fields/multiple-pdfs/multiple-coupons-enable', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_premium' => $is_premium, 'is_multiple_pdfs' => $is_multiple_pdfs, 'custom_attributes' => $custom_attributes, 'settings' => $settings]);
echo '<div class="fc-options-group fc-multiple-pdfs-advanced-options">';
$renderer->output_render('fields/multiple-pdfs/multiple-coupons-send-to-first-email', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_premium' => $is_premium, 'is_multiple_pdfs' => $is_multiple_pdfs, 'custom_attributes' => $custom_attributes, 'settings' => $settings]);
$renderer->output_render('fields/multiple-pdfs/multiple-coupons-forms-limit', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_premium' => $is_premium, 'is_multiple_pdfs' => $is_multiple_pdfs, 'custom_attributes' => $custom_attributes, 'settings' => $settings]);
echo '</div>';
echo '</div>';
echo '<div class="show_if_simple_delay">';
$renderer->output_render('fields/delay-interval', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_sending' => $is_sending]);
$renderer->output_render('fields/delay-value', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_sending' => $is_sending]);
echo '</div>';
echo '<div class="show_if_fixed_date_delay">';
$renderer->output_render('fields/delay-fixed-date', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_sending' => $is_sending]);
echo '</div></div>';
