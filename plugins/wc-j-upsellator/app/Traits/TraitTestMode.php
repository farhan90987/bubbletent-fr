<?php

namespace WcJUpsellator\Traits;

trait TraitTestMode
{	
    private function currentUserCan()
    {
        if( !woo_j_conf('test_mode') || ( woo_j_conf('test_mode') && current_user_can('administrator') ) ) return true;

        return false;
    }
}