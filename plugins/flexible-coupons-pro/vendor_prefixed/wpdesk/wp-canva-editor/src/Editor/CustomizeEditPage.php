<?php

/**
 * Customize edit page.
 *
 * @package WPDesk\FlexibleCouponsPDF
 */
namespace FlexibleCouponsProVendor\WPDesk\Library\WPCanvaEditor;

use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
/**
 * Customize dashboard custom post edit page by remove some meta box, columns and notices.
 *
 * @package WPDesk\FlexibleCouponsPDF\Integration
 */
class CustomizeEditPage implements Hookable
{
    const META_BOX_CONTEXT_NORMAL = 'normal';
    const META_BOX_PRIORITY_HIGH = 'high';
    private const SCREEN_LAYOUT_COLUMNS = 1;
    /**
     * @var string
     */
    private $post_type_name;
    /**
     * @param string $post_type_name
     */
    public function __construct($post_type_name)
    {
        $this->post_type_name = $post_type_name;
    }
    /**
     * Fires hooks.
     */
    public function hooks()
    {
        $post_type = $this->post_type_name;
        add_action('add_meta_boxes', [$this, 'register_meta_boxes_action']);
        add_filter('enter_title_here', [$this, 'change_title_placeholder_for_advert_filter']);
        add_action('admin_enqueue_scripts', [$this, 'deregister_autosave_for_post_types_action']);
        add_action('admin_menu', [$this, 'remove_submitdiv_metabox_action']);
        add_filter('screen_layout_columns', [$this, 'screen_layout_columns'], 10, 2);
        add_filter('get_user_option_screen_layout_' . $post_type, [$this, 'set_single_layout_columns_filter']);
        add_filter('admin_head', [$this, 'deregister_admin_notice_hooks_filter'], 99999);
    }
    /**
     * Register meta boxes for template page edit.
     *
     * @return void
     */
    public function register_meta_boxes_action()
    {
        add_meta_box($this->post_type_name . '_editor_metabox', __('Editor', 'flexible-coupons-pro'), [$this, 'template_editor_callback'], $this->post_type_name, self::META_BOX_CONTEXT_NORMAL, self::META_BOX_PRIORITY_HIGH);
    }
    /**
     * @param \WP_Post $post
     */
    public function template_editor_callback(\WP_Post $post)
    {
        $post_id = $post->ID;
        $editor_data = get_post_meta($post->ID, EditorImplementation::EDITOR_POST_META, \true);
        $is_pl = 'pl_PL' === get_locale();
        $pro_url = $is_pl ? 'https://www.wpdesk.pl/sklep/flexible-coupons-woocommerce/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-pro' : 'https://wpdesk.net/products/flexible-coupons-woocommerce/?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-coupons-pro';
        require_once __DIR__ . '/Views/html-editor-meta-box.php';
    }
    /**
     * @return void
     */
    public function deregister_autosave_for_post_types_action()
    {
        if ($this->post_type_name === get_post_type()) {
            wp_dequeue_script('autosave');
        }
    }
    /**
     * @return void
     */
    public function remove_submitdiv_metabox_action()
    {
        remove_meta_box('submitdiv', $this->post_type_name, 'normal');
    }
    /**
     * @param string $placeholder_title Placeholder title.
     *
     * @return string
     */
    public function change_title_placeholder_for_advert_filter($placeholder_title)
    {
        $screen = get_current_screen();
        if (isset($screen->post_type) && $this->post_type_name === $screen->post_type) {
            $placeholder_title = esc_attr__('Enter template title', 'flexible-coupons-pro');
        }
        return $placeholder_title;
    }
    /**
     * @return int
     */
    public function set_single_layout_columns_filter()
    {
        return self::SCREEN_LAYOUT_COLUMNS;
    }
    /**
     * @param array $empty_columns
     * @param string $screen_id
     * @return array
     */
    public function screen_layout_columns($empty_columns, $screen_id)
    {
        if (!is_string($screen_id) || !$screen_id) {
            return $empty_columns;
        }
        return [$screen_id => self::SCREEN_LAYOUT_COLUMNS];
    }
    /**
     * @return void
     */
    public function deregister_admin_notice_hooks_filter()
    {
        $screen = get_current_screen();
        if ($this->post_type_name === $screen->id) {
            remove_all_actions('admin_notices');
        }
    }
}
