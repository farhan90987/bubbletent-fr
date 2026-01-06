<?php

namespace WcJUpsellator\Admin;

use  WcJUpsellator\Traits\TraitAdmin ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}

class AdminPage
{
    use  TraitAdmin ;
    private  $sub_menus ;
    public function __construct()
    {
        //Nothing to do
    }
    
    public static function register()
    {
        $page = new self();
        
        if ( is_admin() ) {
            $page->loadSubMenus();
            add_action( 'admin_menu', array( $page, 'admin_menu' ), 99 );
            add_filter( 'woocommerce_order_item_display_meta_key', array( $page, 'replace_item_meta_key' ), 99 );
        }
    
    }
    
    private function loadSubMenus()
    {
        $this->sub_menus = array(
            array( $this->menu_name . '-styles', __( 'Style and colors', 'woo_j_cart' ) ),
            array( $this->menu_name . '-upsells', __( 'Upsells', 'woo_j_cart' ) ),
            array( $this->menu_name . '-gifts', __( 'Gifts', 'woo_j_cart' ) ),
            array( $this->menu_name . '-dynamic-bar', __( 'Dynamic bar', 'woo_j_cart' ) ),
            array( $this->menu_name . '-shop-pages', __( 'Shop pages', 'woo_j_cart' ) ),
            array( $this->menu_name . '-exclusion', __( 'Excluded pages', 'woo_j_cart' ) ),
            array( $this->menu_name . '-stats', __( 'Statistics', 'woo_j_cart' ) ),
            array( $this->menu_name . '-shortcodes', __( 'Shortcodes', 'woo_j_cart' ) ),
            array( $this->menu_name . '-integrations', __( 'Integrations', 'woo_j_cart' ) )
        );
    }
    
    public function admin_menu()
    {
        add_menu_page(
            __( 'J Cart Upsell', 'woo_j_cart' ),
            __( $this->base_name, 'woo_j_cart' ),
            'manage_options',
            $this->menu_name,
            array( $this, 'adminPageDispatcher' ),
            'dashicons-schedule',
            99
        );
        foreach ( $this->sub_menus as $key => $value ) {
            add_submenu_page(
                $this->menu_name,
                $value[1],
                $value[1],
                'manage_woocommerce',
                $value[0],
                array( $this, 'adminPageDispatcher' )
            );
        }
    }
    
    /*
    /* Replace in the order admin recap the meta key
    /* because we don't want to see the whole "woo_j_upsellator_upsell" key
    */
    public function replace_item_meta_key( $display_key )
    {
        if ( '_woo_j_upsellator_upsell' === $display_key || '_woo_j_upsellator_gift' === $display_key ) {
            $display_key = __( 'type', 'woo_j_cart' );
        }
        if ( '_woo_j_upsellator_upsell_type' === $display_key ) {
            $display_key = __( 'upsell type', 'woo_j_cart' );
        }
        return $display_key;
    }
    
    public function adminPageDispatcher( $page = '', $action = 'index' )
    {
        $page = sanitize_text_field( $_GET['page'] );
        if ( strpos( $page, $this->menu_name ) !== false ) {
            
            if ( in_array( $page, $this->valid_pages ) ) {
                $page = str_replace( $this->menu_name, '', $page );
                $page = ltrim( $page, '-' );
                if ( $page == '' ) {
                    $page = 'main';
                }
                include WC_J_UPSELLATOR_PLUGIN_DIR . '/views/admin/pages/' . $page . '.page.php';
            }
        
        }
    }

}