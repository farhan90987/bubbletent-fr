<?php

namespace WcJUpsellator\Notices;

use WcJUpsellator\Traits\TraitExclusion;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ModalNotices
{

    use TraitExclusion;

	public function __construct()
	{
		   /*
		   /* Nothing to do
		   */ 
	}

	public static function register()
    {
		
			$page = new self(); 
			/*
			/* We register these filters only if we are in an ajax call
			*/
			if( !is_admin() && ( defined('DOING_AJAX') && DOING_AJAX ) && $page->pluginCanRunThere( $_GET['jcart_page_id'] ?? null )  )
			{	                
                add_filter( 'woocommerce_cart_product_not_enough_stock_already_in_cart_message', array( $page, 'throwErrorViaFragments' ), 10, 2  );
                add_filter( 'woocommerce_cart_product_cannot_be_purchased_message', array( $page, 'throwErrorViaFragments' ), 10, 2  );
                add_filter( 'woocommerce_cart_product_out_of_stock_message', array( $page, 'throwErrorViaFragments' ), 10, 2  );
                add_filter( 'woocommerce_cart_product_cannot_add_another_message', array( $page, 'throwErrorViaFragments' ), 10, 2  );
                add_filter( 'woocommerce_cart_product_not_enough_stock_message', array( $page, 'throwErrorViaFragments' ), 10, 2  );
                add_filter( 'woocommerce_add_error', array( $page, 'throwErrorViaFragmentsReduced' ), 10, 1 );
			}
    }
    /*
    /* @hook woocommerce_cart_product_not_enough_stock_already_in_cart_message
    /* @hook woocommerce_cart_product_cannot_be_purchased_message
    /* @hook woocommerce_cart_product_out_of_stock_message
    /* @hook woocommerce_cart_product_cannot_add_another_message
    /* @hook woocommerce_cart_product_not_enough_stock_message
    /*
    /* We prevent the exception by woocommerce and return the message via fragments
    */
    public function throwErrorViaFragments( $message,  $product_data )
    {     

        wp_send_json( ['fragments' => [
                '.wc-timeline-notifications' => '<div class="wc-timeline-notifications" data-type="error">'. preg_replace('#<a.*?>.*?</a>#i', '',  $message ) .'</div>',					
                ]
        ]);        

    } 
    /* 
    /* @hook woocommerce_add_error
    /*
    /* We prevent the exception by woocommerce and return the message via fragments
    */
    public function throwErrorViaFragmentsReduced( $message )
    {     

        wp_send_json( ['fragments' => [
                '.wc-timeline-notifications' => '<div class="wc-timeline-notifications error">'. preg_replace('#<a.*?>.*?</a>#i', '',  $message ) .'</div>',					
                ]
        ]);        

    } 

    private function pluginCanRunThere( $page_id )
    {        

        if( $page_id == wc_get_page_id( 'cart' ) ) return false;
        if( $page_id == wc_get_page_id( 'checkout' ) ) return false;
        
        if( $this->pageExcluded( $page_id ) ) return false;

        return true;
    }
}