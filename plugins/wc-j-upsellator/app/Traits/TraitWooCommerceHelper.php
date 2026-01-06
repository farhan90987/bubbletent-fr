<?php

namespace WcJUpsellator\Traits;

trait TraitWooCommerceHelper
{	
	
	public function getCartCount()
	{			
		return WC()->cart->get_cart_contents_count();		
	}  

	public function getSubtotal()
	{	
		return !WC()->cart->display_prices_including_tax() || woo_j_conf('subtotal_vat_excluded') 
						 ? WC()->cart->cart_contents_total 
						 : WC()->cart->cart_contents_total + WC()->cart->tax_total;
	}

	public function getCartSubtotal( $exclude_virtual_product = false )
	{
		if( !$exclude_virtual_product ) return $this->getSubtotal();

		$total = 0;

		foreach( WC()->cart->get_cart() as $cart_item ): 

			if( ! $cart_item['data']->is_virtual( ) )
			{
				$total += $cart_item['line_total'];
			}
				
		endforeach;
		
		return $total;
	}
	/*
	/* Get discount total and total taxes
	*/
	public function getDiscountTotal()
	{		
		$total = WC()->cart->get_cart_discount_total();
		$taxes = WC()->cart->get_cart_discount_tax_total();
		
		return round( ($total + $taxes), 2 );
	}

	public function hasItems()
	{
		return ( WC()->cart->get_cart_contents_count() > 0 ) ? true : false ;
	}

	public function getWCCartProducts()
	{
		return WC()->cart->get_cart();
	}

	public function getProduct( $id_product )
	{		
		return wc_get_product( $id_product );
	}
		
	public function getShipping()
	{
	
		$shipping = [
			'price' => 9999,
			'label' => __('standard shipping')
		];

    	$packages_keys 	= (array) array_keys( WC()->cart->get_shipping_packages() );
		
		foreach( $packages_keys as $key )
		{
			$shipping_rates = WC()->session->get('shipping_for_package_'.$key)['rates'] ?? [];
			if( empty( $shipping_rates ) ) return false;

			foreach( $shipping_rates as $rate_key => $rate )
			{				
				if( $rate->cost < $shipping['price'] )
				{
					$shipping['price'] = $rate->cost;
					$shipping['label'] = $rate->label;
					/*
					/* If shipping method has taxes and they are meant to be displayed, let's add them
					*/
					if( !empty( $rate->taxes ) && WC()->cart->display_prices_including_tax() )
					{
						foreach( $rate->taxes as $shipping_tax )
						{
							$shipping['price'] += $shipping_tax;
						}
					}
				} 
			}
		}

		return $shipping;
	}

	public function getAppliedCoupons()
	{
		$coupons_codes = WC()->cart->get_applied_coupons();

		if( empty( $coupons_codes ) ) return [];

		$coupons 		 = [];
		$currency_symbol = get_woocommerce_currency_symbol();

		foreach( $coupons_codes as $coupon )
		{
			$coup 			= new \WC_Coupon( $coupon );
			$coupon_data 	= $coup->get_data();

			$listed_coupon['code'] = $coupon;
			$listed_coupon['type'] = ( 'percent' === $coupon_data['discount_type'] ? $coupon_data['amount'] . '%' : $currency_symbol . $coupon_data['amount'] );
			$coupons[] 			   = $listed_coupon;
		}

		return $coupons;
	}
}