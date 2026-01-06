<?php

namespace YayMail\Integrations\AdvancedShipmentTrackingByZorem\Elements;

use YayMail\Abstracts\BaseElement;
use YayMail\Utils\SingletonTrait;
use YayMail\Integrations\AdvancedShipmentTrackingByZorem\Utils\Helpers;

/**
 * Tracking Information Elements
 * Plugin: Advanced Shipment Tracking for WooCommerce
 * Link: https://wordpress.org/plugins/woo-advanced-shipment-tracking/
 */
class TrackingInformationElement extends BaseElement {

    use SingletonTrait;

    protected static $type = 'tracking_information_by_zorem';

    protected function __construct() {
        $this->available_email_ids = [
            'new_order',
            'cancelled_order',
            'failed_order',
            'customer_failed_order',
            'customer_on_hold_order',
            'customer_processing_order',
            'customer_completed_order',
            'customer_refunded_order',
            'customer_invoice',
            'customer_note',
            'customer_partial_shipped_order',
            'customer_shipped_order',
        ];
    }

    public static function get_data( $attributes = [], $folder_parent = null ) {
        self::$icon = YAYMAIL_EXTRA_ELEMENT_ICON;

        $tracking_info_settings = Helpers::get_tracking_info_settings();

        $tracking_header_conditions = [
            [
                'comparison' => '!=',
                'value'      => true,
                'attribute'  => 'header.hidden',
            ],
        ];

        return [
            'id'          => uniqid(),
            'type'        => self::$type,
            'name'        => __( 'Tracking Information (By Zorem)', 'yaymail' ),
            'icon'        => self::$icon,
            'group'       => 'Advanced Shipment Tracking for WooCommerce',
            'available'   => true,
            'integration' => '3rd',
            'position'    => 190,
            'data'        => [
                'padding'                          => [
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
                'background_color'                 => [
                    'value_path'    => 'background_color',
                    'component'     => 'Color',
                    'title'         => __( 'Background color', 'yaymail' ),
                    'default_value' => isset( $attributes['background_color'] ) ? $attributes['background_color'] : '#fff',
                    'type'          => 'style',
                ],
                'title_color'                      => [
                    'value_path'    => 'title_color',
                    'component'     => 'Color',
                    'title'         => __( 'Title color', 'yaymail' ),
                    'default_value' => isset( $attributes['title_color'] ) ? $attributes['title_color'] : YAYMAIL_COLOR_WC_DEFAULT,
                    'type'          => 'style',
                ],
                'text_color'                       => [
                    'value_path'    => 'text_color',
                    'component'     => 'Color',
                    'title'         => __( 'Text color', 'yaymail' ),
                    'default_value' => isset( $attributes['text_color'] ) ? $attributes['text_color'] : YAYMAIL_COLOR_TEXT_DEFAULT,
                    'type'          => 'style',
                ],
                'font_family'                      => [
                    'value_path'    => 'font_family',
                    'component'     => 'FontFamilySelector',
                    'title'         => __( 'Font family', 'yaymail' ),
                    'default_value' => isset( $attributes['font_family'] ) ? $attributes['font_family'] : YAYMAIL_DEFAULT_FAMILY,
                    'type'          => 'style',
                ],
                'widget_header_breaker'            => [
                    'component' => 'LineBreaker',

                ],
                'widget_header_group_definition'   => [
                    'component'   => 'GroupDefinition',
                    'title'       => __( 'Widget header', 'yaymail' ),
                    'description' => __( 'Handle widget header settings', 'yaymail' ),
                ],
                'header_hidden'                    => [
                    'value_path'    => 'header.hidden',
                    'component'     => 'Switcher',
                    'title'         => __( 'Hide tracking header', 'yaymail' ),
                    'default_value' => $tracking_info_settings['header']['hidden'],
                    'type'          => 'content',
                ],
                'header_text'                      => [
                    'value_path'    => 'header.text',
                    'component'     => 'TextInput',
                    'title'         => __( 'Header text change', 'yaymail' ),
                    'default_value' => $tracking_info_settings['header']['text'],
                    'type'          => 'content',
                    'conditions'    => $tracking_header_conditions,
                ],
                'header_additional_text'           => [
                    'value_path'    => 'header.additional_text',
                    'component'     => 'TextInput',
                    'title'         => __( 'Additional header text', 'yaymail' ),
                    'default_value' => $tracking_info_settings['header']['additional_text'],
                    'type'          => 'content',
                ],
                'widget_style_breaker'             => [
                    'component' => 'LineBreaker',

                ],
                'widget_style_group_definition'    => [
                    'component'   => 'GroupDefinition',
                    'title'       => __( 'Widget style', 'yaymail' ),
                    'description' => __( 'Handle widget style settings', 'yaymail' ),
                ],
                'style_display_shipped_header'     => [
                    'value_path'    => 'style.display_shipped_header',
                    'component'     => 'Switcher',
                    'title'         => __( 'Display shipped header', 'yaymail' ),
                    'default_value' => $tracking_info_settings['style']['display_shipped_header'],
                    'type'          => 'content',
                ],
                'style_tracker_type'               => [
                    'value_path'    => 'style.tracker_type',
                    'component'     => 'Selector',
                    'title'         => __( 'Tracker type', 'yaymail' ),
                    'default_value' => $tracking_info_settings['style']['tracker_type'],
                    'type'          => 'style',
                    'options'       => [
                        [
                            'label' => __( 'Progress bar', 'yaymail' ),
                            'value' => 'progress_bar',
                        ],
                        [
                            'label' => __( 'Icons', 'yaymail' ),
                            'value' => 'icons',
                        ],
                        [
                            'label' => __( 'Single icon', 'yaymail' ),
                            'value' => 'single_icons',
                        ],
                    ],
                ],
                'style_table_background_color'     => [
                    'value_path'    => 'style.table_background_color',
                    'component'     => 'Color',
                    'title'         => __( 'Background color', 'yaymail' ),
                    'default_value' => $tracking_info_settings['style']['table_background_color'],
                    'type'          => 'style',
                ],
                'style_table_border_color'         => [
                    'value_path'    => 'style.table_border_color',
                    'component'     => 'Color',
                    'title'         => __( 'Border color', 'yaymail' ),
                    'default_value' => $tracking_info_settings['style']['table_border_color'],
                    'type'          => 'style',
                ],
                'style_table_border_radius'        => [
                    'value_path'    => 'style.table_border_radius',
                    'component'     => 'BorderRadius',
                    'title'         => __( 'Border radius', 'yaymail' ),
                    'default_value' => [
                        'top_left'     => $tracking_info_settings['style']['table_border_radius'],
                        'top_right'    => $tracking_info_settings['style']['table_border_radius'],
                        'bottom_left'  => $tracking_info_settings['style']['table_border_radius'],
                        'bottom_right' => $tracking_info_settings['style']['table_border_radius'],
                    ],
                    'type'          => 'style',
                ],
                'style_hide_provider_image'        => [
                    'value_path'    => 'style.hide_provider_image',
                    'component'     => 'Switcher',
                    'title'         => __( 'Hide provider image', 'yaymail' ),
                    'default_value' => $tracking_info_settings['style']['hide_provider_image'],
                    'type'          => 'style',
                ],
                'tracking_button_breaker'          => [
                    'component' => 'LineBreaker',

                ],
                'tracking_button_group_definition' => [
                    'component'   => 'GroupDefinition',
                    'title'       => __( 'Tracking button', 'yaymail' ),
                    'description' => __( 'Handle tracking button settings', 'yaymail' ),
                ],
                'button_text'                      => [
                    'value_path'    => 'button.text',
                    'component'     => 'TextInput',
                    'title'         => __( 'Button text', 'yaymail' ),
                    'default_value' => $tracking_info_settings['button']['text'],
                    'type'          => 'content',
                ],
                'button_size'                      => [
                    'value_path'    => 'button.size',
                    'component'     => 'Selector',
                    'title'         => __( 'Button size', 'yaymail' ),
                    'default_value' => $tracking_info_settings['button']['size'],
                    'type'          => 'style',
                    'options'       => [
                        [
                            'label' => __( 'Normal', 'yaymail' ),
                            'value' => 'normal',
                        ],
                        [
                            'label' => __( 'Large', 'yaymail' ),
                            'value' => 'large',
                        ],
                    ],
                ],
                'button_background_color'          => [
                    'value_path'    => 'button.background_color',
                    'component'     => 'Color',
                    'title'         => __( 'Button background color', 'yaymail' ),
                    'default_value' => $tracking_info_settings['button']['background_color'],
                    'type'          => 'style',
                ],
                'button_font_color'                => [
                    'value_path'    => 'button.font_color',
                    'component'     => 'Color',
                    'title'         => __( 'Button font color', 'yaymail' ),
                    'default_value' => $tracking_info_settings['button']['font_color'],
                    'type'          => 'style',
                ],
                'button_radius'                    => [
                    'value_path'    => 'button.radius',
                    'component'     => 'BorderRadius',
                    'title'         => __( 'Button radius', 'yaymail' ),
                    'default_value' => [
                        'top_left'     => $tracking_info_settings['button']['radius'],
                        'top_right'    => $tracking_info_settings['button']['radius'],
                        'bottom_left'  => $tracking_info_settings['button']['radius'],
                        'bottom_right' => $tracking_info_settings['button']['radius'],
                    ],
                    'type'          => 'style',
                ],
                'rich_text'                        => [
                    'value_path'    => 'rich_text',
                    'component'     => '',
                    'title'         => __( 'Content', 'yaymail' ),
                    'default_value' => '[yaymail_order_tracking_information_by_zorem]',
                    'type'          => 'content',
                ],
            ],
        ];
    }

    public static function get_layout( $element, $args ) {
        $path = 'src/Integrations/AdvancedShipmentTrackingByZorem/Templates/Elements/tracking-information-by-zorem.php';
        return yaymail_get_content( $path, array_merge( [ 'element' => $element ], $args ) );
    }
}
