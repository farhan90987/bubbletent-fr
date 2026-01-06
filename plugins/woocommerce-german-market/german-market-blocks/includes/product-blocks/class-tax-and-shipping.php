<?php

class German_Market_Product_Block_Tax_And_Shipping extends German_Market_Product_Block {

    /**
     * Get title
     *
     * @return String
     */
    public function get_title() {
        return __( 'Tax & Shipping', 'woocommerce-german-market' );
    } 
    
    /**
     * Get description
     *
     * @return String
     */
    public function get_description() {
        return __( 'Display tax and shipping information of the product in one line.', 'woocommerce-german-market' );
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

        $tax_and_shipping_info = '';
                    
        if ( WGM_Helper::method_exists( $product, 'get_id' ) ) {
            
            // 1. delimiter
            $delimiter = isset( $attributes[ 'delimiter' ] ) ? $attributes[ 'delimiter' ] : ', ';

            // 2. shipping part
            $shipping_info = WGM_Shipping::get_shipping_page_link( $product );

            // Check for free shipping advertising option
		    $free_shipping = get_option( 'woocommerce_de_show_free_shipping' ) === 'on';

            $info = sprintf(
                '<div class="wgm-info woocommerce_de_versandkosten">%s</div>',
                $shipping_info
            );

            //TODO Deprecate 2nd parameter (used to $stopped_by_option, which has always been false since there was a return before this filter was able to run)
            $info = apply_filters( 'wgm_product_shipping_info', $info, FALSE, $free_shipping, $product );
            $info = trim( strip_tags( $info, '<a>' ) );

            // 3. tax part
            $is_cart = false;
            $tax_info = WGM_Tax::text_including_tax( $product, $is_cart, $is_preview );
            $tax_info = trim( strip_tags( $tax_info ) );

            // 4. together
            if ( empty( $info ) || empty( $tax_info ) ) {
                $delimiter = '';
            }

            $tax_and_shipping_info = $tax_info . $delimiter . $info;
            $tax_and_shipping_info = '<span class="wgm-info tax-and-shipping">' . $tax_and_shipping_info . '</span>';

        }

        return $tax_and_shipping_info;
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

        // 1. delimiter
        $delimiter = isset( $attributes[ 'delimiter' ] ) ? $attributes[ 'delimiter' ] : ', ';

        // 2. shipping part
        $info = strip_tags( apply_filters( 'german_market_get_shipping_page_link_text', __( 'plus <a %s>shipping</a>', 'woocommerce-german-market' ) ) );

        // 3. tax part
        $tax_info = __( 'incl. Vat',  'woocommerce-german-market' );

        $tax_and_shipping_info = $tax_info . $delimiter . $info;
        $tax_and_shipping_info = '<span class="wgm-info tax-and-shipping">' . $tax_and_shipping_info . '</span>';

        return $tax_and_shipping_info;
    }

    /**
     * Get attributes
     * Attention! If you change something here, change it also in .josn of block!
     * @return Array
     */
    public function get_attributes() {
       
        $extra_attributes = array(
            
            'delimiter' => array(
                "type" => "string",
                "default" => ", "
            )
        );

        return array_merge( parent::get_attributes(), $extra_attributes );
    }
}
