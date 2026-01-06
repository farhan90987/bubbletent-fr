<?php

/**
 * Integration. Order.
 *
 * @package WPDesk\Library\WPCoupons
 */
namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Order;

use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers\Plugin;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\Helper;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Settings;
use FlexibleCouponsProVendor\WPDesk\Persistence\Adapter\WordPress\WordpressOptionsContainer;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FlexibleCouponsProVendor\WPDesk\View\Renderer\Renderer;
use WC_Coupon;
use WC_Order;
use WC_Order_Item;
use WC_Order_Item_Product;
use WP_Post;
/**
 * Add coupon meta box to order post type.
 *
 * @package WPDesk\Library\WPCoupons\Integration
 */
class OrderMetaBox implements Hookable
{
    const META_BOX_CONTEXT_SIDE = 'side';
    const META_BOX_PRIORITY_LOW = 'low';
    const PRODUCT_TYPE = 'wpdesk_pdf_coupons';
    /**
     * @var Renderer
     */
    private $renderer;
    /**
     * @var PostMeta
     */
    private $postmeta;
    /**
     * @param Renderer $renderer
     * @param PostMeta $postmeta
     */
    public function __construct(Renderer $renderer, PostMeta $postmeta)
    {
        $this->renderer = $renderer;
        $this->postmeta = $postmeta;
    }
    /**
     * Fires hooks.
     */
    public function hooks()
    {
        add_action('add_meta_boxes', [$this, 'register_meta_boxes_action']);
    }
    /**
     * Add custom meta box to order page.
     *
     * @return void|bool
     */
    public function register_meta_boxes_action()
    {
        if ($this->is_order_page()) {
            $screens = $this->get_allowed_screen_ids();
            $order_id = $this->get_order_id();
            /**
             * @var $order WC_Order Order.
             */
            $order = \wc_get_order($order_id);
            if ($order instanceof \WC_Order) {
                $items = $order->get_items();
                $products = [];
                foreach ($items as $key => $item) {
                    if ($item instanceof \WC_Order_Item_Product) {
                        $products[$item->get_product_id()][] = $item;
                    }
                }
                foreach ($products as $key => $items) {
                    // if ( $item instanceof \WC_Order_Item_Product ) {
                    $product_id = $key;
                    $is_coupon_item = 'yes' === $this->postmeta->get_private($product_id, self::PRODUCT_TYPE);
                    if ($is_coupon_item) {
                        \add_meta_box('flexible_coupon__' . $key, \__('PDF Coupon', 'flexible-coupons-pro'), [$this, 'order_coupon_callback'], $screens, self::META_BOX_CONTEXT_SIDE, self::META_BOX_PRIORITY_LOW, ['post_id' => $order_id, 'items' => $items]);
                    }
                    // }
                }
            }
        } else {
            return \false;
        }
    }
    private function get_order_id(): int
    {
        global $post;
        $post_id = 0;
        if (\is_object($post) && property_exists($post, 'ID')) {
            $post_id = $post->ID;
        } elseif (isset($_REQUEST['post']) && \is_numeric($_REQUEST['post'])) {
            $post_id = \wc_clean(\wp_unslash($_REQUEST['post']));
        } elseif (isset($_REQUEST['id']) && \is_numeric($_REQUEST['id'])) {
            $post_id = \wc_clean(\wp_unslash($_REQUEST['id']));
        }
        return (int) $post_id;
    }
    private function get_allowed_screen_ids(): array
    {
        $screens = ['shop_order', 'shop_subscription'];
        $function_name = 'wc_get_page_screen_id';
        if (function_exists($function_name)) {
            $screens[] = wc_get_page_screen_id('shop-order');
        }
        return $screens;
    }
    private function is_order_page(): bool
    {
        $screen = \get_current_screen();
        $screen_id = $screen->id ?? '';
        $post_type = $screen->post_type ?? '';
        if ('shop_order' === $post_type) {
            return \true;
        }
        if ('shop_subscription' === $post_type) {
            return \true;
        }
        if ('woocommerce_page_wc-orders' === $screen_id) {
            return \true;
        }
        return \false;
    }
    private function should_send_mail_to_first_recipient(int $product_id): bool
    {
        if (!Plugin::is_fc_multiple_pdfs_pro_addon_enabled()) {
            return \false;
        }
        $option = $this->postmeta->get_private($product_id, 'fc_multiple_pdf_first_mail');
        return filter_var($option, \FILTER_VALIDATE_BOOLEAN);
    }
    /**
     * @param object $post Post.
     * @param array $item Order item.
     */
    public function order_coupon_callback($post_or_order_object, $args)
    {
        $order = $post_or_order_object instanceof WP_Post ? wc_get_order($post_or_order_object->ID) : $post_or_order_object;
        $items = $args['args']['items'];
        foreach ($items as $item) {
            $product_id = $item->get_product_id();
            if ($this->should_send_mail_to_first_recipient($product_id)) {
                // todo: magic string. use const and filter var
                foreach ($items as $_item) {
                    $data = $this->get_item_render_data($_item, $order);
                    $this->renderer->output_render('html-order-coupon', $data);
                    if (!$data['coupon_id']) {
                        break;
                    }
                }
                break;
            }
            $data = $this->get_item_render_data($item, $order);
            $this->renderer->output_render('html-order-coupon', $data);
        }
        //phpcs:ignore
    }
    private function get_item_render_data(WC_Order_Item $item, WC_Order $order): array
    {
        $meta_coupon_name = 'fcpdf_order_item_' . $item->get_id() . '_coupon_id';
        $coupon_id = (int) $order->get_meta($meta_coupon_name);
        if (!$coupon_id) {
            $coupon_id = (int) $order->get_meta('_' . $meta_coupon_name);
        }
        $coupon = new \WC_Coupon($coupon_id);
        $coupon_data = $this->postmeta->get_private($coupon_id, 'fcpdf_coupon_data', []);
        $coupon_code = $coupon->get_id() ? $coupon->get_code() : '';
        $coupon_url = $coupon->get_id() ? \admin_url('post.php?post=' . $coupon_id . '&action=edit') : '#';
        $product = \wc_get_product($item->get_product_id());
        $product_id = $product->get_id();
        if ($product->is_type('variation')) {
            $product_id = $product->get_parent_id();
        }
        $product_url = $coupon->get_id() ? \admin_url('post.php?post=' . $product_id . '&action=edit') : '#';
        $download_url = $coupon->get_id() ? Helper::make_coupon_url($coupon_data) : '';
        return [
            'order_id' => $order->get_id(),
            'item_id' => $item->get_id(),
            'product_id' => $item->get_product_id(),
            'variation_id' => $item->get_variation_id(),
            'product_name' => $item->get_name(),
            'product_url' => $product_url,
            'coupon_id' => $coupon->get_id(),
            // TODO
            'coupon_url' => $coupon_url,
            // TODO
            'coupon_code' => $coupon_code,
            // TODO
            'coupon_is_used' => $this->is_coupon_limit_reached($coupon),
            'coupon_title' => $coupon_code,
            'download_url' => $download_url,
            // TODO
            'quantity' => $item->get_quantity(),
            'render_single_element' => !$this->should_send_mail_to_first_recipient($product_id),
        ];
    }
    /**
     * @param WC_Coupon $coupon Coupon data.
     *
     * @return bool
     */
    private function is_coupon_limit_reached(WC_Coupon $coupon): bool
    {
        return $coupon->get_usage_limit() > 0 && $coupon->get_usage_count() >= $coupon->get_usage_limit();
    }
}
