<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields;

use FlexibleCouponsProVendor\WPDesk\Forms\Field\BasicField;
/**
 * Define multiple input text field.
 *
 * @package WPDesk\Library\WPCoupons\Settings\Fields
 */
class MultipleInputTextField extends BasicField
{
    public function __construct()
    {
        $this->set_default_value('');
        $this->set_attribute('type', 'text');
    }
    /**
     * @return string
     */
    public function get_template_name(): string
    {
        return 'input-email-multiple';
    }
}
