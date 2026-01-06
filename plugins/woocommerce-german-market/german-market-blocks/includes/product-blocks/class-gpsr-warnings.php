<?php

class German_Market_Product_Block_GPSR_Warnings extends German_Market_Product_Block {

	public $strip_tags_before_render = false;

    /**
     * Get title
     *
     * @return String
     */
    public function get_title() {
        return __( 'GPSR Warnings and safety information', 'woocommerce-german-market' );
    } 
    
    /**
     * Get description
     *
     * @return String
     */
    public function get_description() {
        return __( 'Display the warnings and safety information of the product.', 'woocommerce-german-market' );
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

         $gpsr = '';
                    
        if ( WGM_Helper::method_exists( $product, 'get_id' ) ) {
            $gpsr = WGM_Product_GPSR::get_general_product_safety_regulation( $product, 'warnings_and_safety_information', true, 'product-block' );
        }

        return $gpsr;
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
        $value = __( 'Example warnings and safety instructions', 'woocommerce-german-market' );
        ob_start();
        ?><span class="german-market-gpsr german-market-gpsr-warnings_and_safety_information">
			<span class="german-market-gpsr-label german-market-gpsr-label-warnings_and_safety_information"><?php echo esc_attr( get_option( 'german_market_gpsr_label_warnings_and_safety_information', __( 'Warnings and safety information', 'woocommerce-german-market' ) ) ); ?></span>
			<?php echo wpautop( wp_kses_post( $value ) ); ?>
		</span>
		<?php

        return ob_get_clean();
    }
}
