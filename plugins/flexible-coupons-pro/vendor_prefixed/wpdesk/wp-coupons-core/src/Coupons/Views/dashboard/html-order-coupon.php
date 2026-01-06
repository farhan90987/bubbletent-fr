<?php

namespace FlexibleCouponsProVendor;

/**
 * Order metabox template.
 */
$data = $params ?? [];
$product_url = $data['product_url'] ?? '';
$download_url = $data['download_url'] ?? '';
$coupon_url = $data['coupon_url'] ?? '';
$coupon_id = (int) isset($data['coupon_id']) ? $data['coupon_id'] : 0;
$coupon_code = $data['coupon_code'] ?? '';
$product_name = $data['product_name'] ?? '';
$item_id = $data['item_id'] ?? 0;
?>
<div class="metabox_coupon">
	<p class="product_name"><strong><?php 
\esc_html_e('Product name:', 'flexible-coupons-pro');
?></strong> <a
			href="<?php 
echo \esc_url($product_url);
?>"><?php 
echo \esc_html($product_name);
?></a></p>
	<?php 
if (!$coupon_id) {
    ?>
		<button class="generate_coupon button button-secondary"
				data-item-id="<?php 
    echo \esc_attr($item_id);
    ?>"><?php 
    \esc_html_e('Generate coupon', 'flexible-coupons-pro');
    ?></button>
		<?php 
    \wp_nonce_field('generate_coupon', 'generate_coupon_nonce');
    ?>
	<?php 
}
?>
	<div class="generated-coupons">

		<?php 
if ($coupon_id) {
    include 'html-order-coupon-generated.php';
}
?>
	</div>
</div>
<?php 
