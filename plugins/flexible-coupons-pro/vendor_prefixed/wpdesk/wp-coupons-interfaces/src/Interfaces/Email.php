<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces;

/**
 * Interface for custom email message
 *
 * @package WPDesk\Library\CouponInterfaces
 */
interface Email
{
    /**
     * @param int   $order_id Order ID.
     * @param array $meta     Meta.
     */
    public function send_mail($order_id, $meta);
}
