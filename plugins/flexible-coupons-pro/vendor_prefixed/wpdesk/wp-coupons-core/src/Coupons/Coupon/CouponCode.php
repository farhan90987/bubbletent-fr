<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Coupon;

use WC_Order_Item;
use WC_Product;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\Helper;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
use FlexibleCouponsProVendor\WPDesk\Persistence\Adapter\WordPress\WordpressOptionsContainer;
/**
 * Create coupon code.
 *
 * @package WPDesk\Library\WPCoupons\Integration\Coupon
 */
class CouponCode
{
    /**
     * @var WordpressOptionsContainer
     */
    private $settings;
    /**
     * @var WC_Order_Item
     */
    private $item;
    /**
     * @var PostMeta
     */
    private $post_meta;
    /**
     * @var false|WC_Product|null
     */
    private $product;
    /**
     * @param WordpressOptionsContainer $settings
     * @param                           $item
     */
    public function __construct(WordpressOptionsContainer $settings, $item)
    {
        $this->settings = $settings;
        $this->item = $item;
        $this->post_meta = new PostMeta();
        $product_id = Helper::get_product_id($item);
        $this->product = wc_get_product($product_id);
    }
    /**
     * @param $product_id
     *
     * @return bool
     */
    public function has_own_prefix($product_id): bool
    {
        return 'yes' === $this->post_meta->get_private($product_id, 'flexible_coupon_coupon_code', 'no');
    }
    /**
     * @return string
     */
    private function get_random_length(): string
    {
        $length = $this->settings->get_fallback('coupon_code_random_length', 5);
        if ($this->product->is_type('variation')) {
            if ($this->has_own_prefix($this->product->get_parent_id())) {
                $length = (int) $this->post_meta->get_private($this->product->get_parent_id(), 'flexible_coupon_coupon_code_length', $length);
            }
        }
        if ($this->has_own_prefix($this->product->get_id())) {
            $length = (int) $this->post_meta->get_private($this->product->get_id(), 'flexible_coupon_coupon_code_length', $length);
        }
        if ($length < 5) {
            $length = 5;
        }
        /**
         * Defines coupon code length (without prefix).
         *
         * @param int $length
         *
         * @since 1.2.4
         */
        $length = (int) apply_filters('fcpdf/core/coupon/code/length', $length);
        return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', $length)), 0, $length);
    }
    /**
     * @return string
     */
    private function get_prefix(): string
    {
        $prefix = $this->settings->get_fallback('coupon_code_prefix', 5);
        if ($this->product->is_type('variation')) {
            if ($this->has_own_prefix($this->product->get_parent_id())) {
                $prefix = $this->post_meta->get_private($this->product->get_parent_id(), 'flexible_coupon_coupon_code_prefix', $prefix);
            }
        }
        if ($this->has_own_prefix($this->product->get_id())) {
            $prefix = $this->post_meta->get_private($this->product->get_id(), 'flexible_coupon_coupon_code_prefix', $prefix);
        }
        $prefix = str_replace('{order_id}', $this->item->get_order_id(), $prefix);
        /**
         * Defines coupon code prefix.
         *
         * @param string $prefix
         *
         * @since 1.4.0
         */
        return apply_filters('fcpdf/core/coupon/code/prefix', $prefix, $this->item, $this->product);
    }
    /**
     * @return string
     */
    private function get_suffix(): string
    {
        $suffix = $this->settings->get_fallback('coupon_code_suffix', 5);
        if ($this->product->is_type('variation')) {
            if ($this->has_own_prefix($this->product->get_parent_id())) {
                $suffix = $this->post_meta->get_private($this->product->get_parent_id(), 'flexible_coupon_coupon_code_suffix', $suffix);
            }
        }
        if ($this->has_own_prefix($this->product->get_id())) {
            $suffix = $this->post_meta->get_private($this->product->get_id(), 'flexible_coupon_coupon_code_suffix', $suffix);
        }
        $suffix = str_replace('{order_id}', $this->item->get_order_id(), $suffix);
        /**
         * Define coupon code suffix.
         *
         * @param string $suffix
         *
         * @since 1.4.0
         */
        return apply_filters('fcpdf/core/coupon/code/suffix', $suffix, $this->item, $this->product);
    }
    /**
     * Generate coupon code.
     *
     * @return string
     */
    public function get(): string
    {
        $coupon_code = $this->get_prefix() . $this->get_random_length() . $this->get_suffix();
        /**
         * Filters coupon code.
         *
         * @param string $coupon_code Coupon code.
         * @param string $coupon_code Coupon code.
         * @param string $suffix      Suffix.
         * @param object $item        Order item.
         * @param object $product      Product.
         *
         * @since 1.4.0
         */
        return apply_filters('fcpdf/core/coupon/code', $coupon_code, $this->get_prefix(), $this->get_suffix(), $this->item, $this->product);
    }
}
