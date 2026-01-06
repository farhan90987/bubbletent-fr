<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Fields;

use FlexibleCouponsProVendor\WPDesk\Forms\Field\BasicField;
class AddonField extends BasicField
{
    protected $meta = ['priority' => self::DEFAULT_PRIORITY, 'default_value' => '', 'label' => '', 'description' => '', 'description_tip' => '', 'data' => [], 'type' => 'text', 'link' => '', 'is_addon' => \false];
    public function get_template_name(): string
    {
        return 'addon';
    }
    public function should_override_form_template(): bool
    {
        return \true;
    }
    public function set_link(string $value): self
    {
        $this->meta['link'] = $value;
        return $this;
    }
    public function get_link(): string
    {
        return $this->meta['link'];
        // @phpstan-ignore-line
    }
    public function set_is_addon(bool $value): self
    {
        $this->meta['is_addon'] = $value;
        return $this;
    }
    public function is_addon(): ?bool
    {
        return $this->meta['is_addon'];
        // @phpstan-ignore-line
    }
}
