<?php

namespace YayMail\Integrations\FooEventsforWooCommerce;

use YayMail\Integrations\FooEventsforWooCommerce\Shortcodes\FooEventsShortcodes;
use YayMail\Utils\SingletonTrait;

/**
 * FooEvents for WooCommerce
 * Link: https://www.fooevents.com/
 *
 * FooEventsforWooCommerce
 * * @method static FooEventsforWooCommerce get_instance()
 */
class FooEventsforWooCommerce {
    use SingletonTrait;

    private function __construct() {
        if ( self::is_3rd_party_installed() ) {
            $this->initialize_shortcodes();
        }
    }

    public static function is_3rd_party_installed() {
        return class_exists( 'FooEvents' );
    }

    private function initialize_shortcodes() {
        add_action(
            'yaymail_register_shortcodes',
            function() {
                FooEventsShortcodes::get_instance();
            }
        );
    }
}
