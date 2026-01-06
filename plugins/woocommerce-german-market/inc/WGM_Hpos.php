<?php

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController; 
use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Helper Functions for High Performance Order Storage from WooCommerce
 *
 */
class WGM_Hpos {

    private static $instance = null;

    /**
    * Singletone get_instance
    *
    * @static
    * @return WGM_Compatibilities
    */
    public static function get_instance() {
        if ( self::$instance == NULL) {
            self::$instance = new WGM_Hpos(); 
        }
        return self::$instance;
    }

    /**
    * Singletone constructor
    *
    * @access private
    */
    private function __construct() {

    	// Declaring extension compatibility "High Performance Order Storage" (HPOS) for WooCommerce
		add_action( 'before_woocommerce_init', function() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', Woocommerce_German_Market::$plugin_filename, true );
			}
		} );
    }

    /**
    * Get if hpos is enabled
    *
    * @return bollean
    */
    public static function is_hpos_enabled() {
        return wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled();
    }
    
    /**
    * Get id of "edit-shop_order" screen
    *
    * @return String
    */
    public static function get_edit_shop_order_screen() {
        return  self::is_hpos_enabled()
                ? wc_get_page_screen_id( 'shop-order' ) 
                : 'edit-shop_order';
    }

    /**
    * Get if current screen id is "edit-shop_order" screen
    *
    * @return Boolean
    */
    public static function is_edit_shop_order_screen() {
        return get_current_screen()->id === self::get_edit_shop_order_screen();
    }

    /**
    * Get hook for order bulk actions
    *
    * @return String
    */
    public static function get_hook_for_order_bulk_actions() {
        return 'bulk_actions-' . self::get_edit_shop_order_screen();
    }

    /**
    * Get hook for order bulk actions
    *
    * @return String
    */
    public static function get_hook_for_order_handle_bulk_actions() {
        return 'handle_bulk_actions-' . self::get_edit_shop_order_screen();
    }

    /**
    * Get hook for "manage_shop_order_posts_custom_column"
    *
    * @return String
    */
    public static function get_hook_manage_shop_order_custom_column() {
         $screen =  self::is_hpos_enabled()
                ? wc_get_page_screen_id( 'shop-order' ) 
                : 'shop_order_posts';
        return 'manage_' . $screen . '_custom_column';
    }

    /**
    * Get hook for "manage_shop_order_posts_columns"
    *
    * @return String
    */
    public static function get_hook_for_manage_shop_order_posts_columns() {
    	 $screen =  self::is_hpos_enabled()
                ? wc_get_page_screen_id( 'shop-order' ) 
                : 'shop_order_posts';
        return 'manage_' . $screen . '_columns';
    }

    /**
	* Get hook for "manage_edit-shop_order_sortable_columns"
	* 
	* @return String
	*/
    public static function get_hook_for_manage_edit_shop_order_sortable_columns() {
    	$screen =  self::is_hpos_enabled()
                ? wc_get_page_screen_id( 'shop-order' ) 
                : 'edit-shop_order';
        return 'manage_' . $screen . '_sortable_columns';
    }

    /**
    * Check if unkown object is an order object
    *
    * @return Bool
    */ 
    public static function is_order( $maybe_order_object ) {

    	$is_order = false;
    	if ( is_object( $maybe_order_object ) && method_exists( $maybe_order_object, 'get_id' ) ) {
    		$is_order = OrderUtil::is_order( $maybe_order_object->get_id(), wc_get_order_types() );
    	}

    	return $is_order;
    }
}
