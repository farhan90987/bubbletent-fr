<?php

namespace YayMail\Integrations\MakeCommerce\Shortcodes;

use YayMail\Utils\Helpers;
use YayMail\Utils\SingletonTrait;
use YayMail\Utils\TemplateHelpers;

/**
 * ShippingShortcodes
 *
 * @method static ShippingShortcodes get_instance()
 */
class ShippingShortcodes extends \YayMail\Abstracts\BaseShortcode {

    use SingletonTrait;

    public function get_shortcodes() {
        return [
            [
                'name'        => 'yaymail_makecommerce_carrier_name',
                'description' => __( 'Carrier Name', 'yaymail' ),
                'group'       => 'MakeCommerce Shipping Details',
                'callback'    => [ $this, 'get_carrier_name' ],
            ],
            [
                'name'        => 'yaymail_makecommerce_machine_name',
                'description' => __( 'Machine Name', 'yaymail' ),
                'group'       => 'MakeCommerce Shipping Details',
                'callback'    => [ $this, 'get_machine_name' ],
            ],
            [
                'name'        => 'yaymail_makecommerce_machine_address',
                'description' => __( 'Machine Address', 'yaymail' ),
                'group'       => 'MakeCommerce Shipping Details',
                'callback'    => [ $this, 'get_machine_address' ],
            ],
            [
                'name'        => 'yaymail_makecommerce_parcel_machine_information',
                'description' => __( 'Parcel Machine Information', 'yaymail' ),
                'group'       => 'MakeCommerce Shipping Details',
                'callback'    => [ $this, 'get_parcel_machine_information' ],
            ],
            [
                'name'        => 'yaymail_makecommerce_delivery_times',
                'description' => __( 'Delivery Times ( Only exists for smartpost courier )', 'yaymail' ),
                'group'       => 'MakeCommerce Shipping Details',
                'callback'    => [ $this, 'get_delivery_times' ],
            ],
            [
                'name'        => 'yaymail_makecommerce_shipment_id',
                'description' => __( 'Shipment ID', 'yaymail' ),
                'group'       => 'MakeCommerce Shipping Details',
                'callback'    => [ $this, 'get_shipment_id' ],
            ],
            [
                'name'        => 'yaymail_makecommerce_tracking_url',
                'description' => __( 'Tracking URL', 'yaymail' ),
                'group'       => 'MakeCommerce Shipping Details',
                'callback'    => [ $this, 'get_tracking_url' ],
            ],
            [
                'name'        => 'yaymail_makecommerce_tracking_link',
                'description' => __( 'Tracking Link', 'yaymail' ),
                'group'       => 'MakeCommerce Shipping Details',
                'callback'    => [ $this, 'get_tracking_link' ],
            ],
            [
                'name'        => 'yaymail_makecommerce_tracking_information',
                'description' => __( 'Tracking Information', 'yaymail' ),
                'group'       => 'MakeCommerce Shipping Details',
                'callback'    => [ $this, 'get_tracking_information' ],
            ],
        ];
    }

    public function get_carrier_name( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return __( 'sample carrier', 'yaymail' );
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            /**
             * Not having order
             */
            return '';
        }

        $shipment_data = self::get_data_from_order( $order );

