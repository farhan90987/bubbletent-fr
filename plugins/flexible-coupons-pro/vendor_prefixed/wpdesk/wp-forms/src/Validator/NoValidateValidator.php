<?php

namespace FlexibleCouponsProVendor\WPDesk\Forms\Validator;

use FlexibleCouponsProVendor\WPDesk\Forms\Validator;
class NoValidateValidator implements Validator
{
    public function is_valid($value): bool
    {
        return \true;
    }
    public function get_messages(): array
    {
        return [];
    }
}
