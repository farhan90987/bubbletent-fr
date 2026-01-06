<?php

declare (strict_types=1);
namespace FCSVendor\WPDesk\License\LicenseServer;

/**
 * Internal Value Object holding basic plugin information required by license server.
 */
final class PluginVersionInfo
{
    private string $plugin_file_name;
    private string $plugin_slug;
    private string $version;
    private string $product_id;
    private string $plugin_name;
    private array $plugin_shops;
    public function __construct(string $plugin_name, string $version, string $product_id, string $plugin_slug, string $plugin_file_name, array $plugin_shops = [])
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->product_id = $product_id;
        $this->plugin_slug = $plugin_slug;
        $this->plugin_file_name = $plugin_file_name;
        $this->plugin_shops = $plugin_shops;
    }
    public function get_plugin_file_name(): string
    {
        return $this->plugin_file_name;
    }
    public function get_version(): string
    {
        return $this->version;
    }
    public function get_product_id(): string
    {
        return $this->product_id;
    }
    public function get_plugin_name(): string
    {
        return $this->plugin_name;
    }
    public function get_plugin_slug(): string
    {
        return $this->plugin_slug;
    }
    public function get_shop_url(): string
    {
        return $this->plugin_shops[get_user_locale()] ?? $this->plugin_shops['default'] ?? $this->plugin_shops[0] ?? 'https://wpdesk.net/';
    }
    public function get_customer_account_url(): string
    {
        return rtrim($this->get_shop_url(), '/') . '/my-account/wp-downloads/';
    }
    /**
     * @param \WPDesk_Plugin_Info $info
     */
    public static function from_legacy_plugin_info($info): self
    {
        return new self($info->get_plugin_name(), $info->get_version(), $info->get_product_id(), $info->get_plugin_slug(), $info->get_plugin_file_name(), empty($info->get_plugin_shops()) ? [] : $info->get_plugin_shops());
    }
}
