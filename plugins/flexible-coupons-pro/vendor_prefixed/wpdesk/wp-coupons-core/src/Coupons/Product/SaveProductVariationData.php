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
 * Save custom meta for product variation.
 *
 * @package WPDesk\Library\WPCoupons\Integration
 */
class SaveProductVariationData implements Hookable
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
        add_action('woocommerce_save_product_variation', [$this, 'save_product_coupons_field'], 10, 2);
    }
    /**
     * Save product data.
     *
     * @param $variation_id
     * @param $i
     */
    public function save_product_coupons_field($variation_id, $i)
    {
        if (isset($_POST[self::NONCE_NAME]) && \wp_verify_nonce(\sanitize_key(\wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
            $variation_id = (int) $variation_id;
            $base_on = $this->post_data('fc_variation_base_on_variation', $i, '');
            $disable_pdf = $this->post_data('fc_disable_pdf_variation', $i, '');
            if ($variation_id && $base_on === 'yes' && $disable_pdf !== 'yes') {
                $this->save_public_fields($variation_id, $i);
                $this->save_premium_fields($variation_id, $i);
                /**
                 * Fires after saving variation data.
                 *
                 * @since 1.5.9
                 */
                do_action('fc/core/product/variation/save', $variation_id, $i, $this);
            }
            $this->post_meta->update_private($variation_id, 'flexible_coupon_disable_pdf', $disable_pdf);
            $this->post_meta->update_private($variation_id, 'flexible_coupon_variation_base_on', $base_on);
        }
    }
    /**
     * @param string $key
     * @param int    $i
     * @param null   $default
     *
     * @return string|string[]|null
     */
    public function post_data(string $key, int $i, $default = null)
    {
        if (isset($_REQUEST[$key][$i])) {
            return \wc_clean(\wp_unslash($_REQUEST[$key][$i]));
        }
        return $default;
    }
    /**
     * @param int $variation_id
     * @param     $i
     */
    private function save_public_fields(int $variation_id, $i)
    {
        $product_template = $this->post_data('fc_product_template_variation', $i, '');
        $expiring_date = $this->post_data('fc_expiring_date_variation', $i, 365);
        $expiring_date_own = $this->post_data('fc_expiring_date_own_variation', $i, 30);
        $variation_ids = $this->post_data('fc_product_ids_variation', $i, []);
        $product_categories = $this->post_data('fc_product_categories_variation', $i, []);
        $free_shipping = $this->post_data('fc_product_free_shipping_variation', $i, 'no');
        $this->post_meta->update_private($variation_id, 'flexible_coupon_product_template', $product_template);
        $this->post_meta->update_private($variation_id, 'flexible_coupon_expiring_date', $expiring_date);
        $this->post_meta->update_private($variation_id, 'flexible_coupon_expiring_date_own', $expiring_date_own);
        $this->post_meta->update_private($variation_id, 'flexible_coupon_product_ids', $variation_ids);
        $this->post_meta->update_private($variation_id, 'flexible_coupon_product_categories', $product_categories);
        $this->post_meta->update_private($variation_id, 'flexible_coupon_product_free_shipping', $free_shipping);
    }
    /**
     * @param int $variation_id
     * @param     $i
     */
    private function save_premium_fields(int $variation_id, $i)
    {
        if (!$this->product_fields->is_premium()) {
            return;
        }
        $remove_usage_limit = $this->post_data('fc_remove_usage_limit_variation', $i, 'no');
        $own_coupon_code = $this->post_data('fc_coupon_code_variation', $i, 'no');
        $coupon_code_length = $this->post_data('fc_coupon_code_length_variation', $i, 5);
        $coupon_code_prefix = $this->post_data('fc_coupon_code_prefix_variation', $i, self::DEFAULT_COUPON_CODE);
        $coupon_code_suffix = $this->post_data('fc_coupon_code_suffix_variation', $i, '');
        if (!empty($this->product_fields->get())) {
            foreach ($this->product_fields->get() as $id => $product_field) {
                $product_field_value = $this->post_data($id . '_variation', $i, 'no');
                $product_field_checked = $this->post_data($id . '_required_variation', $i, 'no');
                $this->post_meta->update_private($variation_id, $id, $product_field_value);
                $this->post_meta->update_private($variation_id, $id . '_required', $product_field_checked);
            }
        }
        $this->post_meta->update_private($variation_id, 'flexible_coupon_remove_usage_limit', $remove_usage_limit);
        $this->post_meta->update_private($variation_id, 'flexible_coupon_coupon_code', $own_coupon_code);
        $this->post_meta->update_private($variation_id, 'flexible_coupon_coupon_code_length', $coupon_code_length);
        $this->post_meta->update_private($variation_id, 'flexible_coupon_coupon_code_prefix', $coupon_code_prefix);
        $this->post_meta->update_private($variation_id, 'flexible_coupon_coupon_code_suffix', $coupon_code_suffix);
    }
}
