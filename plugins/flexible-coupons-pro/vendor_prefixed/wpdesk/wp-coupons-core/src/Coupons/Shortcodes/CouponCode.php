<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Shortcodes;

use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\ShortcodeData;
use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\Shortcode;
/**
 * Coupon code shortcode declaration.
 *
 * @package WPDesk\Library\WPCoupons\Integration
 */
class CouponCode implements Shortcode
{
    const ID = 'coupon_code';
    /**
     * @return string
     */
    public function get_id(): string
    {
        return self::ID;
    }
    /**
     * @return array
     */
    public function definition(): array
    {
        return ['text' => '[coupon_code]', 'top' => 50, 'left' => 20, 'width' => 200, 'height' => 30];
    }
    /**
     * @param ShortcodeData $shortcode_data
     *
     * @return string
     */
    public function get_value(ShortcodeData $shortcode_data): string
    {
        return wp_strip_all_tags($shortcode_data->get_coupon_code());
    }
}
