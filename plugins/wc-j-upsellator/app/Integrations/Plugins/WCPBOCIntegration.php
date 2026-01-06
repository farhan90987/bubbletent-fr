<?php

namespace WcJUpsellator\Integrations\Plugins;
use WcJUpsellator\Integrations\Plugins\BasePlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WCPBOCIntegration extends BasePlugin
{      

    public static function load()
    {
        $page = new self();	

        if( !function_exists('wcpbc_the_zone') ) return;

        add_filter( 'wjufw_shipping_bar_limit', array( $page, 'changeLimit' ) );
        add_filter( 'wjufw_product_cart_limit', array( $page, 'changeLimit' ) );
        add_filter( 'wjufw_upsell_displayed_price', array( $page, 'changeLimit' ) );
        add_filter( 'wjufw_upsell_set_price', array( $page, 'changeLimit' ) );
       
    }

    public function changeLimit( $limit )
    {
        $multiplier = wcpbc_the_zone() ? wcpbc_the_zone()->get_exchange_rate() : 1 ;	
        
        return $limit * $multiplier;
    }  


}