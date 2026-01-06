<?php

namespace WcJUpsellator\Options;

use WcJUpsellator\Traits\TraitWordpress;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class OptionExclusion extends BaseOption
{	
	
	use TraitWordpress;

	const OPTION_NAME 		= 'woo_j_upsellator_exclusion';	
	public $pages 			= [];	
	public $mode;
	
	public function __construct()
	{				
			/*
			/* Nothing to do
			*/ 
	}

	public function addPages( $pages )
	{

		if( !is_array( $pages ) ) return;

		foreach( $pages as $page )
		{
			if( is_numeric( $page ) )
			{
				$this->pages[] = (int)$page ;
			}
		}		
		
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
			$this->pages					= [];
			$this->mode 					= 0;
			
			$this->save();		
	}

	public function save()
	{

			$settings 							= [];			
			$settings['pages']					= $this->pages;
			$settings['mode']					= $this->mode;
			
			if( get_option( self::OPTION_NAME ) ) update_option( self::OPTION_NAME, $settings, 'no' );	
			else								  add_option( self::OPTION_NAME, $settings, '', 'no' );		
			
			$this->load( $settings );
			
			return $settings;

	}

	private function loadPages()
	{
			$this->pages = woo_j_exclusion('pages') ?? [];
	}

}
