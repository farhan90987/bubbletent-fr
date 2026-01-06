<?php

/**
 * Editor implementation.
 *
 * @package WPDesk\Library\WPCanvaEditor
 */
namespace FlexibleCouponsProVendor\WPDesk\Library\WPCanvaEditor;

use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\EditorIntegration;
use FlexibleCouponsProVendor\WPDesk\Library\CouponInterfaces\EditorAreaProperties;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
/**
 * Abstract class for implementation of canva editor.
 *
 * @package WPDesk\Library\WPCanvaEditor
 */
abstract class EditorImplementation implements EditorIntegration, Hookable, HookableCollection
{
    use HookableParent;
    const EDITOR_POST_META = '_editor_data';
    /**
     * @var string
     */
    protected $post_type;
    /**
     * @param $post_type
     */
    public function __construct($post_type)
    {
        $this->set_post_type($post_type);
    }
    /**
     * @return void
     */
    public function hooks()
    {
        $this->add_hookable(new RegisterPostType($this));
        $this->add_hookable(new CustomizeEditPage($this->get_post_type()));
        $this->add_hookable(new Assets($this->get_post_type()));
        $this->add_hookable(new AjaxHandler($this->get_post_type()));
        $this->hooks_on_hookable_objects();
    }
    /**
     * Set post type slug for editor.
     *
     * @param string $post_type Post type. (max. 20 characters and may only contain lowercase alphanumeric characters, dashes, and underscores.)
     */
    private function set_post_type($post_type)
    {
        $this->post_type = $post_type;
    }
    /**
     * @return string
     */
    public function get_post_type()
    {
        return $this->post_type;
    }
    /**
     * Define post type args & labels
     *
     * @return array
     */
    abstract public function post_type_args_definition();
    /**
     * @return string
     */
    public function get_post_meta_name()
    {
        return self::EDITOR_POST_META;
    }
    /**
     * @param int $post_id
     *
     * @return array
     */
    public function get_post_meta($post_id)
    {
        return get_post_meta($post_id, self::EDITOR_POST_META, \true);
    }
    /**
     * @param int $post_id
     *
     * @return EditorAreaProperties
     */
    public function get_area_properties($post_id)
    {
        return new AreaProperties($this->get_post_meta($post_id));
    }
}
