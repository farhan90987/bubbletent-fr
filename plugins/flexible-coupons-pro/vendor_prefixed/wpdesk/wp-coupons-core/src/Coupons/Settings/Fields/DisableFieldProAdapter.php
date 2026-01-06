<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields;

use FlexibleCouponsProVendor\WPDesk\Forms\Field;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\CouponsIntegration;
/**
 * Disable field adapter.
 *
 * @package WPDesk\Library\WPCoupons\Settings\Fields
 */
class DisableFieldProAdapter
{
    /**
     * @var Field\BasicField
     */
    private $field;
    /**
     * @var string
     */
    private $name;
    /**
     * @param Field $field
     */
    public function __construct(string $name, Field $field)
    {
        $this->name = $name;
        $this->field = $field;
    }
    public function get_field()
    {
        if (!CouponsIntegration::is_pro()) {
            $this->field->set_disabled();
            $this->field->set_readonly();
            return $this->field;
        }
        $this->field->set_name($this->name);
        return $this->field;
    }
}
