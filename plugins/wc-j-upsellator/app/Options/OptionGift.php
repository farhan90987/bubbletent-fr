<?php

namespace WcJUpsellator\Options;

use WcJUpsellator\Traits\TraitWooCommerceHelper;
use WcJUpsellator\Traits\TraitWordpress;
use WcJUpsellator\Core\Conf;
  
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class OptionGift extends BaseOption
{	
	
	use TraitWooCommerceHelper;
	use TraitWordpress;
	
	const OPTION_NAME 					= 'woo_j_upsellator_gift';	

	public $products					= [];
	
	public function __construct()
	{				
			/*
			/* Nothing to do
			*/ 
	} 

	protected function setDefaults()
	{				
			$this->products							= [];				

			$this->save();
	}
	/*
	/* Add string translations of gifts text and heading
	*/
	protected function translate()
	{
			$this->loadTranslations();

			$gifts = Conf::get( str_replace('woo_j_upsellator_', '', $this::OPTION_NAME ), 'products' );
				
			if( empty( $gifts ) ) return;

			$counter = 1;

			foreach( $gifts as $gift )
			{
				wjc__addStringTranslation( 'gift_text_' .$counter , $gift['text'] );
				wjc__addStringTranslation( 'gift_heading_' .$counter , $gift['heading'] );	

				$counter++;
			}
	}

	public function editOrAdd( $array )
	{

			$this->loadProducts();
			
			$product = wc_get_product( $array['id'] ); 
			/*
			/* Does product exist?
			*/
			if( $product )
			{

				$this->products[ $array['id'] ] = $array;	
				return true;

			}

			return false;								
				
	}

	public function removeGift( int $id, bool $save = false )
	{

			$this->loadProducts();

			if( isset( $this->products[ $id ] ) ) unset( $this->products[ $id ] );
			if( $save ) $this->save();
	}
	/*
	/* Change single gift status
	*/
	public function changeStatus( int $id, int $status )
	{

			$this->loadProducts();

			if( isset( $this->products[ $id ] ) )
			{

				$this->products[ $id ]['active'] = $status;
				$this->save();

				return true;

			} 

			return false;
	}

	protected function load( $settings )
	{	
		$this->loadSettings( $settings );			
	}

	public function save()
	{

			$settings 							  = [];			
			$settings['products']	  			  = $this->products;		
			
			if( get_option( self::OPTION_NAME ) ) update_option( self::OPTION_NAME,  $settings, 'no' );				
			else 							      add_option( self::OPTION_NAME,  $settings, '', 'no' );				
			
			$this->load( $settings );
			
			return $settings;

	}

	private function loadProducts()
	{			
			$this->products = woo_j_gift('products') ?? [];
	}
	
}
