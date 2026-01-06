<?php

namespace WcJUpsellator\Integrations;

use WcJUpsellator\Integrations\Plugins;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Integrations
{   

	public function __construct()
	{
        /*
        /* Nothing to do
        */ 
	}

    public function load()
    {
		
        $active_integrations = woo_j_integrations('integrations') ?? [];
       
        if( empty( $active_integrations ) ) return;
        
        if( is_admin() ) return;
        
        if( in_array( 'wprocket', $active_integrations ) ) Plugins\WpRocketIntegration::load();
        if( in_array( 'wcpboc', $active_integrations ) ) Plugins\WCPBOCIntegration::load();
        if( in_array( 'woocs', $active_integrations ) ) Plugins\WOOCSIntegration::load();
        if( in_array( 'wmc_curcy', $active_integrations ) ) Plugins\WMCCurcyIntegration::load();
        
    }

}