<?php

namespace WcJUpsellator\Render;
use WcJUpsellator\Traits\TraitWooCommerceHelper;  

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ModalFooter
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
		
		woo_j_render_template('/modal/footer', ['items' => $this->getCartCount() ,												
												'coupons' => woo_j_conf('coupon_code') ? $this->getAppliedCoupons() : [],
												'shipping' => woo_j_conf('shipping_total') ? $this->getShipping() : '',
												'total' => $this->getSubtotal(),
												'discount' => $this->getDiscountTotal() ]);
	}

}



