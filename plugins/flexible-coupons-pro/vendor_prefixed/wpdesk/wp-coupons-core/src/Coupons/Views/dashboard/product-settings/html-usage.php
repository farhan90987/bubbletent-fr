<?php

namespace FlexibleCouponsProVendor;

use FlexibleCouponsProVendor\WPDesk\View\Renderer\Renderer;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
/**
 * Renders the coupon usage settings section on the product edit page.
 *
 * @var Renderer $renderer The template renderer instance.
 * @var PostMeta $post_meta The post meta data handler.
 * @var int $prod_post_id The ID of the product post.
 * @var bool $is_premium Is the main plugin the PRO version.
 * @var array|string[] $custom_attributes Custom attributes for the fields.
 */
echo '<div class="fc-options-group"><div class="input-container">';
echo '<h3>' . \esc_html__('Coupon usage settings', 'flexible-coupons-pro') . '</h3>';
$renderer->output_render('fields/usage-limit', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_premium' => $is_premium, 'custom_attributes' => $custom_attributes]);
$renderer->output_render('fields/expiring-date', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_premium' => $is_premium]);
$renderer->output_render('fields/expiring-date-own', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_premium' => $is_premium]);
$renderer->output_render('fields/free-shipping', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_premium' => $is_premium]);
$renderer->output_render('fields/include-products', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_premium' => $is_premium]);
$renderer->output_render('fields/include-categories', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_premium' => $is_premium]);
echo '</div></div>';
