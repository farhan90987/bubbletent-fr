<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers;

/**
 * Define default email strings.
 */
class EmailStrings
{
    public static function get_default_email_subject(): string
    {
        return __('[{site_title}] You have received a coupon', 'flexible-coupons-pro');
    }
    public static function get_default_email_body(): string
    {
        //phpcs:disable
        return __('Hi {recipient_name},', 'flexible-coupons-pro') . \PHP_EOL . \PHP_EOL . __('Thanks to {buyer_name} you get a gift voucher for use in the {site_url} ({site_title}) store.', 'flexible-coupons-pro') . \PHP_EOL . \PHP_EOL . __('Download PDF with the coupon from: {coupon_url}', 'flexible-coupons-pro') . \PHP_EOL . \PHP_EOL . __('Coupon information', 'flexible-coupons-pro') . \PHP_EOL . __('Coupon code: {coupon_code}', 'flexible-coupons-pro') . \PHP_EOL . __('Coupon value: {coupon_value}', 'flexible-coupons-pro') . \PHP_EOL . __('Expiry date: {coupon_expiry}', 'flexible-coupons-pro') . \PHP_EOL . \PHP_EOL . __('A message from the buyer:', 'flexible-coupons-pro') . \PHP_EOL . __('{recipient_message}', 'flexible-coupons-pro') . \PHP_EOL . \PHP_EOL . __('Thanks for reading!', 'flexible-coupons-pro');
        //phpcs:enable
    }
}
