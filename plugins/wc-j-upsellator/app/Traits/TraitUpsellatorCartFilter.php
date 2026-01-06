<?php

namespace WcJUpsellator\Traits;

trait TraitUpsellatorCartFilter
{
    /*	
    /* Check if one of the product in cart
    /* has the category that triggers the gift or the upsell
    */
    public function categoriesMatches( $values, $cart_product, $upsell_block )
    {
        return false;
    }
    
    /*
    /* Check if one of the product in cart
    /* has the attribute that triggers the gift or the upsell
    */
    private function attributesMatches( $values, $cart_product, $upsell_block )
    {
        return false;
    }
    
    /*
    /* Check if one of the product in cart
    /* is in the list of products that trigger the gift or the upsell
    */
    private function productsMatches( $item_to_check, $cart_product, $upsell_block = false )
    {
        return false;
    }
    
    private function categoriesAndAttributesMatches( $item_to_check, $cart_product, $upsell_block = false )
    {
        return false;
    }
    
    /*
    /* Filters
    */
    private function activeWall( $item )
    {
        return isset( $item['active'] ) && $item['active'] == false;
    }
    
    /*
    /* Logged-in condition
    */
    private function loggedInWall( $item )
    {
        return isset( $item['only_registered'] ) && $item['only_registered'] == true && !is_user_logged_in();
    }
    
    /*
    /* Min subtotal condition
    */
    private function subtotalMinCheck( $item, $subtotal )
    {
        $limit = $item['cart_limit'] ?? 0;
        $filtered_limit = apply_filters(
            'wjufw_product_cart_limit',
            $limit,
            $limit,
            $item
        );
        return $filtered_limit <= $subtotal;
    }
    
    /*
    /* Max subtotal condition
    */
    private function subtotalMaxCheck( $item, $subtotal )
    {
        $limit = ( isset( $item['cart_limit_to'] ) && $item['cart_limit_to'] > 0 ? $item['cart_limit_to'] : PHP_INT_MAX );
        $filtered_limit = apply_filters(
            'wjufw_product_cart_limit',
            $limit,
            $limit,
            $item
        );
        return $filtered_limit > $subtotal;
    }
    
    /*
    /* Coupon condition
    */
    private function couponCheck( $item )
    {
        return true;
    }

}