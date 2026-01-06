<?php

namespace WcJUpsellator;

use  WcJUpsellator\Options ;
use  WcJUpsellator\Ajax ;
use  WcJUpsellator\Notices\ModalNotices ;
use  WcJUpsellator\Api ;
use  WcJUpsellator\Integrations ;
use  WcJUpsellator\Render ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}

class WcJUpsellatorLoader
{
    private static  $_instance = null ;
    /* Make sure only one instance is loaded 
     */
    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /* 
    /* If const is defined, we load all! 
    */
    public function __construct()
    {
        
        if ( defined( 'WC_J_UPSELLATOR_PLUGIN_DIR' ) ) {
            $this->loadOptions();
            $this->loadAdminHooks();
            $this->loadApi();
            WooActions::register();
            if ( woo_j_conf( 'modal_cart_notices' ) ) {
                ModalNotices::register();
            }
            ( new Integrations\Integrations() )->load();
            $this->loadCSS();
            $this->loadJS();
            /*
            /* After it's all loaded, we finally render the modal cart html
            */
            add_action( 'wp_footer', array( $this, 'DrawRenderables' ) );
            if ( woo_j_conf( 'force_recalculate_totals' ) ) {
                add_action( 'wp_loaded', function () {
                    if ( WC() && WC()->cart ) {
                        WC()->cart->calculate_totals();
                    }
                }, 9999 );
            }
            add_filter( 'woocommerce_get_script_data', array( $this, 'addPageIDtoAjax' ) );
            if ( function_exists( 'pll__' ) ) {
                add_filter( 'woocommerce_get_script_data', array( $this, 'addLangParameterToAjaxCalls' ) );
            }
        }
    
    }
    
    /*
    /* Backoffice hooks and functions
    */
    private function loadAdminHooks()
    {
        if ( !is_admin() ) {
            return;
        }
        Admin\AdminPage::register();
        Admin\AdminWooActions::register();
        Ajax\AjaxGift::registerHooks();
        Ajax\AjaxUpsell::registerHooks();
        Ajax\AjaxConfig::registerHooks();
        Ajax\AjaxIntegrations::registerHooks();
    }
    
    /*
    /* Api classes and routes
    */
    private function loadApi()
    {
        Api\StatsApi::register();
    }
    
    /*
    /* Loads all the plugin options
    /* -- Settings, Upsell, Text
    /* -- using wp_options
    */
    private function loadOptions()
    {
        Options\OptionConfig::init();
        Options\OptionStyles::init();
        Options\OptionUpsell::init();
        Options\OptionShippingBar::init();
        Options\OptionCartPage::init();
        Options\OptionIntegrations::init();
        Options\OptionShopPages::init();
        Options\OptionGift::init();
        Options\OptionExclusion::init();
    }
    
    /*
    /* Loads CSS and JS
    /* CSS and JS styles
    */
    public function loadCSS()
    {
        $cssLoader = new CssStyles();
        $cssLoader->loadFrontend();
        $cssLoader->loadAdmin();
    }
    
    public function loadJS()
    {
        $jsLoader = new JsScripts();
        $jsLoader->loadFrontend();
        $jsLoader->loadAdmin();
    }
    
    /*
    /* Plugin frontend render 
    /* render the cart
    */
    public function DrawRenderables()
    {
        if ( !woo_j_conf( 'only_background' ) ) {
            new Render();
        }
        ( new Render\TotalPlaceholder() )->render();
    }
    
    /*
    /* Add current page ID to ajax request
    */
    public function addPageIDtoAjax( $params )
    {
        if ( isset( $params['wc_ajax_url'] ) ) {
            $params['wc_ajax_url'] .= '&jcart_page_id=' . get_queried_object_id();
        }
        return $params;
    }
    
    /*
    /*Add lang parameters to ajax calls
    */
    public function addLangParameterToAjaxCalls( $params )
    {
        $locale = determine_locale();
        $lang = ( !empty($locale) ? strstr( $locale, '_', true ) : '' );
        if ( isset( $params['wc_ajax_url'] ) && !empty($lang) ) {
            $params['wc_ajax_url'] .= '&lang=' . $lang;
        }
        return $params;
    }

}
WcJUpsellatorLoader::instance();