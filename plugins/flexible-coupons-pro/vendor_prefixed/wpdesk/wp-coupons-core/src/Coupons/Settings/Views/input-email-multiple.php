<?php

namespace FlexibleCouponsProVendor;

/**
 * @var Field    $field
 * @var string   $name_prefix
 * @var string   $value
 */
if (empty($value) || \is_string($value)) {
    $input_values[] = '';
} else {
    $input_values = $value;
}
?>
<div class="field-clone-element-container">
	<?php 
foreach ($input_values as $text_value) {
    ?>
		<?php 
    if (!\in_array($field->get_type(), ['number', 'text', 'hidden'])) {
        ?>
			<input type="hidden" name="<?php 
        echo \esc_attr($name_prefix . '[' . $field->get_name() . ']');
        ?>" value="no"/>
		<?php 
    }
    ?>

		<?php 
    if ($field->get_type() === 'checkbox' && $field->has_sublabel()) {
        ?>
			<label><?php 
    }
    ?>
		<div class="field-clone-wrapper">
			<input
				type="<?php 
    echo \esc_attr($field->get_type());
    ?>"
				name="<?php 
    echo \esc_attr($name_prefix) . '[' . \esc_attr($field->get_name()) . '][]';
    ?>"
				id="<?php 
    echo \esc_attr($field->get_id());
    ?>"

				<?php 
    if ($field->has_classes()) {
        ?>
					class="<?php 
        echo \esc_attr($field->get_classes());
        ?>"
				<?php 
    }
    ?>

				<?php 
    if ($field->get_type() === 'text' && $field->has_placeholder()) {
        ?>
					placeholder="<?php 
        echo \esc_html($field->get_placeholder());
        ?>"
				<?php 
    }
    ?>

				<?php 
    foreach ($field->get_attributes() as $key => $atr_val) {
        echo \esc_attr($key . '="' . $atr_val . '"');
        ?>
				<?php 
    }
    ?>

				<?php 
    if ($field->is_required()) {
        ?>
					required="required"<?php 
    }
    ?>
				<?php 
    if ($field->is_disabled()) {
        ?>
					disabled="disabled"<?php 
    }
    ?>
				<?php 
    if ($field->is_readonly()) {
        ?>
					readonly="readonly"<?php 
    }
    ?>
				<?php 
    if (\in_array($field->get_type(), ['number', 'text', 'hidden'])) {
        ?>
					value="<?php 
        echo \esc_html($text_value);
        ?>"
				<?php 
    } else {
        ?>
					value="yes"
					<?php 
        if ($value === 'yes') {
            ?>
						checked="checked"
					<?php 
        }
        ?>
				<?php 
    }
    ?>
			/>
			<span class="add-email-field"><span class="dashicons dashicons-plus-alt"></span></span>
			<span class="remove-email-field"><span class="dashicons dashicons-remove"></span></span>
		</div>

		<?php 
    if ($field->get_type() === 'checkbox' && $field->has_sublabel()) {
        ?>
			<?php 
        echo \esc_html($field->get_sublabel());
        ?></label>
		<?php 
    }
    ?>
	<?php 
}
?>
</div>
<?php 
