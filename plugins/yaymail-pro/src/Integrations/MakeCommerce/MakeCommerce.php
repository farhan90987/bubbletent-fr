<?php

namespace YayMail\Integrations\MakeCommerce;

use YayMail\Integrations\MakeCommerce\Shortcodes\ShippingShortcodes;
use YayMail\Utils\SingletonTrait;

/**
 * MakeCommerce
 * * @method static MakeCommerce get_instance()
 */
class MakeCommerce {
    use SingletonTrait;

    private function __construct() {
        if ( self::is_3rd_party_installed() ) {
            $this->initialize_shortcodes();
        }
    }

    public static function is_3rd_party_installed() {
        return class_exists( 'MakeCommerce' );
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
