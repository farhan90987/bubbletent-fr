<?php

namespace YayMail\Integrations\WoocommerceShipmentTrackingProByPluginHive\Shortcodes;

use YayMail\Utils\Helpers;
use YayMail\Utils\SingletonTrait;
use YayMail\Abstracts\BaseShortcode;

/**
 * TrackingInformationByPluginHiveShortcodes
 * * @method static TrackingInformationByPluginHiveShortcodes get_instance()
 */
class TrackingInformationByPluginHiveShortcodes extends BaseShortcode {
    use SingletonTrait;

    public function get_shortcodes() {
        $shortcodes   = [];
        $shortcodes[] = [
            'name'        => 'yaymail_order_tracking_information_by_pluginhive_service',
            'description' => __( 'PluginHive\'s Tracking Information - Shipment Service', 'yaymail' ),
            'group'       => 'order_details',
            'callback'    => [ $this, 'tracking_service' ],
        ];
        $shortcodes[] = [
            'name'        => 'yaymail_order_tracking_information_by_pluginhive_date',
            'description' => __( 'PluginHive\'s Tracking Information - Shipment Date', 'yaymail' ),
            'group'       => 'order_details',
            'callback'    => [ $this, 'get_tracking_date' ],
        ];
        $shortcodes[] = [
            'name'        => 'yaymail_order_tracking_information_by_pluginhive_tracking_id',
            'description' => __( 'PluginHive\'s Tracking Information - Shipment Tracking ID', 'yaymail' ),
            'group'       => 'order_details',
            'callback'    => [ $this, 'get_tracking_id' ],
        ];

        return $shortcodes;
    }

    public function tracking_service( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            $html = '2GO';
            return $html;
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) || empty( $order->get_id() ) ) {
            /**
             * Not having order/order_id
             */
            return __( 'No order found', 'yaymail' );
        }

        $meta_key = 'wf_wc_shipment_source';

        $order_id      = $order->get_id();
        $args['order'] = $order;

        // References from 3rd-party: Ph_Shipment_Tracking_Util::get_shipment_display_custom_message()
        $shipment_source_data = get_post_meta( $order_id, $meta_key, true );
        $shipping_service_key = $shipment_source_data['shipping_service'];
        $tracking_data        = \Ph_Shipment_Tracking_Util::load_tracking_data();

        $result = isset( $tracking_data[ $shipping_service_key ]['name'] ) ? $tracking_data[ $shipping_service_key ]['name'] : 'No data';

        return $result;
    }

    public function get_tracking_date( $data ) {

        global $wp_version;

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            $html = 'November 1, 2023';
            return $html;
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) || empty( $order->get_id() ) ) {
            /**
             * Not having order/order_id
             */
            return __( 'No order found', 'yaymail' );
        }

        $meta_key = 'wf_wc_shipment_source';

        // References from 3rd-party: Ph_Shipment_Tracking_Util::get_shipment_display_custom_message()
        $shipment_source_data = get_post_meta( $order->get_id(), $meta_key, true );

        if ( ! empty( $shipment_source_data['order_date'] ) ) {
            $wp_date_format = get_option( 'date_format' );
            $formatted_date = new \DateTime( $shipment_source_data['order_date'] );
            $formatted_date = $formatted_date->format( $wp_date_format );

            if ( version_compare( $wp_version, '5.3', '>=' ) ) {

                if ( date_default_timezone_get() ) {

                    $zone = new \DateTimeZone( date_default_timezone_get() );
                } else {

                    $zone = new \DateTimeZone( 'UTC' );
                }

                if ( strtotime( $formatted_date ) ) {

                    $order_date = wp_date( $wp_date_format, strtotime( $formatted_date ), $zone );
                }
            } else {

                if ( strtotime( $formatted_date ) ) {

                    $order_date = date_i18n( $wp_date_format, strtotime( $formatted_date ) );
                }
            }
        } else {

            $order_date = $shipment_source_data['order_date'];
        }

        $result = isset( $order_date ) ? $order_date : 'No data';

        return $result;
    }

    public function get_tracking_id( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            $html = '<a href="#" target="_blank" class="ph_tracking_link">123</a>';
            return $html;
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) || empty( $order->get_id() ) ) {
            /**
             * Not having order/order_id
             */
            return __( 'No order found', 'yaymail' );
        }

        $order_id = $order->get_id();

        // References from 3rd-party: Ph_Shipment_Tracking_Util::get_shipment_display_custom_message()
        $settings = get_option( 'ph_shipment_tracking_settings_data' );
        $url_link = isset( $settings['custom_page_url'] ) ? $settings['custom_page_url'] : '';

        $store_id = '';

        $live_order_packages      = get_post_meta( $order_id, \Ph_Shipment_Tracking_Util::TRACKING_LIVE_API_ORDER, true );
        $store_id                 = get_option( \Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY . '_ph_store_id' );
        $tracking_id_substr       = '';
        $shipment_result_array    = get_post_meta( $order_id, 'wf_wc_shipment_result', true );
        $lookup_page              = true;
        $carrier_page_redirection = isset( $settings['carrier_page_redirection'] ) && ! empty( $settings['carrier_page_redirection'] ) ? $settings['carrier_page_redirection'] : 'yes';

        if ( ! empty( $store_id ) && ! empty( $url_link ) && ! empty( $live_order_packages ) && is_array( $live_order_packages ) ) {

            foreach ( $live_order_packages as $package ) {

                $url_link_with_query = $url_link . '?tracking_number=' . $package['trackingId'];
                $tracking_id_substr .= ' <a href="' . $url_link_with_query . '" target="_blank" class="ph_tracking_link">' . $package['trackingId'] . '</a>,';
            }
        } elseif ( ! empty( $url_link ) && ! empty( $order_id ) && ! $lookup_page ) {

            foreach ( $shipment_result_array['tracking_info'] as $tracking_info ) {

                $url_link_with_query = $url_link . '?OTNum=' . base64_encode( $order_id . '|' . $tracking_info['tracking_id'] );
                $tracking_id_substr .= ' <a href="' . $url_link_with_query . '" target="_blank" class="ph_tracking_link">' . $tracking_info['tracking_id'] . '</a>,';
            }
        } else {

            foreach ( $shipment_result_array['tracking_info'] as $tracking_info ) {

                if ( '' === $tracking_info['tracking_link'] || ( 'yes' !== $carrier_page_redirection && $lookup_page ) ) {
                    $tracking_id_substr .= $tracking_info['tracking_id'] . ',';
                } else {
                    $tracking_id_substr .= ' <a href="' . $tracking_info['tracking_link'] . '" target="_blank" class="ph_tracking_link">' . $tracking_info['tracking_id'] . '</a>,';
                }
            }
        }

        $tracking_id_substr = rtrim( $tracking_id_substr, ',' );
        $tracking_id_substr = trim( $tracking_id_substr );

        $result = isset( $tracking_id_substr ) ? $tracking_id_substr : 'No data';
        return $result;
    }

}
