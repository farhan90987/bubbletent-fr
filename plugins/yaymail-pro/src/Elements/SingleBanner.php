<?php
namespace YayMail\Elements;

use YayMail\Abstracts\BaseElement;
use YayMail\Utils\SingletonTrait;

/**
 * Single Banner Elements
 */
class SingleBanner extends BaseElement {

    use SingletonTrait;

    protected static $type = 'single_banner';

    public $available_email_ids = [ YAYMAIL_ALL_EMAILS ];

    public static function get_data( $attributes = [] ) {
        self::$icon = '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 20 20">
  <path d="M12.51,17.63H2.75c-.91,0-1.66-.79-1.66-1.75V3.1c0-.96.74-1.75,1.66-1.75h14.51c.91,0,1.66.79,1.66,1.75v11.72h-1.5V3.1c0-.15-.09-.25-.16-.25H2.75c-.06,0-.16.1-.16.25v12.79c0,.15.09.25.16.25h9.76v1.5Z"/>
  <path d="M12.1,13.59c-.65,0-1.29-.28-1.73-.82l-2.53-3.06c-.13-.16-.31-.25-.51-.27-.19-.02-.39.04-.54.17l-4.26,3.6c-.31.27-.79.23-1.06-.09-.27-.32-.23-.79.09-1.06l4.26-3.61c.46-.39,1.06-.58,1.64-.52.6.05,1.15.34,1.53.81l2.53,3.06c.26.31.69.37,1.02.13l5-3.73c.33-.25.8-.18,1.05.15.25.33.18.8-.15,1.05l-5,3.73c-.4.3-.87.44-1.33.44Z"/>
  <path d="M13.26,8.57c-1.09,0-1.98-.89-1.98-1.98s.89-1.98,1.98-1.98,1.98.89,1.98,1.98-.89,1.98-1.98,1.98ZM13.26,6.12c-.26,0-.48.21-.48.48s.21.48.48.48.48-.21.48-.48-.21-.48-.48-.48Z"/>
  <g>
    <rect x="14.79" y="14.95" width="1.5" height="3.56" transform="translate(-5.02 6.79) rotate(-21.32)"/>
    <path d="M13.51,16.55l.64-3.37,1.52,1.13s.02.01.03.02l1.21.9-1.97.77s-.01,0-.02,0l-1.41.55Z"/>
  </g>
</svg>';

        $background_conditions = [
            [
                'comparison' => 'contain',
                'value'      => [ 'background_image' ],
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
            'name'      => __( 'Single Banner', 'yaymail' ),
            'icon'      => self::$icon,
            'group'     => 'block',
            'available' => true,
            'position'  => 240,
            'data'      => [
                'padding'                     => [
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
                'background_color'            => [
                    'value_path'    => 'background_color',
                    'component'     => 'Color',
                    'title'         => __( 'Background color', 'yaymail' ),
                    'default_value' => isset( $attributes['background_color'] ) ? $attributes['background_color'] : '#fff',
                    'type'          => 'style',
                ],
                'font_family'                 => [
                    'value_path'    => 'font_family',
                    'component'     => 'FontFamilySelector',
                    'title'         => __( 'Font family', 'yaymail' ),
                    'default_value' => isset( $attributes['font_family'] ) ? $attributes['font_family'] : YAYMAIL_DEFAULT_FAMILY,
                    'type'          => 'style',
                ],
                'rich_text'                   => [
                    'value_path'    => 'rich_text',
                    'component'     => 'RichTextEditor',
                    'title'         => __( 'Content', 'yaymail' ),
                    'default_value' => isset( $attributes['rich_text'] ) ? $attributes['rich_text'] : '<p style="text-align: right;"><strong><span style="color: #ffff00; font-size: 24px;">Your Elegance Our Choice</span></strong></p>
                    <p style="text-align: right;"><span style="color: #ffffff; font-size: 16px;"><strong><span style="color: #ffffff;"><span style="color: #ec4770;">BETTER PRODUCT AT THE RIGHT PRICE</span></span></strong></span></p>
                    <p style="text-align: right;"><span style="font-size: 14px; color: #010101;">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua</span></p>',
                    'type'          => 'content',
                ],
                'content_breaker'             => [
                    'component' => 'LineBreaker',
                ],
                'content_group_definition'    => [
                    'component'   => 'GroupDefinition',
                    'title'       => __( 'Content', 'yaymail' ),
                    'description' => __( 'Handle content settings', 'yaymail' ),
                ],
                'content_width'               => [
                    'value_path'    => 'content_width',
                    'component'     => 'Dimension',
                    'title'         => __( 'Content width', 'yaymail' ),
                    'default_value' => isset( $attributes['content_width'] ) ? $attributes['content_width'] : '60',
                    'type'          => 'style',
                ],
                'content_align'               => [
                    'value_path'    => 'content_align',
                    'component'     => 'Align',
                    'title'         => __( 'Content align', 'yaymail' ),
                    'default_value' => isset( $attributes['content_align'] ) ? $attributes['content_align'] : 'right',
                    'type'          => 'style',
                ],
                'showing_items'               => [
                    'value_path'    => 'showing_items',
                    'component'     => 'CheckboxGroup',
                    'title'         => __( 'Showing items', 'yaymail' ),
                    'default_value' => isset( $attributes['showing_items'] ) ? $attributes['showing_items'] : [ 'button', 'background_image' ],
                    'type'          => 'content',
                    'options'       => [
                        [
                            'label' => __( 'Background image', 'yaymail' ),
                            'value' => 'background_image',
                        ],
                        [
                            'label' => __( 'Button', 'yaymail' ),
                            'value' => 'button',
                        ],
                    ],
                ],
                'background_breaker'          => [
                    'component'  => 'LineBreaker',
                    'conditions' => $background_conditions,
                ],
                'background_group_definition' => [
                    'component'   => 'GroupDefinition',
                    'title'       => __( 'Background', 'yaymail' ),
                    'description' => __( 'Handle background settings', 'yaymail' ),
                    'conditions'  => $background_conditions,
                ],
                'background_image'            => [
                    'value_path'    => 'background_image',
                    'component'     => 'BackgroundImage',
                    'title'         => __( 'Background image', 'yaymail' ),
                    'default_value' => isset( $attributes['background_image'] ) ? $attributes['background_image'] : [
                        'url'        => YAYMAIL_PLUGIN_URL . 'assets/images/shopping-image.jpeg',
                        'position'   => 'center_center',
                        'x_position' => 0,
                        'y_position' => 0,
                        'repeat'     => 'default',
                        'size'       => 'cover',
                    ],
                    'type'          => 'style',
                    'conditions'    => $background_conditions,
                ],
                'button_breaker'              => [
                    'component'  => 'LineBreaker',
                    'conditions' => $button_conditions,
                ],
                'button_group_definition'     => [
                    'component'   => 'GroupDefinition',
                    'title'       => __( 'Button', 'yaymail' ),
                    'description' => __( 'Handle button settings', 'yaymail' ),
                    'conditions'  => $button_conditions,
                ],
                'button_text'                 => [
                    'value_path'    => 'button_text',
                    'component'     => 'TextInput',
                    'title'         => __( 'Button text', 'yaymail' ),
                    'default_value' => isset( $attributes['button_text'] ) ? $attributes['button_text'] : __( 'ORDER NOW', 'yaymail' ),
                    'type'          => 'content',
                    'conditions'    => $button_conditions,
                ],
                'button_url'                  => [
                    'value_path'    => 'button_url',
                    'component'     => 'TextInput',
                    'title'         => __( 'Button URL', 'yaymail' ),
                    'default_value' => isset( $attributes['button_url'] ) ? $attributes['button_url'] : home_url(),
                    'type'          => 'content',
                    'conditions'    => $button_conditions,
                ],
                'button_align'                => [
                    'value_path'    => 'button_align',
                    'component'     => 'Align',
                    'title'         => __( 'Button align', 'yaymail' ),
                    'default_value' => isset( $attributes['button_align'] ) ? $attributes['button_align'] : 'right',
                    'type'          => 'style',
                    'conditions'    => $button_conditions,
                ],
                'button_background_color'     => [
                    'value_path'    => 'button_background_color',
                    'component'     => 'Color',
                    'title'         => __( 'Button background color', 'yaymail' ),
                    'default_value' => isset( $attributes['button_background_color'] ) ? $attributes['button_background_color'] : '#ec4770',
                    'type'          => 'style',
                    'conditions'    => $button_conditions,
                ],
                'button_text_color'           => [
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
