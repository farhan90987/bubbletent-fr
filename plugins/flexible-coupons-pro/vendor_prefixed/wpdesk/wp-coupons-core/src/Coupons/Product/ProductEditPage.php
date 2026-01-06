<?php

/**
 * Integration. Product page.
 *
 * @package WPDesk\Library\WPCoupons
 */
namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Product;

use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\ProductFields;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
use FlexibleCouponsProVendor\WPDesk\Persistence\Adapter\WordPress\WordpressOptionsContainer;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FlexibleCouponsProVendor\WPDesk\View\Renderer\Renderer;
/**
 * Add custom fields to admin product edit page.
 *
 * @package WPDesk\Library\WPCoupons\Integration
 */
class ProductEditPage implements Hookable
{
    const NONCE_NAME = 'flexible_coupons_nonce';
    const NONCE_ACTION = 'save_fields';
    const PRODUCT_COUPON_SLUG = 'wpdesk_pdf_coupons';
    const TAB_KEY = 'pdfcoupons';
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
        add_filter('product_type_options', [$this, 'add_product_type_filter']);
        add_action('save_post_product', [$this, 'update_product_type'], 10);
        add_action('woocommerce_product_data_tabs', [$this, 'add_product_tab_action']);
        add_action('woocommerce_product_data_panels', [$this, 'add_product_general_data_field']);
    }
    /**
     * Add custom tab to edit product page.
     *
     * @param $tabs
     *
     * @return array
     */
    public function add_product_tab_action($tabs): array
    {
        foreach ($tabs as $tab_id => $tab) {
            $new_tabs[$tab_id] = $tab;
            if ($tab_id === 'general') {
                $new_tabs[self::TAB_KEY] = ['label' => esc_html__('PDF Coupon', 'flexible-coupons-pro'), 'target' => 'pdfcoupon_product_data', 'class' => ['hide_if_grouped', 'hide_if_external', 'hide_if_coupon_disabled', 'hide'], 'priority' => 12];
            }
        }
        return $new_tabs;
    }
    /**
     * Add coupons fields to pdf coupon tab.
     */
    public function add_product_general_data_field()
    {
        global $post;
        $expiring_date_default_value = !$this->product_fields->is_premium() ? 365 : 7;
        $this->renderer->output_render('html-product-general-settings', ['renderer' => $this->renderer, 'nonce_name' => self::NONCE_NAME, 'nonce_action' => self::NONCE_ACTION, 'is_premium' => $this->product_fields->is_premium(), 'product_fields' => $this->product_fields, 'product_templates' => $this->get_coupons_templates_options(), 'post_meta' => $this->post_meta, 'post_id' => $post->ID, 'self' => $this, 'expiring_date_default_value' => $expiring_date_default_value, 'custom_attributes' => $this->get_field_attributes(), 'settings' => $this->settings]);
    }
    /**
     * Add product type. Like virtual.
     *
     * @param array $types Product types.
     *
     * @return array
     */
    public function add_product_type_filter(array $types): array
    {
        $types['wpdesk_pdf_coupons'] = ['id' => self::PRODUCT_COUPON_SLUG, 'wrapper_class' => 'show_if_simple', 'label' => esc_html__('PDF Coupon', 'flexible-coupons-pro'), 'description' => esc_html__('Convert this product to PDF Coupon', 'flexible-coupons-pro'), 'default' => 'no'];
        return $types;
    }
    /**
     * Update product type.
     *
     * @param int $product_id Product ID.
     */
    public function update_product_type(int $product_id)
    {
        if (isset($_POST[self::NONCE_NAME]) && \wp_verify_nonce(\sanitize_key(\wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
            $this->post_meta->update_private($product_id, self::PRODUCT_COUPON_SLUG, isset($_POST[self::PRODUCT_COUPON_SLUG]) ? 'yes' : 'no');
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
