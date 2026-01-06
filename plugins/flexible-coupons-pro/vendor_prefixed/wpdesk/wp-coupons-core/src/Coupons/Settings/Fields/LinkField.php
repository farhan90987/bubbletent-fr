<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields;

use FlexibleCouponsProVendor\WPDesk\Forms\Field\BasicField;
/**
 * Define standalone link block (like buy pro).
 *
 * @package WPDesk\Library\WPCoupons\Settings\Fields
 */
class LinkField extends BasicField
{
    public function __construct()
    {
        $this->set_name('');
    }
    /**
     * @return string
     */
    public function get_template_name(): string
    {
        return 'link';
    }
    /**
     * @return true
     */
    public function should_override_form_template(): bool
    {
        return \true;
    }
}
