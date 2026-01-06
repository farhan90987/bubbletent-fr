<?php

namespace WcJUpsellator\Render;

use WcJUpsellator\Core\UpsellatorPreLoader;
use WcJUpsellator\Traits\TraitWooCommerceHelper;
use WcJUpsellator\Gift;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ItemsList
{	

	use TraitWooCommerceHelper;
	
	public function __construct()
	{		
			/*
			/* Nothing to do
			*/ 
	}	

	public function render()
	{			
		
			$preLoader = new UpsellatorPreLoader();			
			
			//$gifts = new Gift( $preLoader );
			//$gifts->check();
			/*
			/* Get triggerable gifts
			*/		
			$possibleGifts   = $this->preparePossibleTriggerableGifts( $preLoader->gifts_in_cart, $preLoader->backend_gifts );
			/*
			/* Is the cart empty?
			*/			
			if( $this->hasItems() )
			{		
					woo_j_render_view('/partials/wctimeline_items_list',  [ 'products' => $preLoader->cart_products, 
																		    'triggerable_gifts' => $possibleGifts,
																			'upsells'  => new UpsellsBlock( 'modal' ),																																				  
																		   ]);					

			}else{					

				woo_j_render_template('/modal/empty');	
					
			}	
			
	}
	/*
	/* Check if there are triggerable gifts to show blurred
	*/
	private function preparePossibleTriggerableGifts( $gifts_in_cart, $all_gifts )
	{
		$triggerable_gifts 	= [];
		$possibleGifts 		= array_diff( array_keys( $all_gifts ), array_keys( $gifts_in_cart ) );

		if( !count( $possibleGifts ) ) return [];

		foreach( $possibleGifts as $gift_id )
		{
			$gift = $all_gifts[ $gift_id ];

			if( !isset( $gift['banner'] ) || $gift['banner'] != true ) continue;
			$gift['woo_product'] = $this->getProduct( $gift_id );

			$triggerable_gifts[] = $gift;
		}

		return $triggerable_gifts;

	}
	/*
	/* 
	*/
	/*private function getUniqueUpsells( $upsells )
	{
		if( empty( $upsells ) ) return [];

		$read_upsells   = [];
		$unique_upsells = [];
		
		foreach( $upsells as $upsell ): 

				if( !in_array( $upsell['id'], $read_upsells ) )
				{
					$read_upsells[]   = $upsell['id'];
					$unique_upsells[] = $upsell;
				} 

		endforeach;

		return $unique_upsells;

	}*/
}

