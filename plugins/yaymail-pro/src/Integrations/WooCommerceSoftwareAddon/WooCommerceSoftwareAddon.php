<?php

namespace YayMail\Integrations\WooCommerceSoftwareAddon;

use YayMail\Integrations\WooCommerceSoftwareAddon\Elements\SoftwareLicenseElement;
use YayMail\Integrations\WooCommerceSoftwareAddon\Shortcodes\SoftwareLicenseShortcodes;
use YayMail\Utils\SingletonTrait;

/**
 * WooCommerceSoftwareAddon
 * * @method static WooCommerceSoftwareAddon get_instance()
 */
class WooCommerceSoftwareAddon {
    use SingletonTrait;

    private function __construct() {
        if ( self::is_3rd_party_installed() ) {
            $this->initialize_elements();
            $this->initialize_shortcodes();
        }
    }

    public static function is_3rd_party_installed() {
        return class_exists( 'WC_Software' );
    }

    private function initialize_elements() {
        add_action(
            'yaymail_register_elements',
            function( $element_service ) {
                $element_service->register_element( SoftwareLicenseElement::get_instance() );
            }
        );
    }

    private function initialize_shortcodes() {
        add_action(
            'yaymail_register_shortcodes',
            function() {
                SoftwareLicenseShortcodes::get_instance();
            }
        );
    }
}
