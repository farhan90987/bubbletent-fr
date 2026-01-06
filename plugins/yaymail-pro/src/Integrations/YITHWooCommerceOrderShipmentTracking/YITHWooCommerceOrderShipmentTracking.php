<?php

namespace YayMail\Integrations\YITHWooCommerceOrderShipmentTracking;

use YayMail\Integrations\YITHWooCommerceOrderShipmentTracking\Elements\YITHTrackingInformationElement;
use YayMail\Integrations\YITHWooCommerceOrderShipmentTracking\Shortcodes\YITHTrackingInformationShortcodes;
use YayMail\Utils\SingletonTrait;

/**
 * YITHWooCommerceOrderShipmentTracking
 * * @method static YITHWooCommerceOrderShipmentTracking get_instance()
 */
class YITHWooCommerceOrderShipmentTracking {
    use SingletonTrait;

    private function __construct() {
        if ( self::is_3rd_party_installed() ) {
            $this->initialize_hooks();
            $this->initialize_elements();
            $this->initialize_shortcodes();
        }
    }

    public static function is_3rd_party_installed() {
        return function_exists( 'yith_ywot_premium_init' ) || function_exists( 'yith_ywot_init' );
    }

    private function initialize_hooks() {
    }

    private function initialize_elements() {
        add_action(
            'yaymail_register_elements',
            function ( $element_service ) {
                $element_service->register_element( YITHTrackingInformationElement::get_instance() );
            }
        );
    }

    private function initialize_shortcodes() {
        add_action(
            'yaymail_register_shortcodes',
            function() {
                YITHTrackingInformationShortcodes::get_instance();
            }
        );
    }
}
