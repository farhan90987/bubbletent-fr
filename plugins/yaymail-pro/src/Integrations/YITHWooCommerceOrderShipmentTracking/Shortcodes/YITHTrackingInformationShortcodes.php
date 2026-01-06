<?php

namespace YayMail\Integrations\YITHWooCommerceOrderShipmentTracking\Shortcodes;

use YayMail\Utils\Helpers;
use YayMail\Utils\SingletonTrait;
use YayMail\Abstracts\BaseShortcode;
use YayMail\Utils\TemplateHelpers;

/**
 * YITHTrackingInformationShortcodes
 * * @method static YITHTrackingInformationShortcodes get_instance()
 */
class YITHTrackingInformationShortcodes extends BaseShortcode {
    use SingletonTrait;

    public function get_yith_tracking_data( $order ) {
        if ( class_exists( 'YITH_Tracking_Data' ) ) {
            $tracking_data = \YITH_Tracking_Data::get( $order );
            return $tracking_data;
        }
        if ( function_exists( 'yith_ywot_init' ) ) {
            return get_post_custom( yit_get_prop( $order, 'id' ) );
        }
    }

    public function get_shortcodes() {
        $shortcodes   = [];
        $shortcodes[] = [
            'name'        => 'yaymail_yith_tracking_carrier_name',
            'description' => __( 'YITH Tracking Information - Carrier Name', 'yaymail' ),
            'group'       => 'yith_tracking_information',
            'callback'    => [ $this, 'yith_tracking_carrier_name' ],
        ];
        $shortcodes[] = [
            'name'        => 'yaymail_yith_tracking_pickup_date',
            'description' => __( 'YITH Tracking Information - Pickup Date', 'yaymail' ),
            'group'       => 'yith_tracking_information',
            'callback'    => [ $this, 'yith_tracking_pickup_date' ],
        ];
        $shortcodes[] = [
            'name'        => 'yaymail_yith_tracking_code',
            'description' => __( 'YITH Tracking Information - Track Code', 'yaymail' ),
            'group'       => 'yith_tracking_information',
            'callback'    => [ $this, 'yith_tracking_code' ],
        ];

        if ( class_exists( 'YITH_Tracking_Data' ) ) {
            $shortcodes[] = [
                'name'        => 'yaymail_yith_tracking_estimated_delivery',
                'description' => __( 'YITH Tracking Information - Estimated Delivery', 'yaymail' ),
                'group'       => 'yith_tracking_information',
                'callback'    => [ $this, 'yith_tracking_estimated_delivery' ],
            ];
            $shortcodes[] = [
                'name'        => 'yaymail_yith_order_tracking_link',
                'description' => __( 'YITH Tracking Information - Track Link', 'yaymail' ),
                'attributes'  => [
                    'text_link' => __( 'Live track your order', 'yith-woocommerce-order-tracking' ),
                ],
                'group'       => 'yith_tracking_information',
                'callback'    => [ $this, 'yith_order_tracking_link' ],
            ];

            $shortcodes[] = [
                'name'        => 'yaymail_yith_order_tracking_url',
                'description' => __( 'YITH Tracking Information - Track URL', 'yaymail' ),
                'group'       => 'yith_tracking_information',
                'callback'    => [ $this, 'yith_order_tracking_url' ],
            ];
        }//end if

        if ( function_exists( 'yith_ywot_init' ) ) {
            $shortcodes[] = [
                'name'        => 'yaymail_yith_order_carrier_link',
                'description' => __( 'YITH Tracking Information - Carrier Link', 'yaymail' ),
                'attributes'  => [
                    'text_link' => __( 'Live track your order', 'yith-woocommerce-order-tracking' ),
                ],
                'group'       => 'yith_tracking_information',
                'callback'    => [ $this, 'yith_order_carrier_link' ],
            ];

            $shortcodes[] = [
                'name'        => 'yaymail_yith_order_carrier_url',
                'description' => __( 'YITH Tracking Information - Carrier URL (String)', 'yaymail' ),
                'group'       => 'yith_tracking_information',
                'callback'    => [ $this, 'yith_order_carrier_url' ],
            ];
        }

        return $shortcodes;
    }

