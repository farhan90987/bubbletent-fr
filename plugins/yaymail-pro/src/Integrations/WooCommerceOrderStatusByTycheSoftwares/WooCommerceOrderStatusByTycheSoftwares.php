<?php

namespace YayMail\Integrations\WooCommerceOrderStatusByTycheSoftwares;

use YayMail\Integrations\WooCommerceOrderStatusByTycheSoftwares\Emails\EmailsHandler;
use YayMail\Utils\SingletonTrait;
use YayMail\Integrations\WooCommerceOrderStatusByTycheSoftwares\Emails\WooCommerceOrderStatusByTycheSoftwares as CustomOrderEmail;
use YayMail\Integrations\WooCommerceOrderStatusByTycheSoftwares\Shortcodes\WooCommerceOrderStatusByTycheSoftwaresShortcodes;
use YayMail\Utils\Localize;

/**
 * Plugin: Custom Order Status for WooCommerce by Tyche Softwares
 * Link: https://wordpress.org/plugins/custom-order-statuses-woocommerce/
 *
 * WooCommerceOrderStatusByTycheSoftwares
 * * @method static WooCommerceOrderStatusByTycheSoftwares get_instance()
 */
class WooCommerceOrderStatusByTycheSoftwares {
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
        return class_exists( 'Alg_WC_Custom_Order_Statuses' );
    }

    private function initialize_hooks() {
    }

    private function initialize_elements() {
    }

    private function initialize_shortcodes() {
        add_action(
            'yaymail_register_shortcodes',
            function () {
                WooCommerceOrderStatusByTycheSoftwaresShortcodes::get_instance();
            }
        );
    }

    private function initialize_emails() {

        /**
         * Hack to bypass the language issue
         * TODO: refactor for other cases
         */
        if ( function_exists( 'PLL' ) ) {
            $request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
            $parsed_url  = wp_parse_url( $request_uri );
            $route       = $parsed_url['path'] ?? '';
            if ( strpos( $route, '/yaymail' ) !== false || strpos( $_REQUEST['action'] ?? '', 'yaymail' ) !== false ) {
                $translate_integrations = Localize::get_translate_integrations();
                if ( ! empty( $translate_integrations['current_translation'] ) ) {
                    $current_language = $translate_integrations['current_translation']['active_language'] ?? 'en';
                    PLL()->curlang    = PLL()->model->get_language( $current_language );
                }
            }
        }

        $custom_statuses_array = function_exists( 'alg_get_custom_order_statuses_from_cpt' ) ? alg_get_custom_order_statuses_from_cpt() : [];
        foreach ( $custom_statuses_array as $key => $value ) {
            $template_informations = (object) [
                'id'        => $key,
                'title'     => $value,
                'recipient' => 'customer',
            ];
            EmailsHandler::get_instance()->add_email( new CustomOrderEmail( $template_informations ) );
        }

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
