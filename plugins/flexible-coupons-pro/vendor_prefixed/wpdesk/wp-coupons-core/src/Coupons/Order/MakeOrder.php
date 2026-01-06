<?php

/**
 * Integration. Order.
 *
 * @package WPDesk\Library\WPCoupons
 */
namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Order;

use WC_Order_Item_Coupon;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\Helper;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WC_Coupon;
use WC_Order;
/**
 * Fire action after make order.
 *
 * @package WPDesk\Library\WPCoupons\Integration
 */
class MakeOrder implements Hookable
{
    /**
     * @var PostMeta
     */
    private $postmeta;
    /**
     * @param PostMeta $postmeta
     */
    public function __construct(PostMeta $postmeta)
    {
        $this->postmeta = $postmeta;
    }
    /**
     * Fires hooks.
     */
    public function hooks()
    {
        add_action('woocommerce_checkout_order_processed', [$this, 'order_processed'], 10, 3);
        add_action('woocommerce_order_item_meta_end', [$this, 'display_coupons_links'], 8, 3);
    }
    public function display_coupons_links($item_id, $item, $order)
    {
        $coupon_key = 'fcpdf_order_item_' . $item_id . '_coupon_id';
        $coupon_id = (int) $order->get_meta($coupon_key, \true);
        if (!$coupon_id) {
            $coupon_id = (int) $order->get_meta('_' . $coupon_key, \true);
        }
        $coupon_data = get_post_meta($coupon_id, '_fcpdf_coupon_data', \true);
        if (!empty($coupon_data) && $item) {
            $coupon = new WC_Coupon($coupon_id);
            $coupon_code = $coupon->get_id() ? $coupon->get_code() : '';
            $download_url = $coupon->get_id() ? Helper::make_coupon_url($coupon_data) : '';
            if ($download_url && $coupon_code) {
                echo '<p><a href="' . \esc_url($download_url) . '"><strong>' . esc_html__('Download PDF coupon', 'flexible-coupons-pro') . '</strong></a></p>';
            }
        }
    }
    /**
     * @param int      $order_id
     * @param array    $posted_data
     * @param WC_Order $order
     */
    public function order_processed(int $order_id, array $posted_data, WC_Order $order)
    {
        $coupon_items = $order->get_items('coupon');
        foreach ($coupon_items as $coupon_item) {
            if ($coupon_item instanceof WC_Order_Item_Coupon) {
                $total = (float) $coupon_item->get_discount() + (float) $coupon_item->get_discount_tax();
                $coupon_code = $coupon_item->get_code();
                $args = ['post_type' => 'shop_coupon', 'title' => $coupon_code, 'posts_per_page' => 1, 'fields' => 'ids'];
                $query = new \WP_Query($args);
                $coupon_ids = $query->posts;
                $coupon_id = !empty($coupon_ids) ? $coupon_ids[0] : 0;
                $coupon_data = $this->postmeta->get_private($coupon_id, 'fcpdf_coupon_data');
                if (!empty($coupon_data)) {
                    $coupon_object = new WC_Coupon($coupon_id);
                    $usage_limit = $coupon_object->get_usage_limit();
                    if (!$usage_limit) {
                        $amount = $coupon_object->get_amount();
                        if ($total > $amount) {
                            $amount = 0;
                        } else {
                            $amount -= $total;
                        }
                        $coupon_object->set_amount(number_format($amount, 2));
                        $coupon_object->save();
                    }
                }
            }
        }
    }
}
