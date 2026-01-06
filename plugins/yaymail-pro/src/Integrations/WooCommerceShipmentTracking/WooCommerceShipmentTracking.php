<?php

namespace YayMail\Integrations\WooCommerceShipmentTracking;

use YayMail\Utils\SingletonTrait;
use YayMail\Integrations\WooCommerceShipmentTracking\Shortcodes\TrackingInformationShortcodes;
use YayMail\Integrations\WooCommerceShipmentTracking\Elements\TrackingInformationElement;
/**
 * Plugin Name: WooCommerce Shipment Tracking
 * Plugin URI: https://yaycommerce.com/yaymail-woocommerce-email-customizer/
 *
 * WooCommerceShipmentTracking
 * * @method static WooCommerceShipmentTracking get_instance()
 */
class WooCommerceShipmentTracking {
    use SingletonTrait;

    private function __construct() {
        if ( self::is_3rd_party_installed() ) {
            $this->initialize_elements();
            $this->initialize_shortcodes();
        }
    }

    public static function is_3rd_party_installed() {
        return is_plugin_active( 'woocommerce-shipment-tracking/woocommerce-shipment-tracking.php' );
    }

    private function initialize_elements() {
        add_action(
            'yaymail_register_elements',
            function ( $element_service ) {
                $element_service->register_element( TrackingInformationElement::get_instance() );
            }
        );
    }

    private function initialize_shortcodes() {

        add_action(
            'yaymail_register_shortcodes',
            function () {
                TrackingInformationShortcodes::get_instance();
            }
        );
    }
}
