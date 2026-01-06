<?php

namespace WcJUpsellator\Render;

use WcJUpsellator\Traits\TraitWooCommerceHelper;
  
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class CountButton
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
		woo_j_render_template('/fixed_button', ['count' => $this->getCartCount() ]);
	}

}

