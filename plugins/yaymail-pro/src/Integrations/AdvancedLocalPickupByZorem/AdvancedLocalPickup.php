<?php

namespace YayMail\Integrations\AdvancedLocalPickupByZorem;

use YayMail\Integrations\AdvancedLocalPickupByZorem\Elements\AdvancedLocalPickupInstructionElement;
use YayMail\Integrations\AdvancedLocalPickupByZorem\Shortcodes\AdvancedLocalPickupShortcodes;
use YayMail\Utils\SingletonTrait;

/**
 * AdvancedLocalPickup
 *
 * @method static AdvancedLocalPickup get_instance()
 */
class AdvancedLocalPickup {
    use SingletonTrait;

    private function __construct() {
        if ( self::is_3rd_party_installed() ) {
            $this->initialize_elements();
            $this->initialize_shortcodes();
        }
    }

    public static function is_3rd_party_installed() {
        return class_exists( 'Woocommerce_Local_Pickup' );
    }

    protected function initialize_elements() {
        add_action(
            'yaymail_register_elements',
            function( $element_service ) {
                $element_service->register_element( AdvancedLocalPickupInstructionElement::get_instance() );
            }
        );
    }

    protected function initialize_shortcodes() {
        add_action(
            'yaymail_register_shortcodes',
            function() {
                if ( class_exists( 'Woocommerce_Local_Pickup' ) ) {
                    AdvancedLocalPickupShortcodes::get_instance();
                }
            }
        );
    }
}
