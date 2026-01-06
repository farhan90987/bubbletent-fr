<?php

namespace YayMail\Integrations\AdvancedLocalPickupByZorem\Elements;

use YayMail\Abstracts\BaseElement;
use YayMail\Utils\SingletonTrait;

/**
 * Advanced Local Pickup Instruction Element
 * Plugin: Advanced Local Pickup for WooCommerce
 * Link: https://wordpress.org/plugins/advanced-local-pickup-for-woocommerce/
 */
class AdvancedLocalPickupInstructionElement extends BaseElement {
    use SingletonTrait;

    protected static $type = 'advanced_local_pickup_instruction';

    public static function get_data( $attributes = [], $folder_parent = null ) {
        self::$icon = YAYMAIL_EXTRA_ELEMENT_ICON;

        return [
            'id'          => uniqid(),
            'type'        => self::$type,
            'name'        => __( 'Advanced Local Pickup Instruction', 'yaymail' ),
            'icon'        => self::$icon,
            'group'       => 'woocommerce',
            'available'   => true,
            'integration' => '3rd',
            'position'    => 190,
            'data'        => [
                'padding'              => [
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
                'background_color'     => [
                    'value_path'    => 'background_color',
                    'component'     => 'Color',
                    'title'         => __( 'Background color', 'yaymail' ),
                    'default_value' => isset( $attributes['background_color'] ) ? $attributes['background_color'] : '#fff',
                    'type'          => 'style',
                ],
                'title_color'          => [
                    'value_path'    => 'title_color',
                    'component'     => 'Color',
                    'title'         => __( 'Title color', 'yaymail' ),
                    'default_value' => isset( $attributes['title_color'] ) ? $attributes['title_color'] : YAYMAIL_COLOR_WC_DEFAULT,
                    'type'          => 'style',
                ],
                'text_color'           => [
                    'value_path'    => 'text_color',
                    'component'     => 'Color',
                    'title'         => __( 'Text color', 'yaymail' ),
                    'default_value' => isset( $attributes['text_color'] ) ? $attributes['text_color'] : YAYMAIL_COLOR_TEXT_DEFAULT,
                    'type'          => 'style',
                ],
                'border_color'         => [
                    'value_path'    => 'border_color',
                    'component'     => 'Color',
                    'title'         => __( 'Border color', 'yaymail' ),
                    'default_value' => isset( $attributes['border_color'] ) ? $attributes['border_color'] : YAYMAIL_COLOR_BORDER_DEFAULT,
                    'type'          => 'style',
                ],
                'font_family'          => [
                    'value_path'    => 'font_family',
                    'component'     => 'FontFamilySelector',
                    'title'         => __( 'Font family', 'yaymail' ),
                    'default_value' => isset( $attributes['font_family'] ) ? $attributes['font_family'] : YAYMAIL_DEFAULT_FAMILY,
                    'type'          => 'style',
                ],
                'title'                => [
                    'value_path'    => 'title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Pickup information title', 'yaymail' ),
                    'default_value' => isset( $attributes['title'] ) ? $attributes['title'] : __( 'Pickup information', 'yaymail' ),
                    'type'          => 'content',
                ],
                'pickup_address_title' => [
                    'value_path'    => 'pickup_address_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Pickup address title', 'yaymail' ),
                    'default_value' => isset( $attributes['pickup_address_title'] ) ? $attributes['pickup_address_title'] : __( 'Pickup Address', 'yaymail' ),
                    'type'          => 'content',
                ],
                'pickup_hours_title'   => [
                    'value_path'    => 'pickup_hours_title',
                    'component'     => 'TextInput',
                    'title'         => __( 'Pickup hours title', 'yaymail' ),
                    'default_value' => isset( $attributes['pickup_hours_title'] ) ? $attributes['pickup_hours_title'] : __( 'Pickup Hours', 'yaymail' ),
                    'type'          => 'content',
                ],
                'rich_text'            => [
                    'value_path'    => 'rich_text',
                    'component'     => '',
                    'title'         => __( 'Content', 'yaymail' ),
                    'default_value' => '[yaymail_advanced_local_pickup_instruction]',
                    'type'          => 'content',
                ],
            ],
        ];
    }

    public static function get_layout( $element, $args ) {
        $path = 'src/Integrations/AdvancedLocalPickupByZorem/Templates/Elements/advanced-local-pickup-instruction.php';
        return yaymail_get_content( $path, array_merge( [ 'element' => $element ], $args ) );
    }
}
