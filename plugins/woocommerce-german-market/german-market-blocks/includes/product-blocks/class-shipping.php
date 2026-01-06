<?php

class German_Market_Product_Block_Shipping extends German_Market_Product_Block {

    /**
     * Get title
     *
     * @return String
     */
    public function get_title() {
        return __( 'Shipping Information', 'woocommerce-german-market' );
    } 
    
    /**
     * Get description
     *
     * @return String
     */
    public function get_description() {
        return sprintf(
            __( 'Display the shipping information of the product (e.g. "%s").', 'woocommerce-german-market' ),
           strip_tags( apply_filters( 'german_market_get_shipping_page_link_text', __( 'plus <a %s>shipping</a>', 'woocommerce-german-market' ) ) )
        );
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

        $info = '';
                    
        if ( WGM_Helper::method_exists( $product, 'get_id' ) ) {

            $shipping_info = WGM_Shipping::get_shipping_page_link( $product );

            // Check for free shipping advertising option
		    $free_shipping = get_option( 'woocommerce_de_show_free_shipping' ) === 'on';

            $info = sprintf(
                '<div class="wgm-info woocommerce_de_versandkosten">%s</div>',
                $shipping_info
            );

            //TODO Deprecate 2nd parameter (used to $stopped_by_option, which has always been false since there was a return before this filter was able to run)
            $info = apply_filters( 'wgm_product_shipping_info', $info, FALSE, $free_shipping, $product );
        }
        
        return $info;
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
        return strip_tags( apply_filters( 'german_market_get_shipping_page_link_text', __( 'plus <a %s>shipping</a>', 'woocommerce-german-market' ) ) );
    }
    
}
