<?php

/**
 * Integration. Product page.
 *
 * @package WPDesk\Library\WPCoupons
 */
namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Product;

use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers\Plugin;
use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\ProductFields;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
/**
 * Save custom meta for simple product.
 *
 * @package WPDesk\Library\WPCoupons\Integration
 */
class SaveProductSimpleData implements Hookable
{
    const NONCE_NAME = 'flexible_coupons_nonce';
    const NONCE_ACTION = 'save_fields';
    const DEFAULT_COUPON_CODE = '';
    /**
     * @var ProductFields
     */
    private $product_fields;
    /**
     * @var PostMeta
     */
    private $post_meta;
    /**
     * @param ProductFields $product_fields Product fields.
     * @param PostMeta      $post_meta      Post meta container.
     */
    public function __construct(ProductFields $product_fields, PostMeta $post_meta)
    {
        $this->post_meta = $post_meta;
        $this->product_fields = $product_fields;
    }
    /**
     * Fires hooks.
     */
    public function hooks()
    {
        add_action('woocommerce_process_product_meta', [$this, 'save_product_coupons_field'], 10, 2);
    }
    /**
     * @param string $key
     * @param mixed $default
     *
     * @return string|string[]|null
     */
    public function post_data(string $key, $default = null)
    {
        if (isset($_REQUEST[$key])) {
            return \wc_clean(\wp_unslash($_REQUEST[$key]));
        }
        return $default;
    }
    /**
     * Save product data.
     *
     * @param int $product_id Product ID.
     */
    public function save_product_coupons_field(int $product_id)
    {
        if (isset($_POST[self::NONCE_NAME]) && \wp_verify_nonce(\sanitize_key(\wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION) && $product_id) {
            $this->save_public_fields($product_id);
            $this->save_premium_fields($product_id);
            /**
             * Fires after saving product data.
             *
             * @since 1.5.9
             */
            do_action('fc/core/product/simple/save', $product_id, $this);
        }
    }
    /**
     * @param int $product_id
     */
    private function save_public_fields(int $product_id)
    {
        $product_template = $this->post_data('fc_product_template', '');
        $expiring_date = $this->post_data('fc_expiring_date', 365);
        $expiring_date_own = $this->post_data('fc_expiring_date_own', 30);
        $product_ids = $this->post_data('fc_product_ids', []);
        $product_categories = $this->post_data('fc_product_categories', []);
        $free_shipping = $this->post_data('fc_product_free_shipping', 'no');
        $import_id = $this->post_data('_product_coupon_import_id', '');
        // Retrieve the import ID
        $this->post_meta->update_private($product_id, 'flexible_coupon_product_template', $product_template);
        $this->post_meta->update_private($product_id, 'flexible_coupon_expiring_date', $expiring_date);
        $this->post_meta->update_private($product_id, 'flexible_coupon_expiring_date_own', $expiring_date_own);
        $this->post_meta->update_private($product_id, 'flexible_coupon_product_ids', $product_ids);
        $this->post_meta->update_private($product_id, 'flexible_coupon_product_categories', $product_categories);
        $this->post_meta->update_private($product_id, 'flexible_coupon_product_free_shipping', $free_shipping);
        $this->post_meta->update_private($product_id, '_product_coupon_import_id', $import_id);
        // Save the import ID
    }
    /**
     * @param int $product_id
     */
    private function save_premium_fields(int $product_id)
    {
        if (!$this->product_fields->is_premium()) {
            return;
        }
        $remove_usage_limit = $this->post_data('fc_remove_usage_limit', 'no');
        $own_coupon_code = $this->post_data('fc_coupon_code', 'no');
        $coupon_code_length = $this->post_data('fc_coupon_code_length', 5);
        $coupon_code_prefix = $this->post_data('fc_coupon_code_prefix', self::DEFAULT_COUPON_CODE);
        $coupon_code_suffix = $this->post_data('fc_coupon_code_suffix', '');
        if (!empty($this->product_fields->get())) {
            foreach ($this->product_fields->get() as $id => $product_field) {
                $product_field_value = $this->post_data($id, 'no');
                $product_field_checked = $this->post_data($id . '_required', 'no');
                $this->post_meta->update_private($product_id, $id, $product_field_value);
                $this->post_meta->update_private($product_id, $id . '_required', $product_field_checked);
            }
        }
        $this->post_meta->update_private($product_id, 'flexible_coupon_remove_usage_limit', $remove_usage_limit);
        $this->post_meta->update_private($product_id, 'flexible_coupon_coupon_code', $own_coupon_code);
        $this->post_meta->update_private($product_id, 'flexible_coupon_coupon_code_length', $coupon_code_length);
        $this->post_meta->update_private($product_id, 'flexible_coupon_coupon_code_prefix', $coupon_code_prefix);
        $this->post_meta->update_private($product_id, 'flexible_coupon_coupon_code_suffix', $coupon_code_suffix);
    }
}
