<?php

class German_Market_Product_Block_All_Data extends German_Market_Product_Block {

    public $strip_tags_before_render = false;
    public $use_markup = true;

    /**
     * Get title
     *
     * @return String
     */
    public function get_title() {
        return __( 'All Additional Data', 'woocommerce-german-market' );
    } 
    
    /**
     * Get description
     *
     * @return String
     */
    public function get_description() {
        return __( 'Display all additional information (e.g. tax, shipping, delivery time, price per unit, ... ).', 'woocommerce-german-market' );
    }

     /**
     * Get default support
     *
     * @return Array
     */
    public function get_support() {
        return array(
            'color' =>
                array(
                    'text'       => true,
                    'background' => true,
                    'link'       => false,
                ),
            'typography' =>
                array(
                    'fontSize'                          => false,
                    'lineHeight'                        => false,
                    '__experimentalFontWeight'          => false,
                    '__experimentalFontStyle'           => false,
                    '__experimentalFontFamily'          => false,
                    '__experimentalSkipSerialization'   => false,
                    '__experimentalLetterSpacing'       => false
                ),
            'spacing' => array(
                    'margin'    => true, 
            ),
            'customClassName' => false,
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

        $all_data = '';
                    
        if ( WGM_Helper::method_exists( $product, 'get_id' ) ) {

           /**
            * To Do:
            * Get by $attributes wheter it is used in archive oder single page
            * Give this information to the callback function, that regards issue https://gitlab.com/MarketPress/woocommerce-german-market/-/issues/2791
            */

            if ( $is_preview ) {
                add_filter( 'german_market_text_including_tax_is_preview', '__return_true' );
            }
        
            $show_price = false;
            $all_data = WGM_Template::get_wgm_product_summary( $product, 'product-block', $show_price );

            if ( $is_preview ) {
                remove_filter( 'german_market_text_including_tax_is_preview', '__return_true' );
            }
        }

        return $all_data;
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
        
        $example_string = '';

        $example_data_classes = array(
            'tax'                   => 'German_Market_Product_Block_Tax',
            'shipping'              => 'German_Market_Product_Block_Shipping',
            'delivery-time'         => 'German_Market_Product_Block_Delivery_Time',
            'ppu'                   => 'German_Market_Product_Block_PPU'
        );

        foreach ( $example_data_classes as $key => $example_data_class ) {

            $example_data_class_object = new $example_data_class( $key );
            if ( method_exists( $example_data_class_object, 'render_preview_without_product' ) ) {
                $example_string .= $example_data_class_object->render( $attributes, $content, $block );
            }
        }
        
        return $example_string;  
    }
}
