<?php

namespace WcJUpsellator\Integrations\Plugins;
use WcJUpsellator\Integrations\Plugins\BasePlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WpRocketIntegration extends BasePlugin
{  
    public static function load()
    {
        add_filter( 'rocket_cache_wc_empty_cart', '__return_false' );      
    }  
}