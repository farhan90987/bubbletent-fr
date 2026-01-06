<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces;

/**
 * Product field definition interface.
 *
 * @package WPDesk\Library\CouponInterfaces
 */
interface ProductFields
{
    /**
     * This method should return an array like: [ 'field_key' => [ 'id' => '', 'type' => '', title => '' ... ] ].
     *
     * @return array
     */
    public function get();
    /**
     * Only if return true fields will be displayed on product page and can be saved into post meta.
     * However, these fields will be displayed on the product edit page, but they will be inactive.
     *
     * @return bool
     */
    public function is_premium();
}
