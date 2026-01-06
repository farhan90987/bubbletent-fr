<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Tabs;

use FlexibleCouponsProVendor\WPDesk\Forms\Field;
use FlexibleCouponsProVendor\WPDesk\Forms\Form\FormWithFields;
use FlexibleCouponsProVendor\WPDesk\View\Renderer\Renderer;
/**
 * Tab than can be rendered on settings page.
 * This abstraction should be used by tabs that want to use Form Fields to render its content.
 *
 * @package WPDesk\Library\WPCoupons\Settings\Tabs
 */
abstract class FieldSettingsTab implements SettingsTab
{
    /**
     * @var FormWithFields
     */
    private $form;
    /**
     * @return Field[]
     */
    abstract protected function get_fields();
    /**
     * @return bool
     */
    public static function is_active()
    {
        return \true;
    }
    /**
     * @return FormWithFields
     */
    protected function get_form()
    {
        if ($this->form === null) {
            $fields = $this->get_fields();
            $this->form = new FormWithFields($fields, static::get_tab_slug());
        }
        return $this->form;
    }
    /**
     * @param Renderer $renderer
     *
     * @return string
     */
    public function render(Renderer $renderer)
    {
        return $this->get_form()->render_form($renderer);
    }
    /**
     * @param Renderer $renderer
     *
     * @return void
     */
    public function output_render(Renderer $renderer)
    {
        echo $this->get_form()->render_form($renderer);
        //phpcs:ignore
    }
    /**
     * @param array|\Psr\Container\ContainerInterface $data
     */
    public function set_data($data)
    {
        $this->get_form()->set_data($data);
    }
    /**
     * @param array $request
     */
    public function handle_request($request)
    {
        $this->get_form()->handle_request($request);
    }
    /**
     * @return array|null
     */
    public function get_data()
    {
        return $this->get_form()->get_data();
    }
}
