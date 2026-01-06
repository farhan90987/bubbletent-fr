<?php

namespace YayMail\Integrations\WooCommerceShipping\Shortcodes;

use YayMail\Utils\Helpers;
use YayMail\Utils\SingletonTrait;

/**
 * ShippingShortcodes
 *
 * @method static ShippingShortcodes get_instance()
 */
class ShippingShortcodes extends \YayMail\Abstracts\BaseShortcode {

    use SingletonTrait;

    public function get_shortcodes() {
        return [
            // [
            // 'name'        => 'yaymail_wc_shipping_tracking_number',
            // 'description' => __( 'Tracking Number', 'yaymail' ),
            // 'group'       => 'WooCommerce Shipping',
            // 'callback'    => [ $this, 'get_tracking_number' ],
            // ],
            // [
            // 'name'        => 'yaymail_wc_shipping_tracking_url',
            // 'description' => __( 'Tracking URL', 'yaymail' ),
            // 'group'       => 'WooCommerce Shipping',
            // 'callback'    => [ $this, 'get_tracking_url' ],
            // ],
            // [
            // 'name'        => 'yaymail_wc_shipping_tracking_link',
            // 'description' => __( 'Tracking Link', 'yaymail' ),
            // 'group'       => 'WooCommerce Shipping',
            // 'callback'    => [ $this, 'get_tracking_link' ],
            // ],
            // [
            // 'name'        => 'yaymail_wc_shipping_carrier_name',
            // 'description' => __( 'Carrier Name', 'yaymail' ),
            // 'group'       => 'WooCommerce Shipping',
            // 'callback'    => [ $this, 'get_carrier_name' ],
            // ],
            // [
            // 'name'        => 'yaymail_wc_shipping_service_name',
            // 'description' => __( 'Service Name', 'yaymail' ),
            // 'group'       => 'WooCommerce Shipping',
            // 'callback'    => [ $this, 'get_service_name' ],
            // ],
            [
                'name'        => 'yaymail_wc_shipping_tracking_items',
                'description' => __( 'Tracking Items', 'yaymail' ),
                'group'       => 'WooCommerce Shipping',
                'callback'    => [ $this, 'get_tracking_items' ],
            ],
        ];
    }

    public function get_tracking_number( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return '#12345';
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            /**
             * Not having order
             */
            return '';
        }

        $shipping_data = self::get_data_from_order( $order );

        return $shipping_data['tracking_number'];
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
            /**
             * Not having order
             */
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

        $template = ! empty( $data['template'] ) ? $data['template'] : null;

        $text_link_color = ! empty( $template ) ? $template->get_text_link_color() : YAYMAIL_COLOR_WC_DEFAULT;

        $text_link = $tracking_url;

