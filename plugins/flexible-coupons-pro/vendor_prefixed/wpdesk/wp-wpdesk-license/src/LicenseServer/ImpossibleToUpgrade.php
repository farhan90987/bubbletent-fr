<?php

declare (strict_types=1);
namespace FlexibleCouponsProVendor\WPDesk\License\LicenseServer;

/**
 * Show message that plugin cannot be upgraded, if the license server is not in use, i.e. the plugin
 * have not been downloaded from original source.
 */
final class ImpossibleToUpgrade
{
    private PluginVersionInfo $plugin_info;
    public function __construct(PluginVersionInfo $plugin_info)
    {
        $this->plugin_info = $plugin_info;
    }
    public function hooks(): void
    {
        add_action('after_plugin_row_' . $this->plugin_info->get_plugin_file_name(), $this);
    }
    public function __invoke(): void
    {
        $plugin_info = $this->plugin_info;
        include __DIR__ . '/../../templates/impossible-to-upgrade.php';
    }
}