        return $shipment_data['carrier'];
    }

    public function get_machine_name( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return __( 'sample machine', 'yaymail' );
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            /**
             * Not having order
             */
            return '';
        }

        $shipping_data = self::get_data_from_order( $order );

        return $shipping_data['machine']['name'] ?? '';
    }

    public function get_machine_address( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return __( 'sample machine address', 'yaymail' );
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            /**
             * Not having order
             */
            return '';
        }

        $shipping_data = self::get_data_from_order( $order );

        return $shipping_data['machine']['address'] ?? '';
    }

    public function get_parcel_machine_information( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return __( '<strong>Parcel machine (sample carrier):</strong> sample machine - sample address', 'yaymail' );
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            /**
             * Not having order
             */
            return '';
        }

        $shipping_data = self::get_data_from_order( $order );

        $carrier_name    = ucfirst( $shipping_data['carrier'] ?? '' );
        $machine_name    = $shipping_data['machine']['name'] ?? '';
        $machine_address = $shipping_data['machine']['address'] ?? '';

        if ( strtolower( $shipping_data['carrier'] ) === 'lp_express_lt' ) {
            $carrier_name = '(LP Express)';
        }

        $result = '<strong>Parcel machine' . ( ! empty( $carrier_name ) ? ' (' . $carrier_name . ')' : '' ) . ( ! empty( $machine_name ) || ! empty( $machine_address ) ? ':' : '' ) . '</strong> ';

        if ( ! empty( $machine_name ) ) {
            $result .= $machine_name;
        }

        if ( ! empty( $machine_address ) ) {
            if ( ! empty( $machine_name ) ) {
                $result .= ' - ';
            }
            $result .= $machine_address;
        }

        return $result;
    }

    public function get_delivery_times( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return __( 'Delivery time', 'wc_makecommerce_domain' ) . ': ' . __( 'Any time', 'wc_makecommerce_domain' );
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            /**
             * Not having order
             */
            return '';
        }

        $shipment_id_error = $order->get_meta( '_parcel_machine_error', true );

        $delivery_times = [];

        foreach ( $order->get_shipping_methods() as $shipping_method ) {

            if ( 'courier_smartpost' === $shipping_method['method_id'] ) {
                $delivery_time = $order->get_meta( '_delivery_time', true );

                if ( ! $shipment_id_error ) {

                    if ( '1' === $delivery_time ) {

                        $delivery_times[] = [
                            'label' => __( 'Delivery time', 'wc_makecommerce_domain' ),
                            'value' => __( 'Any time', 'wc_makecommerce_domain' ),
                        ];
                    }

                    if ( '2' === $delivery_time ) {

                        $delivery_times[] = [
                            'label' => __( 'Delivery time', 'wc_makecommerce_domain' ),
                            'value' => '09:00..17:00',
                        ];
                    }

                    if ( '3' === $delivery_time ) {

                        $delivery_times[] = [
                            'label' => __( 'Delivery time', 'wc_makecommerce_domain' ),
                            'value' => '17:00..21:00',
                        ];
                    }
                }//end if
            }//end if
        }//end foreach

        $result = '';

        foreach ( $delivery_times as $delivery_time ) {
            $result .= '<strong>' . $delivery_time['label'] . ':</strong> ' . $delivery_time['value'] . '<br>';
        }

        return $result;
    }

    public function get_shipment_id( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return __( 'sample shipment ID', 'yaymail' );
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            /**
             * Not having order
             */
            return '';
        }

        $shipping_data = self::get_data_from_order( $order );

        $shipment_id = $shipping_data['shipment_id'];

        $carrier = $shipping_data['carrier'];

        if ( empty( $shipment_id ) || 'lp_express_lt' === strtolower( $carrier ) ) {
            return '';
        }

        return $shipment_id;
    }

    public function get_tracking_url( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return home_url();
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            return '';
        }

        $shipping_data = self::get_data_from_order( $order );
        return $shipping_data['tracking_url'];
    }

    public function get_tracking_link( $data ) {

        $tracking_url = $this->get_tracking_url( $data );

        if ( empty( $tracking_url ) ) {
            return '';
        }

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];
        $order       = Helpers::get_order_from_shortcode_data( $render_data );

        $text_link = $tracking_url;

        $shipping_data = self::get_data_from_order( $order );
        $shipment_id   = $shipping_data['shipment_id'];

        if ( ! self::is_legacy_makecommerce_order() && $shipment_id ) {
            $text_link = $shipment_id;
        }

        return '<a target="_blank" title="' . __( 'Shipment tracking code', 'wc_makecommerce_domain' ) . '" href="' . $tracking_url . '">' . $text_link . '</a>';
    }

    public function get_tracking_information( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return '<strong>' . __( 'Shipment tracking info', 'wc_makecommerce_domain' ) . ' ( ' . __( 'sample carrier', 'wc_makecommerce_domain' ) . ' ):</strong> ' . $this->get_tracking_link( $data );
        }

        $order         = Helpers::get_order_from_shortcode_data( $render_data );
        $tracking_link = $this->get_tracking_link( $data );
        $shipping_data = self::get_data_from_order( $order );
        $carrier       = $shipping_data['carrier'];
        $shipment_id   = $shipping_data['shipment_id'];

        if ( self::is_legacy_makecommerce_order() ) {
            if ( empty( $tracking_link ) && empty( $tracking_link ) ) {
                return '';
            }

            if ( ! $shipment_id || strtolower( $carrier ) === 'lp_express_lt' ) {
                return '';
            }

            if ( empty( $carrier ) ) {
                foreach ( $order->get_shipping_methods() as $shipping_method ) {
                    $carriers = explode( '_', $shipping_method['method_id'] );
                    if ( isset( $carriers[1] ) ) {
                        $carrier = $carriers[1];
                    }
                }
            }

            if ( ! empty( $tracking_link ) ) {
                return '<strong>' . __( 'Shipment tracking info', 'wc_makecommerce_domain' ) . ' (' . $carrier . '):</strong> ' . $tracking_link;
            }

            return '<strong>' . __( 'Shipment tracking info', 'wc_makecommerce_domain' ) . ':</strong> ' . $shipment_id;
        }//end if

        if ( ! empty( $tracking_link ) ) {
            return '<strong>' . __( 'Shipment tracking info:', 'wc_makecommerce_domain' ) . '</strong> ' . $tracking_link;
        } elseif ( $shipment_id ) {
            return '<strong>' . __( 'Shipment tracking info:', 'wc_makecommerce_domain' ) . '</strong> ' . $shipment_id;
        }

        return '';
    }

    public static function get_data_from_order( $order ) {

        $fallback_data = [
            'carrier'         => '',
            'machine'         => '',
            'shipment_id'     => '',
            'tracking_url'    => '',
            'machine_id'      => '',
            'shipping_method' => '',
        ];

        try {

            if ( ! class_exists( '\MakeCommerce\Shipping' ) ) {
                return $fallback_data;
            }

            if ( empty( $order ) || ! ( $order instanceof \WC_Order ) ) {
                return $fallback_data;
            }

            $order           = wc_get_order( $order->get_id() );
            $shipping_method = $order->get_shipping_method();
            $country         = $order->get_shipping_country();

            global $makecommerce_machines;
            if ( ! self::is_legacy_makecommerce_order() ) {
                $carrier    = $order->get_meta( '_mc_shipping_carrier', true );
                $machine_id = $order->get_meta( '_mc_machine_id', true );
                if ( empty( $makecommerce_machines[ $carrier . $country ] ) ) {
                    $makecommerce_machines[ $carrier . $country ] = \MakeCommerce\Shipping::mk_get_machine( $carrier, $machine_id, $country );
                }
                $machine      = $makecommerce_machines[ $carrier . $country ];
                $shipment_id  = $order->get_meta( '_parcel_machine_shipment_id', true );
                $tracking_url = $order->get_meta( '_mc_tracking_link', true );
            } else {
                $carrier = '';
                $machine = '';

                $machine_id = $order->get_meta( '_parcel_machine', true );

                $machine_id_arr = explode( '||', $machine_id );

                if ( 2 === count( $machine_id_arr ) ) {

                    $carrier = $machine_id_arr[0];
                    $machine = $machine_id_arr[1];
                } elseif ( 1 === count( $machine_id_arr ) ) {

                    $carrier = $machine_id_arr[0];
                }

                if ( empty( $makecommerce_machines[ $carrier . $machine_id ] ) ) {
                    $makecommerce_machines[ $carrier . $machine_id ] = \MakeCommerce\Shipping::mk_get_machine( $carrier, $machine );
                }
                $machine = $makecommerce_machines[ $carrier . $machine_id ];

                $dst  = substr( strtolower( $order->get_shipping_country() ), 0, 2 );
                $lang = substr( strtolower( $order->get_meta( 'wpml_language', true ) ), 0, 2 );
                if ( empty( $lang ) ) {
                    if ( function_exists( 'pll_current_language' ) ) {
                        $lang = pll_current_language();
                    }
                }

                $shipment_id = $order->get_meta( '_parcel_machine_shipment_id', true );
                $link        = MC_TRACKING_SERVICE_URL . urlencode( $shipment_id );

                $params = [
                    'carrier' => urlencode( $carrier ),
                    'dst'     => urlencode( $dst ),
                    'lang'    => urlencode( $lang ),
                ];

                $tracking_url = esc_url( add_query_arg( $params, $link ) );

            }//end if

            return [
                'carrier'         => $carrier,
                'machine_id'      => $machine_id,
                'machine'         => $machine,
                'shipment_id'     => $shipment_id,
                'shipping_method' => $shipping_method,
                'tracking_url'    => $tracking_url,
            ];

        } catch ( \Exception $e ) {
            return $fallback_data;
        }//end try
    }

    public static function is_legacy_makecommerce_order() {
        return version_compare( MAKECOMMERCE_VERSION, '4.0.0' ) < 0;
    }
}
