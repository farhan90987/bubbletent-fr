<?php

namespace WcJUpsellator\Options;

use WcJUpsellator\Traits\TraitWordpress;
  
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class OptionIntegrations extends BaseOption
{	
	
	use TraitWordpress;

	const OPTION_NAME 		= 'woo_j_upsellator_integrations';
	
	public $valid_integrations    = ['wcpboc', 'wprocket', 'woocs', 'wmc_curcy'];
	public $integrations		  = [];
	
	
	public function __construct()
	{						
		/*
		/* Nothing to do
		*/					
	}

	public function preSet()
	{
			$settings = get_option( static::OPTION_NAME );

			if( !empty( $settings ) ): 
				
				$this->integrations =  $settings['integrations'];				

			endif;

	}

	public function update( $plugin, $value = 0)
	{
			if( !in_array( $plugin, $this->valid_integrations ) ) return false;

			if( !$value )
			{
				$this->integrations = array_filter( $this->integrations,  function ($element) use ($plugin) {
					return $element !==  $plugin;					
				});

				return true;
			}
			
			array_push( $this->integrations, $plugin );
			
			$this->integrations = array_unique( $this->integrations );

			return true;
	}

	protected function load( $settings )
	{			
			$this->loadSettings( $settings );
	}

	protected function translate()
	{
			//Nothing
	}

	protected function setDefaults()
	{		
				
			$this->integrations			    = [];						
			
			$this->save();		

	}

	public function save()
	{

			$settings 									= [];	
			$settings['integrations'] 					= $this->integrations;				

			if( get_option( self::OPTION_NAME ) ) update_option( self::OPTION_NAME,  $settings, 'no' );	
			else								  add_option( self::OPTION_NAME,  $settings, '', 'no' );		
			
			$this->load( $settings );
			
			return $settings;

	}

}


