<?php
namespace YayMail\Elements;

use YayMail\Abstracts\BaseElement;
use YayMail\Utils\SingletonTrait;

/**
 * Simple Offer Elements
 */
class SimpleOffer extends BaseElement {

    use SingletonTrait;

    protected static $type = 'simple_offer';

    public $available_email_ids = [ YAYMAIL_ALL_EMAILS ];

    public static function get_data( $attributes = [] ) {
        self::$icon = '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 20 20">
  <path d="M17.56,3.61H7.3c-.27,0-.45.09-.63.27l-.27.27-.27-.27c-.18-.18-.36-.27-.63-.27h-3.06c-.81,0-1.44.63-1.44,1.35v9.99c0,.81.63,1.44,1.44,1.44h3.06c.27,0,.45-.09.63-.27l.27-.27.27.27c.18.18.45.27.63.27h10.26c.81,0,1.44-.63,1.44-1.44V4.96c0-.72-.63-1.35-1.44-1.35ZM17.65,14.86c0,.09-.09.18-.18.18H7.48l-.54-.54v-1.71h-1.17v1.71l-.54.54h-2.7c-.09,0-.18-.09-.18-.18V5.14c0-.09.09-.18.18-.18h2.7l.54.54v1.71h1.08v-1.71l.54-.54h9.99c.09,0,.18.09.18.18v9.72h.09ZM5.77,10.54h1.08v1.08h-1.08v-1.08ZM5.77,8.29h1.08v1.08h-1.08v-1.08ZM10.27,9.55c.9,0,1.71-.72,1.71-1.71,0-.9-.72-1.71-1.71-1.71-.9,0-1.71.72-1.71,1.71.09.99.81,1.71,1.71,1.71ZM10.27,7.3c.36,0,.63.27.63.63s-.27.54-.54.54-.54-.27-.54-.54c-.09-.36.09-.63.45-.63ZM15.49,7.03l-5.58,6.75c-.09.09-.27.18-.45.18-.09,0-.27,0-.36-.09-.27-.18-.27-.54-.09-.81l5.58-6.75c.18-.18.54-.27.72-.09.36.18.36.54.18.81ZM14.23,10.45c-.9,0-1.71.72-1.71,1.71,0,.9.72,1.71,1.71,1.71s1.71-.72,1.71-1.71-.72-1.71-1.71-1.71ZM14.23,12.7c-.27,0-.54-.27-.54-.54s.27-.54.54-.54.54.27.54.54c.09.27-.18.54-.54.54Z"/>
</svg>';

        $border_conditions = [
            [
                'comparison' => 'contain',
                'value'      => [ 'border' ],
                'attribute'  => 'showing_items',
            ],
        ];

        $button_conditions = [
            [
                'comparison' => 'contain',
                'value'      => [ 'button' ],
                'attribute'  => 'showing_items',
            ],
        ];

        return [
            'id'        => uniqid(),
            'type'      => self::$type,
            'name'      => __( 'Simple Offer', 'yaymail' ),
            'icon'      => self::$icon,
            'group'     => 'block',
            'available' => true,
            'position'  => 230,
            'data'      => [
                'padding'                  => [
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
                'background_color'         => [
                    'value_path'    => 'background_color',
                    'component'     => 'Color',
                    'title'         => __( 'Background color', 'yaymail' ),
                    'default_value' => isset( $attributes['background_color'] ) ? $attributes['background_color'] : '#fff',
                    'type'          => 'style',
                ],
                'text_color'               => [
                    'value_path'    => 'text_color',
                    'component'     => 'Color',
                    'title'         => __( 'Text color', 'yaymail' ),
                    'default_value' => isset( $attributes['text_color'] ) ? $attributes['text_color'] : YAYMAIL_COLOR_TEXT_DEFAULT,
                    'type'          => 'style',
                ],
                'font_family'              => [
                    'value_path'    => 'font_family',
                    'component'     => 'FontFamilySelector',
                    'title'         => __( 'Font family', 'yaymail' ),
                    'default_value' => isset( $attributes['font_family'] ) ? $attributes['font_family'] : YAYMAIL_DEFAULT_FAMILY,
                    'type'          => 'style',
                ],
                'content_breaker'          => [
                    'component' => 'LineBreaker',
                ],
                'content_group_definition' => [
                    'component'   => 'GroupDefinition',
                    'title'       => __( 'Content', 'yaymail' ),
                    'description' => __( 'Handle content settings', 'yaymail' ),
                ],
                'rich_text'                => [
                    'value_path'    => 'rich_text',
                    'component'     => 'RichTextEditor',
                    'title'         => __( 'Content', 'yaymail' ),
                    'default_value' => isset( $attributes['rich_text'] ) ? $attributes['rich_text'] : '<p style="text-align: left;"><span style="font-size: 30px; color: #ec4770;"><strong>Extra 30% off</strong></span></p>
                    <p style="text-align: left;"><span style="font-size: 18px;"><strong>ON ORDERS ABOVE $100 ON NEW ARRIVALS</strong></span></p>',
                    'type'          => 'content',
                ],
                'showing_items'            => [
                    'value_path'    => 'showing_items',
                    'component'     => 'CheckboxGroup',
                    'title'         => __( 'Showing items', 'yaymail' ),
                    'default_value' => isset( $attributes['showing_items'] ) ? $attributes['showing_items'] : [ 'button', 'border' ],
                    'type'          => 'content',
                    'options'       => [
                        [
                            'label' => __( 'Border', 'yaymail' ),
                            'value' => 'border',
                        ],
                        [
                            'label' => __( 'Button', 'yaymail' ),
                            'value' => 'button',
                        ],
                    ],
                ],
                'border_breaker'           => [
                    'component'  => 'LineBreaker',
                    'conditions' => $border_conditions,
                ],
                'border_group_definition'  => [
                    'component'   => 'GroupDefinition',
                    'title'       => __( 'Border', 'yaymail' ),
                    'description' => __( 'Handle content border settings', 'yaymail' ),
                    'conditions'  => $border_conditions,
                ],
                'border_width'             => [
                    'value_path'    => 'border_width',
                    'component'     => 'NumberInput',
                    'title'         => __( 'Border width', 'yaymail' ),
                    'default_value' => isset( $attributes['border_width'] ) ? $attributes['border_width'] : '3',
                    'min'           => 0,
                    'max'           => 100,
                    'type'          => 'style',
                    'conditions'    => $border_conditions,
                ],
                'border_style'             => [
                    'value_path'    => 'border_style',
                    'component'     => 'Selector',
                    'title'         => __( 'Border style', 'yaymail' ),
                    'default_value' => isset( $attributes['border_style'] ) ? $attributes['border_style'] : 'solid',
                    'options'       => [
                        [
                            'label' => __( 'Solid', 'yaymail' ),
                            'value' => 'solid',
                        ],
                        [
                            'label' => __( 'Double', 'yaymail' ),
                            'value' => 'double',
                        ],
                        [
                            'label' => __( 'Dotted', 'yaymail' ),
                            'value' => 'dotted',
                        ],
                        [
                            'label' => __( 'Dashed', 'yaymail' ),
                            'value' => 'dashed',
                        ],
                    ],
                    'type'          => 'style',
                    'conditions'    => $border_conditions,
                ],
                'border_color'             => [
                    'value_path'    => 'border_color',
                    'component'     => 'Color',
                    'title'         => __( 'Border color', 'yaymail' ),
                    'default_value' => isset( $attributes['border_color'] ) ? $attributes['border_color'] : '#000000',
                    'type'          => 'style',
                    'conditions'    => $border_conditions,
                ],
                'button_breaker'           => [
                    'component'  => 'LineBreaker',
                    'conditions' => $button_conditions,
                ],
                'button_group_definition'  => [
                    'component'   => 'GroupDefinition',
                    'title'       => __( 'Button', 'yaymail' ),
                    'description' => __( 'Handle button settings', 'yaymail' ),
                    'conditions'  => $button_conditions,
                ],
                'button_text'              => [
                    'value_path'    => 'button_text',
                    'component'     => 'TextInput',
                    'title'         => __( 'Button text', 'yaymail' ),
                    'default_value' => isset( $attributes['button_text'] ) ? $attributes['button_text'] : __( 'ORDER NOW', 'yaymail' ),
                    'type'          => 'content',
                    'conditions'    => $button_conditions,
                ],
                'button_url'               => [
                    'value_path'    => 'button_url',
                    'component'     => 'TextInput',
                    'title'         => __( 'Button URL', 'yaymail' ),
                    'default_value' => isset( $attributes['button_url'] ) ? $attributes['button_url'] : home_url(),
                    'type'          => 'content',
                    'conditions'    => $button_conditions,
                ],
                'button_background_color'  => [
                    'value_path'    => 'button_background_color',
                    'component'     => 'Color',
                    'title'         => __( 'Button background color', 'yaymail' ),
                    'default_value' => isset( $attributes['button_background_color'] ) ? $attributes['button_background_color'] : '#ec4770',
                    'type'          => 'style',
                    'conditions'    => $button_conditions,
                ],
                'button_text_color'        => [
                    'value_path'    => 'button_text_color',
                    'component'     => 'Color',
                    'title'         => __( 'Button text color', 'yaymail' ),
                    'default_value' => isset( $attributes['button_text_color'] ) ? $attributes['button_text_color'] : '#fff',
                    'type'          => 'style',
                    'conditions'    => $button_conditions,
                ],
            ],
        ];
    }
}
