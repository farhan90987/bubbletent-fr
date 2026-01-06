<?php
namespace YayMail\Elements;

use YayMail\Abstracts\BaseElement;
use YayMail\Utils\SingletonTrait;

/**
 * Featured Products Elements
 */
class FeaturedProducts extends BaseElement {

    use SingletonTrait;

    protected static $type = 'featured_products';

    public $available_email_ids = [ YAYMAIL_ALL_EMAILS ];

    public static function get_data( $attributes = [] ) {
        self::$icon = '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 20 20">
  <g>
    <path d="M7.7,2.7v9.75H2.5V2.7h5.2M8.7,1.2H1.5c-.28,0-.5.22-.5.5v11.75c0,.28.22.5.5.5h7.2c.28,0,.5-.22.5-.5V1.7c0-.28-.22-.5-.5-.5h0Z"/>
    <g>
      <path d="M8,16.49H2.21c-.41,0-.75-.34-.75-.75s.34-.75.75-.75h5.79c.41,0,.75.34.75.75s-.34.75-.75.75Z"/>
      <path d="M7.19,18.95H3.01c-.41,0-.75-.34-.75-.75s.34-.75.75-.75h4.19c.41,0,.75.34.75.75s-.34.75-.75.75Z"/>
    </g>
  </g>
  <g>
    <path d="M17.5,2.7v9.75h-5.2V2.7h5.2M18.5,1.2h-7.2c-.28,0-.5.22-.5.5v11.75c0,.28.22.5.5.5h7.2c.28,0,.5-.22.5-.5V1.7c0-.28-.22-.5-.5-.5h0Z"/>
    <path d="M17.79,16.49h-5.79c-.41,0-.75-.34-.75-.75s.34-.75.75-.75h5.79c.41,0,.75.34.75.75s-.34.75-.75.75Z"/>
    <path d="M16.99,18.95h-4.19c-.41,0-.75-.34-.75-.75s.34-.75.75-.75h4.19c.41,0,.75.34.75.75s-.34.75-.75.75Z"/>
  </g>
</svg>';

        $buy_button_conditions = [
            [
                'comparison' => 'contain',
                'value'      => [ 'buy_button' ],
                'attribute'  => 'showing_items',
            ],
        ];

        return [
            'id'        => uniqid(),
            'type'      => self::$type,
            'name'      => __( 'Featured Products', 'yaymail' ),
            'icon'      => self::$icon,
            'group'     => 'block',
            'available' => true,
            'position'  => 220,
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
                'text_color'                  => [
                    'value_path'    => 'text_color',
                    'component'     => 'Color',
                    'title'         => __( 'Text color', 'yaymail' ),
                    'default_value' => isset( $attributes['text_color'] ) ? $attributes['text_color'] : YAYMAIL_COLOR_TEXT_DEFAULT,
                    'type'          => 'style',
                ],
                'font_family'                 => [
                    'value_path'    => 'font_family',
                    'component'     => 'FontFamilySelector',
                    'title'         => __( 'Font family', 'yaymail' ),
                    'default_value' => isset( $attributes['font_family'] ) ? $attributes['font_family'] : YAYMAIL_DEFAULT_FAMILY,
                    'type'          => 'style',
                ],
                'content_breaker'             => [
                    'component' => 'LineBreaker',
                ],
                'content_group_definition'    => [
                    'component'   => 'GroupDefinition',
                    'title'       => __( 'Content', 'yaymail' ),
                    'description' => __( 'Handle block items settings', 'yaymail' ),
                ],
                'showing_items'               => [
                    'value_path'    => 'showing_items',
                    'component'     => 'CheckboxGroup',
                    'title'         => __( 'Showing items', 'yaymail' ),
                    'default_value' => isset( $attributes['showing_items'] ) ? $attributes['showing_items'] : [ 'top_content', 'product_image', 'product_name', 'product_price', 'product_original_price', 'buy_button' ],
                    'type'          => 'content',
                    'options'       => [
                        [
                            'label' => __( 'Top content', 'yaymail' ),
                            'value' => 'top_content',
                        ],
                        [
                            'label' => __( 'Product image', 'yaymail' ),
                            'value' => 'product_image',
                        ],
                        [
                            'label' => __( 'Product name', 'yaymail' ),
                            'value' => 'product_name',
                        ],
                        [
                            'label' => __( 'Product price', 'yaymail' ),
                            'value' => 'product_price',
                        ],
                        [
                            'label' => __( 'Product original price', 'yaymail' ),
                            'value' => 'product_original_price',
                        ],
                        [
                            'label' => __( 'Buy button', 'yaymail' ),
                            'value' => 'buy_button',
                        ],
                    ],
                ],
                'top_content'                 => [
                    'value_path'    => 'top_content',
                    'component'     => 'RichTextEditor',
                    'title'         => __( 'Top content', 'yaymail' ),
                    'default_value' => isset( $attributes['top_content'] ) ? $attributes['top_content'] : '<p style="text-align: center;"><span style="font-size: 18px;"><strong>FEATURED PRODUCTS</strong></span></p>
                    <p style="font-size: 14px; text-align: center;">&nbsp;</p>
                    <p style="text-align: center;">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>',
                    'type'          => 'content',
                ],
                'sale_price_color'            => [
                    'value_path'    => 'sale_price_color',
                    'component'     => 'Color',
                    'title'         => __( 'Product price color', 'yaymail' ),
                    'default_value' => isset( $attributes['sale_price_color'] ) ? $attributes['sale_price_color'] : '#ec4770',
                    'type'          => 'style',
                    'conditions'    => [
                        [
                            'comparison' => 'contain',
                            'value'      => [ 'product_price' ],
                            'attribute'  => 'showing_items',
                        ],
                    ],
                ],
                'regular_price_color'         => [
                    'value_path'    => 'regular_price_color',
                    'component'     => 'Color',
                    'title'         => __( 'Product original price color', 'yaymail' ),
                    'default_value' => isset( $attributes['regular_price_color'] ) ? $attributes['regular_price_color'] : '#808080',
                    'type'          => 'style',
                    'conditions'    => [
                        [
                            'comparison' => 'contain',
                            'value'      => [ 'product_original_price' ],
                            'attribute'  => 'showing_items',
                        ],
                    ],
                ],
                'button_breaker'              => [
                    'component'  => 'LineBreaker',
                    'conditions' => $buy_button_conditions,
                ],
                'button_group_definition'     => [
                    'component'   => 'GroupDefinition',
                    'title'       => __( 'Button', 'yaymail' ),
                    'description' => __( 'Handle buy button settings', 'yaymail' ),
                    'conditions'  => $buy_button_conditions,
                ],
                'buy_button_label'            => [
                    'value_path'    => 'buy_button_label',
                    'component'     => 'TextInput',
                    'title'         => __( 'Buy button text', 'yaymail' ),
                    'default_value' => isset( $attributes['buy_button_label'] ) ? $attributes['buy_button_label'] : __( 'BUY NOW', 'yaymail' ),
                    'type'          => 'content',
                    'conditions'    => $buy_button_conditions,
                ],
                'buy_button_background_color' => [
                    'value_path'    => 'buy_button_background_color',
                    'component'     => 'Color',
                    'title'         => __( 'Buy button background color', 'yaymail' ),
                    'default_value' => isset( $attributes['buy_button_background_color'] ) ? $attributes['buy_button_background_color'] : '#ec4770',
                    'type'          => 'style',
                    'conditions'    => $buy_button_conditions,
                ],
                'buy_button_text_color'       => [
                    'value_path'    => 'buy_button_text_color',
                    'component'     => 'Color',
                    'title'         => __( 'Buy button text color', 'yaymail' ),
                    'default_value' => isset( $attributes['buy_button_text_color'] ) ? $attributes['buy_button_text_color'] : '#ffffff',
                    'type'          => 'style',
                    'conditions'    => $buy_button_conditions,
                ],
                'products_breaker'            => [
                    'component' => 'LineBreaker',
                ],
                'products_group_definition'   => [
                    'component'   => 'GroupDefinition',
                    'title'       => __( 'Products', 'yaymail' ),
                    'description' => __( 'Handle products settings', 'yaymail' ),
                ],
                'products_per_row'            => [
                    'value_path'    => 'products_per_row',
                    'component'     => 'NumberInput',
                    'title'         => __( 'Products per row', 'yaymail' ),
                    'default_value' => isset( $attributes['products_per_row'] ) ? $attributes['products_per_row'] : '3',
                    'min'           => 1,
                    'max'           => 3,
                    'type'          => 'content',
                ],
                'product_type'                => [
                    'value_path'    => 'product_type',
                    'component'     => 'Selector',
                    'title'         => __( 'Product type', 'yaymail' ),
                    'default_value' => isset( $attributes['product_type'] ) ? $attributes['product_type'] : 'newest',
                    'type'          => 'content',
                    'options'       => [
                        [
                            'label' => __( 'Newest', 'yaymail' ),
                            'value' => 'newest',
                        ],
                        [
                            'label' => __( 'On sale', 'yaymail' ),
                            'value' => 'on_sale',
                        ],
                        [
                            'label' => __( 'Featured', 'yaymail' ),
                            'value' => 'featured',
                        ],
                        [
                            'label' => __( 'Category selections', 'yaymail' ),
                            'value' => 'category_selections',
                        ],
                        [
                            'label' => __( 'Tag selections', 'yaymail' ),
                            'value' => 'tag_selections',
                        ],
                        [
                            'label' => __( 'Product selections', 'yaymail' ),
                            'value' => 'product_selections',
                        ],
                    ],
                ],
                'categories'                  => [
                    'value_path'    => 'categories',
                    'component'     => 'EntitiesSelector',
                    'title'         => __( 'Product categories', 'yaymail' ),
                    'default_value' => isset( $attributes['categories'] ) ? $attributes['categories'] : [],
                    'type'          => 'content',
                    'entity_type'   => 'categories',
                    'conditions'    => [
                        [
                            'value'     => 'category_selections',
                            'attribute' => 'product_type',
                        ],
                    ],
                ],
                'tags'                        => [
                    'value_path'    => 'tags',
                    'component'     => 'EntitiesSelector',
                    'title'         => __( 'Product tags', 'yaymail' ),
                    'default_value' => isset( $attributes['tags'] ) ? $attributes['tags'] : [],
                    'type'          => 'content',
                    'entity_type'   => 'tags',
                    'conditions'    => [
                        [
                            'value'     => 'tag_selections',
                            'attribute' => 'product_type',
                        ],
                    ],
                ],
                'products'                    => [
                    'value_path'    => 'products',
                    'component'     => 'EntitiesSelector',
                    'title'         => __( 'Products', 'yaymail' ),
                    'default_value' => isset( $attributes['products'] ) ? $attributes['products'] : [],
                    'type'          => 'content',
                    'entity_type'   => 'products',
                    'conditions'    => [
                        [
                            'value'     => 'product_selections',
                            'attribute' => 'product_type',
                        ],
                    ],
                ],
                'sorted_by'                   => [
                    'value_path'    => 'sorted_by',
                    'component'     => 'Selector',
                    'title'         => __( 'Sorted by', 'yaymail' ),
                    'default_value' => isset( $attributes['sorted_by'] ) ? $attributes['sorted_by'] : 'none',
                    'options'       => [
                        [
                            'label' => __( 'None', 'yaymail' ),
                            'value' => 'none',
                        ],
                        [
                            'label' => __( 'Name A-Z', 'yaymail' ),
                            'value' => 'name_a_z',
                        ],
                        [
                            'label' => __( 'Name Z-A', 'yaymail' ),
                            'value' => 'name_z_a',
                        ],
                        [
                            'label' => __( 'Ascending Price', 'yaymail' ),
                            'value' => 'price_ascending',
                        ],
                        [
                            'label' => __( 'Descending Price', 'yaymail' ),
                            'value' => 'price_descending',
                        ],
                        [
                            'label' => __( 'Random', 'yaymail' ),
                            'value' => 'random',
                        ],
                    ],
                    'type'          => 'content',
                ],
                'number_of_products'          => [
                    'value_path'    => 'number_of_products',
                    'component'     => 'NumberInput',
                    'title'         => __( 'Number of featured products', 'yaymail' ),
                    'default_value' => isset( $attributes['number_of_products'] ) ? $attributes['number_of_products'] : '5',
                    'type'          => 'content',
                    'min'           => 1,
                    'max'           => 20,
                    'is_debounce'   => true,
                    'conditions'    => [
                        [
                            'comparison' => '!=',
                            'value'      => 'product_selections',
                            'attribute'  => 'product_type',
                        ],
                    ],
                ],
            ],
        ];
    }
}
