<?php

namespace FlexibleCouponsProVendor\WPDesk\Forms\Sanitizer;

use FlexibleCouponsProVendor\WPDesk\Forms\Sanitizer;
class EmailSanitizer implements Sanitizer
{
    public function sanitize($value): string
    {
        return sanitize_email($value);
    }
}
