<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class Wt_Advanced_Order_Number_Activator {

    public static function activate() {

    	do_action("wt_advanced_order_number_activate");
        self::wt_seq_save_plugin_details();
        
    }

    public static function wt_seq_save_plugin_details()
    {
        if(false  ===  get_option('wt_seq_basic_installation_date'))
        {
            if(get_option('wt_seq_basic_start_date'))
            {
                $install_date = get_option('wt_seq_basic_start_date',current_time( 'timestamp', true ));
            }
            else
            {
                $install_date = current_time( 'timestamp', true );
            }
            update_option('wt_seq_basic_installation_date',$install_date);
        }
    }

}
