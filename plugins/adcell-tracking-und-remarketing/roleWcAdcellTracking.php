<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 29.04.19
 * Time: 08:46
 */

/**
 * Plugin Name: Adcell Tracking und Remarketing
 * Description: Stellt Tracking und Remarketing für das Adcell-Netzwerk im Frontend bereit.
 * Version: 1.0.21
 * Author: Firstlead GmbH
 * Developer: Firstlead GmbH
 * Text-Domain: adcell-tracking
 *
 * WC requires at least: 3.2
 * WC tested up to: 6.5.2
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once(plugin_dir_path(__FILE__).'roleWcAdcellTrackingRenderTracking.php');
require_once(plugin_dir_path(__FILE__).'roleWcAdcellTrackingRenderRetargeting.php');

/**
 * Hinzufügen von Einstellungsseite und Links im Backend
 */
if(is_admin()) {
    add_action('admin_menu', 'roleWcAdcellTrackingRenderMenu');
    add_action('admin_init', 'roleWcAdcellTrackingRegisterOptions');
    add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'roleWcAdcellTrackingAddPluginPageSettingsLink');
}

/**
 * Adds the async attribute to JavaScripts-Files
 *
 * @param $url
 * @return string
 */
function add_async_forscript($url)
{
    if (strpos($url, '#asyncload')===false)
        return $url;
    else if (is_admin())
        return str_replace('#asyncload', '', $url);
    else
        return str_replace('#asyncload', '', $url)."' async='async";
}
add_filter('clean_url', 'add_async_forscript', 11, 1);

/**
 * Adds link to settings page in plugin manager
 */
function roleWcAdcellTrackingAddPluginPageSettingsLink($links) {
    $links[] = '<a href="'.admin_url('options-general.php?page=roleWcAdcellTracking').'">'.__('Settings').'</a>';
    return $links;
}

/**
 * Add settings page to WP-Settings
 */
function roleWcAdcellTrackingRenderMenu() {
    add_options_page('Einstellungen', 'Adcell Tracking', 'manage_options', 'roleWcAdcellTracking', 'roleWcAdcellTrackingRenderOptions');
}

/**
 * Registers Options
 *
 * Einstellungen in WordPress registrieren
 */
function roleWcAdcellTrackingRegisterOptions() {
    register_setting('roleWcAdcellTrackingOptions', 'roleWcAdcellProgramId');
    register_setting('roleWcAdcellTrackingOptions', 'roleWcAdcellEventId');
    register_setting('roleWcAdcellTrackingOptions', 'roleWcAdcellRetargeting');
}

/**
 * Renders the settings page in Adminpanel
 *
 * Ausgeben der Einstellungsseite für das Plugin im WordPress Backend
 */
function roleWcAdcellTrackingRenderOptions() {
    echo '<h1>Einstellungen ADCELL Tracking und Retargeting</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields( 'roleWcAdcellTrackingOptions' );
    do_settings_sections( 'roleWcAdcellTrackingOptions' );
    echo '<table><tr>';
    echo '<td>ProgrammID (ADCELL):</td>';
    echo '<td><input type="number" name="roleWcAdcellProgramId" value="'.esc_attr(get_option('roleWcAdcellProgramId')).'" /></td>';
    echo '</tr><tr>';
    echo '<td>EventID (ADCELL):</td>';
    echo '<td><input type="number" name="roleWcAdcellEventId" value="'.esc_attr(get_option('roleWcAdcellEventId')).'" /></td>';
    echo '</tr><tr>';
    echo '<td>Retargeting aktivieren:</td>';
    if(esc_attr(get_option('roleWcAdcellRetargeting')) === 'true' || get_option('roleWcAdcellRetargeting') === false) {
        echo '<td><input type="checkbox" name="roleWcAdcellRetargeting" value="true" checked="checked" /></td>';
    } else {
        echo '<td><input type="checkbox" name="roleWcAdcellRetargeting" value="true" /></td>';
    }
    echo '</tr></table>';
    submit_button();
    echo '</form>';
}

add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});
