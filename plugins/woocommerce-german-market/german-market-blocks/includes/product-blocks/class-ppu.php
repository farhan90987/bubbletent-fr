<?php

class German_Market_Product_Block_PPU extends German_Market_Product_Block {

    /**
     * Get title
     *
     * @return String
     */
    public function get_title() {
        return __( 'Price per unit', 'woocommerce-german-market' );
    } 
    
    /**
     * Get description
     *
     * @return String
     */
    public function get_description() {
        return __( 'Display price per unit of the product.', 'woocommerce-german-market' );
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

        $ppu_string = '';
                    
        if ( WGM_Helper::method_exists( $product, 'get_id' ) ) {
            
            $output_parts = array();

			if ( is_a( $product, 'WC_Product_Variation' ) || is_a( $product, 'WC_Product_Variable' ) ) {
				$output_parts = wcppufv_add_price_per_unit( $output_parts, $product, false );
			} else {
				$output_parts[ 'ppu' ] = WGM_Price_Per_Unit::get_price_per_unit_string( $product );
			}

			if ( isset( $output_parts[ 'ppu' ] ) ) {
				$ppu_string = $output_parts[ 'ppu' ];
			}
        }

        return $ppu_string;
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

        $result              = '';
		$price_per_unit_data = array(
            'mult'                          => 1000,
            'unit'                          => 'g',
            'price_per_unit'                => 500,
            'complete_product_quantity'     => 100
        );

		$result .= apply_filters(
			'wmg_price_per_unit_loop',
			sprintf( '<span class="wgm-info price-per-unit price-per-unit-loop ppu-variation-wrap">' . trim( WGM_Price_Per_Unit::get_prefix( $price_per_unit_data ) . ' ' . WGM_Price_Per_Unit::get_output_format() ) . '</span>',
			         wc_price( str_replace( ',', '.', $price_per_unit_data[ 'price_per_unit' ] ), apply_filters( 'wgm_ppu_wc_price_args', array() ) ),
			         str_replace( '.', wc_get_price_decimal_separator(), $price_per_unit_data[ 'mult' ] ),
			         $price_per_unit_data[ 'unit' ]
			),
			wc_price( str_replace( ',', '.', $price_per_unit_data[ 'price_per_unit' ] ) ),
			$price_per_unit_data[ 'mult' ],
			$price_per_unit_data[ 'unit' ]
		);

		return $result;
    }
}
