<?php

namespace FlexibleCouponsProVendor;

/**
 * @var \WPDesk\Library\WPCoupons\Settings\Fields\AddonField $field
 */
if ($field->is_disabled()) {
    ?>
	<tr style="position: relative">
		<td style="padding: 0">
			<div class="form-field addon-pill-container addon-pill-container__settings
			<?php 
    echo \esc_attr($field->has_classes() ? $field->get_classes() : '');
    ?>">
			<a href="<?php 
    echo \esc_url($field->get_link() . '&utm_content=coupon-settings');
    ?>"
				target="_blank"
				class="addon-pill"
			>
		<span class="addon-pill-text">
			<?php 
    if ($field->is_addon()) {
        ?>
				<?php 
        \esc_html_e('Add-on - ', 'flexible-coupons-pro');
        ?>
				<?php 
        echo \esc_html($field->get_label());
        ?>
			<?php 
    } else {
        ?>
				<?php 
        echo \esc_html($field->get_label());
        ?>
			<?php 
    }
    ?>
		</span>
					<span class="addon-pill-arrow"> →</span>
					<span class="tooltip">
					<?php 
    echo \esc_html($field->get_description());
    ?>
					</span>
				</a>
			</div>
		</td>
	</tr>

	<?php 
}
