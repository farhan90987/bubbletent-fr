<?php

class German_Market_Product_Block_GPSR_Producer extends German_Market_Product_Block {

	public $strip_tags_before_render = false;
	
    /**
     * Get title
     *
     * @return String
     */
    public function get_title() {
        return __( 'GPSR Manufacturer', 'woocommerce-german-market' );
    } 
    
    /**
     * Get description
     *
     * @return String
     */
    public function get_description() {
        return __( 'Display the GPRS information for the manufacturer of the product.', 'woocommerce-german-market' );
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

        $gpsr_manufacturer = '';
                    
        if ( WGM_Helper::method_exists( $product, 'get_id' ) ) {
            $gpsr_manufacturer = WGM_Product_GPSR::get_general_product_safety_regulation( $product, 'manufacturer', true, 'product-block' );
        }

        return $gpsr_manufacturer;
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
        
        $value = 'Manufacturer Company, Manufacturer Rd. A, 12345 Example Street, Texas, USA, www.manufacturer.com';
        ob_start();
        ?><span class="german-market-gpsr german-market-gpsr-manufacturer">
			<span class="german-market-gpsr-label german-market-gpsr-label-manufacturer"><?php echo esc_attr( get_option( 'german_market_gpsr_label_manufacturer', __( 'Manufacturer', 'woocommerce-german-market' ) ) ); ?></span>
			<?php echo wpautop( wp_kses_post( $value ) ); ?>
		</span>
		<?php

        return ob_get_clean();
    }
}
