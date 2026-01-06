<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Shortcodes;

use WC_Coupon;
use WC_Order;
use WC_Order_Item;
use WC_Product;
use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\ShortcodeData;
/**
 * Shortcode data container.
 *
 * We pass here some variables and pass them to the single shortcode implementation.
 *
 * @package WPDesk\Library\WPCoupons\Shortcodes
 */
class ShortcodeDataContainer implements ShortcodeData
{
    /**
     * @var WC_Order
     */
    private $order;
    /**
     * @var WC_Product
     */
    private $product;
    /**
     * @var array
     */
    private $product_field_values;
    /**
     * @var WC_Coupon
     */
    private $coupon;
    /**
     * @var string
     */
    private $coupon_code;
    /**
     * @var WC_Order_Item
     */
    private $item;
    /**
     * @param WC_Order $order
     */
    public function set_order(WC_Order $order)
    {
        $this->order = $order;
    }
    /**
     * @return WC_Order
     */
    public function get_order(): WC_Order
    {
        return $this->order;
    }
    /**
     * @param WC_Order_Item $order
     */
    public function set_item(WC_Order_Item $item)
    {
        $this->item = $item;
    }
    /**
     * @return WC_Order_Item
     */
    public function get_item(): WC_Order_Item
    {
        return $this->item;
    }
    /**
     * @param WC_Product $product
     */
    public function set_product(WC_Product $product)
    {
        $this->product = $product;
    }
    /**
     * @return WC_Product
     */
    public function get_product(): WC_Product
    {
        return $this->product;
    }
    /**
     * @param array $product_fields_values
     */
    public function set_product_fields_values(array $product_fields_values)
    {
        $this->product_field_values = $product_fields_values;
    }
    /**
     * Get array of custom product field values saved in post meta for order item.
     *
     * @return array
     */
    public function get_product_fields_values(): array
    {
        return $this->product_field_values;
    }
    /**
     * @param string $coupon_code
     */
    public function set_coupon_code(string $coupon_code)
    {
        $this->coupon_code = $coupon_code;
    }
    /**
     * @param WC_Coupon $coupon
     *
     * @return WC_Coupon
     */
    public function set_coupon(WC_Coupon $coupon): WC_Coupon
    {
        return $this->coupon = $coupon;
    }
    /**
     * @return WC_Coupon
     */
    public function get_coupon(): WC_Coupon
    {
        return $this->coupon;
    }
    /**
     * @return string
     */
    public function get_coupon_code(): string
    {
        return $this->coupon_code;
    }
}
