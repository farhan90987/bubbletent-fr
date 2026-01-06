<?php

class German_Market_Product_Block_Extra_Costs_Non_Eu extends German_Market_Product_Block {

    /**
     * Get title
     *
     * @return String
     */
    public function get_title() {
        return __( 'Shipping Note Non-EU Countries', 'woocommerce-german-market' );
    } 
    
    /**
     * Get description
     *
     * @return String
     */
    public function get_description() {
        return __( 'Display the shipping note for non-EU countries.', 'woocommerce-german-market' );
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
        return $this->get_string_non_eu();
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
        return $this->get_string_non_eu();
    }

    /**
     * Get String for legal information
     *
     * @return String
     */
    public function get_string_non_eu() {
        $extra_costs_non_eu = apply_filters( 'wgm_show_extra_costs_eu_html',
        sprintf(
                '<small class="wgm-info wgm-extra-costs-eu">%s</small>',
                get_option( 'woocommerce_de_show_extra_cost_hint_eu_text', __( 'Additional costs (e.g. for customs or taxes) may occur when shipping to non-EU countries.', 'woocommerce-german-market' ))
            )
        );

        return $extra_costs_non_eu;
    }
}
