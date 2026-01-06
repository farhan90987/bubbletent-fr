<?php

namespace YayMail\Integrations\WooCommerceShippingTax\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Helpers Classes
 */
class Helpers {

    public static function get_service_schemas( $carrier ) {
        $service_schemas = \WC_Connect_Options::get_option( 'services', null );
        if ( ! is_object( $service_schemas ) ) {
            return null;
        }

        foreach ( $service_schemas as $service_type => $service_type_service_schemas ) {
            $matches = wp_filter_object_list( $service_type_service_schemas, [ 'id' => $carrier ] );
            if ( $matches ) {
                return array_shift( $matches );
            }
        }
    }
}
