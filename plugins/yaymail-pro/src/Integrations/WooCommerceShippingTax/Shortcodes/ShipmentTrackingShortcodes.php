<?php

namespace YayMail\Integrations\WooCommerceShippingTax\Shortcodes;

use YayMail\Abstracts\BaseShortcode;
use YayMail\Utils\SingletonTrait;

/**
 * ShipmentTrackingShortcodes
 * * @method static ShipmentTrackingShortcodes get_instance()
 */
class ShipmentTrackingShortcodes extends BaseShortcode {
    use SingletonTrait;

    public function get_shortcodes() {
        $shortcodes = [];

        $shortcodes[] = [
            'name'        => 'yaymail_wc_shipping_tax_shipment_tracking',
            'description' => __( 'Shipment Tracking Details', 'yaymail' ),
            'group'       => 'wc_shipping_tax',
            'callback'    => [ $this, 'yaymail_wc_shipping_tax_shipment_tracking' ],
        ];

        return $shortcodes;
    }

    public function yaymail_wc_shipping_tax_shipment_tracking( $args ) {
        $render_data             = isset( $args['render_data'] ) ? $args['render_data'] : [];
        $is_sample               = isset( $render_data['is_sample'] ) ? $render_data['is_sample'] : false;
        $is_customized_preview   = isset( $render_data['is_customized_preview'] ) ? $render_data['is_customized_preview'] : false;
        $template                = ! empty( $args['template'] ) ? $args['template'] : null;
        $args['text_link_color'] = ! empty( $template ) ? $template->get_text_link_color() : YAYMAIL_COLOR_WC_DEFAULT;

        $path_to_shortcodes_template = 'src/Integrations/WooCommerceShippingTax/Templates/Shortcodes/tracking-information';

        if ( $is_sample ) {
            /**
             * Is sample data
             */
            $html = yaymail_get_content( $path_to_shortcodes_template . '/sample.php', $args );
            return $html;
        }

        $order = '';

        if ( isset( $render_data['order'] ) ) {
            $order = $render_data['order'];
        }

        if ( empty( $order ) && $is_customized_preview ) {
            return '';
        }

        $html = yaymail_get_content( $path_to_shortcodes_template . '/main.php', $args );
        return $html;
    }
}
