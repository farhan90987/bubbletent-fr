<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces;

/**
 * Editor area properties interface.
 *
 * @package WPDesk\Library\CouponInterfaces
 */
interface EditorAreaProperties
{
    /**
     * @return int
     */
    public function get_width();
    /**
     * @return int
     */
    public function get_height();
    /**
     * Get page format.
     *
     * Return A4, A5 or A6.
     *
     * @return string
     */
    public function get_format();
    /**
     * Get page orientation.
     *
     * Return L or P.
     *
     * @return string
     */
    public function get_orientation();
    /**
     * Get page format.
     *
     * Return rgba or hex value. Example: rgba(255,255,255,1) or hex: #FFFFFF.
     *
     * @return string
     */
    public function get_background_color();
}
