<?php

namespace YayMail\Integrations\CustomOrderStatusManagerByBrightPlugins;

use YayMail\Integrations\CustomOrderStatusManagerByBrightPlugins\Emails\CustomOrderStatusEmail;
use YayMail\Integrations\CustomOrderStatusManagerByBrightPlugins\Emails\EmailsHandler;
use YayMail\Utils\SingletonTrait;
/**
 * Plugin: Custom Order Status Manager by Bright Plugins
 * Link: https://wordpress.org/plugins/bp-custom-order-status-for-woocommerce/
 *
 * CustomOrderStatusManagerByBrightPlugins
 * * @method static CustomOrderStatusManagerByBrightPlugins get_instance()
 */
class CustomOrderStatusManagerByBrightPlugins {
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
        return class_exists( 'Bright_Plugins_COSW' );
    }

    private function initialize_hooks() {
    }

    private function initialize_elements() {
    }

    private function initialize_shortcodes() {
    }

    private function initialize_emails() {
        $arg            = [
            'numberposts' => -1,
            'post_type'   => 'order_status',
        ];
        $order_statuses = get_posts( $arg );

        if ( ! $order_statuses ) {
            return;
        }

        $wc_emails = \WC_Emails::instance()->get_emails();
        foreach ( $order_statuses as $order_status ) {
            $slug = get_post_meta( $order_status->ID, 'status_slug', true );
            if ( empty( $slug ) ) {
                continue;
            }

            $slug = 'bvos_custom_' . $slug;

            if ( isset( $wc_emails[ $slug ] ) ) {
                $to_be_customized_email = $wc_emails[ $slug ];

                if ( empty( $to_be_customized_email ) || empty( $to_be_customized_email->id ) ) {
                    continue;
                }

                EmailsHandler::get_instance()->add_email( $slug, new CustomOrderStatusEmail( $to_be_customized_email ) );
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
