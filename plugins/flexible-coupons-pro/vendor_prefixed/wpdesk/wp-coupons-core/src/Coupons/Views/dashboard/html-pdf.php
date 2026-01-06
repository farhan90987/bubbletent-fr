<?php

namespace FlexibleCouponsProVendor;

/**
 * PDF template.
 */
$params = isset($params) ? $params : [];
$data = \wp_parse_args($params, ['editor_width' => '600', 'editor_height' => '800', 'editor_bgcolor' => '#FFFFFF', 'html' => '']);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php 
\esc_html('PDF Coupon', 'flexible-coupons-pro');
?></title>
	<?php 
\do_action('flexible_coupons_head');
?>
	<style>
		.wrapper {
			top: 0;
			left: 0;
			position: absolute;
			width: <?php 
echo (float) $data['editor_width'];
?>px;
			height: <?php 
echo (float) $data['editor_height'];
?>px;
			background-color: <?php 
echo \esc_attr($data['editor_bgcolor']);
?>
		}

		@media print {
			.wrapper {
				top: 0;
				left: 0;
				position: absolute;
				width: <?php 
echo (float) $data['editor_width'];
?>px;
				height: <?php 
echo (float) $data['editor_height'];
?>px;
				background-color: <?php 
echo \esc_attr($data['editor_bgcolor']);
?>
			}
		}
	</style>
</head>
<body>
<?php 
\do_action('flexible_coupons_body_before');
?>
<div class="wrapper"></div>
<?php 
echo $data['html'];
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, (Ensure base64 content -tickets addon - is generated. 
\do_action('flexible_coupons_body_after');
?>
</body>
</html>
<?php 
