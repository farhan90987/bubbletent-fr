<?php

namespace WcJUpsellator;

use  WcJUpsellator\Core\UpsellatorPreLoader ;
use  WcJUpsellator\Traits\TraitWooCommerceHelper ;
use  WcJUpsellator\Traits\TraitUpsellatorCartFilter ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}

class Upsell
{
    use  TraitWooCommerceHelper ;
    use  TraitUpsellatorCartFilter ;
    public  $valid_upsells = array() ;
    private  $backend_upsells = array() ;
    private  $in_cart_upsells = array() ;
    private  $standard_products_in_cart_ids = array() ;
    private  $cart_clean_total ;
    private  $in_cart_gifts = array() ;
    private  $products_qty_on_cart = array() ;
    private  $gifts_in_cart_parent_ids = array() ;
    private  $cart_products = array() ;
    public function __construct( UpsellatorPreLoader $loadedProducts )
    {
        $this->backend_upsells = woo_j_upsell( 'products' );
        $this->in_cart_upsells = $loadedProducts->upsells_in_cart;
        $this->cart_products = $loadedProducts->cart_products;
        $this->cart_clean_total = $loadedProducts->clean_total;
        $this->standard_products_in_cart_ids = $loadedProducts->card_standard_products_ids;
        $this->in_cart_gifts = $loadedProducts->gifts_in_cart;
        $this->gifts_in_cart_parent_ids = $loadedProducts->gifts_in_cart_parent_ids ?? [];
        $this->products_qty_on_cart = $loadedProducts->products_qty_on_cart;
    }
    
    public function check()
    {
        /* 
        /* Do we have items in cart and upsells set by the admin?
        */
        if ( !empty($this->backend_upsells) && !empty($this->cart_products) ) {
            foreach ( $this->backend_upsells as $upsell ) {
                /*
                /* Guard classes
                */
                if ( $this->activeWall( $upsell ) ) {
                    continue;
                }
                if ( $this->loggedInWall( $upsell ) ) {
                    continue;
                }
                if ( !$this->subtotalMinCheck( $upsell, $this->cart_clean_total ) ) {
                    continue;
                }
                $this->checkIfTriggersUpsell( $upsell );
            }
        }
        /*
        /* Shuffle upsells order if "random order" is set
        /* Random order in checkout may be bugged, so we skip shuffle if we are on checkout
        */
        if ( !is_checkout() && woo_j_conf( 'upsells_random_order' ) == 1 ) {
            shuffle( $this->valid_upsells );
        }
        /*
        /* Now that all is set, we do changes
        */
        $this->updateChanges();
    }
    
    /*
    /* STEP 1 
    /* We loop trough all cart items and backend upsells 
    /* checking if requirements are met. If so, we give the valid upsell to
    /* the function @updateList
    */
    private function checkIfTriggersUpsell( $upsell )
    {
        $passed = 0;
        $condition_reversed = isset( $upsell['condition-reversed'] ) && $upsell['condition-reversed'] == 1;
        foreach ( $this->cart_products as $product ) {
            /*
            /* Guard classes
            */
            if ( isset( $upsell['sold_individually'] ) && $upsell['sold_individually'] == true && in_array( $upsell['id'], $this->standard_products_in_cart_ids ) ) {
                continue;
            }
            $test_passed = false;
            $test_passed = ( $upsell['type'] == 'cart-limit' ? true : false );
            $passed += $test_passed;
        }
        if ( $condition_reversed && !$passed ) {
            $this->updateList( $upsell );
        }
        if ( !$condition_reversed && $passed ) {
            $this->updateList( $upsell );
        }
    }
    
    /*
    /* This upsell can be triggered. Let's see
    /* if we have already listed it and it's purchasable
    */
    private function updateList( $item )
    {
        $item['woo_product'] = $this->getProduct( $item['id'] );
        $qty = $item['quantity'] ?? 1;
        /*
        /* Is this upsell item purchasable?
        */
        if ( $this->canBeSold( $item['woo_product'] ) ) {
            /*
            /* Is this upsell already accepted by the customer?
            */
            
            if ( !array_key_exists( $item['id'], $this->in_cart_upsells ) ) {
                if ( !$this->hasEnoughQuantity( $item['woo_product'], $item['id'], $qty ) ) {
                    return;
                }
                /*
                /* If we marked it as "do not propose if already in cart as gift", we skip
                /* if we find same id as gift on cart or same parent id as gift on cart
                */
                
                if ( isset( $item['hide_if_gifted'] ) && $item['hide_if_gifted'] == true ) {
                    if ( array_key_exists( $item['id'], $this->in_cart_gifts ) ) {
                        return;
                    }
                    /*
                    /* Check for parent id also
                    */
                    if ( isset( $item['hide_if_gifted_parent'] ) && $item['hide_if_gifted_parent'] == true ) {
                        if ( in_array( $item['woo_product']->get_parent_id(), $this->gifts_in_cart_parent_ids ) ) {
                            return;
                        }
                    }
                }
                
                /*
                /* Have we already listed it?
                */
                if ( !array_key_exists( $item['id'], $this->valid_upsells ) ) {
                    $this->valid_upsells[$item['id']] = $item;
                }
            } else {
                if ( !$this->hasEnoughQuantity( $item['woo_product'], $item['id'], 0 ) ) {
                    return;
                }
                //else, since it's a valid upsell and has been added by the customer, we mark the one
                //already in the cart as valid ( checked = 1 )
                $this->in_cart_upsells[$item['id']]['checked'] = true;
            }
        
        }
    }
    
    /*
    /* STEP 2
    /* now that we parsed all the upsells and checked what has been added to the cart
    /* we remove what has been added but doesn't met requirements anymore
    */
    private function updateChanges()
    {
        foreach ( $this->in_cart_upsells as $id => $value ) {
            if ( !$value['checked'] ) {
                WC()->cart->remove_cart_item( $value['key'] );
            }
        }
    }
    
    /*
    /* Check if the product is purchasable
    */
    private function canBeSold( $item )
    {
        if ( $item && $item->is_in_stock() && $item->is_purchasable() && empty($item->get_post_password()) ) {
            return true;
        }
        return false;
    }
    
    /*
    /* Check if the product has enough stock to be displayed/in cart
    */
    private function hasEnoughQuantity( $item, $prod_id, $qtyToCompare )
    {
        if ( !$item->managing_stock() ) {
            return true;
        }
        $qty_on_cart = ( isset( $this->products_qty_on_cart[$prod_id] ) ? $this->products_qty_on_cart[$prod_id]['qty'] : 0 );
        if ( $item->get_stock_quantity() - $qty_on_cart >= $qtyToCompare ) {
            return true;
        }
        return false;
    }

}