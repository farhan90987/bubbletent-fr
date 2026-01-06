<?php

namespace YayMail\Integrations\TrackingMoreOrderTrackingForWc;

use YayMail\Integrations\TrackingMoreOrderTrackingForWc\Elements\TrackingInformationElement;
use YayMail\Integrations\TrackingMoreOrderTrackingForWc\Shortcodes\TrackingInformationShortcodes;
use YayMail\Utils\SingletonTrait;

/**
 * TrackingMoreOrderTrackingForWc
 * * @method static TrackingMoreOrderTrackingForWc get_instance()
 */
class TrackingMoreOrderTrackingForWc {
    use SingletonTrait;

    private function __construct() {
        if ( self::is_3rd_party_installed() ) {
            $this->initialize_hooks();
            $this->initialize_elements();
            $this->initialize_shortcodes();
        }
    }

    public static function is_3rd_party_installed() {
        return class_exists( 'TrackingMore' );
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
                TrackingInformationShortcodes::get_instance();
            }
        );
    }
}
