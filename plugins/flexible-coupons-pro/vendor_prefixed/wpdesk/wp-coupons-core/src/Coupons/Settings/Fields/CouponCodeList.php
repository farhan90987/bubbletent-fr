<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields;

use FlexibleCouponsProVendor\WPDesk\Forms\Field\BasicField;
class CouponCodeList extends BasicField
{
    public function get_template_name(): string
    {
        return 'coupon-code-list';
    }
}
