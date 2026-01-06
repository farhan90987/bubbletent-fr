<?php

namespace WcJUpsellator\Render;

use WcJUpsellator\Core\UpsellatorPreLoader;
use WcJUpsellator\Traits\TraitWooCommerceHelper;
use WcJUpsellator\Upsell;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class UpsellsBlock
{	

	use TraitWooCommerceHelper;
	public $mode;
	private $default_modes = ['cart', 'checkout', 'modal'];

	public function __construct( )
	{		
			
	}	

	public function render( $mode, $extra = null )
	{	
			
			$preLoader = new UpsellatorPreLoader();					
			
			$upsell = new Upsell( $preLoader );			
			$upsell->check();	

			$type  = '';
			$class = $extra ?? $mode;

			if( $mode == 'modal' )		$type = woo_j_conf('modal_upsell_type');
			if( $mode == 'cart' ) 		$type = woo_j_cartpage('cartpage_upsell_type');
			if( $mode == 'checkout' ) 	$type = woo_j_checkout('checkout_upsell_type');
			
			$valid_upsells = $this->getValidUpsells( $upsell->valid_upsells, $mode, $type );
			
			if( !$extra )
			{
				if( $this->canBeLogged( $mode ) ) do_action('wjufw_after_triggerable_upsells_loaded', $valid_upsells, $mode );	
			}					

			?>
			
			<div class="wc-timeline-<?php echo $class ?>-upsell"> 

			<?php
			
			woo_j_render_view('/partials/wctimeline_upsell_item', [ 'upsell_items' => $valid_upsells,
																	'type' => $type,																	
																	'mode' => $mode                
																	]);

			?></div><?php				
			
	}

	private function getValidUpsells( $upsells_array, $display_area, $type )
	{		

		if( empty( $upsells_array ) ) return [];
		/*
		/* If carousel, we get all triggerable upsells
		*/
		if( $type == 2 ) return $upsells_array;
		/*
		/* If single, we get the first one
		*/
		if( $type == 1 ) return [current( $upsells_array )];
		
		switch( $display_area )
		{
			case 'cart':

				$max = woo_j_cartpage('cartpage_upsell_max_displayed');
				break;

			case 'checkout':

				$max = woo_j_checkout('checkout_upsell_max_displayed');
				break;

			case 'modal':

				$max = woo_j_conf('modal_upsell_max_displayed');
				break;

			default:

				$max = 3;
				break;
		}	
		
		if( $max == 0 ) return [];

		$upsells = array_slice( $upsells_array, 0, $max );
		
		return $upsells;
			
	}
	/*
	/* Check if upsell can be logged
	*/
	private function canBeLogged( $mode )
	{
		/*
		/* If it's not a modal upsell, we just log it
		*/
		if( $mode != 'modal' ) return true;
		/*
		/* We check if it's a modal upsell but we are on cart page
		*/
		$current_page        = isset( $_REQUEST['jcart_page_id'] ) ? (int)$_REQUEST['jcart_page_id'] : 0; 
        $cart_page_id        = wc_get_page_id('cart');

		return $current_page != $cart_page_id;
	}
	
}

