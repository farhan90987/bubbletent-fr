<?php

namespace FlexibleCouponsProVendor\WPDesk\Forms\Field;

use FlexibleCouponsProVendor\WPDesk\Forms\Sanitizer;
use FlexibleCouponsProVendor\WPDesk\Forms\Sanitizer\TextFieldSanitizer;
class InputNumberField extends BasicField
{
    public function get_type(): string
    {
        return 'number';
    }
    public function get_sanitizer(): Sanitizer
    {
        return new TextFieldSanitizer();
    }
    public function get_template_name(): string
    {
        return 'input-number';
    }
}
