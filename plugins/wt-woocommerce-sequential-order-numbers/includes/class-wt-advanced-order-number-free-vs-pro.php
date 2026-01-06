<?php
/**
 * Free VS Pro Comparison
 *
 * @link       
 * @since 1.4.2    
 *
 * @package Wt_Advanced_Order_Number  
 */
if (!defined('ABSPATH')) {
    exit;
}

class Wt_Sequential_Order_Freevspro
{
	public function __construct()
	{
		/*
		*	Add free_vs_pro tab
		*/
		add_action('wt_sequential_free_vs_pro_section', array($this, 'wt_free_vs_pro'));
		add_action('wt_sequential_free_vs_pro_section_bottom', array($this, 'wt_bottom_banner'));

	}

	/**
	* 
	*	Comparison table
	*/
	public function wt_free_vs_pro()
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/views/wt-comparison-table.php';
	}

	/**
	* 
	*	Bottom banner
	*/
	public function wt_bottom_banner()
	{
		$tick=WT_SEQUENCIAL_ORDNUMBER_URL.'assets/images/tick_icon.png';
		$pro_upgarde_features=array(
		    __('Add custom suffix for order numbers.', 'wt-woocommerce-sequential-order-numbers'),
		    __('Date suffix in order numbers.', 'wt-woocommerce-sequential-order-numbers'),
		    __('Auto reset sequence per month/year etc.', 'wt-woocommerce-sequential-order-numbers'),
		    __('Custom sequence for free orders.','wt-woocommerce-sequential-order-numbers'), 
		    __('More order number templates.', 'wt-woocommerce-sequential-order-numbers'),  
		    __('Increment sequence in custom series.', 'wt-woocommerce-sequential-order-numbers'),
		);
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/views/wt-bottom-banner.php';
	}

}
new Wt_Sequential_Order_Freevspro();