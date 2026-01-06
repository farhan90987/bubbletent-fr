<?php

namespace FlexibleCouponsProVendor;

global $post;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers\Links;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers\Plugin;
$params = isset($params) ? (array) $params : [];
/**
 * @var WPDesk\Library\WPCoupons\Integration\PostMeta $post_meta
 * @var \WPDesk\View\Renderer\Renderer $renderer
 */
$post_meta = $params['post_meta'];
$renderer = $params['renderer'];
$nonce_name = $params['nonce_name'];
$nonce_action = $params['nonce_action'];
$product_fields = $params['product_fields'];
$product_templates = $params['product_templates'];
$custom_attributes = $params['custom_attributes'];
$prod_post_id = (int) $params['post_id'];
$settings = $params['settings'];
$pro_url = Links::get_pro_link();
$is_premium = $params['is_premium'];
$is_sending = Plugin::is_fcs_pro_addon_enabled();
$is_multiple_pdfs = Plugin::is_fc_multiple_pdfs_pro_addon_enabled();
$is_code_import = Plugin::is_fcci_pro_addon_enabled();
?>

<div id="pdfcoupon_product_data" class="panel woocommerce_options_panel" style="display: none;">
	<div class="fc-options-group">
		<div class="input-container">
			<?php 
\wp_nonce_field($nonce_action, $nonce_name);
$renderer->output_render('fields/product-template', ['post_meta' => $post_meta, 'post_id' => $prod_post_id, 'is_premium' => $is_premium, 'product_templates' => $product_templates]);
?>
		</div>
	</div>
	<?php 
require __DIR__ . '/product-settings/html-form-fields.php';
require __DIR__ . '/product-settings/html-purchase.php';
require __DIR__ . '/product-settings/html-usage.php';
require __DIR__ . '/product-settings/html-coupon-code.php';
require __DIR__ . '/product-settings/html-email.php';
?>
</div>
<?php 
