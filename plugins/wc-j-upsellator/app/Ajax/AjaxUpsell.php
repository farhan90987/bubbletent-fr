<?php

namespace WcJUpsellator\Ajax;

use  WcJUpsellator\Options\OptionUpsell ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}

class AjaxUpsell extends BaseAjax
{
    public static function registerHooks()
    {
        $page = new self();
        
        if ( is_admin() ) {
            add_action( 'wp_ajax_wjufc_switch_upsell_status', array( $page, 'wjufc_switch_upsell_status' ) );
            add_action( 'wp_ajax_wjufc_update_upsell', array( $page, 'wjufc_update_upsell' ) );
            add_action( 'wp_ajax_wjufc_delete_upsell', array( $page, 'wjufc_delete_upsell' ) );
            add_action( 'wp_ajax_wjufc_reorder_upsells', array( $page, 'wjufc_reorder_upsells' ) );
        }
    
    }
    
    /*
    /* Switch upsell status
    */
    public function wjufc_switch_upsell_status()
    {
        $this->validateRequest();
        $status = ( isset( $_POST['status'] ) && $_POST['status'] == 1 ? 1 : 0 );
        $upsell_id = (int) $_POST['id'];
        /*
        /* Pre-load properties
        */
        ( new OptionUpsell() )->changeStatus( $upsell_id, $status );
        wp_send_json( true );
    }
    
    /*
    /* Delete upsell
    */
    public function wjufc_delete_upsell()
    {
        $this->validateRequest();
        $upsell_id = (int) $_POST['id'];
        ( new OptionUpsell() )->removeUpsell( $upsell_id, true );
        wp_send_json( [
            'modal'   => true,
            'icon'    => 'success',
            'text'    => __( 'Your upsell has been deleted', 'woo_j_cart' ),
            'heading' => __( 'Upsell deleted', 'woo_j_cart' ),
            'body'    => [
            'id' => $upsell_id,
        ],
        ] );
    }
    
    /*
    /* Update upsell
    */
    public function wjufc_update_upsell()
    {
        $this->validateRequest();
        if ( !isset( $_POST['id'] ) || !is_numeric( $_POST['id'] ) ) {
            $this->throwErrorModal();
        }
        $upsell = [];
        $upsell['id'] = (int) $_POST['id'];
        $upsell['heading'] = wp_kses( wp_unslash( $_POST['heading'] ), woo_j_string_filter() ) ?? '';
        $upsell['text'] = wp_kses( wp_unslash( $_POST['text'] ), woo_j_string_filter() ) ?? '';
        $upsell['discount_type'] = (int) $_POST['discount_type'];
        $upsell['discount'] = (double) $_POST['discount'];
        $upsell['cart_limit'] = (int) $_POST['cart_limit'] ?? 0;
        $upsell['only_registered'] = ( isset( $_POST['only_registered'] ) ? 1 : 0 );
        $upsell['sold_individually'] = ( isset( $_POST['sold_individually'] ) ? 1 : 0 );
        $upsell['quantity_change'] = ( isset( $_POST['quantity_change'] ) ? 1 : 0 );
        $upsell['quantity_change_max'] = ( isset( $_POST['quantity_change_max'] ) ? (int) $_POST['quantity_change_max'] : 0 );
        $upsell['hide_if_gifted'] = ( isset( $_POST['hide_if_gifted'] ) ? 1 : 0 );
        $upsell['hide_if_gifted_parent'] = ( isset( $_POST['hide_if_gifted_parent'] ) ? 1 : 0 );
        $upsell['button_action'] = sanitize_text_field( $_POST['button_action'] );
        $upsell['keep_in_cart'] = ( isset( $_POST['keep_in_cart'] ) ? 1 : 0 );
        $upsell['type'] = 'cart-limit';
        $upsell['products'] = [];
        $upsell['attributes'] = [];
        $upsell['categories'] = [];
        $upsell['condition-reversed'] = 0;
        $upsell['condition-reversed'] = ( $upsell['type'] == 'cart-limit' ? 0 : $upsell['condition-reversed'] );
        $upsell['quantity'] = (int) $_POST['quantity'];
        $ups = new OptionUpsell();
        
        if ( $ups->editOrAdd( $upsell ) ) {
            $ups->save();
            wp_send_json( [
                'modal'   => true,
                'icon'    => 'success',
                'text'    => __( 'Your upsell has been updated', 'woo_j_cart' ),
                'heading' => __( 'Upsell updated', 'woo_j_cart' ),
            ] );
        }
        
        $this->throwErrorModal();
    }
    
    /*
    /* Reorder Upsells
    */
    public function wjufc_reorder_upsells()
    {
        $this->validateRequest();
        $upsell_ids = explode( ',', $_POST['order'] );
        
        if ( !empty($upsell_ids) ) {
            $upsell_ids = array_filter( $upsell_ids, 'ctype_digit' );
            ( new OptionUpsell() )->reorderUpsells( $upsell_ids );
        }
        
        wp_send_json( [
            'modal'   => true,
            'icon'    => 'success',
            'text'    => __( 'Your upsells have been reordered', 'woo_j_cart' ),
            'heading' => __( 'Upsells reordered', 'woo_j_cart' ),
        ] );
    }

}