<?php

namespace YayMail\Integrations\WcAdminCustomOrderFieldBySkyverge;

use YayMail\Integrations\WcAdminCustomOrderFieldBySkyverge\Elements\AdminCustomOrderFields;
use YayMail\Integrations\WcAdminCustomOrderFieldBySkyverge\Shortcodes\AdminCustomOrderFieldsShortcodes;
use YayMail\Utils\SingletonTrait;

/**
 * WcAdminCustomOrderFieldBySkyverge
 * * @method static WcAdminCustomOrderFieldBySkyverge get_instance()
 */
class WcAdminCustomOrderFieldBySkyverge {
    use SingletonTrait;

    private function __construct() {
        if ( self::is_3rd_party_installed() ) {
            $this->initialize_hooks();
            $this->initialize_elements();
            $this->initialize_shortcodes();
            $this->initialize_emails();
        }
    }

    public static function is_3rd_party_installed() {
        return class_exists( 'WC_Admin_Custom_Order_Fields' );
    }

    private function initialize_hooks() {
    }

    private function initialize_elements() {
        add_action(
            'yaymail_register_elements',
            function ( $element_service ) {
                $element_service->register_element( AdminCustomOrderFields::get_instance() );
            }
        );
    }

    private function initialize_shortcodes() {
        add_action(
            'yaymail_register_shortcodes',
            function() {
                AdminCustomOrderFieldsShortcodes::get_instance();
            }
        );
    }

    private function initialize_emails() {
    }
}
