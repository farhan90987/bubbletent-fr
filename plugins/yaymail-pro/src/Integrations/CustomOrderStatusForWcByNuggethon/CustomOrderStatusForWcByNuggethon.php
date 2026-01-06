<?php

namespace YayMail\Integrations\CustomOrderStatusForWcByNuggethon;

use YayMail\Utils\SingletonTrait;
use YayMail\Integrations\CustomOrderStatusForWcByNuggethon\Emails\CustomOrderStatusForWcByNuggethon as CustomOrderEmail;
use YayMail\Integrations\CustomOrderStatusForWcByNuggethon\Emails\EmailsHandler;

/**
 * Plugin: Custom Order Statuses for WooCommerce
 * Link: https://wordpress.org/plugins/custom-order-statuses-for-woocommerce/
 *
 * CustomOrderStatusForWcByNuggethon
 * * @method static CustomOrderStatusForWcByNuggethon get_instance()
 */
class CustomOrderStatusForWcByNuggethon {
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
        return class_exists( 'WOOCOS_Email_Manager' );
    }

    private function initialize_hooks() {
    }

    private function initialize_elements() {
    }

    private function initialize_shortcodes() {
    }

    private function initialize_emails() {

        $custom_order_statuses = json_decode( get_option( 'woocos_custom_order_statuses' ) );
        if ( ! $custom_order_statuses ) {
            return;
        }

        foreach ( $custom_order_statuses as $order_status ) {
            $wc_emails = \WC_Emails::instance()->get_emails();
            $slug      = $order_status->slug;

            if ( isset( $wc_emails[ $slug ] ) ) {

                $to_be_customized_email = $wc_emails[ $slug ];

                if ( empty( $to_be_customized_email ) || empty( $to_be_customized_email->id ) ) {
                    continue;
                }

                EmailsHandler::get_instance()->add_email( $slug, new CustomOrderEmail( $to_be_customized_email ) );

            }
        }//end foreach

        add_action(
            'yaymail_register_emails',
            function ( $email_service ) {
                $emails = EmailsHandler::get_instance()->get_emails();
                foreach ( $emails as $email_instance ) {
                    $email_service->register( $email_instance );
                }
            }
        );
    }
}
