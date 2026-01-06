<?php

class German_Market_Product_Block_Digital_Prerequisits extends German_Market_Product_Block {

    /**
     * Get title
     *
     * @return String
     */
    public function get_title() {
        return __( 'Requirements (digital)', 'woocommerce-german-market' );
    } 
    
    /**
     * Get description
     *
     * @return String
     */
    public function get_description() {
        return __( 'Display the requirements for a digital product.', 'woocommerce-german-market' );
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

        $digital_prerequisits = '';
                    
        if ( WGM_Helper::method_exists( $product, 'get_id' ) ) {
            
            $digital_prerequisits = WGM_Template::get_digital_product_prerequisits( $product );

        }
        
        return $digital_prerequisits;
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
        $prerequisites = __( 'This block displays the requirements for a digital product.', 'woocommerce-german-market' );
        return '<div class="wgm-info wgm-product-prerequisites">' . $prerequisites . '</div>';
    }
    
}
