<?php

if ( !function_exists( 'wju_fs' ) ) {
    // Create a helper function for easy SDK access.
    function wju_fs()
    {
        global  $wju_fs ;
        
        if ( !isset( $wju_fs ) ) {
            // Include Freemius SDK.
            require_once WC_J_UPSELLATOR_PLUGIN_DIR . '/freemius/start.php';
            $wju_fs = fs_dynamic_init( array(
                'id'             => '7494',
                'slug'           => 'wc-j-upsellator',
                'premium_slug'   => 'wc-j-upsellator-premium',
                'type'           => 'plugin',
                'public_key'     => 'pk_2024432b8d60f06d4fc45be8f9e86',
                'is_premium'     => false,
                'premium_suffix' => 'Pro',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                'days'               => 7,
                'is_require_payment' => false,
            ),
                'menu'           => array(
                'slug'    => 'wc-j-upsellator',
                'contact' => false,
                'support' => false,
            ),
                'is_live'        => true,
            ) );
        }
        
        return $wju_fs;
    }
    
    // Init Freemius.
    wju_fs();
    // Signal that SDK was initiated.
    do_action( 'wju_fs_loaded' );
}
