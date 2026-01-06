<?php

namespace WcJUpsellator\Options;

use WcJUpsellator\Traits\TraitWordpress;
  
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class OptionCartPage extends BaseOption
{	
	
	use TraitWordpress;

	const OPTION_NAME 		= 'woo_j_upsellator_cartpage';

	public $cartpage_upsell_type;
	public $cartpage_upsell_max_displayed;
	public $cartpage_upsell_position;
	public $display_on_cartpage;
	
	public $cartpage_upsell_hooks;
	
	public function __construct()
	{				
		$this->cartpage_upsell_hooks =  ["woocommerce_cart_collaterals" => __( 'Next to order total', 'woo_j_cart' ),
										"woocommerce_cart_totals_before_shipping" => __( 'Before shipping methods', 'woo_j_cart' ),
										"woocommerce_cart_coupon" => __( 'After cart coupon', 'woo_j_cart' ),
										"woocommerce_cart_contents" => __( 'Before cart table', 'woo_j_cart' ),
										"woocommerce_proceed_to_checkout" => __( 'Before pay button', 'woo_j_cart' ),			 
										];
								
	}

	public function getCartpageHooks()
	{
		return $this->cartpage_upsell_hooks;
	}

	public function setCartpageUpsellHook( $hook )
	{
			$this->cartpage_upsell_position = "woocommerce_cart_collaterals";
			
			if( array_key_exists ( $hook, $this->cartpage_upsell_hooks ))
			{				
				$this->cartpage_upsell_position = $hook;

			}
	}

	public function preSet()
	{
			$settings = get_option( static::OPTION_NAME );

			if( !empty( $settings ) ): 
				
				foreach( $settings as $key => $value ):
				
						$this->{ $key } =  $value;

				endforeach;

			endif;

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
				
			$this->display_on_cartpage			    = false;
			$this->cartpage_upsell_max_displayed 	= 5;
			$this->cartpage_upsell_type 			= 0;
			$this->cartpage_upsell_position			= 'woocommerce_cart_totals_before_shipping';			
			
			$this->save();		

	}

	public function save()
	{

			$settings 									= [];
			$settings['display_on_cartpage']	  		= $this->display_on_cartpage;
			$settings['cartpage_upsell_type']	 		= $this->cartpage_upsell_type;	
			$settings['cartpage_upsell_position']		= $this->cartpage_upsell_position;
			$settings['cartpage_upsell_max_displayed']	= $this->cartpage_upsell_max_displayed >= 0  ? $this->cartpage_upsell_max_displayed : 1;					
				
			if( get_option( self::OPTION_NAME ) ) update_option( self::OPTION_NAME,  $settings, 'no' );	
			else								  add_option( self::OPTION_NAME,  $settings, '', 'no' );		
			
			$this->load( $settings );
			
			return $settings;

	}

}


