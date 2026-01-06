<?php

namespace FCSVendor\WPDesk\License\LicenseServer;

/**
 * Provides plugin license information and gives a change to modify it.
 */
class PluginLicense
{
    private const ACTIVATED_VALUE = 'Activated';
    private PluginVersionInfo $plugin_info;
    public function __construct(PluginVersionInfo $info)
    {
        $this->plugin_info = $info;
    }
    public function is_active(): bool
    {
        return get_option($this->prepare_option_is_active()) === self::ACTIVATED_VALUE;
    }
    public function set_active(): void
    {
        update_option($this->prepare_option_is_active(), self::ACTIVATED_VALUE);
    }
    public function set_inactive(): void
    {
        update_option($this->prepare_option_is_active(), 'Inactive');
    }
    private function prepare_option_is_active(): string
    {
        return $this->prepare_option_name('activated');
    }
    private function prepare_option_name(string $field): string
    {
        return sprintf('api_%1$s_%2$s', $this->plugin_info->get_plugin_slug(), $field);
    }
}