    public function yith_order_carrier_url( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return date_i18n( get_option( 'date_format' ), strtotime( get_option( 'date_format' ) ) );
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) || empty( $order->get_id() ) ) {
            /**
             * Not having order/order_id
             */
            return __( 'No order found', 'yaymail' );
        }

        $tracking_data = $this->get_yith_tracking_data( $order );

        $order_carrier_link = isset( $tracking_data['ywot_carrier_url'][0] ) ? $tracking_data['ywot_carrier_url'][0] : '';

        return $order_carrier_link;
    }

    public function yith_order_carrier_link( $data, $shortcode_atts = [] ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        $is_placeholder = isset( $data['is_placeholder'] ) ? $data['is_placeholder'] : false;

        $text_link = isset( $shortcode_atts['text_link'] ) ? $shortcode_atts['text_link'] : TemplateHelpers::get_content_as_placeholder( 'text_link', __( 'Live track your order', 'yith-woocommerce-order-tracking' ), $is_placeholder );

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */

            $html = "<a href='#'>" . $text_link . '</a>';

            return $html;
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) || empty( $order->get_id() ) ) {
            /**
             * Not having order/order_id
             */
            return __( 'No order found', 'yaymail' );
        }

        $order_carrier_link = $this->yith_order_carrier_url( $data );

        $html = "<a href='" . esc_url( $order_carrier_link ) . "'>" . $text_link . '</a>';

        return $html;
    }

    public function yith_order_tracking_url( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */

            return get_home_url();
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) || empty( $order->get_id() ) ) {
            /**
             * Not having order/order_id
             */
            return __( 'No order found', 'yaymail' );
        }

        $tracking_data = $this->get_yith_tracking_data( $order );

        $order_track_url = '';

        if ( $tracking_data->is_pickedup() ) {
            $carriers = \Carriers::get_instance()->get_carrier_list();

            $tracking_code     = strval( $tracking_data->get_tracking_code() );
            $tracking_postcode = $tracking_data->get_tracking_postcode();
            $carrier_id        = $tracking_data->get_carrier_id();

            if ( ! isset( $carriers[ $carrier_id ] ) ) {
                return '';
            }

            $carrier_object = $carriers[ $carrier_id ];

            // Check if tracking code is single or multiple
            if ( strpos( $tracking_code, '{' ) !== false ) {
                $order_track_url = $carrier_object['track_url'];

                preg_match_all( '/{(.*?)}/', $tracking_code, $words );

                $length_word = count( $words[1] );

                for ( $i = 0; $i < $length_word; $i++ ) {
                    $order_track_url = str_replace( '[TRACK_CODE][' . $i . ']', $words[1][ $i ], $order_track_url );
                }
            } else {

                $text            = [ '[TRACK_CODE]', '[TRACK_POSTCODE]' ];
                $codes           = [ $tracking_code, $tracking_postcode ];
                $order_track_url = str_replace( $text, $codes, $carrier_object['track_url'] );
            }

            if ( strpos( $order_track_url, '[TRACK_YEAR]' ) !== false || strpos( $order_track_url, '[TRACK_MONTH]' ) !== false || strpos( $order_track_url, '[TRACK_DAY]' ) !== false ) {
                $pickup_date     = strval( $tracking_data->get_pickup_date() );
                $array_date      = explode( '-', $pickup_date );
                $order_track_url = str_replace( '[TRACK_YEAR]', $array_date[0], $order_track_url );
                $order_track_url = str_replace( '[TRACK_MONTH]', $array_date[1], $order_track_url );
                $order_track_url = str_replace( '[TRACK_DAY]', $array_date[2], $order_track_url );
            }
        }//end if

        return $order_track_url;
    }

    public function yith_order_tracking_link( $data, $shortcode_atts = [] ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        $is_placeholder = isset( $data['is_placeholder'] ) ? $data['is_placeholder'] : false;

        $text_link = isset( $shortcode_atts['text_link'] ) ? $shortcode_atts['text_link'] : TemplateHelpers::get_content_as_placeholder( 'text_link', __( 'Live track your order', 'yith-woocommerce-order-tracking' ), $is_placeholder );

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */

            $html = "<a href='#'>" . $text_link . '</a>';

            return $html;
        }

        $order_track_url = $this->yith_order_tracking_url( $data );

        $html = "<a href='" . esc_url( $order_track_url ) . "'>" . $text_link . '</a>';

        return $html;
    }

    public function yith_tracking_estimated_delivery( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return '2';
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) || empty( $order->get_id() ) ) {
            /**
             * Not having order/order_id
             */
            return __( 'No order found', 'yaymail' );
        }

        $tracking_data = $this->get_yith_tracking_data( $order );

        $estimated_delivery_date = $tracking_data->get_estimated_delivery_date();

        return $estimated_delivery_date;
    }

    public function yith_tracking_code( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return 'SAMPLE_TRACKING_CODE';
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) || empty( $order->get_id() ) ) {
            /**
             * Not having order/order_id
             */
            return __( 'No order found', 'yaymail' );
        }

        $tracking_code = '';

        $tracking_data = $this->get_yith_tracking_data( $order );

        if ( class_exists( 'YITH_Tracking_Data' ) ) {
            $tracking_code = strval( $tracking_data->get_tracking_code() );

            if ( strpos( $tracking_code, '{' ) !== false ) {
                preg_match_all( '/{(.*?)}/', $tracking_code, $words );
                $tracking_code = implode( ' ', $words[1] );
            }
        }

        if ( function_exists( 'yith_ywot_init' ) ) {
            $tracking_code = isset( $tracking_data['ywot_tracking_code'][0] ) ? $tracking_data['ywot_tracking_code'][0] : '';
        }

        return $tracking_code;
    }

    public function yith_tracking_pickup_date( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return date_i18n( get_option( 'date_format' ), strtotime( get_option( 'date_format' ) ) );
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) || empty( $order->get_id() ) ) {
            /**
             * Not having order/order_id
             */
            return '';
        }

        $pickup_date = '';

        $tracking_data = $this->get_yith_tracking_data( $order );

        if ( class_exists( 'YITH_Tracking_Data' ) ) {
            $pickup_date = strval( $tracking_data->get_pickup_date() );
        }

        if ( function_exists( 'yith_ywot_init' ) ) {
            $pickup_date = isset( $tracking_data['ywot_pick_up_date'][0] ) ? strval( $tracking_data['ywot_pick_up_date'][0] ) : '';
        }

        return date_i18n( get_option( 'date_format' ), strtotime( $pickup_date ) );
    }

    public function yith_tracking_carrier_name( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        $carrier_default_name = get_option( 'ywot_carrier_default_name' );

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return $carrier_default_name;
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) || empty( $order->get_id() ) ) {
            /**
             * Not having order/order_id
             */
            return __( 'No order found', 'yaymail' );
        }

        $carrier_name = '';

        $tracking_data = $this->get_yith_tracking_data( $order );

        if ( class_exists( 'YITH_Tracking_Data' ) ) {
            $carriers = \Carriers::get_instance()->get_carrier_list();

            $carrier_id = $tracking_data->get_carrier_id();

            $carrier_name = isset( $carriers[ $carrier_id ]['name'] ) ? $carriers[ $carrier_id ]['name'] : '';
        }

        if ( function_exists( 'yith_ywot_init' ) ) {
            $carrier_name = isset( $tracking_data['ywot_carrier_name'][0] ) ? $tracking_data['ywot_carrier_name'][0] : '';
        }

        return $carrier_name;
    }
}
