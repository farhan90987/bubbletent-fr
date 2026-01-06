<?php

namespace WcJUpsellator\Ajax;

use WcJUpsellator\Options\OptionIntegrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class AjaxIntegrations extends BaseAjax 
{	   

	public static function registerHooks()
	{	
			$page = new self();
			
			if( is_admin() )
			{	
					add_action( 'wp_ajax_wjufc_integrations', array( $page, 'wjufc_integrations' ) );																							
			} 
	}
	/*
	/* Change plugin mode from test to live
	*/
	public function wjufc_integrations() 
    {
			
			$this->validateRequest();			

			$active  	 = ( isset( $_POST['active'] ) && $_POST['active'] == 'on' ) ? 1 : 0;
			$plugin_name = sanitize_text_field( $_POST['plugin'] );

			$config = new OptionIntegrations();
			/*
			/* Pre-load properties
			*/
			$config->preSet();
			$config->update( $plugin_name, $active );
			$config->save();
			

			$heading = $active ? __('Integration enabled', 'woo_j_cart' )  : __('Integration disabled', 'woo_j_cart' );
			$text 	 = $active ? 
					__('The selected integration has been enabled', 'woo_j_cart' ) : 
					__('The selected integration has been disabled', 'woo_j_cart' );
			$icon   = $active ? 'success' : 'error';

			wp_send_json( ['modal' => true, 'icon' => $icon, 'text' => $text, 'heading' => $heading ] );

	}	
}