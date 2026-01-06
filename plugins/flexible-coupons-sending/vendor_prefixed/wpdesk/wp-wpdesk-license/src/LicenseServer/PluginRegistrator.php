<?php

namespace FCSVendor\WPDesk\License\LicenseServer;

use FCSVendor\Psr\Log\LoggerInterface;
/**
 * New server license manager.
 * Fields in this class can be replaced during build process and/or package preparation on the license server.
 */
class PluginRegistrator
{
    private PluginVersionInfo $plugin_info;
    private LoggerInterface $logger;
    /**
     * Field CAN be replaced during build process.
     *
     * @var string License server URL.
     */
    private string $server = 'https://license.wpdesk.dev';
    /**
     * Token WILL BE REPLACED during package preparation on the license server.
     *
     * @var string User token.
     */
    private static string $token =  'd2c106e1-b526-4e82-93d2-fefc972e0560';
    /**
     * This field WILL BE REPLACED during package preparation on the license server.
     * Thanks to this field we know whether a plugin has been downloaded from license server.
     *
     * @var bool Should use license server.
     */
    private static bool $should_use_license_server = true;
    public static function get_token(): string
    {
        return apply_filters('wpdesk/license/token', self::$token);
    }
    public static function should_use_license_server(): bool
    {
        return apply_filters('wpdesk/license/use_license', self::$should_use_license_server);
    }
    /**
     * @param PluginVersionInfo|\WPDesk_Plugin_Info $plugin_info
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($plugin_info, ?LoggerInterface $logger = null)
    {
        if ($plugin_info instanceof PluginVersionInfo) {
            $this->plugin_info = $plugin_info;
        } else {
            try {
                $this->plugin_info = PluginVersionInfo::from_legacy_plugin_info($plugin_info);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(sprintf('Plugin info is not valid. Error: %s', $e->getMessage()));
            }
        }
        $this->logger = $logger ?? new DummyLogger();
    }
    public function is_active(): bool
    {
        return (new PluginLicense($this->plugin_info))->is_active();
    }
    public function initialize_license_manager(): void
    {
        $this->server = apply_filters('wpdesk/license/server', $this->server);
        if (self::should_use_license_server()) {
            (new PluginUpgrade($this->plugin_info, $this->server, self::get_token(), $this->logger))->hooks();
            (new PluginExternalBlocking($this->plugin_info, $this->server))->hooks();
            (new PluginViewVersionInfo($this->plugin_info, $this->server, $this->logger))->hooks();
        } else {
            (new ImpossibleToUpgrade($this->plugin_info))->hooks();
        }
    }
}
