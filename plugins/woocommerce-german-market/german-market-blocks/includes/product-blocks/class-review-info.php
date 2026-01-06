<?php

class German_Market_Product_Block_Review_Info extends German_Market_Product_Block {

    /**
     * Get title
     *
     * @return String
     */
    public function get_title() {
        return __( 'Legal Information for Product Reviews', 'woocommerce-german-market' );
    } 
    
    /**
     * Get description
     *
     * @return String
     */
    public function get_description() {
        return __( 'Display the legal information for product reviews.', 'woocommerce-german-market' );
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
                    
        $gm_legal_information_product_reviews = WGM_Legal_Information_Product_Reviews::get_instance();
		$review_info = sprintf( $gm_legal_information_product_reviews->get_markup_before_review(), $gm_legal_information_product_reviews->get_info_text() );
		return apply_filters( 'gm_product_review_info', $review_info );
        
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
        return $this->render_product( null, $attributes, $content, $block, true );
    }
}
