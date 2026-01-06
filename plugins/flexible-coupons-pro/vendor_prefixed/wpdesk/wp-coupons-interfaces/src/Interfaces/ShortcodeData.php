<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces;

/**
 * An interface that groups data from different sources and is passing to the single shortcode implementation.
 *
 * @package WPDesk\Library\CouponInterfaces
 */
interface ShortcodeData
{
    /**
     * @return \WC_Order
     */
    public function get_order();
    /**
     * @return \WC_Product
     */
    public function get_product();
    /**
     * Get array of custom product field values saved in post meta for order item.
     *
     * Return [ 'field_key' => 'value ' ... ].
     *
     * @return array
     */
    public function get_product_fields_values();
    /**
     * @return \WC_Coupon
     */
    public function get_coupon();
    /**
     * @return string
     */
    public function get_coupon_code();
}
