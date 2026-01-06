<?php

namespace WcJUpsellator\Options;

use WcJUpsellator\Core\Conf;
  
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class BaseOption 
{	
	/*
	/* Loads default settings ( using wp_option )
	/* -- If option exist, retreive it,
	/* -- else create it with default values
	*/    
	public static function init()
	{		

			$page       = new static();           
			$settings   = get_option( static::OPTION_NAME );
			
			if( $settings ) $page->load( $settings );
			else			$page->setDefaults();

			if( !function_exists( 'pll_register_string' ) ) return;

			$page->translate();			
		
	} 

	protected function getOptions()
	{
		return get_option( static::OPTION_NAME );
	}
	/*
	/* Clear and repopulate the helper
	*/
	protected function loadSettings( $values )
	{
		
		Conf::clear( static::OPTION_NAME );

		if( empty( $values ) ) return;
	
		foreach( $values as $key => $value ):			
			
			Conf::set( str_replace('woo_j_upsellator_', '', static::OPTION_NAME ), $key, $value );			

		endforeach;	
	}
	/*
	/* Add polylang translateable strings if plugin exists
	*/
	protected function loadTranslations()
	{		

		$translatable = $this->translatable ?? [];

		if( empty( $translatable ) ) return;

		foreach( $translatable as $transl )
		{
			$value = Conf::get( str_replace('woo_j_upsellator_', '', $this::OPTION_NAME ), $transl );
			
			wjc__addStringTranslation( $transl, $value );
		}
		
	}
}