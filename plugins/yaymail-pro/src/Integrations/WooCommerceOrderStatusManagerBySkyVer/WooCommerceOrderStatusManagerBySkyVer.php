<?php

namespace YayMail\Integrations\WooCommerceOrderStatusManagerBySkyVer;

use YayMail\Utils\SingletonTrait;
use YayMail\Integrations\WooCommerceOrderStatusManagerBySkyVer\Emails\WooCommerceOrderStatusManagerBySkyVer as CustomOrderEmail;
use YayMail\Integrations\WooCommerceOrderStatusManagerBySkyVer\Emails\EmailsHandler;
/**
 * Plugin: WooCommerce Order Status Manager by SkyVer
 * Link: https://woo.com/products/woocommerce-order-status-manager/
 *
 * WooCommerceOrderStatusManagerBySkyVer
 * * @method static WooCommerceOrderStatusManagerBySkyVer get_instance()
 */
class WooCommerceOrderStatusManagerBySkyVer {
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
        return class_exists( 'WC_Order_Status_Manager_Loader' );
    }

    private function initialize_hooks() {
    }

    private function initialize_elements() {
    }

    private function initialize_shortcodes() {
    }

    private function initialize_emails() {

        $wc_emails = \WC_Emails::instance()->get_emails();
        $emails    = wc_order_status_manager()->get_emails_instance()->get_emails();

        foreach ( $emails as $email ) {

            $email_id = 'wc_order_status_email_' . esc_attr( $email->ID );

            if ( isset( $wc_emails[ $email_id ] ) ) {

                $to_be_customized_email = $wc_emails[ $email_id ];

                if ( empty( $to_be_customized_email ) || empty( $to_be_customized_email->id ) ) {
                    continue;
                }

                EmailsHandler::get_instance()->add_email( new CustomOrderEmail( $to_be_customized_email ) );

            }
        }

        add_action(
            'yaymail_register_emails',
            function ( $email_service ) {
                $instances = EmailsHandler::get_instance()->get_emails();
                foreach ( $instances as $instance ) {
                    $email_service->register( $instance );
                }
            }
        );
    }
}
