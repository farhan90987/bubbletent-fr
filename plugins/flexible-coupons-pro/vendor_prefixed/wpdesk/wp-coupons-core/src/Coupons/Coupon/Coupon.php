<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Coupon;

use WC_Coupon;
use WC_Order_Item;
use WC_Order_Item_Product;
use FlexibleCouponsProVendor\WPDesk\Persistence\PersistentContainer;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\Helper;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
/**
 * Create WooCommerce coupon.
 *
 * @package WPDesk\Library\WPCoupons\Integration\Coupon
 */
class Coupon
{
    const DEFAULT_EXPIRING_TIME = 365;
    /**
     * @var PostMeta
     */
    private $post_meta;
    /**
     * @var PersistentContainer;
     */
    private $settings;
    /**
     * @param PostMeta $post_meta
     */
    public function __construct(PostMeta $post_meta, $settings)
    {
        $this->post_meta = $post_meta;
        $this->settings = $settings;
    }
    /**
     * Insert WooCommerce coupon.
     *
     * @param WC_Order_Item $item
     * @param string        $coupon_code
     * @param array         $product_fields_values
     * @param int           $order_id
     *
     * @return int|false
     * @throws Exception
     */
    public function insert(WC_Order_Item $item, string $coupon_code, array $product_fields_values, int $order_id)
    {
        $coupon = new WC_Coupon();
        if ($item instanceof WC_Order_Item_Product) {
            $amount = $this->get_price_from_oder_item($item);
            $coupon->set_code($coupon_code);
            $coupon->set_date_created(current_time('mysql'));
            $product_id = Helper::get_product_id($item);
            if (!$this->is_never_expiring($product_id)) {
                $expiry_date = $this->get_coupon_expiring_date($product_id);
                $coupon->set_date_expires($expiry_date);
            }
            $coupon->set_description(implode(', ', $product_fields_values));
            if ('yes' !== $this->post_meta->get_private(Helper::get_product_id($item), 'flexible_coupon_remove_usage_limit')) {
                $coupon->set_usage_limit(1);
            }
            if ('yes' === $this->post_meta->get_private(Helper::get_product_id($item), 'flexible_coupon_product_free_shipping')) {
                $coupon->set_free_shipping(\true);
            }
            $product_ids = $this->post_meta->get_private(Helper::get_product_id($item), 'flexible_coupon_product_ids');
            if (!empty($product_ids)) {
                $coupon->set_product_ids($product_ids);
            }
            $product_categories = $this->post_meta->get_private(Helper::get_product_id($item), 'flexible_coupon_product_categories');
            if (!empty($product_categories)) {
                $coupon->set_product_categories($product_categories);
            }
            $coupon->set_amount($amount);
            /**
             * Set coupon data before save.
             *
             * @param WC_Coupon $coupon
             * @param int $order_id
             * @param WC_Order_Item $item
             *
             * @since 1.2.4
             */
            $coupon = apply_filters('fcpdf/core/coupon/before/create', $coupon, $order_id, $item);
            if ($coupon instanceof WC_Coupon) {
                $coupon = $coupon->save();
            } else {
                throw new \Exception('Failed to save coupon. Check before filter! ');
            }
            return $coupon;
        }
        return \false;
    }
    /**
     * @param int $product_id Product ID.
     *
     * @return int
     */
    public function get_coupon_expiring_time(int $product_id): int
    {
        $product_expiring = $this->post_meta->get_private($product_id, 'flexible_coupon_expiring_date', self::DEFAULT_EXPIRING_TIME);
        $product_expiring_own = (int) $this->post_meta->get_private($product_id, 'flexible_coupon_expiring_date_own', self::DEFAULT_EXPIRING_TIME);
        if ($product_expiring === 'own' && $product_expiring_own) {
            return $product_expiring_own;
        }
        $expiring_time = (int) $product_expiring;
        if ($expiring_time === 0) {
            return self::DEFAULT_EXPIRING_TIME;
        }
        return $expiring_time;
    }
    /**
     * @param int $product_id
     *
     * @return bool
     */
    private function is_never_expiring(int $product_id): bool
    {
        $expiring = $this->post_meta->get_private($product_id, 'flexible_coupon_expiring_date', self::DEFAULT_EXPIRING_TIME);
        return $expiring !== 'own' && 0 === (int) $expiring;
    }
    /**
     * @param int $product_id Product ID.
     *
     * @return string|false
     */
    private function get_coupon_expiring_date(int $product_id)
    {
        $product_expiring = $this->get_coupon_expiring_time($product_id);
        /**
         * Defines own expiry date for coupon.
         *
         * @param string|false $date
         *
         * @since 1.2.4
         */
        $date = date('Y-m-d', strtotime('NOW +' . $product_expiring . ' days'));
        return apply_filters('fcpdf/core/coupon/expiry', $date);
        //phpcs:ignore
    }
    /**
     * @param WC_Order_Item_Product $order_item
     *
     * @return float
     */
    private function get_price_from_oder_item(WC_Order_Item_Product $order_item): float
    {
        $product_id = $order_item->get_product_id();
        $variation_id = $order_item->get_variation_id();
        if ($variation_id) {
            $product_id = $variation_id;
        }
        $item_price = (float) $order_item->get_total();
        if (\wc_prices_include_tax()) {
            $item_price += (float) $order_item->get_total_tax();
        }
        if ($this->settings->get_fallback('coupon_regular_price') === 'yes') {
            $product = wc_get_product($product_id);
            if ((float) $product->get_regular_price() > 0) {
                $item_price = $product->get_regular_price();
            }
        }
        return wc_format_decimal($item_price, wc_get_price_decimals());
    }
}
