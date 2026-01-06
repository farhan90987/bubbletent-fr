<?php

class German_Market_Product_Block_Charging_Device extends German_Market_Product_Block {

	public $strip_tags_before_render = false;
    public $has_editor_style = true;

    /**
     * Get title
     *
     * @return String
     */
    public function get_title() {
        return __( 'Charging Device', 'woocommerce-german-market' );
    } 
    
    /**
     * Get description
     *
     * @return String
     */
    public function get_description() {
        return __( 'Display the pictogram and label for the charging device.', 'woocommerce-german-market' );
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

         $charging_device_output = '';
                    
        if ( WGM_Helper::method_exists( $product, 'get_id' ) ) {
        	$charging_device = WGM_Product_Charging_Device::get_instance();
            $charging_device_output = $charging_device->get_markup_by_product( $product );
        }

        return $charging_device_output;
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
        $charging_device_output = __( 'This block outputs the picotram and the label for the charging device.', 'woocommerce-german-market' );
        return $charging_device_output;
    }
}
