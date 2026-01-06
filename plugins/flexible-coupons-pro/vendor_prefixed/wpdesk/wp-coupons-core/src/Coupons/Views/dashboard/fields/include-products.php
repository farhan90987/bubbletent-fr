<?php

namespace FlexibleCouponsProVendor;

/**
 * Products field template.
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
$loop_id = isset($params['loop']) ? '_variation' . $params['loop'] : '';
$loop_name = isset($params['loop']) ? "_variation[{$params['loop']}]" : '';
$parent_id = isset($params['parent_id']) ? $params['parent_id'] : null;
// Get the parent default meta value for variable.
$default = $meta->get_private($parent_id, 'flexible_coupon_product_ids', []);
$product_ids = $meta->get_private($prod_post_id, 'flexible_coupon_product_ids', $default);
?>
<p class="form-field">
	<label for="fc_product_ids<?php 
echo \esc_attr($loop_id);
?>"><?php 
\esc_html_e('Products', 'flexible-coupons-pro');
?></label>
	<select
		id="fc_product_ids<?php 
echo \esc_attr($loop_id);
?>"
		class="wc-product-search" multiple="multiple"
		name="fc_product_ids<?php 
echo \esc_attr($loop_name);
?>[]"
		style="width: 80% !important;"
		data-placeholder="<?php 
\esc_attr_e('Search for a product&hellip;', 'flexible-coupons-pro');
?>"
		data-action="woocommerce_json_search_products_and_variations">
		<?php 
if (!\is_array($product_ids)) {
    $product_ids = [];
}
foreach ($product_ids as $product_id) {
    $product = \wc_get_product($product_id);
    if (\is_object($product)) {
        echo '<option value="' . \esc_attr($product_id) . '" ' . \selected(\true, \true, \false) . '>' . \wp_kses_post(\htmlspecialchars($product->get_formatted_name())) . '</option>';
    }
}
?>
	</select>
	<?php 
echo \wc_help_tip(\esc_html__('Products for which the coupon will be used. Do not select any to apply to all products.', 'flexible-coupons-pro'));
?>
</p>
<?php 
