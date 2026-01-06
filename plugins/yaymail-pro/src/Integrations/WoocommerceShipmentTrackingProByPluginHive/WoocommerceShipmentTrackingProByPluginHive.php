<?php

namespace YayMail\Integrations\WoocommerceShipmentTrackingProByPluginHive;

use YayMail\Integrations\WoocommerceShipmentTrackingProByPluginHive\Elements\TrackingInformationElement;
use YayMail\Integrations\WoocommerceShipmentTrackingProByPluginHive\Shortcodes\TrackingInformationByPluginHiveShortcodes;
use YayMail\Utils\SingletonTrait;

/**
 * WoocommerceShipmentTrackingProByPluginHive
 * * @method static WoocommerceShipmentTrackingProByPluginHive get_instance()
 */
class WoocommerceShipmentTrackingProByPluginHive {
    use SingletonTrait;

    private function __construct() {
        if ( self::is_3rd_party_installed() ) {
            $this->initialize_hooks();
            $this->initialize_elements();
            $this->initialize_shortcodes();
        }
    }

    public static function is_3rd_party_installed() {
        return class_exists( 'PH_Shipment_Tracking_Common' );
    }
    private function initialize_hooks() {
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
            function() {
                TrackingInformationByPluginHiveShortcodes::get_instance();
            }
        );
    }
}
