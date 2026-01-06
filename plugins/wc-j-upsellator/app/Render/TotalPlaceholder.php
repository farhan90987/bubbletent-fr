<?php

namespace WcJUpsellator\Render;

use WcJUpsellator\Traits\TraitWooCommerceHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class TotalPlaceholder
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
			/*
			/* Print placeholder for total
			*/	
			if( !is_admin()  )
			{	
				woo_j_render_view('/wctimeline_total_placeholder', ['total' => $this->getSubtotal() ]);
			}	
			
	}	
}

