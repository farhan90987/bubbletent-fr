<?php

namespace FlexibleCouponsProVendor;

use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers\Links;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers\Plugin;
use FlexibleCouponsProVendor\WPDesk\View\Renderer\Renderer;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
/**
 * Renders the email settings section in the product edit screen.
 *
 * @var bool $is_sending Is the advanced sending addon enabled.
 * @var bool $is_premium Is the main plugin the PRO version.
 * @var Renderer $renderer The template renderer instance.
 * @var PostMeta $post_meta The post meta data handler.
 * @var int $prod_post_id The ID of the product post.
 * @var array|string[] $custom_attributes Custom attributes for the fields.
 * @var string $pro_url The URL for the PRO version upgrade.
 */
echo '<div class="fc-options-group"><div class="input-container">';
echo '<h3>' . \esc_html__('Email settings', 'flexible-coupons-pro') . '</h3>';
if (!$is_sending) {
    $renderer->output_render('fields/addon', ['text' => \__('Advanced Sending', 'flexible-coupons-pro'), 'tooltip_text' => \__('Buy Flexible PDF Coupons PRO - Advanced Sending and enable options', 'flexible-coupons-pro'), 'link' => Links::get_fcs_link()]);
}
if ($is_sending && !$is_premium) {
    $renderer->output_render('fields/addon', ['text' => \__('Upgrade to PRO', 'flexible-coupons-pro'), 'tooltip_text' => \__('Upgrade to PRO and enable options below', 'flexible-coupons-pro'), 'link' => $pro_url, 'is_addon' => \false]);
}
$renderer->output_render('fields/email-template-list', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'disabled' => !$is_sending || !$is_premium, 'options' => \apply_filters('fc/field/email-template-list/options', ['' => \__('Disabled', 'flexible-coupons-pro')])]);
$renderer->output_render('fields/delay-type', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_sending' => $is_sending, 'is_variation' => \false, 'custom_attributes' => $custom_attributes]);
echo '</div></div>';
