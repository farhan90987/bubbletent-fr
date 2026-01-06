<?php

class German_Market_Product_Block_Delivery_Time extends German_Market_Product_Block {

    /**
     * Get title
     *
     * @return String
     */
    public function get_title() {
        return __( 'Delivery Time', 'woocommerce-german-market' );
    } 
    
    /**
     * Get description
     *
     * @return String
     */
    public function get_description() {
        return __( 'Display the delivery time of the product.', 'woocommerce-german-market' );
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

        $delivery_time = '';
                    
        if ( WGM_Helper::method_exists( $product, 'get_id' ) ) {
            ob_start();
            WGM_Template::add_template_loop_shop( $product );
            $delivery_time .= ob_get_clean();
        }
        
        return $delivery_time;
    }
    
    /**
     * Use PHPs strip_tag
     *
     * @param String $rendered_content
     * @return String
     */
    public function strip_tags( $rendered_content ) {
        return str_replace( ' shipping_de shipping_de_string', '', strip_tags( $rendered_content, '<div>' ) );
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
        return '<span class="wgm-info">' . __( 'Delivery Time:', 'woocommerce-german-market' ) . ' ' . __( 'available for immediate delivery', 'woocommerce-german-market' ) . '</span>';
    }
}
