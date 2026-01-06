<?php

namespace FlexibleCouponsProVendor\WPDesk\Library\WPCoupons\Helpers;

/**
 * Plugin helpers functions.
 *
 * @package WPDesk\Library\WPCoupons\Helpers
 */
class Plugin
{
    /**
     * Is plugin active.
     *
     * @param string $plugin Slug of plugin.
     *
     * @return bool
     */
    public static function is_active(string $plugin): bool
    {
        if (function_exists('is_plugin_active_for_network')) {
            if (is_plugin_active_for_network($plugin)) {
                return \true;
            }
        }
        return in_array($plugin, (array) get_option('active_plugins', []));
    }
    /**
     * Is Advanced Sending for Flexible Coupons plugin active.
     *
     * @return bool
     */
    public static function is_fcs_pro_addon_enabled(): bool
    {
        return self::is_active('flexible-coupons-sending/flexible-coupons-sending.php');
    }
    public static function is_fc_multiple_pdfs_pro_addon_enabled(): bool
    {
        return self::is_active('flexible-coupons-multiple-pdfs/flexible-coupons-multiple-pdfs.php');
    }
    public static function is_fcci_pro_addon_enabled(): bool
    {
        return self::is_active('flexible-coupons-code-import/flexible-coupons-code-import.php');
    }
}
