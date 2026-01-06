<?php

namespace YayMail\Integrations\WooCommerceShippingTax;

use YayMail\Integrations\WooCommerceShippingTax\Elements\ShipmentTrackingElement;
use YayMail\Integrations\WooCommerceShippingTax\Shortcodes\ShipmentTrackingShortcodes;
use YayMail\Utils\SingletonTrait;

/**
 * WooCommerceShippingTax
 * * @method static WooCommerceShippingTax get_instance()
 */
class WooCommerceShippingTax {
    use SingletonTrait;

    private function __construct() {
        if ( self::is_3rd_party_installed() ) {
            $this->initialize_elements();
            $this->initialize_shortcodes();
        }
    }

    public static function is_3rd_party_installed() {
        return class_exists( 'WC_Connect_Loader' );
    }

    private function initialize_elements() {
        add_action(
            'yaymail_register_elements',
            function( $element_service ) {
                $element_service->register_element( ShipmentTrackingElement::get_instance() );
            }
        );
    }

    private function initialize_shortcodes() {
        add_action(
            'yaymail_register_shortcodes',
            function() {
                ShipmentTrackingShortcodes::get_instance();
            }
        );
    }
}
