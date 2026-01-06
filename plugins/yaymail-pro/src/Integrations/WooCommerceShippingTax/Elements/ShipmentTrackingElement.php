<?php

namespace YayMail\Integrations\WooCommerceShippingTax\Elements;

use YayMail\Abstracts\BaseElement;
use YayMail\Utils\SingletonTrait;

/**
 * Shipment Tracking Elements
 * Plugin: WooCommerce Shipping & Tax
 * Link: https://wordpress.org/plugins/woocommerce-services/
 */
class ShipmentTrackingElement extends BaseElement {

    use SingletonTrait;

    protected static $type = 'wc_shipping_tax_shipment_tracking';

    public static function get_data( $attributes = [] ) {
        self::$icon = YAYMAIL_EXTRA_ELEMENT_ICON;

        return [
            'id'          => uniqid(),
            'type'        => self::$type,
            'name'        => __( 'WC Shipping Tax Shipment Tracking', 'yaymail' ),
            'icon'        => self::$icon,
            'group'       => 'woocommerce',
            'available'   => true,
            'integration' => '3rd',
            'position'    => 190,
            'data'        => [
                'padding'               => [
                    'value_path'    => 'padding',
                    'component'     => 'Spacing',
                    'title'         => __( 'Padding', 'yaymail' ),
                    'default_value' => isset( $attributes['padding'] ) ? $attributes['padding'] : [
                        'top'    => '15',
                        'right'  => '50',
                        'bottom' => '15',
                        'left'   => '50',
                    ],
                    'type'          => 'style',
                ],
                'background_color'      => [
                    'value_path'    => 'background_color',
                    'component'     => 'Color',
                    'title'         => __( 'Background color', 'yaymail' ),
                    'default_value' => isset( $attributes['background_color'] ) ? $attributes['background_color'] : '#fff',
                    'type'          => 'style',
                ],
                'title_color'           => [
                    'value_path'    => 'title_color',
                    'component'     => 'Color',
                    'title'         => __( 'Title color', 'yaymail' ),
                    'default_value' => isset( $attributes['title_color'] ) ? $attributes['title_color'] : YAYMAIL_COLOR_WC_DEFAULT,
                    'type'          => 'style',
                ],
                'text_color'            => [
                    'value_path'    => 'text_color',
                    'component'     => 'Color',
                    'title'         => __( 'Text color', 'yaymail' ),
                    'default_value' => isset( $attributes['text_color'] ) ? $attributes['text_color'] : YAYMAIL_COLOR_TEXT_DEFAULT,
                    'type'          => 'style',
                ],
                'border_color'          => [
                    'value_path'    => 'border_color',
                    'component'     => 'Color',
                    'title'         => __( 'Border color', 'yaymail' ),
                    'default_value' => isset( $attributes['border_color'] ) ? $attributes['border_color'] : YAYMAIL_COLOR_BORDER_DEFAULT,
                    'type'          => 'style',
                ],
                'font_family'           => [
                    'value_path'    => 'font_family',
                    'component'     => 'FontFamilySelector',
                    'title'         => __( 'Font family', 'yaymail' ),
                    'default_value' => isset( $attributes['font_family'] ) ? $attributes['font_family'] : YAYMAIL_DEFAULT_FAMILY,
                    'type'          => 'style',
                ],
                'title'                 => [
                    'value_path'    => 'title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Tracking title', 'yaymail' ),
                    'default_value' => isset( $attributes['title'] ) ? $attributes['title'] : __( 'Tracking', 'yaymail' ),
                    'type'          => 'content',
                ],
                'provider_title'        => [
                    'value_path'    => 'provider_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Courier title', 'yaymail' ),
                    'default_value' => isset( $attributes['provider_title'] ) ? $attributes['provider_title'] : __( 'Provider', 'yaymail' ),
                    'type'          => 'content',
                ],
                'tracking_number_title' => [
                    'value_path'    => 'tracking_number_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Tracking number title', 'yaymail' ),
                    'default_value' => isset( $attributes['tracking_number_title'] ) ? $attributes['tracking_number_title'] : __( 'Tracking number', 'yaymail' ),
                    'type'          => 'content',
                ],
                'rich_text'             => [
                    'value_path'    => 'rich_text',
                    'component'     => '',
                    'title'         => __( 'Content', 'yaymail' ),
                    'default_value' => '[yaymail_wc_shipping_tax_shipment_tracking]',
                    'type'          => 'content',
                ],
            ],
        ];
    }

    public static function get_layout( $element, $args ) {
        $path = 'src/Integrations/WooCommerceShippingTax/Templates/Elements/shipment-tracking.php';
        return yaymail_get_content( $path, array_merge( [ 'element' => $element ], $args ) );
    }
}
