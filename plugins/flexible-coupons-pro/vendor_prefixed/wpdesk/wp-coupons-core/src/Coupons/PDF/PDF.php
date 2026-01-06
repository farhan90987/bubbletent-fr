<?php

/**
 * PDF Renderer.
 *
 * @package WPDesk\Library\WPCoupons
 */
namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\PDF;

use Exception;
use RuntimeException;
use WC_Coupon;
use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\EditorIntegration;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\Helper;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\ProductFields;
use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\Shortcode;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Product\ProductEditPage;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Shortcodes\Shortcodes;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Shortcodes\ShortcodeDataContainer;
use FlexibleCouponsProVendor\WPDesk\View\Renderer\Renderer;
use WC_Order;
use WC_Order_Item;
use WC_Order_Item_Product;
use WC_Product;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\ShortCodeReplacer;
/**
 * Render PDF
 *
 * @package WPDesk\Library\WPCoupons\PDF
 */
class PDF
{
    /**
     * Key of the array where we store all objects added in editor
     */
    const AREA_OBJECTS = 'areaObjects';
    /**
     * @var Renderer
     */
    private $renderer;
    /**
     * @var ProductFields
     */
    private $product_fields;
    /**
     * @var PostMeta
     */
    private $postmeta;
    /**
     * @var EditorIntegration;
     */
    private $editor;
    /**
     * @var Shortcodes
     */
    private $shortcodes;
    /**
     * @param EditorIntegration $editor         Editor integration.
     * @param Renderer          $renderer       Renderer.
     * @param ProductFields     $product_fields Product fields.
     * @param PostMeta          $post_meta      Post meta.
     * @param array             $shortcodes
     */
    public function __construct(EditorIntegration $editor, Renderer $renderer, ProductFields $product_fields, PostMeta $post_meta, array $shortcodes)
    {
        $this->editor = $editor;
        $this->renderer = $renderer;
        $this->product_fields = $product_fields;
        $this->postmeta = $post_meta;
        $this->shortcodes = $shortcodes;
    }
    /**
     * @param WC_Order $order   Order.
     * @param int      $item_id Item ID.
     *
     * @return array
     * @throws Exception Throw exception if item doesn't exists.
     */
    private function get_order_item_meta(WC_Order $order, int $item_id): array
    {
        $item = $order->get_item($item_id);
        if (!$item) {
            throw new RuntimeException('Item doesn\'t exists');
        }
        $meta = [];
        if ($item instanceof WC_Order_Item_Product) {
            $is_coupon_item = 'yes' === $this->postmeta->get_private($item->get_product_id(), ProductEditPage::PRODUCT_COUPON_SLUG, \true);
            if ($is_coupon_item && $this->product_fields->is_premium()) {
                foreach ($this->product_fields->get() as $id => $field) {
                    $meta[$id] = wc_get_order_item_meta($item->get_id(), $id, \true);
                }
            }
        }
        return $meta;
    }
    /**
     * @param WC_Order   $order                 Order.
     * @param WC_Product $product               Product.
     * @param WC_Coupon  $coupon                Coupon object.
     * @param array      $product_fields_values Product fields values saved in order item meta.
     * @param string     $coupon_code           Coupon code.
     *
     * @return array
     */
    private function match_shortcode_values(WC_Order $order, WC_Order_Item $item, WC_Product $product, WC_Coupon $coupon, array $product_fields_values, string $coupon_code): array
    {
        $shortcodes = [];
        foreach ($this->shortcodes as $shortcode) {
            if ($shortcode instanceof Shortcode) {
                $data_container = new ShortcodeDataContainer();
                $data_container->set_order($order);
                $data_container->set_product($product);
                $data_container->set_product_fields_values($product_fields_values);
                $data_container->set_coupon_code($coupon_code);
                $data_container->set_coupon($coupon);
                $data_container->set_item($item);
                $shortcodes[$shortcode->get_id()] = $shortcode->get_value($data_container);
            }
        }
        return $shortcodes;
    }
    /**
     * Prepare template data.
     *
     * @param int           $template_id           Product coupon template ID.
     * @param WC_Order      $order                 Order.
     * @param WC_Order_Item $item                  Order item.
     * @param array         $product_fields_values Product fields values storage in order item meta.
     *
     * @return array
     * @throws Exception Throw error if product doesn't exists.
     */
    private function prepare_template_data(int $template_id, WC_Order $order, WC_Order_Item $item, array $product_fields_values): array
    {
        $template_meta = $this->editor->get_post_meta($template_id);
        if ($item instanceof WC_Order_Item_Product) {
            $coupon_id = (int) $order->get_meta('fcpdf_order_item_' . $item->get_id() . '_coupon_id');
            if (!$coupon_id) {
                $coupon_id = (int) $order->get_meta('_fcpdf_order_item_' . $item->get_id() . '_coupon_id');
            }
            if (empty($template_meta) || !is_array($template_meta)) {
                throw new RuntimeException(esc_html__('Unknown template', 'flexible-coupons-pro'));
            }
            $product = $item->get_product();
            $coupon = new WC_Coupon($coupon_id);
            if (!$coupon->get_id()) {
                throw new RuntimeException(esc_html__('Coupon doesn\'t exists.', 'flexible-coupons-pro'));
            }
            $shortcodes_to_replace = $this->match_shortcode_values($order, $item, $product, $coupon, $product_fields_values, $coupon->get_code());
            $replacer = new ShortCodeReplacer($shortcodes_to_replace);
            if (isset($template_meta[self::AREA_OBJECTS])) {
                foreach ($template_meta[self::AREA_OBJECTS] as $id => $data) {
                    if (isset($template_meta[self::AREA_OBJECTS][$id]['text'])) {
                        $template_meta[self::AREA_OBJECTS][$id]['text'] = $replacer->replace_shortcodes($template_meta[self::AREA_OBJECTS][$id]['text']);
                    }
                }
                return $template_meta[self::AREA_OBJECTS];
            }
        }
        if (!isset($template_meta[self::AREA_OBJECTS]) || !is_array($template_meta[self::AREA_OBJECTS])) {
            throw new RuntimeException(esc_html__('Template empty or corrupted. Please check template in the editor.', 'flexible-coupons-pro'));
        }
        return $template_meta[self::AREA_OBJECTS];
    }
    /**
     * @param int $order_id Order ID.
     * @param int $item_id  Item ID.
     *
     * @return string
     *
     * @throws RuntimeException Throw exception if order id or item id doesn't exists.
     * @throws Exception
     */
    public function string_output($order_id, $item_id): string
    {
        if (!$order_id || !$item_id) {
            throw new RuntimeException('Order ID or item ID doesn\'t exists.');
        }
        $order = wc_get_order($order_id);
        $item = $order->get_item($item_id);
        $item_meta = $this->get_order_item_meta($order, $item_id);
        $template_id = (int) $this->postmeta->get_private(Helper::get_product_id($item), 'flexible_coupon_product_template');
        $template_data = $this->prepare_template_data($template_id, $order, $item, $item_meta);
        $editor_objects = new Items($template_data);
        $area_properties = $this->editor->get_area_properties($template_id);
        $data = ['editor_width' => $area_properties->get_width(), 'editor_height' => $area_properties->get_height(), 'editor_bgcolor' => $area_properties->get_background_color(), 'html' => $editor_objects->get_html()];
        $html = $this->renderer->render('html-pdf', $data);
        $pdf_library = new PDFWrapper();
        $pdf_library->set_editor_data($area_properties);
        if (defined('FLEXIBLE_COUPONS_DEBUG')) {
            $this->debug_before_render_pdf($html);
        }
        return $pdf_library->render($html);
    }
    /**
     * Define FLEXIBLE_COUPONS_DEBUG in wp-config.php if you want display HTML not PDF in browser for debug mode.
     *
     * @param string $html
     */
    private function debug_before_render_pdf(string $html)
    {
        echo $html;
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        die;
    }
}
