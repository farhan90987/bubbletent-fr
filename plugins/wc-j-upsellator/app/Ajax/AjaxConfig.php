<?php

namespace WcJUpsellator\Ajax;

use WcJUpsellator\Options\OptionConfig;
  
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class AjaxConfig extends BaseAjax 
{	   

	public static function registerHooks()
	{	
			$page = new self();
			
			if( is_admin() )
			{	
					add_action( 'wp_ajax_wjufc_set_test_mode', array( $page, 'wjufc_set_test_mode' ) );																							
			} 
	}
	/*
	/* Change plugin mode from test to live
	*/
	public function wjufc_set_test_mode() 
    {
			
			$this->validateRequest();
			
			$config = new OptionConfig();
			/*
			/* Pre-load properties
			*/
			$config->preSet();
			$config->test_mode = ( isset( $_POST['test_mode'] ) ) ? true : false;
			$config->save();

			$heading = isset( $_POST['test_mode'] ) ? __('Test mode activated', 'woo_j_cart' )  : __('Live mode activated', 'woo_j_cart' );
			$text 	 = isset( $_POST['test_mode'] ) ? 
					__('The test mode has been activated: J Cart Upsell and Cross-sell now is visible only to admins', 'woo_j_cart' ) : 
					__('Live mode activated: J Cart Upsell and Cross-sell on full charge!', 'woo_j_cart' );
			$icon   = isset( $_POST['test_mode'] ) ? 'warning' : 'success';

			wp_send_json( ['modal' => true, 'icon' => $icon, 'text' => $text, 'heading' => $heading ] );

	}	
}