<?php

namespace WcJUpsellator\Ajax;

use  WcJUpsellator\Options\OptionGift ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}

class AjaxGift extends BaseAjax
{
    public static function registerHooks()
    {
        $page = new self();
        
        if ( is_admin() ) {
            add_action( 'wp_ajax_wjufc_switch_gift_status', array( $page, 'wjufc_switch_gift_status' ) );
            add_action( 'wp_ajax_wjufc_update_gift', array( $page, 'wjufc_update_gift' ) );
            add_action( 'wp_ajax_wjufc_delete_gift', array( $page, 'wjufc_delete_gift' ) );
        }
    
    }
    
    /*
    /* Delete gift
    */
    public function wjufc_delete_gift()
    {
        $this->validateRequest();
        $gift_id = (int) $_POST['id'];
        ( new OptionGift() )->removeGift( $gift_id, true );
        wp_send_json( [
            'modal'   => true,
            'icon'    => 'success',
            'text'    => __( 'Your gift has been deleted', 'woo_j_cart' ),
            'heading' => __( 'Gift deleted', 'woo_j_cart' ),
            'body'    => [
            'id' => $gift_id,
        ],
        ] );
    }
    
    /*
    /* Update gift
    */
    public function wjufc_update_gift()
    {
        $this->validateRequest();
        if ( !isset( $_POST['id'] ) || !is_numeric( $_POST['id'] ) ) {
            $this->throwErrorModal();
        }
        $p_gift = [];
        $p_gift['id'] = (int) $_POST['id'];
        $p_gift['heading'] = wp_kses( wp_unslash( $_POST['heading'] ), woo_j_string_filter() ) ?? '';
        $p_gift['text'] = wp_kses( wp_unslash( $_POST['text'] ), woo_j_string_filter() ) ?? '';
        $p_gift['shop_label'] = ( isset( $_POST['shop_label'] ) ? sanitize_text_field( $_POST['shop_label'] ) : '' );
        $p_gift['single_product_text'] = ( isset( $_POST['single_product_text'] ) ? wp_kses( wp_unslash( $_POST['single_product_text'] ), woo_j_string_filter() ) : '' );
        $p_gift['cart_limit'] = (int) $_POST['cart_limit'] ?? 0;
        $p_gift['cart_limit_to'] = (int) $_POST['cart_limit_to'] ?? 0;
        $p_gift['exclude_virtual_products'] = ( isset( $_POST['exclude_virtual_products'] ) ? 1 : 0 );
        $p_gift['only_registered'] = ( isset( $_POST['only_registered'] ) ? 1 : 0 );
        $p_gift['product_quantity'] = (int) $_POST['product_quantity'] ?? 1;
        $p_gift['gifted_individually'] = ( isset( $_POST['gifted_individually'] ) ? 1 : 0 );
        $p_gift['gifted_if_not_upsell'] = ( isset( $_POST['gifted_if_not_upsell'] ) ? 1 : 0 );
        $p_gift['banner'] = ( isset( $_POST['banner'] ) ? 1 : 0 );
        $p_gift['banner_text'] = wp_kses( wp_unslash( $_POST['banner_text'] ), woo_j_string_filter() ) ?? '';
        
        if ( $p_gift['cart_limit'] > 0 && $p_gift['cart_limit_to'] > 0 && $p_gift['cart_limit_to'] < $p_gift['cart_limit'] ) {
            $t = $p_gift['cart_limit'];
            $p_gift['cart_limit'] = $p_gift['cart_limit_to'];
            $p_gift['cart_limit_to'] = $t;
        }
        
        $p_gift['type'] = 'cart-limit';
        $p_gift['products'] = [];
        $p_gift['categories'] = [];
        $p_gift['attributes'] = [];
        $p_gift['once_per_order'] = 1;
        $p_gift['coupon'] = '';
        $p_gift['product_quantity'] = 1;
        $p_gift['quantity'] = (int) $_POST['quantity'];
        $giftOptions = new OptionGift();
        
        if ( $giftOptions->editOrAdd( $p_gift ) ) {
            $giftOptions->save();
            wp_send_json( [
                'modal'   => true,
                'icon'    => 'success',
                'text'    => __( 'Your gift has been updated', 'woo_j_cart' ),
                'heading' => __( 'Gift updated', 'woo_j_cart' ),
            ] );
        }
        
        $this->throwErrorModal();
    }
    
    /*
    /* Switch gift status
    */
    public function wjufc_switch_gift_status()
    {
        $this->validateRequest();
        $status = ( isset( $_POST['status'] ) && $_POST['status'] == 1 ? 1 : 0 );
        $gift_id = (int) $_POST['id'];
        /*
        /* Pre-load properties
        */
        ( new OptionGift() )->changeStatus( $gift_id, $status );
        wp_send_json( true );
    }

}