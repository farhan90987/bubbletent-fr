<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings;

use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\CouponsIntegration;
use FlexibleCouponsProVendor\WPDesk\Notice\Notice;
use FlexibleCouponsProVendor\WPDesk\View\Renderer\Renderer;
use FlexibleCouponsProVendor\WPDesk\View\Resolver\DirResolver;
use FlexibleCouponsProVendor\WPDesk\View\Resolver\ChainResolver;
use FlexibleCouponsProVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use FlexibleCouponsProVendor\WPDesk\Persistence\PersistentContainer;
use FlexibleCouponsProVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use FlexibleCouponsProVendor\WPDesk\Forms\Resolver\DefaultFormFieldResolver;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Settings\Tabs\SettingsTab;
use FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers\Plugin;
/**
 * Adds settings to the menu and manages how and what is shown on the settings page.
 *
 * @package WPDesk\Library\WPCoupons\Settings
 */
class SettingsForm implements Hookable
{
    const MENU_PAGE_URL = 'edit.php?post_type=wpdesk-coupons';
    const SETTINGS_SLUG = 'fc-settings';
    const NONCE_ACTION = 'save_settings';
    const NONCE_NAME = 'settings_nonce';
    /**
     * @var PersistentContainer
     */
    private $options_container;
    private $renderer;
    private string $plugin_version;
    /**
     * @var SettingsTab[]
     */
    protected $tabs = [];
    /**
     * @param PersistentContainer $options_container
     */
    public function __construct(PersistentContainer $options_container, Renderer $renderer, string $plugin_version)
    {
        $this->options_container = $options_container;
        $this->renderer = $renderer;
        $this->plugin_version = $plugin_version;
    }
    /**
     * Fires hooks.
     */
    public function hooks()
    {
        add_action('admin_menu', function () {
            add_submenu_page(self::MENU_PAGE_URL, esc_html__('Settings', 'flexible-coupons-pro'), esc_html__('Settings', 'flexible-coupons-pro'), 'manage_options', self::SETTINGS_SLUG, [$this, 'render_page_action'], 40);
        }, 999);
        add_action('admin_init', [$this, 'save_settings_action'], 5);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts'], 5);
    }
    public function enqueue_scripts()
    {
        if (!isset($_GET['page']) || $_GET['page'] !== self::SETTINGS_SLUG) {
            return;
        }
        wp_enqueue_editor();
        wp_enqueue_script('fc-settings', CouponsIntegration::get_assets_url() . '/js/index.js', [], $this->plugin_version, \true);
        wp_localize_script('fc-settings', 'fcCodeImport', ['ajaxurl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('fc-core-nonce'), 'enabled' => Plugin::is_fcci_pro_addon_enabled() && CouponsIntegration::is_pro()]);
        wp_localize_script('fc-settings', 'fcSendingSettings', ['ajaxurl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('fc-email-templates-nonce'), 'enabled' => Plugin::is_fcs_pro_addon_enabled() && CouponsIntegration::is_pro()]);
    }
    /**
     * Save POST tab data. Before render.
     *
     * @return void
     */
    public function save_settings_action()
    {
        if (!isset($_GET['page']) || $_GET['page'] !== self::SETTINGS_SLUG) {
            return;
        }
        $tab = $this->get_active_tab();
        $tab_data = isset($_POST[$tab::get_tab_slug()]) ? wp_unslash($_POST[$tab::get_tab_slug()]) : '';
        //phpcs:ignore
        $nonce_value = $tab_data[self::NONCE_NAME] ?? '';
        $nonce = wp_verify_nonce($nonce_value, self::NONCE_ACTION);
        if (!empty($tab_data) && $nonce) {
            $tab->handle_request($tab_data);
            $this->save_tab_data($tab_data);
            /**
             * Fires after saving the tab settings.
             *
             * @param string              $tab                      Tab ID.
             * @param PersistentContainer $this->options_container  Persistent Container Object.
             *
             * @since 1.6.0
             */
            do_action('fc/core/settings/tabs/saved', $tab, $this->options_container);
            new Notice(esc_html__('Your settings have been saved.', 'flexible-coupons-pro'), Notice::NOTICE_TYPE_SUCCESS);
        } else {
            $tab->set_data($this->options_container);
        }
        /**
         * Fires after saving the settings.
         *
         * @since 1.6.0
         */
        do_action('fc/core/settings/ready');
    }
    /**
     * Save data from tab to persistent container.
     *
     * @param array $post_data
     */
    private function save_tab_data(array $post_data)
    {
        foreach ($post_data as $key => $value) {
            if ($key === '_empty_value' || $key === '') {
                continue;
                // Prevent save values for pro field.
            }
            if (is_array($value)) {
                $value = array_filter($value, static function ($v) {
                    return !empty($v);
                });
            }
            $this->options_container->set($key, $value);
        }
        if (!empty($_SERVER['REQUEST_URI'])) {
            wp_safe_redirect(wp_unslash($_SERVER['REQUEST_URI']), 301);
            exit;
        }
    }
    /**
     * Get URL to plugin settings, optionally to specific tab.
     *
     * @param string|null $tab_slug When null returns URL to general settings.
     *
     * @return string
     */
    public static function get_url(string $tab_slug = null): string
    {
        $url = admin_url(add_query_arg(['page' => self::SETTINGS_SLUG], self::MENU_PAGE_URL));
        if ($tab_slug !== null) {
            $url = add_query_arg(['tab' => $tab_slug], $url);
        }
        return $url;
    }
    /**
     * Render
     *
     * @return void
     */
    public function render_page_action()
    {
        $tab = $this->get_active_tab();
        $renderer = $this->get_renderer();
        $renderer->output_render('menu', ['base_url' => self::get_url(), 'menu_items' => $this->get_tabs_menu_items(), 'selected' => $this->get_active_tab()->get_tab_slug()]);
        $tab->output_render($renderer);
        $renderer->output_render('footer');
    }
    /**
     * @return SettingsTab
     */
    private function get_active_tab(): SettingsTab
    {
        $selected_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : null;
        //phpcs:ignore
        $tabs = $this->get_settings_tabs();
        if (!empty($selected_tab) && isset($tabs[$selected_tab])) {
            return $tabs[$selected_tab];
        }
        return reset($tabs);
    }
    /**
     * @return SettingsTab[]
     */
    private function get_settings_tabs(): array
    {
        if (empty($this->tabs)) {
            $this->tabs[Tabs\MainSettings::get_tab_slug()] = new Tabs\MainSettings($this->renderer);
            $this->tabs[Tabs\CouponSettings::get_tab_slug()] = new Tabs\CouponSettings();
            $this->tabs[Tabs\EmailSettings::get_tab_slug()] = new Tabs\EmailSettings();
            /**
             * Filters setting tabs.
             *
             * @param array $this->tabs .
             *
             * @return SettingsTab[]
             *
             * @since 1.6.0
             */
            $this->tabs = apply_filters('fc/core/settings/tabs', $this->tabs);
        }
        return $this->tabs;
    }
    /**
     * @return Renderer
     */
    private function get_renderer()
    {
        $chain = new ChainResolver();
        /**
         * Filters resolvers for setting templates.
         *
         * @param Resolver $resolvers Resolvers.
         *
         * @return array Array of Resolvers.
         * @since 3.0.0
         */
        $resolver_list = (array) apply_filters('fcpdf/settings/template_resolvers', [new DirResolver(__DIR__ . '/Views'), new DefaultFormFieldResolver()]);
        foreach ($resolver_list as $resolver) {
            $chain->appendResolver($resolver);
        }
        return new SimplePhpRenderer($chain);
    }
    /**
     * @return string[]
     */
    private function get_tabs_menu_items(): array
    {
        $menu_items = [];
        foreach ($this->get_settings_tabs() as $tab) {
            if ($tab::is_active()) {
                $menu_items[$tab::get_tab_slug()] = $tab->get_tab_name();
            }
        }
        return $menu_items;
    }
}
