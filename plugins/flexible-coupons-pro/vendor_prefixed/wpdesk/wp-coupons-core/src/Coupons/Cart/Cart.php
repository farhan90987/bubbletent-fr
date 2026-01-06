<?php

/**
 * Integration. Cart.
 *
 * @package WPDesk\Library\WPCoupons
 */
namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Cart;

use Exception;
use FlexibleCouponsProVendor\Psr\Log\LoggerInterface;
use WC_Order_Item;
use WC_Order_Item_Product;
use WC_Product;
use WC_Product_Simple;
use WC_Product_Variable;
use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\ProductFields;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration\PostMeta;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Product\ProductEditPage;
use FlexibleCouponsProVendor\WPDesk\Persistence\Adapter\WordPress\WordpressOptionsContainer;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
/**
 * Integrate coupons with WooCommerce shop cart by adding custom field with validation.
 *
 * @package WPDesk\Library\WPCoupons\Cart
 */
class Cart implements Hookable
{
    const PRODUCT_TYPE_GROUPED = 'grouped';
    const PRODUCT_TYPE_VARIABLE = 'variable';
    const PRODUCT_TYPE_VARIATION = 'variation';
    const PRODUCT_FIELDS_POSITION = 'below';
    /**
     * @var ProductFields
     */
    private $product_fields;
    /**
     * @var PostMeta
     */
    private $post_meta;
    /**
     * @var WordpressOptionsContainer
     */
    private $settings;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @param ProductFields $product_fields Product fields.
     * @param PostMeta $post_meta Product post meta.
     * @param WordpressOptionsContainer $settings Plugin settings.
     */
    public function __construct(ProductFields $product_fields, PostMeta $post_meta, WordpressOptionsContainer $settings, LoggerInterface $logger)
    {
        $this->product_fields = $product_fields;
        $this->post_meta = $post_meta;
        $this->settings = $settings;
        $this->logger = $logger;
    }
    /**
     * Fires hooks.
     */
    public function hooks()
    {
        if (!empty($this->product_fields)) {
            $position = $this->settings->get_fallback('coupon_product_position', self::PRODUCT_FIELDS_POSITION);
            if ($position === self::PRODUCT_FIELDS_POSITION) {
                add_action('woocommerce_after_add_to_cart_button', [$this, 'add_fields_to_product_action']);
            } else {
                add_action('woocommerce_before_add_to_cart_button', [$this, 'add_fields_to_product_action']);
            }
            add_filter('woocommerce_add_to_cart_validation', [$this, 'add_to_cart_validation_filter'], 30, 3);
            add_action('woocommerce_new_order_item', [$this, 'new_order_item_action'], 10, 2);
            add_filter('woocommerce_add_cart_item_data', [$this, 'add_cart_item_data_filter'], 30, 3);
            add_filter('woocommerce_get_item_data', [$this, 'get_item_data_filter'], 30, 3);
            add_filter('woocommerce_order_item_display_meta_key', [$this, 'replace_item_meta_key_filter'], 20, 3);
            add_action('wp_ajax_get_variation_fields', [$this, 'get_variation_fields']);
            add_action('wp_ajax_nopriv_get_variation_fields', [$this, 'get_variation_fields']);
        }
    }
    /**
     * Replace item meta keys.
     *
     * @param string $key Key.
     * @param object $meta Meta.
     * @param WC_Order_Item $item Item Product.
     *
     * @return mixed
     */
    public function replace_item_meta_key_filter($key, $meta, $item): string
    {
        if ($item instanceof WC_Order_Item_Product) {
            $fields = $this->get_fields($item->get_product_id());
            if (isset($fields[$meta->key]['title'])) {
                return $fields[$meta->key]['title'];
            }
        }
        return $key;
    }
    /**
     * Is voucher type.
     *
     * @param WC_Product $product Product.
     *
     * @return bool
     */
    private function is_voucher_type(WC_Product $product): bool
    {
        $product_id = $product->get_id();
        if ($product->is_type(self::PRODUCT_TYPE_VARIATION)) {
            $product_id = $product->get_parent_id();
        }
        return 'yes' === $this->post_meta->get_private($product_id, ProductEditPage::PRODUCT_COUPON_SLUG);
    }
    /**
     * Checks if product is disabled. Variable product only.
     *
     * @param WC_Product $product
     *
     * @return boolean
     */
    private function is_disabled(WC_Product $product): bool
    {
        if (!$product->is_type(self::PRODUCT_TYPE_VARIATION)) {
            return \false;
        }
        return 'yes' === $this->post_meta->get_private($product->get_ID(), 'flexible_coupon_disable_pdf', 'no');
    }
    /**
     * @param int $product_id Product ID.
     *
     * @return array
     */
    private function get_fields(int $product_id): array
    {
        $fields = [];
        if ($this->product_fields->is_premium()) {
            foreach ($this->product_fields->get() as $id => $product_field) {
                $is_enabled = 'yes' === $this->post_meta->get_private($product_id, 'product_' . $id, 'yes');
                if ($is_enabled) {
                    $fields[$id] = $product_field;
                }
            }
        }
        return $fields;
    }
    /**
     * @param int $product_id
     *
     * @return WC_Product
     * @throws Exception Invalid product type.
     */
    private function get_product_type(int $product_id): WC_Product
    {
        $product = wc_get_product($product_id);
        if ($product->is_type(self::PRODUCT_TYPE_GROUPED)) {
            return $product;
        }
        if ($product->is_type(self::PRODUCT_TYPE_VARIABLE)) {
            $default_attributes = $product->get_default_attributes();
            $atrribute_keys = array_keys($default_attributes);
            if (isset($atrribute_keys[0])) {
                $attribute_name = str_replace('pa_', '', $atrribute_keys[0]);
                $attribute_key = $atrribute_keys[0];
                $attribute_value = $default_attributes[$attribute_key];
                foreach ($product->get_children() as $children_id) {
                    $children = wc_get_product($children_id);
                    $children_attr_value = $children->get_attributes($attribute_name);
                    if ($attribute_value === $children_attr_value) {
                        return $children;
                    }
                }
            }
        }
        return $product;
    }
    /**
     * @param string $html
     *
     * @return string
     */
    private function field_wrapper(string $html = ''): string
    {
        return '<div class="pdf-coupon-fields" style="clear: both;">' . $html . '</div>';
    }
    /**
     * Add to cart action.
     */
    public function add_fields_to_product_action()
    {
        global $product;
        $own_product = $product;
        try {
            $own_product = $this->get_product_type($own_product->get_id());
            $variation_id = isset($_POST['variation_id']) ? (int) $_POST['variation_id'] : 0;
            // phpcs:ignore WordPress.Security.NonceVerification.Missing
            if ($variation_id) {
                $own_product = \wc_get_product($variation_id);
            }
            // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
            // content should already be escaped.
            if ($variation_id && $own_product instanceof WC_Product_Variable) {
                echo $this->field_wrapper($this->get_fields_as_html($own_product));
            } elseif ($own_product instanceof WC_Product_Simple) {
                echo $this->field_wrapper($this->get_fields_as_html($own_product));
            } else {
                echo $this->field_wrapper();
            }
            // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), ['product_id' => $own_product->get_id()]);
        }
    }
    /**
     * @param array $field
     *
     * @return array
     */
    private function field_defaults(array $field): array
    {
        $defaults = ['type' => 'text', 'label' => '', 'description' => '', 'placeholder' => '', 'maxlength' => \false, 'required' => \false, 'autocomplete' => \false, 'id' => '', 'class' => [], 'label_class' => [], 'input_class' => [], 'return' => \true, 'options' => [], 'custom_attributes' => [], 'validate' => [], 'default' => '', 'autofocus' => '', 'priority' => ''];
        return (array) wp_parse_args($field, $defaults);
    }
    /**
     * @param int $variation_id
     *
     * @return bool
     */
    private function has_variation_own_settings(int $variation_id): bool
    {
        return 'yes' === $this->post_meta->get_private($variation_id, 'flexible_coupon_variation_base_on');
    }
    /**
     * Ajax action for display variation fields.
     */
    public function get_variation_fields()
    {
        if (!check_ajax_referer('fc-security-nonce', 'security', \false)) {
            wp_send_json_error(['error' => 'Invalid security token sent.']);
        }
        $_post = $_POST;
        $variation_id = isset($_post['variation_id']) ? (int) $_post['variation_id'] : 0;
        $product = wc_get_product($variation_id);
        $has_own_settings = $this->has_variation_own_settings($variation_id);
        if (!$has_own_settings) {
            $product = wc_get_product($product->get_parent_id());
        }
        if (!$this->is_voucher_type($product)) {
            wp_send_json_error('This is not a coupon product.');
        }
        if ($product) {
            try {
                $html = $this->get_fields_as_html($product);
                wp_send_json_success($html);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage(), ['product_id' => $product->get_id()]);
                wp_send_json_error($e->getMessage());
            }
        }
        wp_send_json_error('Unknown error');
    }
    /**
     * Show fields.
     *
     * @param WC_Product $product
     *
     * @return string
     */
    private function get_fields_as_html(WC_Product $product): string
    {
        $output = '';
        if ($this->is_voucher_type($product) && !$this->is_disabled($product)) {
            $data = $_POST;
            //phpcs:ignore
            foreach ($this->get_fields($product->get_id()) as $field) {
                if ('no' === $this->post_meta->get_private($product->get_id(), $field['id'], 'yes')) {
                    continue;
                }
                $field = $this->field_defaults($field);
                $product_required = $this->post_meta->get_private($product->get_id(), $field['id'] . '_required');
                if ($product_required) {
                    $field['required'] = 'yes' === $product_required;
                    $field['custom_attributes']['required'] = 'yes' === $product_required;
                }
                if (isset($field['validation']['minlength']) && (int) $field['validation']['minlength'] > 0) {
                    $field['minlength'] = $field['validation']['minlength'];
                    $field['custom_attributes']['data-description'] = sprintf($field['custom_attributes']['data-description'], (int) $field['validation']['minlength']);
                }
                if (isset($field['validation']['maxlength']) && (int) $field['validation']['maxlength'] > 0) {
                    $field['maxlength'] = $field['validation']['maxlength'];
                }
                $field['label'] = $field['title'] ?? '';
                $output .= woocommerce_form_field(
                    $field['name'] ?? $field['id'],
                    $field,
                    // TODO: $data[ $field['id'] ] ?? ''
                    ''
                );
            }
        }
        ob_start();
        $coupon_tips = $this->settings->get_fallback('coupon_tips', \false);
        if ($coupon_tips === 'yes') {
            ?>
			<style>
				.pdf-coupon-fields > p {
					position: relative;
				}

				.tooltip {
					display: inline-block;
					position: absolute;
					right: 0;
					top: -2px;
					cursor: pointer;
				}

				.tooltip .tooltiptext {
					visibility: hidden;
					width: 120px;
					background-color: black;
					color: #fff;
					text-align: center;
					border-radius: 3px;
					padding: 8px;

					/* Position the tooltip */
					position: absolute;
					right: 20px;
					z-index: 1;
					font-size: 14px;
				}

				.tooltip:hover .tooltiptext {
					visibility: visible;
				}
			</style>
			<script>
				(function ($) {
					let field_wrapper = $('.pdf-coupon-fields');
					if (field_wrapper.length) {
						let fields = field_wrapper.find('input,textarea');
						fields.each(function () {
							let description = $(this).attr('data-description');
							if (description) {
								$(this).before('<div class="tooltip"><svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><path d="M256 76c48.1 0 93.3 18.7 127.3 52.7S436 207.9 436 256s-18.7 93.3-52.7 127.3S304.1 436 256 436c-48.1 0-93.3-18.7-127.3-52.7S76 304.1 76 256s18.7-93.3 52.7-127.3S207.9 76 256 76m0-28C141.1 48 48 141.1 48 256s93.1 208 208 208 208-93.1 208-208S370.9 48 256 48z"></path><path d="M256.7 160c37.5 0 63.3 20.8 63.3 50.7 0 19.8-9.6 33.5-28.1 44.4-17.4 10.1-23.3 17.5-23.3 30.3v7.9h-34.7l-.3-8.6c-1.7-20.6 5.5-33.4 23.6-44 16.9-10.1 24-16.5 24-28.9s-12-21.5-26.9-21.5c-15.1 0-26 9.8-26.8 24.6H192c.7-32.2 24.5-54.9 64.7-54.9zm-26.3 171.4c0-11.5 9.6-20.6 21.4-20.6 11.9 0 21.5 9 21.5 20.6s-9.6 20.6-21.5 20.6-21.4-9-21.4-20.6z"></path></svg><span class="tooltiptext">' + description + '</span></div>');
							}
						})
					}
				})(jQuery);
			</script>
			<?php 
        }
        $coupon_textarea_counter = $this->settings->get_fallback('coupon_textarea_counter', \false);
        if ($coupon_textarea_counter === 'yes') {
            ?>
			<script>
				(function ($) {
					let textarea = $('#flexible_coupon_recipient_message');
					if (textarea.length) {
						let textarea_maxlength = textarea.attr('maxlength');
						textarea.after('<span class="fc-counter-wrapper"><span class="fc-counter-current">0</span>/<span class="max">' + textarea_maxlength + '</span></span>');
						textarea.on('input', function () {
							var maxlength = $(this).attr("maxlength");
							var currentLength = $(this).val().length;
							$('.fc-counter-current').html(currentLength);
							if (currentLength >= maxlength) {
								return false;
							}
						})
						let current_length = textarea.val().length;
						$('.fc-counter-current').html(current_length);
					}

				})(jQuery);
			</script>
			<style>
				span.fc-counter-wrapper {
					text-align: right;
					display: block;
					padding: 2px;
					margin-bottom: 2px;
				}
			</style>
			<?php 
        }
        $scripts = ob_get_clean();
        return $output . $scripts;
    }
    /**
     * @param bool $passed Is passed.
     * @param int $product_id Product ID.
     * @param int $qty Stock.
     * @param array|null $post_data Post data.
     *
     * @return bool
     */
    public function add_to_cart_validation_filter($passed, $product_id, $qty, $post_data = null): bool
    {
        try {
            if (is_null($post_data)) {
                $post_data = \wp_unslash($_POST);
                // phpcs:ignore WordPress.Security.NonceVerification.Missing
            }
            $variation_id = isset($post_data['variation_id']) ? (int) $post_data['variation_id'] : 0;
            if ($variation_id) {
                $product_id = $variation_id;
            }
            $product = $this->get_product_type($product_id);
            $has_own_settings = $this->has_variation_own_settings($variation_id);
            if (!$has_own_settings && $product->get_type() === 'variation') {
                $product_id = $product->get_parent_id();
            }
            if ($this->is_voucher_type($product) && !$this->is_disabled($product)) {
                $fields = $this->get_fields($product_id);
                foreach ($fields as $field) {
                    if ('no' === $this->post_meta->get_private($product_id, $field['id'], 'yes')) {
                        continue;
                    }
                    $value = null;
                    if (isset($post_data[$field['id']])) {
                        if (isset($field['type']) && 'textarea' === $field['type']) {
                            $value = sanitize_textarea_field($post_data[$field['id']]);
                        } else {
                            $value = sanitize_text_field($post_data[$field['id']]);
                        }
                    }
                    try {
                        $this->validate_field($product_id, $field, $value);
                    } catch (Exception $e) {
                        wc_add_notice($e->getMessage(), 'error');
                        $passed = \false;
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), ['product_id' => $product_id]);
        } finally {
            return $passed;
        }
    }
    /**
     * Validate product fields before add to cart.
     *
     * @param int $product_id Product ID.
     * @param array $field Field.
     * @param mixed $value Value.
     *
     * @throws Exception Throw exception on error.
     */
    private function validate_field($product_id, array $field, $value)
    {
        $is_required = \false;
        try {
            $product = $this->get_product_type($product_id);
            $required = $this->post_meta->get_private($product->get_id(), $field['id'] . '_required');
            if (empty($required)) {
                $is_required = $field['required'];
                // This option is defined in field declaration.
            } else {
                $is_required = $required === 'yes';
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), ['product_id' => $product_id, 'fields' => $field]);
        }
        if ($is_required) {
            $this->should_throw_exception_is_empty($field, $value);
        }
        if (!empty($value)) {
            $this->should_throw_exception_for_minlength($field, $value);
            $this->should_throw_exception_for_maxlength($field, $value);
            $this->should_throw_exception_for_email($field, $value);
            $this->should_throw_exception_for_invalid_date($field, $value);
            $this->should_throw_exception_for_past_date($field, $value);
        }
    }
    /**
     * Do not move the validation methods to a separate class! This will not work from the main page.
     *
     * @throws Exception Throw error exception.
     */
    private function should_throw_exception_is_empty($field, $value)
    {
        if (empty($value)) {
            // translators: field title.
            throw new Exception(sprintf(__('<strong>%s</strong>  is required field.', 'flexible-coupons-pro'), $field['title']));
        }
    }
    /**
     * @throws Exception Throw error exception.
     */
    private function should_throw_exception_for_minlength($field, $value)
    {
        if (isset($field['validation']['minlength']) && mb_strlen(trim($value)) < (int) $field['validation']['minlength']) {
            // translators: field title.
            throw new Exception(sprintf(__('<strong>%1$s</strong>  is to short. Minimum length: %2$d.', 'flexible-coupons-pro'), $field['title'], $field['validation']['minlength']));
        }
    }
    /**
     * @throws Exception Throw error exception.
     */
    private function should_throw_exception_for_maxlength($field, $value)
    {
        if (isset($field['validation']['maxlength']) && mb_strlen(trim($value)) > (int) $field['validation']['maxlength']) {
            // translators: field title.
            throw new Exception(sprintf(__('<strong>%1$s</strong>  is to long. Maximum length: %2$d.', 'flexible-coupons-pro'), $field['title'], $field['validation']['maxlength']));
        }
    }
    /**
     * @throws Exception Throw error exception.
     */
    private function should_throw_exception_for_email($field, $value)
    {
        if (isset($field['validation']['email']) && !is_email($value)) {
            // translators: field title.
            throw new Exception(sprintf(__('<strong>%s</strong>  has invalid email.', 'flexible-coupons-pro'), $field['title']));
        }
    }
    /**
     * @throws Exception Throw error exception.
     */
    private function should_throw_exception_for_invalid_date($field, $value)
    {
        if (isset($field['validation']['date']) && strtotime($value) === \false) {
            // translators: field title.
            throw new Exception(sprintf(__('<strong>%s</strong> has an invalid date.', 'flexible-coupons-pro'), $field['title']));
        }
    }
    /**
     * @throws Exception Throw error exception.
     */
    private function should_throw_exception_for_past_date($field, $value)
    {
        if (isset($field['validation']['past_date']) && strtotime($value) < \time()) {
            // translators: field title.
            throw new Exception(sprintf(__('<strong>%s</strong> has a date from the past.', 'flexible-coupons-pro'), $field['title']));
        }
    }
    /**
     * Save product fields.
     *
     * @param int $item_id Item ID.
     * @param WC_Order_Item $item Item.
     *
     * @throws Exception
     */
    public function new_order_item_action($item_id, $item)
    {
        // @phpstan-ignore-line
        if ($item instanceof WC_Order_Item_Product && !empty($item->legacy_values) && !empty($item->legacy_values['flexible_coupons'])) {
            // @phpstan-ignore-line
            foreach ($item->legacy_values['flexible_coupons'] as $field) {
                // @phpstan-ignore-line
                $name = $field['id'];
                wc_add_order_item_meta($item_id, $name, $field['value']);
            }
        }
    }
    /**
     * Add cart item data.
     *
     * @param array $cart_item_data Cart item data.
     * @param int $product_id Product ID.
     * @param int $variation_id Variation ID.
     *
     * @return array
     */
    public function add_cart_item_data_filter($cart_item_data, $product_id, $variation_id)
    {
        try {
            if ($variation_id) {
                $product_id = $variation_id;
            }
            $product = $this->get_product_type($product_id);
            if (!$this->is_voucher_type($product) || $this->is_disabled($product)) {
                return $cart_item_data;
            }
            $fields = $this->get_fields($product->get_id());
            $post_data = wp_unslash($_POST);
            //phpcs:ignore
            $fields = apply_filters('flexible_coupons_apply_logic_rules', $fields, $post_data);
            $clone_items_data = $this->get_clone_items_data($post_data);
            $cart_item_data['flexible_coupons_multiple_pdfs'] = $clone_items_data;
            foreach ($fields as $field) {
                if ($this->is_field_disabled($product, $field)) {
                    continue;
                }
                if (!isset($cart_item_data['flexible_coupons'])) {
                    $cart_item_data['flexible_coupons'] = [];
                }
                if (isset($post_data[$field['id']])) {
                    $field['value'] = wp_strip_all_tags($post_data[$field['id']]);
                    $should_add_field = \true;
                    foreach ($cart_item_data['flexible_coupons'] as $defined_field) {
                        if ($defined_field['id'] === $field['id']) {
                            $should_add_field = \false;
                            break;
                        }
                    }
                    if ($should_add_field) {
                        $cart_item_data['flexible_coupons'][] = $field;
                    }
                }
            }
            return $cart_item_data;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $cart_item_data;
    }
    private function is_field_disabled(\WC_Product $product, $field): bool
    {
        return 'no' === $this->post_meta->get_private($product->get_id(), $field['id'], 'yes');
    }
    private function get_clone_items_data($post_data): array
    {
        $clone_items_data = [];
        if (isset($post_data['flexible_coupon_recipient_name-clone'])) {
            foreach ($post_data['flexible_coupon_recipient_name-clone'] as $key => $value) {
                $clone_items_data[$key]['flexible_coupon_recipient_name'] = $value;
            }
        }
        if (isset($post_data['flexible_coupon_recipient_email-clone'])) {
            foreach ($post_data['flexible_coupon_recipient_email-clone'] as $key => $value) {
                $clone_items_data[$key]['flexible_coupon_recipient_email'] = $value;
            }
        }
        if (isset($post_data['flexible_coupon_recipient_message-clone'])) {
            foreach ($post_data['flexible_coupon_recipient_message-clone'] as $key => $value) {
                $clone_items_data[$key]['flexible_coupon_recipient_message'] = $value;
            }
        }
        return $clone_items_data;
    }
    /**
     * Display values defined product fields in cart and checkout.
     *
     * @param array $item_data Other data.
     * @param array $cart_item Cart item.
     *
     * @return array
     */
    public function get_item_data_filter($item_data, $cart_item): array
    {
        try {
            $product_id = (int) $cart_item['product_id'];
            $variation_id = (int) $cart_item['variation_id'];
            if ($variation_id) {
                $product_id = $variation_id;
            }
            $product = $this->get_product_type($product_id);
            if (!$this->is_voucher_type($product) || $this->is_disabled($product)) {
                return $item_data;
            }
            $fields = $this->get_fields($product->get_id());
            if (!empty($cart_item['flexible_coupons'])) {
                foreach ($cart_item['flexible_coupons'] as $field) {
                    if (!isset($field['id']) || $this->is_field_disabled($product, $field)) {
                        continue;
                    }
                    $name = $fields[$field['id']]['title'] ?? '';
                    $value = $field['value'];
                    if (!empty($value)) {
                        $item_data[] = ['name' => $name, 'value' => wp_strip_all_tags($field['value']), 'display' => $field['display'] ?? ''];
                    }
                }
            }
            return $item_data;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $item_data;
    }
}
