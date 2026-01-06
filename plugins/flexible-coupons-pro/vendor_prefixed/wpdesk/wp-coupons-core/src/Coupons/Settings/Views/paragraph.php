<?php

namespace FlexibleCouponsProVendor;

/**
 * @var \WPDesk\Forms\Field $field
 * @var string              $name_prefix
 * @var string              $value
 */
if ($field->has_description()) {
    ?>
	<tr>
		<th class="titledesc" scope="row">
			<?php 
    echo wp_kses_post($field->get_label());
    ?>
		</th>
		<td class="forminp">
			<?php 
    echo wp_kses_post($field->get_description());
    ?>
		</td>
	</tr>
	<?php 
}
