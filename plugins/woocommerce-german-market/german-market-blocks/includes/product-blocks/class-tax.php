<?php

class German_Market_Product_Block_Tax extends German_Market_Product_Block {

    /**
     * Get title
     *
     * @return String
     */
    public function get_title() {
        return __( 'Tax', 'woocommerce-german-market' );
    } 
    
    /**
     * Get description
     *
     * @return String
     */
    public function get_description() {
        return __( 'Display the tax information for the product.', 'woocommerce-german-market' );
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

        $tax_info = '';
                    
        if ( WGM_Helper::method_exists( $product, 'get_id' ) ) {
            $is_cart = false;
            $tax_info = WGM_Tax::text_including_tax( $product, $is_cart, $is_preview );
        }

        return $tax_info;
    }

    /**
     * Render preview, no product available
     * 
     * @param Array $attributes
     * @param Array $content
     * @param Array $block
     * @return String
     */
    public function render_preview_without_product( $attributes, $content, $block ) {
        return __( 'incl. Vat',  'woocommerce-german-market' );
    }
}
