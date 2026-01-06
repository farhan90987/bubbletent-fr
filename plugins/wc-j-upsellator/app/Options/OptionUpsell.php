<?php

namespace WcJUpsellator\Options;

use WcJUpsellator\Traits\TraitWooCommerceHelper;
use WcJUpsellator\Core\Conf;
  
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class OptionUpsell extends BaseOption
{	
	
	use TraitWooCommerceHelper;
	
	const OPTION_NAME 					= 'woo_j_upsellator_upsell';

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

	public function removeUpsell( int $id, bool $save = false )
	{

			$this->loadProducts();

			if( isset( $this->products[ $id ] ) ) unset( $this->products[ $id ] );
			if( $save ) $this->save();
	}
	/*
	/* Change single upsell status
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

	public function editOrAdd( $array )
	{

			$this->loadProducts();
		
			$woo_product 	= wc_get_product( $array['id'] ); 			
			/*
			/* Does product exist?
			*/
			if( $woo_product )
			{

				$product_price  = $woo_product->get_regular_price();
				/*
				/* Is it ready to be sold and discounted?
				*/
				if( !empty( $product_price ) )
				{

					if( $array['discount_type'] == 1 )
					{
							$array['discount'] = ( $array['discount'] > 100 ) ? 100 : $array['discount'];

					}elseif( $array['discount_type'] == 0 ){
							
							$array['discount'] = ( $array['discount'] >= $product_price ) ? ( $product_price -1 ) : $array['discount'];

					}else{

							$array['discount'] = 0;
					}				
					
					$this->products[ $array['id'] ] = $array;
					
					return true;

				}

				return false;

			}
			/*
			/* If we are here, the product is a variable base or not ready to be sold
			*/
			return false;

				
	}
	
	/*
	/* We reorder the upsells 
	*/
	public function reorderUpsells( Array $ids )
	{
		$ordered = [];
		$this->loadProducts();	
		
		foreach( $ids as $id ): 

			$ordered[ $id ] = $this->products[ $id ];			

		endforeach;

		if( count( $this->products ) == count( $ordered ) )
		{
			$this->products = $ordered;
			$this->save();
		}		

	}	

	protected function load( $settings )
	{			
			$this->loadSettings( $settings );
	}
	/*
	/* Add string translations of upsells text and heading
	*/
	protected function translate()
	{
			$this->loadTranslations();

			$upsells = Conf::get( str_replace('woo_j_upsellator_', '', $this::OPTION_NAME ), 'products' );
				
			if( empty( $upsells ) ) return;

			$counter = 1;

			foreach( $upsells as $upsell )
			{
				wjc__addStringTranslation( 'upsell_text_' .$counter , $upsell['text'] );
				wjc__addStringTranslation( 'upsell_heading_' .$counter , $upsell['heading'] );	

				$counter++;
			}
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
			$this->products = woo_j_upsell('products') ?? [];
	}
	
}
