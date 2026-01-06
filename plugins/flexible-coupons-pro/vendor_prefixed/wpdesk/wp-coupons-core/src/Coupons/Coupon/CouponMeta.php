<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Coupon;

use WC_Coupon;
use WC_Order_Item;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\Helper;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
/**
 * Save post meta for coupon.
 *
 * @package WPDesk\Library\WPCoupons\Integration\Coupon
 */
class CouponMeta
{
    /**
     * @var PostMeta
     */
    private $post_meta;
    /**
     * @param PostMeta $post_meta
     */
    public function __construct(PostMeta $post_meta)
    {
        $this->post_meta = $post_meta;
    }
    /**
     * @param WC_Order_Item $item
     * @param int           $order_id
     * @param int           $coupon_id
     * @param string        $coupon_code
     *
     * @return array
     */
    public function update(WC_Order_Item $item, int $order_id, int $coupon_id, string $coupon_code): array
    {
        $coupon_prefix = 'fcpdf_order_item_' . $item->get_id();
        $order = \wc_get_order($order_id);
        $order->update_meta_data($coupon_prefix . '_coupon_id', $coupon_id);
        $order->update_meta_data($coupon_prefix . '_coupon_code', $coupon_code);
        $order->save();
        $coupon = new WC_Coupon($coupon_id);
        $order = wc_get_order($order_id);
        $coupon_data['hash'] = \wp_hash($coupon_code, 'nonce');
        $coupon_data['order_id'] = $order_id;
        $coupon_data['coupon_id'] = $coupon->get_id();
        $coupon_data['coupon_code'] = $coupon->get_code();
        $coupon_data['coupon_value'] = wc_price($coupon->get_amount(), ['currency' => $order->get_currency()]);
        if ($coupon->get_date_expires()) {
            $expiry_date_format = get_option('flexible_coupons_expiry_date_format', get_option('date_format', 'Y-m-d'));
            if (empty($expiry_date_format)) {
                $expiry_date_format = get_option('date_format', 'Y-m-d');
            }
            $coupon_data['coupon_expiry'] = $coupon->get_date_expires()->date_i18n($expiry_date_format);
        } else {
            $coupon_data['coupon_expiry'] = '';
        }
        $coupon_data['product_id'] = $item->get_product_id();
        $coupon_data['variation_id'] = $item->get_variation_id();
        $coupon_data['item_id'] = $item->get_id();
        $coupon_data['coupon_url'] = Helper::make_coupon_url($coupon_data);
        $coupon_data = apply_filters('fcpro/core/coupon/after/save', $coupon_data);
        $this->post_meta->update_private($coupon_id, 'fcpdf_coupon_hash', $coupon_data['hash']);
        $this->post_meta->update_private($coupon_id, 'fcpdf_coupon_data', $coupon_data);
        return $coupon_data;
    }
}
