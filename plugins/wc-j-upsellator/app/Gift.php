<?php

namespace WcJUpsellator;

use  WcJUpsellator\Core\UpsellatorPreLoader ;
use  WcJUpsellator\Traits\TraitWooCommerceHelper ;
use  WcJUpsellator\Traits\TraitUpsellatorCartFilter ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}

class Gift
{
    /*
    /* Gift class		 
    /* Step 1 - We loop trough current items in cart and we check every product if it mets a gift condition
    /* - if yes, depending if it's multiple or not, we adjust the value in the #2 array in the new_qty parameters
    /* Step 2 - We check all the #2 array: if qty != new_qty, we adjust the cart
    */
    use  TraitWooCommerceHelper ;
    use  TraitUpsellatorCartFilter ;
    private  $backend_gifts = array() ;
    private  $gifts = array() ;
    private  $cart_products ;
    private  $products_qty_on_cart = array() ;
    private  $standard_products_in_cart_ids = array() ;
    private  $in_cart_upsells = array() ;
    private  $rounds = 0 ;
    private  $unlocked_gifts = array() ;
    public function __construct( UpsellatorPreLoader $loadedProducts )
    {
        $this->backend_gifts = $loadedProducts->backend_gifts;
        $this->gifts = $loadedProducts->gifts_in_cart;
        $this->cart_products = $loadedProducts->cart_products;
        $this->standard_products_in_cart_ids = $loadedProducts->card_standard_products_ids;
        $this->in_cart_upsells = $loadedProducts->upsells_in_cart;
        $this->products_qty_on_cart = $loadedProducts->products_qty_on_cart;
    }
    
    public function check()
    {
        /*
        /* Do we have items in cart and gifts set by the admin?
        */
        if ( !empty($this->backend_gifts) && !empty($this->cart_products) && $this->getSubtotal() > 0 ) {
            foreach ( $this->backend_gifts as $backend_gift ) {
                $current_subtotal = $this->getCartSubtotal( $backend_gift['exclude_virtual_products'] ?? false );
                /*
                /* Guard classes
                */
                if ( $this->activeWall( $backend_gift ) ) {
                    continue;
                }
                if ( $this->loggedInWall( $backend_gift ) ) {
                    continue;
                }
                if ( !$this->subtotalMinCheck( $backend_gift, $current_subtotal ) ) {
                    continue;
                }
                if ( !$this->subtotalMaxCheck( $backend_gift, $current_subtotal ) ) {
                    continue;
                }
                $this->checkIfTriggersGift( $backend_gift );
            }
        }
        /*
        /* Now that we have all loaded, we can update the cart				
        */
        $this->updateChanges();
    }
    
    /*
    /* STEP 1 
    /* We loop trough all cart items and backend gifts 
    /* checking if requirements are met. If so, we give the valid gift to
    /* the function @updateList
    */
    private function checkIfTriggersGift( $backend_gift )
    {
        foreach ( $this->cart_products as $product ) {
            /*
            /* Guard classes
            */
            if ( isset( $backend_gift['gifted_individually'] ) && $backend_gift['gifted_individually'] == true && in_array( $backend_gift['id'], $this->standard_products_in_cart_ids ) ) {
                continue;
            }
            if ( isset( $backend_gift['gifted_if_not_upsell'] ) && $backend_gift['gifted_if_not_upsell'] == true && array_key_exists( $backend_gift['id'], $this->in_cart_upsells ) ) {
                continue;
            }
            $valid_product = false;
            $valid_product = ( $backend_gift['type'] == 'cart-limit' ? 1 : false );
            if ( $valid_product ) {
                $this->updateList( $backend_gift, $valid_product );
            }
        }
    }
    
    /* 		
    /* Check if the selected backend-gift is already in the cart ( as gift ).
    /* If true, we just adjust the new quantity, else
    /* we create a new gift with the new quantity
    */
    private function updateList( $backend_gift, $gift_quantity_on_cart )
    {
        $product = $this->getProduct( $backend_gift['id'] );
        /*
        /* Check if product is purchasable
        */
        if ( !$product->is_in_stock() ) {
            return;
        }
        $multiplier = ( isset( $backend_gift['quantity'] ) && !empty($backend_gift['quantity']) ? (int) $backend_gift['quantity'] : 1 );
        $qty_on_cart = ( isset( $this->products_qty_on_cart[$backend_gift['id']] ) ? $this->products_qty_on_cart[$backend_gift['id']]['qty'] : 0 );
        $quantity = $multiplier;
        /*
        /* Let's get the product to check stock qty
        */
        /*
        /* Is this backend-gift already in the customer cart as gift?			   
        */
        
        if ( array_key_exists( $backend_gift['id'], $this->gifts ) ) {
            $quantity_to_set = ( !empty($backend_gift['once_per_order']) ? $quantity : $quantity + $this->gifts[$backend_gift['id']]['new_qty'] );
            $quantity_on_cart_without_gifts = $qty_on_cart - $quantity;
            $max_quantity_for_gifts = $product->get_stock_quantity() - $quantity_on_cart_without_gifts;
            /*
            /* If stock quantity is lower than desired quantity, we level them
            */
            if ( $product->managing_stock() && $max_quantity_for_gifts < $quantity_to_set ) {
                $quantity_to_set = 0;
            }
            $this->gifts[$backend_gift['id']]['new_qty'] = $quantity_to_set;
            return;
        }
        
        if ( $product->managing_stock() && $product->get_stock_quantity() - $qty_on_cart < $quantity ) {
            return;
        }
        /*
        /* If it's not, we add it				
        */
        $this->gifts[$backend_gift['id']] = [
            'key'     => '',
            'qty'     => 0,
            'new_qty' => $quantity,
        ];
        return;
    }
    
    /* 
    /* STEP 2
    /* Now that we have all gift loaded, we check them
    /* If there's no cart-key, we need to add that to the cart
    /* else, if the new qty is different from the previous, we update it
    */
    private function updateChanges()
    {
        foreach ( $this->gifts as $id => $value ) {
            
            if ( empty($value['key']) ) {
                $this->add( $id, $value['new_qty'] );
            } else {
                /*
                /* Has quantity changed?
                */
                if ( $value['qty'] != $value['new_qty'] ) {
                    WC()->cart->set_quantity( $value['key'], $value['new_qty'], true );
                }
            }
        
        }
    }
    
    /*
    /* We add the gift to the cart
    /* since it's a new one
    */
    private function add( $product_id, $quantity = 1 )
    {
        $product = $this->getProduct( $product_id );
        /*
        /* Is this product active?
        */
        if ( $product ) {
            /*
            /* Is this gift in stock?
            */
            
            if ( !$product->managing_stock() || $product->get_stock_quantity() >= $quantity ) {
                WC()->cart->add_to_cart(
                    $product_id,
                    $quantity,
                    0,
                    [],
                    [
                    'gift'         => true,
                    'custom_price' => 0,
                ]
                );
            } else {
                /*
                /* Gift not in stock
                */
            }
        
        }
    }

}