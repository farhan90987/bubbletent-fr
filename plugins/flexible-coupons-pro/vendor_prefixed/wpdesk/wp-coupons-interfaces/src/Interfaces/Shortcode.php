<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces;

/**
 * Shortcode interface for declaration single shortcode.
 *
 * @package WPDesk\Library\CouponInterfaces
 */
interface Shortcode
{
    /**
     * @return string
     */
    public function get_id();
    /**
     * Shortcode definition for canva editor.
     *
     * Example: [ 'text' => '[shortcode_name]', 'top' => 20, 'left' => 20, 'width'  => 200, 'height' => 30 ];
     *
     * @return array
     */
    public function definition();
    /**
     * @param ShortcodeData $shortcode_data
     *
     * @return string
     */
    public function get_value(ShortcodeData $shortcode_data);
}
