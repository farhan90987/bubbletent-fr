<?php

namespace FlexibleCouponsProVendor;

/**
 * @var \WPDesk\Forms\Field $field
 */
if ($field->is_disabled()) {
    ?>
	<tr>
		<th class="titledesc" colspan="2" class="<?php 
    $field->has_classes() ? $field->get_classes() : '';
    ?>">
			<?php 
    echo \wp_kses_post($field->get_label());
    ?>
		</th>
	</tr>
	<?php 
}
