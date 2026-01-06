<?php

namespace YayMail\Integrations\AdvancedShipmentTrackingByZorem;

use YayMail\Integrations\AdvancedShipmentTrackingByZorem\Elements\ASTOrderDetails;
use YayMail\Integrations\AdvancedShipmentTrackingByZorem\Elements\TrackingInformationElement;
use YayMail\Integrations\AdvancedShipmentTrackingByZorem\Shortcodes\ZoremTrackingInformation;
use YayMail\Integrations\AdvancedShipmentTrackingByZorem\Shortcodes\AstProTrackingInformation;
use YayMail\Integrations\AdvancedShipmentTrackingByZorem\Emails\CustomerPartialShippedOrder;
use YayMail\Integrations\AdvancedShipmentTrackingByZorem\Emails\CustomerShippedOrder;
use YayMail\Integrations\AdvancedShipmentTrackingByZorem\Shortcodes\ASTProCustomerShippedShortcodes;
use YayMail\Utils\SingletonTrait;

/**
 * AdvancedShipmentTracking
 * * @method static AdvancedShipmentTracking get_instance()
 */
class AdvancedShipmentTracking {
    use SingletonTrait;

    private function __construct() {
        if ( self::is_3rd_party_installed() ) {
            $this->initialize_emails();
            $this->initialize_elements();
            $this->initialize_shortcodes();
            $this->initialize_localize_data();
        }
    }

    public static function is_3rd_party_installed() {
        return class_exists( 'Zorem_Woocommerce_Advanced_Shipment_Tracking' ) || ( class_exists( 'Ast_Pro' ) && ast_pro()->license && method_exists( ast_pro()->license, 'check_subscription_status' ) && ast_pro()->license->check_subscription_status() );
    }

    private function initialize_elements() {
        add_action(
            'yaymail_register_elements',
            function ( $element_service ) {
                $element_service->register_element( TrackingInformationElement::get_instance() );
                $element_service->register_element( ASTOrderDetails::get_instance() );
            }
        );
    }

    private function initialize_shortcodes() {

        add_action(
            'yaymail_register_shortcodes',
            function () {
                if ( class_exists( 'Zorem_Woocommerce_Advanced_Shipment_Tracking' ) ) {
                    ZoremTrackingInformation::get_instance();
                }
                if ( class_exists( 'Ast_Pro' ) ) {
                    AstProTrackingInformation::get_instance();
                    ASTProCustomerShippedShortcodes::get_instance();
                }
            }
        );
    }

    private function initialize_emails() {
        add_action(
            'yaymail_register_emails',
            function ( $email_service ) {
                $email_service->register( CustomerPartialShippedOrder::get_instance() );
                $email_service->register( CustomerShippedOrder::get_instance() );
            }
        );
    }

    private function initialize_localize_data() {
        add_filter(
            'yaymail_additional_localized_variables',
            function ( $data ) {
                if ( function_exists( 'wc_advanced_shipment_tracking' ) ) {
                    $tracker_image = [
                        'progress_bar' => esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ) . 'assets/images/progress_bar.png',
                        'icons'        => esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ) . 'assets/images/icons.png',
                        'single_icons' => esc_url( wc_advanced_shipment_tracking()->plugin_dir_url() ) . 'assets/images/single_icons.png',
                    ];
                } else {
                    $tracker_image = [
                        'progress_bar' => esc_url( ast_pro()->plugin_dir_url() ) . 'assets/images/progress_bar.png',
                        'icons'        => esc_url( ast_pro()->plugin_dir_url() ) . 'assets/images/icons.png',
                        'single_icons' => esc_url( ast_pro()->plugin_dir_url() ) . 'assets/images/single_icons.png',
                    ];
                }
                $upload_dir                       = wp_upload_dir();
                $ast_directory                    = $upload_dir['baseurl'] . '/ast-shipping-providers/';
                $data['ast_tracking_information'] = [
                    'date_shipped'   => esc_html( date_i18n( get_option( 'date_format' ), strtotime( gmdate( 'm-d-y' ) ) ) ),
                    'tracker_image'  => $tracker_image,
                    'provider_image' => esc_url( $ast_directory . 'usps.png' ),
                ];
                return $data;
            }
        );
    }
}
