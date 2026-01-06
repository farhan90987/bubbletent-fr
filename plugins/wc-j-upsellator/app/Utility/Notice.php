<?php

namespace WcJUpsellator\Utility;
  
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Notice{

 
	private $text;
	private $type = "success";
	private $plugin_name;
	
	public function __construct()
	{
	
			$this->plugin_name = WC_J_UPSELLATOR_PLUGIN_NAME;
			$this->text_domain = WC_J_UPSELLATOR_TEXTDOMAIN;
			
	}
	
	public function setText( $string )
	{
		
			$this->text = $string;
		
	}
	
	public function error()
	{		
			$this->type = 'error';
	}
	
	public function success()
	{		
			$this->type = 'notice notice-success';
	}
	
	public function warning()
	{		
			$this->type = 'notice notice-warning';
	}
	
	public function show()
	{	
	
			$message 		= sprintf( __( '<b>%s</b>: %s', '%s' ), $this->plugin_name, $this->text, $this->text_domain );
			$html_message   = sprintf( '<div class="%s">%s</div>', $this->type ,wpautop( $message ) );
		
			echo wp_kses_post( $html_message );
		
	}
  
 

}

