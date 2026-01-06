<?php

/**
 * Integration. Save editor data by ajax.
 *
 * @package WPDesk\FlexibleCouponsPDF
 */
namespace FlexibleCouponsProVendor\WPDesk\Library\WPCanvaEditor;

use FlexibleCouponsProVendor\WPDesk\Library\WPCanvaEditor\Exceptions\EditorException;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
/**
 * Handle ajax action.
 *
 * @package WPDesk\FlexibleCouponsPDF\Integration
 */
class AjaxHandler implements Hookable
{
    /**
     * @var string
     */
    protected $post_type;
    /**
     * @param $post_type
     */
    public function __construct($post_type)
    {
        $this->post_type = $post_type;
    }
    /**
     * Fires hooks.
     */
    public function hooks()
    {
        add_action('wp_ajax_editor_save_post_' . $this->post_type, [$this, 'save_editor_data'], 2, 10);
    }
    /**
     * Save advert via ajax action
     */
    public function save_editor_data()
    {
        check_ajax_referer('editor_save_post_' . $this->post_type, 'security');
        try {
            $post_id = $this->save_template_data();
            wp_send_json_success(['message' => __('Saved', 'flexible-coupons-pro'), 'post_id' => $post_id]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage(), 'post_id' => 0]);
        }
    }
    /**
     * Validate coupon.
     *
     * @param array $editor_data
     *
     * @return bool
     */
    protected function validate_data_from_editor($editor_data)
    {
        if (isset($editor_data['areaObjects'])) {
            foreach ($editor_data['areaObjects'] as $object) {
                if (preg_match('/\[coupon_code]/i', $object['text'])) {
                    return \true;
                }
            }
        }
        return \false;
    }
    /**
     * @return int
     *
     * @throws EditorException Throw exception when data is not valid or cannot saving.
     */
    protected function save_template_data()
    {
        $post_id = (int) $this->get_request_value('post_id');
        $post_title = $this->get_request_value('post_title');
        $editor_data = $this->get_request_value('editor_data');
        if ($post_id) {
            $post_args = ['ID' => $post_id ? $post_id : null, 'post_title' => $post_title, 'post_name' => sanitize_title($post_title), 'post_status' => 'publish', 'post_type' => $this->post_type];
            try {
                $post_id = wp_update_post($post_args);
                update_post_meta($post_id, EditorImplementation::EDITOR_POST_META, $editor_data);
                if (empty($post_title)) {
                    throw new EditorException(__('Enter template title', 'flexible-coupons-pro'));
                }
                if (is_array($editor_data)) {
                    if (!$this->validate_data_from_editor($editor_data)) {
                        throw new EditorException(__('Template does not contain [coupon_code] shortcode', 'flexible-coupons-pro'));
                    }
                }
                return $post_id;
            } catch (\Exception $e) {
                throw new EditorException('WordPress: ' . $e->getMessage());
            }
        }
        throw new EditorException(__('Data cannot be saved', 'flexible-coupons-pro'));
    }
    /**
     * Get value from $_POST.
     *
     * @param string $name
     * @param string $default
     *
     * @return string
     */
    protected function get_request_value($name, $default = '')
    {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        if (isset($_POST[$name])) {
            return \wc_clean(\wp_unslash($_POST[$name]));
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
        return $default;
    }
}
