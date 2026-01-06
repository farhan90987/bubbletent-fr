<?php

namespace YayMail\Integrations\WoocommerceShipmentTrackingProByPluginHive\Elements;

use YayMail\Abstracts\BaseElement;
use YayMail\Utils\SingletonTrait;

/**
 * Tracking Information Elements
 * Plugin: Woocommerce Shipment Tracking Pro (PluginHive)
 * Link: https://www.pluginhive.com/product/woocommerce-shipment-tracking-pro/
 */
class TrackingInformationElement extends BaseElement {

    use SingletonTrait;

    protected static $type = 'tracking_information_by_pluginhive';

    public static function get_data( $attributes = [], $folder_parent = null ) {
        self::$icon = YAYMAIL_EXTRA_ELEMENT_ICON;

        return [
            'id'          => uniqid(),
            'type'        => self::$type,
            'name'        => __( 'Tracking Information (By PluginHive)', 'yaymail' ),
            'icon'        => self::$icon,
            'group'       => 'woocommerce',
            'available'   => true,
            'integration' => '3rd',
            'position'    => 190,
            'data'        => [
                'padding'          => [
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
                'background_color' => [
                    'value_path'    => 'background_color',
                    'component'     => 'Color',
                    'title'         => __( 'Background color', 'yaymail' ),
                    'default_value' => isset( $attributes['background_color'] ) ? $attributes['background_color'] : '#fff',
                    'type'          => 'style',
                ],
                'title_color'      => [
                    'value_path'    => 'title_color',
                    'component'     => 'Color',
                    'title'         => __( 'Title color', 'yaymail' ),
                    'default_value' => isset( $attributes['title_color'] ) ? $attributes['title_color'] : YAYMAIL_COLOR_WC_DEFAULT,
                    'type'          => 'style',
                ],
                'text_color'       => [
                    'value_path'    => 'text_color',
                    'component'     => 'Color',
                    'title'         => __( 'Text color', 'yaymail' ),
                    'default_value' => isset( $attributes['text_color'] ) ? $attributes['text_color'] : YAYMAIL_COLOR_TEXT_DEFAULT,
                    'type'          => 'style',
                ],
                'font_family'      => [
                    'value_path'    => 'font_family',
                    'component'     => 'FontFamilySelector',
                    'title'         => __( 'Font family', 'yaymail' ),
                    'default_value' => isset( $attributes['font_family'] ) ? $attributes['font_family'] : YAYMAIL_DEFAULT_FAMILY,
                    'type'          => 'style',
                ],
                'title'            => [
                    'value_path'    => 'title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Tracking title', 'yaymail' ),
                    'default_value' => isset( $attributes['title'] ) ? $attributes['title'] : __( 'Tracking Information', 'yaymail' ),
                    'type'          => 'content',
                ],
                'rich_text'        => [
                    'value_path'    => 'rich_text',
                    'component'     => 'RichTextEditor',
                    'title'         => __( 'Content', 'yaymail' ),
                    'default_value' => isset( $attributes['rich_text'] ) ? $attributes['rich_text'] : '<p>' . __( 'Your order was shipped on [yaymail_order_tracking_information_by_pluginhive_date] via [yaymail_order_tracking_information_by_pluginhive_service]. To track shipment, please follow the link of shipment ID(s) [yaymail_order_tracking_information_by_pluginhive_tracking_id]', 'yaymail' ) . '</p>',
                    'type'          => 'content',
                ],
            ],
        ];
    }

    public static function get_layout( $element, $args ) {
        $path = 'src/Integrations/WoocommerceShipmentTrackingProByPluginHive/Templates/Elements/tracking-information-by-pluginhive.php';
        return yaymail_get_content( $path, array_merge( [ 'element' => $element ], $args ) );
    }
}