        return '<a target="_blank" href="' . $tracking_url . '">' . $text_link . '</a>';
    }

    public function get_carrier_name( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return 'YayMail';
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            /**
             * Not having order
             */
            return '';
        }

        $shipping_data = self::get_data_from_order( $order );

        return $shipping_data['carrier'];
    }

    public function get_service_name( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            return 'YayMail';
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            /**
             * Not having order
             */
            return '';
        }

        $shipping_data = self::get_data_from_order( $order );

        return $shipping_data['service_name'];
    }

    public function get_tracking_items( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            $tracking_items = [
                [
                    'custom_tracking_provider' => 'YayMail',
                    'tracking_number'          => '#12345',
                    'custom_tracking_link'     => home_url(),
                ],
            ];
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) && empty( $render_data['is_sample'] ) ) {
            /**
             * Not having order
             */
            return '';
        }

        if ( empty( $render_data['is_sample'] ) ) {
            $shipping_data = self::get_data_from_order( $order );

            $tracking_items = $shipping_data['tracking_items'];
        }

        if ( empty( $tracking_items ) ) {
            return '';
        }

        ob_start();
        $table_style = 'min-width: 100%;border: 1px solid #dddddd;';
        $cell_style  = 'padding: 12px; border: 1px solid #dddddd;';
        $th_style    = $cell_style . 'text-align: left;';
        $td_style    = $cell_style . 'text-align: left;';
        ?>
        <table class="yaymail-wc-shipping-tracking-items-shortcode" style="<?php echo esc_attr( $table_style ); ?>" cellspacing="0" cellpadding="6" width="100%" border="1">
            <thead class="yaymail-wc-shipping-tracking-items-shortcode-heading">
                <tr>
                    <th style="<?php echo esc_attr( $th_style ); ?>" class="yaymail-wc-shipping-tracking-items-shortcode-heading--provider"><?php echo esc_html__( 'Provider', 'yaymail' ); ?></th>
                    <th style="<?php echo esc_attr( $th_style ); ?>" class="yaymail-wc-shipping-tracking-items-shortcode-heading-tracking-number"><?php echo esc_html__( 'Tracking number', 'yaymail' ); ?></th>
                    <th style="<?php echo esc_attr( $th_style ); ?>" class="yaymail-wc-shipping-tracking-items-shortcode-heading-tracking-link"><?php echo esc_html__( 'Tracking link', 'yaymail' ); ?></th>
                </tr>
            </thead>
            <tbody class="yaymail-wc-shipping-tracking-items-shortcode-body">
                <?php
                foreach ( $tracking_items as $tracking_item ) :
                    ?>
                    <tr class="yaymail-wc-shipping-tracking-items-shortcode-item">
                        <td style="<?php echo esc_attr( $td_style ); ?>" class="yaymail-wc-shipping-tracking-items-shortcode-item--provider"><?php echo esc_html( $tracking_item['custom_tracking_provider'] ); ?></td>
                        <td style="<?php echo esc_attr( $td_style ); ?>" class="yaymail-wc-shipping-tracking-items-shortcode-item--tracking-number"><?php echo esc_html( $tracking_item['tracking_number'] ); ?></td>
                        <td style="<?php echo esc_attr( $td_style ); ?>" class="yaymail-wc-shipping-tracking-items-shortcode-item--tracking-link"><?php echo esc_html( $tracking_item['custom_tracking_link'] ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        $tracking_items_html = ob_get_contents();
        ob_end_clean();

        return $tracking_items_html;
    }

    public static function get_data_from_order( $order ) {

        global $wc_shipping_shipping_data;

        if ( ! empty( $wc_shipping_shipping_data[ $order->get_id() ] ) ) {
            return $wc_shipping_shipping_data[ $order->get_id() ];
        }

        $fallback_data = [
            'carrier'         => '',
            'tracking_number' => '',
            'tracking_url'    => '',
            'service_name'    => '',
            'tracking_items'  => [],
        ];

        try {
            $labels_data = self::get_labels_data_from_order( $order );

            require_once WCSHIPPING_PLUGIN_DIR . '/classes/class-wc-connect-api-client-live.php';
            $loader        = new \Automattic\WCShipping\Loader();
            $validator     = new \Automattic\WCShipping\Connect\WC_Connect_Service_Schemas_Validator();
            $core_logger   = new \WC_Logger();
            $logger        = new \Automattic\WCShipping\Connect\WC_Connect_Logger( $core_logger );
            $api_client    = new \Automattic\WCShipping\Connect\WC_Connect_API_Client_Live( $validator, $loader );
            $schemas_store = new \Automattic\WCShipping\Connect\WC_Connect_Service_Schemas_Store( $api_client, $logger );

            $service_schemas_store = $schemas_store;

            foreach ( $labels_data as $index => $label_data ) {
                if ( isset( $label_data['tracking'] ) ) {
                    $tracking_number = $label_data['tracking'];
                    $carrier         = $label_data['carrier_id'];
                    $service_name    = $label_data['service_name'];

                    if ( 'upsdap' === $carrier ) {
                        $carrier = 'ups';
                    }

                    // Get carrier name
                    $carrier_service = $service_schemas_store->get_service_schema_by_id( $carrier );
                    $carrier_name    = ( ! $carrier_service || empty( $carrier_service->carrier_name ) ) ? strtoupper( $carrier ) : $carrier_service->carrier_name;

                    // Get tracking url
                    $tracking_urls = [
                        'ups'        => 'https://www.ups.com/track?tracknum=',
                        'upsdap'     => 'https://www.ups.com/track?tracknum=',
                        'usps'       => 'https://tools.usps.com/go/TrackConfirmAction?tLabels=',
                        'fedex'      => 'https://www.fedex.com/apps/fedextrack/?tracknumbers=',
                        'dhlexpress' => 'https://www.dhl.com/en/express/tracking.html?AWB=',
                    ];
                    $tracking_url  = ( isset( $tracking_urls[ $carrier ] ) ? $tracking_urls[ $carrier ] : '' ) . $tracking_number;

                    // Get tracking items

                    $tracking_item = [
                        'tracking_provider'        => '',
                        'custom_tracking_provider' => \wc_clean( $service_name ? $service_name : $carrier ),
                        'tracking_number'          => \wc_clean( $tracking_number ),
                        'custom_tracking_link'     => \wc_clean( $tracking_url ),
                    ];
                    // Generate a unique key for the tracking item.
                    $key                          = md5( "{$tracking_item['custom_tracking_provider']}-{$tracking_item['tracking_number']}" . microtime() );
                    $tracking_item['tracking_id'] = $key;
                    $tracking_items               = $order->get_meta( '_wc_shipment_tracking_items' );
                    $tracking_items               = is_array( $tracking_items ) ? $tracking_items : [];
                    if ( empty( $tracking_items ) ) {
                        $tracking_items[] = $tracking_item;
                    }
                    $tracking_items = apply_filters( 'wcshipping_tracking_before_add_tracking_items', $tracking_items, $tracking_item, $order->get_id() );

                    return [
                        'tracking_number' => $tracking_number,
                        'carrier'         => $carrier_name,
                        'service_name'    => $service_name,
                        'tracking_url'    => $tracking_url,
                        'tracking_items'  => $tracking_items,
                    ];
                }//end if
            }//end foreach
        } catch ( \Exception $e ) {
            return $fallback_data;
        }//end try

        return $fallback_data;
    }

    public static function get_labels_data_from_order( $order ) {

        if ( ! $order instanceof \WC_Order ) {
            return [];
        }

        $use_legacy_key = false;

        $label_data = $order->get_meta( $use_legacy_key ? 'wc_connect_labels' : 'wcshipping_labels', true );

        if ( ! $label_data ) {
            return [];
        }

        if ( is_array( $label_data ) ) {
            return $label_data;
        }

        $decoded_labels = json_decode( $label_data, true );
        if ( $decoded_labels ) {
            return $decoded_labels;
        }

        $label_data     = self::try_recover_invalid_json_string( 'package_name', $label_data );
        $decoded_labels = json_decode( $label_data, true );
        if ( $decoded_labels ) {
            return $decoded_labels;
        }

        $label_data     = self::try_recover_invalid_json_array( 'product_names', $label_data );
        $decoded_labels = json_decode( $label_data, true );
        if ( $decoded_labels ) {
            return $decoded_labels;
        }

        return [];
    }

    public static function try_recover_invalid_json_string( $field_name, $json ) {
        $regex = '/"' . $field_name . '":"(.+?)","/';
        preg_match_all( $regex, $json, $match_groups );
        if ( 2 === count( $match_groups ) ) {
            foreach ( $match_groups[0] as $idx => $match ) {
                $value         = $match_groups[1][ $idx ];
                $escaped_value = preg_replace( '/(?<!\\\)"/', '\\"', $value );
                $json          = str_replace( $match, '"' . $field_name . '":"' . $escaped_value . '","', $json );
            }
        }
        return $json;
    }

    public static function try_recover_invalid_json_array( $field_name, $json ) {
        $regex = '/"' . $field_name . '":\["(.+?)"\]/';
        preg_match_all( $regex, $json, $match_groups );
        if ( 2 === count( $match_groups ) ) {
            foreach ( $match_groups[0] as $idx => $match ) {
                $array         = $match_groups[1][ $idx ];
                $escaped_array = preg_replace( '/(?<![,\\\])"(?!,)/', '\\"', $array );
                $json          = str_replace( '["' . $array . '"]', '["' . $escaped_array . '"]', $json );
            }
        }
        return $json;
    }
}
