<?php

namespace FlexibleCouponsProVendor\WPDesk\Forms\Sanitizer;

use FlexibleCouponsProVendor\WPDesk\Forms\Sanitizer;
class NoSanitize implements Sanitizer
{
    public function sanitize($value)
    {
        return $value;
    }
}
