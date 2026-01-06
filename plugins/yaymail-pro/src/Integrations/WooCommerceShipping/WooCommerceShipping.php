<?php

namespace YayMail\Integrations\WooCommerceShipping;

use YayMail\Integrations\WooCommerceShipping\Shortcodes\ShippingShortcodes;
use YayMail\Utils\SingletonTrait;

/**
 * WooCommerceShipping
 * * @method static WooCommerceShipping get_instance()
 */
class WooCommerceShipping {
    use SingletonTrait;

    private function __construct() {
        if ( self::is_3rd_party_installed() ) {
            $this->initialize_shortcodes();
        }
    }

    public static function is_3rd_party_installed() {
        return class_exists( '\Automattic\WCShipping\Loader' );
    }

    private function initialize_shortcodes() {

        add_action(
            'yaymail_register_shortcodes',
            function () {
                ShippingShortcodes::get_instance();
            }
        );
    }
}
