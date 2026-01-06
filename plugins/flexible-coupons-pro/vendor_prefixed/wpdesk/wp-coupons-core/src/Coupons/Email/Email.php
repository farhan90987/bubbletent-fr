<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Email;

use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Data\Email\EmailMeta;
/**
 * Interface for custom email message
 *
 * @package WPDesk\Library\CouponInterfaces
 */
interface Email
{
    /**
     * @param int $order_id Order ID.
     * @param EmailMeta $meta     Meta.
     */
    public function send_mail(int $order_id, EmailMeta $meta);
}
