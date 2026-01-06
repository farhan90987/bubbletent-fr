<?php

/**
 * Integration. Register custom post type.
 *
 * @package WPDesk\FlexibleCouponsPDF
 */
namespace FlexibleCouponsProVendor\WPDesk\Library\WPCanvaEditor;

use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FlexibleCouponsProVendor\WPDesk\Library\WPCanvaEditor\EditorImplementation;
/**
 * Register custom post types for editor template.
 *
 * @package WPDesk\FlexibleCouponsPDF\Integration
 */
class RegisterPostType implements Hookable
{
    private EditorImplementation $editor;
    /**
     * @param EditorImplementation $editor
     */
    public function __construct($editor)
    {
        $this->editor = $editor;
    }
    /**
     * Fires hooks.
     */
    public function hooks()
    {
        \add_action('init', [$this, 'register_post_type_action']);
    }
    /**
     * Get post type args.
     *
     * @return array
     */
    protected function get_post_type_args()
    {
        $labels = ['name' => _x('Editor', 'post type general name', 'flexible-coupons-pro'), 'singular_name' => _x('Editor', 'post type singular name', 'flexible-coupons-pro'), 'menu_name' => _x('Editor', 'admin menu', 'flexible-coupons-pro'), 'name_admin_bar' => _x('Editor', 'add new on admin bar', 'flexible-coupons-pro')];
        $args = ['labels' => $labels, 'description' => __('Manage editor templates.', 'flexible-coupons-pro')];
        return \wp_parse_args($this->editor->post_type_args_definition(), $args);
    }
    /**
     * @return void
     */
    public function register_post_type_action()
    {
        \register_post_type($this->editor->get_post_type(), $this->get_post_type_args());
    }
}
