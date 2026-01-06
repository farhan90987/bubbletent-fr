<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces;

/**
 * Fonts interface.
 *
 * @package WPDesk\Library\CouponInterfaces
 */
interface Fonts
{
    /**
     * Returns a font list for the MPDF library
     *
     * Format: [ 'lato' => [ 'R'  => 'Lato-Regular.ttf', 'I'  => 'Lato-Regular.ttf', 'B'  => 'Lato-Bold.ttf', 'BI' => 'Lato-Bold.ttf', ] ... ],
     *
     * @return array
     */
    public function get();
}
