<?php

/**
 * Coupons. Helper.
 *
 * @package WPDesk\Library\WPCoupons\Integration
 */
namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration;

use WC_Order_Item;
/**
 * Shame helper.
 *
 * @package WPDesk\Library\WPCoupons\Integration
 */
class Helper
{
    /**
     * For post meta we need to get the product ID in case the variable has its own settings for the PDF coupon.
     *
     * @param WC_Order_Item $item
     *
     * @return int
     */
    public static function get_product_id(WC_Order_Item $item): int
    {
        $variation_id = (int) $item->get_variation_id();
        $product_id = (int) $item->get_product_id();
        if (!$variation_id) {
            return $product_id;
        }
        $base_on = get_post_meta($variation_id, '_flexible_coupon_variation_base_on', \true);
        if ('yes' === $base_on) {
            return $variation_id;
        }
        return $product_id;
    }
    /**
     * @param array $coupon_data
     *
     * @return string
     */
    public static function make_coupon_url(array $coupon_data): string
    {
        return add_query_arg(['action' => 'download_coupon_pdf', 'coupon_id' => $coupon_data['coupon_id'] ?? '', 'hash' => $coupon_data['hash'] ?? ''], admin_url('admin-ajax.php'));
    }
}
