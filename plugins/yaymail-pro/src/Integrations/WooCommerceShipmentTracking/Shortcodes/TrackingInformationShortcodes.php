<?php

namespace YayMail\Integrations\WooCommerceShipmentTracking\Shortcodes;

use YayMail\Abstracts\BaseShortcode;
use YayMail\Utils\Helpers;
use YayMail\Utils\SingletonTrait;

/**
 * TrackingInformationShortcodes
 */
class TrackingInformationShortcodes extends BaseShortcode {
    use SingletonTrait;

    public function get_shortcodes() {
        $shortcodes   = [];
        $shortcodes[] = [
            'name'        => 'yaymail_order_tracking_information_by_wc_shipment_tracking',
            'description' => __( 'Tracking Information', 'yaymail' ),
            'group'       => 'order_details',
            'callback'    => [ $this, 'get_tracking_information' ],
        ];

        return $shortcodes;
    }

    public function get_tracking_information( $data ) {

        $render_data = isset( $data['render_data'] ) ? $data['render_data'] : [];

        $element = isset( $data['element'] ) ? $data['element'] : [];

        $is_placeholder = isset( $data['is_placeholder'] ) ? $data['is_placeholder'] : false;

        $template = ! empty( $data['template'] ) ? $data['template'] : null;

        $text_link_color = ! empty( $template ) ? $template->get_text_link_color() : YAYMAIL_COLOR_WC_DEFAULT;

        $args = [
            'text_link_color' => $text_link_color,
            'element'         => $element,
            'is_placeholder'  => $is_placeholder,
        ];

        if ( ! empty( $render_data['is_sample'] ) ) {
            $html = yaymail_get_content( $this->get_path_to_shortcodes_template() . 'tracking-information-by-wc-shipment-tracking/sample.php', $args );
            return $html;
        }

        $order = Helpers::get_order_from_shortcode_data( $render_data );

        if ( empty( $order ) ) {
            return __( 'No order found', 'yaymail' );
        }

        $st = \WC_Shipment_Tracking_Actions::get_instance();

        $tracking_items = $st->get_tracking_items( $order->get_id(), true );

        $email_id = isset( $render_data['email'] ) ? $render_data['email']->id : ( isset( $template->get_data()['name'] ) ? $template->get_data()['name'] : '' );

        $args['order']          = $order;
        $args['email_id']       = $email_id;
        $args['tracking_items'] = $tracking_items;

        $html = yaymail_get_content( $this->get_path_to_shortcodes_template() . 'tracking-information-by-wc-shipment-tracking/main.php', $args );
        return $html;
    }

    public static function get_path_to_shortcodes_template() {
        return 'src/Integrations/WooCommerceShipmentTracking/Templates/Shortcodes/';
    }
}
