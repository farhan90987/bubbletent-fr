<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces;

/**
 * PDF library interface.
 *
 * @package WPDesk\Library\CouponInterfaces
 */
interface PDFLibrary
{
    /**
     * @param string $html_content
     *
     * @return string
     */
    public function get_content_pdf($html_content);
}
