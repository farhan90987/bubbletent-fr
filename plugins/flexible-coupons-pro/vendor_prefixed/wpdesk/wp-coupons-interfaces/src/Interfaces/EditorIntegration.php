<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces;

/**
 * Editor interface. The interface is used to integrate with plugin and coupons library.
 *
 * @package WPDesk\Library\CouponInterfaces
 */
interface EditorIntegration
{
    /**
     * @return string
     */
    public function get_post_type();
    /**
     * @return array
     */
    public function get_post_meta($post_id);
    /**
     * @return EditorAreaProperties
     */
    public function get_area_properties($post_id);
}
