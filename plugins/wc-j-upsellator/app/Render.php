<?php

namespace WcJUpsellator;

use WcJUpsellator\Render as Renders;
use WcJUpsellator\Traits\TraitExclusion;
use WcJUpsellator\Traits\TraitTestMode;
use WcJUpsellator\Traits\TraitWooCommerceHelper; 

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/*
/* Class to display the modal cart -> frontend only 
*/
class Render
{

	use TraitExclusion;
	use TraitTestMode;
	use TraitWooCommerceHelper;	

	public function __construct()
	{
		
			$this->startRendering();
	}
	
	public function startRendering()
    {	
		
		/*
		/*	Test mode?
		*/	
		
		if( $this->currentUserCan() )
		{	
			/*
			/* Can we render the modal cart?
			*/
			if( !is_admin() && !$this->pageExcluded() && !is_cart() && !is_checkout() )
			{											
				
					woo_j_render_view('/wctimeline_cart', ['count_button' => new Renders\CountButton(),
														   'footer' => new Renders\ModalFooter(),
														   'items_list' => new Renders\ItemsList(),
														   'logo' => $this->evaluateLogo(),	
														   'dynamic_bar_visible' => $this->hasDynamicBar()										 										
													  	  ]);
					
			}   
			
		}			
		
	}
	/*
	/* Check if dynamic bar is visible
	*/
	private function hasDynamicBar()
	{
		
		if( !woo_j_shipping('shipping_timeline') ) return false;
		if( !woo_j_shipping('goals') ) return false;

		return apply_filters( 'wjufw_dynamic_bar_display', true );
	}

	private function evaluateLogo()
	{

		if( woo_j_conf('modal_theme') == 'logo' ): 
			
			$images         = wp_get_attachment_image_src( woo_j_conf('logo_attachment_id'), 'medium');	
			
			return $images[0];

		endif;

		return null;

	}
	

}

