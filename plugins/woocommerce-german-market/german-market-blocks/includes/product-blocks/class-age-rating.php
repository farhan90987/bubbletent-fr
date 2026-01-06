<?php

class German_Market_Product_Block_Age_Rating extends German_Market_Product_Block {

    public $strip_tags_before_render = false;
    
    /**
     * Get title
     *
     * @return String
     */
    public function get_title() {
        return __( 'Age Rating', 'woocommerce-german-market' );
    } 
    
    /**
     * Get description
     *
     * @return String
     */
    public function get_description() {
        return __( 'Display the minimum age the buyer must be to buy the product.', 'woocommerce-german-market' );
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

        $product_age_rating_without_intval = '';

		if ( WGM_Helper::method_exists( $product, 'get_id' ) ) {
			$product_age_rating = WGM_Age_Rating::get_age_rating_or_product( $product );
			$product_age_rating_without_intval = WGM_Age_Rating::get_age_rating_or_product( $product, false );
		}

		if ( '' === $product_age_rating_without_intval ) {

			if ( '' === get_option( 'german_market_age_rating_default_age_rating', '' ) ) {
				return '';
			}

			$product_age_rating = intval( get_option( 'german_market_age_rating_default_age_rating', '' ) );
		}

		$product_age_rating_string = $this->get_markup_with_prefix_and_suffix( $product_age_rating, $attributes );

		return apply_filters( 'german_market_shortcode_age_rating_callback', $product_age_rating_string, $product, $attributes );
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
        return $this->get_markup_with_prefix_and_suffix( 18, $attributes );
    }

    /**
     * Get Markup for age with prefix and suffix
     *
     * @param Integer $age
     * @param Array $attributes
     * @return String
     */
    public function get_markup_with_prefix_and_suffix( $age, $attributes ) {

        $prefix = isset( $attributes[ 'prefix' ] ) ? $attributes[ 'prefix' ] : '';
		$suffix = isset( $attributes[ 'suffix' ] ) ? $attributes[ 'suffix' ] : '';

        if ( ! empty( $prefix ) ) {
            $prefix = '<span class="german-market-age-rating german-market-age-rating-prefix german-market-age-rating-age-' . $age . '">' . $prefix . '</span>';
        }

        if ( ! empty( $suffix ) ) {
            $suffix = '<span class="german-market-age-rating german-market-age-rating-suffix german-market-age-rating-age-' . $age . '">' . $suffix . '</span>';
        }

        $product_age_rating_string = $prefix . '<span class="german-market-age-rating german-market-age-rating-age german-market-age-rating-age-' . $age . '">' . $age . '</span>' . $suffix;

        if ( isset( $attributes[ 'hide_if_zero' ] ) && true === $attributes[ 'hide_if_zero' ] ) {
			if ( intval( $age ) === 0 ) {
				$product_age_rating_string = '<span class="german-market-age-rating-empty"></span>';
			}
		}

        return $product_age_rating_string;
    }

    /**
     * Get attributes
     * Attention! If you change something here, change it also in .josn of block!
     * @return Array
     */
    public function get_attributes() {
       
        $extra_attributes = array(
            
            'prefix' => array(
                "type" => "string",
                "default" => ""
            ),

            'suffix' => array(
                "type" => "string",
                "default" => ""
            ),

            'hide_if_zero'  => array(
                "type" => "boolean",
                "default" => true
            ),
        );

        return array_merge( parent::get_attributes(), $extra_attributes );
    }
}
