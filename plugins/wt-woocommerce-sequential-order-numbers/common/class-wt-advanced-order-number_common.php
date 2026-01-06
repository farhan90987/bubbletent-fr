<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.webtoffee.com
 * @since      1.5.2
 *
 * @package    Wt_Advanced_Order_Number
 * @subpackage Wt_Advanced_Order_Number/common
 */

if(!class_exists('Wt_Advanced_Order_Number_Common'))
{
class Wt_Advanced_Order_Number_Common
{
    /**
     * The ID of this plugin.
     *
     * @since    1.5.2
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.5.2
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;
    private static $instance = null;
    private static $hpos_enabled = null;

    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    public static function get_instance($plugin_name, $version)
    {
        if(self::$instance==null)
        {
            self::$instance=new Wt_Advanced_Order_Number_Common($plugin_name, $version);
        }
        return self::$instance;
    }

    /**
     * Is WooCommerce HPOS enabled
     * 
     * @since   1.5.2
     * @static
     * @return  bool True when enabled otherwise false
     */
    public static function is_wc_hpos_enabled()
    {
        if(is_null(self::$hpos_enabled))
        {
            if(class_exists('Automattic\WooCommerce\Utilities\OrderUtil'))
            {
                self::$hpos_enabled = Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
            }else
            {
                self::$hpos_enabled = false;
            }
        }
        return self::$hpos_enabled;
    }

    /**
     * Get WC_Order object from the given value.
     * 
     * @since   1.5.2
     * @static
     * @param   int|WC_order    $order      Order id or order object
     * @return  WC_order        Order object
     */
    public static function get_order($order)
    {
        return (is_int($order) || is_string($order) ? wc_get_order($order) : $order);
    }

    
    /**
     * Get order id from the given value.
     * 
     * @since   1.5.2
     * @static
     * @param   int|WC_order    $order      Order id or order object
     * @return  int             Order id
     */
    public static function get_order_id($order)
    {
        return (is_int($order) || is_string($order) ? (int)$order : $order->get_id());
    }

    /**
     * Get orders based on the arguments provided
     * 
     * @since   1.5.2
     * @static
     * @param   array   $args     Query arguments for `wc_get_orders` function
     * @return  array   Orders
     */
    public static function get_orders($args)
    {
        return wc_get_orders($args);
    }

    /**
     * Get order meta value.
     * HPOS and non-HPOS compatible
     * 
     * @since   1.5.2
     * @static
     * @param   int|WC_order    $order      Order id or order object
     * @param   string     $meta_key   Meta key
     * @param   mixed      $default    Optional, Default value for the meta
     */
    public static function get_order_meta($order, $meta_key, $default = '')
    {
        if(self::is_wc_hpos_enabled())
        {
            $order = self::get_order($order); 
            if(!$order)
            {
                return $default;
            }
            $meta_value = $order->get_meta($meta_key);
            return (!$meta_value ? get_post_meta($order->get_id(), $meta_key, true) : $meta_value);
        }
        else
        {
            $order_id = self::get_order_id($order);
            $meta_value = get_post_meta($order_id, $meta_key, true);
            if(!$meta_value)
            {
                $order = wc_get_order($order_id);
                return $order ? $order->get_meta($meta_key) : $default;
            }
            else
            {
                return $meta_value;
            }
            }
        }

    /**
     * Update order meta.
     * HPOS and non-HPOS compatible
     * 
     * @since   1.5.2
     * @static
     * @param   int|WC_order    $order      Order id or order object
     * @param   string          $meta_key   Meta key
     * @param   mixed           $value      Value for meta
     */
    public static function update_order_meta($order, $meta_key, $value)
    {
        if(self::is_wc_hpos_enabled())
        {
            $order = self::get_order($order);
            $order->update_meta_data($meta_key, $value);

            /**
             *  if post and order table are not synchronized,
             *  then update the meta key and meta value from the post meta table
             */ 
            if("yes" !== get_option( 'woocommerce_custom_orders_table_data_sync_enabled' )){
                $order_id = self::get_order_id($order);
                update_post_meta($order_id, $meta_key, $value);
            }
            $order->save();
        }else
        {
            $order = self::get_order($order);
            $order_id = self::get_order_id($order);
            update_post_meta($order_id, $meta_key, $value);
            $order->save();

            /**
             *  If the post and order table are not synchronized or HPOS is not enabled yet,
             *  then update the meta key and meta value from the wc_order_meta table
             */
            if("yes" !== get_option( 'woocommerce_custom_orders_table_data_sync_enabled' )){
                self::add_meta_to_wc_order_table($order,$meta_key,$value);
            }
        }
    }

    public static function meta_key_exists_in_wc_order_meta($order_id,$meta_key){
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $search = $wpdb->get_row($wpdb->prepare("SELECT `id` from {$wpdb->prefix}wc_orders_meta WHERE `meta_key` IN (%s) AND `order_id` = %d",array($meta_key,$order_id)));
        if(!$search){
            return false;
        }else{
            return true;
        }
    }

    public static function add_meta_to_wc_order_table($order,$meta_key,$value){
        global $wpdb;
        $table_name = $wpdb->prefix.'wc_orders_meta';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        if($wpdb->get_var("SHOW TABLES LIKE {$wpdb->prefix}wc_orders_meta") === $table_name){
            $order_id = self::get_order_id($order);
            if(self::meta_key_exists_in_wc_order_meta($order_id,$meta_key)){
                $update_data        = array('meta_value' => $value); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
                $update_data_type   = array( '%s' );
                $update_where       = array(
                    'order_id'  => $order_id,
                    'meta_key'  => $meta_key // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                );
                $update_where_type  = array('%d','%s');
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->update($table_name,$update_data,$update_where,$update_data_type,$update_where_type);
            }else{
                $insert_data = array(
                    'order_id'      =>  $order_id,
                    'meta_key'      =>  $meta_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    'meta_value'    =>  $value // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
                );
                $insert_data_type = array(
                    '%d','%s','%s'
                );
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                $wpdb->insert($table_name,$insert_data,$insert_data_type);
            }
        }
    }

    public static function which_table_to_take(){
        if(self::is_wc_hpos_enabled()){
			if("yes" !== get_option( 'woocommerce_custom_orders_table_data_sync_enabled' )){
				$which_table = 'order_table';
			}else{
				$which_table = 'post_table';
			}
		}else{
			$which_table = "post_table";
		}
        return $which_table;
    }
}
}