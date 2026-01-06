<?php

namespace FlexibleCouponsProVendor;

/**
 * @var \WPDesk\Forms\Field $field
 * @var string $name_prefix
 * @var string $value
 */
$header_size = $field->get_meta_value('header_size') ?: '2';
?>
<tr>
	<td class="header-column" colspan="2">
		<?php 
if ($field->has_label()) {
    ?>
			<h<?php 
    echo (int) $header_size;
    ?>	<?php 
    $field->has_classes() ? ' class="' . \esc_attr($field->get_classes()) . '"' : '';
    ?>>
				<?php 
    echo \esc_html($field->get_label());
    ?>
			</h<?php 
    echo (int) $header_size;
    ?>>
			<?php 
}
if ($field->has_description()) {
    ?>
			<p <?php 
    $field->has_classes() ? ' class="' . \esc_attr($field->get_classes()) . '"' : '';
    ?>>
				<?php 
    echo \wp_kses_post($field->get_description());
    ?>
			</p>
			<?php 
}
?>
	</td>
</tr>
<?php 
