<?php

namespace YayMail\Integrations\AdvancedShipmentTrackingByZorem\Shortcodes;

use YayMail\Utils\SingletonTrait;

/**
 * ZoremTrackingInformation
 * @method static ZoremTrackingInformation get_instance()
 */

class ZoremTrackingInformation extends TrackingInformationShortcodes {
    use SingletonTrait;

    protected function get_ast_instance() {
        if ( class_exists( 'Zorem_Woocommerce_Advanced_Shipment_Tracking' ) ) {
            return \WC_Advanced_Shipment_Tracking_Actions::get_instance();
        }
        return null;
    }

    protected function get_content( $args ) {
        return yaymail_get_content( $this->get_path_to_shortcodes_template() . 'tracking-information/main.php', $args );
    }
}
