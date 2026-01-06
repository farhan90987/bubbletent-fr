<?php

/**
 * Integration. Product page.
 *
 * @package WPDesk\Library\WPCoupons
 */
namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Product;

use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\ProductFields;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers\Links;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
use FlexibleCouponsProVendor\WPDesk\Persistence\Adapter\WordPress\WordpressOptionsContainer;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FlexibleCouponsProVendor\WPDesk\View\Renderer\Renderer;
/**
 * Add custom fields to admin variation product edit page.
 *
 * @package WPDesk\Library\WPCoupons\Integration
 */
class ProductVariationEditPage implements Hookable
{
    const NONCE_NAME = 'flexible_coupons_nonce';
    const NONCE_ACTION = 'save_fields';
    /**
     * @var WordpressOptionsContainer
     */
    private $settings;
    /**
     * @var Renderer
     */
    private $renderer;
    /**
     * @var array
     */
    private $product_fields;
    /**
     * @var string
     */
    private $editor_post_type;
    /**
     * @var PostMeta
     */
    private $post_meta;
    /**
     * @param WordpressOptionsContainer $settings
     * @param Renderer                  $renderer       Renderer.
     * @param ProductFields             $product_fields Product fields.
     * @param PostMeta                  $post_meta
     * @param string                    $editor_post_type
     */
    public function __construct(WordpressOptionsContainer $settings, Renderer $renderer, ProductFields $product_fields, PostMeta $post_meta, string $editor_post_type)
    {
        $this->settings = $settings;
        $this->renderer = $renderer;
        $this->product_fields = $product_fields;
        $this->post_meta = $post_meta;
        $this->editor_post_type = $editor_post_type;
    }
    /**
     * Fires hooks.
     */
    public function hooks()
    {
        add_action('woocommerce_product_after_variable_attributes', [$this, 'add_product_general_data_field'], 10, 3);
    }
    /**
     * Add coupons fields to pdf coupon tab.
     */
    public function add_product_general_data_field($loop, $variation_data, $variation)
    {
        $expiring_date_default_value = !$this->product_fields->is_premium() ? 365 : 7;
        $this->renderer->output_render('variation/html-variation-select', ['post_id' => $variation->ID, 'loop' => $loop, 'is_premium' => $this->product_fields->is_premium(), 'custom_attributes' => $this->get_field_attributes(), 'pro_url' => Links::get_pro_link()]);
        $variation = wc_get_product($variation->ID);
        if ($this->product_fields->is_premium()) {
            $this->renderer->output_render('variation/html-product-variation-settings', ['loop' => $loop, 'renderer' => $this->renderer, 'nonce_name' => self::NONCE_NAME, 'nonce_action' => self::NONCE_ACTION, 'is_premium' => $this->product_fields->is_premium(), 'product_fields' => $this->product_fields, 'product_templates' => $this->get_coupons_templates_options(), 'post_meta' => $this->post_meta, 'post_id' => $variation->get_id(), 'parent_id' => $variation->get_parent_id(), 'self' => $this, 'expiring_date_default_value' => $expiring_date_default_value, 'custom_attributes' => $this->get_field_attributes(), 'settings' => $this->settings]);
        }
    }
    /**
     * @return array
     */
    private function get_coupons_templates_options(): array
    {
        $items = [];
        $posts = get_posts(['post_type' => $this->editor_post_type, 'post_status' => 'publish', 'posts_per_page' => '-1']);
        foreach ($posts as $post) {
            $items[$post->ID] = $post->post_title;
        }
        return $items;
    }
    /**
     * @return array|string[]
     */
    public function get_field_attributes(): array
    {
        if (!$this->product_fields->is_premium()) {
            $attributes = ['readonly' => 'readonly', 'disabled' => 'disabled'];
        } else {
            $attributes = [];
        }
        return $attributes;
    }
}
