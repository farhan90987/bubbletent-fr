<?php

namespace YayMail\Integrations\BackInStockNotifier;

use YayMail\Utils\SingletonTrait;
use YayMail\Integrations\BackInStockNotifier\Emails\NotifierInstockMail;
use YayMail\Integrations\BackInStockNotifier\Emails\NotifierSubscribeMail;
use YayMail\Integrations\BackInStockNotifier\Shortcodes\BackInStockNotifierShortcodes;
/**
 * Plugin: Back In Stock Notifier for WooCommerce | WooCommerce Waitlist Pro
 * Link: https://codewoogeek.online/shop/free-plugins/back-in-stock-notifier/
 *
 * BackInStockNotifier
 * * @method static BackInStockNotifier get_instance()
 */
class BackInStockNotifier {
    use SingletonTrait;

    private function __construct() {
        if ( self::is_3rd_party_installed() ) {
            $this->initialize_emails();
            $this->initialize_shortcodes();
        }
    }

    public static function is_3rd_party_installed() {
        return class_exists( 'CWG_Instock_API' );
    }

    private function initialize_emails() {
        add_action(
            'yaymail_register_emails',
            function( $yaymail_emails ) {
                $yaymail_emails->register( NotifierInstockMail::get_instance() );
                $yaymail_emails->register( NotifierSubscribeMail::get_instance() );
            }
        );
    }

    private function initialize_shortcodes() {
        add_action(
            'yaymail_register_shortcodes',
            function() {
                BackInStockNotifierShortcodes::get_instance();
            }
        );
    }
}
