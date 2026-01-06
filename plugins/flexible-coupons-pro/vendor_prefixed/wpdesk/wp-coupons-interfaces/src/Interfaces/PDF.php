<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces;

/**
 * PDF Interface.
 *
 * @package WPDesk\Library\CouponInterfaces
 */
interface PDF
{
    /**
     * @param int $order_id Order ID.
     * @param int $item_id  Order item ID.
     *
     * @return string
     */
    public function get_pdf($order_id, $item_id);
}
