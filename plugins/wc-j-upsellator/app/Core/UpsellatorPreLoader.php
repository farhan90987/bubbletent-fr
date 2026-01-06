<?php

namespace WcJUpsellator\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class UpsellatorPreLoader
{	

	public $gifts_in_cart 		   		= [];
	public $upsells_in_cart 	   		= [];
	public $cart_products		   		= [];
	public $card_standard_products_ids 	= [];
	public $backend_gifts;
	public $backend_upsells;
	public $products_qty_on_cart		= [];

	public $clean_total			   = 0;

	public function __construct()
	{

			$this->backend_upsells 	= array_filter( woo_j_upsell('products') ?? [], [ $this, 'getOnlyActive' ] );
			$this->backend_gifts 	= array_filter( woo_j_gift('products') ?? [], [ $this, 'getOnlyActive' ] );
			/*
			/* Load all the current products in cart
			/* and save all products_ids, categories_ids and attributes_ids
			*/
			$this->loadCartProducts();		
		
	}	
	/*
	/* Load all the cart products
	/* and trasform them to upsellator products
	*/
	private function loadCartProducts()
	{			
			$cartProducts   = WC()->cart ? WC()->cart->get_cart() : null;

			if( !$cartProducts ) return; 

			$products 		= [];
			$cart_products  = WC()->cart->get_cart();

			foreach( $cart_products as $product ): 
				
				$product_created =  new UpsellatorProduct( $product );
				/*
				/* Is the product a valid one?
				*/
				if( $product_created->isValid() ){

					$products[ $product_created->key ] = $product_created;
					$this->evaluateProduct( $product_created );				

				} 

			endforeach;
			/*
			/* Let's put gift at the end, for bundles better compatibility
			*/
			usort( $products, function( $a, $b ) { return strcmp( $a->isGift() , $b->isGift()  ); } );

			$this->cart_products = $products;	
	}
	/*
	/* We evaluate the product to see if it's a gift or upsell
	*/
	private function evaluateProduct( $prod )
	{
			if( $prod->isGift() )
			{	
					if( array_key_exists ( $prod->id, $this->backend_gifts ))
					{			
							$this->gifts_in_cart[ $prod->id ] = [ 'key' => $prod->key, 
																  'qty' => $prod->quantity_on_cart,
																  'new_qty' => 0 ];

					}else{
							/*
							/* If the gift is in the customer cart but
							/* admin deleted it from WCJ/Gifts, we remove it from cart!
							*/
							WC()->cart->remove_cart_item( $prod->key );
							$this->removeFromList( $prod->key );		
					} 												     			
			}
			
			elseif( $prod->isUpsell() )
			{	
					if( array_key_exists ( $prod->id, $this->backend_upsells ))
					{			
							$checked = isset( $this->backend_upsells[ $prod->id ]['keep_in_cart'] ) && $this->backend_upsells[ $prod->id ]['keep_in_cart'] == 1 ? true : false;

							$this->upsells_in_cart[ $prod->id ] = [ 
																	'key' => $prod->key, 
																	'checked' => $checked  
																  ];								

					}else{
							/*
							/* If the upsell is in the customer cart but
							/* admin deleted it from WCJ/Upsells, we remove it from cart!
							*/
							WC()->cart->remove_cart_item( $prod->key );
							$this->removeFromList( $prod->key );		
					} 												     			
			}else{

					/*
					/* Total without upsells
					*/
					$this->clean_total += ( $prod->actual_price * $prod->quantity_on_cart );
					$this->card_standard_products_ids[] = $prod->id;
			}
			/*
			/* Update the qty of the product, to check if we can gift/upsell it
			*/
			isset( $this->products_qty_on_cart[ $prod->id ] ) 
					? $this->products_qty_on_cart[ $prod->id ]['qty'] += $prod->quantity_on_cart
					: $this->products_qty_on_cart[ $prod->id ]['qty'] = $prod->quantity_on_cart;
	}
	
	public function removeFromList( $key )
	{
			if( isset( $this->cart_products[ $key ]) )
			{			
				unset( $this->cart_products[ $key ] );
			}
	}

	public function getOnlyActive( $item )
	{
		return  !isset( $item['active'] ) || $item['active'] == true;		
	}
}