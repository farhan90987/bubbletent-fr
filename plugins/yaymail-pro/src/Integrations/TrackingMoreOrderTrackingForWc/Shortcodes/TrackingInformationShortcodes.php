<?php

namespace YayMail\Integrations\TrackingMoreOrderTrackingForWc\Shortcodes;

use YayMail\Abstracts\BaseShortcode;
use YayMail\Utils\Helpers;
use YayMail\Utils\SingletonTrait;

/**
 * TrackingInformationShortcodes
 * * @method static TrackingInformationShortcodes get_instance()
 */
class TrackingInformationShortcodes extends BaseShortcode {
    use SingletonTrait;

    private $trackingmore_instance = null;

    public function __construct() {
        if ( class_exists( 'TrackingMore' ) ) {
            $this->trackingmore_instance = \TrackingMore::instance();
        }
        parent::__construct();
    }

    public function get_shortcodes() {

        if ( empty( $this->trackingmore_instance ) ) {
            return [];
        }

        $shortcodes = [];

        $shortcodes[] = [
            'name'        => 'yaymail_order_trackingmore_tracking_number',
            'description' => __( 'TrackingMore Tracking Number', 'yaymail' ),
            'group'       => 'tracking_more_order_tracking',
            'callback'    => [ $this, 'get_trackingmore_tracking_number' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_order_trackingmore_courier',
            'description' => __( 'TrackingMore Courier', 'yaymail' ),
            'group'       => 'tracking_more_order_tracking',
            'callback'    => [ $this, 'get_trackingmore_courier' ],
        ];

        $shortcodes[] = [
            'name'        => 'yaymail_order_trackingmore_tracking_information',
            'description' => __( 'TrackingMore Tracking Information', 'yaymail' ),
            'group'       => 'tracking_more_order_tracking',
            'callback'    => [ $this, 'get_trackingmore_tracking_information' ],
        ];

        return $shortcodes;
    }

    public function get_trackingmore_tracking_number( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            $html = '12345';
            return $html;
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            /**
             * Not having order/order_id
             */
            return __( 'No order found', 'yaymail' );
        }

        $order_id = $order->get_id();
        if ( ! isset( $order_id, $this->trackingmore_instance->trackingmore_fields['trackingmore_tracking_number']['id'] ) ) {
            return __( 'No data', 'yaymail' );
        }
        $tracking_number = get_post_meta( $order_id, '_' . $this->trackingmore_instance->trackingmore_fields['trackingmore_tracking_number']['id'], true );

        $result = isset( $tracking_number ) ? $tracking_number : __( 'No data', 'yaymail' );
        return $result;
    }

    public function get_trackingmore_courier( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        if ( ! empty( $render_data['is_sample'] ) ) {
            /**
             * Is sample order
             */
            $html = 'UPS';
            return $html;
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            /**
             * Not having order/order_id
             */
            return __( 'No order found', 'yaymail' );
        }

        $order_id = $order->get_id();
        if ( ! isset( $order_id, $this->trackingmore_instance->trackingmore_fields['trackingmore_tracking_provider_name']['id'] ) ) {
            return __( 'No data', 'yaymail' );
        }
        $courier = get_post_meta( $order_id, '_' . $this->trackingmore_instance->trackingmore_fields['trackingmore_tracking_provider_name']['id'], true );

        $result = isset( $courier ) ? $courier : __( 'No data', 'yaymail' );
        return $result;
    }

    public function get_trackingmore_tracking_information( $data ) {

        $element = isset( $data['element'] ) ? $data['element'] : [];

        $is_placeholder = isset( $data['is_placeholder'] ) ? $data['is_placeholder'] : false;

        $template = ! empty( $data['template'] ) ? $data['template'] : null;

        $text_link_color = ! empty( $template ) ? $template->get_text_link_color() : YAYMAIL_COLOR_WC_DEFAULT;

        $path_to_shortcodes_template = 'src/Integrations/TrackingMoreOrderTrackingForWc/Templates/Shortcodes/tracking-information';

        $args = [
            'text_link_color' => $text_link_color,
            'element'         => $element,
            'is_placeholder'  => $is_placeholder,
        ];

        $html = yaymail_get_content( $path_to_shortcodes_template . '/main.php', $args );
        return $html;
    }

}
