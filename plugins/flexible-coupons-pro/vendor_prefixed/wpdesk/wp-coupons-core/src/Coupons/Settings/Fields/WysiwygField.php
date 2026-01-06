<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields;

use FlexibleCouponsProVendor\WPDesk\Forms\Field;
/**
 * Define custom wysiwyg field.
 *
 * @package WPDesk\Library\WPCoupons\Settings\Fields
 */
// @phpstan-ignore-next-line
class WysiwygField extends Field\WyswigField
{
    /**
     * @return string
     */
    public function get_template_name(): string
    {
        return 'wysiwyg';
    }
    /**
     * @return false
     */
    public function should_override_form_template(): bool
    {
        return \false;
    }
}
