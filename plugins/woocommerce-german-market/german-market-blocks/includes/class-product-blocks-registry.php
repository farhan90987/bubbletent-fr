<?php

defined( 'ABSPATH' ) || exit;

/**
 * This class registers all blocks for product data
 */
class German_Market_Product_Blocks_Registry extends German_Market_Blocks_Methods {

    /**
     * Call actions and filters
     *
     * @return void
     */
    public function init() {

        // add our own block category
        add_action( 'block_categories_all', array( $this, 'register_block_category' ), 10, 2 );

        // register blocks
        add_action( 'init', array( $this, 'register_blocks' ) );
        
        // autoload classes
        spl_autoload_register( array( $this, 'autoload' ) );
    }

    /**
	 * Get all product blocks
	 *
	 * @return Array
	 */
    public function get_product_blocks() {

        $german_market_product_blocks = array(
            
            'delivery-time'        	 	=> 'German_Market_Product_Block_Delivery_Time',
            'tax'                  		=> 'German_Market_Product_Block_Tax',
            'shipping'             	 	=> 'German_Market_Product_Block_Shipping',
            'tax-and-shipping'      	=> 'German_Market_Product_Block_Tax_And_Shipping',
            'ppu'                   	=> 'German_Market_Product_Block_PPU',
            'gtin'                  	=> 'German_Market_Product_Block_GTIN',
            'extra-costs-non-eu'   		=> 'German_Market_Product_Block_Extra_Costs_Non_Eu',
            'sale-label'           	 	=> 'German_Market_Product_Block_Sale_Label',
            'digital-prerequisits' 	 	=> 'German_Market_Product_Block_Digital_Prerequisits',
            'review-info'           	=> 'German_Market_Product_Block_Review_Info',
            'all-data'              	=> 'German_Market_Product_Block_All_Data',
            'gpsr-producer'				=> 'German_Market_Product_Block_GPSR_Producer',
            'gpsr-responsible-person'	=> 'German_Market_Product_Block_GPSR_Responsible_Person',
            'gpsr-warnings'				=> 'German_Market_Product_Block_GPSR_Warnings',
            'charging-device'           => 'German_Market_Product_Block_Charging_Device',
        );

        if ( get_option( 'german_market_age_rating', 'off' ) == 'on' ) {
			$german_market_product_blocks[ 'age-rating' ] = 'German_Market_Product_Block_Age_Rating';
		}

        return $german_market_product_blocks;
    }

    /**
	 * autoload classes on demand
	 *
	 * @param string $class
	 * @return void
	 */
	public function autoload( $classname ) {

        if ( false !== strpos( $classname, 'German_Market_Product_Block_' ) ) {
            
            $filename = str_replace( 'German_Market_Product_Block_', '', $classname );
            $filename = strtolower( $filename );
            $filename = str_replace( '_', '-', $filename );
            
            require_once( GermanMarketBlocks::$package_path . 'includes/product-blocks/class-' . $filename . '.php'  );
        }

    }

    /**
	 * Register product related block categories.
	 *
	 * @param array[]                 $block_categories Array of categories for block types.
	 * @param WP_Block_Editor_Context $editor_context   The current block editor context.
     * @return Array
	 */
    public function register_block_category( $block_categories, $editor_context ) {
        
        $block_categories[] = array(
            'slug'  => 'german-market-product-elements',
            'title' => __( 'German Market Product Elements', 'woocommerce-german-market' ),
            'icon'  => null,
        );

        return $block_categories;
    }

    /**
     * Get data for class "GermanMarketBlockIntegration"
     *
     * @return Array
     */
    public function get_block_integration_data() {

        $blocks = array();
        
        foreach ( $this->get_product_blocks() as $key => $class_name ) {
            
            $german_market_block = new $class_name( $key );

            $blocks[ 'german-market-product-' . $key ] = array(
        
                'directory'			            => 'product-' . $key,
                'editor-script'		            => 'index.js',
                'editor-asset'		            => 'index.asset.php',
                'frontend-script'	            => false,
                'editor-style'		            => $german_market_block->has_editor_style() ? 'style-index.css' : false,
                'has-editor-script-translation' => $german_market_block->has_editor_script_translation(),

            );
        }

        return $blocks;
    }

    /**
	 * Register product related blocks
	 *
     * @return void
	 */
    public function register_blocks() {

        foreach ( $this->get_product_blocks() as $key => $class_name ) {
            
            $german_market_block = new $class_name( $key );

            register_block_type( GermanMarketBlocks::$package_path . '/build/blocks/product-' . $key,  
                array(
                    'render_callback'   => array( $german_market_block, 'render' ),
                    'title'         	=> $german_market_block->get_title(),
                    'description'       => $german_market_block->get_description(),
                    'uses_context'      => $german_market_block->get_uses_context(),
                    'supports'          => $german_market_block->get_support(),
                    'attributes'        => $german_market_block->get_attributes(),
                )
            );
        }
    }
}
