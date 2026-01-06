<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Defaults;

use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers\EmailStrings;
/**
 * Handles AJAX requests for default email templates when the Sending addon is not active.
 */
class DefaultEmailTemplateAjax implements Hookable
{
    const AJAX_ACTION = 'fc_email_template_get_all';
    public function hooks()
    {
        add_action('wp_ajax_' . self::AJAX_ACTION, [$this, 'handle_get_defaults']);
    }
    /**
     * @return void
     */
    public function handle_get_defaults()
    {
        $this->verify_request();
        // hardcoded default template data.
        $default_templates = [['id' => 0, 'name' => __('Default Coupon Email', 'flexible-coupons-pro'), 'subject' => EmailStrings::get_default_email_subject(), 'recipient' => '', 'content' => EmailStrings::get_default_email_body(), 'enabled' => \false, 'is_default' => \false]];
        wp_send_json_success($default_templates);
    }
    private function verify_request(): void
    {
        check_ajax_referer('fc-email-templates-nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized', 'flexible-coupons-pro'), 403);
        }
    }
}
