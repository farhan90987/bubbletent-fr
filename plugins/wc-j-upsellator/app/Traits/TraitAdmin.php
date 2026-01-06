<?php

namespace WcJUpsellator\Traits;

trait TraitAdmin
{	
    private $menu_name      = WC_J_UPSELLATOR_ITEM_SLUG;
    private $base_name      = WC_J_UPSELLATOR_PLUGIN_NAME;
    
    private $valid_pages = [ WC_J_UPSELLATOR_ITEM_SLUG,
                            WC_J_UPSELLATOR_ITEM_SLUG.'-styles',
                            WC_J_UPSELLATOR_ITEM_SLUG.'-upsells',
                            WC_J_UPSELLATOR_ITEM_SLUG.'-gifts',                            
                            WC_J_UPSELLATOR_ITEM_SLUG.'-dynamic-bar',
                            WC_J_UPSELLATOR_ITEM_SLUG.'-shop-pages',
                            WC_J_UPSELLATOR_ITEM_SLUG.'-exclusion',
                            WC_J_UPSELLATOR_ITEM_SLUG.'-stats',                          
                            WC_J_UPSELLATOR_ITEM_SLUG.'-shortcodes', 
                            WC_J_UPSELLATOR_ITEM_SLUG.'-integrations',  
                            WC_J_UPSELLATOR_ITEM_SLUG.'-support',                          
                            ];
}