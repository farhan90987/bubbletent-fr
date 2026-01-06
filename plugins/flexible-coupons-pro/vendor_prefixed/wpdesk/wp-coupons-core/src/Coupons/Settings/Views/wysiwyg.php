<?php

namespace FlexibleCouponsProVendor;

/**
 * @var \WPDesk\Forms\Field $field
 * @var string              $name_prefix
 * @var string              $value
 */
\wp_print_styles('media-views');
$editor_id = 'wyswig_' . $field->get_name();
$editor_settings = ['textarea_name' => \esc_attr($name_prefix) . '[' . \esc_attr($field->get_name()) . ']', 'teeny' => \true, 'textarea_rows' => 10, 'quicktags' => \false, 'media_buttons' => \false];
if ($field->is_readonly()) {
    $editor_settings['tinymce']['readonly'] = \true;
}
\wp_editor(\wp_kses_post($value), $editor_id, $editor_settings);
