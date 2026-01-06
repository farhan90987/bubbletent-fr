<?php

/**
 * Sample templates.
 *
 * @package WPDesk\FlexibleCouponsPro
 */
namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Integration;

/**
 * Creates sample templates for editor.
 *
 * @package WPDesk\FlexibleCouponsPro
 */
class SampleTemplates
{
    const EDITOR_AREA_OBJECTS_KEY = 'areaObjects';
    /**
     * @var string
     */
    private $post_type_name;
    /**
     * @param string $post_type_name
     */
    public function __construct(string $post_type_name)
    {
        $this->post_type_name = $post_type_name;
    }
    /**
     * @return string
     */
    private function get_assets_images_url(): string
    {
        return trailingslashit(plugin_dir_url(__DIR__)) . 'assets/images/';
    }
    /**
     * @return string
     */
    private function get_templates_dir(): string
    {
        return trailingslashit(plugin_dir_path(__DIR__)) . 'Templates/';
    }
    /**
     * @param array $template
     *
     * @return array
     */
    private function find_and_replace_shortcodes(array $template): array
    {
        if (isset($template[self::EDITOR_AREA_OBJECTS_KEY])) {
            foreach ($template[self::EDITOR_AREA_OBJECTS_KEY] as $object_id => $object) {
                if (isset($object['url'])) {
                    $object['url'] = str_replace('{plugin_assets_url}', $this->get_assets_images_url(), $object['url']);
                }
                $template[self::EDITOR_AREA_OBJECTS_KEY][$object_id] = $object;
            }
        }
        return $template;
    }
    /**
     * Create sample templates posts after plugin activation.
     */
    public function create()
    {
        if (get_option('flexible_coupons_sample_templates') !== 'yes') {
            foreach ($this->get_sample_template_objects() as $template) {
                $filepath = $this->get_templates_dir() . $template . '.php';
                if (!file_exists($filepath)) {
                    continue;
                }
                $this->insert_post($filepath);
            }
            add_option('flexible_coupons_sample_templates', 'yes');
        }
    }
    /**
     * @param string $filepath
     */
    private function insert_post(string $filepath)
    {
        $template_data = include $filepath;
        $title = $template_data['title'] ?? esc_html__('Sample template', 'flexible-coupons-pro');
        $post_args = ['ID' => 0, 'post_title' => $title, 'post_name' => sanitize_title($title), 'post_status' => 'publish', 'post_type' => $this->post_type_name];
        $post_id = wp_insert_post($post_args);
        unset($template_data['title']);
        update_post_meta($post_id, '_editor_data', $this->find_and_replace_shortcodes($template_data));
    }
    /**
     * Get sample templates.
     *
     * @return string[]
     */
    private function get_sample_template_objects(): array
    {
        return ['Cooking', 'Ties', 'Ebook', 'Travel'];
    }
}
