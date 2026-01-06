<?php

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Blocks\Utils\StyleAttributesUtils;

abstract class German_Market_Product_Block {

    public $strip_tags_before_render = true;
    public $use_markup = true;
    private $slug = '';
    public $has_editor_style = false;

    /**
     * Construct
     *
     * @param String] $key
     */
    public function __construct( $key ) {
        $this->slug = $key;
    }

    /**
     * Get slug
     *
     * @return String
     */
    public function get_slug() {
        return $this->slug;
    }

    /**
     * Get 'use_context' when calling 'register_block_type'
     *
     * @return Array
     */
    public function get_uses_context() {
        return array( 'query', 'queryId', 'postId' );
    }

    /**
     * Call your render function
     *
     * @param Array $attributes
     * @param Array $content
     * @param Object $block
     * @return String
     */
    public function render( $attributes, $content, $block ) {

        $post_id = isset( $block->context[ 'postId' ] ) ? $block->context[ 'postId' ] : '';

        $is_preview = false;
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            $is_preview = true;
        }

        $rendered_content = '';

        if ( $is_preview && empty( $post_id ) ) {
            $rendered_content = $this->render_preview_without_product( $attributes, $content, $block );
        } else if ( ! empty( $post_id ) ) {
            $product = wc_get_product( $post_id );
            $rendered_content = $this->render_product( $product, $attributes, $content, $block, $is_preview );
        }

        return apply_filters( 
                'german_market_product_element_block_after_render',
                $this->get_markup_for_rendered_content( $attributes, $rendered_content, $is_preview ),
                $this->get_slug(),
                $rendered_content,
                $attributes,
                $this
        );
    }

    /**
     * Get bloc markup for rendered contend
     *
     * @param Array $attributes
     * @param String $rendered_content
     * @return String
     */
    public function get_markup_for_rendered_content( $attributes, $rendered_content, $is_preview = false ) {
        
        $markup = $rendered_content;

        if ( $this->use_markup ) {
            
            $styles_attributes = $attributes;
            if ( isset( $styles_attributes[ 'align' ] ) ) {
                unset( $styles_attributes[ 'align' ] );
            }

            $styles_and_classes = StyleAttributesUtils::get_classes_and_styles_by_attributes( $styles_attributes );

            $align_style = '';

            if ( isset( $attributes[ 'align' ] ) ) {
                if ( ! empty( $attributes[ 'align' ] ) ) {
                    $align_style = ' text-align: ' . esc_attr( $attributes[ 'align' ] ) . ';';
                }
            }

            if ( $is_preview ) {
            	$styles_and_classes[ 'styles' ] = '';
            	$styles_and_classes[ 'classes' ] = '';
            }

            $markup =  sprintf(
                '<div class="wp-block-german-market german-market-product-element-%4$s"><div class="wgm-info wgm-info-%4$s %1$s" style="%2$s">
                    %3$s
                </div></div>',
                esc_attr( $styles_and_classes[ 'classes' ] ),
                esc_attr( $styles_and_classes[ 'styles' ] ?? '' ) . $align_style,
                $this->strip_tags_before_render ? $this->strip_tags( $rendered_content ) : $rendered_content,
                $this->slug
            );
        }

        return $markup;
    }

    /**
     * Use PHPs strip_tag
     *
     * @param String $rendered_content
     * @return String
     */
    public function strip_tags( $rendered_content ) {
        return strip_tags( $rendered_content, '<a>' );
    }

    /**
     * Get support
     * Attention! If you change something here, change it also in .josn of block!
     * 
     * @return Array
     */
    public function get_support() {
        return array_merge( $this->get_default_support(), $this->get_custom_support() );
    }

    /**
     * Get default support
     *
     * @return Array
     */
    final function get_default_support() {
        return array(
            'color' =>
                array(
                    'text'       => true,
                    'background' => true,
                    'link'       => false,
                ),
            'typography' =>
                array(
                    'fontSize'                          => true,
                    'lineHeight'                        => true,
                    '__experimentalFontWeight'          => true,
                    '__experimentalFontStyle'           => true,
                    '__experimentalFontFamily'          => true,
                    '__experimentalLetterSpacing'       => true 
                ),
            'spacing' => array(
                    'margin'    => true, 
                    'padding'   => true,
            ),
            'customClassName' => false,
        );
    }

    /**
     * Add or change something to the default supports
     * Attention! If you change something here, change it also in .josn of block!
     * 
     * @return Array
     */
    public function get_custom_support() {
        return array();
    }

    /**
     * Get attributes
     * Attention! If you change something here, change it also in .josn of block!
     * @return Array
     */
    public function get_attributes() {
        return array(
            'align' => array(
                "type" => "string",
                "default" => ""
            )
            );
    }

    /**
     * Return true if your edit.js has translations
     *
     * @return boolean
     */
    public function has_editor_script_translation() {
        return false;
    }

    /**
     * Render function with product
     * 
     * @param WC_Product $product
     * @param Array $attributes
     * @param Array $content
     * @param Array $block
     * @param Boolean $is_preview
     * @return String
     */
    abstract function render_product( $product, $attributes, $content, $block, $is_preview );

    /**
     * Render preview, no product available
     * 
     * @param Array $attributes
     * @param Array $content
     * @param Array $block
     * @return String
     */
    public function render_preview_without_product( $attributes, $content, $block ) {
        return '<span class="wgm-info">' . __( 'German Market example data', 'woocommerce-german-market' ) . '</span>';
    }

    /**
     * Get title
     *
     * @return String
     */
    abstract function get_title();

    /**
     * Get description
     *
     * @return String
     */
    abstract function get_description();

    /**
     * Has editor style
     *
     * @return boolean | String
     */
    public function has_editor_style() {
        return $this->has_editor_style;
    }
}
