<?php

class German_Market_Product_Block_Sale_Label extends German_Market_Product_Block {

    /**
     * Get title
     *
     * @return String
     */
    public function get_title() {
        return __( 'Sale Label', 'woocommerce-german-market' );
    } 
    
    /**
     * Get description
     *
     * @return String
     */
    public function get_description() {
        return __( 'Display the sale label of the product.', 'woocommerce-german-market' );
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

        $sale_label = '';
                    
        if ( WGM_Helper::method_exists( $product, 'get_id' ) ) {
            $sale_label = WGM_Template::add_sale_label_to_price( '', $product, false );
        }

        return $sale_label;
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
        
        $term_id = get_option( WGM_Helper::get_wgm_option( 'global_sale_label' ) );
        $label_term = get_term( $term_id, 'product_sale_labels' );
		
        if ( is_wp_error( $label_term ) || ! isset( $label_term ) ) {
			$label_string = '';
		} else {
			$label_string = $label_term->name;
		}

		if (  empty( $label_string ) ) {
            $label_string = __( 'MSRP: ', 'woocommerce-german-market' );
        }
			
        return '<span class="wgm-sale-label">' . $label_string . '</span> ';
    }
}
