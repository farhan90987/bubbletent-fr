<?php

namespace WcJUpsellator\Ajax;
  
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class BaseAjax 
{	   
	/*
	/* Check referer and user permissions
	*/
	protected function validateRequest()
	{		
			check_ajax_referer( 'wjucf-ajax', 'security' ); 

			if ( !current_user_can( 'manage_woocommerce' ) ) {
				wp_die();
			}
	}
	/*
	/* Return error if something went wrong
	*/
	protected function throwErrorModal()
	{
			wp_send_json(['modal' => true, 
						'icon' => 'warning', 
						'text' => __('Something went wrong', 'woo_j_cart' ), 
						'heading' => __('Error', 'woo_j_cart' ) 
					]);
	}
}