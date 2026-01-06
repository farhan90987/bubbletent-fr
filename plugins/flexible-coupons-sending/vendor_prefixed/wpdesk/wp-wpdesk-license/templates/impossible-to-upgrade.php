<?php

namespace FCSVendor;

\defined('ABSPATH') || exit;
?>
<tr class="plugin-update-tr active">
	<td colspan="4" class="plugin-update colspanchange">
		<div class="update-message notice inline notice-warning notice-alt">
			<p>
			<?php 
\printf(\esc_html__('This version of %1$s plugin cannot be automatically upgraded. Download the plugin from %2$syour account%3$s to receive automatic updates in future.', 'flexible-coupons-sending'), \esc_html($plugin_info->get_plugin_name()), '<a href="' . \esc_url_raw($plugin_info->get_customer_account_url()) . '" target=\'_blank\'>', '</a>');
?>
			</p>
		</div>
	</td>
</tr>
<?php 
