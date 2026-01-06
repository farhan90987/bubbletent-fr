<?php

namespace YayMail\Integrations\YITHWooCommerceOrderShipmentTracking\Elements;

use YayMail\Abstracts\BaseElement;
use YayMail\Utils\SingletonTrait;

/**
 * YITH Tracking Information Element
 * Plugin: YITH WooCommerce Order & Shipment Tracking Premium
 * Link: https://yithemes.com/themes/plugins/yith-woocommerce-order-tracking/
 */
class YITHTrackingInformationElement extends BaseElement {

    use SingletonTrait;

    protected static $type = 'yith_tracking_information';

    public static function get_data( $attributes = [], $folder_parent = null ) {
        self::$icon = YAYMAIL_EXTRA_ELEMENT_ICON;

        $rich_text = '<p>' . __( 'Your order has been picked up by <strong>[yaymail_yith_tracking_carrier_name]</strong> on <strong>[yaymail_yith_tracking_pickup_date]</strong>', 'yaymail' ) . '</p>';

        if ( class_exists( 'YITH_Tracking_Data' ) ) {
            $rich_text .= '<p>' . __( 'Estimated delivery date: <strong>[yaymail_yith_tracking_estimated_delivery]</strong>', 'yaymail' ) . '</p>';
        }

        $rich_text .= '<p>' . __( 'Your tracking code is: <strong>[yaymail_yith_tracking_code]</strong>', 'yaymail' ) . '</p>';

        return [
            'id'          => uniqid(),
            'type'        => self::$type,
            'name'        => __( 'Tracking Information (By YITH)', 'yaymail' ),
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
                    'default_value' => $rich_text,
                    'type'          => 'content',
                ],
            ],
        ];
    }

    public static function get_layout( $element, $args ) {
        $path = 'src/Integrations/YITHWooCommerceOrderShipmentTracking/Templates/Elements/yith-tracking-information.php';
        return yaymail_get_content( $path, array_merge( [ 'element' => $element ], $args ) );
    }
}
