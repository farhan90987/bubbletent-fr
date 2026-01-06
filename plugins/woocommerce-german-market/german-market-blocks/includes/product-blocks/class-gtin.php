<?php

class German_Market_Product_Block_GTIN extends German_Market_Product_Block {

    /**
     * Get title
     *
     * @return String
     */
    public function get_title() {
        return __( 'GTIN', 'woocommerce-german-market' );
    } 
    
    /**
     * Get description
     *
     * @return String
     */
    public function get_description() {
        return __( 'Display the global trade item number (GTIN) of the product.', 'woocommerce-german-market' );
    }

     /**
     * Return true if your edit.js has translations
     *
     * @return boolean
     */
    public function has_editor_script_translation() {
        return true;
    }

    /**
     * Render callback
     *
     * @param WC_Product $product
     * @param Array $attributes
     * @param Array $content
     * @param Array $block
     * @param Boolean $is_preview
     * 
     * @return String
     */
    public function render_product( $product, $attributes, $content, $block, $is_preview ) {

        $gtin = '';
                    
        if ( WGM_Helper::method_exists( $product, 'get_meta' ) ) {
            
            $gtin = $product->get_meta( '_gm_gtin' );
            
            if ( 'variable' === $product->get_type() ) {
            	$gtin = '';
            }

            if ( ! empty( $gtin ) ) {
                $gtin = $this->get_markup_with_prefix( $gtin, $attributes );
            }
        }

        return $gtin;
    }

    /**
     * Example rendering
     *
     * @param Array $attributes
     * @param Array $content
     * @param Object $block
     * @return String
     */
    public function render_preview_without_product( $attributes, $content, $block ) {
        return $this->get_markup_with_prefix( '123456789', $attributes );
    }

     /**
     * Get Markup for GTIN with prefix
     *
     * @param String $gtin
     * @param Array $attributes
     * @return String
     */
    public function get_markup_with_prefix( $gtin, $attributes ) {

        $prefix = isset( $attributes[ 'prefix' ] ) ? $attributes[ 'prefix' ] : '';
        if ( ! empty( $prefix ) ) {
            $gtin = '<span class="gtin-prefix">' . $prefix . '</span>' . '<span class="gtin">' . $gtin . '</span>';
        }

        return $gtin;
    }

    /**
     * Get attributes
     * Attention! If you change something here, change it also in .josn of block!
     * @return Array
     */
    public function get_attributes() {
       
        $extra_attributes = array(
            
            'prefix' => array(
                "type" => "string",
                "default" => ""
            )
        );

        return array_merge( parent::get_attributes(), $extra_attributes );
    }
}
